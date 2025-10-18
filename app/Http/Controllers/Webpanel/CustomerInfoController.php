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

use App\Models\Backend\CustomerInfoModel;
use App\Models\Backend\GalleryCustomerModel;
use App\Models\Backend\TypeCustomerModel;
use App\Models\Backend\TagContentModel;
use App\Models\Backend\CountryModel;
use App\Models\Backend\StatusSlideModel;


class CustomerInfoController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'customer-info';
    protected $folder = 'customer-info';
    protected $menu_id = '7';
    protected $name_page = "ข้อมูลลูกค้าของเรา";

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
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลลูกค้าของเรา", "last" => 1],
        ];
        $country = CountryModel::where('status','on')->whereNull('deleted_at')->get();
        $type  = TypeCustomerModel::get();
        $data = StatusSlideModel::find(1);
        return view("$this->prefix.pages.$this->folder.index", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'menu_control' => $menu_control,
            'country' => $country,
            'type' => $type,
            'row' => $data,
        ]);
    }
    public function datatable(Request $request)
    {
        $like = $request->Like;
        $sTable = CustomerInfoModel::orderby('id', 'desc')
        ->when($like, function ($query) use ($like) {
            if (@$like['name'] != "") {
                $query->where('company', 'like', '%' . $like['name'] . '%');
            }
            if(@$like['country'] != ""){
                $query->where('country_id', 'like', '%"' . $like['country'] . '"%');
            }
            if(@$like['type_customer'] != ""){
                $query->where('type_id', 'like', '%' . $like['type_customer'] . '%');
            }
        })
        ->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                $data = date('d/m/Y', strtotime('+543 Years', strtotime($row->created_at)));
                return $data;
            })
            ->addColumn('action', function ($row) {
                $data = "";
                $menu_control = Helper::menu_active($this->menu_id);
                if($menu_control->edit == "on")
                {
                    $data.= " <a href='$this->segment/$this->folder/$row->id' class='mr-3 mb-2 btn btn-sm btn-info' title='Edit'><i class='fa fa-edit w-4 h-4 mr-1'></i> แก้ไข </a>  ";  
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
            ->addColumn('country', function ($row) { 
                $country_select = json_decode($row->country_id,true);
                $country_sel = CountryModel::whereIn('id',$country_select)->get();
                $data = "";
                    foreach($country_sel as $co){
                        $data .= "<p class='mb-1'> ".$co->country_name_th."</p>";
                    }
                return $data;
               
            })
            ->addColumn('type', function ($row) {
                $type = TypeCustomerModel::find($row->type_id);
                $data = $type->type;
                return $data;
            })
            ->rawColumns(['created_at', 'action','status','country','type'])
            ->make(true);
    }
    public function status($id = null)
    {
        $data = CustomerInfoModel::find($id);
        $data->status = ($data->status == 'off') ? 'on' : 'off';
        if ($data->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
    public function status_slide(Request $request)
    {
        try {
            $data = StatusSlideModel::find(1);
            $data->status_cus = $request->slide;
            $data->time_cus = $request->time;
            $data->created_at = date('Y-m-d H:i:s');
            $data->updated_at = date('Y-m-d H:i:s');
            $data->save();
            return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder/")]);
        } catch (\Throwable $th) {
           
            return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder/")]);
        }
    }
    public function add(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->add  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลลูกค้าของเรา", "last" => 1],
            '2' => ['url' => "$this->segment/$this->folder/add", 'name' => "เพิ่มข้อมูล", "last" => 1],
        ];
        $type  = TypeCustomerModel::get();
        $tag  = TagContentModel::whereNull('deleted_at')->get();
        $country = CountryModel::where('status','on')->whereNull('deleted_at')->get();
        return view("$this->prefix.pages.$this->folder.add", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'type' => $type,
            'tag'  => $tag,
            'data_tag'  => json_encode($tag),
            'country' => $country,
        ]);
    }
    public function edit(Request $request, $id)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = CustomerInfoModel::find($id);
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลลูกค้าของเรา", "last" => 1],
            '2' => ['url' => "$this->segment/$this->folder/$id", 'name' => "แก้ไขข้อมูล", "last" => 1],
        ];
        $type  = TypeCustomerModel::get();
        $tag  = TagContentModel::whereNull('deleted_at')->get();
        $country = CountryModel::where('status','on')->whereNull('deleted_at')->get();
        return view("$this->prefix.pages.$this->folder.edit", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'row' => $data,
            'navs' => $navs,
            'id' => $id,
            'type' => $type,
            'tag'  => $tag,
            't_select'  => json_encode(TagContentModel::whereIn('id',json_decode($data->tag,true))->get()),
            'data_tag'  => json_encode($tag),
            'country' => $country,
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
                $data = new CustomerInfoModel();
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');
                $data->save();

                // //logo
                // if (!empty($request->logo)) {
                //     $path = 'upload/customer';
                //     $img = $request->file('logo');
                //     $ext = $img->getClientOriginalExtension();
                //     if($ext == 'jpg'||$ext == 'jpeg'||$ext == 'png'||$ext = 'svg'){
                //         $name_new = 'logo-customer' . date('YmdHis') . '.' . $img->getClientOriginalExtension();
                //         $save_path = $img->move(public_path($path), $name_new);
                //         $data->logo             = $path.'/'.$name_new;
                //         $data->save();
                //     }
                    
                // } 
                // //image cover
                // if (!empty($request->image)) {
                //     $path = 'upload/customer';
                //     $img = $request->file('image');
                //     $ext = $img->getClientOriginalExtension();
                //     if($ext == 'jpg'||$ext == 'jpeg'||$ext == 'png'){
                //         $name_new = 'customer' . date('YmdHis') . '.' . $img->getClientOriginalExtension();
                //         $save_path = $img->move(public_path($path), $name_new);
                //         $data->img_cover             = $path.'/'.$name_new;
                //         $data->save();
                //     }
                    
                // } 
                // // gellery
                // if (!empty($request->img)) {
                //     foreach($request->img as $i => $img){
                //             $path = 'upload/customer';
                //             $ext = $img->getClientOriginalExtension();
                //             if($ext == 'jpg'||$ext == 'jpeg'||$ext == 'png'){
                //                 $gallery = new GalleryCustomerModel();
                //                 $name_new = 'customer-gallery-'.$i. date('YmdHis') . '.' . $img->getClientOriginalExtension();
                //                 $save_path = $img->move(public_path($path), $name_new);
                //                 $gallery->img             = $path.'/'.$name_new;
                //                 $gallery->cus_id          = $data->id;
                //                 $gallery->save();
                //             }
                //     }
                // }
            } else {
                $data = CustomerInfoModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s');
                // // logo
                // if (!empty($request->file('logo'))) {
                //     //ลบรูปเก่าเพื่ออัพโหลดรูปใหม่แทน
                //     if (!empty($data->logo)) {
                //         $path2 = 'public/upload/customer/';
                //         @unlink($path2.$data->logo);
                //     }

                //     $path = 'upload/customer';
                //     $img = $request->file('logo');
                //     $ext = $img->getClientOriginalExtension();
                //     if($ext == 'jpg'||$ext == 'jpeg'||$ext == 'png'||$ext = 'svg'){
                //         $img_name = 'logo-customer' . date('YmdHis') . '.' . $img->getClientOriginalExtension();
                //         $save_path = $img->move(public_path($path), $img_name);
                //         $image = $img_name;
                //         $data->logo      = $path.'/'.$image;
                //         $data->save();
                //     }
                // }
                // //image cover
                // if (!empty($request->file('image'))) {
                //     //ลบรูปเก่าเพื่ออัพโหลดรูปใหม่แทน
                //     if (!empty($data->img_cover)) {
                //         $path2 = 'public/upload/customer/';
                //         @unlink($path2.$data->img_cover);
                //     }

                //     $path = 'upload/customer';
                //     $img = $request->file('image');
                //     $ext = $img->getClientOriginalExtension();
                //     if($ext == 'jpg'||$ext == 'jpeg'||$ext == 'png'){
                //         $img_name = 'customer' . date('YmdHis') . '.' . $img->getClientOriginalExtension();
                //         $save_path = $img->move(public_path($path), $img_name);
                //         $image = $img_name;
                //         $data->img_cover      = $path.'/'.$image;
                //         $data->save();
                //     }
                // }
                // // gellery
                // if (!empty($request->file('img'))) {
                //     //ลบรูปเก่าเพื่ออัพโหลดรูปใหม่แทน
                //     if (!empty($data->img)) {
                //         $path2 = 'public/upload/customer/';
                //         @unlink($path2.$data->img);
                //     }

                //     foreach($request->img as $i => $img){
                //         $path = 'upload/customer';
                //         $ext = $img->getClientOriginalExtension();
                //         if($ext == 'jpg'||$ext == 'jpeg'||$ext == 'png'){
                //             $gallery = new GalleryCustomerModel();
                //             $name_new = 'customer-gallery-'.$i. date('YmdHis') . '.' . $img->getClientOriginalExtension();
                //             $save_path = $img->move(public_path($path), $name_new);
                //             $gallery->img             = $path.'/'.$name_new;
                //             $gallery->cus_id          = $data->id;
                //             $gallery->save();
                //         }
                //     }
                // }
               
            }
            $image = $request->image;
            $logo = $request->logo;
            $gallery_img = $request->img;
            $allow = ['png', 'jpeg', 'jpg','svg'];
            if ($image) {
                if ($data->img_cover != null) {
                    Storage::disk('public')->delete($data->img_cover);
                }
                $lg = Image::make($image->getRealPath());
                $ext = explode("/", $lg->mime());
                $lg->resize(1116, 602)->stream();
                
                $new = 'upload/customer/image' . date('dmY-Hism') . '.' . $ext[1];
                if (in_array($ext[1], $allow)) {
                    $store = Storage::disk('public')->put($new, $lg);
                    $data->img_cover = $new;
                    $data->save();
                  
                }
                // dd($data);
            }
            if ($logo) {
                if ($data->logo != null) {
                    Storage::disk('public')->delete($data->logo);
                }
                $lg = Image::make($logo->getRealPath());
                $ext = explode("/", $lg->mime());
                // $lg->resize(129, 87)->stream();
                $lg->resize('100%','100%')->stream();
                $new = 'upload/customer/logo' . date('dmY-Hism') . '.' . $ext[1];
                if (in_array($ext[1], $allow)) {
                    $store = Storage::disk('public')->put($new, $lg);
                    $data->logo = $new;
                    $data->save();
                }
            }
            if ($gallery_img) {
                foreach($gallery_img as $i => $img){
                    // dd($gallery_img);
                    $imgs = Image::make($img->getRealPath());
                    $ext = explode("/", $imgs->mime());
                    // $imgs->resize(543, 293)->stream();
                    $imgs->resize('100%','100%')->stream();
                    $new = 'upload/customer/gallery-'.$i. date('dmY-Hism') . '.' . $ext[1];
                    if (in_array($ext[1], $allow)) {
                        $gallery = new GalleryCustomerModel();
                        $store = Storage::disk('public')->put($new, $imgs);
                        $gallery->img = $new;
                        $gallery->cus_id    = $data->id;
                        $gallery->save();
                    
                    }
                }
            //    dd($gallery);
            }
            $data->type_id = $request->type_id;
            $data->company = $request->company;
            $data->detail = $request->detail;
            $data->tag = $request->tag_id?json_encode($request->tag_id):'[]';
            $data->country_id = $request->country_id?json_encode($request->country_id):'[]'; 
           

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
        $datas = CustomerInfoModel::find($id);
        if (@$datas) {
            $query = CustomerInfoModel::destroy($datas->id);
            $gallery = GalleryCustomerModel::where('cus_id',$id)->delete();
        }

        if (@$query) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
     //==== BEGIN: Gallery Data ====
     public function datatable_gallery(Request $request,$id)
     {
         // dd($request);
         $like = $request->Like;
         $sTable = GalleryCustomerModel::where('cus_id',$id)->orderby('id', 'desc')->get();
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
     public function edit_gallery(Request $request, $id)
     {
         $menu_control = Helper::menu_active($this->menu_id);
         if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
         $data = GalleryCustomerModel::find($id);
         $cus_id = $data->cus_id;
         $name = CustomerInfoModel::find($cus_id);
         $navs = [
             '0' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลลูกค้าของเรา", "last" => 0],
             '1' => ['url' => "$this->segment/$this->folder/$cus_id", 'name' => "$name->company", "last" => 1],
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
             'cus_id'  => $cus_id,
             'id' => $id,
         ]);
     }
     public function update_gallery(Request $request, $id)
     {
         try {
             DB::beginTransaction();
             
                $data = GalleryCustomerModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s');
                 
                $image = $request->image;
                $allow = ['png', 'jpeg', 'jpg'];
                if ($image) {
                    if ($data->img != null) {
                        Storage::disk('public')->delete($data->img);
                    }
                    $lg = Image::make($image->getRealPath());
                    $ext = explode("/", $lg->mime());
                    $lg->resize(306, 165)->stream();
                    
                    $new = 'upload/customer/gallery-' . date('dmY-Hism') . '.' . $ext[1];
                    if (in_array($ext[1], $allow)) {
                        $store = Storage::disk('public')->put($new, $lg);
                        $data->img = $new;
                        $data->save();
                    
                    }
                }
             // dd($data);
             if ($data->save()) {
                 DB::commit();
                 return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder/".$data->cus_id)]);
             } else {
                 return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder/".$data->cus_id)]);
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
     public function destroy_gallery($id)
     {
         $datas = GalleryCustomerModel::find($id);
         if (@$datas) {
             $query = GalleryCustomerModel::destroy($datas->id);
         }
         if (@$query) {
             return response()->json(true);
         } else {
             return response()->json(false);
         }
     }
     //==== END: Gallery Data ====
}
