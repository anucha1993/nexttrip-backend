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

use App\Models\Backend\GroupGalleryModel;
use App\Models\Backend\GroupListModel;
use App\Models\Backend\GroupModel;
use App\Models\Backend\GroupContentModel;

class GroupController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'group';
    protected $folder = 'group';
    protected $menu_id = '28';
    protected $name_page = "ข้อมูลจัดกรุ๊ปทัวร์";

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
        $data = GroupContentModel::find($id);
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder/$id", 'name' => "ข้อมูลจัดกรุ๊ปทัวร์", "last" => 0],
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
                $data = GroupContentModel::find($id);
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
                    $lg->resize(596, 365)->stream();
                    
                    $new = 'upload/group/banner-left' . date('dmY-Hism') . '.' . $ext[1];
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
                    $lg->resize(596, 365)->stream();
                    
                    $new = 'upload/group/banner-right' . date('dmY-Hism') . '.' . $ext[1];
                    if (in_array($ext[1], $allow)) {
                        $store = Storage::disk('public')->put($new, $lg);
                        $data->img_right = $new;
                        $data->save();
                      
                    }
                }

                $data->detail = $request->detail;
                $data->text_center = $request->text_center;
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
    //==== BEGIN: List Data ====
    public function datatable_list(Request $request,$id)
    {
        // dd($request);
        $like = $request->Like;
        $sTable = GroupListModel::orderby('id', 'asc')->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('img', function ($row) {
                $data = "<center> <img src='$row->img' style='width: 30%;'></center>";
                return $data;
            })
            ->addColumn('action', function ($row) {
                return "                                        
                <a href='$this->segment/$this->folder/edit-list/$row->id' class='mr-3 mb-2 btn btn-sm btn-info' title='Edit'><i class='fa fa-edit w-4 h-4 mr-1'></i> แก้ไข </a>  ";
            })
            ->addColumn('created_at', function ($row) {
                $data = date('d/m/Y', strtotime('+543 Years', strtotime($row->created_at)));
                return $data;
            })
            ->rawColumns(['action','created_at','img'])
            ->make(true);
    } 

     public function edit_list(Request $request, $id)
     {
         $menu_control = Helper::menu_active($this->menu_id);
         if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
         $data = GroupListModel::find($id);
         $navs = [
             '0' => ['url' => "$this->segment/$this->folder/1", 'name' => "ข้อมูลจัดกรุ๊ปทัวร์", "last" => 0],
             '2' => ['url' => "$this->segment/$this->folder/edit-list/$id", 'name' => "แก้ไขข้อมูล", "last" => 1],
         ];
         return view("$this->prefix.pages.$this->folder.edit-list", [
             'prefix' => $this->prefix,
             'folder' => $this->folder,
             'segment' => $this->segment,
             'name_page' => $this->name_page,
             'row' => $data,
             'navs' => $navs,
             'id' => $id,
         ]);
     }
     
     public function update_list(Request $request, $id)
     {
         try {
             DB::beginTransaction();
                 $data = GroupListModel::find($id);
                 $data->updated_at = date('Y-m-d H:i:s');
                 $data->list = $request->list;
                 $data->detail = $request->detail;
                 if (!empty($request->file('image'))) {
                     //ลบรูปเก่าเพื่ออัพโหลดรูปใหม่แทน
                     if (!empty($data->img)) {
                         $path2 = 'public/upload/group/';
                         @unlink($path2.$data->img);
                     }
 
                     $path = 'upload/group';
                     $img = $request->file('image');
                     $ext = $img->getClientOriginalExtension();
                     if($ext == 'jpg'||$ext == 'jpeg'||$ext == 'png'){
                         $img_name = 'list' . date('YmdHis') . '.' . $img->getClientOriginalExtension();
                         $save_path = $img->move(public_path($path), $img_name);
                         $image = $img_name;
                         $data->img      = $path.'/'.$image;
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
    //==== END: List Data ====
    //==== BEGIN: Group Data ====
    public function datatable_group(Request $request,$id)
    {
        // dd($request);
        $like = $request->Like;
        $sTable = GroupModel::orderby('id', 'desc')->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('img', function ($row) {
                $data = "<center> <img src='$row->img' style='width: 30%;'></center>";
                return $data;
            })
            ->addColumn('action', function ($row) {
                $data = "";
                $menu_control = Helper::menu_active($this->menu_id);
                if($menu_control->edit == "on")
                {
                    $data.= " <a href='$this->segment/$this->folder/edit-group/$row->id' class='mr-3 mb-2 btn btn-sm btn-info' title='Edit'><i class='fa fa-edit w-4 h-4 mr-1'></i> แก้ไข </a>  ";  
                }
                if($menu_control->delete == "on")
                {
                    $data.= " <a href='javascript:' class='mr-3 mb-2 btn btn-sm btn-danger' onclick='deleteItem($row->id)' title='Delete'><i class='far fa-trash-alt w-4 h-4 mr-1'></i> ลบ </a>";  
                }
                return $data;
            })
            ->addColumn('created_at', function ($row) {
                $data = date('d/m/Y', strtotime('+543 Years', strtotime($row->created_at)));
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
            ->rawColumns(['action','img','created_at','status'])
            ->make(true);
    } 

    public function status($id = null)
    {
        $data = GroupModel::find($id);
        $data->status = ($data->status == 'off') ? 'on' : 'off';
        if ($data->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function add_group(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->add  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = GroupContentModel::find(1);
        $id = $data->id;
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder/1", 'name' => "ข้อมูลจัดกรุ๊ปทัวร์", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/add", 'name' => "เพิ่มข้อมูล", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.add-group", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'id' => $id,
        ]);
    }
     public function edit_group(Request $request, $id)
     {
         $menu_control = Helper::menu_active($this->menu_id);
         if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
         $data = GroupModel::find($id);
         $navs = [
             '0' => ['url' => "$this->segment/$this->folder/1", 'name' => "ข้อมูลจัดกรุ๊ปทัวร์", "last" => 0],
             '2' => ['url' => "$this->segment/$this->folder/edit-group/$id", 'name' => "แก้ไขข้อมูล", "last" => 1],
         ];
         return view("$this->prefix.pages.$this->folder.edit-group", [
             'prefix' => $this->prefix,
             'folder' => $this->folder,
             'segment' => $this->segment,
             'name_page' => $this->name_page,
             'row' => $data,
             'navs' => $navs,
             'id' => $id,
         ]);
     }
     public function insert_group(Request $request, $id = null)
     {
         return $this->store_group($request, $id = null);
     }
     public function update_group(Request $request, $id)
     {
         return $this->store_group($request, $id);
     }
     public function store_group($request, $id = null)
     {
         try {
             DB::beginTransaction();
             if ($id == null) {
                 $data = new GroupModel();
                 $data->created_at = date('Y-m-d H:i:s');
                 $data->updated_at = date('Y-m-d H:i:s');
                 $data->save();
                // gellery
                if (!empty($request->img)) {
                    foreach($request->img as $i => $img){
                            $path = 'upload/group';
                            $ext = $img->getClientOriginalExtension();
                            if($ext == 'jpg'||$ext == 'jpeg'||$ext == 'png'){
                                $gallery = new GroupGalleryModel();
                                $name_new = 'group-gallery-'.$i. date('YmdHis') . '.' . $img->getClientOriginalExtension();
                                $save_path = $img->move(public_path($path), $name_new);
                                $gallery->img             = $path.'/'.$name_new;
                                $gallery->group_id          = $data->id;
                                $gallery->save();
                            }
                    }
                }
             } else {
                 $data = GroupModel::find($id);
                 $data->updated_at = date('Y-m-d H:i:s');
                 // gellery
                if (!empty($request->img)) {
                    foreach($request->img as $i => $img){
                        $path = 'upload/group';
                        $ext = $img->getClientOriginalExtension();
                        if($ext == 'jpg'||$ext == 'jpeg'||$ext == 'png'){
                            $gallery = new GroupGalleryModel();
                            $name_new = 'group-gallery-'.$i. date('YmdHis') . '.' . $img->getClientOriginalExtension();
                            $save_path = $img->move(public_path($path), $name_new);
                            $gallery->img             = $path.'/'.$name_new;
                            $gallery->group_id          = $data->id;
                            $gallery->save();
                        }
                    }
                }
             }

             $image = $request->image;
             $allow = ['png', 'jpeg', 'jpg'];
             if ($image) {
                 if ($data->img != null) {
                     Storage::disk('public')->delete($data->img);
                 }
                 $lg = Image::make($image->getRealPath());
                 $ext = explode("/", $lg->mime());
                 $lg->resize(543, 293)->stream();
                 
                 $new = 'upload/group/group-cover' . date('dmY-Hism') . '.' . $ext[1];
                 if (in_array($ext[1], $allow)) {
                     $store = Storage::disk('public')->put($new, $lg);
                     $data->img = $new;
                     $data->save();
                   
                 }
             }
             
             $data->name = $request->name;

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
     public function destroy_group($id)
     {
         $datas = GroupModel::find($id);
         if (@$datas) {
             $query = GroupModel::destroy($datas->id);
             $gallery = GroupGalleryModel::where('group_id',$id)->delete();
         }
         if (@$query) {
             return response()->json(true);
         } else {
             return response()->json(false);
         }
     }
    //==== END: Group Data ====
    //==== BEGIN: Gallery Data ====
    public function datatable_gallery(Request $request,$id)
    {
        // dd($request);
        $like = $request->Like;
        $sTable = GroupGalleryModel::orderby('id', 'desc')->get();
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
        $data = GroupGalleryModel::find(1);
        $id = $data->id;
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder/1", 'name' => "ข้อมูลจัดกรุ๊ปทัวร์", "last" => 0],
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
         $data = GroupGalleryModel::find($id);
         $gid = $data->group_id;
         $navs = [
             '0' => ['url' => "$this->segment/$this->folder/1", 'name' => "ข้อมูลจัดกรุ๊ปทัวร์", "last" => 0],
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
             'id' => $gid,
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
                 $data = new GroupGalleryModel();
                 $data->created_at = date('Y-m-d H:i:s');
                 $data->updated_at = date('Y-m-d H:i:s');
                 $data->save();
                 if (!empty($request->image)) {
                     $path = 'upload/group';
                     $img = $request->file('image');
                     $ext = $img->getClientOriginalExtension();
                     if($ext == 'jpg'||$ext == 'jpeg'||$ext == 'png'){
                         $name_new = 'group-gallery' . date('YmdHis') . '.' . $img->getClientOriginalExtension();
                         $save_path = $img->move(public_path($path), $name_new);
                         $data->img             = $path.'/'.$name_new;
                         $data->save();
                     }
                 } 
             } else {
                 $data = GroupGalleryModel::find($id);
                 $data->updated_at = date('Y-m-d H:i:s');
                 if (!empty($request->file('image'))) {
                     //ลบรูปเก่าเพื่ออัพโหลดรูปใหม่แทน
                     if (!empty($data->img)) {
                         $path2 = 'public/upload/group/';
                         @unlink($path2.$data->img);
                     }
 
                     $path = 'upload/group';
                     $img = $request->file('image');
                     $ext = $img->getClientOriginalExtension();
                     if($ext == 'jpg'||$ext == 'jpeg'||$ext == 'png'){
                         $img_name = 'group-gallery' . date('YmdHis') . '.' . $img->getClientOriginalExtension();
                         $save_path = $img->move(public_path($path), $img_name);
                         $image = $img_name;
                         $data->img      = $path.'/'.$image;
                         $data->save();
                     }
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
         $datas = GroupGalleryModel::find($id);
         if (@$datas) {
             $query = GroupGalleryModel::destroy($datas->id);
         }
         if (@$query) {
             return response()->json(true);
         } else {
             return response()->json(false);
         }
     }
     //==== END: Gallery Data ====

     
}
