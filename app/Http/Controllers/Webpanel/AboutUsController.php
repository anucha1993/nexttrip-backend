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

use App\Models\Backend\AboutUsModel;
use App\Models\Backend\AwardModel;
use App\Models\Backend\AboutLicensModel;

class AboutUsController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'about-us';
    protected $folder = 'about-us';
    protected $menu_id = '13';
    protected $name_page = "ข้อมูลที่ตั้งบริษัท";

    public function auth_menu()
    {
        return view("$this->prefix.alert.alert",[
            'url'=> "webpanel",
            'title' => "เกิดข้อผิดพลาด",
            'text' => "คุณไม่ได้รับอนุญาตให้ใช้เมนูนี้! ",
            'icon' => 'error'
        ]); 
    }
    public function edit(Request $request, $id)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = AboutUsModel::find($id);
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder/$id", 'name' => "ข้อมูลที่ตั้งบริษัท", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/$id", 'name' => "แก้ไขข้อมูล", "last" => 1],
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
                $data = AboutUsModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s');
                $image_l = $request->img_left;
                $image_r = $request->img_right;
                $allow = ['png', 'jpeg', 'jpg'];
                // Image left
                if ($image_l) {
                    if ($data->img_left != null) {
                        Storage::disk('public')->delete($data->img_left);
                    }
                    $lg = Image::make($image_l->getRealPath());
                    $ext = explode("/", $lg->mime());
                    $lg->resize(960, 501)->stream();
                    
                    $new = 'upload/award/banner-left' . date('dmY-Hism') . '.' . $ext[1];
                    if (in_array($ext[1], $allow)) {
                        $store = Storage::disk('public')->put($new, $lg);
                        $data->img_left = $new;
                        $data->save();
                      
                    }
                }
                // Image Right
                if ($image_r) {
                    if ($data->img_right != null) {
                        Storage::disk('public')->delete($data->img_right);
                    }
                    $lg = Image::make($image_r->getRealPath());
                    $ext = explode("/", $lg->mime());
                    $lg->resize(960, 501)->stream();
                    
                    $new = 'upload/award/banner-right' . date('dmY-Hism') . '.' . $ext[1];
                    if (in_array($ext[1], $allow)) {
                        $store = Storage::disk('public')->put($new, $lg);
                        $data->img_right = $new;
                        $data->save();
                      
                    }
                }
                $data->detail = $request->detail;
                $data->text_left  = $request->text_left;
                $data->text_right  = $request->text_right;

             if ($data->save()) {
                 DB::commit();
                 return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder/$id")]);
             } else {
                DB::rollback();
                return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder/$id")]);
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
    //==== BEGIN: Gallery Data ====
    public function datatable_gallery(Request $request,$id)
    {
        // dd($request);
        $like = $request->Like;
        $sTable = AwardModel::orderby('id', 'desc')->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('img', function ($row) {
                $data = "<center> <img src='$row->img' style='width: 30%;'></center>";
                return $data;
            })
            ->addColumn('action', function ($row) {
                return "                                        
                <a href='$this->segment/$this->folder/edit-gallery/$row->id' class='mr-3 mb-2 btn btn-sm btn-info' title='Edit'><i class='fa fa-edit w-4 h-4 mr-1'></i> แก้ไข </a>                                                 
                <a href='javascript:' class='mr-3 mb-2 btn btn-sm btn-danger' onclick='deleteItem($row->id)' title='Delete'><i class='far fa-trash-alt w-4 h-4 mr-1'></i> ลบ</a>
            ";
            })
            ->addColumn('created_at', function ($row) {
                $data = date('d/m/Y', strtotime('+543 Years', strtotime($row->created_at)));
                return $data;
            })
            ->rawColumns(['action','img','created_at'])
            ->make(true);
    } 

    public function add_gallery(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->add  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = AboutUsModel::find(1);
        $id = $data->id;
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder/1", 'name' => "ข้อมูลที่ตั้งบริษัท", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/add", 'name' => "เพิ่มข้อมูล", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.add-gallery", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'id' => $id,
        ]);
    }
     public function edit_gallery(Request $request, $id)
     {
         $menu_control = Helper::menu_active($this->menu_id);
         if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
         $data = AwardModel::find($id);
         $navs = [
             '0' => ['url' => "$this->segment/$this->folder/1", 'name' => "ข้อมูลที่ตั้งบริษัท", "last" => 0],
             '2' => ['url' => "$this->segment/$this->folder/edit-gallery/$id", 'name' => "แก้ไขข้อมูล", "last" => 1],
         ];
        
         return view("$this->prefix.pages.$this->folder.edit-gallery", [
             'prefix' => $this->prefix,
             'folder' => $this->folder,
             'segment' => $this->segment,
             'name_page' => $this->name_page,
             'row' => $data,
             'menus' => \App\Models\Backend\MenuModel::where(['status' => 'on', 'position' => 'main'])->get(),
             'navs' => $navs,
             'id' => $id,
         ]);
     }
     public function insert_gallery(Request $request, $id = null)
     {
         return $this->store($request, $id = null);
     }
     public function update_gallery(Request $request, $id)
     {
         return $this->store($request, $id);
     }
     public function store($request, $id = null)
     {
         try {
             DB::beginTransaction();
             if ($id == null) {
                 $data = new AwardModel();
                 $data->created_at = date('Y-m-d H:i:s');
                 $data->updated_at = date('Y-m-d H:i:s');
                 $data->save();
             } else {
                 $data = AwardModel::find($id);
                 $data->updated_at = date('Y-m-d H:i:s');
             }
             $image = $request->image;
             $allow = ['png', 'jpeg', 'jpg'];
             if ($image) {
                 if ($data->img != null) {
                     Storage::disk('public')->delete($data->img);
                 }
                 $lg = Image::make($image->getRealPath());
                 $ext = explode("/", $lg->mime());
                 $lg->resize(281, 381)->stream();
                 
                 $new = 'upload/award/award' . date('dmY-Hism') . '.' . $ext[1];
                 if (in_array($ext[1], $allow)) {
                     $store = Storage::disk('public')->put($new, $lg);
                     $data->img = $new;
                     $data->save();
                 }
             }
             if ($data->save()) {
                 DB::commit();
                 return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder/1")]);
             } else {
                DB::rollback();
                return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder/1")]);
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
     public function destroy_gallery($id)
     {
         $datas = AwardModel::find($id);
         if (@$datas) {
             $query = AwardModel::destroy($datas->id);
         }
         if (@$query) {
             return response()->json(true);
         } else {
             return response()->json(false);
         }
     }
     //==== END: Gallery Data ====

     //==== BEGIN: Licens Data ====
    public function datatable_licens(Request $request,$id)
    {
        // dd($request);
        $like = $request->Like;
        $sTable = AboutLicensModel::orderby('id', 'desc')->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('img', function ($row) {
                $data = "<center> <img src='$row->img' style='width: 30%;'></center>";
                return $data;
            })
            ->addColumn('action', function ($row) {
                return "                                        
                <a href='$this->segment/$this->folder/edit-licens/$row->id' class='mr-3 mb-2 btn btn-sm btn-info' title='Edit'><i class='fa fa-edit w-4 h-4 mr-1'></i> แก้ไข </a>                                                 
                <a href='javascript:' class='mr-3 mb-2 btn btn-sm btn-danger' onclick='deleteItemLicens($row->id)' title='Delete'><i class='far fa-trash-alt w-4 h-4 mr-1'></i> ลบ</a>
            ";
            })
            ->addColumn('created_at', function ($row) {
                $data = date('d/m/Y', strtotime('+543 Years', strtotime($row->created_at)));
                return $data;
            })
            ->rawColumns(['action','img','created_at'])
            ->make(true);
    } 

    public function add_licens(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->add  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = AboutUsModel::find(1);
        $id = $data->id;
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder/1", 'name' => "ข้อมูลที่ตั้งบริษัท", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/add", 'name' => "เพิ่มข้อมูล", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.add-licens", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'id' => $id,
        ]);
    }
     public function edit_licens(Request $request, $id)
     {
         $menu_control = Helper::menu_active($this->menu_id);
         if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
         $data = AboutLicensModel::find($id);
         $navs = [
             '0' => ['url' => "$this->segment/$this->folder/1", 'name' => "ข้อมูลที่ตั้งบริษัท", "last" => 0],
             '2' => ['url' => "$this->segment/$this->folder/edit-licens/$id", 'name' => "แก้ไขข้อมูล", "last" => 1],
         ];
         return view("$this->prefix.pages.$this->folder.edit-licens", [
             'prefix' => $this->prefix,
             'folder' => $this->folder,
             'segment' => $this->segment,
             'name_page' => $this->name_page,
             'row' => $data,
             'navs' => $navs,
             'id' => $id,
         ]);
     }
     public function insert_licens(Request $request, $id = null)
     {
         return $this->store_licens($request, $id = null);
     }
     public function update_licens(Request $request, $id)
     {
         return $this->store_licens($request, $id);
     }
     public function store_licens($request, $id = null)
     {
         try {
             DB::beginTransaction();
             if ($id == null) {
                 $data = new AboutLicensModel();
                 $data->created_at = date('Y-m-d H:i:s');
                 $data->updated_at = date('Y-m-d H:i:s');
                 $data->save();
             } else {
                 $data = AboutLicensModel::find($id);
                 $data->updated_at = date('Y-m-d H:i:s');
             }
             $image = $request->image;
             $allow = ['png', 'jpeg', 'jpg','svg'];
             if ($image) {
                 if ($data->img != null) {
                     Storage::disk('public')->delete($data->img);
                 }
                 $lg = Image::make($image->getRealPath());
                 $ext = explode("/", $lg->mime());
                 $lg->resize(197, 92)->stream();
                 
                 $new = 'upload/award/licens' . date('dmY-Hism') . '.' . $ext[1];
                 if (in_array($ext[1], $allow)) {
                     $store = Storage::disk('public')->put($new, $lg);
                     $data->img = $new;
                     $data->save();
                 }
             }
             $data->title = $request->title;
             $data->detail = $request->detail;
             if ($data->save()) {
                 DB::commit();
                 return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder/1")]);
             } else {
                DB::rollback();
                return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder/1")]);
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
     public function destroy_licens($id)
     {
         $datas = AboutLicensModel::find($id);
         if (@$datas) {
             $query = AboutLicensModel::destroy($datas->id);
         }
         if (@$query) {
             return response()->json(true);
         } else {
             return response()->json(false);
         }
     }
     //==== END: Licens Data ====
}
