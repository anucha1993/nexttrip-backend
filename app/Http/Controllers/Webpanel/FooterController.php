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

use App\Models\Backend\FooterModel;
use App\Models\Backend\FooterListModel;

class FooterController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'footer';
    protected $folder = 'footer';
    protected $menu_id = '36';
    protected $name_page = "Footer";

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
        $data = FooterModel::find($id);
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder/$id", 'name' => "Footer", "last" => 0],
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
                $data = FooterModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s');
                
                $image = $request->image;
                $footer = $request->footer;
                $allow = ['png', 'jpeg', 'jpg', 'webp'];
                if ($image) {
                    if ($data->img != null) {
                        Storage::disk('public')->delete($data->img);
                    }
                    $lg = Image::make($image->getRealPath());
                    $ext = explode("/", $lg->mime());
                    $lg->resize(1920, 678)->stream();
                    
                    $new = 'upload/footer/image' . date('dmY-Hism') . '.' . $ext[1];
                    if (in_array($ext[1], $allow)) {
                        $store = Storage::disk('public')->put($new, $lg);
                        $data->img = $new;
                        $data->save();
                      
                    }
                }
                // image footer
                if ($footer) {
                    if ($data->img_footer != null) {
                        Storage::disk('public')->delete($data->img_footer);
                    }
                    $lg = Image::make($footer->getRealPath());
                    $ext = explode("/", $lg->mime());
                    $lg->resize(1110, 409)->stream();
                    
                    $new = 'upload/footer/image-Footer' . date('dmY-Hism') . '.' . $ext[1];
                    if (in_array($ext[1], $allow)) {
                        $store = Storage::disk('public')->put($new, $lg);
                        $data->img_footer = $new;
                        $data->save();
                      
                    }
                }
                $data->title = $request->title;
                $data->detail = $request->detail;
                $data->status = $request->status;

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
        $sTable = FooterListModel::orderby('id', 'asc')->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('img', function ($row) {
                $data = "<center> <img src='$row->img' style='width: 10%;'></center>";
                return $data;
            })
            ->addColumn('action', function ($row) {
                return "                                        
                <a href='$this->segment/$this->folder/edit-list/$row->id' class='mr-3 mb-2 btn btn-sm btn-info' title='Edit'><i class='fa fa-edit w-4 h-4 mr-1'></i> แก้ไข </a>";
            })
            ->addColumn('created_at', function ($row) {
                $data = date('d/m/Y', strtotime('+543 Years', strtotime($row->created_at)));
                return $data;
            })
            ->rawColumns(['action','img','created_at'])
            ->make(true);
    } 

     public function edit_list(Request $request, $id)
     {
         $menu_control = Helper::menu_active($this->menu_id);
         if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
         $data = FooterListModel::find($id);
         $navs = [
             '0' => ['url' => "$this->segment/$this->folder/1", 'name' => "Footer", "last" => 0],
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
             if ($id == null) {
                 $data = new FooterListModel();
                 $data->created_at = date('Y-m-d H:i:s');
                 $data->updated_at = date('Y-m-d H:i:s');
                 $data->save();
                 
             } else {
                 $data = FooterListModel::find($id);
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
                 $lg->resize(48, 48)->stream();
                 
                 $new = 'upload/footer/list' . date('dmY-Hism') . '.' . $ext[1];
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
     //==== END: list Data ====
}
