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

use App\Models\Backend\BannerModel;

class BannerController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'banner-cover';
    protected $folder = 'banner-cover';
    protected $menu_id = '29';
    protected $name_page = "แบนเนอร์";

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
            '1' => ['url' => "$this->segment", 'name' => "หน้าหลัก", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder", 'name' => "แบนเนอร์", "last" => 1],
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
        $sTable = BannerModel::orderby('id', 'asc') ->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('img', function ($row) {
                $data = "<center> <img src='$row->img' style='width: 20%;'></center>";
                return $data;
            })
            ->addColumn('created_at', function ($row) {
                $data = date('d/m/Y', strtotime('+543 Years', strtotime($row->created_at)));
                return $data;
            })
            ->addColumn('action', function ($row) {
                $data = "";
                $menu_control = Helper::menu_active($this->menu_id);
                if($menu_control->edit == "on")
                {
                    $data.= " <a href='$this->segment/$this->folder/edit/$row->id' class='mr-3 mb-2 btn btn-sm btn-info' title='Edit'><i class='fa fa-edit w-4 h-4 mr-1'></i> แก้ไข </a>  ";  
                }
                return $data;
            })
            ->rawColumns(['created_at', 'action','img'])
            ->make(true);
    }
    public function edit(Request $request, $id)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = BannerModel::find($id);
        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "หน้าหลัก", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "แบนเนอร์", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/edit/$id", 'name' => "แก้ไขรูปแบนเนอร์เมนู $data->menu", "last" => 1],
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
    
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
                $data = BannerModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s');
                $data->detail = $request->detail;

                $image = $request->image;
                $image_mb = $request->image_mb;
                $allow = ['png', 'jpeg', 'jpg', 'webp'];
                if ($image) {
                    if ($data->img != null) {
                        Storage::disk('public')->delete($data->img);
                    }
                    $lg = Image::make($image->getRealPath());
                    $ext = explode("/", $lg->mime());
                    $lg->resize(1920, 400)->stream();
                    
                    $new = 'upload/banner/banner-cover' . date('dmY-Hism') . '.' . $ext[1];
                    if (in_array($ext[1], $allow)) {
                        $store = Storage::disk('public')->put($new, $lg);
                        $data->img = $new;
                        $data->save();
                    
                    }
                    // dd($data);
                }
                if ($image_mb) {
                    if ($data->img_mobile != null) {
                        Storage::disk('public')->delete($data->img_mobile);
                    }
                    $lg = Image::make($image_mb->getRealPath());
                    $ext = explode("/", $lg->mime());
                    $lg->resize(390, 350)->stream();
                    
                    $new = 'upload/banner/banner-cover-mobile' . date('dmY-Hism') . '.' . $ext[1];
                    if (in_array($ext[1], $allow)) {
                        $store = Storage::disk('public')->put($new, $lg);
                        $data->img_mobile = $new;
                        $data->save();
                      
                    }
                    // dd($data);
                }
                
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
}
