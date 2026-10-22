<?php
declare(strict_types=1);

namespace App\Http\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

final class AuthController extends Controller
{
    public function showLogin(string $error = '', string $email = '', int $status = 200): never
    {
        if ($error === '' && Auth::user() !== null) {
            Response::redirect('/admin');
        }
        $notice = Session::pull('_expired') ? 'ໝົດເວລາເຂົ້າລະບົບ ເພາະບໍ່ໄດ້ໃຊ້ງານດົນ. ກະລຸນາເຂົ້າໃໝ່.' : '';
        Response::html(View::render('admin/auth/login', ['error' => $error, 'email' => $email, 'notice' => $notice]), $status);
    }

    public function login(): never
    {
        $email = trim(Request::post('email'));
        if (!Csrf::valid(Request::post('_csrf'))) {
            $this->showLogin('ໝົດເວລາຂອງຟອມ. ກະລຸນາລອງໃໝ່.', $email, 400);
        }
        if ($email === '' || Request::post('password') === '') {
            $this->showLogin('ກະລຸນາໃສ່ອີເມວ ແລະ ລະຫັດຜ່ານ.', $email, 422);
        }
        $result = Auth::attempt($email, Request::post('password'), Request::ipBinary());
        if ($result === 'locked') {
            $this->showLogin('ໃສ່ລະຫັດຜິດຫຼາຍຄັ້ງເກີນໄປ. ກະລຸນາລໍຖ້າ 15 ນາທີ ແລ້ວລອງໃໝ່.', $email, 429);
        }
        if ($result !== 'ok') {
            $this->showLogin('ອີເມວ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ.', $email, 401);
        }
        Response::redirect('/admin', 303);
    }

    public function logout(): never
    {
        if (Csrf::valid(Request::post('_csrf'))) {
            Auth::logout();
        }
        Response::redirect('/admin/login', 303);
    }
}
