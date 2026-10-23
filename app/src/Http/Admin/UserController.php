<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\App;
use App\Core\Request;
use App\Core\Validator;
use App\Repository\UserRepository;

/** /admin/users (admins only) and /admin/account (anyone: change own password). */
final class UserController extends Controller
{
    public const ROLE_NAMES = ['admin' => 'ຜູ້ດູແລລະບົບ', 'editor' => 'ຜູ້ແກ້ໄຂເນື້ອຫາ'];

    private function repo(): UserRepository
    {
        return new UserRepository(App::db());
    }

    public function index(): never
    {
        $this->requireAdmin();
        $this->page('users/index', 'ຜູ້ໃຊ້', ['users' => $this->repo()->all()]);
    }

    public function create(): never
    {
        $this->requireAdmin();
        $this->page('users/edit', 'ເພີ່ມຜູ້ໃຊ້', ['u' => ['id' => 0, 'name' => '', 'email' => '', 'role' => 'editor', 'is_active' => 1], 'errors' => []]);
    }

    public function store(): never
    {
        $this->requireAdmin();
        $this->checkCsrf();
        [$data, $errors] = $this->input(null);
        if ($errors !== []) {
            $this->page('users/edit', 'ເພີ່ມຜູ້ໃຊ້', ['u' => ['id' => 0] + $data, 'errors' => $errors], 422);
        }
        $id = $this->repo()->create($data['name'], $data['email'], Request::post('password'), $data['role']);
        App::logger()->info('users: created', ['id' => $id, 'role' => $data['role']]);
        $this->activity('create', 'users', $id, ['name' => $data['name'], 'role' => $data['role']]);
        $this->back('/admin/users', 'ok', 'ເພີ່ມຜູ້ໃຊ້ແລ້ວ.');
    }

    public function edit(int $id): never
    {
        $this->requireAdmin();
        $u = $this->repo()->find($id);
        if ($u === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        $this->page('users/edit', 'ແກ້ໄຂຜູ້ໃຊ້', ['u' => $u, 'errors' => []]);
    }

    public function update(int $id): never
    {
        $me = $this->requireAdmin();
        $this->checkCsrf();
        $repo = $this->repo();
        $u = $repo->find($id);
        if ($u === null) {
            $this->page('errors/not-found', 'ບໍ່ພົບ', [], 404);
        }
        [$data, $errors] = $this->input($u);
        // never leave the system without an active admin, and never lock yourself out
        $losesAdmin = $u['role'] === 'admin' && (int) $u['is_active'] === 1 && ($data['role'] !== 'admin' || !$data['is_active']);
        if ($losesAdmin && ((int) $u['id'] === (int) $me['id'] || $repo->countActiveAdmins() <= 1)) {
            $errors['role'] = 'ປ່ຽນສິດ ຫຼື ປິດບັນຊີຂອງຕົນເອງ ຫຼື ຜູ້ດູແລຄົນສຸດທ້າຍບໍ່ໄດ້.';
        }
        if ($errors !== []) {
            $this->page('users/edit', 'ແກ້ໄຂຜູ້ໃຊ້', ['u' => ['id' => $id] + $data, 'errors' => $errors], 422);
        }
        $repo->update($id, $data['name'], $data['email'], $data['role'], $data['is_active']);
        if (Request::post('password') !== '') {
            $repo->setPassword($id, Request::post('password'));
        }
        App::logger()->info('users: updated', ['id' => $id, 'by' => (int) $me['id']]);
        $this->activity('update', 'users', $id, ['role' => $data['role'], 'active' => (bool) $data['is_active'], 'password' => Request::post('password') !== '']);
        $this->back('/admin/users', 'ok', 'ບັນທຶກແລ້ວ.');
    }

    /** @return array{0: array, 1: array<string,string>} */
    private function input(?array $existing): array
    {
        $data = [
            'name' => Validator::clean(Request::post('name')),
            'email' => mb_strtolower(Validator::clean(Request::post('email'))),
            'role' => Request::post('role'),
            'is_active' => Request::post('is_active') === '1',
        ];
        $errors = [];
        $len = mb_strlen($data['name']);
        if ($len < 2 || $len > 120) {
            $errors['name'] = 'ຊື່ຕ້ອງມີ 2–120 ຕົວອັກສອນ.';
        }
        if (!Validator::isEmail($data['email'])) {
            $errors['email'] = 'ອີເມວບໍ່ຖືກຕ້ອງ.';
        } else {
            $other = $this->repo()->findByEmail($data['email']);
            if ($other !== null && (int) $other['id'] !== (int) ($existing['id'] ?? 0)) {
                $errors['email'] = 'ອີເມວນີ້ມີຜູ້ໃຊ້ແລ້ວ.';
            }
        }
        if (!in_array($data['role'], UserRepository::ROLES, true)) {
            $errors['role'] = 'ກະລຸນາເລືອກສິດ.';
        }
        $password = Request::post('password');
        if ($existing === null || $password !== '') {
            $error = self::passwordError($password, Request::post('password_confirm'));
            if ($error !== null) {
                $errors['password'] = $error;
            }
        }
        return [$data, $errors];
    }

    public static function passwordError(string $password, string $confirm): ?string
    {
        if (mb_strlen($password) < UserRepository::MIN_PASSWORD) {
            return 'ລະຫັດຜ່ານຕ້ອງມີຢ່າງໜ້ອຍ ' . UserRepository::MIN_PASSWORD . ' ຕົວອັກສອນ.';
        }
        if (strlen($password) > 72) {
            return 'ລະຫັດຜ່ານຍາວເກີນໄປ (ສູງສຸດ 72 byte).';
        }
        return $password === $confirm ? null : 'ລະຫັດຜ່ານທັງສອງຊ່ອງບໍ່ກົງກັນ.';
    }

    public function account(): never
    {
        $this->requireLogin();
        $this->page('users/account', 'ບັນຊີຂອງຂ້ອຍ', ['errors' => []]);
    }

    public function updateAccount(): never
    {
        $me = $this->requireLogin();
        $this->checkCsrf();
        $errors = [];
        if (!password_verify(Request::post('current'), $me['password_hash'])) {
            $errors['current'] = 'ລະຫັດຜ່ານປັດຈຸບັນບໍ່ຖືກຕ້ອງ.';
        }
        $error = self::passwordError(Request::post('password'), Request::post('password_confirm'));
        if ($error !== null) {
            $errors['password'] = $error;
        }
        if ($errors !== []) {
            $this->page('users/account', 'ບັນຊີຂອງຂ້ອຍ', ['errors' => $errors], 422);
        }
        $this->repo()->setPassword((int) $me['id'], Request::post('password'));
        $this->activity('password', 'users', (int) $me['id']);
        $this->back('/admin/account', 'ok', 'ປ່ຽນລະຫັດຜ່ານແລ້ວ.');
    }
}
