<?php
// Create the first admin user (or another one) from the command line.
//   php bin/create-admin.php                          asks for name, email, password
//   php bin/create-admin.php --name="..." --email=... [--role=editor]   asks only for the password
declare(strict_types=1);

use App\Core\App;
use App\Core\Validator;
use App\Http\Admin\UserController;
use App\Repository\UserRepository;

require __DIR__ . '/../bootstrap.php';

$opts = getopt('', ['name:', 'email:', 'role:']);
$name = Validator::clean((string) ($opts['name'] ?? ask('Name: ')));
$email = mb_strtolower(Validator::clean((string) ($opts['email'] ?? ask('Email: '))));
$role = (string) ($opts['role'] ?? 'admin');

$users = new UserRepository(App::db());
$fail = static function (string $msg): never {
    fwrite(STDERR, $msg . "\n");
    exit(1);
};
if (mb_strlen($name) < 2) {
    $fail('Name must be at least 2 characters.');
}
if (!Validator::isEmail($email)) {
    $fail('Invalid email.');
}
if (!in_array($role, UserRepository::ROLES, true)) {
    $fail('Role must be admin or editor.');
}
if ($users->findByEmail($email) !== null) {
    $fail('A user with this email already exists.');
}

// XTK_ADMIN_PASSWORD lets scripts/tests run without a terminal; normal use types it hidden
$password = getenv('XTK_ADMIN_PASSWORD') ?: ask('Password (min ' . UserRepository::MIN_PASSWORD . ' chars): ', true);
$confirm = getenv('XTK_ADMIN_PASSWORD') ?: ask('Repeat password: ', true);
$error = UserController::passwordError($password, $confirm);
if ($error !== null) {
    $fail('Password: ' . $error);
}

$id = $users->create($name, $email, $password, $role);
echo "Created {$role} #{$id} <{$email}>\n";

function ask(string $prompt, bool $hidden = false): string
{
    echo $prompt;
    if ($hidden && stream_isatty(STDIN)) {
        shell_exec('stty -echo');
        $line = fgets(STDIN);
        shell_exec('stty echo');
        echo "\n";
    } else {
        $line = fgets(STDIN);
    }
    return trim((string) $line);
}
