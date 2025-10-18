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

use App\Models\Backend\PackageModel;
use App\Models\Backend\CountryModel;

class PackageController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'package';
    protected $folder = 'package';
    protected $menu_id = '43';
    protected $name_page = "แพ็คเกจทัวร์";

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
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "แพ็คเกจทัวร์", "last" => 1],
        ];
        $country = CountryModel::where('status','on')->whereNull('deleted_at')->get();
        return view("$this->prefix.pages.$this->folder.index", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'menu_control' => $menu_control,
            'country'  => $country,
        ]);
    }
    public function datatable(Request $request)
    {
        $like = $request->Like;
        $sTable = PackageModel::orderby('id', 'desc')
        ->when($like, function ($query) use ($like) {
            if (@$like['name'] != "") {
                $query->where('package', 'like', '%' . $like['name'] . '%');
            }
            if(@$like['country'] != ""){
                $query->where('country_id', 'like', '%"' . $like['country'] . '"%');
            }
        })
        ->whereNull('deleted_at')
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
            ->addColumn('name', function ($row) {
                $name = explode('ราคา', $row->package);
                return $name[0];
            })
            ->addColumn('img', function ($row) {
              
                $data = "<center> <img src='$row->img' style='width: 30%;'></center>";
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
            ->addColumn('price', function ($row) {
                $data = $row->price.' บาท';
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
            ->rawColumns(['created_at', 'action','status','price','country','img','name'])
            ->make(true);
    }
    public function status($id = null)
    {
        $data = PackageModel::find($id);
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
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "แพ็คเกจทัวร์", "last" => 1],
            '2' => ['url' => "$this->segment/$this->folder/add", 'name' => "เพิ่มข้อมูล", "last" => 1],
        ];
        $country = CountryModel::where('status','on')->whereNull('deleted_at')->get();
        return view("$this->prefix.pages.$this->folder.add", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'country' => $country,
        ]);
    }
    public function edit(Request $request, $id)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = PackageModel::find($id);
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "แพ็คเกจทัวร์", "last" => 1],
            '2' => ['url' => "$this->segment/$this->folder/edit/$id", 'name' => "แก้ไขข้อมูล", "last" => 1],
        ];
        $country = CountryModel::where('status','on')->whereNull('deleted_at')->get();
        return view("$this->prefix.pages.$this->folder.edit", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'row' => $data,
            'navs' => $navs,
            'id' => $id,
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
                $data = new PackageModel();
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');
                $data->save();

                
            } else {
                $data = PackageModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s');

            }
            $image = $request->image;
            $allow = ['png', 'jpeg', 'jpg'];
            $allow_pdf = ['pdf'];
            if ($image) {
                if ($data->img != null) {
                    Storage::disk('public')->delete($data->img);
                }
                $lg = Image::make($image->getRealPath());
                $ext = explode("/", $lg->mime());
                $lg->resize(600, 600)->stream();
                
                $new = 'upload/package/image' . date('dmY-Hism') . '.' . $ext[1];
                if (in_array($ext[1], $allow)) {
                    $store = Storage::disk('public')->put($new, $lg);
                    $data->img = $new;
                    $data->save();
                  
                }
            }
            if ($request->hasFile('pdf_file')) {
                // รับไฟล์ PDF
                $pdfFile = $request->file('pdf_file');
    
                // สร้างชื่อไฟล์ใหม่เพื่อบันทึก
                $path = 'upload/package/';
                $ext = $pdfFile->getClientOriginalExtension();
                $filename = 'pdf' . date('dmY-Hism') . '.' . $ext;

                // บันทึกไฟล์ PDF ลงในเซิร์ฟเวอร์
                if (in_array($ext, $allow_pdf)) {
                    $pdfFile->move(public_path($path), $filename);
                    $data->pdf = $path . $filename;
                    $data->save();
                }
            }
            $data->country_id = $request->country_id?json_encode($request->country_id):'[]'; 
            $data->package = $request->package;
            $data->price = $request->price;
            $data->detail = $request->detail;
            $data->not_include = $request->not_include;
            $data->include = $request->include;

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
        $datas = PackageModel::find($id);
        if (@$datas) {
            $datas->deleted_at = date('Y-m-d H:i:s'); // soft delete
            if($datas->save()){
                return response()->json(true);
            } else {
                return response()->json(false);
            }
        } else {
            return response()->json(false);
        }
    }
    // deleted file
    public function destroy_pdf($id)
    {
        $datas = PackageModel::find($id);
        if (@$datas) {
            $query = PackageModel::where('id',$id)->update(['pdf'=> null]);
            Storage::disk('public')->delete($datas->pdf);
        }
        if (@$query) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
}
