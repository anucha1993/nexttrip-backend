<?php

namespace App\Http\Controllers\Webpanel;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Functions\MenuControl;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Webpanel\LogsController;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use App\Models\Backend\LandmassModel;
use App\Models\Backend\CountryModel;

class CountryController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'country';
    protected $folder = 'country';
    protected $menu_id = '25';
    protected $name_page = "ข้อมูลประเทศ";

    public function auth_menu()
    {
        return view("$this->prefix.alert.alert",[
            'url'=> "webpanel",
            'title' => "เกิดข้อผิดพลาด",
            'text' => "คุณไม่ได้รับอนุญาตให้ใช้เมนูนี้! ",
            'icon' => 'error'
        ]); 
    }

    public function items($parameters)
    {
        $search = Arr::get($parameters, 'search');
        $sort = Arr::get($parameters, 'sort', 'desc');
        $paginate = Arr::get($parameters, 'total', 15);
        $query = new CountryModel;
        if ($search) {
            $query = $query->where('name', 'LIKE', '%' . trim($search) . '%');
        }
        $query = $query->orderBy('id', $sort);
        $results = $query->paginate($paginate);
        return $results;
    }

    public function index(Request $request)
    {
        $items = $this->items($request->all());
        $items->pages = new \stdClass();
        $items->pages->start = ($items->perPage() * $items->currentPage()) - $items->perPage();

        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}

        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => $this->name_page, "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.index", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'items' => $items,
            'menu_control' => $menu_control,
            'landmass' => LandmassModel::whereNull('deleted_at')->get(),
        ]);
    }

    public function datatable(Request $request){
			
        $like = $request->Like;

        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}

        $sTable = CountryModel::orderby('id','desc')
            ->when($like, function ($query) use ($like) {
                $query->where(function ($query) use ($like) {
                    if (@$like['search_name'] != "") {
                        $query->where('country_name_th', 'like', '%' . $like['search_name'] . '%');
                        $query->orWhere('country_name_en', 'like', '%' . $like['search_name'] . '%');
                    }
                });
                if (@$like['search_landmass'] != "") {
                    $query->where('landmass_id', $like['search_landmass']);
                }
                if (@$like['search_status'] != "") {
                    $query->where('status', $like['search_status']);
                }
            })
            ->whereNull('deleted_at');
        
        $sQuery = DataTables::of($sTable);
        return $sQuery
        ->addIndexColumn()
        ->editColumn('status', function ($row) {
            $status = "";
            if($row->status == "on")
            {
                $status = "checked";
            }
            $data = "<div class='form-switch w-full sm:w-auto sm:ml-auto mt-3 sm:mt-0'>
                        <input id='status_change_$row->id' data-id='$row->id' onclick='status($row->id);' class='show-code form-check-input mr-0 ml-3' type='checkbox' $status>
                    </div>";
            return $data;
        })
        ->editColumn('landmass_id', function ($row) {
            $landmass = LandmassModel::find($row->landmass_id);
            if($landmass){
                $data = $landmass->landmass_name;
            }else{
                $data = "-";
            }
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
        ->rawColumns(['status','updated_at','action'])
        ->make(true);
    }

    public function add(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}

        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => $this->name_page, "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/add", 'name' => "เพิ่ม", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.add", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'landmass' => LandmassModel::whereNull('deleted_at')->get(),
        ]);
    }

    public function edit(Request $request,$id=null)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}

        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => $this->name_page, "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/edit/$id", 'name' => "แก้ไข", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.edit", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'row' => CountryModel::find($id),
            'landmass' => LandmassModel::whereNull('deleted_at')->get(),
        ]);
    }

    //==== Function Insert Update Delete Status Sort & Others ====
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
                $data = new CountryModel();
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');
                if($request->landmass_id && $request->country_name_th){
                    $check = CountryModel::where(['landmass_id'=>$request->landmass_id,'country_name_th'=>$request->country_name_th])->whereNull('deleted_at')->first();
                    if($check){
                        return view("$this->prefix.alert.alert", ['url' => url("$this->segment/$this->folder/add"),'title'=>'ไม่สามารถดำเนินการได้','text'=>'เนื่องจากมีรายการนี้ในระบบแล้ว!!','icon'=>'error']);
                    }
                }
               
            } else {
                $data = CountryModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s');
            }
          
            $data->landmass_id = $request->landmass_id;
            $data->country_name_th = $request->country_name_th;
            $data->country_name_en = $request->country_name_en;
            $data->iso2 = $request->iso2;
            $data->iso3 = $request->iso3;
            $data->description = $request->description;
            $data->slug = Str::slug($request->country_name_en);
            $data->banner_detail = $request->banner_detail;
            $img_icon = $request->img_icon;
            $img_banner = $request->img_banner;
            $banner = $request->banner;
            $allow = ['png', 'jpeg', 'jpg','svg','webp'];
            if ($img_icon) {
                if ($data->img_icon != null) {
                    Storage::disk('public')->delete($data->img_icon);
                }
                $lg = Image::make($img_icon->getRealPath());
                $ext = explode("/", $lg->mime());
                $lg->resize(150, 100)->stream();
                $new = 'upload/country/image_icon' . date('dmY-Hism') . '.' . $ext[1];
                if (in_array($ext[1], $allow)) {
                    $store = Storage::disk('public')->put($new, $lg);
                    $data->img_icon = $new;
                }
            }

            if ($img_banner) {
                if ($data->img_banner != null) {
                    Storage::disk('public')->delete($data->img_banner);
                }
                $lg = Image::make($img_banner->getRealPath());
                $ext = explode("/", $lg->mime());
               $lg->resize(1200, 900)->stream();
                $new = 'upload/country/image_banner' . date('dmY-Hism') . '.' . $ext[1];
                if (in_array($ext[1], $allow)) {
                    $store = Storage::disk('public')->put($new, $lg);
                    $data->img_banner = $new;
                }
            }

            if ($banner) {
                if ($data->banner != null) {
                    Storage::disk('public')->delete($data->banner);
                }
                $lg = Image::make($banner->getRealPath());
                $ext = explode("/", $lg->mime());
                $lg->resize(1920, 400)->stream();
                $new = 'upload/country/banner' . date('dmY-Hism') . '.' . $ext[1];
                if (in_array($ext[1], $allow)) {
                    $store = Storage::disk('public')->put($new, $lg);
                    $data->banner = $new;
                }
            }
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

    public function status($id = null)
    {
        $data = CountryModel::find($id);
        $data->status = ($data->status == 'off') ? 'on' : 'off';
        if ($data->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function destroy(Request $request)
    {
        $datas = CountryModel::find($request->id);
        if (@$datas) {
            foreach ($datas as $data) {
                Storage::disk('public')->delete($data->img_icon);
                Storage::disk('public')->delete($data->img_banner);
                $data->deleted_at = date('Y-m-d H:i:s'); // soft delete
                if($data->save()){
                    return response()->json(true);
                } else {
                    return response()->json(false);
                }
            }
        } else {
            return response()->json(false);
        }
    }

}
