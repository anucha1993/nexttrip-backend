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

use App\Models\Backend\ContactModel;

class ContactUsController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'contact';
    protected $folder = 'contact';
    protected $menu_id = '18';
    protected $name_page = "ข้อมูลการติดต่อ";

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
        $data = ContactModel::find($id);
        $navs = [
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "ติดต่อเรา", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder/$id", 'name' => "ข้อมูลการติดต่อ", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/$id", 'name' => "แก้ไขข้อมูล", "last" => 1],
        ];
        // dd($detail);
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
    public function update(Request $request,$id)
    {
        try {
            // dd($request);
            DB::beginTransaction();
            $data = ContactModel::find($id);
            $data->updated_at = date('Y-m-d H:i:s');
            // img QR Line
            $image = $request->image;
            $allow = ['png', 'jpeg', 'jpg'];
                if ($image) {
                    if ($data->qr_code != null) {
                        Storage::disk('public')->delete($data->qr_code);
                    }
                    $lg = Image::make($image->getRealPath());
                    $ext = explode("/", $lg->mime());
                    $lg->resize(142, 142)->stream();
                    
                    $new = 'upload/contact/qr-code' . date('dmY-Hism') . '.' . $ext[1];
                    if (in_array($ext[1], $allow)) {
                        $store = Storage::disk('public')->put($new, $lg);
                        $data->qr_code = $new;
                        $data->save();
                      
                    }
            }
            //map
            if (!empty($request->file('map'))) {
                //ลบรูปเก่าเพื่ออัพโหลดรูปใหม่แทน
                if (!empty($data->map)) {
                    $path2 = 'public/upload/contact/';
                    @unlink($path2.$data->map);
                }

                $path = 'upload/contact';
                $img = $request->file('map');
                $ext = $img->getClientOriginalExtension();
                if($ext == 'jpg'||$ext == 'jpeg'||$ext == 'png'){
                    $img_name = 'map' . date('YmdHis') . '.' . $img->getClientOriginalExtension();
                    $save_path = $img->move(public_path($path), $img_name);
                    $image = $img_name;
                    $data->map      = $path.'/'.$image;
                    $data->save();
                }
            }
            $data->phone_front = $request->phone_front;
            $data->phone_group = $request->phone_group;
            $data->phone_problem = $request->phone_problem;
            $data->address = $request->address;
            $data->time = $request->time;
            $data->service_time = $request->service_time;
            $data->office = $request->office;
            $data->hotline = $request->hotline;
            $data->fax = $request->fax;
            $data->mail = $request->mail;
            $data->google_map = $request->google_map;
            $data->line_id = $request->line_id;
            $data->link_fb = $request->link_fb;
            $data->link_ig = $request->link_ig;
            $data->link_yt = $request->link_yt;
            $data->link_tiktok = $request->link_tiktok;
            // dd($data);
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
}
