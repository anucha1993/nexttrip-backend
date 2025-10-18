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
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\DB;

use App\Models\Backend\BookingFormModel;
use App\Models\Backend\BookingDetailModel;
use App\Models\Backend\TourModel;
use App\Models\Backend\TourPeriodModel;
use App\Models\Backend\CountryModel;
use App\Models\Backend\WholesaleModel;
use App\Models\Backend\ContactModel;
use App\Models\Backend\User;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class BookingFormController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'booking-form';
    protected $folder = 'booking-form';
    protected $menu_id = '35';
    protected $name_page = "ข้อมูลการจองทัวร์";

    public function auth_menu()
    {
        return view("$this->prefix.alert.alert",[
            'url'=> "webpanel",
            'title' => "เกิดข้อผิดพลาด",
            'text' => "คุณไม่ได้รับอนุญาตให้ใช้เมนูนี้! ",
            'icon' => 'error'
        ]); 
    }
    public function check_sale_id()
    {
        return view("$this->prefix.alert.alert",[
            'url'=> "webpanel/booking-form",
            'title' => "เกิดข้อผิดพลาด",
            'text' => "คุณไม่ได้รับอนุญาตให้ใช้หน้านี้! ",
            'icon' => 'error'
        ]); 
    }
    public function index(Request $request)
    {
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
            'menu_control' => $menu_control,
        ]);
    }
    public function datatable(Request $request)
    {
        $like = $request->Like;

        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}

        $user = User::find(Auth::Guard()->id());

        if($user->role == 1){
            $sTable = BookingFormModel::orderby('id', 'desc')
            ->when($like, function ($query) use ($like) {
                if (@$like['search_tap_name'] != "") {
                    if(@$like['search_tap_name'] == 1){
                        $query->whereIn('status', ['Booked','Waiting','Pay']);
                    }else{
                        $query->whereIn('status', ['Success','Cancel']);
                    }
                }
                $query->where(function ($query) use ($like) {
                    if (@$like['search_name'] != "") {
                        $query->where('code', 'like', '%' . $like['search_name'] . '%');
                        $query->orWhere('name', 'like', '%' . $like['search_name'] . '%');
                        $query->orWhere('surname', 'like', '%' . $like['search_name'] . '%');
                    }
                });
                if(@$like['search_type_date'] != ""){
                    if(@$like['search_type_date'] == 1){
                        if(@$like['search_start_date'] != "" && @$like['search_end_date'] != ""){
                            $query->where('start_date', '>=' , $like['search_start_date']);
                            $query->where('end_date', '<=' , $like['search_end_date']);
                        }else if(@$like['search_start_date'] != ""){
                            $query->where('start_date', '>=' , $like['search_start_date']);
                        }
                    }else{
                        if(@$like['search_start_date'] != "" && @$like['search_end_date'] != ""){
                            $query->wherebetween('created_at', [$like['search_start_date'], $like['search_end_date']]);
                        }else if(@$like['search_start_date'] != ""){
                            $query->where('created_at', '>=' , $like['search_start_date']);
                        }
                    }
                }
                if (@$like['search_status'] != "") {
                    $query->where('status', @$like['search_status']);
                }
                if (@$like['search_sale'] != "") {
                    $query->where('sale_id', @$like['search_sale']);
                }
            })
            ->whereNull('deleted_at');
        }else{
            $sTable = BookingFormModel::where('sale_id',$user->id)->orderby('id', 'desc')
            ->when($like, function ($query) use ($like) {
                if (@$like['search_tap_name'] != "") {
                    if(@$like['search_tap_name'] == 1){
                        $query->whereIn('status', ['Booked','Waiting','Pay']);
                    }else{
                        $query->whereIn('status', ['Success','Cancel']);
                    }
                }
                if (@$like['search_name'] != "") {
                    $query->where('code', 'like', '%' . $like['search_name'] . '%');
                    $query->where('name', 'like', '%' . $like['search_name'] . '%');
                    $query->orWhere('surname', 'like', '%' . $like['search_name'] . '%');
                }
                if(@$like['search_type_date'] != ""){
                    if(@$like['search_type_date'] == 1){
                        if(@$like['search_start_date'] != "" && @$like['search_end_date'] != ""){
                            $query->where('start_date', '>=' , $like['search_start_date']);
                            $query->where('end_date', '<=' , $like['search_end_date']);
                        }else if(@$like['search_start_date'] != ""){
                            $query->where('start_date', '>=' , $like['search_start_date']);
                        }
                    }else{
                        if(@$like['search_start_date'] != "" && @$like['search_end_date'] != ""){
                            $query->wherebetween('created_at', [$like['search_start_date'], $like['search_end_date']]);
                        }else if(@$like['search_start_date'] != ""){
                            $query->where('created_at', '>=' , $like['search_start_date']);
                        }
                    }
                }
                if (@$like['search_status'] != "") {
                    $query->where('status', @$like['search_status']);
                }
                if (@$like['search_sale'] != "") {
                    $query->where('sale_id', @$like['search_sale']);
                }
            })
            ->whereNull('deleted_at');
        }

        $sQuery = DataTables::of($sTable);
        return $sQuery
            ->addIndexColumn()
            ->editColumn('ref', function ($row) {
                $data = "<a href='$this->segment/$this->folder/view/$row->id'><b>".$row->code."</b></a>";
                // $data = "<a href='$this->segment/$this->folder/view/$row->id' style='text-decoration: underline; color:#0283df;'>".$row->code."</a>";
                return $data;
            })
            ->editColumn('name', function ($row) {
                $data = "<a href='$this->segment/$this->folder/view/$row->id'><b>".$row->name.' '.$row->surname."</b></a>";
                return $data;
            })
            ->editColumn('status', function ($row) {
                if($row->status == "Booked"){
                    $data = "<p style='color: blue;'>Booked</p>";
                    // $data = "<p style='color: rgb(30 64 175);'>Booked</p>";
                }else if($row->status == "Waiting"){
                    $data = "<p style='color: orange;'>Wait List</p>";
                }else if($row->status == "Pay"){
                    $data = "<p style='color: #20B2AA;'>Wait Pay</p>";
                    // $data = "<p style='color: goldenrod;'>Wait Pay</p>";
                }else if($row->status == "Success"){
                    $data = "<p style='color: green;'>Success</p>";
                }else if($row->status == "Cancel"){
                    $data = "<p style='color: red;'>Cancel</p>";
                }else{
                    $data = '-';
                }
                return $data;
            })
            ->editColumn('code', function ($row) {
                $tour = TourModel::find($row->tour_id);
                $data = "<p><b>$tour->code</b></p>";
                return $data;
            })
            ->editColumn('tour', function ($row) {
                $tour = TourModel::find($row->tour_id);
                $data = "<p><b>$tour->name</b></p>";
                // $data .= "<a href='$this->segment/tour/edit/$tour->id' style='text-decoration: underline; color:#0283df;'>".$tour->name."</a><br><br>";
                return $data;
            })
            ->editColumn('period', function ($row) {
                $month = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
                $period = TourPeriodModel::find($row->period_id);
                if($period){
                    $data = date('d',strtotime($period->start_date)) ." ". $month[date('n',strtotime($period->start_date))] ." ". date('y', strtotime('+543 Years', strtotime($period->start_date))) . "-" . date('d',strtotime($period->end_date)) ." ". $month[date('n',strtotime($period->end_date))] ." ". date('y', strtotime('+543 Years', strtotime($period->end_date)));
                }else{
                    $data = "-";
                }
                return $data;
            })
            ->addColumn('sale', function ($row) {
                $user = User::find($row->sale_id);
                $data = "";
                if($user){
                    $data .= $user->name;
                }
                return $data;
            })
            ->addColumn('created_at', function ($row) {
                $data = "";
                if($row->created_at){
                    $data .= date('d/m/Y H:i', strtotime('+543 Years', strtotime($row->created_at)));
                }
                return $data;
            })
            ->editColumn('action', function ($row) {
                $data = "";
                $menu_control = Helper::menu_active($this->menu_id);
                if($menu_control->edit == "on")
                {
                    $data.= " <a href='javascript:' class='mr-3 mb-2 btn btn-sm btn-success' onclick='Confirm($row->id)' title='Confirm'><i class='fa fa-check w-4 h-4 mr-1'></i> ยืนยันที่นั่ง </a>";
                    $data.= " <a href='$this->segment/$this->folder/edit/$row->id' class='mr-3 mb-2 btn btn-sm btn-info' title='Edit'><i class='fa fa-edit w-4 h-4 mr-1'></i> แก้ไข </a>";
                }
                if($menu_control->delete == "on")
                {
                    $data.= " <a href='javascript:' class='mr-3 mb-2 btn btn-sm btn-danger' onclick='deleteItem($row->id)' title='Delete'><i class='far fa-trash-alt w-4 h-4 mr-1'></i> ยกเลิกงาน </a>";  
                }
                return $data;
            })
            ->rawColumns(['ref','name','code','tour','period','status','sale','created_at','action'])
            ->make(true);
    }

    public function call_period(Request $request)
    {			
        // dd($request->all());
        $v_id 		= $request['tour_id'];			
        $sqlcall   	= "select * from tb_tour_period where tour_id='$v_id' and deleted_at is null";
        // echo $sqlcall; exit();
        $rowcall 	= DB::select($sqlcall);			
        $month = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];

        $v_detail 	= "<option value=''>กรุณาเลือก</option>";
        foreach($rowcall as $k => $v){
            $v_detail 	.= "";
            $v_detail 	.= "<option value='$v->id'>" .date('d',strtotime($v->start_date)) ." ". $month[date('n',strtotime($v->start_date))] ." ". date('y', strtotime('+543 Years', strtotime($v->start_date))). " - " .date('d',strtotime($v->end_date)) ." ". $month[date('n',strtotime($v->end_date))] ." ". date('y', strtotime('+543 Years', strtotime($v->end_date)))."</option>";
            // $v_detail 	.= "<option value='$v->id'> $v->name_th  /  $v->name_en </option>";
        }		
                    
        $datas 		= array(
            "r_status"	=> 'y',
            "r_detail"	=> $v_detail
        );
        return json_encode($datas);	
    }

    public function add(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $user = User::find(Auth::Guard()->id());

        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => $this->name_page, "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/add", 'name' => "เพิ่ม", "last" => 1],
        ];
        $tour = TourModel::where(['status'=>'on'])->whereNull('deleted_at')->get();
        if($user->role == 1){
            $sales = User::where(['role'=> 2 , 'status'=>'active'])->get();
        }else{
            $sales = User::where(['id'=> $user->id])->get();
        }
        return view("$this->prefix.pages.$this->folder.add", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'tour' => $tour,
            'sales' => $sales,
        ]);
    }

    public function view(Request $request, $id)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = BookingFormModel::find($id);
        $navs = [
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลการจองทัวร์", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder/edit/$id", 'name' => "แก้ไขข้อมูลคุณ $data->name $data->surname", "last" => 0],
        ];
        $tour = TourModel::find($data->tour_id);
        $sales = User::where(['role'=> 2 , 'status'=>'active'])->get();
        return view("$this->prefix.pages.$this->folder.view", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'row' => $data,
            'navs' => $navs,
            'id' => $id,
            'tour' => $tour,
            'sales' => $sales,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = BookingFormModel::find($id);
        $user = User::find(Auth::Guard()->id());
        if($user->role != 1 && $data->sale_id != $user->id){ return $this->check_sale_id(); }
        $navs = [
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลการจองทัวร์", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder/edit/$id", 'name' => "แก้ไขข้อมูลคุณ $data->name $data->surname", "last" => 0],
        ];
        $tour = TourModel::find($data->tour_id);
        if($user->role == 1){
            $sales = User::where(['role'=> 2 , 'status'=>'active'])->get();
        }else{
            $sales = User::where(['id'=>$user->id])->get();
        }
        return view("$this->prefix.pages.$this->folder.edit", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'row' => $data,
            'navs' => $navs,
            'id' => $id,
            'tour' => $tour,
            'sales' => $sales,
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
        // dd($request->all(),str_replace(",","",$request->total_price),str_replace(",","",$request->price1));
        DB::beginTransaction();
        try {

            if ($id == null) {
                $data = new BookingFormModel();
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');

                $code_booking = IdGenerator::generate([
                    'table' => 'tb_booking_form', 
                    'field' => 'code', 
                    'length' => 8,
                    'prefix' =>'B'.date('ym'),
                    'reset_on_prefix_change' => true 
                ]);
    
                $data->code = $code_booking;
                $send_mail = 'N';
                
            } else {
                $data = BookingFormModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s');

                $send_mail = 'Y';
            }

            $period = TourPeriodModel::where(['tour_id'=>$request->tour_id,'id'=>$request->period_id])->first();

            if($period){
                $data->start_date = $period->start_date;
                $data->end_date = $period->end_date;
            }

            $data->tour_id = $request->tour_id;
            $data->period_id = $request->period_id;
            $data->sale_id = $request->sale_id;
            $data->name = $request->name;
            $data->surname = $request->surname;
            $data->email = $request->email;
            $data->phone = $request->phone;
            $data->detail = $request->detail;
            $data->num_twin = $request->num_twin;
            $data->num_single = $request->num_single;
            $data->num_child = $request->num_child;
            $data->num_childnb = $request->num_childnb;
            
            $data->price1 = str_replace(",","",$request->price1);
            $data->sum_price1 = str_replace(",","",$request->sum_price1);
            $data->price2 = str_replace(",","",$request->price2);
            $data->sum_price2 = str_replace(",","",$request->sum_price2);
            $data->price3 = str_replace(",","",$request->price3);
            $data->sum_price3 = str_replace(",","",$request->sum_price3);
            $data->price4 = str_replace(",","",$request->price4);
            $data->sum_price4 = str_replace(",","",$request->sum_price4);
            $data->total_price = str_replace(",","",$request->total_price);
            $data->total_qty = $request->total_qty;

            $chk_send_mail = 'N';
            if($data->status != $request->status){ // เช็คเพื่อใช้ส่งเมล
                $chk_send_mail = 'Y';
            }

            $data->status = $request->status;
            $data->remark = $request->remark;

            if ($data->save()) {
                DB::commit();

                if($send_mail == 'Y' && $chk_send_mail == 'Y'){
                    $this->sendmail_status_booking($data);
                }
                return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder/edit/$data->id")]);
            } else {
                DB::rollback();
                return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder")]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            //dd($e->getMessage());
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

    public function confirm($id)
    {
        $datas = BookingFormModel::find($id);
        if (@$datas) {
            $datas->status = "Success";
        }
        if (@$datas->save()) {
            $this->sendmail_status_booking($datas);
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function destroy($id)
    {
        $datas = BookingFormModel::find($id);
        if (@$datas) {
            $datas->status = "Cancel";
            // $datas->deleted_at = date('Y-m-d H:i:s');
        }
        if (@$datas->save()) {
            $this->sendmail_status_booking($datas);
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function loadPrice(Request $request)
    {
        $period = TourPeriodModel::find($request->id);
        if(@$period->special_price1 > 0){
            $price1 = @$period->price1 - @$period->special_price1;
        }else{
            $price1 = @$period->price1;
        }

        if($period->special_price2 > 0){
            $orignal_price = $period->price1 + $period->price2; // เอาพักเดี่ยวบวกพักคู่ จะได้ราคาจริง
            $price2 = $orignal_price - $period->special_price2; // เอาราคาที่บวกแล้วมาลบส่วนลด
        }else{
            $price2 = $period->price1 + $period->price2;
        }

        if($period->special_price3 > 0){
            $price3 = $period->price3 - $period->special_price3;
        }else{
            $price3 = $period->price3;
        }

        if($period->special_price4 > 0){
            $price4 = $period->price4 - $period->special_price4;
        }else{
            $price4 = $period->price4;
        }

        $count = $period->count;

        if($period){
            $arr = [
                'status' => '200',
                'result' => 'success',
                'price1' => $price1,
                'price2' => $price2,
                'price3' => $price3,
                'price4' => $price4,
                'count' => $count,
                'message' => 'ดำเนินการสำเร็จ'
            ];
        }else{
            $arr = [
                'status' => '500',
                'result' => 'error',
                'price1' => 0,
                'price2' => 0,
                'price3' => 0,
                'price4' => 0,
                'count' => 0,
                'message' => 'เกิดข้อผิดพลาด'
            ];
        }
        return response()->json($arr);
    }

    public function edit_detail(Request $request, $id)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = BookingDetailModel::find($id);
        $navs = [
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลการจองทัวร์", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder/edit-detail/$id", 'name' => "แก้ไขเงื่อนไขการจองทัวร์", "last" => 0],
        ];
        return view("$this->prefix.pages.$this->folder.edit-detail", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'row' => $data,
            'navs' => $navs,
            'id' => $id,
        ]);
    }
   
    public function update_detail(Request $request,$id)
    {
        try {
            DB::beginTransaction();
            $data = BookingDetailModel::find($id);
            $data->updated_at = date('Y-m-d H:i:s'); 
            $data->detail = $request->detail;
           
            if ($data->save()) {
                DB::commit();
                return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder/edit-detail/1")]);
            } else {
                DB::rollback();
                return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder/edit-detail/1")]);
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

    public function sendmail_status_booking($data){
        try {
            $address = ContactModel::find(1);

            $mail = new PHPMailer(true);
            //Server settings
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->SMTPAuth   = true;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            //Recipients
            $mail->setFrom('noreply@liw.orangeworkshop.info', 'แจ้งสถานะข้อมูลการสั่งจองทัวร์กับ Next trip Holiday');
            $mail->addAddress($data->email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'แจ้งสถานะข้อมูลการสั่งจองทัวร์กับ Next trip Holiday';
            $mail->Body    = '';
            $mail->Body .= $this->contact_sendmailv_html_header();
            $mail->Body .= $this->contact_sendmailv_html_center($data);
            $mail->Body .= $this->contact_sendmailv_html_footer($address);

            $mail->send();
            // echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }

    public function contact_sendmailv_html_header()
    {
        return $detail	= '
            <html>
                <table width="800" border="0" cellspacing="0" cellpadding="0" bgcolor="white" align="center">
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                        <td	style="width:5%;background-color:orange;line-height: 2px;"">&nbsp;</td>
                        </tr>';
    }

    public function contact_sendmailv_html_center($data)
    {
        return $detail	= '
                        <tr>
                            <td>
                                <center>
                                    <table width="100%" cellspacing="0" cellpadding="15" style="font-family: Sarabun, sans-serif;border: 1px solid transparent;background-color:transparent; " >
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>ชื่อเรื่อง : </b> <span style="margin-left:15px;">แจ้งสถานะข้อมูลการจองโปรแกรมทัวร์</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent; padding-top:.5rem; padding-bottom:.5rem;"><b>รายละเอียด </b> <span style="margin-left:15px;"></span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent; padding-top:.5rem; padding-bottom:.5rem;"><span style="margin-left:15px;">คำสั่งจองหมายเลข <span style="color:orange;">'.$data['code'].'</span> ของคุณ '.$data['name'].' '.$data['surname'].' ได้มีการเปลี่ยนสถานะเป็น <span style="color:orange;">'.$data['status'].'</span></span></td>
                                        </tr>
                                    </table>
                                </center>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>';
    }

    public function contact_sendmailv_html_footer($data)
    {
        return $detail	= '
                        <tr style="background-color:lightgray;line-height: 1px;">
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>
                                <center>
                                    <table width="100%" cellspacing="0" cellpadding="15" style="font-family: Sarabun, sans-serif;border: 1px solid transparent;background-color:transparent; " >
                                    <tr>
                                        <td style="width:50%;">
                                            <span style="font-size: 12px;color:gray; line-height:20px; ">'
                                                .$data->address.'
                                            </span>
                                        </td>
                                        <td style="width:40%;  text-align:right;"><span style="font-size: 12px;color:gray; line-height:20px;">Tel: '.$data->phone_front.'</span></td>
                                    </tr>
                                    </table>
                                </center>
                            </td>
                        </tr>
                    </table>
                </html>';
    }
}
