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

use App\Models\Backend\PromotionModel;
use App\Models\Backend\PromotionTagModel;

class PromotionController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'promotion';
    protected $folder = 'promotion';
    protected $menu_id = '32';
    protected $name_page = "รายการโปรโมชั่น";

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
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลโปรโมชั่น", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "รายการโปรโมชั่น", "last" => 0],
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
        $sTable = PromotionModel::where('deleted_at',null)->orderby('id', 'desc')
        ->when($like, function ($query) use ($like) {
            if (@$like['name'] != "") {
                $query->where('promotion_name', 'like', '%' . $like['name'] . '%');
            }
        })
        ->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            // ->addColumn('tag_id', function ($row) {
            //     $tag = PromotionTagModel::find($row->tag_id);
            //     $data = $tag->tag;
            //     return $data;
            // })
            // ->addColumn('count', function ($row) {
            //     $data = $row->count_date;
            //     return $data;
            // })
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
            ->rawColumns(['action','status','tag_id'])
            ->make(true);
    }
    public function status($id = null)
    {
        $data = PromotionModel::find($id);
        $data->status = ($data->status == 'off') ? 'on' : 'off';
        if ($data->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
    public function add(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->add  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $navs = [
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลโปรโมชั่น", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "รายการโปรโมชั่น", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/add", 'name' => "เพิ่มข้อมูล", "last" => 1],
        ];
        $tag = PromotionTagModel::get();
        return view("$this->prefix.pages.$this->folder.add", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'tag' => $tag,
        ]);
    }
    public function edit(Request $request, $id)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = PromotionModel::find($id);
        $navs = [
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลโปรโมชั่น", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "รายการโปรโมชั่น", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/edit/$id", 'name' => "แก้ไขข้อมูล", "last" => 1],
        ];
        $tag = PromotionTagModel::get();
        return view("$this->prefix.pages.$this->folder.edit", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'row' => $data,
            'navs' => $navs,
            'id' => $id,
            'tag' => $tag,
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
                $data = new PromotionModel();
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');
                $data->save();
            } else {
                $data = PromotionModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s'); 
            }
            $data->tag_id = $request->tag_id;
            $data->promotion_name = $request->promotion_name;
            // if($request->discount != null){
            //     $data->discount = $request->discount;
            // }else{
            //     $data->discount = 0;
            // }
            // $data->type_duscount = $request->type_duscount;
            $data->start_date = $request->start_date;
            $data->end_date = $request->end_date;
            $start = strtotime($request->start_date);
            $end = strtotime($request->end_date);
            $data->count_date = ($end - $start)/86400;
            $data->detail = $request->detail;
            // dd($data);
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
    public function destroy($id)
    {
        $datas = PromotionModel::find($id);
        if (@$datas) {
            $datas->deleted_at = date('Y-m-d H:i:s');
        }
        if (@$datas->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
}
