<?php

namespace App\Http\Controllers\Webpanel;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Functions\MenuControl;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Webpanel\LogsController;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\DB;

use App\Models\Backend\User;
use App\Models\Backend\RoleModel;

class UserController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'user';
    protected $folder = 'user';
    protected $name_page = "รายการผู้ดูแล";

    public function datatable(Request $request)
    {
        $like = [
            'search_choice' => $request->search_choice,
            'search_type' => $request->search_type,
            'search_keyword' => $request->search_keyword,
        ];
        $sTable = User::orderby('id', 'asc')
            ->when($like, function ($query) use ($like) {
                if (@$like['search_choice'] != "") 
                {
                    if(@$like['search_type'] == "like")
                    {
                        $query->where(@$like['search_choice'], 'like', '%' . @$like['search_keyword'] . '%');
                    }
                    else
                    {
                        $query->where(@$like['search_choice'], @$like['search_type'], @$like['search_keyword']);
                    }
                }
            })
            ->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('action_name', function ($row) {
                $data = $row->name;
                return $data;
            })
            ->addColumn('action_username', function ($row) {
                $data = $row->email;
                return $data;
            })
            ->addColumn('action_role', function ($row) {
                $query = RoleModel::find($row->role);
                $data = @$query->name;
                return $data;
            })
            ->editColumn('status', function ($row) {
                $status = "";
                if($row->status == "active")
                {
                    $status = "checked";
                }
                $data = "<div class='form-check form-switch w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0'>
                            <input id='status_change_$row->id' data-id='$row->id' onclick='status($row->id);' class='show-code form-check-input mr-0 ml-3' type='checkbox' $status>
                        </div>";
                return $data;
            })
            ->addColumn('created', function ($row) {
                $data = date('d/m/Y', strtotime('+543 Years', strtotime($row->created)));
                return $data;
            })
            ->addColumn('action', function ($row) {
                return " <a href='$this->segment/$this->folder/edit/$row->id' class='mr-3 btn btn-sm btn-info' title='Edit'><i class='fa fa-edit w-4 h-4 mr-1'></i> Edit </a>                                                 
                <a href='javascript:' class='btn btn-sm btn-danger' onclick='deleteItem($row->id)' title='Delete'><i class='far fa-trash-alt w-4 h-4 mr-1'></i> Delete</a>
            ";
            })
            ->rawColumns(['action_name', 'status', 'created', 'action'])
            ->make(true);
    }

    public function index(Request $request)
    {
        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "User", "last" => 1],
        ];
        $user = Auth::user();
        if($user->role != 1){
            abort (404);
        }
        return view("$this->prefix.pages.$this->folder.index", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
        ]);
    }

    public function add(Request $request)
    {
        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "User", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/add", 'name' => "Add", "last" => 1],
        ];
        $user = Auth::user();
        if($user->role != 1){
            abort (404);
        }
        return view("$this->prefix.pages.$this->folder.add", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'roles' => RoleModel::where(['status' => 'on'])->get(),
            'navs' => $navs,
        ]);
    }



    public function edit(Request $request, $id)
    {
        $data = User::find($id);
        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "User", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/edit/$id", 'name' => "$data->name", "last" => 1],
        ];
        $user = Auth::user();
        if($user->role != 1){
            abort (404);
        }
        return view("$this->prefix.pages.$this->folder.edit", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'row' => $data,
            'menus' => \App\Models\Backend\MenuModel::where(['status' => 'on', 'position' => 'main'])->get(),
            'roles' => RoleModel::where(['status' => 'on'])->get(),
            'navs' => $navs,
        ]);
    }

    public function status($id = null)
    {
        $data = User::find($id);
        $data->status = ($data->status == 'inactive') ? 'active' : 'inactive';
        if ($data->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function destroy(Request $request)
    {
        $datas = User::find(explode(',', $request->id));
        if (@$datas) {
            foreach ($datas as $data) {
                $query = User::destroy($data->id);
            }
        }

        if (@$query) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
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
            $check = $this->check_user($request,$id,$request->username);
            if($check != "yes")
            {
                return view("$this->prefix.alert.alert", [
                    'url' => "$this->segment/$this->folder",
                    'title' => "เกิดข้อผิดพลาด",
                    'text' => "$check->text ",
                ]);
                return false;
            }
            if ($id == null) {
                $data = new User();
                $data->created = date('Y-m-d H:i:s');
                $data->updated = date('Y-m-d H:i:s');
                $data->password = bcrypt($request->password);
            } else {
                $data = User::find($id);
                $data->updated = date('Y-m-d H:i:s');
                if($request->resetpassword == "on")
                {
                    $data->password = bcrypt($request->password);
                }
            }
            $data->role = $request->role;
            $data->status = $request->status_check;
            $data->name = $request->name;
            $data->email = $request->username;
			   $data->phone = $request->phone;
            if ($data->save()) {
                DB::commit();
                return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder")]);
            } else {
                DB::rollback();
                return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder")]);
            }
        } catch (\Exception $e) {
            DB::rollback();
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

    public function check_user(Request $request, $id = null, $email)
    {
        if ($id == null) {
            $check = User::where('email', $email)->first();
            if ($check) 
            {
                return view("$this->prefix.alert.alert", [
                    'url' => "$this->segment/$this->folder",
                    'title' => "เกิดข้อผิดพลาด",
                    'text' => "อีเมล์นี้มีอยู่ในระบบ ",
                ]);
                $check_true = "no";
            } else {
                $check_true = "yes";
            }
        } else {
            $check = User::where('email', $email)->where('id', '!=', $id)->first();
            if ($check) {
                return view("$this->prefix.alert.alert", [
                    'url' => "$this->segment/$this->folder",
                    'title' => "เกิดข้อผิดพลาด",
                    'text' => "อีเมล์นี้มีอยู่ในระบบ ",
                ]);
                $check_true = "no";
            } else {
                $check_true = "yes";
            }
        }
        return $check_true;
    }
}
