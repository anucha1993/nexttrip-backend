<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Haruncpi\LaravelIdGenerator\IdGenerator;

use App\Models\Backend\SubscribeModel;
use App\Models\Backend\TermsModel;
use App\Models\Backend\CountryModel;
use App\Models\Backend\ProvinceModel;
use App\Models\Backend\TourModel;
use App\Models\Backend\TourGroupModel;
use App\Models\Backend\TourTypeModel;
use App\Models\Backend\TourDetailModel;
use App\Models\Backend\TourPeriodModel;
use App\Models\Backend\CalendarModel;
use App\Models\Backend\BookingFormModel;
use App\Models\Backend\CityModel;
use App\Models\Backend\User;
use App\Models\Backend\TravelTypeModel;
use App\Models\Backend\KeywordSearchModel;
use App\Models\Backend\AmupurModel;
use App\Models\Backend\ContactModel;
use App\Models\Backend\TagContentModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use Illuminate\Support\Facades\Auth;
use App\Models\Backend\MemberModel;

use DB;
use Session;
use Carbon\Carbon;

class FrontController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function wishlist($id)
    {
        // dd($id,$request);
        $data = array(
            'data' => TermsModel::where('status','on')->orderby('id','asc')->get(),
            'id' => $id,
        );
        return view('frontend.wishlist',$data);
    }
    public function wishlist_country(Request $request)
    {
        // dd($request);
        $likedTourIds = $request->likedTours?$request->likedTours:[];
        $dat = TourModel::whereIn('id',$likedTourIds)->where(['status'=>'on'])->whereNull('deleted_at')->orderby('id','desc')->get();
        $c_id = $request->c_id;
        $country_all = array();
        $province_all = array();
        $id_tour = array();
        $id_tourP = array();
        if($dat){
            foreach($dat as $da){
                if(json_decode($da->country_id,true) != null ){
                    $country_all = array_merge($country_all,json_decode($da->country_id,true));
                    foreach(json_decode($da->country_id,true) as $cid){
                        $id_tour[$cid][] = $da->id;
                        $id_tour[$cid] = array_unique($id_tour[$cid]);
                    }
                }if(json_decode($da->province_id,true) != null)
                    $province_all = array_merge($province_all,json_decode($da->province_id,true));
                    foreach(json_decode($da->province_id,true) as $cid){
                        $id_tourP[$cid][] = $da->id;
                        $id_tourP[$cid] = array_unique($id_tourP[$cid]);
                    }
               
            }
        }
        $country_all = array_unique($country_all);
        $province_all = array_unique($province_all);
        //dd($province_all,$country_all);
        $data = array(
            'data' => $dat,
            'country_all' => $country_all,
            'province_all' => $province_all,
            'c_id' => $c_id,
            'id_tour' => $id_tour,
            'id_tourP' => $id_tourP,
        );
        return view('frontend.wishlist_country',$data);
    }
    public function getLikedTours(Request $request)
    {
        $likedTourIds = $request->likedTours?$request->likedTours:[];
       
        //ฟิลเตอร์ประเทศ
        $country_id = $request->c_id;
        
        $dat = TourModel::whereIn('id',$likedTourIds)
        ->when($country_id, function ($query) use ($country_id){
                if ($country_id) {
                    $query->where('country_id', 'like', '%"' . $country_id . '"%');
                    $query->orWhere('province_id', 'like', '%"' . $country_id . '"%');
                }
            })
        ->where(['status'=>'on'])->whereNull('deleted_at')->orderby('id','desc')->get();
        //dd($likedTourIds,$dat);
        $data_like = TourModel::whereIn('id',$likedTourIds)->where(['status'=>'on'])->whereNull('deleted_at')->orderby('id','desc')->get(); // ทัวร์ที่แสดงอยู่

        $likedTourIds = array_map('intval', $likedTourIds);
        $invalidIds = array_diff($likedTourIds, $data_like->pluck('id')->toArray());
           
        $c = array();
        foreach($dat as $d){
            $c[] = $d->id;
        }

        $period = TourPeriodModel::whereIn('tour_id',$c)->where('status_display','on')->whereDate('start_date','>=',now())->whereNull('deleted_at')->get();
        
        $calendar = null;
        if($period){
            $min = $period->min('start_date');
            $max = $period->max('start_date');
            $datenow = '2023-07-11';
            if($min && $max){
                // $calendar = CalendarModel::whereYear('start_date','>=',date('Y',strtotime($min)))
                // ->whereMonth('start_date','>=',date('m',strtotime($min)))
                // ->whereDate('start_date','<=',$max)
                // ->where('status','on')
                // ->whereNull('deleted_at')
                // ->get();
                $calendar = CalendarModel::whereDate('start_date','>=',now())
                ->where('status','on')
                ->whereNull('deleted_at')
                ->get();
            }else{
                $calendar = null;
            }
        }

        if($calendar){
            $arr = array();
            foreach($calendar as $calen){
                $start = strtotime($calen->start_date);
                while ($start <= strtotime($calen->end_date)) {
                    $arr[] = date('Y-m-d',$start);
                    $start = $start + 86400;
                }
            }
        }else{
            $arr = null;
        }
        $data = array(
            'data' => $dat,
            'arr' => $arr,
            'invalidIds' => $invalidIds,
        );
        return view('frontend.wishlist_tour',$data);
    }

    public function subscribe(Request $request)
    {
        try {
            DB::beginTransaction();
            $email = SubscribeModel::where('email',$request->email)->first();
            if(!$email){
                $lastRecord = SubscribeModel::latest()->first();
                if(!$lastRecord || now()->diffInSeconds($lastRecord->created_at) > 10){
                    $data = new SubscribeModel;
                    $data->email = $request->email;
                    if ($data->save()) {
                        DB::commit();
                        return back()->with(['success' => 'บันทึกข้อมูลเรียบร้อย']);
                    } else {
                        return back()->with(['error' => 'เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง!!']);
                    }
                }else{
                    return back()->with(['error' => 'เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง!!']);
                }

            }else{
                return back()->with(['error' => 'เกิดข้อผิดพลาด อีเมลนี้มีในระบบแล้ว!!']);
            }
            

        } catch (Exception $e) {
            \DB::rollback();
            dd($e->getMessage());
        }
    }

    public function policy()
    {
        $data = array(
            'data' => TermsModel::where('status','on')->orderby('id','asc')->get(),
        );
        return view('frontend.policy',$data);
    }

    // public function filter_oversea(Request $request)
    // {
    //     $tour_id = json_decode($request->tour_id,true);
    //     $calendar = json_decode($request->calen_id,true);
    //     $orderby_data = '';
    //     $pe_data = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereNull('tb_tour.deleted_at');
    //     if($tour_id){
    //         $pe_data =  $pe_data->whereIn('tb_tour.id',$tour_id); 
    //     }
    //     // $calen = array(); 
    //     // if($request->slug){
    //     //     $pe_data = $pe_data->where('tb_tour.slug','like','%"'.$request->slug.'"%');
    //     // }
       
    //     if($request->data){
    //         if(isset($request->data['day'])){
    //             $pe_data = $pe_data->whereIn('tb_tour_period.day',$request->data['day']);
    //         }
    //         if(isset($request->data['price'])){
    //             $pe_data = $pe_data->whereIn('tb_tour.price_group',$request->data['price']);
    //         }
    //         if(isset($request->data['airline'])){
    //             $pe_data = $pe_data->whereIn('tb_tour.airline_id',$request->data['airline']);
    //         }
            
    //         if(isset($request->data['rating'])){
    //             if(!in_array(0,$request->data['rating'])){
    //                 $pe_data = $pe_data->whereIn('tb_tour.rating',$request->data['rating']);
    //             }else{
    //                 $pe_data = $pe_data->whereNull('tb_tour.rating');
    //             }
    //         }
    //         // dd($pe_data->get(),$request->data['rating']);
    //         if(isset($request->data['month_fil'])){
    //             $pe_data = $pe_data->whereIn('tb_tour_period.group_date',$request->data['month_fil']);
    //         }
    //         if(isset($request->data['calen_start'])){
    //             if($calendar){
    //                 foreach($calendar as $c => $calen_date){
    //                     if(in_array($c,$request->data['calen_start'])){
    //                     }else{
    //                         unset($calendar[$c]);
    //                     }
    //                 }
    //             }
                
    //         }
    //     }
    //     if($request->orderby){
    //         $orderby_data = $request->orderby;
    //         // ราคาถูกสุด
    //          if($request->orderby == 1){
    //             $pe_data = $pe_data->orderby('tb_tour.price','asc');
    //         }
    //         // ยอดวิวเยอะสุด
    //         if($request->orderby == 2){
    //             $pe_data = $pe_data->orderby('tb_tour.tour_views','desc');
    //         }
    //         // ลดราคา
    //         if($request->orderby == 3){
    //             $pe_data = $pe_data->where('tb_tour.special_price','>',0)->orderby('tb_tour.special_price','desc');
    //         }
    //         // โปรโมชั่น
    //         if($request->orderby == 4){
    //             $pe_data = $pe_data->whereNotNull('tb_tour_period.promotion_id')->whereDate('tb_tour_period.pro_start_date','<=',date('Y-m-d'))->whereDate('tb_tour_period.pro_end_date','>=',date('Y-m-d'));
    //         }
    //     }
    //     if($request->start_date && $request->end_date){
    //         if($request->start_date){
    //             $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',$request->start_date);
    //         }if($request->end_date){
    //             $pe_data = $pe_data->whereDate('tb_tour_period.end_date','<=',$request->end_date);
    //         }
    //     }
    //     else{
    //         $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',now());
    //     }
    //     // if($request->start_date_mb ){
    //     //     $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',$request->start_date_mb);
    //     // }if($request->end_date_mb){
    //     //     $pe_data = $pe_data->whereDate('tb_tour_period.start_date','<=',$request->end_date_mb);
    //     // }
    //     $pe_data = $pe_data->where('tb_tour.status','on')
    //         ->where('tb_tour_period.status_display','on')
    //         ->orderby('tb_tour_period.start_date','asc')
    //         ->orderby('tb_tour.rating','desc')
    //         ->select('tb_tour_period.*')
    //         ->get();
    //     if(isset($request->data['calen_start']) && $calendar){    
    //         $id_pe = array();
    //         foreach($pe_data as $da){
    //             $check_test = false;
    //             foreach($calendar as $cid => $calen){
    //                 $start_pe = strtotime($da->start_date);
    //                 while ($start_pe <= strtotime($da->end_date)) {
    //                     if(in_array(date('Y-m-d',$start_pe),$calen)){
    //                         $check_test = true;
    //                         break;
    //                     }
    //                     $start_pe = $start_pe + 86400;
    //                 }
    //             }
    //             if($check_test){
    //                 $id_pe[] = $da->id;
    //             }
    //         }
    //         $pe_data = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereIn('tb_tour_period.id',$id_pe)
    //         ->orderby('tb_tour_period.start_date','asc')
    //         ->orderby('tb_tour.rating','desc')
    //         ->select('tb_tour_period.*')
    //         ->get()
    //         ->groupBy('tour_id');
            
    //     }else{
    //         $pe_data = $pe_data->groupBy('tour_id');
    //     }
    //     // dd($pe_data);
    //     $period = array();
    //     foreach($pe_data as $k => $pe){
    //         $period[$k]['period']  = $pe;
    //         // dd($pe);
    //         $period[$k]['recomand'] = TourPeriodModel::where('tour_id',$k)
    //         ->where('status_display','on')->where('deleted_at',null)
    //         ->orderby('start_date','asc')
    //         ->limit(2)->get()->groupBy('group_date');
    //         $period[$k]['soldout'] = TourPeriodModel::where('tour_id',$k);
    //         if($request->data){
    //             if(isset($request->data['start_date'])){
    //                 $period[$k]['soldout'] = $period[$k]['soldout']->whereDate('start_date','>=',$request->data['start_date']);
    //             }
    //             if(isset($request->data['end_date'])){
    //                 $period[$k]['soldout'] = $period[$k]['soldout']->whereDate('start_date','<=',$request->data['end_date']);
    //             }
    //         }
    //         // ->whereDate('start_date','>=',$dat->start_date)
    //         // ->whereDate('start_date','<=',$dat->end_date)
    //         $period[$k]['soldout'] = $period[$k]['soldout']->where('status_period',3)->where('status_display','on')
    //         ->where('deleted_at',null)
    //         ->orderby('start_date','asc')
    //         ->get()->groupBy('group_date');
    //             $tour = TourModel::find($k);
    //             $period[$k]['tour'] = $tour;
    //             $period[$k]['country_id'] = json_decode($tour->country_id,true);
    //             $period[$k]['city_id'] = json_decode($tour->city_id,true);
                
    //             // $min = $pe->min('start_date');
    //             // $max = $pe->max('start_date');
    //             // // dd($min,$max);
    //             // if($min && $max){
    //             //         $calendar = CalendarModel::whereYear('start_date','>=',date('Y',strtotime($min)))
    //             //         ->whereMonth('start_date','>=',date('m',strtotime($min)))
    //             //         ->whereDate('start_date','<=',$max)
    //             //         ->where('status','on')
    //             //         ->whereNull('deleted_at')
    //             //         ->get();
    //             //     }else{
    //             //         $calendar = null;
    //             //     }
    //             // if($calendar){
    //             //     $arr = array();
    //             //     foreach($calendar as $calen){
    //             //         $start = strtotime($calen->start_date);
    //             //         while ($start <= strtotime($calen->end_date)) {
    //             //             $arr[$calen->id] = date('Y-m-d',$start);
    //             //             $start = $start + 86400;
    //             //         }
    //             //     }
    //             // }else{
    //             //     $arr = null;
    //             // }
                
    //     }
    //     // dd($arr,$calendar);
    //     $filter = array();
    //     foreach($period as $i => $per){
    //         if(isset($request->data['country'])){
    //             if(count(array_intersect($request->data['country'],$per['country_id'])) != count($request->data['country'])){
    //                 unset($period[$i]);
    //             }
    //             // if(!count(array_intersect($request->data['country'],$per['country_id']))){
    //             //     unset($period[$i]);
    //             // }
    //         }
    //         if(isset($request->data['city'])){
    //             if(count(array_intersect($request->data['city'],$per['city_id'])) != count($request->data['city'])){
    //                 unset($period[$i]);
    //             }
    //         }
    //        if(isset($period[$i])){
    //             //ช่วงราคา
    //             if(isset($filter['price'][$per['tour']->price_group])){
    //                 if(!in_array($per['tour']->id,$filter['price'][$per['tour']->price_group])){
    //                     $filter['price'][$per['tour']->price_group][] = $per['tour']->id;
    //                 }
    //             }else{
    //                 $filter['price'][$per['tour']->price_group][] = $per['tour']->id;
    //             }
    //             //จำนวนวัน
    //             foreach($per['period']  as $p){
    //                 // dd($per['period'] );
    //                 if($p->day){
    //                     if(isset($filter['day'][$p->day])){
    //                         if(!in_array($per['tour']->id,$filter['day'][$p->day])){
    //                             $filter['day'][$p->day][] = $per['tour']->id;
    //                         }
    //                     }else{
    //                         $filter['day'][$p->day][] = $per['tour']->id;
    //                     }
    //                 }
    //                 if($p->start_date){
    //                     //ช่วงเดือน
    //                     $month_start = date('n',strtotime($p->start_date));
    //                     //ช่วงเดือน-ปี
    //                     $year_start = date('Y',strtotime($p->start_date));
    //                     if(isset($filter['year'][$year_start][$month_start])){
    //                         if(!in_array($per['tour']->id,$filter['year'][$year_start][$month_start])){
    //                             $filter['year'][$year_start][$month_start][] = $per['tour']->id;
    //                         }
    //                     }else{
    //                         $filter['year'][$year_start][$month_start][] = $per['tour']->id;
    //                     }
                        
    //                 }
    //                 //วันหยุดเทศกาล
    //                 // if($p->tour_id == 98){
    //                 //     dd($p->tour_id);
    //                 // }
    //                 // dd($calendar);
    //                 //วันหยุดเทศกาล
    //                 if($calendar){
    //                     foreach($calendar as $cid => $calen){
    //                         $start_pe = strtotime($p->start_date);
    //                         while ($start_pe <= strtotime($p->end_date)) {
    //                             // echo date('Y-m-d',$start_pe).$tour->tour_id."<br>";
    //                             if(in_array(date('Y-m-d',$start_pe),$calen)){
    //                                 // dd($p);
    //                                 if(isset($filter['calendar'][$cid])){
    //                                     if(!in_array($p->tour_id,$filter['calendar'][$cid])){
    //                                             $filter['calendar'][$cid][] = $p->tour_id;
    //                                         }
    //                                     }else{
    //                                         $filter['calendar'][$cid][] = $p->tour_id;
    //                                     }
    //                             }
    //                             $start_pe = $start_pe + 86400;
    //                         }
    //                     }
    //                 }
                    
    //             }
    //             //ประเทศ
    //             if($per['tour']->country_id){
    //                 if(isset($filter['country'])){
    //                     $filter['country'] = array_merge($filter['country'],json_decode($per['tour']->country_id,true));
    //                     $filter['country'] = array_unique($filter['country']);
    //                 }else{
    //                     $filter['country'] = json_decode($per['tour']->country_id,true);
    //                 }
    //             }
    //             //เมือง
    //             if($per['tour']->city_id){
    //                 if(isset($filter['city'])){
    //                     $filter['city'] = array_merge($filter['city'],json_decode($per['tour']->city_id,true));
    //                     $filter['city'] = array_unique($filter['city']);
    //                 }else{
    //                     $filter['city'] = json_decode($per['tour']->city_id,true);
    //                 }
    //             }
    //             //สายการบิน
    //              if($per['tour']->airline_id){
    //                 $filter['airline'][$per['tour']->airline_id][] = $per['tour']->id;
    //             }
               
    //             //ดาว
    //             $filter['rating'][$per['tour']->rating][] = $per['tour']->rating ;
                   
    //        }
    //     }

    //     // dd($filter,$request->data);
    //     $num_price = 0;
    //     if(isset($filter['price'])){
    //         foreach($filter['price'] as $p){
    //             $num_price =  $num_price + count($p);
    //         }
    //     }
      
    //     $row = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id');
    //     if($request->data){
    //         if(isset($request->data['start_date']) && isset($request->data['end_date'])){
    //             $row = $row->whereDate('tb_tour_period.start_date','>=',$request->data['start_date'])->whereDate('tb_tour_period.end_date','<=',$request->data['end_date']);
    //         }
    //         // if(isset($request->data['end_date'])){
    //         //     $row = $row ->whereDate('tb_tour_period.start_date','<=',$request->data['end_date']);
    //         // }
    //     }
        
    //     $row = $row->where('tb_tour.status','on')
    //     ->where('tb_tour_period.status_display','on')
    //     ->orderby('tb_tour_period.start_date','asc')
    //     ->select('tb_tour_period.*')
    //     ->get()
    //     ->groupBy('tour_id');

    //     $count_pe = count($period);

    //     $data = array(
    //         // 'data' => $dat,
    //         'period' => $period,
    //         'calen_id' => $request->id,
    //         'slug' => $request,
    //         'row' => $row,
    //         'filter' => $filter,
    //         'airline_data' => TravelTypeModel::/* where('status','on')-> */where('deleted_at',null)->get(),
    //         'num_price' => $num_price,
    //         'tour_list' => $this->search_filter($request,$period),
    //         'tour_grid' => $this->search_filter_grid($request,$period),
    //         'count_pe' => $count_pe,
    //         'orderby_data' =>  $orderby_data,
    //     );
    //     return response()->json($data);
       
    // }
    // public function oversea(Request $request,$main_slug)
    // {
    //     // dd(Session::get('data'));
    //     try {

    //         $search_price = '';
    //         $search_start = '';
    //         $search_end = '';
    //         $search_city = '';
    //         if($request){
    //             $search_data = Session::get('data');
    //             if(isset($search_data)){
    //                 if($search_data){
    //                     $search_price = $search_data['price'];
    //                     $search_start = $search_data['start_date'];
    //                     $search_end = $search_data['end_date'];
    //                     $search_city = $search_data['city_id'];
    //                 }
    //             }
    //         }
    //         // dd($search_data,$search_price,$search_start,$search_end,$search_city);

    //         $country = CountryModel::where('slug',$main_slug)->whereNull('deleted_at')->first();
    //         $orderby_data = '';
    //         $dat = TourModel::where('country_id', 'LIKE', '%"'.$country->id.'"%');
    //         if(isset($search_data['price'])){
    //             if($search_data['price']){
    //                 $dat = $dat->where('price_group',$search_data['price']*1);
    //             }
    //         }
    //         if(isset($search_data['start_date'])  && isset($search_data['end_date'])){
    //             if($search_data['start_date'] && $search_data['end_date']){
    //                 $check_period = array();
    //                 $check_p = TourPeriodModel::whereDate('start_date','>=',$search_data['start_date'])->whereDate('start_date','<=',$search_data['end_date'])->get(['tour_id']);
    //                 foreach($check_p  as $check){
    //                     $check_period[] = $check->tour_id;
    //                 }
    //                 if(count($check_period)){
    //                     $dat = $dat->whereIn('id',$check_period);
    //                 }  
    //             }
    //         }
    //         if($request){
               
    //             $orderby_data = $request->orderby;
    //             // ราคาถูกสุด
    //             if($request->orderby == 1){
    //                 $dat = $dat->orderby('price','asc');
    //             }
    //             // ยอดวิวเยอะสุด
    //             if($request->orderby == 2){
    //                 $dat = $dat->orderby('tour_views','desc');
    //             }
    //             //ลดราคา
    //             if($request->orderby == 3){
    //                 // $check_period = array();
    //                 // $check_p = TourPeriodModel::where('special_price1','>',0)->orwhere('special_price2','>',0)->orwhere('special_price3','>',0)->orwhere('special_price4','>',0)->get(['tour_id']);
    //                 // foreach($check_p  as $check){
    //                 //     $check_period[] = $check->tour_id;
    //                 // }
    //                 // if(count($check_period)){
    //                 //     $dat = $dat->whereIn('id',$check_period);
    //                 // }  
    //                 $dat = $dat->where('special_price','>',0)->orderby('special_price','desc');
    //                 // dd($check_period);
    //             }
    //             if($request->orderby == 4){
    //                 $check_period = array();
    //                 $check_p = TourPeriodModel::whereNotNull('promotion_id')->whereDate('pro_start_date','<=',date('Y-m-d'))->whereDate('pro_end_date','>=',date('Y-m-d'))->get(['tour_id']);
    //                 foreach($check_p  as $check){
    //                     $check_period[] = $check->tour_id;
    //                 }
    //                 // if(count($check_period)){
    //                     $dat = $dat->whereIn('id',$check_period)/* ->where('promotion2','Y') */;
    //                 // }
    //             }
    //             if($request->type){
    //                 $dat = $dat->where('type_id',$request->type*1);
    //             }
    //         }
    //         $dat = $dat->where(['status'=>'on'])->whereNull('deleted_at')->orderby('id','desc')->get();
    //         $c = array();
    //         foreach($dat as $d){
    //             $c[] = $d->id;
    //         }
    //         $dat_tour = TourModel::whereIn('id',$c)->paginate(10);
    //         $period = TourPeriodModel::whereIn('tour_id',$c)
    //                 ->whereDate('start_date','>=',now());
    //         if(isset($search_data['start_date'])  && isset($search_data['end_date'])){
    //             if($search_data['start_date'] && $search_data['end_date']){
    //                 $period = $period->whereDate('start_date','>=',$search_data['start_date'])->whereDate('start_date','<=',$search_data['end_date']);
    //             }
    //             // else{
    //             //     $period = $period->whereDate('start_date','>=',now()); 
    //             // }
    //         }
    //         // else{
    //         //     $period = $period->whereDate('start_date','>=',now());
    //         // }
    //         $period = $period->whereNull('deleted_at')->where('status_display','on')->get();
    //         // dd($dat,$period,$search_data);
    //         $calendar = null;
    //         if($period){
    //             $min = $period->min('start_date');
    //             $max = $period->max('start_date');
    //             $datenow = '2023-07-11';
    //             if($min && $max){
    //                 $calendar = CalendarModel::whereYear('start_date','>=',date('Y',strtotime($min)))
    //                 ->whereMonth('start_date','>=',date('m',strtotime($min)))
    //                 ->whereDate('start_date','<=',$max)
    //                 ->where('status','on')
    //                 ->whereNull('deleted_at')
    //                 ->get();
    //             }else{
    //                 $calendar = null;
    //             }
    //         }
        
    //         if($calendar){
    //             $arr = array();
    //             foreach($calendar as $calen){
    //                 $start = strtotime($calen->start_date);
    //                 while ($start <= strtotime($calen->end_date)) {
    //                     $arr[$calen->id][] = date('Y-m-d',$start);
    //                     $start = $start + 86400;
    //                 }
    //             }
    //         }else{
    //             $arr = [];
    //         }
    //         // Filter
    //         $filter = array();
    //         if($period){
    //             foreach($period as $tour){
    //                 $tour_fill = TourModel::find($tour->tour_id);
    //             //ช่วงราคา
    //             if(isset($search_data['price'])){
    //                     $filter['price'][$search_data['price']][] = $tour->tour_id;
    //             }else{
    //                     if(isset($filter['price'][$tour_fill->price_group])){
    //                         if(!in_array($tour->tour_id,$filter['price'][ $tour_fill->price_group])){
    //                             $filter['price'][$tour_fill->price_group][] = $tour->tour_id;
    //                         }
    //                     }else{
    //                         $filter['price'][$tour_fill->price_group][] = $tour->tour_id;
    //                     }
    //                 }
    //                 // foreach($period as $p){
    //                     //จำนวนวัน
    //                     if($tour->day){
    //                         if(isset($filter['day'][$tour->day])){
    //                             if(!in_array($tour->tour_id,$filter['day'][$tour->day])){
    //                                 $filter['day'][$tour->day][] = $tour->tour_id;
    //                             }
    //                         }else{
    //                             $filter['day'][$tour->day][] = $tour->tour_id;
    //                         }
    //                     }
                    
    //                     if($tour->start_date){
    //                         //ช่วงเดือน
    //                         $month_start = date('n',strtotime($tour->start_date));
    //                         // if(isset($filter['month'][$month_start])){
    //                         //     if(!in_array($tour->tour_id,$filter['month'][$month_start])){
    //                         //         $filter['month'][$month_start][] = $tour->tour_id;
    //                         //     }
    //                         // }else{
    //                         //     $filter['month'][$month_start][] = $tour->tour_id;
    //                         // }
    //                         //ช่วงเดือน-ปี
    //                         $year_start = date('Y',strtotime($tour->start_date));
    //                         if(isset($filter['year'][$year_start][$month_start])){
    //                             if(!in_array($tour->tour_id,$filter['year'][$year_start][$month_start])){
    //                                 $filter['year'][$year_start][$month_start][] = $tour->tour_id;
    //                             }
    //                         }else{
    //                             $filter['year'][$year_start][$month_start][] = $tour->tour_id;
    //                         }
    //                         //วันหยุดเทศกาล
    //                         foreach($arr as $cid => $calen){
    //                             $start_pe = strtotime($tour->start_date);
    //                             while ($start_pe <= strtotime($tour->end_date)) {
    //                                 // echo date('Y-m-d',$start_pe).$tour->tour_id."<br>";
    //                                 if(in_array(date('Y-m-d',$start_pe),$calen)){
    //                                     // dd($tour);
    //                                     if(isset($filter['calendar'][$cid])){
    //                                         if(!in_array($tour->tour_id,$filter['calendar'][$cid])){
    //                                                 $filter['calendar'][$cid][] = $tour->tour_id;
    //                                             }
    //                                         }else{
    //                                             $filter['calendar'][$cid][] = $tour->tour_id;
    //                                         }
    //                                 }
    //                                 $start_pe = $start_pe + 86400;
    //                             }
    //                             // dd($calen);
    //                             // if($tour->start_date <= $calen || $tour->end_date >= $calen){
    //                             // if(strtotime($tour->start_date) <= strtotime($calen) && strtotime($tour->end_date) >= strtotime($calen)){
    //                             //     // dd(strtotime($tour->start_date),strtotime($calen),strtotime($tour->end_date),$calen);
    //                             //     if(isset($filter['calendar'][$cid])){
                                    
    //                             //         if(!in_array($tour->tour_id,$filter['calendar'][$cid])){
    //                             //             $filter['calendar'][$cid][] = $tour->tour_id;
    //                             //         }
    //                             //     }else{
    //                             //         $filter['calendar'][$cid][] = $tour->tour_id;
    //                             //     }
    //                             // }
    //                         }
    //                         // dd($arr);
    //                     }
    //                 // }
    //                 //เมือง
    //                 if($tour_fill->city_id){
    //                     $filter['city'][$tour->tour_id] = $tour_fill->city_id;
    //                 }
    //                 //สายการบิน
    //                 if($tour_fill->airline_id){
    //                     if(isset($filter['airline'][$tour_fill->airline_id])){
    //                         if(!in_array($tour->tour_id,$filter['airline'][$tour_fill->airline_id])){
    //                             $filter['airline'][$tour_fill->airline_id][] = $tour->tour_id;
    //                         }
    //                     }else{
    //                         $filter['airline'][$tour_fill->airline_id][] = $tour->tour_id;
    //                     }
    //                 }
    //                 //ดาว
    //                 if($tour_fill->rating*1 == 5 ){
    //                     if(isset($filter['rating'][0])){
    //                         if(!in_array($tour->tour_id,$filter['rating'][0])){
    //                             $filter['rating'][0][] = $tour->tour_id;
    //                         }
    //                     }else{
    //                         $filter['rating'][0][] = $tour->tour_id;
    //                     }
    //                 }else if($tour_fill->rating*1 == 4){
    //                     if(isset($filter['rating'][1])){
    //                         if(!in_array($tour->tour_id,$filter['rating'][1])){
    //                             $filter['rating'][1][] = $tour->tour_id;
    //                         }
    //                     }else{
    //                         $filter['rating'][1][] = $tour->tour_id;
    //                     }
    //                 }else if($tour_fill->rating*1 == 3){
                    
    //                     if(isset($filter['rating'][2])){
    //                         if(!in_array($tour->tour_id,$filter['rating'][2])){
    //                             $filter['rating'][2][] = $tour->tour_id;
    //                         }
    //                     }else{
    //                         $filter['rating'][2][] = $tour->tour_id;
    //                     }
    //                 }else if($tour_fill->rating*1 == 2){
    //                     // dd($filter);
    //                     if(isset($filter['rating'][3])){
    //                         if(!in_array($tour->tour_id,$filter['rating'][3])){
    //                             $filter['rating'][3][] = $tour->tour_id;
    //                         }
    //                     }else{
    //                         $filter['rating'][3][] = $tour->tour_id;
    //                     }
    //                 }else if($tour_fill->rating*1 == 1){
    //                     if(isset($filter['rating'][4])){
    //                         if(!in_array($tour->tour_id,$filter['rating'][4])){
    //                             $filter['rating'][4][] = $tour->tour_id;
    //                         }
    //                     }else{
    //                         $filter['rating'][4][] = $tour->tour_id;
    //                     }
    //                 }else if($tour_fill->rating*1 == 0 || $tour_fill->rating == null){
    //                     if(isset($filter['rating'][5])){
    //                         if(!in_array($tour->tour_id,$filter['rating'][5])){
    //                             $filter['rating'][5][] = $tour->tour_id;
    //                         }
    //                     }else{
    //                         $filter['rating'][5][] = $tour->tour_id;
    //                     }
    //                 } 
    //             }
    //             // dd($filter,$dat);
    //         }else{}
    //         // dd($dat,$period);
    //         $num_price = 0;
    //         if(isset($filter['price'])){
    //             foreach($filter['price'] as $p){
    //                 $num_price =  $num_price + count($p);
    //             }
    //         }
    //         // dd($filter);

    //         // Platform check 
    //         $isWin = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows")):0; 
    //         $isMac = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mac"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mac")):0; 
    //         $isAndroid = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android")):0; 
    //         $isIPhone = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone")):0; 
    //         $isIPad = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad")):0; 
            
    //         $data = array(
    //             'tour_id' => $c,
    //             'country' => $country,
    //             'data' => $dat_tour,
    //             'arr' => $arr,
    //             'filter' => $filter,
    //             'airline_data' => TravelTypeModel::/* where('status','on')-> */where('deleted_at',null)->get(),
    //             'num_price' => $num_price,
    //             'main_slug' => $main_slug,
    //             'search_price' => $search_price,
    //             'search_start' => $search_start,
    //             'search_end' => $search_end,
    //             'search_city' => $search_city,
    //             'orderby_data'  => $orderby_data ,
    //             'isWin' => $isWin,
    //             'isMac' => $isMac,
    //             'isAndroid' => $isAndroid,
    //             'isIPhone' => $isIPhone,
    //             'isIPad' => $isIPad,
    //         );
    //         return view('frontend.oversea',$data);
    //     } catch (\Throwable $th) {
    //         // dd($th);
    //     }
        
    // }
    // public function filter_inthai(Request $request)
    // {
    //     $tour_id = json_decode($request->tour_id,true);
    //     $calendar = json_decode($request->calen_id,true);
    //     $orderby_data = '';
    //     $pe_data = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereNull('tb_tour.deleted_at');
    //     if($tour_id){
    //         $pe_data =  $pe_data->whereIn('tb_tour.id',$tour_id); 
    //     }
    //     if($request->data){
    //         if(isset($request->data['day'])){
    //             $pe_data = $pe_data->whereIn('tb_tour_period.day',$request->data['day']);
    //         }
    //         if(isset($request->data['price'])){
    //             $pe_data = $pe_data->whereIn('tb_tour.price_group',$request->data['price']);
    //         }
    //         if(isset($request->data['airline'])){
    //             $pe_data = $pe_data->whereIn('tb_tour.airline_id',$request->data['airline']);
    //         }
    //         if(isset($request->data['rating'])){
    //             if(!in_array(0,$request->data['rating'])){
    //                 $pe_data = $pe_data->whereIn('tb_tour.rating',$request->data['rating']);
    //             }else{
    //                 $pe_data = $pe_data->whereNull('tb_tour.rating');
    //             }
    //         }if(isset($request->data['month_fil'])){
    //             $pe_data = $pe_data->whereIn('tb_tour_period.group_date',$request->data['month_fil']);
    //         }
         
    //         if(isset($request->data['calen_start'])){
    //             if($calendar){
    //                 foreach($calendar as $c => $calen_date){
    //                     if(in_array($c,$request->data['calen_start'])){
    //                     }else{
    //                         unset($calendar[$c]);
    //                     }
    //                 }
    //             }
                
    //         }
    //     }
    //     if($request->orderby){
    //         $orderby_data = $request->orderby;
    //         // ราคาถูกสุด
    //          if($request->orderby == 1){
    //             $pe_data = $pe_data->orderby('tb_tour.price','asc');
    //         }
    //         // ยอดวิวเยอะสุด
    //         if($request->orderby == 2){
    //             $pe_data = $pe_data->orderby('tb_tour.tour_views','desc');
    //         }
    //         // ลดราคา
    //         if($request->orderby == 3){
    //             $pe_data = $pe_data->where('tb_tour.special_price','>',0)->orderby('tb_tour.special_price','desc');
    //         }
    //         // โปรโมชั่น
    //         if($request->orderby == 4){
    //             $pe_data = $pe_data->whereNotNull('tb_tour_period.promotion_id')->whereDate('tb_tour_period.pro_start_date','<=',date('Y-m-d'))->whereDate('tb_tour_period.pro_end_date','>=',date('Y-m-d'));
    //         }
    //     }
    //     if($request->start_date && $request->end_date){
    //         if($request->start_date){
    //             $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',$request->start_date);
    //         }if($request->end_date){
    //             $pe_data = $pe_data->whereDate('tb_tour_period.end_date','<=',$request->end_date);
    //         }
    //     }else{
    //         $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',now());
    //     }
    //     // if($request->start_date){
    //     //     $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',$request->start_date);
    //     // }if($request->end_date){
    //     //     $pe_data = $pe_data->whereDate('tb_tour_period.end_date','<=',$request->end_date);
    //     // }
    //     // // if($request->start_date_mb ){
    //     // //     $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',$request->start_date_mb);
    //     // // }if($request->end_date_mb){
    //     // //     $pe_data = $pe_data->whereDate('tb_tour_period.start_date','<=',$request->end_date_mb);
    //     // // }
    //     // else{
    //     //     $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',now());
    //     // }
    //     $pe_data = $pe_data->where('tb_tour.status','on')
    //         ->where('tb_tour_period.status_display','on')
    //         ->orderby('tb_tour_period.start_date','asc')
    //         ->orderby('tb_tour.rating','desc')
    //         ->select('tb_tour_period.*')
    //         ->get();
      
    //     if(isset($request->data['calen_start']) && $calendar){    
    //         $id_pe = array();
    //         foreach($pe_data as $da){
    //             $check_test = false;
    //             foreach($calendar as $cid => $calen){
    //                 $start_pe = strtotime($da->start_date);
    //                 while ($start_pe <= strtotime($da->end_date)) {
    //                     if(in_array(date('Y-m-d',$start_pe),$calen)){
    //                         $check_test = true;
    //                         break;
    //                     }
    //                     $start_pe = $start_pe + 86400;
    //                 }
    //             }
    //             if($check_test){
    //                 $id_pe[] = $da->id;
    //             }
    //         }
    //         $pe_data = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereIn('tb_tour_period.id',$id_pe)
    //         ->orderby('tb_tour_period.start_date','asc')
    //         ->orderby('tb_tour.rating','desc')
    //         ->select('tb_tour_period.*')
    //         ->get()
    //         ->groupBy('tour_id');
    //     }else{
    //         $pe_data = $pe_data->groupBy('tour_id');
    //     }
    //     $period = array();
    //     foreach($pe_data as $k => $pe){
    //         $period[$k]['period']  = $pe;
    //         $period[$k]['recomand'] = TourPeriodModel::where('tour_id',$k)
    //         ->where('status_display','on')->where('deleted_at',null)
    //         ->orderby('start_date','asc')
    //         ->limit(2)->get()->groupBy('group_date');
    //         $period[$k]['soldout'] = TourPeriodModel::where('tour_id',$k);
    //         if($request->data){
    //             if(isset($request->data['start_date'])){
    //                 $period[$k]['soldout'] = $period[$k]['soldout']->whereDate('start_date','>=',$request->data['start_date']);
    //             }
    //             if(isset($request->data['end_date'])){
    //                 $period[$k]['soldout'] = $period[$k]['soldout']->whereDate('start_date','<=',$request->data['end_date']);
    //             }
    //         }
    //         $period[$k]['soldout'] = $period[$k]['soldout']->where('status_period',3)->where('status_display','on')
    //         ->where('deleted_at',null)
    //         ->orderby('start_date','asc')
    //         ->get()->groupBy('group_date');
    //             $tour = TourModel::find($k);
    //             $period[$k]['tour'] = $tour;
    //             $period[$k]['country_id'] = json_decode($tour->province_id,true);
    //             $period[$k]['city_id'] = json_decode($tour->district_id,true);
    //     }
      
    //     $filter = array();
    //     foreach($period as $i => $per){
    //         if(isset($request->data['country'])){
    //             if(count(array_intersect($request->data['country'],$per['country_id'])) != count($request->data['country'])){
    //                 unset($period[$i]);
    //             }
    //         }
    //         if(isset($request->data['city'])){
    //             if(count(array_intersect($request->data['city'],$per['city_id'])) != count($request->data['city'])){
    //                 unset($period[$i]);
    //             }
    //         }
    //        if(isset($period[$i])){
    //             //ช่วงราคา
    //             if(isset($filter['price'][$per['tour']->price_group])){
    //                 if(!in_array($per['tour']->id,$filter['price'][$per['tour']->price_group])){
    //                     $filter['price'][$per['tour']->price_group][] = $per['tour']->id;
    //                 }
    //             }else{
    //                 $filter['price'][$per['tour']->price_group][] = $per['tour']->id;
    //             }
    //             //จำนวนวัน
    //             foreach($per['period']  as $p){
    //                 // dd($per['period'] );
    //                 if($p->day){
    //                     if(isset($filter['day'][$p->day])){
    //                         if(!in_array($per['tour']->id,$filter['day'][$p->day])){
    //                             $filter['day'][$p->day][] = $per['tour']->id;
    //                         }
    //                     }else{
    //                         $filter['day'][$p->day][] = $per['tour']->id;
    //                     }
    //                 }
    //                 if($p->start_date){
    //                     //ช่วงเดือน
    //                     $month_start = date('n',strtotime($p->start_date));
    //                     //ช่วงเดือน-ปี
    //                     $year_start = date('Y',strtotime($p->start_date));
    //                     if(isset($filter['year'][$year_start][$month_start])){
    //                         if(!in_array($per['tour']->id,$filter['year'][$year_start][$month_start])){
    //                             $filter['year'][$year_start][$month_start][] = $per['tour']->id;
    //                         }
    //                     }else{
    //                         $filter['year'][$year_start][$month_start][] = $per['tour']->id;
    //                     }
                        
    //                 }
    //                 //วันหยุดเทศกาล
    //                 if($calendar){
    //                     foreach($calendar as $cid => $calen){
    //                         $start_pe = strtotime($p->start_date);
    //                         while ($start_pe <= strtotime($p->end_date)) {
    //                             // echo date('Y-m-d',$start_pe).$tour->tour_id."<br>";
    //                             if(in_array(date('Y-m-d',$start_pe),$calen)){
    //                                 // dd($p);
    //                                 if(isset($filter['calendar'][$cid])){
    //                                     if(!in_array($p->tour_id,$filter['calendar'][$cid])){
    //                                             $filter['calendar'][$cid][] = $p->tour_id;
    //                                         }
    //                                     }else{
    //                                         $filter['calendar'][$cid][] = $p->tour_id;
    //                                     }
    //                             }
    //                             $start_pe = $start_pe + 86400;
    //                         }
    //                     }
    //                 }
                    
    //             }
    //             //ประเทศ
    //             if($per['tour']->province_id){
    //                 if(isset($filter['country'])){
    //                     $filter['country'] = array_merge($filter['country'],json_decode($per['tour']->province_id,true));
    //                     $filter['country'] = array_unique($filter['country']);
    //                 }else{
    //                     $filter['country'] = json_decode($per['tour']->province_id,true);
    //                 }
    //             }
    //             //เมือง
    //             if($per['tour']->district_id){
    //                 if(isset($filter['city'])){
    //                     $filter['city'] = array_merge($filter['city'],json_decode($per['tour']->district_id,true));
    //                     $filter['city'] = array_unique($filter['city']);
    //                 }else{
    //                     $filter['city'] = json_decode($per['tour']->district_id,true);
    //                 }
    //             }
    //             //สายการบิน
    //              if($per['tour']->airline_id){
    //                 $filter['airline'][$per['tour']->airline_id][] = $per['tour']->id;
    //             }
               
    //             //ดาว
    //             $filter['rating'][$per['tour']->rating][] = $per['tour']->rating ;
                   
    //        }
    //     }

    //     // dd($filter);
    //     $num_price = 0;
    //     if(isset($filter['price'])){
    //         foreach($filter['price'] as $p){
    //             $num_price =  $num_price + count($p);
    //         }
    //     }
      
    //     $row = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id');
    //     if($request->data){
    //         if(isset($request->data['start_date']) && isset($request->data['end_date'])){
    //             $row = $row->whereDate('tb_tour_period.start_date','>=',$request->data['start_date'])->whereDate('tb_tour_period.end_date','<=',$request->data['end_date']);
    //         }
    //         // if(isset($request->data['end_date'])){
    //         //     $row = $row ->whereDate('tb_tour_period.start_date','<=',$request->data['end_date']);
    //         // }
    //     }
        
    //     $row = $row->where('tb_tour.status','on')
    //     ->where('tb_tour_period.status_display','on')
    //     ->orderby('tb_tour_period.start_date','asc')
    //     ->select('tb_tour_period.*')
    //     ->get()
    //     ->groupBy('tour_id');

    //     $count_pe = count($period);
       
    //     $data = array(
    //         // 'data' => $dat,
    //         'period' => $period,
    //         'calen_id' => $request->id,
    //         'slug' => $request,
    //         'row' => $row,
    //         'filter' => $filter,
    //         'airline_data' => TravelTypeModel::/* where('status','on')-> */where('deleted_at',null)->get(),
    //         'num_price' => $num_price,
    //         'tour_list' => $this->search_filter($request,$period),
    //         'tour_grid' => $this->search_filter_grid($request,$period),
    //         'count_pe' => $count_pe,
    //         'orderby_data' => $orderby_data,
    //     );
    //     return response()->json($data);
       
    // }
    // public function inthai(Request $request,$main_slug)
    // {
    //     $search_price = '';
    //     $search_start = '';
    //     $search_end = '';
    //     $search_city = '';
    //     if($request){
    //         $search_data = Session::get('data');
    //         if(isset($search_data)){
    //             if($search_data){
    //                 $search_price = $search_data['price'];
    //                 $search_start = $search_data['start_date'];
    //                 $search_end = $search_data['end_date'];
    //                 $search_city = $search_data['city_id'];
    //             }
    //         }
    //     }
    //     $orderby_data = '';
    //     $province = ProvinceModel::where('slug',$main_slug)->whereNull('deleted_at')->first();
    //     $dat = TourModel::where('province_id', 'LIKE', '%"'.$province->id.'"%');
    //     if(isset($search_data['price'])){
    //         if($search_data['price']){
    //             $dat = $dat->where('price_group',$search_data['price']);
    //         }
    //     }
    //     if($request){
    //         $orderby_data = $request->orderby;
    //         // ราคาถูกสุด
    //         if($request->orderby == 1){
    //             $dat = $dat->orderby('price','asc');
    //         }
    //         // ยอดวิวเยอะสุด
    //         if($request->orderby == 2){
    //             $dat = $dat->orderby('tour_views','desc');
    //         }
    //         //ลดราคา
    //         if($request->orderby == 3){
    //             // $check_period = array();
    //             // $check_p = TourPeriodModel::where('special_price1','>',0)->orwhere('special_price2','>',0)->orwhere('special_price3','>',0)->orwhere('special_price4','>',0)->get(['tour_id']);
    //             // foreach($check_p  as $check){
    //             //     $check_period[] = $check->tour_id;
    //             // }
    //             // if(count($check_period)){
    //             //     $dat = $dat->whereIn('id',$check_period);
    //             // }  
    //             $dat = $dat->where('special_price','>',0)->orderby('special_price','desc');
    //         }
    //         //มีโปรโมชั่น
    //         if($request->orderby == 4){
    //             $check_period = array();
    //             $check_p = TourPeriodModel::whereNotNull('promotion_id')->whereDate('pro_start_date','<=',date('Y-m-d'))->whereDate('pro_end_date','>=',date('Y-m-d'))->get(['tour_id']);
    //             foreach($check_p  as $check){
    //                 $check_period[] = $check->tour_id;
    //             }
    //             // if(count($check_period)){
    //                 $dat = $dat->whereIn('id',$check_period)/* ->where('promotion2','Y') */;
    //             // }  
    //         }
    //     }
    //     $dat = $dat->where(['status'=>'on'])->whereNull('deleted_at')->orderby('id','desc')->get();
    //     // dd($dat);
    //     $c = array();
    //     foreach($dat as $d){
    //         $c[] = $d->id;
    //     }
    //     $dat_tour = TourModel::whereIn('id',$c)->paginate(10);
    //     $period = TourPeriodModel::whereIn('tour_id',$c);
    //     if(isset($search_data['start_date'])  && isset($search_data['end_date'])){
    //         if($search_data['start_date'] && $search_data['end_date']){
    //             $period = $period->whereDate('start_date','>=',$search_data['start_date'])->whereDate('start_date','<=',$search_data['end_date']);
    //         }else{
    //             $period = $period->whereDate('start_date','>=',now()); 
    //         }
    //     }else{
    //         $period = $period->whereDate('start_date','>=',now());
    //     }
    //     $period = $period->whereNull('deleted_at')->where('status_display','on')->get();
       
    //     $calendar = null;
    //     if($period){
    //         $min = $period->min('start_date');
    //         $max = $period->max('start_date');
    //         $datenow = '2023-07-11';
    //         if($min && $max){
    //             $calendar = CalendarModel::whereYear('start_date','>=',date('Y',strtotime($min)))
    //             ->whereMonth('start_date','>=',date('m',strtotime($min)))
    //             ->whereDate('start_date','<=',$max)
    //             ->where('status','on')
    //             ->whereNull('deleted_at')
    //             ->get();
    //         }else{
    //             $calendar = null;
    //         }
    //     }
       
    //     if($calendar){
    //         $arr = array();
    //         foreach($calendar as $calen){
    //             $start = strtotime($calen->start_date);
    //             while ($start <= strtotime($calen->end_date)) {
    //                 $arr[$calen->id][] = date('Y-m-d',$start);
    //                 $start = $start + 86400;
    //             }
    //         }
    //     }else{
    //         $arr = null;
    //     }
    //     // Filter
    //     $filter = array();
    //     if($dat){
    //         foreach($period as $tour){
    //             $tour_fill = TourModel::find($tour->tour_id);
    //              //ช่วงราคา
    //             if(isset($search_data['price'])){
    //                 $filter['price'][$search_data['price']][] = $tour->tour_id;
    //             }else{
    //                     if(isset($filter['price'][$tour_fill->price_group])){
    //                         if(!in_array($tour->tour_id,$filter['price'][ $tour_fill->price_group])){
    //                             $filter['price'][$tour_fill->price_group][] = $tour->tour_id;
    //                         }
    //                     }else{
    //                         $filter['price'][$tour_fill->price_group][] = $tour->tour_id;
    //                     }
    //             }
    //             // foreach($period as $p){
    //                 //จำนวนวัน
    //                 if($tour->day){
    //                     if(isset($filter['day'][$tour->day])){
    //                         if(!in_array($tour->tour_id,$filter['day'][$tour->day])){
    //                             $filter['day'][$tour->day][] = $tour->tour_id;
    //                         }
    //                     }else{
    //                         $filter['day'][$tour->day][] = $tour->tour_id;
    //                     }
    //                 }
                   
    //                 if($tour->start_date){
    //                     //ช่วงเดือน
    //                     $month_start = date('n',strtotime($tour->start_date));
    //                     //ช่วงเดือน-ปี
    //                     $year_start = date('Y',strtotime($tour->start_date));
    //                     if(isset($filter['year'][$year_start][$month_start])){
    //                         if(!in_array($tour->tour_id,$filter['year'][$year_start][$month_start])){
    //                             $filter['year'][$year_start][$month_start][] = $tour->tour_id;
    //                         }
    //                     }else{
    //                         $filter['year'][$year_start][$month_start][] = $tour->tour_id;
    //                     }
    //                     //วันหยุดเทศกาล
    //                     foreach($arr as $cid => $calen){
    //                         $start_pe = strtotime($tour->start_date);
    //                         while ($start_pe <= strtotime($tour->end_date)) {
    //                             // echo date('Y-m-d',$start_pe).$tour->tour_id."<br>";
    //                             if(in_array(date('Y-m-d',$start_pe),$calen)){
    //                                 // dd($tour);
    //                                 if(isset($filter['calendar'][$cid])){
    //                                     if(!in_array($tour->tour_id,$filter['calendar'][$cid])){
    //                                             $filter['calendar'][$cid][] = $tour->tour_id;
    //                                         }
    //                                     }else{
    //                                         $filter['calendar'][$cid][] = $tour->tour_id;
    //                                     }
    //                             }
    //                             $start_pe = $start_pe + 86400;
    //                         }
    //                     }
    //                 }
    //             // }
    //             //อำเภอ
    //             if($tour_fill->district_id){
    //                 $filter['city'][$tour->tour_id] = $tour_fill->district_id;
    //             }
    //             //สายการบิน
    //             if($tour_fill->airline_id){
    //                 if(isset($filter['airline'][$tour_fill->airline_id])){
    //                     if(!in_array($tour->tour_id,$filter['airline'][$tour_fill->airline_id])){
    //                         $filter['airline'][$tour_fill->airline_id][] = $tour->tour_id;
    //                     }
    //                 }else{
    //                     $filter['airline'][$tour_fill->airline_id][] = $tour->tour_id;
    //                 }
    //             }
    //             //ดาว
    //             if($tour_fill->rating*1 == 5 ){
                    
    //                 if(isset($filter['rating'][0])){
    //                     if(!in_array($tour->tour_id,$filter['rating'][0])){
    //                         $filter['rating'][0][] = $tour->tour_id;
    //                     }
    //                 }else{
    //                     $filter['rating'][0][] = $tour->tour_id;
    //                 }
    //             }else if($tour_fill->rating*1 == 4){
    //                 if(isset($filter['rating'][1])){
    //                     if(!in_array($tour->tour_id,$filter['rating'][1])){
    //                         $filter['rating'][1][] = $tour->tour_id;
    //                     }
    //                 }else{
    //                     $filter['rating'][1][] = $tour->tour_id;
    //                 }
    //             }else if($tour_fill->rating*1 == 3){
                   
    //                 if(isset($filter['rating'][2])){
    //                     if(!in_array($tour->tour_id,$filter['rating'][2])){
    //                         $filter['rating'][2][] = $tour->tour_id;
    //                     }
    //                 }else{
    //                     $filter['rating'][2][] = $tour->tour_id;
    //                 }
    //             }else if($tour_fill->rating*1 == 2){
    //                 // dd($filter);
    //                 if(isset($filter['rating'][3])){
    //                     if(!in_array($tour->tour_id,$filter['rating'][3])){
    //                         $filter['rating'][3][] = $tour->tour_id;
    //                     }
    //                 }else{
    //                     $filter['rating'][3][] = $tour->tour_id;
    //                 }
    //             }else if($tour_fill->rating*1 == 1){
    //                 if(isset($filter['rating'][4])){
    //                     if(!in_array($tour->tour_id,$filter['rating'][4])){
    //                         $filter['rating'][4][] = $tour->tour_id;
    //                     }
    //                 }else{
    //                     $filter['rating'][4][] = $tour->tour_id;
    //                 }
    //             }else if($tour_fill->rating*1 == 0 || $tour_fill->rating == null){
    //                 if(isset($filter['rating'][5])){
    //                     if(!in_array($tour->tour_id,$filter['rating'][5])){
    //                         $filter['rating'][5][] = $tour->tour_id;
    //                     }
    //                 }else{
    //                     $filter['rating'][5][] = $tour->tour_id;
    //                 }
    //             } 
    //         }
    //         // dd($filter);
    //     }
    //     $num_price = 0;
    //     if(isset($filter['price'])){
    //         foreach($filter['price'] as $p){
    //             $num_price =  $num_price + count($p);
    //         }
    //     }

    //     // Platform check 
    //     $isWin = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows")):0; 
    //     $isMac = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mac"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mac")):0; 
    //     $isAndroid = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android")):0; 
    //     $isIPhone = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone")):0; 
    //     $isIPad = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad")):0; 
    //     $data = array(
    //         'prov' => $province,
    //         'data' => $dat_tour,
    //         'arr' => $arr,
    //         'filter' => $filter,
    //         'airline_data' => TravelTypeModel::/* where('status','on')-> */where('deleted_at',null)->get(),
    //         'tour_id' => $c,
    //         'num_price' => $num_price,
    //         'main_slug' => $main_slug,
    //         'search_price' => $search_price,
    //         'search_start' => $search_start,
    //         'search_end' => $search_end,
    //         'search_city' => $search_city,
    //         'orderby_data'  => $orderby_data ,
    //         'isWin' => $isWin,
    //         'isMac' => $isMac,
    //         'isAndroid' => $isAndroid,
    //         'isIPhone' => $isIPhone,
    //         'isIPad' => $isIPad,
    //     );
    //     return view('frontend.inthai',$data);
    // }
    // public function search_filter($request,$period)
    // {
    //     try {
    //         // dd($period);
    //         $calen_data = json_decode($request->calen_id,true);
    //         $data = "";
    //         $month = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    //         $allSoldOut = array();
    //         $checkSold = false;
    //         $count_p = 1;
    //         $page_data = array();
            
    //         // if(isset($_GET['page'])){ $page = urldecode($_GET['page']); }else{ $page = 1;}
    //         // foreach($period as $pre){
                
    //             // dd($pre['tour'],$tour->lastPage());
    //             // if(count($period)){
    //             //     $page_data[$count_p][] = $pre['tour'];
                    
    //             // }
    //         // }
    //         // if(isset($page_data[$page])){
    //         //     foreach($page_data[$count_p] as $page_t){
    //         //         if(count($page_t) >= 1){
    //         //             $count_p++;
    //         //             dd($page_t['tour'],$page_t);
    //         //         }
    //         //     } 
    //         // }
    //     // dd(count($page_data[$page]),count($page_data[$count_p]));
    //     //    if(isset($page_data[$page])){
               
    //             foreach($period as $pre){
    //                 $numday = 0;
    //                 foreach ($pre['period'] as $p){ 
    //                     $checkSold = false;
    //                     $numday = $p->day;
    //                     if($p->count == 0 && $p->status_period == 3){
    //                         $allSoldOut[$p->tour_id][] = $p->id;
    //                     }  
    //                     if(isset($allSoldOut[$pre['tour']->id])){
    //                         if(count($pre['period']) == count($allSoldOut[$pre['tour']->id])){
    //                             $checkSold = true;
    //                         } 
    //                     }
                    
    //                 }
    //                 // if($pre['tour']->id == 207){   dd(count($pre['period']),count($allSoldOut[$pre['tour']->id]),$allSoldOut[$pre['tour']->id],$checkSold); }
    //                 $country = CountryModel::whereIn('id',json_decode($pre['tour']->country_id,true))->get();
    //                 $province = ProvinceModel::whereIn('id',json_decode($pre['tour']->province_id,true))->get();
    //                 $airline =  TravelTypeModel::find($pre['tour']->airline_id);
    //                 $type =  TourTypeModel::find(@$pre['tour']->type_id);
    //                 if($pre['tour']){
    //                 $data .= "<div class='boxwhiteshd'><div class='toursmainshowGroup  hoverstyle'><div class='row'>";
    //                 $data .= "<div class='col-lg-12 col-xl-3 pe-0'><div class='covertourimg'><figure>";
    //                 $data .= "<a href='".url('tour/'.$pre['tour']->slug)."'><img src='".asset(@$pre['tour']->image)."' alt=''></a>";
    //                 $data .= "</figure><div class='d-block d-sm-block d-md-block d-lg-none d-xl-none'>";
    //                 $data .= "<a href='javascript:void(0);' class='tagicbest'  name='type_data' onclick='OrderByType(".@$pre['tour']->type_id.")'><img src='".asset(@$type->image)."'class='img-fluid' alt=''></a></div>";
    //                 if($pre['tour']->special_price > 0){
    //                     $data .= "<div class='saleonpicbox'><span> ลดราคาพิเศษ</span> <br>".number_format($pre['tour']->special_price,0)." บาท</div>";
    //                 }
    //                 if($checkSold){
    //                     $data .= "<div class='soldfilter'><div class='soldop'><span class='bigSold'>SOLD OUT </span> <br><span class='textsold'> ว้า! หมดแล้ว คุณตัดสินใจช้าไป</span> <br>";
    //                     $data .= "<a href='".url('tour/'.$pre['tour']->slug)."' target='_blank' class='btn btn-second mt-3'><i class='fi fi-rr-search'></i> หาโปรแกรมทัวร์ใกล้เคียง</a></div></div>";
    //                 }
    //                 $data .= "<div class='priceonpic'>";
    //                 if($pre['tour']->special_price > 0){
    //                     $price = $pre['tour']->price - $pre['tour']->special_price; 
    //                     $data .= "<span class='originalprice'>ปกติ ".number_format($pre['tour']->price,0)." </span><br>";
    //                     $data .= "เริ่ม<span class='saleprice'>".number_format(@$price,0)." บาท</span>";
    //                 }else{
    //                     $data .= "<span class='saleprice'>".number_format($pre['tour']->price,0)." บาท</span>";
    //                 }                         
    //                 $data .= "</div><div class='addwishlist'>";
    //                 $data .= "<a href='javascript:void(0);' class='wishlist' data-tour-id='".$pre['tour']->id."'><i class='bi bi-heart-fill' id='likeButton' onclick='likedTour(".@$pre['tour']->id.")'></i></a>";
    //                 $data .= "</div></div></div>";
    //                 $data .= "<div class='col-lg-12 col-xl-9'><div class='codeandhotel Cropscroll mt-1'>";
    //                 $data .= "<li class='bgwhite'> "; 
    //                 if($country){
    //                     $data .= "<a href='".url('oversea/'.@$country[0]->slug)."'><i class='fi fi-rr-marker' style='color:#f15a22;'></i>";
    //                     foreach ($country as $coun){
    //                         $data .= $coun->country_name_th?$coun->country_name_th:$coun->country_name_en ;
    //                     }  
    //                 }else{
    //                     $data .= "<a href='".url('inthai/'.@$province[0]->slug)."'><i class='fi fi-rr-marker' style='color:#f15a22;'></i>";
    //                     foreach ($province as $prov){
    //                         $data .= $prov->name_th?$prov->name_th:$prov->name_en ;
    //                     }  
    //                 }
    //                 $data .= "</a></li>";
    //                 $data .= "<li>รหัสทัวร์ : <span class='bluetext'>";
    //                 if(@$pre['tour']->code1_check){
    //                     $data .= @$pre['tour']->code1;
    //                 }else{
    //                     $data .= @$pre['tour']->code;
    //                 } 
    //                 $data .= "</span> </li>";
    //                 $data .= "<li class='rating'>โรงแรม";
    //                 if($pre['tour']->rating > 0){
    //                     $data .= "<a href='javascript:void(0);' onclick='Check_filter(".$pre['tour']->rating.",\"rating\")'>";
    //                     for($i=1; $i <= $pre['tour']->rating; $i++){
    //                         $data .= "<i class='bi bi-star-fill'></i>";
    //                     }
    //                     $data .= "</a>";
    //                 }else{
    //                     $data .= "<a href='javascript:void(0);' onclick='Check_filter(0,\"rating\")'>";
    //                 }                                                  
    //                 $data .= "</a></li><li>สายการบิน <a href='javascript:void(0);' onclick='Check_filter(".@$airline->id.",\"airline\")'><img src='".asset(@$airline->image)."' alt=''></a></li>";
    //                 $data .= "<li><div class='d-none d-sm-none d-md-none d-lg-block d-xl-block'><a href='javascript:void(0);' class='tagicbest'  name='type_data' onclick='OrderByType(".@$pre['tour']->type_id.")'><img src='".asset(@$type->image)."' class='img-fluid' alt=''></a></div></li>";
    //                 $data .= "<li class='bgor'>ระยะเวลา <a href='javascript:void(0);' onclick='Check_filter($numday,\"day\")'>".$pre['tour']->num_day."</a></li></div>";
    //                 $data .= "<div class='nameTop'><h3> <a href='".url('tour/'.$pre['tour']->slug)."'>".$pre['tour']->name."</a></h3></div>";
    //                 $data .= "<div class='pricegroup text-end'>";
    //                 if($pre['tour']->special_price > 0){
    //                     $price = $pre['tour']->price - $pre['tour']->special_price; 
    //                     $data .= "<span class='originalprice'>ปกติ ".number_format($pre['tour']->price,0)." </span><br>เริ่ม<span class='saleprice'> ".number_format(@$price,0)." บาท</span>";
    //                 }else{
    //                     $data .= "เริ่ม<span class='saleprice'> ".number_format($pre['tour']->price,0)." บาท</span>";
    //                 }
    //                 $data .=  "</div>";
    //                 if($pre['tour']->description){
    //                     $data .= "<div class='highlighttag'> <span><i class='fi fi-rr-tags'></i> </span> ".@$pre['tour']->description."</div>";
    //                 }
    //                 $count_hilight = 0;
    //                 if($pre['tour']->travel){  $count_hilight++; }
    //                 if($pre['tour']->shop){  $count_hilight++; }
    //                 if($pre['tour']->eat){  $count_hilight++; }
    //                 if($pre['tour']->special){  $count_hilight++;}
    //                 if($pre['tour']->stay){  $count_hilight++;}
    //                 if($count_hilight > 0){
    //                     $data .= "<div class='hilight mt-2'><div class='readMore'><div class='readMoreWrapper'><div class='readMoreText2'>";
    //                     if($pre['tour']->travel){
    //                         $data .= "<li><div class='iconle'><span><i class='bi bi-camera-fill'></i></span></div><div class='topiccenter'><b>เที่ยว</b></div><div class='details'> ".@$pre['tour']->travel."</div></li>";
    //                     }
    //                     if($pre['tour']->shop){
    //                         $data .= "<li><div class='iconle'><span><i class='bi bi-bag-fill'></i></span></div><div class='topiccenter'><b>ช้อป </b></div><div class='details'> ".@$pre['tour']->shop."</div></li>";
    //                     }
    //                     if($pre['tour']->eat){
    //                         $data .= "<li><div class='iconle'><span><svg xmlns='http://www.w3.org/2000/svg' width='22' height='22' fill='currentColor' class='bi bi-cup-hot-fill'viewBox='0 0 16 16'>";
    //                         $data .= "<path fill-rule='evenodd' d='M.5 6a.5.5 0 0 0-.488.608l1.652 7.434A2.5 2.5 0 0 0 4.104 16h5.792a2.5 2.5 0 0 0 2.44-1.958l.131-.59a3 3 0 0 0 1.3-5.854l.221-.99A.5.5 0 0 0 13.5 6H.5ZM13 12.5a2.01 2.01 0 0 1-.316-.025l.867-3.898A2.001 2.001 0 0 1 13 12.5Z' />";
    //                         $data .= "<path d='m4.4.8-.003.004-.014.019a4.167 4.167 0 0 0-.204.31 2.327 2.327 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.31 3.31 0 0 1-.202.388 5.444 5.444 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 3.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 3.6 2.8l-.01-.012a5.099 5.099 0 0 1-.37-.543A1.53 1.53 0 0 1 3 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a5.446 5.446 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 4.4.8Zm3 0-.003.004-.014.019a4.167 4.167 0 0 0-.204.31 2.327 2.327 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.31 3.31 0 0 1-.202.388 5.444 5.444 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 6.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 6.6 2.8l-.01-.012a5.099 5.099 0 0 1-.37-.543A1.53 1.53 0 0 1 6 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a5.446 5.446 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 7.4.8Zm3 0-.003.004-.014.019a4.077 4.077 0 0 0-.204.31 2.337 2.337 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.198 3.198 0 0 1-.202.388 5.385 5.385 0 0 1-.252.382l-.019.025-.005.008-.002.002A.5.5 0 0 1 9.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 9.6 2.8l-.01-.012a5.099 5.099 0 0 1-.37-.543A1.53 1.53 0 0 1 9 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a5.446 5.446 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 10.4.8Z' /></svg></span> </div>";
    //                         $data .= "<div class='topiccenter'><b>กิน </b></div><div class='details'>".@$pre['tour']->eat."</div> </li>";
    //                     }
    //                     if($pre['tour']->special){
    //                         $data .= "<li><div class='iconle'><span><i class='bi bi-bookmark-heart-fill'></i></span></div><div class='topiccenter'><b>พิเศษ </b></div><div class='details'>".@$pre['tour']->special."</div></li>";
    //                     }
    //                     if($pre['tour']->stay){
    //                         $data .= "<li><div class='iconle'><span><svg xmlns='http://www.w3.org/2000/svg' width='22' height='22'fill='currentColor' class='bi bi-buildings-fill' viewBox='0 0 16 16'>";
    //                         $data .= "<path d='M15 .5a.5.5 0 0 0-.724-.447l-8 4A.5.5 0 0 0 6 4.5v3.14L.342 9.526A.5.5 0 0 0 0 10v5.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V14h1v1.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5V.5ZM2 11h1v1H2v-1Zm2 0h1v1H4v-1Zm-1 2v1H2v-1h1Zm1 0h1v1H4v-1Zm9-10v1h-1V3h1ZM8 5h1v1H8V5Zm1 2v1H8V7h1ZM8 9h1v1H8V9Zm2 0h1v1h-1V9Zm-1 2v1H8v-1h1Zm1 0h1v1h-1v-1Zm3-2v1h-1V9h1Zm-1 2h1v1h-1v-1Zm-2-4h1v1h-1V7Zm3 0v1h-1V7h1Zm-2-2v1h-1V5h1Zm1 0h1v1h-1V5Z' /></svg></span> </div>";
    //                         $data .= "<div class='topiccenter'><b>พัก </b></div><div class='details'>".@$pre['tour']->stay."</div></li>";
    //                     }
    //                     $data .= "</div><div class='readMoreGradient'></div></div><a class='readMoreBtn2'></a><span class='readLessBtnText' style='display: none;'>Read Less</span>";
    //                     $data .= "<span class='readMoreBtnText' style='display: none;'>Read More </span></div></div>";
    //                 }
    //                 $data .=  "</div></div>";
    //                 if(!$checkSold){
    //                     $sold_tour = array();
    //                     $data .= "<div class='periodtime'><div class='readMore'><div class='readMoreWrapper'><div class='readMoreText'><div class='listperiod_moredetails'>";
    //                     foreach($pre['period']->groupby('group_date') as $group){
    //                         $data .= "<div class='tagmonth'><span class='month'>".$month[date('n',strtotime($group[0]->start_date))]."</span></div><div class='splgroup'>";
    //                         if(!in_array($pre['tour']->id,$allSoldOut)){    
    //                                 foreach ($group as $p){ 
    //                                     if($p->count == 0 && $p->status_period == 3){
    //                                         $sold_tour[] = $p->id;
    //                                     }  
                                    
    //                                     $start = strtotime($p->start_date);
    //                                         ${'holliday'.$p->id} = 0;
    //                                         while ($start <= strtotime($p->end_date)) {
    //                                             if(isset($calen_data) && in_array(date('Y-m-d',$start),$calen_data) || date('N',$start) >= 6){
    //                                                 if($p->count <= 10){
    //                                                 }else{
    //                                                     ${'holliday'.$p->id}++;
    //                                                 }
    //                                             }
    //                                         $start = $start + 86400;
    //                                     }
    //                                     $data .= "<li>"; 
    //                                     $data .= "<a ";
    //                                     if(${'holliday'.$p->id} > 0){
    //                                         $data .= "data-tooltip='".${'holliday'.$p->id}." วัน'";
    //                                     }
    //                                     $data .= "id='staydate".$p->id."' class='staydate'>";
    //                                     $start = strtotime($p->start_date); 
    //                                         $chk_price = true;
    //                                         while ($start <= strtotime($p->end_date)) {
    //                                             if(isset($calen_data) && in_array(date('Y-m-d',$start),$calen_data) || date('N',$start) >= 6){
    //                                                 $chk_price = false;
    //                                                 if($p->count <= 10){
    //                                                     $data .= "<span class='fulltext'>*</span>";
    //                                                     break;
    //                                                 }else{
    //                                                     $data .= "<span class='staydate'>-</span>";
    //                                                 }
    //                                             }
    //                                             $start = $start + 86400;
    //                                         }
    //                                         if($chk_price){
    //                                             if($p->special_price1 && $p->special_price1 > 0){
    //                                                 $price = $p->price1 - $p->special_price1;
    //                                             }else{
    //                                                 $price = $p->price1;
    //                                             }
    //                                         $data .= "<span class='saleperiod'>".number_format($price,0)."฿ </span>";
    //                                         }
    //                                     $data .= "</a><br>";
    //                                     $data .= date('d',strtotime($p->start_date))." - ".date('d',strtotime($p->end_date));
    //                                     $data .= "</li>";
    //                                     }
    //                                 }
    //                                 $data .= "</div><hr>";
    //                             }
    //                             $data .= "</div></div><div class='readMoreGradient'></div></div>";
    //                             if(count($pre['period']) > 30){
    //                                 $data .= "<a class='readMoreBtn'></a>";
    //                                 $data .= "<span class='readLessBtnText' style='display: none;'>Read Less</span><span class='readMoreBtnText' style='display: none;'>Read More</span>";
    //                             }
    //                             $data .= "</div></div><div class='remainsFull'><li>* ใกล้เต็ม</li><li><span class='noshowpad'>-</span><span class='showpad'>-</span> จำนวนวันหยุด</li></div>";
    //                             $data .= "<div class='row'><div class='col-md-9'>";
    //                             $data .= "<div class='fullperiod'>";
    //                             if(count($sold_tour)){
    //                                 $data .= "<h6>พีเรียดที่เต็มแล้ว (".count($sold_tour).")</h6>";
    //                                 foreach ($pre['period'] as $sold){
    //                                     if($sold->count == 0 && $sold->status_period == 3){
    //                                         $data .= "<span class='monthsold'>".$month[date('n',strtotime($pre['period'][0]->start_date))]."</span>";
    //                                         $data .= "<li>".date('d',strtotime($sold->start_date))." - ".date('d',strtotime($sold->end_date))."</li>";
    //                                     }
    //                                 }
    //                             }          
    //                             $data .=  "</div>";
    //                             $data .= "</div><div class='col-md-3 text-md-end'><a href='".url('tour/'.$pre['tour']->slug)."' class='btn-main-og  morebtnog'>รายละเอียด</a>";
    //                             $data .= "</div></div>";
    //                     }
    //                 }
    //                 $data .= "<br></div></div>";
    //             }
    //             // $data .= "<div class='row mt-4 mb-4'><div class='col'><div class='pagination_bot'><nav class='pagination-container'>";
    //             // $data .= "<div class='pagination'>";

    //             // $total_page = count($page_data); 
    //             // $older = $page+1;    
    //             // $newer = $page-1;
    //             // $data .= "$total_page.หน้า";
    //             // if($total_page > 1){
                  
    //             //     if($page != $newer && $page != 1){
    //             //         $data .=  "<a class='pagination-newer' href='?page=".$newer."'><i class='fas fa-angle-left'></i></a>";
    //             //     }                                               
    //             //     $data .= "<span class='pagination-inner'>";
    //             //             if($total_page > 1){
    //             //                 for($i=1; $i<=$total_page; $i++) {
    //             //                     $data .= "<a ";
    //             //                     if($i == $page){
    //             //                         $data .= "class='pagination-active'" ;
    //             //                     } 
    //             //                     $data .= "href='?page=".$i."'>".$i."</a>";
    //             //                 }
    //             //             }
    //             //     $data .=  "</span>";                                        
    //             //     if($page != $older && $page != $total_page){
    //             //         $data .=  "<a class='pagination-older' href='?page=".$olde."'><i class='fas fa-angle-right'></i></a>";
    //             //     }                  
    //             // }                              
    //             // $data .= "</div>";
    //             // $data .= "</nav></div></div></div>";
    //     //    }
           

    //         return $data ;

    //     } catch (\Throwable $th) {
    //         // dd($th);
    //     }
        
    // }
    // public function search_filter_grid($request,$period)
    // {
    //     try {
    //         $data = "";
    //         foreach($period as  $pre){
    //             $numday = 0;
    //             foreach ($pre['period'] as $p){ 
    //                 $numday = $p->day;
    //             }
    //             $type = TourTypeModel::find(@$pre['tour']->type_id);
    //             $airline = TravelTypeModel::find(@$pre['tour']->airline_id);
    //             @$country = CountryModel::where('slug',$request->slug)->whereNull('deleted_at')->first();
    //             @$province = ProvinceModel::where('slug',$request->slug)->whereNull('deleted_at')->first();
    //             @$country_search = CountryModel::whereIn('id',json_decode($pre['tour']->country_id,true))->first();
    //             // dd($country_search,$pre['tour']->country_id);
    //             $data .= "<tr><td><div class='row'><div class='col-5 col-lg-4'>";
    //             $data .= "<a href='".url('tour/'.$pre['tour']->slug)."' target='_blank'><img src='".asset(@$pre['tour']->image)."' class='img-fluid' alt=''></a>";
    //             $data .= "</div><div class='col-7 col-lg-8 titlenametab'>";
    //             $data .= "<h3><a href='".url('tour/'.$pre['tour']->slug)."' target='_blank'>".$pre['tour']->name."</a></h3>";
    //             $data .= "</div></div></td>";
    //             if($country){
    //                 $data .=    "<td><a href='".url('oversea/'.@$country->slug)."'>".$country->country_name_th."</a> </td>";
    //             }else if($province){
    //                 $data .=    "<td><a href='".url('inthai/'.@$province->slug)."'>".$province->name_th."</a> </td>";
    //             }else{
    //                 $data .=    "<td><a href='".url('oversea/'.@$country_search->slug)."'>".$country_search->country_name_th."</a> </td>";  
    //             }
    //             $data .=    "<td><a href='javascript:void(0);' onclick='Check_filter($numday,\"day\")'>".$pre['tour']->num_day."</a> </td>";
    //             $data .=    "<td><a href='javascript:void(0);' onclick='Check_filter(".@$airline->id.",\"airline\")'><img src='".asset(@$airline->image)."' alt=''></a> </td>";
    //             $data .=    "<td>";
    //             if($pre['tour']->special_price > 0){
    //                 $price = $pre['tour']->price - $pre['tour']->special_price; 
    //                 $data .=  "เริ่ม ".number_format(@$price,0) ." บาท";
    //             }else{
    //                 $data .=  "เริ่ม ".number_format($pre['tour']->price,0)." บาท";
    //             } 
    //             $data .=    "</td>";
    //             $data .=    "<td><div class='rating'>";
    //             if($pre['tour']->rating > 0){
    //                 $data .= "<a href='javascript:void(0);' onclick='Check_filter(".$pre['tour']->rating.",\"rating\")'>";
    //                 for($i=1; $i <= @$pre['tour']->rating; $i++){
    //                     $data .=  "<i class='bi bi-star-fill'></i>";
    //                 }
    //             }else{
    //                 $data .= "<a href='javascript:void(0);' onclick='Check_filter(0,\"rating\")'>";
    //             }
    //             $data .=    "</div></td>";
    //             $data .=    "<td> <a href='javascript:void(0);' class='tagicbest'  name='type_data' onclick='OrderByType(".@$pre['tour']->type_id.")'><img src='".asset(@$type->image)."' class='img-fluid' alt=''></a></td>";
    //             $data .=    "<td> <a href='".url('tour/'.$pre['tour']->slug)."' target='_blank' class='link'><i class='bi bi-chevron-right'></i></a></td></tr>";
    //         }
    //         return $data ;

    //     } catch (\Throwable $th) {
    //         // dd($th);
    //     }
        
    // }
    // public function search_inthai(Request $request)
    // {
    //     $tour_id = json_decode($request->tour_id,true);
    //     $period = array();
    //     $price = json_decode($request->price,true);
    //     $pe_data = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id');
    //     if($tour_id){
    //         $pe_data = $pe_data->whereIn('tb_tour.id',$tour_id);
    //         // dd($tour_id);
    //     }
    //     if($request->start_date){
          
    //         $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',$request->start_date);
    //     }
    //     if($request->end_date){
    //         $pe_data = $pe_data->whereDate('tb_tour_period.start_date','<=',$request->end_date);
    //     }
    //     if($request->price){
    //         //ช่วงราคา
    //         if(isset($price[1])){
    //             $pe_data = $pe_data->where('tb_tour.price','>=',$price[0])->where('tb_tour.price','<=',$price[1]);
    //         }else{
    //             $pe_data = $pe_data->where('tb_tour.price','>=',$price[0]);
    //         }
    //     }
    //     if($request->day){
    //         $pe_data = $pe_data->where('tb_tour_period.day',$request->day);
    //     }
    //     if($request->city){
    //         $pe_data = $pe_data->where('tb_tour.district_id','like','%"'.$request->city.'"%');
    //     }
    //     if($request->airline){
    //         $pe_data = $pe_data->where('tb_tour.airline_id',$request->airline);
    //     }
    //     if($request->rating){
    //         $pe_data = $pe_data->where('tb_tour.rating',$request->rating);
    //     }
    //     if($request->calen_start){
    //         $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',$request->calen_start)->whereDate('tb_tour_period.start_date','<=',$request->calen_end);
    //     }
    //     if($request->month_fil){
    //         $pe_data = $pe_data->where('tb_tour_period.group_date',$request->month_fil);
    //     }
        
    //     $pe_data = $pe_data->where('tb_tour.status','on')
    //         ->where('tb_tour_period.status_display','on')
    //         ->orderby('tb_tour_period.start_date','asc')
    //         ->select('tb_tour_period.*')
    //         ->get()
    //         ->groupBy('tour_id');
    //     // dd($pe_data);
    //     foreach($pe_data as $k => $pe){
    //         $period[$k]['period']  = $pe;
    //         $period[$k]['soldout'] = TourPeriodModel::where('tour_id',$k);
    //         if($request->start_date && $request->end_date){
    //             $period[$k]['soldout'] = $period[$k]['soldout']->whereDate('start_date','>=',$request->start_date)->whereDate('start_date','<=',$request->end_date);
    //         }
    //         $period[$k]['soldout'] = $period[$k]['soldout']->where('status_period',3)->where('status_display','on')
    //         ->where('deleted_at',null)
    //         ->orderby('start_date','asc')
    //         ->get()->groupBy('group_date');
    //         $tour = TourModel::find($k);
    //         $period[$k]['tour'] = $tour;
    //     }
    //     $count_pe = count($period);
    //     // dd($request->rating,$pe_data);
    //     $data = "";
    //     $month = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    //     foreach($period as $pre){
    //         $numday = 0;
    //         foreach($pre['period']  as $pe){
    //             $numday = $pe->day;
    //         }
    //         $country = ProvinceModel::whereIn('id',json_decode($pre['tour']->province_id,true))->get();
    //         $airline =  TravelTypeModel::find($pre['tour']->airline_id);
    //         $type =  TourTypeModel::find(@$pre['tour']->type_id);
            
    //         if($pre['tour']){
    //         $data .= "<div class='boxwhiteshd'><div class='toursmainshowGroup  hoverstyle'><div class='row'>";
    //         $data .= "<div class='col-lg-12 col-xl-3 pe-0'><div class='covertourimg'><figure>";
    //         $data .= "<a href='".url('tour/'.$pre['tour']->slug)."'><img src='".asset(@$pre['tour']->image)."' alt=''></a>";
    //         $data .= "</figure><div class='d-block d-sm-block d-md-block d-lg-none d-xl-none'>";
    //         $data .= "<a href='javascript:void(0);' class='tagicbest'  name='type_data' onclick='OrderByType(".@$pre['tour']->type_id.")'><img src='".asset(@$type->image)."'class='img-fluid' alt=''></a></div>";
    //         if($pre['tour']->special_price > 0){
    //             $data .= "<div class='saleonpicbox'><span> ลดราคาพิเศษ</span> <br>".number_format($pre['tour']->special_price,0)." บาท</div>";
    //         }
    //         // $data .= "<div class='tagontop'><li class='bgor'><a href='".url('tour/'.$pre['tour']->slug)."'>".$pre['tour']->num_day."</a> </li>";
    //         // $data .= "<li class='bgwhite'><a href='".url('tour/'.$pre['tour']->slug)."'><i class='fi fi-rr-marker'></i>";
    //         // foreach ($country as $coun){
    //         //     $data .= $coun->name_th ;
    //         // } 
    //         // $data .= "</a></li></div>"; 
    //         $data .= "<div class='addwishlist'>";
    //         $data .= "<a href='javascript:void(0);' class='wishlist' data-tour-id='".$pre['tour']->id."'><i class='bi bi-heart-fill' id='likeButton' onclick='likedTour(".@$pre['tour']->id.")'></i></a>";
    //         $data .= "</div></div></div>";
    //         $data .= "<div class='col-lg-12 col-xl-9'><div class='codeandhotel Cropscroll mt-1'>";
    //         $data .= "<li class='bgwhite'><i class='fi fi-rr-marker' style='color:#f15a22;'></i> "; 
    //         foreach ($country as $coun){
    //             $data .= $coun->country_name_th?$coun->country_name_th:$coun->country_name_en ;
    //         }  
    //         $data .= "</li>";
    //         $data .= "<li>รหัสทัวร์ : <span class='bluetext'>";
    //         if(@$pre['tour']->code1_check){
    //             $data .= @$pre['tour']->code1;
    //         }else{
    //             $data .= @$pre['tour']->code;
    //         } 
    //         $data .= "</span> </li>";
    //         $data .= "<li class='rating'>โรงแรม <a href='".url('tour/'.$pre['tour']->slug)."'>";
    //         if($pre['tour']->rating > 0){
    //             $data .= "<a href='javascript:void(0);' onclick='Check_filter(".$pre['tour']->rating.",\"rating\")'>";
    //             for($i=1; $i <= $pre['tour']->rating; $i++){
    //                 $data .= "<i class='bi bi-star-fill'></i>";
    //             }
    //         }else{
    //             $data .= "<a href='javascript:void(0);' onclick='Check_filter(0,\"rating\")'>";
    //         }                                                          
    //         $data .= "</a></li><li>สายการบิน <a href='javascript:void(0);' onclick='Check_filter(".@$airline->id.",\"airline\")'><img src='".asset(@$airline->image)."' alt=''></a></li>";
    //         $data .= "<li><div class='d-none d-sm-none d-md-none d-lg-block d-xl-block'><a href='javascript:void(0);' class='tagicbest'  name='type_data' onclick='OrderByType(".@$pre['tour']->type_id.")'><img src='".asset(@$type->image)."' class='img-fluid' alt=''></a></div></li>";
    //         $data .= "<li class='bgor'>ระยะเวลา <a href='javascript:void(0);' onclick='Check_filter($numday,\"day\")'>".$pre['tour']->num_day."</a></li></div>";
    //         $data .= "<div class='nameTop'><h3> <a href='".url('tour/'.$pre['tour']->slug)."'>".$pre['tour']->name."</a></h3></div>";
    //         $data .= "<div class='pricegroup text-end'>";
    //         if($pre['tour']->special_price > 0){
    //             $price = $pre['tour']->price - $pre['tour']->special_price; 
    //             $data .= "<span class='originalprice'>ปกติ ".number_format($pre['tour']->price,0)." </span><br>เริ่ม<span class='saleprice'> ".number_format(@$price,0)." บาท</span>";
    //         }else{
    //             $data .= "เริ่ม<span class='saleprice'> ".number_format($pre['tour']->price,0)." บาท</span>";
    //         }
    //         $data .=  "</div>";
    //         if($pre['tour']->description){
    //             $data .= "<div class='highlighttag'> <span><i class='fi fi-rr-tags'></i> </span> ".@$pre['tour']->description."</div>";
    //         }
    //         // $data .= "</div><div class='highlighttag'> <span><i class='fi fi-rr-tags'></i> </span> ".@$pre['tour']->description."</div>";
    //         $count_hilight = 0;
    //         if($pre['tour']->travel){  $count_hilight++; }
    //         if($pre['tour']->shop){  $count_hilight++; }
    //         if($pre['tour']->eat){  $count_hilight++; }
    //         if($pre['tour']->special){  $count_hilight++;}
    //         if($pre['tour']->stay){  $count_hilight++;}
    //         if($count_hilight > 0){
    //             $data .= "<div class='hilight mt-2'><div class='readMore'><div class='readMoreWrapper'><div class='readMoreText2'>";
    //             if($pre['tour']->travel){
    //                 $data .= "<li><div class='iconle'><span><i class='bi bi-camera-fill'></i></span></div><div class='topiccenter'><b>เที่ยว</b></div><div class='details'> ".@$pre['tour']->travel."</div></li>";
    //             }
    //             if($pre['tour']->shop){
    //                 $data .= "<li><div class='iconle'><span><i class='bi bi-bag-fill'></i></span></div><div class='topiccenter'><b>ช้อป </b></div><div class='details'> ".@$pre['tour']->shop."</div></li>";
    //             }
    //             if($pre['tour']->eat){
    //                 $data .= "<li><div class='iconle'><span><svg xmlns='http://www.w3.org/2000/svg' width='22' height='22' fill='currentColor' class='bi bi-cup-hot-fill'viewBox='0 0 16 16'>";
    //                 $data .= "<path fill-rule='evenodd' d='M.5 6a.5.5 0 0 0-.488.608l1.652 7.434A2.5 2.5 0 0 0 4.104 16h5.792a2.5 2.5 0 0 0 2.44-1.958l.131-.59a3 3 0 0 0 1.3-5.854l.221-.99A.5.5 0 0 0 13.5 6H.5ZM13 12.5a2.01 2.01 0 0 1-.316-.025l.867-3.898A2.001 2.001 0 0 1 13 12.5Z' />";
    //                 $data .= "<path d='m4.4.8-.003.004-.014.019a4.167 4.167 0 0 0-.204.31 2.327 2.327 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.31 3.31 0 0 1-.202.388 5.444 5.444 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 3.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 3.6 2.8l-.01-.012a5.099 5.099 0 0 1-.37-.543A1.53 1.53 0 0 1 3 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a5.446 5.446 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 4.4.8Zm3 0-.003.004-.014.019a4.167 4.167 0 0 0-.204.31 2.327 2.327 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.31 3.31 0 0 1-.202.388 5.444 5.444 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 6.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 6.6 2.8l-.01-.012a5.099 5.099 0 0 1-.37-.543A1.53 1.53 0 0 1 6 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a5.446 5.446 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 7.4.8Zm3 0-.003.004-.014.019a4.077 4.077 0 0 0-.204.31 2.337 2.337 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.198 3.198 0 0 1-.202.388 5.385 5.385 0 0 1-.252.382l-.019.025-.005.008-.002.002A.5.5 0 0 1 9.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 9.6 2.8l-.01-.012a5.099 5.099 0 0 1-.37-.543A1.53 1.53 0 0 1 9 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a5.446 5.446 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 10.4.8Z' /></svg></span> </div>";
    //                 $data .= "<div class='topiccenter'><b>กิน </b></div><div class='details'>".@$pre['tour']->eat."</div> </li>";
    //             }
    //             if($pre['tour']->special){
    //                 $data .= "<li><div class='iconle'><span><i class='bi bi-bookmark-heart-fill'></i></span></div><div class='topiccenter'><b>พิเศษ </b></div><div class='details'>".@$pre['tour']->special."</div></li>";
    //             }
    //             if($pre['tour']->stay){
    //                 $data .= "<li><div class='iconle'><span><svg xmlns='http://www.w3.org/2000/svg' width='22' height='22'fill='currentColor' class='bi bi-buildings-fill' viewBox='0 0 16 16'>";
    //                 $data .= "<path d='M15 .5a.5.5 0 0 0-.724-.447l-8 4A.5.5 0 0 0 6 4.5v3.14L.342 9.526A.5.5 0 0 0 0 10v5.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V14h1v1.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5V.5ZM2 11h1v1H2v-1Zm2 0h1v1H4v-1Zm-1 2v1H2v-1h1Zm1 0h1v1H4v-1Zm9-10v1h-1V3h1ZM8 5h1v1H8V5Zm1 2v1H8V7h1ZM8 9h1v1H8V9Zm2 0h1v1h-1V9Zm-1 2v1H8v-1h1Zm1 0h1v1h-1v-1Zm3-2v1h-1V9h1Zm-1 2h1v1h-1v-1Zm-2-4h1v1h-1V7Zm3 0v1h-1V7h1Zm-2-2v1h-1V5h1Zm1 0h1v1h-1V5Z' /></svg></span> </div>";
    //                 $data .= "<div class='topiccenter'><b>พัก </b></div><div class='details'>".@$pre['tour']->stay."</div></li>";
    //             }
    //             $data .= "</div><div class='readMoreGradient'></div></div><a class='readMoreBtn2'></a><span class='readLessBtnText' style='display: none;'>Read Less</span>";
    //             $data .= "<span class='readMoreBtnText' style='display: none;'>Read More </span></div></div>";
    //         }
    //         $data .= "</div></div>";
    //         $data .= "<div class='periodtime'><div class='readMore'><div class='readMoreWrapper'><div class='readMoreText'><div class='listperiod_moredetails'>";
    //         foreach($pre['period']->groupby('group_date') as $group){
    //         $data .= "<div class='tagmonth'><span class='month'>".$month[date('n',strtotime($group[0]->start_date))]."</span></div><div class='splgroup'>";
    //         foreach ($group as $p){                  
    //             $calen_start = strtotime($request->start_date);
    //             $calen_end = strtotime($request->end_date);
    //             $calendar = ceil(($calen_end - $calen_start)/86400);
    //             $arrayDate = array();
    //             $arrayDate[] = date('Y-m-d',$calen_start); 
    //             for($x = 1; $x < $calendar; $x++){
    //                 $arrayDate[] = date('Y-m-d',($calen_start+(86400*$x)));
    //             }
    //             $arrayDate[] = date('Y-m-d',$calen_end); 
    //             $sum_end = strtotime($p->end_date);
    //             $sum_start = strtotime($p->start_date);
    //             $arr = array();
    //             $arr[] = date('Y-m-d',$sum_start); 
    //             $sum_day = ceil(($sum_end - $sum_start)/86400) ;
    //             if(in_array($p->start_date,$arrayDate)){
    //                 for($i = 1; $i < $sum_day; $i++){
    //                     $arr[] = date('Y-m-d',($sum_start+(86400*$i)));
    //                 }
    //             }
    //             $arr[] = date('Y-m-d',$sum_end);

    //             $data .= "<li>";
    //             if($p->status_period != 3){
    //                 if($p->count <= 5){
    //                     $data .= "<span class='fulltext'> * </span><br>";
    //                 }elseif($arrayDate != null){ 
    //                     $data .= "<a href='".url('tour/'.$p->slug)."' data-tooltip='".count($arr)."วัน' class='staydate'>";
    //                     if($arrayDate != null){
    //                         $start = strtotime($p->start_date); // แปลง start_date เป็นตัวเลข
    //                         while ($start <= strtotime($p->end_date)) { // จับคู่กับวันหยุดแล้วใส่จุด
    //                             if(in_array(date('Y-m-d',$start),$arrayDate) || date('N',$start) >= 6 ){
    //                                 $p->count <= 10 ? $data .= "<span class='fulltext'>*</span>" : $data .= "<span class='staydate'>-</span>";
    //                                 if($p->count <= 10){
    //                                     break;
    //                                 }
    //                             }
    //                             $start = $start + 86400;
    //                         }
    //                     }
    //                 $data .= "</a><br>";
    //             }else{
    //                 $data .= "<span class='saleperiod'>";
    //                         if($p->special_price1 > 0){
    //                             $data .= number_format($p->price1 - $p->special_price1)."฿";  
    //                         }else{
    //                             $data .= number_format($p->price1)."฿"; 
    //                         }
    //                 $data .= "</span> <br>";
    //             }
    //                 $data .= date('d',strtotime($p->start_date))."-".date('d',strtotime($p->end_date));
    //             }
    //             $data .= "</li>";
           
    //         }
    //         $data .= "</div><hr>";
    //         }
    //         $data .= "</div></div><div class='readMoreGradient'></div></div><a class='readMoreBtn'></a>";
    //         $data .= "<span class='readLessBtnText' style='display: none;'>Read Less</span><span class='readMoreBtnText' style='display: none;'>Read More</span></div></div>";
    //         $data .= "<div class='remainsFull'><li>* ใกล้เต็ม</li><li><span class='noshowpad'>-</span><span class='showpad'>-</span> จำนวนวันหยุด</li></div>";
    //         $data .= "<div class='row'><div class='col-md-9'>";
    //         if($pre['soldout']){
    //             $data .= "<div class='fullperiod'><h6 class='pb-2'>พีเรียดที่เต็มแล้ว (".count($pre['soldout']).")</h6>";
    //                     foreach ($pre['soldout'] as $sold){
    //                         $data .= "<span class='monthsold'>".$month[date('n',strtotime($sold[0]->start_date))]."</span>";
    //                         foreach($sold as $so){
    //                             $data .= "<li>".date('d',strtotime($so->start_date))."-".date('d',strtotime($so->end_date))."</li>";
    //                         }
    //                     }
    //             $data .=  "</div>";
    //         }
    //             $data .= "</div><div class='col-md-3 text-md-end'><a href='".url('tour/'.$pre['tour']->slug)."' class='btn-main-og  morebtnog'>รายละเอียด</a>";
    //             $data .= "</div></div></div></div>";
    //         }
    //     }
    //     $datas = array(
    //         'data' => $data,
    //         'period' => $count_pe,
    //     );
    //     return $datas ;
    // }
    // public function search_airline(Request $request){
        
    //     $air = TravelTypeModel::whereIn('id',json_decode($request->id,true));
    //     if($request->text != null){
    //         $air = $air->where('travel_name','like','%'.$request->text.'%');
    //     }
       
    //     $air = $air->orderBy('id','desc')->get();
    //     $data = '';
    //     $num = json_decode($request->num,true);
    //     foreach( $air as $a){
    //         $data .=  "<li><label class='check-container'>";
    //         if($a->image){
    //             $data .=  "<img src='".asset($a->image)."' alt=''> ";
    //         }
    //         $data .=  $a->travel_name ;
    //         $data .=  "<input type='radio' name='airline' id='airline$a->id' onclick='UncheckdAirline($a->id)'  value='$a->id'>";
    //         $data .=  "<span class='checkmark'></span> <div class='count'>";
    //         $data .= "(".$num[$a->id].")";
    //         $data .=  "</div></label></li>";
          
    //     }

    //     return $data;
    //     // dd($data);
    // }
    // public function search_city(Request $request){
       
    //     $city = CityModel::whereIn('id',json_decode($request->id,true));
    //     if($request->text != null){
    //         $city = $city->where('city_name_th','like','%'.$request->text.'%');
    //     }
    //     $city = $city->orderBy('id','desc')->get();
       
    //     $data = '';
    //     $num = json_decode($request->num,true);
    //     foreach($city as $a){
    //         $data .=  "<li><label class='check-container'>";
    //         $data .=  $a->city_name_th ;
    //         $data .=  "<input type='checkbox' name='city' id='city$a->id' onclick='Check_filter($a->id,\"city\")'  value='$a->id'>";
    //         $data .=  "<span class='checkmark'></span> <div class='count'>";
    //         $data .= "(".$num[$a->id].")";
    //         $data .=  "</div></label></li>";
          
    //     }
       
    //     return $data;
    // }
    // public function search_amupur(Request $request){
       
    //     $city = AmupurModel::whereIn('id',json_decode($request->id,true));
    //     if($request->text != null){
    //         $city = $city->where('name_th','like','%'.$request->text.'%');
    //     }
    //     $city = $city->orderBy('id','desc')->get();
       
    //     $data = '';
    //     $num = json_decode($request->num,true);
    //     foreach($city as $a){
    //         $data .=  "<li><label class='check-container'>";
    //         $data .=  $a->name_th ;
    //         $data .=  "<input type='checkbox' name='city' id='city$a->id' onclick='Check_filter($a->id,\"city\")'  value='$a->id'>";
    //         $data .=  "<span class='checkmark'></span> <div class='count'>";
    //         $data .= "(".$num[$a->id].")";
    //         $data .=  "</div></label></li>";
          
    //     }
    //     return $data;
    // }
    // public function search_country(Request $request){
       
    //     $city = CountryModel::whereIn('id',json_decode($request->id,true));
    //     if($request->text != null){
    //         $city = $city->where('country_name_th','like','%'.$request->text.'%');
    //     }
    //     $city = $city->orderBy('id','desc')->get();
       
    //     $data = '';
    //     $num = json_decode($request->num,true);
    //     foreach($city as $a){
    //         $data .=  "<li><label class='check-container'>";
    //         $data .=  $a->country_name_th ;
    //         $data .=  "<input type='checkbox' name='country' id='country$a->id' onclick='Check_filter($a->id,\"country\")'  value='$a->id'>";
    //         $data .=  "<span class='checkmark'></span> <div class='count'>";
    //         $data .= "(".$num[$a->id].")";
    //         $data .=  "</div></label></li>";
          
    //     }
    //     return $data;
    // }
    // public function filter_inthai(Request $request)
    // {
    //     $tour_id = json_decode($request->tour_id,true);
    //     $calendar = json_decode($request->calen_id,true);
    //     $orderby_data = '';
    //     $pe_data = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereNull('tb_tour.deleted_at');
    //     if($tour_id){
    //         $pe_data =  $pe_data->whereIn('tb_tour.id',$tour_id); 
    //     }
    //     if($request->data){
    //         if(isset($request->data['day'])){
    //             $pe_data = $pe_data->whereIn('tb_tour_period.day',$request->data['day']);
    //         }
    //         if(isset($request->data['price'])){
    //             $pe_data = $pe_data->whereIn('tb_tour.price_group',$request->data['price']);
    //         }
    //         if(isset($request->data['airline'])){
    //             $pe_data = $pe_data->whereIn('tb_tour.airline_id',$request->data['airline']);
    //         }
    //         if(isset($request->data['rating'])){
    //             if(!in_array(0,$request->data['rating'])){
    //                 $pe_data = $pe_data->whereIn('tb_tour.rating',$request->data['rating']);
    //             }else{
    //                 $pe_data = $pe_data->whereNull('tb_tour.rating');
    //             }
    //         }if(isset($request->data['month_fil'])){
    //             $pe_data = $pe_data->whereIn('tb_tour_period.group_date',$request->data['month_fil']);
    //         }
         
    //         if(isset($request->data['calen_start'])){
    //             if($calendar){
    //                 foreach($calendar as $c => $calen_date){
    //                     if(in_array($c,$request->data['calen_start'])){
    //                     }else{
    //                         unset($calendar[$c]);
    //                     }
    //                 }
    //             }
                
    //         }
    //     }
    //     if($request->orderby){
    //         $orderby_data = $request->orderby;
    //         // ราคาถูกสุด
    //          if($request->orderby == 1){
    //             $pe_data = $pe_data->orderby('tb_tour.price','asc');
    //         }
    //         // ยอดวิวเยอะสุด
    //         if($request->orderby == 2){
    //             $pe_data = $pe_data->orderby('tb_tour.tour_views','desc');
    //         }
    //         // ลดราคา
    //         if($request->orderby == 3){
    //             $pe_data = $pe_data->where('tb_tour.special_price','>',0)->orderby('tb_tour.special_price','desc');
    //         }
    //         // โปรโมชั่น
    //         if($request->orderby == 4){
    //             $pe_data = $pe_data->whereNotNull('tb_tour_period.promotion_id')->whereDate('tb_tour_period.pro_start_date','<=',date('Y-m-d'))->whereDate('tb_tour_period.pro_end_date','>=',date('Y-m-d'));
    //         }
    //     }
    //     if($request->start_date && $request->end_date){
    //         if($request->start_date){
    //             $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',$request->start_date);
    //         }if($request->end_date){
    //             $pe_data = $pe_data->whereDate('tb_tour_period.end_date','<=',$request->end_date);
    //         }
    //     }else{
    //         $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',now());
    //     }
    //     // if($request->start_date){
    //     //     $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',$request->start_date);
    //     // }if($request->end_date){
    //     //     $pe_data = $pe_data->whereDate('tb_tour_period.end_date','<=',$request->end_date);
    //     // }
    //     // // if($request->start_date_mb ){
    //     // //     $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',$request->start_date_mb);
    //     // // }if($request->end_date_mb){
    //     // //     $pe_data = $pe_data->whereDate('tb_tour_period.start_date','<=',$request->end_date_mb);
    //     // // }
    //     // else{
    //     //     $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',now());
    //     // }
    //     $pe_data = $pe_data->where('tb_tour.status','on')
    //         ->where('tb_tour_period.status_display','on')
    //         ->orderby('tb_tour_period.start_date','asc')
    //         ->orderby('tb_tour.rating','desc')
    //         ->select('tb_tour_period.*')
    //         ->get();
      
    //     if(isset($request->data['calen_start']) && $calendar){    
    //         $id_pe = array();
    //         foreach($pe_data as $da){
    //             $check_test = false;
    //             foreach($calendar as $cid => $calen){
    //                 $start_pe = strtotime($da->start_date);
    //                 while ($start_pe <= strtotime($da->end_date)) {
    //                     if(in_array(date('Y-m-d',$start_pe),$calen)){
    //                         $check_test = true;
    //                         break;
    //                     }
    //                     $start_pe = $start_pe + 86400;
    //                 }
    //             }
    //             if($check_test){
    //                 $id_pe[] = $da->id;
    //             }
    //         }
    //         $pe_data = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereIn('tb_tour_period.id',$id_pe)
    //         ->orderby('tb_tour_period.start_date','asc')
    //         ->orderby('tb_tour.rating','desc')
    //         ->select('tb_tour_period.*')
    //         ->get()
    //         ->groupBy('tour_id');
    //     }else{
    //         $pe_data = $pe_data->groupBy('tour_id');
    //     }
    //     $period = array();
    //     foreach($pe_data as $k => $pe){
    //         $period[$k]['period']  = $pe;
    //         $period[$k]['recomand'] = TourPeriodModel::where('tour_id',$k)
    //         ->where('status_display','on')->where('deleted_at',null)
    //         ->orderby('start_date','asc')
    //         ->limit(2)->get()->groupBy('group_date');
    //         $period[$k]['soldout'] = TourPeriodModel::where('tour_id',$k);
    //         if($request->data){
    //             if(isset($request->data['start_date'])){
    //                 $period[$k]['soldout'] = $period[$k]['soldout']->whereDate('start_date','>=',$request->data['start_date']);
    //             }
    //             if(isset($request->data['end_date'])){
    //                 $period[$k]['soldout'] = $period[$k]['soldout']->whereDate('start_date','<=',$request->data['end_date']);
    //             }
    //         }
    //         $period[$k]['soldout'] = $period[$k]['soldout']->where('status_period',3)->where('status_display','on')
    //         ->where('deleted_at',null)
    //         ->orderby('start_date','asc')
    //         ->get()->groupBy('group_date');
    //             $tour = TourModel::find($k);
    //             $period[$k]['tour'] = $tour;
    //             $period[$k]['country_id'] = json_decode($tour->province_id,true);
    //             $period[$k]['city_id'] = json_decode($tour->district_id,true);
    //     }
      
    //     $filter = array();
    //     foreach($period as $i => $per){
    //         if(isset($request->data['country'])){
    //             if(count(array_intersect($request->data['country'],$per['country_id'])) != count($request->data['country'])){
    //                 unset($period[$i]);
    //             }
    //         }
    //         if(isset($request->data['city'])){
    //             if(count(array_intersect($request->data['city'],$per['city_id'])) != count($request->data['city'])){
    //                 unset($period[$i]);
    //             }
    //         }
    //        if(isset($period[$i])){
    //             //ช่วงราคา
    //             if(isset($filter['price'][$per['tour']->price_group])){
    //                 if(!in_array($per['tour']->id,$filter['price'][$per['tour']->price_group])){
    //                     $filter['price'][$per['tour']->price_group][] = $per['tour']->id;
    //                 }
    //             }else{
    //                 $filter['price'][$per['tour']->price_group][] = $per['tour']->id;
    //             }
    //             //จำนวนวัน
    //             foreach($per['period']  as $p){
    //                 // dd($per['period'] );
    //                 if($p->day){
    //                     if(isset($filter['day'][$p->day])){
    //                         if(!in_array($per['tour']->id,$filter['day'][$p->day])){
    //                             $filter['day'][$p->day][] = $per['tour']->id;
    //                         }
    //                     }else{
    //                         $filter['day'][$p->day][] = $per['tour']->id;
    //                     }
    //                 }
    //                 if($p->start_date){
    //                     //ช่วงเดือน
    //                     $month_start = date('n',strtotime($p->start_date));
    //                     //ช่วงเดือน-ปี
    //                     $year_start = date('Y',strtotime($p->start_date));
    //                     if(isset($filter['year'][$year_start][$month_start])){
    //                         if(!in_array($per['tour']->id,$filter['year'][$year_start][$month_start])){
    //                             $filter['year'][$year_start][$month_start][] = $per['tour']->id;
    //                         }
    //                     }else{
    //                         $filter['year'][$year_start][$month_start][] = $per['tour']->id;
    //                     }
                        
    //                 }
    //                 //วันหยุดเทศกาล
    //                 if($calendar){
    //                     foreach($calendar as $cid => $calen){
    //                         $start_pe = strtotime($p->start_date);
    //                         while ($start_pe <= strtotime($p->end_date)) {
    //                             // echo date('Y-m-d',$start_pe).$tour->tour_id."<br>";
    //                             if(in_array(date('Y-m-d',$start_pe),$calen)){
    //                                 // dd($p);
    //                                 if(isset($filter['calendar'][$cid])){
    //                                     if(!in_array($p->tour_id,$filter['calendar'][$cid])){
    //                                             $filter['calendar'][$cid][] = $p->tour_id;
    //                                         }
    //                                     }else{
    //                                         $filter['calendar'][$cid][] = $p->tour_id;
    //                                     }
    //                             }
    //                             $start_pe = $start_pe + 86400;
    //                         }
    //                     }
    //                 }
                    
    //             }
    //             //ประเทศ
    //             if($per['tour']->province_id){
    //                 if(isset($filter['country'])){
    //                     $filter['country'] = array_merge($filter['country'],json_decode($per['tour']->province_id,true));
    //                     $filter['country'] = array_unique($filter['country']);
    //                 }else{
    //                     $filter['country'] = json_decode($per['tour']->province_id,true);
    //                 }
    //             }
    //             //เมือง
    //             if($per['tour']->district_id){
    //                 if(isset($filter['city'])){
    //                     $filter['city'] = array_merge($filter['city'],json_decode($per['tour']->district_id,true));
    //                     $filter['city'] = array_unique($filter['city']);
    //                 }else{
    //                     $filter['city'] = json_decode($per['tour']->district_id,true);
    //                 }
    //             }
    //             //สายการบิน
    //              if($per['tour']->airline_id){
    //                 $filter['airline'][$per['tour']->airline_id][] = $per['tour']->id;
    //             }
               
    //             //ดาว
    //             $filter['rating'][$per['tour']->rating][] = $per['tour']->rating ;
                   
    //        }
    //     }

    //     // dd($filter);
    //     $num_price = 0;
    //     if(isset($filter['price'])){
    //         foreach($filter['price'] as $p){
    //             $num_price =  $num_price + count($p);
    //         }
    //     }
      
    //     $row = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id');
    //     if($request->data){
    //         if(isset($request->data['start_date']) && isset($request->data['end_date'])){
    //             $row = $row->whereDate('tb_tour_period.start_date','>=',$request->data['start_date'])->whereDate('tb_tour_period.end_date','<=',$request->data['end_date']);
    //         }
    //         // if(isset($request->data['end_date'])){
    //         //     $row = $row ->whereDate('tb_tour_period.start_date','<=',$request->data['end_date']);
    //         // }
    //     }
        
    //     $row = $row->where('tb_tour.status','on')
    //     ->where('tb_tour_period.status_display','on')
    //     ->orderby('tb_tour_period.start_date','asc')
    //     ->select('tb_tour_period.*')
    //     ->get()
    //     ->groupBy('tour_id');

    //     $count_pe = count($period);
       
    //     $data = array(
    //         // 'data' => $dat,
    //         'period' => $period,
    //         'calen_id' => $request->id,
    //         'slug' => $request,
    //         'row' => $row,
    //         'filter' => $filter,
    //         'airline_data' => TravelTypeModel::/* where('status','on')-> */where('deleted_at',null)->get(),
    //         'num_price' => $num_price,
    //         'tour_list' => $this->search_filter($request,$period),
    //         'tour_grid' => $this->search_filter_grid($request,$period),
    //         'count_pe' => $count_pe,
    //         'orderby_data' => $orderby_data,
    //     );
    //     return response()->json($data);
       
    // }

    public function oversea(Request $request,$main_slug)
    {
        try {
            if(!$request->_token){
                Session::put($main_slug,null);
            }
            if(Session::get($main_slug)){
                // dd(Session::get($main_slug));
                return view('frontend.oversea',Session::get($main_slug));
            }else{
                // Platform check 
                $isWin = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows")):0; 
                $isMac = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "macintosh"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "macintosh")):0; 
                $isAndroid = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android")):0; 
                $isIPhone = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone")):0; 
                $isIPad = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad")):0; 
                $country = CountryModel::where('slug',$main_slug)->whereNull('deleted_at')->first();
                $data = array(
                    'country_search' => $country->id,
                    'banner'        => $country->banner,
                    'banner_detail' => $country->banner_detail,
                    'isWin' => $isWin,
                    'isMac' => $isMac,
                    'isAndroid' => $isAndroid,
                    'isIPhone' => $isIPhone,
                    'isIPad' => $isIPad,
                );
                return view('frontend.oversea',$data);
            }
           
           
        } catch (\Throwable $th) {
            // dd($th);
        }
        
    }


    public function inthai(Request $request,$main_slug)
    {
        try {
            // Platform check 
            $isWin = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows")):0; 
            $isMac = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "macintosh"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "macintosh")):0; 
            $isAndroid = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android")):0; 
            $isIPhone = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone")):0; 
            $isIPad = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad")):0; 
            $country = ProvinceModel::where('slug',$main_slug)->whereNull('deleted_at')->first();
            $data = array(
                    'country_search' => $country->id,
                    'banner'        => $country->banner,
                    'banner_detail' => $country->banner_detail,
                    'isWin' => $isWin,
                    'isMac' => $isMac,
                    'isAndroid' => $isAndroid,
                    'isIPhone' => $isIPhone,
                    'isIPad' => $isIPad,
            );
            return view('frontend.inthai',$data);
        } catch (\Throwable $th) {
            // dd($th);
        }
        
    }

    public function search_total(Request $request){
        $country_id = null;
        $city_id = null;
        $travel_id = array();
        $keyword_search = null;
        $tour_code = array();
        $tour_code1 = array();
        $slug_country = null;
        $banner_country = null;
        $detail_country = null;
        $tag_search = null;
        $tag_name = null;
        // รหัสทัวร์
        $code_id = null;
        if($request->search_data){
            $country_keyword =  CountryModel::get();
            $city_keyword =  CityModel::get();
            $travel_keyword =  TourModel::get();
            foreach($country_keyword  as $row){
                // ประเทศ
                if(strstr($request->search_data,$row->country_name_th) || strstr($request->search_data,$row->country_name_en)){
                    $country_count = CountryModel::find($row->id);
                    $country_count->count_search = $country_count->count_search+1;
                    $country_count->save();
                    $country_id = $row->id;
                    $slug_country = $row->slug;
                    $banner_country = $row->banner;
                    $detail_country = $row->banner_detail;
                }
            }
            foreach($city_keyword  as $row){
                // เมือง
                if(strstr($request->search_data,$row->city_name_th) || strstr($request->search_data,$row->city_name_en)){
                    $city_count = CityModel::find($row->id);
                    $city_count->count_search = $city_count->count_search+1;
                    $city_count->save();
                    $city_id = $row->id;
                }
            }
            $keyword = KeywordSearchModel::where('keyword',$request->search_data)->first();
            if($keyword){
                $keyword->count_search = $keyword->count_search + 1;
                $keyword->save();
            }else{
                $keyword_new = new KeywordSearchModel ();
                $keyword_new->keyword = $request->search_data;
                $keyword_new->count_search = $keyword_new->count_search + 1;
                $keyword_new->save();
            }
            if($country_id == null && $city_id == null){
                $keyword_search = $request->search_data;
                foreach($travel_keyword  as $row){
                    // สถานที่ท่องเที่ยว
                    if(stristr($row->travel,$request->search_data)){
                        $travel_id[] = $row->id;
                    }
                }
                $travel_id = array_unique($travel_id);
            }
        }
        if($request->code_tour){
            $code =  TourModel::get();
            $percent = 0;
            $code_id = $request->code_tour;
            foreach($code as $row){
                // รหัสทัวร์
                if(stristr($row->code,$request->code_tour)){
                    $tour_code[] = $row->id;
                }
                if(!empty($row->code1) && strstr($request->code_tour, $row->code1)){ 
                    similar_text($row->code1, $request->code_tour,$percent);
                    if($percent == 100){
                        $tour_code1[] = $row->id;
                    }                
                }
            }
        }
        if($request->tag){
            $tag_search = $request->tag;
            $tag_data = TagContentModel::find($request->tag);
            if (!$tag_data) {
                abort(404, "ไม่พบ Tag: id = '{$request->tag}'");
            }
            $tag_name = $tag_data->tag;
        }
        //dd($request);
        // Platform check 
        $isWin = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows")):0; 
        $isMac = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "macintosh"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "macintosh")):0; 
        $isAndroid = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android")):0; 
        $isIPhone = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone")):0; 
        $isIPad = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad")):0; 
        $data = [
            'price_search' => $request->price,
            'country_search' => $country_id,
            'city_search' => $city_id,
            'travel_search' => $travel_id,
            'keyword_search' => $keyword_search,
            'tour_code' => $tour_code?$tour_code:$tour_code1,
            'code_id' => $code_id,
            'start_search' => $request->start_date,
            'end_search' => $request->end_date,
            'str_start' => $request->start_date?strtotime($request->start_date):0,
            'str_end' => $request->end_date?strtotime($request->end_date):0,
            'banner' => $banner_country,
            'banner_detail' => $detail_country,
            'isWin' => $isWin,
            'isMac' => $isMac,
            'isAndroid' => $isAndroid,
            'isIPhone' => $isIPhone,
            'isIPad' => $isIPad,
            'tag_search' => $tag_search,
            'tag_name' => $tag_name,
        ];
        if($country_id != null){
            Session::put($slug_country,$data);
            return redirect("/oversea/$slug_country?_token=".$request->_token);
        }
        return view('frontend.search-result',$data);
    }
    public function clear_search(Request $request){
       $request->session()->invalidate();
       return response()->json(true);
    }
    public function filter_search(Request $request)
    {
        $travel_id = json_decode($request->travel_id,true);
        $tour_id = json_decode($request->tour_id,true);
        $calendar = json_decode($request->calen_id,true);
        $orderby_data = '';
        $pe_data = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereNull('tb_tour.deleted_at')/* ->whereIn('tb_tour.id',$tour_id) */; 
        if($travel_id){
            $pe_data = $pe_data->whereIn('tb_tour.id',$travel_id);
        }
        if($request->data){
            if(isset($request->data['day'])){
                $pe_data = $pe_data->whereIn('tb_tour_period.day',$request->data['day']);
            }
            if(isset($request->data['price'])){
                $pe_data = $pe_data->whereIn('tb_tour.price_group',$request->data['price']);
            }
            if(isset($request->data['airline'])){
                $pe_data = $pe_data->whereIn('tb_tour.airline_id',$request->data['airline']);
            }
            if(isset($request->data['rating'])){
                if(!in_array(0,$request->data['rating'])){
                    $pe_data = $pe_data->whereIn('tb_tour.rating',$request->data['rating']);
                }else{
                    $pe_data = $pe_data->whereNull('tb_tour.rating');
                }
            }if(isset($request->data['month_fil'])){
                $pe_data = $pe_data->whereIn('tb_tour_period.group_date',$request->data['month_fil']);
            }
            if(isset($request->data['calen_start'])){
                if($calendar){
                    foreach($calendar as $c => $calen_date){
                        if(in_array($c,$request->data['calen_start'])){
                        }else{
                            unset($calendar[$c]);
                        }
                    }
                } 
            }
        }
        if($request->orderby){
            $orderby_data = $request->orderby;
            // ราคาถูกสุด
             if($request->orderby == 1){
                $pe_data = $pe_data->orderby('tb_tour.price','asc');
            }
            // ยอดวิวเยอะสุด
            if($request->orderby == 2){
                $pe_data = $pe_data->orderby('tb_tour.tour_views','desc');
            }
            // ลดราคา
            if($request->orderby == 3){
                $pe_data = $pe_data->where('tb_tour.special_price','>',0)->orderby('tb_tour.special_price','desc');
            }
            // โปรโมชั่น
            if($request->orderby == 4){
                $pe_data = $pe_data->whereNotNull('tb_tour_period.promotion_id')->whereDate('tb_tour_period.pro_start_date','<=',date('Y-m-d'))->whereDate('tb_tour_period.pro_end_date','>=',date('Y-m-d'));
            }
        }
        if($request->start_date || $request->end_date){
            if($request->start_date){
                $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',$request->start_date);
            }
            if($request->end_date){
                $pe_data = $pe_data->whereDate('tb_tour_period.start_date','<=',$request->end_date);
            }else{
                $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',now());
            }
        }else{
            $pe_data = $pe_data->whereDate('tb_tour_period.start_date','>=',now());
            
        }
        $pe_data = $pe_data->where('tb_tour.status','on')
            ->where('tb_tour_period.status_display','on')
            ->orderby('tb_tour_period.start_date','asc')
            ->orderby('tb_tour.rating','desc')
            ->select('tb_tour_period.*')
            ->get();
       
        // dd($pe_data->count());
        if(isset($request->data['calen_start']) && $calendar){    
            $id_pe = array();
            foreach($pe_data as $da){
                $check_test = false;
                foreach($calendar as $cid => $calen){
                    $start_pe = strtotime($da->start_date);
                    while ($start_pe <= strtotime($da->end_date)) {
                        if(in_array(date('Y-m-d',$start_pe),$calen)){
                            $check_test = true;
                            break;
                        }
                        $start_pe = $start_pe + 86400;
                    }
                }
                if($check_test){
                    $id_pe[] = $da->id;
                }
            } $pe_data = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereIn('tb_tour_period.id',$id_pe)
            ->orderby('tb_tour_period.start_date','asc')
            ->orderby('tb_tour.rating','desc')
            ->select('tb_tour_period.*')
            ->get()
            ->groupBy('tour_id');
        }else{
            $pe_data = $pe_data->groupBy('tour_id');
        }
        // dd($pe_data);
        $period = array();
        foreach($pe_data as $k => $pe){
            $period[$k]['period']  = $pe;
            $period[$k]['recomand'] = TourPeriodModel::where('tour_id',$k)
            ->where('status_display','on')->where('deleted_at',null)
            ->orderby('start_date','asc')
            ->limit(2)->get()->groupBy('group_date');
            $period[$k]['soldout'] = TourPeriodModel::where('tour_id',$k);
            if($request->data){
                if(isset($request->data['start_date'])){
                    $period[$k]['soldout'] = $period[$k]['soldout']->whereDate('start_date','>=',$request->data['start_date']);
                }
                if(isset($request->data['end_date'])){
                    $period[$k]['soldout'] = $period[$k]['soldout']->whereDate('start_date','<=',$request->data['end_date']);
                }
            }
            // ->whereDate('start_date','>=',$dat->start_date)
            // ->whereDate('start_date','<=',$dat->end_date)
            $period[$k]['soldout'] = $period[$k]['soldout']->where('status_period',3)->where('status_display','on')
            ->where('deleted_at',null)
            ->orderby('start_date','asc')
            ->get()->groupBy('group_date');
            $tour = TourModel::find($k);
                $period[$k]['tour'] = $tour;
                $period[$k]['country_id'] = json_decode($tour->country_id,true);
                $period[$k]['city_id'] = json_decode($tour->city_id,true); 
        }
        $filter = array();
        foreach($period as $i => $per){
            if(isset($request->data['country'])){
               
                if(count(array_intersect($request->data['country'],$per['country_id'])) != count($request->data['country'])){
                    // dd($per['country_id'],$i,$period[$i]);
                    unset($period[$i]);
                }
            }
            if(isset($request->data['city'])){
                if(count(array_intersect($request->data['city'],$per['city_id'])) != count($request->data['city'])){
                    unset($period[$i]);
                }
            }
           if(isset($period[$i])){
                //ช่วงราคา
                if(isset($filter['price'][$per['tour']->price_group])){
                    if(!in_array($per['tour']->id,$filter['price'][$per['tour']->price_group])){
                        $filter['price'][$per['tour']->price_group][] = $per['tour']->id;
                    }
                }else{
                    $filter['price'][$per['tour']->price_group][] = $per['tour']->id;
                }
                //จำนวนวัน
                foreach($per['period']  as $p){
                    if($p->day){
                        if(isset($filter['day'][$p->day])){
                            if(!in_array($per['tour']->id,$filter['day'][$p->day])){
                                $filter['day'][$p->day][] = $per['tour']->id;
                            }
                        }else{
                            $filter['day'][$p->day][] = $per['tour']->id;
                        }
                    }
                    if($p->start_date){
                        //ช่วงเดือน
                        $month_start = date('n',strtotime($p->start_date));
                        //ช่วงเดือน-ปี
                        $year_start = date('Y',strtotime($p->start_date));
                        if(isset($filter['year'][$year_start][$month_start])){
                            if(!in_array($per['tour']->id,$filter['year'][$year_start][$month_start])){
                                $filter['year'][$year_start][$month_start][] = $per['tour']->id;
                            }
                        }else{
                            $filter['year'][$year_start][$month_start][] = $per['tour']->id;
                        }
                        //วันหยุดเทศกาล
                        if($calendar){
                            foreach($calendar as $cid => $calen){
                                $start_pe = strtotime($p->start_date);
                                while ($start_pe <= strtotime($p->end_date)) {
                                    if(in_array(date('Y-m-d',$start_pe),$calen)){
                                        if(isset($filter['calendar'][$cid])){
                                            if(!in_array($p->tour_id,$filter['calendar'][$cid])){
                                                    $filter['calendar'][$cid][] = $p->tour_id;
                                                }
                                            }else{
                                                $filter['calendar'][$cid][] = $p->tour_id;
                                            }
                                    }
                                    $start_pe = $start_pe + 86400;
                                }
                            }
                        }
                    }
                }
                //ประเทศ
                if($per['tour']->country_id){
                    if(isset($filter['country'])){
                        $filter['country'] = array_merge($filter['country'],json_decode($per['tour']->country_id,true));
                        $filter['country'] = array_unique($filter['country']);
                    }else{
                        $filter['country'] = json_decode($per['tour']->country_id,true);
                    }
                }
                //เมือง
                if($per['tour']->city_id){
                    if(isset($filter['city'])){
                        $filter['city'] = array_merge($filter['city'],json_decode($per['tour']->city_id,true));
                        $filter['city'] = array_unique($filter['city']);
                    }else{
                        $filter['city'] = json_decode($per['tour']->city_id,true);
                    }
                }
                //สายการบิน
                 if($per['tour']->airline_id){
                    $filter['airline'][$per['tour']->airline_id][] = $per['tour']->id;
                }
               
                //ดาว
                $filter['rating'][$per['tour']->rating][] = $per['tour']->rating ;
                   
           }
        }
        // dd($filter,$request->data);
        $num_price = 0;
        if(isset($filter['price'])){
            foreach($filter['price'] as $p){
                $num_price =  $num_price + count($p);
            }
        }
      
        $row = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id');
        if($request->data){
            if(isset($request->data['start_date'])){
                $row = $row->whereDate('tb_tour_period.start_date','>=',$request->data['start_date']);
            }
            if(isset($request->data['end_date'])){
                $row = $row ->whereDate('tb_tour_period.start_date','<=',$request->data['end_date']);
            }
        }
        $row = $row->where('tb_tour.status','on')
        ->where('tb_tour_period.status_display','on')
        ->orderby('tb_tour_period.start_date','asc')
        ->select('tb_tour_period.*')
        ->get()
        ->groupBy('tour_id');
        // dd($period);
        $count_pe = count($period);
        $data = array(
            // 'data' => $dat,
            'period' => $period,
            'calen_id' => $request->calen_id,
            'slug' => $request,
            'row' => $row,
            'filter' => $filter,
            'airline_data' => TravelTypeModel::/* where('status','on')-> */where('deleted_at',null)->get(),
            'num_price' => $num_price,
            'tour_list' => $this->search_filter($request,$period),
            'tour_grid' => $this->search_filter_grid($request,$period),
            'count_pe' => $count_pe,
            'orderby_data' => $orderby_data,
        );
        return response()->json($data);
       
    }

    public function recordPageView(Request $request)
    {
        $dat = TourModel::find($request->id);
        if($dat){
            $dat->increment('tour_views');
        }

        // เก็บยอด views ประเทศ
        $country = CountryModel::whereIn('id',json_decode(@$dat->country_id,true))->get();
        if(count($country) > 0){
            foreach($country as $co){
                $co->increment('country_views');
            }
        }

    }

    public function tour_detail($detail_slug)
    {
        $dat = TourModel::where('slug',$detail_slug)->whereNull('deleted_at')->first();
        
        if (!$dat) {
            abort(404, "ไม่พบทัวร์: slug = '{$detail_slug}'");
        }
        
        $period = TourPeriodModel::where(['tour_id'=>$dat->id,'status_display'=>'on'])->whereDate('start_date','>=',now())->whereNull('deleted_at')->get();
        $min = $period->min('start_date');
        $max = $period->max('start_date');
        $datenow = '2023-07-11';
        if($min && $max){
            $calendar = CalendarModel::whereYear('start_date','>=',date('Y',strtotime($min)))
            ->whereMonth('start_date','>=',date('m',strtotime($min)))
            ->whereDate('start_date','<=',$max)
            ->where('status','on')
            ->whereNull('deleted_at')
            ->get();
        }else{
            $calendar = null;
        }

        if($calendar){
            $arr = array();
            foreach($calendar as $calen){
                $start = strtotime($calen->start_date);
                while ($start <= strtotime($calen->end_date)) {
                    $arr[] = date('Y-m-d',$start);
                    $start = $start + 86400;
                }
            }
        }else{
            $arr = null;
        }
        $data = array(
            'data' => $dat,
            'arr' => $arr,
            'detail_slug' => $detail_slug,
        );
        return view('frontend.tour_detail',$data);
    }

    public function tour_summary($detail_slug,$id)
    {

        Session::forget('booking');
        Session::forget('tour');

        $dat = TourModel::where('slug',$detail_slug)->whereNull('deleted_at')->first();
		
        if (!$dat) {
            abort(404, "ไม่พบทัวร์: slug = '{$detail_slug}'");
        }

        $periods = TourPeriodModel::where(['tour_id'=>$dat->id,'status_display'=>'on','status_period'=>1])->whereDate('start_date','>=',now())->whereNull('deleted_at')->orderby('start_date','asc')->get();

        $period = TourPeriodModel::find($id);
        
        if (!$period) {
            abort(404, "ไม่พบ Period: id = '{$id}' สำหรับทัวร์ '{$detail_slug}'");
        }

        $sales = User::where(['role'=>2,'status'=>'active'])->get();
        
        $data = array(
            'data' => $dat,
            'periods' => $periods,
            'period' => $period,
            'sales' => $sales,
            'period_id' => $id,
        );
        return view('frontend.tour_summary',$data);
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

    public function booking(Request $request){
        //dd($request->all());
        DB::beginTransaction();
        try {
            $recaptcha = $request['g-recaptcha-response'];
            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=6LdQYyIqAAAAAGFTw3OBhEZwsete72cClVP705o_&response=' . $recaptcha . '&remoteip=' . $_SERVER['REMOTE_ADDR'];
            // $url = 'https://www.google.com/recaptcha/api/siteverify?secret=6Le6CQopAAAAAM5FUeFatNKC7Rqc5ziE1FbTuJiY&response=' . $recaptcha . '&remoteip=' . $_SERVER['REMOTE_ADDR'];
            $reponse = json_decode(file_get_contents($url), true);

            if ($reponse['success'] == true && $reponse['score']>=0.5) {
                $data = new BookingFormModel;

                $code_booking = IdGenerator::generate([
                    'table' => 'tb_booking_form', 
                    'field' => 'code', 
                    'length' => 8,
                    'prefix' =>'B'.date('ym'),
                    'reset_on_prefix_change' => true 
                ]);

                $period = TourPeriodModel::where(['tour_id'=>$request->tour_id,'id'=>$request->period_id])->whereNull('deleted_at')->first();

                if($period){
                    $data->start_date = $period->start_date;
                    $data->end_date = $period->end_date;
                }
    
                $data->code = $code_booking;
                $data->tour_id = $request->tour_id;
                $data->period_id = $request->period_id;
                $data->num_twin = $request->qty1;
                $data->num_single = $request->qty2;
                $data->num_child = $request->qty3;
                $data->num_childnb = $request->qty4;
                $data->price1 = $request->price1;
                $data->sum_price1 = $request->sum_price1;
                $data->price2 = $request->price2;
                $data->sum_price2 = $request->sum_price2;
                $data->price3 = $request->price3;
                $data->sum_price3 = $request->sum_price3;
                $data->price4 = $request->price4;
                $data->sum_price4 = $request->sum_price4;
                $data->total_price = $request->net_total;
                $data->total_qty = $request->total_qty;

                $data->name = $request->name;
                $data->surname = $request->surname;
                $data->email = $request->email;
                $data->phone = $request->phone;
                $data->sale_id = $request->sale_id;
                $data->detail = strip_tags($request->detail);

                if($request->tour_id && $request->period_id){
                    if($period->count >= $request->total_qty){
                        $data->status = "Booked";
                    }else{
                        $data->status = "Waiting";
                    }
                }else{
                    $data->status = "Booked";
                }
                // $data->status = "Booked";
                
                
                $check_auth =  MemberModel::find(Auth::guard('Member')->id());
                if($check_auth){
                    $data->member_id = $check_auth->id;
                }
             
                if ($data->save()) {
                    DB::commit();

                    $tour = TourModel::find($data->tour_id);

                    Session::put('booking',$data);
                    Session::put('tour',$tour);

                    $this->sendmail_booking($data,$tour);
                    $this->sendmail_booking_admin($data,$tour);

                    return redirect(url('/booking-success'))->with(['success' => 'บันทึกข้อมูลเรียบร้อย']);
                    // return back()->with(['success' => 'บันทึกข้อมูลเรียบร้อย']);
                } else {
                    DB::rollback();
                    return back()->with(['error' => 'เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง!!']);
                }
    
            }else{
                return back();
            }
            

        } catch (Exception $e) {
            DB::rollback();
            dd($e->getMessage());
        }

    }

    public function booking_success(){
        return view('frontend.tour_success');
    }

    public function sendmail_booking($data,$tour){
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
            // $mail->setFrom('noreply_nexttrip@liw.orangeworkshop.info', 'แจ้งรายละเอียดข้อมูลการสั่งจองทัวร์กับ Next trip Holiday');
            $mail->setFrom('noreply@nexttripholiday.com', 'แจ้งรายละเอียดข้อมูลการสั่งจองทัวร์กับ Next trip Holiday');
            $mail->addAddress($data->email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'แจ้งรายละเอียดข้อมูลการสั่งจองทัวร์กับ Next trip Holiday';
            $mail->Body    = '';
            $mail->Body .= $this->contact_sendmailv_html_header();
            $mail->Body .= $this->contact_sendmailv_html_center($data,$tour);
            $mail->Body .= $this->contact_sendmailv_html_footer($address);

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }

    public function sendmail_booking_admin($data,$tour){
        try {
            $address = ContactModel::find(1);
            $sale = User::find($data->sale_id);
            $link = url("/webpanel/booking-form/view/$data->id");

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
            // $mail->setFrom('noreply_nexttrip@liw.orangeworkshop.info', 'แจ้งรายละเอียดข้อมูลการสั่งจอง');
            $mail->setFrom('noreply@nexttripholiday.com', 'แจ้งรายละเอียดข้อมูลการสั่งจอง');
            $mail->addAddress($address->mail);
            // if($sale){
            //     $mail->addAddress($sale->email);
            // }

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'แจ้งรายละเอียดข้อมูลการสั่งจอง';
            $mail->Body    = '';
            $mail->Body .= $this->contact_sendmailv_html_header();
            $mail->Body .= $this->contact_sendmailv_html_center_admin($data,$tour,$link);
            $mail->Body .= $this->contact_sendmailv_html_footer($address);

            $mail->send();
            echo 'Message has been sent';
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

    public function contact_sendmailv_html_center($data,$tour)
    {
        $month = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        return $detail	= '
                        <tr>
                            <td>
                                <center>
                                    <table width="100%" cellspacing="0" cellpadding="0" style="font-family: Sarabun, sans-serif;border: 1px solid transparent;background-color:transparent; " >
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>เรียน : </b> <span style="margin-left:15px;">คุณ '.$data['name'].' '.$data['surname'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>ชื่อเรื่อง : </b> <span style="margin-left:15px;">แจ้งสถานะข้อมูลการจองโปรแกรมทัวร์</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent; padding-top:.5rem; padding-bottom:.5rem;"><span style="margin-left:15px;">คำสั่งจองหมายเลข <span style="color:orange;">'.$data['code'].'</span> ของคุณ ได้รับการยืนยันการจองเข้ามาแล้ว และผู้ขายได้รับการแจ้งเตือนให้ตรวจสอบคำสั่งจองของคุณแล้ว</span></td>
                                        </tr>
                                        <tr style="background-color:lightgray;line-height: 0.2px; !important;">
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent; padding-top:.5rem; padding-bottom:.5rem;"><b>รายละเอียดคำสั่งจอง </b> <span style="margin-left:15px;"></span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>หมายเลขคำสั่งจอง : </b> <span style="margin-left:15px; color:orange;">'.$data['code'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>สถานะ : </b> <span style="margin-left:15px;">'.$data['status'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>วันที่สั่งจอง : </b> <span style="margin-left:15px;">'. date('d',strtotime($data['created_at'])) .' '. $month[date('n',strtotime($data['created_at']))] .' '. date('y', strtotime('+543 Years', strtotime($data['created_at']))) .' '. date('H:i:s', strtotime($data['created_at'])) .'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>ชื่อ-นามสกุล : </b> <span style="margin-left:15px;">คุณ '.$data['name'].' '.$data['surname'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>รหัสทัวร์ : </b> <span style="margin-left:15px;">'.@$tour['code'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>ชื่อโปรแกรมทัวร์ : </b> <span style="margin-left:15px;">'.@$tour['name'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>วันที่เดินทาง : </b> <span style="margin-left:15px;">'. date('d',strtotime($data['start_date'])) .' '. $month[date('n',strtotime($data['start_date']))] .' '. date('y', strtotime('+543 Years', strtotime($data['start_date']))) .' - '. date('d',strtotime($data['end_date'])) .' '. $month[date('n',strtotime($data['end_date']))] .' '. date('y', strtotime('+543 Years', strtotime($data['end_date']))) .'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>จำนวน : </b> <span style="margin-left:15px;">'.@$data['total_qty'].' คน</span></td>
                                        </tr>
                                        <tr style="background-color:lightgray;line-height: 0.2px; !important;">
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>เงื่อนไขการสำรองที่นั่งกับ Next Trip Holiday </b><br><ul><li>โปรดรอเจ้าหน้าที่ติดต่อกลับเพื่อทำการ Confirm ที่นั่ง</li><li>การสำรองที่นั่งนี้จะสมบูรณ์ก็ต่อเมื่อทางบริษัทฯ ได้รับการชำระเงินเรียบร้อยแล้ว</li></ul></td>
                                        </tr>
                                    </table>
                                </center>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>';
    }

    public function contact_sendmailv_html_center_admin($data,$tour,$link)
    {
        $month = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
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
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent; padding-top:.5rem; padding-bottom:.5rem;"><span style="margin-left:15px;">มีคำสั่งจองหมายเลข <span style="color:orange;">'.$data['code'].'</span> ของคุณ '.$data['name'].' '.$data['surname'].' เข้ามาในระบบ สามารถตรวจสอบรายละเอียดการจองเพิ่มเติมได้ที่เมนูข้อมูลการจองทัวร์</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent; padding-top:.5rem; padding-bottom:.5rem;"><a href="'.$link.'"><button type="button" class="btn btn-warning w-24" style="background-color:orange; border:none; height:35px; border-radius:20px;">รายละเอียดการจอง</button></a></td>
                                        </tr>
                                        <tr style="background-color:lightgray;line-height: 0.2px; !important;">
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent; padding-top:.5rem; padding-bottom:.5rem;"><b>รายละเอียดคำสั่งจอง </b> <span style="margin-left:15px;"></span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>หมายเลขคำสั่งจอง : </b> <span style="margin-left:15px; color:orange;">'.$data['code'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>สถานะ : </b> <span style="margin-left:15px;">'.$data['status'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>วันที่สั่งจอง : </b> <span style="margin-left:15px;">'. date('d',strtotime($data['created_at'])) .' '. $month[date('n',strtotime($data['created_at']))] .' '. date('y', strtotime('+543 Years', strtotime($data['created_at']))) .' '. date('H:i:s', strtotime($data['created_at'])) .'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>ชื่อ-นามสกุล : </b> <span style="margin-left:15px;">คุณ '.$data['name'].' '.$data['surname'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>รหัสทัวร์ : </b> <span style="margin-left:15px;">'.@$tour['code'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>ชื่อโปรแกรมทัวร์ : </b> <span style="margin-left:15px;">'.@$tour['name'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>วันที่เดินทาง : </b> <span style="margin-left:15px;">'. date('d',strtotime($data['start_date'])) .' '. $month[date('n',strtotime($data['start_date']))] .' '. date('y', strtotime('+543 Years', strtotime($data['start_date']))) .' - '. date('d',strtotime($data['end_date'])) .' '. $month[date('n',strtotime($data['end_date']))] .' '. date('y', strtotime('+543 Years', strtotime($data['end_date']))) .'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>จำนวน : </b> <span style="margin-left:15px;">'.@$data['total_qty'].' คน</span></td>
                                        </tr>
                                        <tr style="background-color:lightgray;line-height: 0.2px; !important;">
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>เงื่อนไขการสำรองที่นั่งกับ Next Trip Holiday </b><br><ul><li>โปรดรอเจ้าหน้าที่ติดต่อกลับเพื่อทำการ Confirm ที่นั่ง</li><li>การสำรองที่นั่งนี้จะสมบูรณ์ก็ต่อเมื่อทางบริษัทฯ ได้รับการชำระเงินเรียบร้อยแล้ว</li></ul></td>
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