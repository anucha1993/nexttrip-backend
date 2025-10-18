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
use App\Models\Backend\PromotionModel;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\DB;

use App\Models\Backend\PromotionTagModel;

class PromotionTagController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'promotion-tag';
    protected $folder = 'promotion-tag';
    protected $menu_id = '31';
    protected $name_page = "แท็กโปรโมชั่น";

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
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "แท็กโปรโมชั่น", "last" => 0],
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
        $sTable = PromotionTagModel::orderby('id', 'desc')
        ->when($like, function ($query) use ($like) {
            if (@$like['name'] != "") {
                $query->where('tag', 'like', '%' . $like['name'] . '%');
            }
        })
        ->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                $data = date('d/m/Y', strtotime('+543 Years', strtotime($row->created_at)));
                return $data;
            })
            ->addColumn('img', function ($row) {
                $data = "<center> <img src='$row->img' style='width: 15%;'></center>";
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
            ->rawColumns(['created_at', 'action','img'])
            ->make(true);
    }
    public function add(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->add  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $navs = [
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลโปรโมชั่น", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "แท็กโปรโมชั่น", "last" => 0],
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
        $data = PromotionTagModel::find($id);
        $navs = [
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลโปรโมชั่น", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "แท็กโปรโมชั่น", "last" => 0],
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
                $data = new PromotionTagModel();
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');
                $data->save();
            } else {
                $data = PromotionTagModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s'); 
            }
            $image  = $request->image;
            $allow = ['png', 'jpeg', 'jpg','svg'];
            if ($image) {
                if ($data->img != null) {
                    Storage::disk('public')->delete($data->img);
                }
                $lg = Image::make($image->getRealPath());
                $ext = explode("/", $lg->mime());
                $lg->resize(65, 65)->stream();
                    
                $new = 'upload/promotion/tag' . date('dmY-Hism') . '.' . $ext[1];
                if (in_array($ext[1], $allow)) {
                    $store = Storage::disk('public')->put($new, $lg);
                    $data->img = $new;
                    $data->save();
                }
                  
            }
            $data->tag = $request->tag;
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
        $datas = PromotionTagModel::find($id);
        if (@$datas) {
            $query = PromotionTagModel::destroy($datas->id);
            $promo = PromotionModel::where('tag_id',$datas->id)->get();
            foreach($promo as $pro){
                $pro->tag_id = null;
                $pro->save();
            }
        }

        if (@$query) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
}
