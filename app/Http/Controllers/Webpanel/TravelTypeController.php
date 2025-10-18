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
use Illuminate\Support\Facades\DB;

use App\Models\Backend\TravelTypeModel;

class TravelTypeController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'travel-type';
    protected $folder = 'travel-type';
    protected $menu_id = '34';
    protected $name_page = "เที่ยวบินและการเดินทาง";

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
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "แพ็คเกจทัวร์", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "เที่ยวบินและการเดินทาง", "last" => 0],
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
        $sTable = TravelTypeModel::where('deleted_at',null)->orderby('id', 'desc')
        ->when($like, function ($query) use ($like) {
            if (@$like['search_code'] != "") {
                $query->where('code', 'like', '%' . $like['search_code'] . '%');
            }
            if (@$like['search_name'] != "") {
                $query->where('travel_name', 'like', '%' . $like['search_name'] . '%');
            }
            if (@$like['search_status'] != "") {
                $query->where('status', $like['search_status'] );
            }
        })
        ->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('img', function ($row) {
                $data = "<center> <img src='$row->image' style='width: 20%;'></center>";
                return $data;
            })
            ->editColumn('status', function ($row) {
                $status = "";
                if($row->status == "on")
                {
                    $status = "checked";
                }
                $data = "<div class='form-check form-switch w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0'>
                            <input id='status_change_$row->id' data-id='$row->id' onclick='status($row->id);' class='show-code form-check-input mr-0 ml-3' type='checkbox' $status>
                        </div>";
                return $data;
            })
            ->addColumn('updated_at', function ($row) {
                $data = date('d/m/Y', strtotime('+543 Years', strtotime($row->updated_at)));
                return $data;
            })
            ->addColumn('action', function ($row) {
                $data = "";
                $menu_control = Helper::menu_active($this->menu_id);
                if($menu_control->edit == "on")
                {
                    $data.= " <a href='$this->segment/$this->folder/edit/$row->id' class='mr-3 mb-2 btn btn-sm btn-info' title='Edit'><i class='fa fa-edit w-4 h-4 mr-1'></i> แก้ไข </a>  ";  
                }
                if($menu_control->delete == "on")
                {
                    $data.= " <a href='javascript:' class='mr-3 mb-2 btn btn-sm btn-danger' onclick='deleteItem($row->id)' title='Delete'><i class='far fa-trash-alt w-4 h-4 mr-1'></i> ลบ </a>";  
                }
                return $data;
            })
            ->rawColumns(['img','created_at','status','action'])
            ->make(true);
    }
    public function add(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->add  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $navs = [
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "แพ็คเกจทัวร์", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "เที่ยวบินและการเดินทาง", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/add", 'name' => "เพิ่มข้อมูล", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.add", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
        ]);
    }
    public function edit(Request $request, $id)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = TravelTypeModel::find($id);
        $navs = [
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "แพ็คเกจทัวร์", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "เที่ยวบินและการเดินทาง", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/edit/$id", 'name' => "แก้ไขข้อมูล", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.edit", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'row' => $data,
            'navs' => $navs,
            'id' => $id,
        ]);
    }
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
            if ($id == null) {
                $data = new TravelTypeModel();
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');
                $data->save();
            } else {
                $data = TravelTypeModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s');
            }

            $image = $request->image;
            $allow_img = ['png', 'jpeg', 'jpg'];
            if ($image) {
                if ($data->image != null) {
                    Storage::disk('public')->delete($data->image);
                }
                $lg = Image::make($image->getRealPath());
                $ext = explode("/", $lg->mime());
                $lg->resize(65, 18)->stream();
                $new = 'upload/travel-type/logo' . date('dmY-Hism') . '.' . $ext[1];
                if (in_array($ext[1], $allow_img)) {
                    $store = Storage::disk('public')->put($new, $lg);
                    $data->image = $new;
                }
            }

            $data->code = $request->code;
            $data->travel_name = $request->travel_name;
            $data->code = $request->code;
            $data->code1 = $request->code1;
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

    public function status($id = null)
    {
        $data = TravelTypeModel::find($id);
        $data->status = ($data->status == 'off') ? 'on' : 'off';
        if ($data->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function destroy($id)
    {
        $datas = TravelTypeModel::find($id);
        if (@$datas) {
            Storage::disk('public')->delete($datas->image);
            $datas->deleted_at = date('Y-m-d H:i:s');
        }
        if (@$datas->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
}
