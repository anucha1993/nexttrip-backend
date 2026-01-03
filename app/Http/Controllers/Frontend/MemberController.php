<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Functions\MenuControl;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Webpanel\LogsController;
use App\Http\Middleware\Member;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use App\Models\Backend\MemberModel;

class MemberController extends Controller
{
    protected $prefix = 'front-end';
    //=== Member Auth ===
    public function cart(Request $request)
    {
        return view("$this->prefix.cart",[
            'prefix' => $this->prefix,
        ]);
    }
    public function account_info(Request $request)
    {
        return view("$this->prefix.account-info",[
            'prefix' => $this->prefix,
            'row' => MemberModel::find(Auth::guard('Member')->id()),
        ]);
    }

    public function account_info_changename(Request $request)
    {
        try {
            $lang = Session('lang');
            DB::beginTransaction();
            
            $data = MemberModel::find(Auth::guard('Member')->id());
            $data->updated = date('Y-m-d H:i:s');
            $data->firstname = $request->firstname;
            $data->lastname = $request->lastname;

            if ($data->save()) {
                DB::commit();
                return view("$this->prefix.alert.success", ['url' => url("$lang/account-info")]);
            } else {
                return view("$this->prefix.alert.error", ['url' => url("$lang/account-info")]);
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

    public function account_info_changephone(Request $request)
    {
        try {
            $lang = Session('lang');
            DB::beginTransaction();
            
            $data = MemberModel::find(Auth::guard('Member')->id());
            $data->updated = date('Y-m-d H:i:s');
            $data->phone = $request->phone;

            if ($data->save()) {
                DB::commit();
                return view("$this->prefix.alert.success", ['url' => url("$lang/account-info")]);
            } else {
                return view("$this->prefix.alert.error", ['url' => url("$lang/account-info")]);
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
    //=====================
}
