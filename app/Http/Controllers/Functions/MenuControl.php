<?php

namespace App\Http\Controllers\Functions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Backend\User;
use App\Models\Backend\RoleModel;
use App\Models\Backend\Role_listModel;

class MenuControl extends Controller
{
    protected $prefix = 'back-end';
    public static function auth_menu()
    {
        return view("back-end.alert.alert",[
            'url'=> "webpanel",
            'title' => "เกิดข้อผิดพลาด",
            'text' => "คุณไม่ได้รับสิทธิ์ในการใช้เมนูนี้ ! ",
            'icon' => 'error'
        ]); 
    }
    public static function menu_active(Request $request, $menu_id)
    {
        $member_id = Auth::guard('')->id();
        $member = User::find($member_id);
        $role = RoleModel::find($member->role);
        $list_role = Role_listModel::where(['role_id'=>$role->id, 'menu_id'=>$menu_id])->first();
        return $list_role;


        // $member_id = Auth::guard('')->id();
        // $member = User::find($member_id);
        // $role = RoleModel::find($member->role);

        // $array_menu = null;
        // $active_menu = $role->active_menu;
        // $active_menu=str_replace('[','',$active_menu);
        // $active_menu=str_replace(']','',$active_menu);
        // $active_menu=str_replace('"','',$active_menu);
        // $loop =explode(',' , $active_menu );
        // $array_menu = $loop;
        // if(!in_array($menu_id,$array_menu))
        // {
        //     $status = 'off';
        // }
        // else
        // {
        //     $status = 'on';
        // }
        // return $status;

    }
}
