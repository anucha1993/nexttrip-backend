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

use App\Models\Backend\CalendarModel;
use App\Models\Backend\StatusSlideModel;

class CalendarController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'calendar';
    protected $folder = 'calendar';
    protected $menu_id = '42';
    protected $name_page = "ปฏิทินวันหยุด";

    public function auth_menu()
    {
        return view("$this->prefix.alert.alert",[
            'url'=> "webpanel",
            'title' => "เกิดข้อผิดพลาด",
            'text' => "คุณไม่ได้รับอนุญาตให้ใช้เมนูนี้! ",
            'icon' => 'error'
        ]); 
    }
    public function calendar(Request $request){
        if($request->month){ 
            $now = $request->month;
            $y = $request->year - 543;
            $time = date("$y-$now-01");
            if($request->type=='a'){
                $month = date('Y-m-d',strtotime("+1 month",strtotime($time)));
            }else{
                $month = date('Y-m-d',strtotime("-1 month",strtotime($time)));
               
            }
            $year = date('Y',strtotime($month));
            $now = date('m',strtotime($month));
            $n = date('n',strtotime($month));
            $day = date('t',strtotime($month)); 
            $round = date('N',strtotime($month));
        }else{
            // dd($request->month);
            $year = date('Y');
            $now = date('m');
            $n = date('n');
            $month = date('Y-m-01');
            $day = date('t'); 
            $round = date('N',strtotime($month));
        }

        $date = CalendarModel::whereMonth('start_date',$now)->whereYear('start_date',$year)->get();
        // dd($date);
        return view("$this->prefix.pages.$this->folder.calendar",[
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'n' => $n,
            'now' => $now,
            'day' => $day,
            'round' => $round,
            'year' => $year+543,
            'date' => $date,
        ]);
    }
    public function index(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $navs = [
            '1' => ['url' => "$this->segment", 'name' => "หน้าหลัก", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder", 'name' => "ปฏิทินวันหยุด", "last" => 1],
        ];
        if($request->month){ 
            $now = $request->month;
            $y = $request->year - 543;
            $time = date("$y-$now-01");
            if($request->type=='a'){
                $month = date('Y-m-d',strtotime("+1 month",strtotime($time)));
            }else{
                $month = date('Y-m-d',strtotime("-1 month",strtotime($time)));
            }
            $year = date('Y',strtotime($month));
            $now = date('m',strtotime($month));
            $nm = date('n',strtotime($month));
            $day = date('t',strtotime($month)); 
            $round = date('N',strtotime($month));
        }else{
            $year = date('Y');
            $now = date('m');
            $nm = date('n');
            $month = date('Y-m-01');
            $day = date('t'); 
            $round = date('N',strtotime($month));

        }
        $data = StatusSlideModel::find(1);
        return view("$this->prefix.pages.$this->folder.index", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'menu_control' => $menu_control,
            'nm' => $nm,
            'now' => $now,
            'day' => $day,
            'round' => $round,
            'year' => $year+543,
            'row' => $data,
        ]);
    }
    public function datatable(Request $request)
    {
        $like = $request->Like;
        $sTable = CalendarModel::where('deleted_at',null)->orderby('id', 'desc') ->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('img', function ($row) {
                $data = "<center> <img src='$row->img_cover' style='width: 20%;'></center>";
                return $data;
            })
            ->addColumn('date', function ($row) {
                $data = date('d/m/Y', strtotime('+543 Years', strtotime($row->start_date))).' - '.date('d/m/Y', strtotime('+543 Years', strtotime($row->end_date)));
                return $data;
            })
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
            ->rawColumns(['status','created_at', 'action','img'])
            ->make(true);
    }
    public function status_slide(Request $request)
    {
        try {
            $data = StatusSlideModel::find(1);
            $data->status_calendar = $request->slide;
            $data->time_calendar = $request->time;
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
            '0' => ['url' => "$this->segment", 'name' => "หน้าหลัก", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "ปฏิทินวันหยุด", "last" => 0],
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
        $data = CalendarModel::find($id);
        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "หน้าหลัก", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "ปฏิทินวันหยุด", "last" => 0],
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
                $data = new CalendarModel();
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');
                $data->save();

            } else {
                $data = CalendarModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s');
                
            }
            $image  = $request->image;
            $image_cover = $request->image_cover;
            $allow = ['png', 'jpeg', 'jpg'];
                if ($image) {
                    if ($data->img != null) {
                        Storage::disk('public')->delete($data->img);
                    }
                    $lg = Image::make($image->getRealPath());
                    $ext = explode("/", $lg->mime());
                    $lg->resize(1920, 537)->stream();
                    
                    $new = 'upload/calendar/banner' . date('dmY-Hism') . '.' . $ext[1];
                    if (in_array($ext[1], $allow)) {
                        $store = Storage::disk('public')->put($new, $lg);
                        $data->img_banner = $new;
                        $data->save();
                    
                    }
                  
                }
                // Cover
                if ($image_cover) {
                    if ($data->img_cover != null) {
                        Storage::disk('public')->delete($data->img_cover);
                    }
                    $lg = Image::make($image_cover->getRealPath());
                    $ext = explode("/", $lg->mime());
                    $lg->resize(392, 224)->stream();
                    
                    $new = 'upload/calendar/cover' . date('dmY-Hism') . '.' . $ext[1];
                    if (in_array($ext[1], $allow)) {
                        $store = Storage::disk('public')->put($new, $lg);
                        $data->img_cover = $new;
                        $data->save();
                    }
                }
            $data->description = $request->description;
            $data->holiday = $request->holiday;
            $data->detail = $request->detail;
            $data->start_date = $request->start_date;
            $data->end_date = $request->end_date;
            
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
        $datas = CalendarModel::find($id);
        if (@$datas) {
            $datas->deleted_at = date('Y-m-d H:i:s');
        }

        if (@$datas->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function status($id = null)
    {
        $data = CalendarModel::find($id);
        $data->status = ($data->status == 'off') ? 'on' : 'off';
        if ($data->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
}
