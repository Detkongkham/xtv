<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Db;

final class UserRepository
{
    public const ROLES = ['admin', 'editor'];
    public const MIN_PASSWORD = 12;

    public function __construct(private Db $db)
    {
    }

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->one('SELECT * FROM users WHERE email = ?', [mb_strtolower(trim($email))]);
    }

    public function all(): array
    {
        return $this->db->all('SELECT id, name, email, role, is_active, last_login_at, created_at FROM users ORDER BY is_active DESC, name');
    }

    /** Active users, for the "assign to" select. */
    public function active(): array
    {
        return $this->db->all('SELECT id, name FROM users WHERE is_active = 1 ORDER BY name');
    }

    public function countActiveAdmins(): int
    {
        return (int) $this->db->value("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1");
    }

    public function create(string $name, string $email, string $password, string $role): int
    {
        return $this->db->insert('users', [
            'name' => $name,
            'email' => mb_strtolower(trim($email)),
            'password_hash' => self::hash($password),
            'role' => $role,
        ]);
    }

    public function update(int $id, string $name, string $email, string $role, bool $active): void
    {
        $this->db->run(
            'UPDATE users SET name = ?, email = ?, role = ?, is_active = ? WHERE id = ?',
            [$name, mb_strtolower(trim($email)), $role, $active ? 1 : 0, $id]
        );
    }

    public function setPassword(int $id, string $password): void
    {
        $this->db->run('UPDATE users SET password_hash = ? WHERE id = ?', [self::hash($password), $id]);
    }

    public function setPasswordHash(int $id, string $hash): void
    {
        $this->db->run('UPDATE users SET password_hash = ? WHERE id = ?', [$hash, $id]);
    }

    public function touchLogin(int $id): void
    {
        $this->db->run('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$id]);
    }

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
