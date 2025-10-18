<?php

namespace App\Http\Controllers\Webpanel;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Functions\MenuControl;
use App\Http\Controllers\Functions\FunctionControl;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Webpanel\LogsController;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\DB;

use App\Models\Backend\MemberModel;
use App\Models\Backend\User;

class MemberController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'member';
    protected $folder = 'member';
    protected $menu_id = '47';
    protected $name_page = "ข้อมูลสมาชิก";

    public function auth_menu()
    {
        return view("$this->prefix.alert.alert",[
            'url'=> "webpanel",
            'title' => "เกิดข้อผิดพลาด",
            'text' => "คุณไม่ได้รับอนุญาตให้ใช้เมนูนี้! ",
            'icon' => 'error'
        ]); 
    }

    public function index(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder", 'name' => $this->name_page, "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.index", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'menu_control' => $menu_control,
        ]);
    }
    public function datatable(Request $request)
    {
        $like = $request->Like;

        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}

        $sTable = MemberModel::orderby('id','desc')
            ->when($like, function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    if (@$like['search_name'] != "") {
                        $query->where('name', 'like', '%' . $like['search_name'] . '%');
                        $query->orWhere('surname', 'like', '%' . $like['search_name'] . '%');
                    }
                });
                if (@$like['search_phone'] != "") {
                    $query->where('phone', 'like', '%' . $like['search_phone'] . '%');
                }
            })
            ->whereNull('deleted_at');

        $sQuery = DataTables::of($sTable);
        return $sQuery
            ->addIndexColumn()
            ->editColumn('name', function ($row) {
                $data = $row->name.' '.$row->surname;
                return $data;
            })
            ->addColumn('updated_at', function ($row) {
                $data = "";
                if($row->updated_at){
                    $data .= date('d/m/Y H:i', strtotime('+543 Years', strtotime($row->updated_at)));
                }
                return $data;
            })
            ->addColumn('type', function ($row) {
                $data = "";
                if($row->type == 'F'){
                    $data .= 'Facebook';
                }elseif($row->type == 'L'){
                    $data .= 'Line';
                }elseif($row->type == 'G'){
                    $data .= 'Gmail';
                }elseif($row->type == 'R'){
                    // if($row->google_id){
                    //     $data .= 'สมัครสมาชิก,Gmail';
                    // }else{
                        $data .= 'Register';
                    // }
                }
                return $data;
            })
            ->editColumn('action', function ($row) {
                $data = "";
                $menu_control = Helper::menu_active($this->menu_id);
                if($menu_control->edit == "on")
                {
                    $data.= " <a href='$this->segment/$this->folder/edit/$row->id' class='mr-3 mb-2 btn btn-sm btn-info' title='Edit'><i class='fa fa-edit w-4 h-4 mr-1'></i> แก้ไข </a>";
                }
                if($menu_control->delete == "on")
                {
                    $data.= " <a href='javascript:' class='mr-3 mb-2 btn btn-sm btn-danger' onclick='deleteItem($row->id)' title='Delete'><i class='far fa-trash-alt w-4 h-4 mr-1'></i> ลบ </a>";  
                }
                return $data;
            })
            ->rawColumns(['name','created_at','action','type'])
            ->make(true);
    }

    public function add(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}

        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => $this->name_page, "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/add", 'name' => "เพิ่ม", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.add", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
        ]);
    }

    public function edit(Request $request,$id=null)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}

        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => $this->name_page, "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/edit/$id", 'name' => "แก้ไข", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.edit", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'row' => MemberModel::find($id),
        ]);
    }

    //==== Function Insert Update Delete Status Sort & Others ====
    public function insert(Request $request, $id = null)
    {
        return $this->store($request, $id = null);
    }
    public function update(Request $request, $id)
    {
        return $this->store($request, $id);
    }
    public function store($request, $id = null)
    {
        try {
            DB::beginTransaction();
            $check_mail = $request->email?MemberModel::where('email',$request->email)->where('id','!=',$id)->whereNull('deleted_at')->first():0;
            if($check_mail){
                return view("$this->prefix.alert.alert", [
                    'url' => '/webpanel/member',
                    'title' => "เกิดข้อผิดพลาดทางโปรแกรม",
                    'text' => "มีอีเมลนี้อยู่ในระบบแล้ว",
                    'icon' => 'error'
                ]);
            }
            if ($id == null && !$check_mail) {
                $data = new MemberModel();
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');
                $data->password = bcrypt($request->password);
            } else {
                $data = MemberModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s');
                if($request->resetpassword == "on"){
                    $data->password = bcrypt($request->password);
                }
            }

            $data->name = $request->name;
            $data->surname = $request->surname;
            $data->phone = $request->phone;
            $data->email = $request->email;

            if ($data->save()) {
                DB::commit();
                return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder")]);
            } else {
                DB::rollback();
                return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder")]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            //dd($e->getMessage());
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

    // public function destroy(Request $request)
    // {
    //     $datas = MemberModel::find($request->id);
    //     if (@$datas) {
    //         foreach ($datas as $data) {
    //             $query = MemberModel::destroy($data->id);
    //         }
    //     }

    //     if (@$query) {
    //         return response()->json(true);
    //     } else {
    //         return response()->json(false);
    //     }
    // }

    public function destroy(Request $request)
    {
        $datas = MemberModel::find($request->id);
        if (@$datas) {
            foreach ($datas as $data) {
                $data->deleted_at = date('Y-m-d H:i:s'); // soft delete
                if($data->save()){
                    return response()->json(true);
                } else {
                    return response()->json(false);
                }
            }
        } else {
            return response()->json(false);
        }
    }
}
