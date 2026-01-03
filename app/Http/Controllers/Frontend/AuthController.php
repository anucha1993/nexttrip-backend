<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Webpanel\LogsController;

//=== Model Backend ===
use App\Models\Backend\MemberModel;

//=====================

class AuthController extends Controller
{
    protected $prefix = 'front-end';

    //=== Submit Auth ===
    public function postLogin(Request $request)
    {
        $lang = Session('lang');
        $username = $request->email;
        $password = $request->password;

        $remember = ($request->remember == 'on') ? true : false;
        if (Auth::guard('Member')->attempt(['email' => $username, 'password' => $password], $remember)) {
            $data = MemberModel::find(Auth::guard('Member')->id());
            if ($data->status == "off") {
                Auth::guard('Member')->logout();
                return view("back-end.alert.alert", [
                    'url' => "/$lang/login",
                    'title' => "เกิดข้อผิดพลาด",
                    'text' => "บัญชีนี้ไม่พร้อมใช้งานกรุณาติดต่อทางผู้ดูแล",
                    'icon' => 'error'
                ]);
            }
            if ($data->delete_status == "yes") {
                Auth::guard('Member')->logout();
                return view("back-end.alert.alert", [
                    'url' => "/$lang/login",
                    'title' => "เกิดข้อผิดพลาด",
                    'text' => "บัญชีนี้ถูกปิด กรุณาติดต่อทางผู้ดูแล",
                    'icon' => 'error'
                ]);
            }
            if ($data->status == "on" || $data->delete_status == "off") {
                return redirect("/$lang/account-info");
            }
        } else {
            return redirect("/$lang/login")->with(['error' => 'ชื่อผู้ใช้งาน หรือรหัสผ่านผิด !']);
        }
    }


    public function postRegister(Request $request)
    {
        try {
            $lang = Session('lang');
            DB::beginTransaction();
            $check = $this->check_user('new', $request->email);
            $data = new MemberModel();
            $data->password = bcrypt($request->password);

            $data->status = 'on';
            $data->delete_status = 'off';
            $data->created = date('Y-m-d H:i:s');
            $data->updated = date('Y-m-d H:i:s');

            if ($check) {
                return view("$this->prefix.alert.alert", [
                    'url' => "$lang/register",
                    'title' => "เกิดข้อผิดพลาด",
                    'text' => "ชื่อผู้ใช้งานนี้มีอยู่ในระบบ !",
                    'icon' => 'error'
                ]);
            } else {
                $data->email = $request->email;
                $data->firstname = $request->firstname;
                $data->lastname = $request->lastname;

                if ($data->save()) {
                    DB::commit();
                    return view("$this->prefix.alert.success", ['url' => url("$lang/login")]);
                } else {
                    return view("$this->prefix.alert.error", ['url' => url("$lang/register")]);
                }
            }
        } catch (\Exception $e) {
            $error_log = $e->getMessage();
            $error_line = $e->getLine();
            $type_log = 'backend';
            $error_url = url()->current();
            $log_id = LogsController::save_logbackend($type_log, $error_log, $error_line, $error_url);

            return view("$this->prefix.alert.alert", [
                'url' => $error_url,
                'title' => "เกิดข้อผิดพลาดทางโปรแกรม",
                'text' => "กรุณาแจ้งรหัส Code : $log_id ให้ทางผู้พัฒนาโปรแกรม ",
                'icon' => 'error'
            ]);
        }
    }
    public function check_user($type, $email, $id = null)
    {
        if ($type == 'new') {
            $check = MemberModel::where(['email' => $email])->first();
        } else if ($type == 'edit') {
            $check = MemberModel::where(['email' => $email])->where('id', '!=', $id)->first();
        }
        return $check;
    }
    public function logOut()
    {
        $lang = Session('lang');
        if (!Auth::guard('Member')->logout()) {
              return redirect("/");
        }
    }
}
