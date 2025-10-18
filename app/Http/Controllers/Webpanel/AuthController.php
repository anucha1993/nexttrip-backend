<?php

namespace App\Http\Controllers\Webpanel;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


use App\Models\Backend\User;
use App\Models\Backend\MemberModel;

class AuthController extends Controller
{
    protected $prefix = 'backend';
    public function getLogin()
    {
        if (Auth::guard()->id() != null) {
            return redirect('webpanel\slide');
        } else {
            return view("$this->prefix.auth.login", [
                'css' => [""],
                'prefix' => $this->prefix
            ]);
        }
    }
    public function postLogin(Request $request)
    {
        $username = $request->username;
        $password = $request->password;

        $remember = ($request->remember == 'on') ? true : false;
        if (Auth::attempt(['email' => $username, 'password' => $password], $remember)) {
            $member = User::find(Auth::guard()->id());
            if ($member->status != "active") {
                return redirect('webpanel\login')->with(['error' => 'ไม่สามารถใช้งานได้ กรุณาติดต่อผู้ดูแล !']);
            } else {
                return redirect('webpanel\slide');
            }
        } else {
            return redirect('webpanel/login')->with(['error' => 'ชื่อผู้ใช้งาน หรือรหัสผ่านผิด !']);
        }
    }

    public function logOut()
    {
        if (!Auth::logout()) {
            return redirect("webpanel/login");
        }
    }
}
