<?php
// Copy to config.php and fill in real values. config.php is never committed (chmod 600 on the server).
declare(strict_types=1);

return [
    // 'local' shows errors on screen; 'production' only writes them to storage/logs
    'env' => 'local',

    // full origin of the site, no trailing slash. Used for links in emails and the Origin check
    'base_url' => 'http://127.0.0.1:8000',
    // extra origins allowed to POST the contact form (e.g. the live domain during go-live)
    'allowed_origins' => [],

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'xtklaos_xtk',
        'user' => 'xtklaos_app',
        'pass' => '',
    ],

    // local: Mailpit (127.0.0.1:1025, no auth). Server: mail.xtklaos.com:465, secure 'ssl'
    'mail' => [
        'host' => '127.0.0.1',
        'port' => 1025,
        'secure' => '',          // '', 'ssl' or 'tls'
        'user' => '',
        'pass' => '',
        'from' => 'noreply@xtklaos.com',
        'from_name' => 'XTK',
        'timeout' => 10,
    ],

    // random 64-char hex string: php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
    'hmac_secret' => 'change-me',

    // set true behind HTTPS so the admin session cookie is Secure
    'cookie_secure' => false,

    // full path of mysqldump / mysql for bin/backup.php (blank = look in PATH)
    'mysqldump' => '',
    'mysql' => '',
];
