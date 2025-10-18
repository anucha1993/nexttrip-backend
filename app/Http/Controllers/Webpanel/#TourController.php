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
use Illuminate\Support\Facades\Http;
use Intervention\Image\ImageManagerStatic as Image;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PDF;

use App\Models\Backend\TourModel;
use App\Models\Backend\TourGroupModel;
use App\Models\Backend\TourTypeModel;
use App\Models\Backend\TourDetailModel;
use App\Models\Backend\TourPeriodModel;
use App\Models\Backend\TourPeriodStatusModel;

use App\Models\Backend\WholesaleModel;
use App\Models\Backend\LandmassModel;
use App\Models\Backend\CountryModel;
use App\Models\Backend\CountriesModel; // Import
use App\Models\Backend\StatesModel; // Import
use App\Models\Backend\CityModel;
use App\Models\Backend\ProvinceModel;
use App\Models\Backend\DistrictModel;
use App\Models\Backend\TagContentModel;
use App\Models\Backend\TravelTypeModel;
use App\Models\Backend\PromotionModel;
use App\Models\Backend\TourGalleryModel;

class TourController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'tour';
    protected $folder = 'tour';
    protected $menu_id = '7';
    protected $name_page = "ข้อมูลโปรแกรมทัวร์";

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
        $query = new TourModel;
        if ($search) {
            $query = $query->where('name', 'LIKE', '%' . trim($search) . '%');
        }
        $query = $query->orderBy('id', $sort);
        $results = $query->paginate($paginate);
        return $results;
    }

    public function updateManual(){
        // $tour = TourModel::where('price',null)->get();
        // foreach($tour as $t){
        //     // $code_tour = IdGenerator::generate([
        //     //     'table' => 'tb_tour', 
        //     //     'field' => 'code', 
        //     //     'length' => 10, 
        //     //     'prefix' =>'NT'.date('ym'),
        //     //     'reset_on_prefix_change' => true 
        //     // ]);
        //     $t->price = 0.00;
        //     $t->save();
        // }

        // $tour = TourPeriodModel::where('price3',null)->get();
        // foreach($tour as $t){
        //     // $code_tour = IdGenerator::generate([
        //     //     'table' => 'tb_tour', 
        //     //     'field' => 'code', 
        //     //     'length' => 10, 
        //     //     'prefix' =>'NT'.date('ym'),
        //     //     'reset_on_prefix_change' => true 
        //     // ]);
        //     $t->price3 = 0.00;
        //     $t->save();
        // }

        // $tour = TourPeriodModel::where('special_price3',null)->get();
        // foreach($tour as $t){
        //     // $code_tour = IdGenerator::generate([
        //     //     'table' => 'tb_tour', 
        //     //     'field' => 'code', 
        //     //     'length' => 10, 
        //     //     'prefix' =>'NT'.date('ym'),
        //     //     'reset_on_prefix_change' => true 
        //     // ]);
        //     $t->special_price3 = 0.00;
        //     $t->save();
        // }

        // $tour = TourPeriodModel::where('old_price3',null)->get();
        // foreach($tour as $t){
        //     // $code_tour = IdGenerator::generate([
        //     //     'table' => 'tb_tour', 
        //     //     'field' => 'code', 
        //     //     'length' => 10, 
        //     //     'prefix' =>'NT'.date('ym'),
        //     //     'reset_on_prefix_change' => true 
        //     // ]);
        //     $t->old_price3 = 0.00;
        //     $t->save();
        // }
    }

    public function downloadAndSave(){
        $url = 'https://www.zegotravel.com/uploadfile/p_d_f/programtour/926_20230105155112.pdf';
        // $url = 'https://www.zegotravel.com/uploadfile/word/programtour/926_20230105155112.docx';

        $response = Http::get($url);

        $allow_pdf = ['pdf'];
        $allow_word = ['doc', 'docx'];
        if ($response->successful()) {

            $filename = basename($url);
            $ext = explode(".", $filename);
            $new = 'upload/test/' . $filename;
            // $new = 'upload/tour/word_file/zegoapi/' . $filename;

            if (in_array($ext[1], $allow_pdf)) {
                Storage::disk('public')->put($new, $response->body());
            }

            $this->generateHeaderFooter($filename);
            
            return 'ดาวน์โหลดและบันทึกไฟล์สำเร็จ';
        } else {
            return 'ไม่สามารถดาวน์โหลดไฟล์';
        }
    }

    public function generateHeaderFooter($filename)
    {
        // อ่านไฟล์ PDF
        // $pdf = PDF::loadFile(public_path('pdf/pdf10072023-15554007.pdf'));
        $pdf = PDF::loadFile(public_path('upload/test/' . $filename));

        // กำหนด Header และ Footer
        $header = view("$this->prefix.pdf.header")->render();
        $footer = view("$this->prefix.pdf.footer")->render();

        // ตั้งค่า Header และ Footer ใน PDF
        $pdf->setOptions([
            'isPhpEnabled' => true,
            'defaultCharset' => 'UTF-8',
            'header-html' => $header,
            'footer-html' => $footer,
            'header-spacing' => 5,
            'footer-spacing' => 10,
        ]);

        // บันทึกไฟล์ PDF ทับไฟล์เดิม
        // $pdf->save(public_path('pdf/pdf10072023-15554007.pdf'));
        $pdf->save(public_path('upload/test/' . $filename));
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

        $tour = TourModel::where('group_id',3)->whereNotNull('wholesale_id')->whereNull('deleted_at')->get();
        return view("$this->prefix.pages.$this->folder.index", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'items' => $items,
            'menu_control' => $menu_control,
            'tour' => $tour,
            'country' => CountryModel::whereNull('deleted_at')->orderby('country_name_th','asc')->get(),
            'city' => CityModel::whereNull('deleted_at')->get(),
            'province' => ProvinceModel::whereNull('deleted_at')->get(),
            'promotion' => PromotionModel::where('status','on')->whereNull('deleted_at')->get(),
            'type' => TourTypeModel::where('status','on')->whereNull('deleted_at')->get(),
        ]);
    }

    public function datatable(Request $request){
			
        $like = $request->Like;

        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}

        $sTable = TourModel::select(
                'tb_tour.id as id',
                'tb_tour.wholesale_id',
                'tb_tour.type_id',
                'tb_tour.image',
                'tb_tour.name',
                'tb_tour.code',
                'tb_tour.code1',
                'tb_tour.word_file',
                'tb_tour.pdf_file',
                'tb_tour.country_id',
                'tb_tour.city_id',
                'tb_tour.province_id',
                'tb_tour.district_id',
                'tb_tour.promotion1',
                'tb_tour.promotion2',
                'tb_tour.status',
                'tb_tour.updated_at',
                'tb_tour.deleted_at',
                'tb_tour_period.start_date',
                'tb_tour_period.promotion_id',
            )
            ->leftjoin('tb_tour_period', 'tb_tour_period.tour_id', 'tb_tour.id')->orderby('tb_tour.id','desc')
            ->when($like, function ($query) use ($like) {
                if (@$like['search_tap_name'] != "") {
                    if($like['search_tap_name'] == 'all'){
                        $query->whereExists(function ($subquery) {
                            $subquery->select(DB::raw(1))
                                ->from('tb_tour_period')
                                ->where('tb_tour_period.tour_id', '=', DB::raw('tb_tour.id'))
                                ->whereNull('tb_tour_period.deleted_at')
                                ->whereDate('tb_tour_period.start_date', '>=', now());
                        });
                    }else if($like['search_tap_name'] == 'off'){
                        $query->whereNotExists(function ($subquery) {
                            $subquery->select(DB::raw(1))
                                ->from('tb_tour_period')
                                ->where('tb_tour_period.tour_id', '=', DB::raw('tb_tour.id'))
                                ->whereNull('tb_tour_period.deleted_at')
                                ->whereDate('tb_tour_period.start_date', '>=', now());
                        });
                    }
                }
                $query->where(function ($query) use ($like) {
                    if (@$like['search_title'] != "") {
                        $query->where('tb_tour.code', 'like', '%' . $like['search_title'] . '%');
                        $query->orWhere('tb_tour.code1', 'like', '%' . $like['search_title'] . '%');
                        $query->orWhere('tb_tour.name', 'like', '%' . $like['search_title'] . '%');
                    }
                });
                if (@$like['search_status'] != "") {
                    $query->where('tb_tour.status', @$like['search_status']);
                }
                if (@$like['search_wholesale'] != "") {
                    $query->where('tb_tour.wholesale_id', @$like['search_wholesale']);
                }
                if (@$like['search_country'] != "") {
                    $query->where('tb_tour.country_id', 'like', '%"' . $like['search_country'] . '"%');
                }
                if (@$like['search_tag_promotion'] != "") {
                    $query->where('tb_tour_period.promotion_id', $like['search_tag_promotion'] );
                }
                // if (@$like['search_city'] != "") {
                //     if (@$like['search_city'] != "") {
                //         $arr = array();
                //         $ca = explode('.',$like['search_city']);
                //         $arr[$ca[0]][] = $ca[1];
                //     }
                //     if(isset($arr['CI'])){
                //         $query->where('city_id', 'like', '%"' . $arr['CI'][0] . '"%');
                //     }
                //     if(isset($arr['PRO'])){
                //         $query->where('province_id', 'like', '%"' . $arr['PRO'][0] . '"%');
                //     }
                // }
                if (@$like['search_type'] != "") {
                    $query->where('tb_tour.type_id', $like['search_type'] );
                }
                if (@$like['search_promotion'] != "") {
                    if($like['search_promotion'] == 1){
                        $query->where('tb_tour.promotion1', 'Y');
                    }else{
                        $query->where('tb_tour.promotion2', 'Y');
                    }
                }
            })
            ->whereNull('tb_tour.deleted_at')
            ->groupby('tb_tour.id');
        
        $sQuery = DataTables::of($sTable);
        return $sQuery
        ->addIndexColumn()
        ->editColumn('image', function ($row) {
            $data = "<p><b>$row->code</b></p><br> <p><center> <img src='$row->image' style='width: 50%;'></center></p>";
            return $data;
        })
        ->editColumn('name', function ($row) {
            $data = "";
            if($row->wholesale_id){
                $wholesale = WholesaleModel::find($row->wholesale_id);
            }
            if(@$wholesale){
                $data .= "<b>$row->code1</b><p>$wholesale->wholesale_name_th</p><br>";
            }else{
                $data .= "<p><b>$row->code1</b></p><br>";
            }
            $data .= "<a href='$this->segment/$this->folder/edit/$row->id' style='text-decoration: underline; color:#0283df;'>".$row->name."</a><br><br>";
            if($row->pdf_file || $row->word_file){
                $data .= "เอกสารโปรแกรมทัวร์<br>";
            }
            if($row->word_file){
                $data .= "<a href='$row->word_file' target='_blank'><i class='fa fa-file-word-o' style='font-size:20px; color:blue; !important'></i></a>&nbsp;&nbsp;&nbsp;";
            }
            if($row->pdf_file){
                $data .= "<a href='$row->pdf_file' target='_blank'><i class='fa fa-file-pdf-o' style='font-size:20px; color:red; !important'></i></a>";
            }
            return $data;
        })
        ->editColumn('country', function ($row) {
            $country_select = json_decode($row->country_id,true);
            $city_select = json_decode($row->city_id,true); 
            $province_select = json_decode($row->province_id,true);
            $district_select = json_decode($row->district_id,true);

            $country_sel = CountryModel::whereIn('id',$country_select)->get();
            $city_sel = CityModel::whereIn('id',$city_select)->get();
            $province_sel = ProvinceModel::whereIn('id',$province_select)->get();
            $district_sel = DistrictModel::whereIn('id',$district_select)->get();

            $type = TourTypeModel::find($row->type_id);
            
            $data = "";
            foreach($country_sel as $co){
                $data .= "<p class='mb-1'> ".$co->country_name_th."</p>";
            }
            foreach($city_sel as $ci){
                $data .= "<p class='mb-1'> ".$ci->city_name_th."</p>";
            }
            foreach($province_sel as $pro){
                $data .= "<p class='mb-1'> ".$pro->name_th."</p>";
            }
            foreach($district_sel as $dis){
                $data .= "<p class='mb-1'> ".$dis->name_th."</p>";
            }
            if($type){
                $data .= "<p class='mt-3' style='color:blue;'>".$type->type_name."</p>";
            }
            if($row->promotion1 == 'Y'){
                $data .= "<p class='mt-3' style='color:red;'>โปรไฟไหม้</p>";
            }else if($row->promotion2 == 'Y'){
                $data .= "<p class='mt-3' style='color:red;'>โปรโมชั่นทัวร์</p>";
            }
            return $data;
        })
        ->editColumn('period', function ($row) use ($like) {
            // if (@$like['search_tap_name'] != "") {
            //     $period = TourPeriodModel::where('tour_id', $row->id)->whereNull('deleted_at')->get();
            // } else {
            //     $period = TourPeriodModel::where('tour_id', $row->id)->whereDate('start_date', '>=', now())->whereNull('deleted_at')->get();
            // }
            $period = TourPeriodModel::where('tour_id', $row->id)->whereNull('deleted_at')->get();
            $data = "";
            foreach ($period as $i => $pe) {
                $data .= "<a href='$this->segment/$this->folder/edit-period/$pe->id' style='text-decoration: underline; color:#0283df;'><p class='mb-1'>" . date('d M y', strtotime($pe->start_date)) . "  -  " . date('d M y', strtotime($pe->end_date)) . "</p></a>";
            }
            return $data;
        })
        ->editColumn('price', function ($row) use ($like) {
            // if (@$like['search_tap_name'] != "") {
            //     $period = TourPeriodModel::where('tour_id', $row->id)->whereNull('deleted_at')->get();
            // } else {
            //     $period = TourPeriodModel::where('tour_id', $row->id)->whereDate('start_date', '>=', now())->whereNull('deleted_at')->get();
            // }
            $period = TourPeriodModel::where('tour_id', $row->id)->whereNull('deleted_at')->get();
            $data = "";
            foreach($period as $i => $pe){
                $data .= "<a href='$this->segment/$this->folder/edit-period/$pe->id' style='text-decoration: underline; color:#0283df;'><p class='mb-1'>" .number_format($pe->price1,0). "</p></a>";
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
        ->addColumn('updated_at', function ($row) {
            $data = date('d/m/Y H:i:s', strtotime('+543 Years', strtotime($row->updated_at)));
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
        ->rawColumns(['image','name','country','period','price','status','updated_at','action'])
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
        $tag = TagContentModel::whereNull('deleted_at')->get();
        return view("$this->prefix.pages.$this->folder.add", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'wholesale' => WholesaleModel::whereNull('deleted_at')->get(),
            'group' => TourGroupModel::whereNull('deleted_at')->get(),
            'type' => TourTypeModel::where('status','on')->whereNull('deleted_at')->get(),
            'landmass' => LandmassModel::where('status','on')->whereNull('deleted_at')->get(),
            'country' => CountryModel::where('status','on')->whereNull('deleted_at')->get(),
            'city' => CityModel::where('status','on')->whereNull('deleted_at')->get(),
            'province' => ProvinceModel::where('status','on')->whereNull('deleted_at')->get(),
            'district' => DistrictModel::where('status','on')->whereNull('deleted_at')->get(),
            'tag' => $tag,
            'data_tag'  => json_encode($tag),
            'travel' => TravelTypeModel::where('status','on')->whereNull('deleted_at')->get(),
            'period_status' => TourPeriodStatusModel::whereNull('deleted_at')->get(),
            'promotion' => PromotionModel::where('status','on')->whereNull('deleted_at')->get(),
        ]);
    }

    public function edit(Request $request,$id=null)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}

        $tour = TourModel::find($id);

        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => $this->name_page, "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/edit/$id", 'name' => "แก้ไข", "last" => 1],
        ];
        $tag = TagContentModel::whereNull('deleted_at')->get();
        return view("$this->prefix.pages.$this->folder.edit", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'row' => TourModel::find($id),
            'wholesale' => WholesaleModel::whereNull('deleted_at')->get(),
            'group' => TourGroupModel::whereNull('deleted_at')->get(),
            'type' => TourTypeModel::where('status','on')->whereNull('deleted_at')->get(),
            'landmass' => LandmassModel::where('status','on')->whereNull('deleted_at')->get(),
            'country' => CountryModel::where('status','on')->whereNull('deleted_at')->get(),
            'city' => CityModel::where('status','on')->whereNull('deleted_at')->get(),
            'province' => ProvinceModel::where('status','on')->whereNull('deleted_at')->get(),
            'district' => DistrictModel::where('status','on')->whereNull('deleted_at')->get(),
            'tag' => $tag,
            't_select'  => json_encode(TagContentModel::whereIn('id',json_decode($tour->tag_id,true))->get()),
            'data_tag'  => json_encode($tag),
            'travel' => TravelTypeModel::where('status','on')->whereNull('deleted_at')->get(),
            'gallery' => TourGalleryModel::where(['tour_id' => $id])->get(),
            // 'detail' => TourDetailModel::where(['tour_id' => $id])->get(),
            'period' => TourPeriodModel::where(['tour_id' => $id])->whereNull('deleted_at')->orderby('start_date','asc')->get(),
            'period_status' => TourPeriodStatusModel::whereNull('deleted_at')->get(),
            'promotion' => PromotionModel::where('status','on')->whereNull('deleted_at')->get(),
            'id' => $id,
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
        // dd($request->all());
        // dd($request->detail);

        try {
            DB::beginTransaction();

            if ($id == null) {
                $data = new TourModel();
                $data->data_type = 1; // 1 system , 2 api
                $data->image_check_change = 1; // 1 ไม่ดึงรูปจาก Api , 2 ดึงรูปจาก Api
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');

                $code_tour = IdGenerator::generate([
                    'table' => 'tb_tour', 
                    'field' => 'code', 
                    'length' => 10, 
                    'prefix' =>'NT'.date('ym'),
                    'reset_on_prefix_change' => true 
                ]);

                $data->code = $code_tour;
               
            } else {
                $data = TourModel::find($id);
                $data->name_check_change = $request->name_check_change;
                $data->country_check_change = $request->country_check_change;
                $data->airline_check_change = $request->airline_check_change;
                $data->description_check_change = $request->description_check_change;
                $data->updated_at = date('Y-m-d H:i:s');
            }

            $arr = array();
            foreach($request->category as $cat){
                $ca = explode('.',$cat);
                $arr[$ca[0]][] = $ca[1];
            }

            // generate slug
            $co_slug = "";
            if(isset($arr['CO'])){
                $country = CountryModel::whereIn('id',$arr['CO'])->get();
                foreach($country as $co){
                    $co_slug .= $co->slug.'-';
                }
            }
            $ci_slug = "";
            if(isset($arr['CI'])){
                $city = CityModel::whereIn('id',$arr['CI'])->get();
                foreach($city as $ci){
                    $ci_slug .= $ci->slug.'-';
                }
            }
            $p_slug = "";
            if(isset($arr['P'])){
                $province = ProvinceModel::whereIn('id',$arr['P'])->get();
                foreach($province as $p){
                    $p_slug .= $p->slug.'-';
                }
            }
            $d_slug = "";
            if(isset($arr['D'])){
                $district = DistrictModel::whereIn('id',$arr['D'])->get();
                foreach($district as $d){
                    $d_slug .= $d->slug.'-';
                }
            }

            $data->name = $request->name;
            $data->type_id = $request->type_id;
            $data->group_id = $request->group_id;
            if($request->group_id == 3){
                $data->wholesale_id = $request->wholesale_id;
            }else{
                $data->wholesale_id = null;
            }

            $data->country_id = isset($arr['CO']) ? json_encode($arr['CO']):'[]';
            $data->city_id = isset($arr['CI']) ? json_encode($arr['CI']):'[]';
            $data->province_id = isset($arr['P']) ? json_encode($arr['P']):'[]';
            $data->district_id = isset($arr['D']) ? json_encode($arr['D']):'[]';
            $data->tag_id = $request->tag_id?json_encode($request->tag_id):'[]';

            // generate code
            // $data->code = '';
            // if($request->code){
            //     $data->code = $request->code;
            // }else{
            //     $month = date("m");
            //     $year  = date("Y");
            //     $format =  $year.$month;
            //     $code = DB::table('tb_tour')->select(DB::raw('LPAD(MAX(CAST(substring(code,10,length(code))+1 AS int)),5,0) as maximum'))->where('code','LIKE','%'.$format.'%')->orderby('id','desc')->get()[0];
            //     if($code->maximum){
            //         $code = 'Next'.$format.$code->maximum;
            //     }else{
            //         $code = 'Next'.$format.'00001';
            //     }
            //     $data->code = $code;
            // }

            // code slug
            $code_slug = Str::slug($data->code);
            $data->slug = $co_slug.$ci_slug.$p_slug.$d_slug.$code_slug; // save generate slug

            $data->code1 = $request->code1;
            $data->code1_check = $request->code1_check;
            $data->rating = $request->rating;
            $data->airline_id = $request->airline_id;
            $data->description = $request->description;
            $data->travel = $request->travel;
            $data->shop = $request->shop;
            $data->eat = $request->eat;
            $data->special = $request->special;
            $data->stay = $request->stay;
            $data->video = $request->video;

            $image = $request->image;
            $video_cover = $request->video_cover;
            $pdf = $request->pdf_file;
            $word = $request->word_file;
            $allow_img = ['png', 'jpeg', 'jpg','webp'];
            $allow_pdf = ['pdf'];
            $allow_word = ['doc', 'docx'];
            if ($image) {
                if ($data->image != null) {
                    Storage::disk('public')->delete($data->image);
                }
                $lg = Image::make($image->getRealPath());
                $ext = explode("/", $lg->mime());
                $lg->resize(600, 600)->stream();
                // $lg->resize(784, 522)->stream();
                $new = 'upload/tour/image' . date('dmY-Hism') . '.' . $ext[1];
                if (in_array($ext[1], $allow_img)) {
                    // $image->storeAs('', $new, 'public');
                    $store = Storage::disk('public')->put($new, $lg);
                    $data->image = $new;
                    if($data->data_type == 2){ // กรณีเป็นข้อมูลที่มาจาก Api
                        $data->image_check_change = 1; // เช็คไม่ให้ครั้งต่อไปดึงรูปจาก Api
                    }
                }
            }

            if ($video_cover) {
                if ($data->video_cover != null) {
                    Storage::disk('public')->delete($data->video_cover);
                }
                $lg1 = Image::make($video_cover->getRealPath());
                $ext1 = explode("/", $lg1->mime());
                $lg1->resize(394, 230)->stream();
                $new1 = 'upload/tour/video-cover' . date('dmY-Hism') . '.' . $ext1[1];
                if (in_array($ext1[1], $allow_img)) {
                    $store1 = Storage::disk('public')->put($new1, $lg1);
                    $data->video_cover = $new1;
                }
            }

            if ($request->hasFile('pdf_file')) {

                if ($data->pdf_file != null) {
                    Storage::disk('public')->delete($data->pdf_file);
                }

                // รับไฟล์ PDF
                $pdfFile = $request->file('pdf_file');
    
                // สร้างชื่อไฟล์ใหม่เพื่อบันทึก
                $path = 'upload/tour/pdf_file/';
                $ext = $pdfFile->getClientOriginalExtension();
                $filename = 'pdf' . date('dmY-Hism') . '.' . $ext;

                // บันทึกไฟล์ PDF ลงในเซิร์ฟเวอร์
                if (in_array($ext, $allow_pdf)) {
                    $pdfFile->move(public_path($path), $filename);
                    $data->pdf_file = $path . $filename;
                }
    
                // เรียกใช้เมธอดสร้าง Header และ Footer
                // $this->generateHeaderFooter($filename);
            }

            if ($request->hasFile('word_file')) {

                if ($data->word_file != null) {
                    Storage::disk('public')->delete($data->word_file);
                }

                // รับไฟล์ Word
                $wordFile = $request->file('word_file');
    
                // สร้างชื่อไฟล์ใหม่เพื่อบันทึก
                $path = 'upload/tour/word_file/';
                $ext = $wordFile->getClientOriginalExtension();
                $filename = 'word' . date('dmY-Hism') . '.' . $ext;

                // บันทึกไฟล์ Word ลงในเซิร์ฟเวอร์
                if (in_array($ext, $allow_word)) {
                    $wordFile->move(public_path($path), $filename);
                    $data->word_file = $path . $filename;
                }
            }

            $arr_detail = array();
            if($request->detail){
                foreach($request->detail as $i => $de){
                    $arr_detail[] = $de;
                    if(isset($de['sub'])){
                        foreach($de['sub'] as $k => $v){
                            $data_tour = json_decode($data->tour_detail,true); // ดึงข้อมูลเดิมที่เก็บเป็น json

                            $image = isset($request->detail[$i]['sub'][$k]['image']) ? $request->detail[$i]['sub'][$k]['image'] : false;
                            if($image){
                                if ($data_tour != null && $data_tour[$i]['sub'][$k]['image'] != false) {
                                    Storage::disk('public')->delete($data_tour[$i]['sub'][$k]['image']);
                                }

                                $lg = Image::make($image->getRealPath());
                                $ext = explode("/", $lg->mime());
                                $lg->resize(288, 225)->stream();
                                $new_detail = 'upload/tour/detail_image_'.$i.$k.date('dmY-Hism') . '.' . $ext[1];
                                if (in_array($ext[1], $allow_img)) {
                                    $store = Storage::disk('public')->put($new_detail, $lg);
                                }

                                $arr_detail[$i]['sub'][$k]['image'] = $new_detail;
                            }else{
                                if($data_tour != null && isset($data_tour[$i]['sub'][$k]['image']) && $data_tour[$i]['sub'][$k]['image'] != false){
                                    $arr_detail[$i]['sub'][$k]['image'] = $data_tour[$i]['sub'][$k]['image'];
                                }else{
                                    $arr_detail[$i]['sub'][$k]['image'] = false;
                                }
                            }
                        }
                    }
                }
            }

            $data->tour_detail = json_encode($arr_detail);

            if ($data->save()) {

                // gellery
                if (!empty($request->img)) {
                    foreach($request->img as $i => $img){
                        $lg = Image::make($img->getRealPath());
                        $ext = explode("/", $lg->mime());
                        $lg->resize(600, 600)->stream();
                        $new = 'upload/tour/tour-gallery-'. $i . date('dmY-Hism') . '.' . $ext[1];
                        if (in_array($ext[1], $allow_img)) {
                            $store = Storage::disk('public')->put($new, $lg);
                            $gallery = new TourGalleryModel();
                            $gallery->img             = $new;
                            $gallery->tour_id         = $data->id;
                            $gallery->save();
                        }
                    }
                }

                // $detail = TourDetailModel::where('tour_id',$data->id)->delete();
    
                // if($request->detail){
                //     foreach($request->detail as $i => $de){
                //         if($de){
                //             $data2 = new TourDetailModel;
                //             $data2->tour_id = $data->id;
                //             $data2->title = $de['title'][0];
                //             $data2->detail = $de['detail'][0];
                //             $data2->save();
                //         }
                //     }
                // }

                // $period = TourPeriodModel::where('tour_id',$data->id)->delete();

                if($request->period){
                    $max = array();
                    $period_id = array();
                    $cal1 = 0;
                    $cal2 = 0;
                    $cal3 = 0;
                    $cal4 = 0;
                    foreach($request->period as $pe){

                        if($pe['period_id'][0]){
                            $period = TourPeriodModel::find($pe['period_id'][0]);
                        }else{
                            $period = new TourPeriodModel;
                        }

                        $period->tour_id = $data->id;
                        $period->group_date = date('mY',strtotime($pe['start_date'][0]));
                        $period->start_date = $pe['start_date'][0];
                        $period->end_date = $pe['end_date'][0];

                        if($request->price1_check){
                            if($request->price1_all){
                                $period->price1 = $request->price1_all;
                            }else{
                                $period->price1 = $pe['price1'][0];
                            }
                            if($request->special_price1_all){
                                $period->special_price1 = $request->special_price1_all;
                            }else{
                                $period->special_price1 = $pe['special_price1'][0];
                            }
                        }else{
                            $period->price1 = $pe['price1'][0];
                            $period->special_price1 = $pe['special_price1'][0];
                        }

                        if($request->price2_check){
                            if($request->price2_all){
                                $period->price2 = $request->price2_all;
                            }else{
                                $period->price2 = $pe['price2'][0];
                            }
                            if($request->special_price2_all){
                                $period->special_price2 = $request->special_price2_all;
                            }else{
                                $period->special_price2 = $pe['special_price2'][0];
                            }
                        }else{
                            $period->price2 = $pe['price2'][0];
                            $period->special_price2 = $pe['special_price2'][0];
                        }

                        if($request->price3_check){
                            if($request->price3_all){
                                $period->price3 = $request->price3_all;
                            }else{
                                $period->price3 = $pe['price3'][0];
                            }
                            if($request->special_price3_all){
                                $period->special_price3 = $request->special_price3_all;
                            }else{
                                $period->special_price3 = $pe['special_price3'][0];
                            }
                        }else if($request->copy_price3){
                            $period->price3 = $period->price1;
                            $period->special_price3 = $period->special_price1;
                        }else{
                            $period->price3 = $pe['price3'][0];
                            $period->special_price3 = $pe['special_price3'][0];
                        }

                        if($request->price4_check){
                            if($request->price4_all){
                                $period->price4 = $request->price4_all;
                            }else{
                                $period->price4 = $pe['price4'][0];
                            }
                            if($request->special_price4_all){
                                $period->special_price4 = $request->special_price4_all;
                            }else{
                                $period->special_price4 = $pe['special_price4'][0];
                            }
                        }else if($request->copy_price4){
                            $period->price4 = $period->price1;
                            $period->special_price4 = $period->special_price1;
                        }else{
                            $period->price4 = $pe['price4'][0];
                            $period->special_price4 = $pe['special_price4'][0];
                        }

                        $period->day = $pe['day'][0];
                        $period->night = $pe['night'][0];

                        if($request->group_check){
                            if($request->group_all){
                                $period->group = $request->group_all;
                            }else{
                                $period->group = $pe['group'][0];
                            }
                        }else{
                            $period->group = $pe['group'][0];
                        }

                        if($request->count_check){
                            if($request->count_all){
                                $period->count = $request->count_all;
                            }else{
                                $period->count = $pe['count'][0];
                            }
                        }else{
                            $period->count = $pe['count'][0];
                        }

                        if($request->promotion_check){
                            $period->promotion_id = $request->promotion_all;
                        }else{
                            $period->promotion_id = $pe['promotion_id'][0];
                        }

                        if($request->start_date_check){
                            $period->pro_start_date = $request->pro_start_date_all;
                        }else{
                            $period->pro_start_date = $pe['pro_start_date'][0];
                        }

                        if($request->end_date_check){
                            $period->pro_end_date = $request->pro_end_date_all;
                        }else{
                            $period->pro_end_date = $pe['pro_end_date'][0];
                        }

                        $period->status_display = $pe['status_display'][0];
                        $period->status_period = $pe['status_period'][0];
                        if($period->save()){
                            $period_id[] = $period->id;
                        }
    
                        
                        if($pe['special_price1'][0]){
                            // $diff = $pe['price1'][0] - $pe['special_price1'][0]; 
                            // $cal1 = ($diff / $pe['price1'][0]) * 100;
                            if (isset($pe['price1'][0]) && $pe['price1'][0] != 0) {
                                $cal1 = ($pe['special_price1'][0] / $pe['price1'][0]) * 100;
                            } else {
                                $cal1 = 0;
                            }
                        }
                        if($pe['special_price2'][0]){
                            $total = $pe['price1'][0] + $pe['price2'][0];
                            if (isset($total) && $total != 0) {
                                $cal2 = ($pe['special_price2'][0] / $total) * 100;
                            } else {
                                $cal2 = 0;
                            }
                        }
                        if($pe['special_price3'][0]){
                            if (isset($pe['price3'][0]) && $pe['price3'][0] != 0) {
                                $cal3 = ($pe['special_price3'][0] / $pe['price3'][0]) * 100;
                            } else {
                                $cal3 = 0;
                            }
                        }
                        if($pe['special_price4'][0]){
                            if (isset($pe['price4'][0]) && $pe['price4'][0] != 0) {
                                $cal4 = ($pe['special_price4'][0] / $pe['price4'][0]) * 100;
                            } else {
                                $cal4 = 0;
                            }
                        }
                        
                        $calmax = max($cal1, $cal2, $cal3, $cal4);
                        array_push($max, $calmax);
                    }

                    // บันทึกจำนวนวัน และ ราคาเข้าไป tb_tour
                    // $data4 = TourPeriodModel::where(['tour_id'=>$data->id])->whereNull('deleted_at')->orderby('id','asc')->first();
                    $data4 = TourPeriodModel::where(['tour_id'=>$data->id])->whereNull('deleted_at')->get();
                    if($data4){
                        $maxSpecialPrice = $data4->max('special_price1');

                        // $data5 = $data4->sortBy(function ($item) { // ดึงข้อมูลที่มียอด total น้อยที่สุด
                        //     return $item->price1 - $item->special_price1;
                        // })->first();

                        $data5 = $data4->where('special_price1', $maxSpecialPrice)->first();
                    
                        if($data5){
                            $num_day = "";
                            if($data5->day && $data5->night){
                                $num_day = $data5->day.' วัน '.$data5->night.' คืน';
                            }
                            $price = $data5->price1;
                            $special_price = $data5->special_price1;
                            if($special_price && $special_price > 0){
                                $net_price = $price - $special_price;
                            }else{
                                $net_price = $price;
                            }
                            
                            if($net_price && $net_price > 0){
                                if($net_price <= 10000){
                                    $price_group = 1;
                                }else if($net_price  > 10000 && $net_price <= 20000){
                                    $price_group = 2;
                                }else if($net_price  > 20000 && $net_price <= 30000){
                                    $price_group = 3;
                                }
                                else if($net_price  > 30000 && $net_price <= 50000){
                                    $price_group = 4;
                                }
                                else if($net_price  > 50000 && $net_price <= 80000){
                                    $price_group = 5;
                                }else if($net_price  > 80000){
                                    $price_group = 6;
                                }
                            }else{
                                $price_group = 0;
                            }
                            TourModel::where('id',$data->id)->update(['num_day'=> $num_day,'price'=> $price,'price_group' => $price_group,'special_price'=> $special_price]);
                        }
                    }

                    $maxCheck = max($max);
                    if($maxCheck > 0 && $maxCheck >= 30){
                        TourModel::where('id',$data->id)->update(['promotion1'=>'Y','promotion2'=>'N']); // เป็นโปรไฟไหม้
                    }elseif($maxCheck > 0 && $maxCheck < 30){
                        TourModel::where('id',$data->id)->update(['promotion1'=>'N','promotion2'=>'Y']); // เป็นโปรธรรมดา
                    }else{
                        TourModel::where('id',$data->id)->update(['promotion1'=>'N','promotion2'=>'N']); // ไม่เป็นโปรโมชั่น
                    }

                    // ลบ Period
                    TourPeriodModel::where('tour_id',$data->id)->whereNotIn('id',$period_id)->update(['deleted_at'=>date('Y-m-d H:i:s')]);
                }

                DB::commit();
                return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder/edit/$data->id")]);
            } else {
                return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder")]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            dd($e->getLine(),$e->getMessage());
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

    // public function generateHeaderFooter($filename)
    // {
    //     // อ่านไฟล์ PDF
    //     // $pdf = PDF::loadFile(public_path('pdf/pdf10072023-15554007.pdf'));
    //     $pdf = PDF::loadFile(public_path('upload/tour/pdf_file/' . $filename));

    //     // กำหนด Header และ Footer
    //     $header = view("$this->prefix.pdf.header")->render();
    //     $footer = view("$this->prefix.pdf.footer")->render();

    //     // ตั้งค่า Header และ Footer ใน PDF
    //     $pdf->setOptions([
    //         'header-html' => $header,
    //         'footer-html' => $footer,
    //         'header-spacing' => 5,
    //         'footer-spacing' => 10,
    //     ]);

    //     // บันทึกไฟล์ PDF ทับไฟล์เดิม
    //     // $pdf->save(public_path('pdf/pdf10072023-15554007.pdf'));
    //     $pdf->save(public_path('upload/tour/pdf_file/' . $filename));
    // }

    public function status_edit($id = null)
    {
        $data = TourPeriodModel::find($id);
        $data->status_display = ($data->status_display == 'off') ? 'on' : 'off';
        if ($data->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function status($id = null)
    {
        $data = TourModel::find($id);
        $data->status = ($data->status == 'off') ? 'on' : 'off';
        if ($data->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function destroy(Request $request)
    {
        $datas = TourModel::find($request->id);

        if (!$datas) {
            return response()->json(false);
        }
        
        foreach ($datas as $data) {
            // Delete files
            Storage::disk('public')->delete([$data->image, $data->video_cover, $data->pdf_file, $data->word_file]);

            // Delete files In tour_detail column
            $jsonData = $data->tour_detail;

            $tourDetails = json_decode($jsonData, true);

            foreach ($tourDetails as $tourDetail) {
                foreach ($tourDetail['sub'] as $detail) {
                    $imagePath = $detail['image'];

                    if($imagePath){
                        Storage::disk('public')->delete($imagePath);
                    }
                }
            }
        
            // Soft delete
            $data->deleted_at = now();
        
            if ($data->save()) {
                // Delete associated gallery items and their images
                $gallery = TourGalleryModel::where('tour_id', $data->id)->get();
                foreach ($gallery as $gal) {
                    Storage::disk('public')->delete($gal->img);
                }
                TourGalleryModel::where('tour_id', $data->id)->delete();
        
                // Soft delete associated tour periods
                TourPeriodModel::where('tour_id', $data->id)->update(['deleted_at' => now()]);
            } else {
                return response()->json(false);
            }
        }
        
        return response()->json(true);
    }

    // deleted file
    public function destroy_pdf($id)
    {
        $datas = TourModel::find($id);
        if (@$datas) {
            $query = TourModel::where('id',$id)->update(['pdf_file'=> null]);
            Storage::disk('public')->delete($datas->pdf_file);
        }
        if (@$query) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function destroy_word($id)
    {
        $datas = TourModel::find($id);
        if (@$datas) {
            $query = TourModel::where('id',$id)->update(['word_file'=> null]);
            Storage::disk('public')->delete($datas->word_file);
        }
        if (@$query) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
    // end delete file

    public function change_status_display(Request $request) // ลบรายละเอียด
    {
        try {
            DB::beginTransaction();
            $tour_id = $request->tour_id;
            $data_period_id = explode(",",$request->data_period_id);

            foreach($data_period_id as $period_id){
                $data = TourPeriodModel::where(['tour_id'=>$tour_id,'id'=>$period_id])->first();
                $data->status_display = $request->display;
                $data->save();
            }

            DB::commit();
            $arr = [
                'status' => '200',
                'result' => 'success',
                'id' => $data->id,
                'message' => 'ดำเนินการสำเร็จ'
            ];
            return json_encode($arr);

        } catch (\Exception $e) {
            DB::rollback();
            dd($e->getMessage());
        }
        
    }
    
    public function change_status_period(Request $request) // ลบรายละเอียด
    {
        try {
            DB::beginTransaction();
            $tour_id = $request->tour_id;
            $data_period_id = explode(",",$request->data_period_id);

            foreach($data_period_id as $period_id){
                $data = TourPeriodModel::where(['tour_id'=>$tour_id,'id'=>$period_id])->first();
                $data->status_period = $request->status;
                $data->save();
            }

            DB::commit();
            $arr = [
                'status' => '200',
                'result' => 'success',
                'id' => $data->id,
                'message' => 'ดำเนินการสำเร็จ'
            ];
            return json_encode($arr);

        } catch (\Exception $e) {
            DB::rollback();
            dd($e->getMessage());
        }
        
    }

    public function edit_period(Request $request, $id)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = TourPeriodModel::find($id);
        $tour_id = $data->tour_id;
        $name = TourModel::find($tour_id);
        $navs = [
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลลูกค้าของเรา", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder/edit/$tour_id", 'name' => "ทัวร์ $name->name", "last" => 1],
            '2' => ['url' => "$this->segment/$this->folder/edit-period/$id", 'name' => "แก้ไขข้อมูล", "last" => 1],
        ];
       
        return view("$this->prefix.pages.$this->folder.edit-period", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'row' => $data,
            'menus' => \App\Models\Backend\MenuModel::where(['status' => 'on', 'position' => 'main'])->get(),
            'navs' => $navs,
            'period_status' => TourPeriodStatusModel::whereNull('deleted_at')->get(),
            'promotion' => PromotionModel::where('status','on')->whereNull('deleted_at')->get(),
            'tour_id'  => $tour_id,
            'id' => $id,
        ]);
    }
    public function update_period(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $data = TourPeriodModel::find($id);
            $data->group_date = date('mY',strtotime($request->start_date));
            $data->start_date = $request->start_date;
            $data->end_date = $request->end_date;
            $data->price1 = $request->price1;
            $data->special_price1 = $request->special_price1;
            $data->price2 = $request->price2;
            $data->special_price2 = $request->special_price2;
            $data->price3 = $request->price3;
            $data->special_price3 = $request->special_price3;
            $data->price4 = $request->price4;
            $data->special_price4 = $request->special_price4;
            $data->promotion_id = $request->promotion_id;
            $data->pro_start_date = $request->pro_start_date;
            $data->pro_end_date = $request->pro_end_date;
            $data->day = $request->day;
            $data->night = $request->night;
            $data->group = $request->group;
            $data->count = $request->count;
            $data->status_display = $request->status_display;
            $data->status_period = $request->status_period;

            if ($data->save()) {
                DB::commit();
                return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder/edit/".$data->tour_id)]);
            } else {
                return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder/edit/".$data->tour_id)]);
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
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

    public function destroy_period($id)
    {
        $datas = TourPeriodModel::find($id);
        if (@$datas) {
            $query = TourPeriodModel::destroy($datas->id);
        }
        if (@$query) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    //==== BEGIN: Gallery Data ====
    public function edit_gallery(Request $request, $id)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = TourGalleryModel::find($id);
        $tour_id = $data->tour_id;
        $name = TourModel::find($tour_id);
        $navs = [
            '0' => ['url' => "$this->segment/$this->folder", 'name' => "ข้อมูลลูกค้าของเรา", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder/edit/$tour_id", 'name' => "ทัวร์ $name->name", "last" => 1],
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
            'tour_id'  => $tour_id,
            'id' => $id,
        ]);
    }
    public function update_gallery(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $data = TourGalleryModel::find($id);
            $data->updated_at = date('Y-m-d H:i:s');
            
            $allow = ['png', 'jpeg', 'jpg'];
            if (!empty($request->file('image'))) {

                if (!empty($data->img)) {
                    Storage::disk('public')->delete($data->img);
                }

                $img = $request->file('image');
                $lg = Image::make($img->getRealPath());
                $ext = explode("/", $lg->mime());
                $lg->resize(600, 600)->stream();
                $new = 'upload/tour/tour-gallery-'. date('dmY-Hism') . '.' . $ext[1];
                if (in_array($ext[1], $allow)) {
                    $store = Storage::disk('public')->put($new, $lg);
                    $data->img             = $new;
                    $data->save();
                }
            }
            if ($data->save()) {
                DB::commit();
                return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder/edit/".$data->tour_id)]);
            } else {
                return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder/edit/".$data->tour_id)]);
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
        $datas = TourGalleryModel::find($id);
        if (@$datas) {
            $query = TourGalleryModel::destroy($datas->id);
            Storage::disk('public')->delete($datas->img);
        }
        if (@$query) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
    //==== END: Gallery Data ====

}
