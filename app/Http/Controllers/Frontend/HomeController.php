<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\Member;

use App\Models\Backend\BannerSlideModel;
use App\Models\Backend\BannerAdsModel;
use App\Models\Backend\CustomerInfoModel;
use App\Models\Backend\GalleryCustomerModel;
use App\Models\Backend\AboutLicensModel;
use App\Models\Backend\AboutUsModel;
use App\Models\Backend\AwardModel;
use App\Models\Backend\BusinessModel;
use App\Models\Backend\BannerModel;
use App\Models\Backend\CustomerGroupsModel;
use App\Models\Backend\TagContentModel;
use App\Models\Backend\TravelModel;
use App\Models\Backend\TypeNewModel;
use App\Models\Backend\CountryModel;
use App\Models\Backend\VideoModel;
use App\Models\Backend\NewModel;
use App\Models\Backend\ThankInfoModel;
use App\Models\Backend\QuestionModel;
use App\Models\Backend\ContactModel;
use App\Models\Backend\TopicContactModel;
use App\Models\Backend\GroupContentModel;
use App\Models\Backend\GroupListModel;
use App\Models\Backend\GroupModel;
use App\Models\Backend\PackageModel;
use App\Models\Backend\PromotionModel;
use App\Models\Backend\TourModel;
use App\Models\Backend\TourPeriodModel;
use App\Models\Backend\CalendarModel;
use App\Models\Backend\CityModel;
use App\Models\Backend\FooterModel;
use App\Models\Backend\FooterListModel;
use App\Models\Backend\KeywordSearchModel;
use App\Models\Backend\TypeArticleModel;
use App\Models\Backend\StatusSlideModel;
use App\Models\Backend\TravelTypeModel;
use App\Models\Backend\TourTypeModel;
use App\Models\Backend\AmupurModel;
use App\Models\Backend\ProvinceModel;
use App\Models\Backend\MessageModel;
use App\Models\Backend\MemberModel;
use App\Models\Backend\BookingFormModel;
use setasign\Fpdi\Fpdi;
use App\Models\Backend\ImagePDFModel;

use DB;
use Session;
use Laravel\Socialite\Facades\Socialite;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


class HomeController extends Controller
 
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function error_page(){
        return view('error-page');
    }
     public function generateStrongPassword($length = 10, $add_dashes = false, $available_sets = 'luds')
     {
         $sets = array();
         if(strpos($available_sets, 'l') !== false)
             $sets[] = 'abcdefghjkmnpqrstuvwxyz';
         if(strpos($available_sets, 'u') !== false)
             $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
         if(strpos($available_sets, 'd') !== false)
             $sets[] = '23456789';
         if(strpos($available_sets, 's') !== false)
             $sets[] = '!@#$%&*?';
 
         $all = '';
         $password = '';
         foreach($sets as $set)
         {
             $password .= $set[array_rand(str_split($set))];
             $all .= $set;
         }
 
         $all = str_split($all);
         for($i = 0; $i < $length - count($sets); $i++)
             $password .= $all[array_rand($all)];
 
         $password = str_shuffle($password);
 
         if(!$add_dashes)
             return $password;
 
         $dash_len = floor(sqrt($length));
         $dash_str = '';
         while(strlen($password) > $dash_len)
         {
             $dash_str .= substr($password, 0, $dash_len) . '-';
             $password = substr($password, $dash_len);
         }
         $dash_str .= $password;
         // dd($dash_str);
         return $dash_str;
     }
     public function LogIn(Request $request)
     {
         if (Auth::guard('Member')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $member = MemberModel::find(Auth::guard('Member')->id());
            $member->login_by = 'R';
            $member->updated_at = date('Y-m-d H:i:s');
            $member->save();
             return redirect('/member-booking');
         } else {
             return redirect('/')->with(['error' => 'ชื่อผู้ใช้งาน หรือรหัสผ่านผิด !']);
         }
     }
     public function logOut()
     {
         if (!Auth::guard('Member')->logout()) {
             return redirect("/");
         }
     }
    
     public function forgot(Request $request){
         $mail = $request->reset_email;
         // $token = $request->_token;
         $data = $this->chk_email($mail);
         if ($data) {
             $random = $this->generateStrongPassword();
             $mem = MemberModel::where('email',$mail)->whereNull('deleted_at')->first();
             $mem->password = bcrypt($random);
             if($mem->save()){
                 $re_pass = [
                     'email' => $request->reset_email,
                     'password' =>  $random,
                 ];
                 $this->contact_sendmail($re_pass);
                 return redirect(url('/'))->with('success', 'กรุณาตรวจสอบที่อีเมล');
             }else{
                 return redirect(url('/'))->with('error', 'เกิดข้อผิดพลาด!!');
             }
 
         } else {
             return redirect(url('/'))->with('error', 'ไม่มีอีเมลนี้ในระบบ!!');
         }
     }
     public function chk_email($mail)
     {
         $data = MemberModel::where('email',$mail)->whereNull('deleted_at')->first();
         return $data;
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
                                         <tr style="font-size:18px;">
                                             <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>อีเมล : </b> <span style="margin-left:15px;">'.$data['email'].'</span></td> 
                                         </tr>
                                         <tr style="font-size:18px;">
                                             <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>รหัสผ่านใหม่ : </b> <span style="margin-left:15px;">'.$data['password'].'</span></td>
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
 
     public function contact_sendmail($data)
     {
        
         $mail = new PHPMailer(true);
         try { 	 
             $contact = ContactModel::find(1);
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
             $mail->setFrom('noreply@nexttripholiday.com', 'ติดต่อจากเว็บไซต์ Nexttripholiday');
             $mail->addAddress($data['email']);
 
             // Content
             $mail->isHTML(true);
             $mail->Subject = 'รหัสผ่านใหม่สำหรับเข้าระบบสมาชิก nexttripholiday.com';
             $mail->Body    = '';
             $mail->Body .= $this->contact_sendmailv_html_header();
             $mail->Body .= $this->contact_sendmailv_html_center($data);
             $mail->Body .= $this->contact_sendmailv_html_footer($contact);
             $mail->send();
             echo 'Message has been sent';
         } catch (Exception $e) {
             dd($e);
             echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
         }
     }
 
     public function loginLine(Request $request)
     {
        // dd($request);
        try {
            $data = MemberModel::where('line_id',$request->profile['userId'])->whereNull('deleted_at')->first();
            // dd($check);
            if(!$data){
                $data = new MemberModel();
                $data->name = $request->profile['displayName'];
                $data->line_id = $request->profile['userId'];
                $data->status_message = '[]';
                $data->accept = 'Y';
                $data->type = 'L';
                $data->login_by = 'L';
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');
                $data->save();
                // dd($data);
            } 
            Auth::guard('Member')->loginUsingId($data->id);
            return response()->json(true);
        } catch (\Throwable $th) {
            //throw $th;
        } 
     }
     
     public function redirectToFacebook()
     {
         return Socialite::driver('facebook')->redirect();
     }
     public function handleFacebookCallback(Request $request)
     {
        if($request->error_code || $request->error){
            return redirect('')->with('error', 'ล็อคอินไม่สำเร็จ กรุณาล็อคอินใหม่');
        }
        try {
             $user = Socialite::driver('facebook')->user();
             $data = MemberModel::where('facebook_id',$user->id)->first();
            //  dd($user);
            //  if(!$data){
            //     if($user->email){
            //         $data = MemberModel::where('email',$user->email)->first();
            //     } 
            //  }
             if(!$data){
                 $data = new MemberModel();
                 $data->name = $user->name;
                //  $data->email = $user->email;
                 $data->facebook_id = $user->id;
                 $data->status_message = '[]';
                 $data->accept = 'Y';
                 $data->type = 'F';
                 $data->login_by = 'F';
                 $data->created_at = date('Y-m-d H:i:s');
                 $data->updated_at = date('Y-m-d H:i:s');
                 $data->save();
             }else{
                 $data->facebook_id = $user->id;
                 $data->login_by = 'F';
                 $data->updated_at = date('Y-m-d H:i:s');
                 $data->save();
             }
             Auth::guard('Member')->loginUsingId($data->id);
             return redirect('/member-booking');
             
        
         } catch (Exception $e) {
             dd($e->getMessage());
         }
     }
     public function redirectToGoogle()
     {
         return Socialite::driver('google')->redirect();
     }
     public function handleGoogleCallback(Request $request)
     {
        if($request->error){
            return redirect('');
        }
         try {
             $user = Socialite::driver('google')->user();
             $data = MemberModel::where('google_id',$user->id)->first();
             if(!$data){
                if($user->email){
                    $data = MemberModel::where('email',$user->email)->first();
                }
             }
             if(!$data){
                 $data = new MemberModel();
                 $data->name = $user->name;
                 $data->email = $user->email;
                 $data->google_id = $user->id;
                 $data->status_message = '[]';
                 $data->accept = 'Y';
                 $data->type = 'G';
                 $data->login_by = 'G';
                 $data->created_at = date('Y-m-d H:i:s');
                 $data->updated_at = date('Y-m-d H:i:s');
                 $data->save();
             }else{
                 $data->google_id = $user->id;
                 $data->login_by = 'G';
                 $data->updated_at = date('Y-m-d H:i:s');
                 $data->save();
             }
             Auth::guard('Member')->loginUsingId($data->id);
             return redirect('/member-booking');
            
         } catch (Exception $e) {
             dd($e->getMessage());
         }
     }
     public function line_callback(Request $request){
        if($request->error){
            dd($request);
            return redirect('');
        }
        return view('frontend.line-callback');
     }
     public function member(){

        $member_id = MemberModel::find(Auth::guard('Member')->id());
        $check_type = null ;
        if(!$member_id->google_id && !$member_id->facebook_id && !$member_id->line_id){
            $check_type = 1; //แอคเคาท์ที่มาจากสมัครสมาชิก
        }else{
            $check_type = 3; //แอคเคาท์ที่มาจากโซเชียลลล็อคอิน
        }
        $booking = BookingFormModel::where('member_id',$member_id->id)->get();
        // dd($member_id,$booking);
        $message = MessageModel::whereDate('created_at','>=',$member_id->created_at)->orderBy('id','desc')->get();
        $read_mes = MessageModel::whereNotIn('id',json_decode($member_id->status_message,true))->whereDate('created_at','>=',$member_id->created_at)->count();
        $data = [
            'booking' => $booking,
            'message' => $message,
            'member' => $member_id,
            'read_message' => $read_mes,
            'check_type' =>  $check_type,
        ];
        // dd($data);
        return view('frontend.member_booking',$data);
    }
    public function update_message(Request $request){
        $member = MemberModel::find($request->id);
        $check = json_decode($member->status_message,true);
        if(!in_array($request->message,$check)){
            $check[] = $request->message;
            $member->status_message =  json_encode($check);
            $member->updated_at = date('Y-m-d H:i:s');
            $member->save();
        }
        $count =  MessageModel::whereNotIn('id',json_decode($member->status_message,true))->whereDate('created_at','>=',$member->created_at)->count();
        return response()->json($count);
     
    }
    public function register(Request $request){
        try {
            $data = $this->chk_email($request->email);
            if($data){
                return response()->json(false);
            }else{
                $data = new MemberModel();
                $data->name = $request->name;
                $data->surname = $request->surname;
                $data->email = $request->email;
                $data->phone = $request->phone;
                $data->password = bcrypt($request->password);
                $data->status_message = '[]';
                $data->accept = 'Y';
                $data->type = 'R';
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');
                if($data->save()){
                   return response()->json(true);
                }
            }
        } catch (\Throwable $th) { } 
    }
   
    public function update_member(Request $request){
        try {
            $data = MemberModel::find(Auth::guard('Member')->id());
            $check_mail = $request->email?MemberModel::where('email',$request->email)->where('id','!=',$data->id)->count():0;
            // dd($data,$check_mail);
            if($data && !$check_mail){
                if($request->password){
                    $data->password = bcrypt($request->password);
                }
                $data->name = $request->name;
                $data->surname = $request->surname;
                $data->email = $request->email;
                $data->phone = $request->phone;
                $data->accept = 'Y';
                $data->updated_at = date('Y-m-d H:i:s');
                if($data->save()){
                    return response()->json($data);
                }
            }else{
                return response()->json(false);
            }
        } catch (\Throwable $th) {
           dd($th);
        }
    }
    public function get_data(Request $request){
       
        $data = TourPeriodModel::where('start_date','>=',now())
        ->where('status_display','on')
        ->whereNull('deleted_at')
        ->orderBy('start_date','asc')
        ->get();
        $calendar = CalendarModel::where('start_date','>=',now())
        ->where('status','on')
        ->whereNull('deleted_at')
        ->get();
        $tour_id = array();
        $country_id = array();
        $city_id = array();
        $province_id = array();
        $amupur_id = array();
        $airline_id = array();
        $day_num = array();
        $rating = array();
        $month_start = array();
        $month = array();
        $holiday = array();
        $special_price = array();
        $promotion = array();
        $date_now = strtotime(now());
        $holiday_date = array();
        $type_id = array();
        $new_period = array();
    
        foreach($calendar as $cal){
            $holiday[] = [
                'id' => $cal->id,
                'name' => $cal->holiday,
                'start' => $cal->start_date,
                'end' => $cal->end_date,
                'num_start' => strtotime($cal->start_date),
                'num_end' => strtotime($cal->end_date),
            ];
            $start = strtotime($cal->start_date);
                while ($start <= strtotime($cal->end_date)) {
                    // $holiday_date[] = date('Y-m-d',$start);
                    $holiday_date[] = $start;
                    $start = $start + 86400;
                }
        }
        foreach($data as $dat){
            $tour_id[] = $dat->tour_id*1;
            if((int) $dat->day > 0){
                $day_num[] = $dat->day;
            }
            $month_start[] =  date('Y-n',strtotime($dat->start_date));
           
            if($dat->promotion_id != null && strtotime($dat->pro_start_date) <= $date_now && strtotime($dat->pro_end_date) >= $date_now){
                $promotion[] = $dat->tour_id*1;
            }
            $str_start = strtotime($dat->start_date);
            $str_end = strtotime($dat->end_date);
            $period_date = 0;  
            $count_holiday = '';   
            $max = ($str_end-$str_start)/86400;
            $count_holiday = '';       
            for($x=0;$x<=$max;$x++){
                if(in_array($str_start,$holiday_date)){
                        $period_date++;
                        $count_holiday = $count_holiday.'-';                                                  
                    }
                    $str_start = $str_start+86400;
                }
            $new_period[] = [
                'id' => $dat->id,
                'tour_id' => $dat->tour_id*1,
                'group_date' => $dat->group_date,
                'start_date' => $dat->start_date,
                'end_date' => $dat->end_date,
                'check_start' => strtotime($dat->start_date),
                'check_end' => strtotime($dat->end_date),
                'day'    => $dat->day,
                'period_date' =>  $period_date,
                'count_holiday' => $count_holiday,
                'status_period' => $dat->status_period,
                'count' => $dat->count,
                'price1' =>  $dat->price1,
                'special_price1' => $dat->special_price1,
            ];
        }
        $month_start =  array_unique($month_start);
        $promotion =  array_unique($promotion);
        foreach($month_start as $m){
            $month[date('Y',strtotime($m))][] = date('m',strtotime($m));
            sort($month[date('Y',strtotime($m))]);
        }
        $tour_id = array_unique($tour_id);
        $day_num =  array_unique($day_num);
        sort($day_num);
        $tour_data = TourModel::whereIn('id', $tour_id)->where('status','on')->whereNull('deleted_at')->get();
        foreach($tour_data as $t){
            $country_id = array_merge(json_decode($t->country_id,true),$country_id);
            $city_id = array_merge(json_decode($t->city_id,true),$city_id);
            $province_id = array_merge(json_decode($t->province_id,true),$province_id);
            $amupur_id = array_merge(json_decode($t->district_id,true),$amupur_id);
            $airline_id[] = $t->airline_id;
            $rating[] = $t->rating?$t->rating:"0";
            if($t->special_price > 0){
                $special_price[] = $t->id;
            }
            $type_id[] = $t->type_id;
        }
        
      
        $country_id = array_unique($country_id);
        $city_id = array_unique($city_id);
        $province_id = array_unique($province_id);
        $amupur_id = array_unique($amupur_id);
        $airline_id = array_unique($airline_id);
        $holiday_date = array_unique($holiday_date);
        $rating = array_unique($rating);
        $type_id = array_unique($type_id);
        $country = CountryModel::whereIn('id',$country_id)->where('status','on')->whereNull('deleted_at')->get();
        $city = CityModel::whereIn('id',$city_id)->where('status','on')->whereNull('deleted_at')->get();
        $province = ProvinceModel::whereIn('id',$province_id)->where('status','on')->whereNull('deleted_at')->get();
        $amupur = AmupurModel::whereIn('id',$amupur_id)->where('status','on')->whereNull('deleted_at')->get();
        $price = ['','ต่ำกว่า 10,000','10,001-20,000','20,001-30,000','30,001-50,000','50,001-80,000','80,001 ขึ้นไป'];
        $airline = TravelTypeModel::whereIn('id',$airline_id)->where('status','on')->whereNull('deleted_at')->get();
        $month_data =['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
        $month_period = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        $tour_type = TourTypeModel::whereIn('id',$type_id)->where('status','on')->whereNull('deleted_at')->get();
        
        $rating = array_values($rating);
        $promotion = array_values($promotion);

        $period = json_encode($new_period);
        $tour = json_encode($tour_data);
        $country = json_encode($country);
        $city = json_encode($city);
        $province = json_encode($province);
        $amupur = json_encode($amupur);
        $price = json_encode($price);
        $airline = json_encode($airline);
        $rating = json_encode($rating);
        $day_num = json_encode($day_num);
        $month = json_encode($month);
        $holiday = json_encode($holiday);
        $month_data = json_encode($month_data);
        $month_period = json_encode($month_period);
        $special_price = json_encode($special_price);
        $promotion = json_encode($promotion);
        $holiday_date = json_encode($holiday_date);
        $tour_type = json_encode($tour_type);
        //  dd($tour_type,json_encode($promotion));
      

        $script="var period = $period;";
        $script.="var tour = $tour;";
        $script.="var country = $country;";
        $script.="var province = $province;";
        $script.="var price = $price;";
        $script.="var airline = $airline;";
        $script.="var rating = $rating;";
        $script.="var day_num = $day_num;";
        $script.="var month = $month;";
        $script.="var holiday  = $holiday;";
        $script.="var month_data  = $month_data;";
        $script.="var month_period  = $month_period;";
        $script.="var city = $city;";
        $script.="var amupur = $amupur;";
        $script.="var special_price = $special_price;";
        $script.="var promotion = $promotion;";
        $script.="var holiday_date = $holiday_date;";
        $script.="var tour_type = $tour_type;";

        $fileName="script-filter.js";
        $filePath =  public_path($fileName);
        file_put_contents($filePath, $script);
        return response()->json([]);
    }
    public function index()
    {
      
        $slide = BannerSlideModel::where('status','on')->orderBy('id','desc')->get();
        $ads = BannerAdsModel::where('status','on')->orderBy('id','desc')->get();
        $customer = CustomerInfoModel::where('type_id',2)->where('status','on')->get();
        $check = ThankInfoModel::where('status','on')->orderBy('id','desc')->get();
         
        $re_country = array();
        foreach($check as $re){
            $re_country = array_merge($re_country,json_decode($re->country_id,true));
        }
        
        $re_country = array_unique($re_country);
        $review = ThankInfoModel::where('country_id','!=',$re_country)->where('status','on')->orderBy('id','desc')->limit(6)->get();
        $calendar = CalendarModel::where(['status'=>'on','deleted_at'=>null])->where('start_date','>=',date('Y-m-d'))->orderby('start_date','asc')->get();
        $footer = FooterModel::find(1);
        $footer_list = FooterListModel::get();

        $country = CountryModel::where(['status'=>'on','deleted_at'=>null])->orderby('country_views','desc')->limit(6)->get();
        // $tour_views = TourModel::where(['status'=>'on','deleted_at'=>null])->orderby('tour_views','desc')->limit(4)->get();
        $tour_views = TourModel::where(['tb_tour.status'=>'on','tb_tour.deleted_at'=>null])
        ->leftjoin('tb_tour_period','tb_tour.id','=','tb_tour_period.tour_id')
        ->where('tb_tour_period.status_display', 'on')
        ->where('tb_tour_period.count','>',0)
        ->whereDate('tb_tour_period.start_date','>=',now())
        ->whereNull('tb_tour_period.deleted_at')
        ->where('tb_tour_period.status_period','!=',3)
        ->orderby('tb_tour_period.start_date','asc')
        ->groupBy('tb_tour_period.tour_id')
        ->select('tb_tour.*')
        ->orderby('tb_tour.tour_views','desc')->limit(4)->get();
        $status = StatusSlideModel::find(1);

        $data_country = CountryModel::where(['status'=>'on','deleted_at'=>null])->whereNotNull('country_name_th')->get();
        $data_city = CityModel::where(['status'=>'on','deleted_at'=>null])->whereNotNull('city_name_th')->get();
        $data_province = ProvinceModel::where(['status'=>'on','deleted_at'=>null])->whereNotNull('name_th')->get();
        $data_amupur= AmupurModel::where(['status'=>'on','deleted_at'=>null])->whereNotNull('name_th')->get();

        $country_famus = CountryModel::where('count_search','!=',0)->orderBy('count_search','desc')->limit(3)->get();
        $keyword_famus = KeywordSearchModel::orderBy('count_search','desc')->limit(10)->get();
        // dd($keyword_famus);
        $data = array(
          'slide' => $slide,
          'ads' => $ads,
          'customer' => $customer,
          'review' => $review,
          'calendar' => $calendar,
          'footer' => $footer,
          'footer_list' => $footer_list,
          'country' => $country,
          'tour_views' => $tour_views,
          'status' => $status,
          'data_country' => json_encode($data_country),
          'data_city' => json_encode($data_city),
          'country_famus' => json_encode($country_famus),
          'keyword_famus' => json_encode($keyword_famus),
          'data_province' => json_encode($data_province),
          'data_amupur' => json_encode($data_amupur),

        );
        return view('frontend.index',$data);
    }
    public function about()
    {
        $banner = BannerModel::where('id',6)->first();
        $about = AboutUsModel::find(1);
        $licen = AboutLicensModel::orderBy('id','desc')->get();
        $award = AwardModel::orderBy('id','desc')->get();
        $business = BusinessModel::orderBy('id','asc')->get();
        $group = CustomerGroupsModel::orderBy('id','asc')->get();
        
        $data = array(
          'banner' => $banner,
          'about' => $about,
          'licen' => $licen,
          'award' => $award,
          'business' => $business,
          'group' => $group,
        );
        return view('frontend.about',$data);
    }
    public function aroundworld($id,$tyid,$tid)
    {
        // dd($id,'id',$tyid,'tyid',$tid,'tid');
        $banner = BannerModel::where('id',5)->first();
        $travel = TravelModel::where('status','on');
        if($id){
            $travel = $travel->where('country_id','like','%"'.$id.'"%');
        }
        if($tyid){
            $travel = $travel->where('type_id',$tyid*1);
        }
        if($tid){
            $travel = $travel->where('tag','like','%"'.$tid.'"%');
        }
        $travel = $travel->orderBy('id','desc')->paginate(12);
        // dd($travel);
        $latest = TravelModel::where('status','on')->orderBy('id','desc')->limit(3)->get();
        $popular = TravelModel::where('status','on')->orderBy('views','desc')->limit(3)->get();
        $row = TravelModel::where('status','on')->orderBy('id','desc')->get();
        
        $info = array();
        foreach($travel as $t => $tra){
            $info[$t]['tour'] = $tra;
          
        }
        // dd($info);
        $count = TravelModel::where('status','on')->orderBy('id','desc')->count();
        $month =['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
        $addYear = 543;
        $data = array(
            'banner' => $banner,
            'info' => $info,
            'count' => $count,
            'country_id' => $id,
            'tag_id' => $tid,
            'travel' => $travel,
            'latest' => $latest,
            'popular' => $popular,
            'month' => $month,
            'addYear' => $addYear,
            'type_id' => $tyid,
            'row' => $row,

        );
        return view('frontend.aroundworld',$data);
    }
    public function around_detail($id)
    {
        $travel = TravelModel::find($id);
        $now = TravelModel::where('id','!=',$id)->where('status','on')->orderBy('id','desc')->get();
        $connect = TravelModel::where('id','!=',$id)->where('status','on')->orderBy('id','desc')->limit(3)->get();

        $row = TravelModel::where('status','on')->orderBy('id','desc')->get();

        $type_a = TypeArticleModel::find($travel->type_id);
        $tag = TagContentModel::whereIn('id',json_decode($travel->tag,true))->get();
        $country_id = json_decode($travel->country_id,true);
        $tour_id = array();
        foreach($country_id as $c){
           $check_tour =  TourModel::where('country_id','like','%"'.$c.'"%')->get(['id']);
           foreach($check_tour as $t){
                $tour_id[] = $t->id;
           }
        }
        $tour_id = array_unique($tour_id);
        $tour = TourModel::whereIn('id',$tour_id)
            ->where(['status'=>'on','deleted_at'=>null])
            ->orderby('id','desc')
            ->limit(12)
            ->get();
        $popular = TravelModel::where('id','!=',$id)->where('status','on')->orderBy('views','desc')->limit(3)->get();
        $month =['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
        $addYear = 543;
        $data = array(
            'row' => $travel,
            'now' => $now,
            'connect' => $connect,
            'type_a' => $type_a,
            'tag' => $tag,
            'id' => $id,
            'tour' => $tour,
            'month' => $month,
            'addYear' => $addYear,
            'popular' => $popular,
            'country_id' => $country_id,
            'row_all' => $row,
        );
        return view('frontend.around_detail',$data);
    }
    public function recordPageView($id)
    {
        TravelModel::where('id',$id)->increment('views');
    }
    public function clients_company($id)
    {
        $banner = BannerModel::where('id',7)->first();
        if($id){
            $row = CustomerInfoModel::where('tag','like','%"'.$id.'"%')->where('type_id',1)->where('status','on')->orderBy('id','desc')->paginate(6);
            $private  = CustomerInfoModel::where('tag','like','%"'.$id.'"%')->where('type_id',1)->where('status','on')->count();
        }else{
            $row = CustomerInfoModel::where('type_id',1)->where('status','on')->orderBy('id','desc')->paginate(6);
            $private  = CustomerInfoModel::where('type_id',1)->where('status','on')->count();
        }
       
        $data = array(
        'banner' => $banner,
        'row' => $row,
        'private' => $private,
        );
        return view('frontend.clients_company',$data);
    }
    public function clients_detail($id)
    {
        $row = CustomerInfoModel::find($id);
        $gallery = GalleryCustomerModel::where('cus_id',$id)->orderBy('id','desc')->get();
        $tag = TagContentModel::whereIn('id',json_decode($row->tag,true))->get();
        $count_gal = GalleryCustomerModel::where('cus_id',$id)->orderBy('id','desc')->count();
        $data = array(
            'row' => $row,
            'gallery' => $gallery,
            'tag' => $tag,
            'count_gal' => $count_gal,
        );
        return view('frontend.clients_detail',$data);
    }
    public function clients_review($id,$cid)
    {
        $banner = BannerModel::where('id',10)->first();
        if($id){
            if($cid){
                $recomend = ThankInfoModel::where('country_id','like','%"'.$id.'"%')->where('city_id','like','%"'.$cid.'"%')->where('status','on')->orderBy('id','desc')->limit(3)->get();
            }else{
                $recomend = ThankInfoModel::where('country_id','like','%"'.$id.'"%')->where('status','on')->orderBy('id','desc')->limit(3)->get();
            }
        }else{
            if($cid){
                $recomend = ThankInfoModel::where('city_id','like','%"'.$cid.'"%')->where('status','on')->orderBy('id','desc')->limit(3)->get();
            }else{
                $recomend = ThankInfoModel::where('status','on')->orderBy('id','desc')->limit(3)->get();
            }
        }
        $now_id = array();
         foreach($recomend as $n){
            $now_id[] = $n->id;
        }
        if($id){
            if($cid){
                $row = ThankInfoModel::whereNotIn('id',$now_id)->where('country_id','like','%"'.$id.'"%')->where('city_id','like','%"'.$cid.'"%')->where('status','on')->orderBy('id','desc')->paginate(12);
            }else{
                $row = ThankInfoModel::whereNotIn('id',$now_id)->where('country_id','like','%"'.$id.'"%')->where('status','on')->orderBy('id','desc')->paginate(12);
            }
        }else{
            if($cid){
                $row = ThankInfoModel::whereNotIn('id',$now_id)->where('city_id','like','%"'.$cid.'"%')->where('status','on')->orderBy('id','desc')->paginate(12);
            }else{
                $row = ThankInfoModel::whereNotIn('id',$now_id)->where('status','on')->orderBy('id','desc')->paginate(12);
            }
        }
        $country = CountryModel::where(['status'=>'on','deleted_at'=>null])->whereNotNull('country_name_th')->get();
        $city = CityModel::where(['status'=>'on','deleted_at'=>null])->whereNotNull('city_name_th')->get();
        $data = array(
            'banner' => $banner,
            'row' => $row,
            'recomend' => $recomend ,
            'data_country' => json_encode($country),
            'data_city' => json_encode($city),
        );
        return view('frontend.clients_review',$data);
    }
    public function clients_govern($id)
    {
        $banner = BannerModel::where('id',7)->first();
        if($id){
            $row = CustomerInfoModel::where('tag','like','%"'.$id.'"%')->where('type_id',2)->where('status','on')->orderBy('id','desc')->paginate(6);
            $government = CustomerInfoModel::where('tag','like','%"'.$id.'"%')->where('type_id',2)->where('status','on')->count();
        }else{
            $row = CustomerInfoModel::where('type_id',2)->where('status','on')->orderBy('id','desc')->paginate(6);
            $government = CustomerInfoModel::where('type_id',2)->where('status','on')->count();
        }
       
        $data = array(
            'banner' => $banner,
            'row' => $row,
            'government' => $government ,
        );
        return view('frontend.clients_govern',$data);
    }
    public function news($tyid,$id)
    {
        $banner = BannerModel::where('id',8)->first();
        if($tyid != 0){
            if($id){
                $now = NewModel::where('type_id',$tyid)->where('tag','like','%"'.$id.'"%')->where('status','on')->orderBy('id','desc')->limit(6)->get();
                $new = NewModel::where('type_id',$tyid)->where('tag','like','%"'.$id.'"%')->where('status','on')->orderBy('id','desc')->paginate(12);
            }else{
                $now = NewModel::where('type_id',$tyid)->where('status','on')->orderBy('id','desc')->limit(6)->get();
                $new = NewModel::where('type_id',$tyid)->where('status','on')->orderBy('id','desc')->paginate(12); 
            }
        }else{
            if($id){
                $now = NewModel::where('tag','like','%"'.$id.'"%')->where('status','on')->orderBy('id','desc')->limit(6)->get();
                $new = NewModel::where('tag','like','%"'.$id.'"%')->where('status','on')->orderBy('id','desc')->paginate(12);
            }else{
                $now = NewModel::where('status','on')->orderBy('id','desc')->limit(6)->get();
                $new = NewModel::where('status','on')->orderBy('id','desc')->paginate(12); 
            }
        }
        $now_id = array();
        foreach($now as $n){
            $now_id[] = $n->id;
        }
        if($tyid != 0){
            if($id){
                $recomend = NewModel::whereNotIn('id',$now_id)->where('type_id',$tyid)->where('tag','like','%"'.$id.'"%')->where('status','on')->orderBy('id','desc')->limit(6)->get();
            }else{
                $recomend = NewModel::whereNotIn('id',$now_id)->where('type_id',$tyid)->where('status','on')->orderBy('id','desc')->limit(6)->get();
            }
        }else{
            if($id){
                $recomend = NewModel::whereNotIn('id',$now_id)->where('tag','like','%"'.$id.'"%')->where('status','on')->orderBy('id','desc')->limit(6)->get();
            }else{
                $recomend = NewModel::whereNotIn('id',$now_id)->where('status','on')->orderBy('id','desc')->limit(6)->get();
            }
        }
        
        $travel = TravelModel::where('status','on')->orderBy('id','desc')->limit(3)->get();
        // dd($new);
        $data = array(
            'banner' => $banner,
            'row' => $recomend,
            'now' => $now, 
            'new' => $new,
            'travel' => $travel,
            'id' => $tyid,
        );
        return view('frontend.news',$data);
    }
    public function news_detail($id)
    {
        $new = NewModel::find($id);
        $now = NewModel::where('id','!=',$id)->where('status','on')->orderBy('id','desc')->limit(3)->get();
        $connect = NewModel::where('type_id',$new->type_id)->where('status','on')->where('id','!=',$id)->orderBy('id','desc')->limit(3)->get();
        $type = TypeNewModel::find($new->type_id);
        $tag = TagContentModel::whereIn('id',json_decode($new->tag,true))->get();
        $data = array(
            'row' => $new,
            'now' => $now,
            'connect' => $connect,
            'type' => $type,
            'tag' => $tag,
            'id' => $id,
        );
        return view('frontend.news_detail',$data);
    }
    public function video($id,$cid)
    {
        $banner = BannerModel::where('id',9)->first();
        if($id){
            if($cid){
                $now = VideoModel::where('country_id','like','%"'.$id.'"%')->where('city_id','like','%"'.$cid.'"%')->where('status','on')->orderBy('id','desc')->limit(4)->get();
            }else{
                $now = VideoModel::where('country_id','like','%"'.$id.'"%')->where('status','on')->orderBy('id','desc')->limit(4)->get();
            }
        }else{
            if($cid){
                $now = VideoModel::where('city_id','like','%"'.$cid.'"%')->where('status','on')->orderBy('id','desc')->limit(4)->get();
            }else{
                $now = VideoModel::where('status','on')->orderBy('id','desc')->limit(4)->get();
            }
        }
        $now_id = array();
         foreach($now as $n){
            $now_id[] = $n->id;
        }
        if($id){
            if($cid){
                $row = VideoModel::whereNotIn('id',$now_id)->where('country_id','like','%"'.$id.'"%')->where('city_id','like','%"'.$cid.'"%')->where('status','on')->orderBy('id','desc')->paginate(12);
            }else{
                $row = VideoModel::whereNotIn('id',$now_id)->where('country_id','like','%"'.$id.'"%')->where('status','on')->orderBy('id','desc')->paginate(12);
            }
        }else{
            if($cid){
                $row = VideoModel::whereNotIn('id',$now_id)->where('city_id','like','%"'.$cid.'"%')->where('status','on')->orderBy('id','desc')->paginate(12);
            }else{
                $row = VideoModel::whereNotIn('id',$now_id)->where('status','on')->orderBy('id','desc')->paginate(12);
            }
        }
       
        $country = CountryModel::where(['status'=>'on','deleted_at'=>null])->whereNotNull('country_name_th')->get();
        $city = CityModel::where(['status'=>'on','deleted_at'=>null])->whereNotNull('city_name_th')->get();
        $data = array(
            'banner' => $banner,
            'row' => $row,
            'now' => $now, 
            'data_country' => json_encode($country),
            'data_city' => json_encode($city),
        );
        return view('frontend.video',$data);
    }
    public function search_video(Request $request)
    {
        $banner = BannerModel::where('id',9)->first();
        $now = VideoModel::where('status','on')->orderBy('id','desc')->limit(4)->get();
        $now_id = array();
         foreach($now as $n){
            $now_id[] = $n->id;
        }
        $row = VideoModel::whereNotIn('id',$now_id)->where('status','on')->orderBy('id','desc')->get();
        $data = array(
            'banner' => $banner,
            'row' => $row,
            'now' => $now, 
        );
        return view('frontend.video',$data);
    }
    public function faq()
    {
        $banner = BannerModel::where('id',11)->first();
        $row = QuestionModel::where('status','on')->get();
        $data = array(
            'banner' => $banner,
            'row' => $row,
        );
        return view('frontend.faq',$data);
    }
    public function contact()
    {
        $banner = BannerModel::where('id',12)->first();
        $row = ContactModel::find(1);
        $topic = TopicContactModel::orderBy('id','asc')->get();
        $data = array( 
            'banner' => $banner,
            'row' => $row,
            'topic' => $topic,
        );
        return view('frontend.contact',$data);
    }
    public function promotiontour(Request $request,$id,$tid)
    { 
        $banner = BannerModel::where('id',1)->first();
        //filter ประเทศ
        $data_pro_1  = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereDate('tb_tour_period.start_date','>=',date('Y-m-d'))
        ->where('tb_tour.promotion1','Y')
        // ->whereNotNull('tb_tour.country_id')
        ->where('tb_tour.status','on')
        ->select('*','tb_tour.id as t_id','tb_tour_period.id as pe_id','tb_tour_period.promotion_id as pr_promotion')
        ->where('tb_tour_period.status_display','on')->where('tb_tour_period.deleted_at',null)->orderBy('tb_tour_period.start_date','asc')
        ->get()
        ->groupBy('t_id');
        //dd(11111);
        $data_pro_2  = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereDate('tb_tour_period.start_date','>=',date('Y-m-d'))
        //->where('tb_tour.promotion2','Y')
        // ->whereNotNull('tb_tour.country_id')
        ->where('tb_tour.status','on')
        ->select('*','tb_tour.id as t_id','tb_tour_period.id as pe_id','tb_tour_period.promotion_id as pr_promotion')
        ->where('tb_tour_period.status_display','on')->where('tb_tour_period.deleted_at',null)->orderBy('tb_tour_period.start_date','asc')
        ->get()
        ->groupBy('t_id');

        
        $promotion_id = array();
        $country_all = array();
        $id_tour = array();
        $id_tour_pro = array();
        $check_d1 = false;
        $check_d2 = false;
        $check_d3 = false;
        $check_d4 = false;
        if($data_pro_1){
            foreach($data_pro_1 as $data1){
                foreach($data1 as $pr1){
                    $check_d1 = false;
                    $check_d2 = false;
                    $check_d3 = false;
                    $check_d4 = false;
                    $price1 = $pr1->price1*30/100; 
                    $price2 = $pr1->price2*30/100;
                    $price3 = $pr1->price3*30/100;
                    $price4 = $pr1->price4*30/100;
                    if($pr1->special_price1 > $price1 || $pr1->special_price1 > 0){
                        $check_d1 = true;
                    }
                    if($pr1->special_price2 > $price2 || $pr1->special_price2 > 0){
                        $check_d2 = true;
                    }
                    
                    if($pr1->special_price3 > $price3 || $pr1->special_price3 > 0){
                        $check_d3 = true;
                    } 
                    
                    if($pr1->special_price4 > $price4 || $pr1->special_price4 > 0){
                        $check_d4 = true;
                    }
                    if($check_d1 || $check_d2 || $check_d3 || $check_d4){
                        $country_all = array_merge($country_all,json_decode($pr1->country_id,true));
                        foreach(json_decode($pr1->country_id,true) as $c_id){
                        $id_tour[$c_id][] = $pr1->t_id;
                        $id_tour[$c_id] = array_unique($id_tour[$c_id]);
                        }
                    }
                }
            }
        }
        // dd($id_tour,$pro_1);
        $check_date = false;
        $check_date = false;
        $check_p1 = false;
        $check_p2 = false;
        $check_p3 = false;
        $check_p4 = false;
        if($data_pro_2){
            foreach($data_pro_2 as $data2){
                foreach($data2 as $pr2){
                    $check_date = false;
                    $check_p1 = false;
                    $check_p2 = false;
                    $check_p3 = false;
                    $check_p4 = false;
                    $check_date = false;
                    if($pr2->pro_start_date){
                        $date_now = strtotime(date('Y-m-d'));
                        $date_start = strtotime($pr2->pro_start_date);
                        $date_end = strtotime($pr2->pro_end_date);
                        if($date_start <= $date_now && $date_end >=  $date_now){
                            $check_date = true;
                        }
                        // echo $check_date,'-',$pr2->pe_id;
                        if($check_date){
                            // if($pr2->promotion2 == 'Y'){
                               
                                $promotion_id[$pr2->pr_promotion][] = $pr2->t_id;
                                $promotion_id[$pr2->pr_promotion] = array_unique($promotion_id[$pr2->pr_promotion]);

                                if(isset($id_tour_pro[$pr2->pr_promotion])){
                                    if(!in_array($pr2->t_id,$id_tour_pro[$pr2->pr_promotion])){
                                        $id_tour_pro[$pr2->pr_promotion][] = $pr2->t_id;
                                    }
                                }else{
                                    $id_tour_pro[$pr2->pr_promotion][] = $pr2->t_id;
                                }

                                // if(isset($id_tour_pro[$pr2->pr_promotion])){
                                //     if(!in_array($pr2->t_id,$id_tour_pro[$pr2->pr_promotion])){
                                //         dd($id_tour_pro);
                                //         $id_tour_pro[$pr2->pr_promotion][] = $pr2->t_id;
                                //     }else{
                                //         $id_tour_pro[$pr2->pr_promotion][] = $pr2->t_id;
                                //     }
                                // }
                                $country_all = array_merge($country_all,json_decode($pr2->country_id,true));
                                foreach(json_decode($pr2->country_id,true) as $c_id){
                                    $id_tour[$c_id][] = $pr2->t_id;
                                    $id_tour[$c_id] = array_unique($id_tour[$c_id]);
                                }
                            // }
                           
                        }
                    }else if($pr2->promotion2 == 'Y'){
                        if($pr2->special_price1 > 0){
                            $check_p1 = true;
                        }
                        if($pr2->special_price2 > 0){
                            $check_p2 = true;
                        }
                        
                        if($pr2->special_price3 > 0){
                            $check_p3 = true;
                        } 
                        
                        if($pr2->special_price4 > 0){
                            $check_p4 = true;
                        }
                        if($check_p1 || $check_p2 || $check_p3 || $check_p4){
                            $country_all = array_merge($country_all,json_decode($pr2->country_id,true));
                            foreach(json_decode($pr2->country_id,true) as $c_id){
                                $id_tour[$c_id][] = $pr2->t_id;
                                $id_tour[$c_id] = array_unique($id_tour[$c_id]);
                            }
                        }
                    }
                    
                    
                }
                    
            }
           
        }
        $data_show1 = array();
        $data_show2 = array();
        $pro_country = array();
        if($id){
            $pro_country = $id_tour[$id];
            $data_show1  = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereDate('tb_tour_period.start_date','>=',date('Y-m-d'))
            ->whereIn('tb_tour.id',$id_tour[$id])
            ->where('tb_tour.promotion1','Y')
            ->where('tb_tour.status','on')
            ->select('*','tb_tour.id as t_id','tb_tour_period.id as pe_id','tb_tour_period.promotion_id as pr_promotion')
            ->where('tb_tour_period.status_display','on')->where('tb_tour_period.deleted_at',null)->orderBy('tb_tour_period.start_date','asc')
            ->get()
            ->groupBy('t_id');

            $data_show2  = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereDate('tb_tour_period.start_date','>=',date('Y-m-d'))
            ->whereIn('tb_tour.id',$id_tour[$id])
            ->where('tb_tour.status','on')
            ->select('*','tb_tour.id as t_id','tb_tour_period.id as pe_id','tb_tour_period.promotion_id as pr_promotion')
            ->where('tb_tour_period.status_display','on')->where('tb_tour_period.deleted_at',null)->orderBy('tb_tour_period.start_date','asc')
            ->get()
            ->groupBy('t_id');

        }else{
            $ct = array();
            foreach($id_tour as $tour){
                $ct = array_merge($ct,$tour);
            }
            $pro_country = $ct;
            $data_show1  = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereDate('tb_tour_period.start_date','>=',date('Y-m-d'))
            ->whereIn('tb_tour.id',$ct)
            ->where('tb_tour.promotion1','Y')
            ->where('tb_tour.status','on')
            ->select('*','tb_tour.id as t_id','tb_tour_period.id as pe_id','tb_tour_period.promotion_id as pr_promotion')
            ->where('tb_tour_period.status_display','on')->where('tb_tour_period.deleted_at',null)->orderBy('tb_tour_period.start_date','asc')
            ->get()
            ->groupBy('t_id');

            $data_show2  = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')->whereDate('tb_tour_period.start_date','>=',date('Y-m-d'))
            ->whereIn('tb_tour.id',$ct)
            ->where('tb_tour.status','on')
            ->select('*','tb_tour.id as t_id','tb_tour_period.id as pe_id','tb_tour_period.promotion_id as pr_promotion')
            ->where('tb_tour_period.status_display','on')->where('tb_tour_period.deleted_at',null)->orderBy('tb_tour_period.start_date','asc')
            ->get()
            ->groupBy('t_id');
        }
        $country_all = array_unique($country_all);
        // dd($promotion_id,$id_tour_pro);
        $data = array(
            'banner' => $banner,
            'express' => $data_show1,
            'row' => $data_show2,
            'id' => $id,
            'tid' => $tid,
            'promotion_total' => $promotion_id,
            'pro_country' => $pro_country,
            'country_all' => $country_all,
            'id_tour' => $id_tour,
            'id_tour_pro' => $id_tour_pro,
            'page' => $request->page?$request->page:1,
        );
        return view('frontend.promotiontour',$data);
    }
    public function promotion_filter(Request $request){
        if(isset($request->tour_id[$request->paginate]) && is_array($request->tour_id[$request->paginate])){
            $pro_2  = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')
            ->whereIn('tb_tour.id',$request->tour_id[$request->paginate])
            ->whereDate('tb_tour_period.start_date','>=',date('Y-m-d'))
            ->where('tb_tour.status','on')
            ->select('*','tb_tour.id as t_id','tb_tour_period.id as pe_id','tb_tour_period.promotion_id as pr_promotion')
            ->where('tb_tour_period.status_display','on')->where('tb_tour_period.deleted_at',null)->orderBy('tb_tour_period.start_date','asc')
            ->get()
            ->groupBy('t_id');
            $pre_id = array();
            foreach($pro_2 as $pro){
                foreach($pro as $p){
                    $pre_id[] = $p->pe_id;
                }
            }
            $month =['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
            $tour_id = array_unique($request->tour_id[$request->paginate]);
            $check_data = isset($request->tour_id[$request->paginate+1])?true:false;
            $data = [
                'tour' => TourModel::wherein('id',$tour_id)->get(),
                'period_id' => $pre_id,
                // 'period_data' => TourPeriodModel::whereIn('tour_id',$tour_id)->whereIn('id',$pre_id)->get(),
                'month' => $month,
                'page' => $request->paginate,
                'check_data' => $check_data,
            ];
        return view('frontend.show-more-promo',$data);
        // return response()->json($data);
        // $data = array(
        //     'data' => $pro_2,
        // );
        // return view('frontend.promotion-filter',$data);
        }else {
            return response()->json(false);
            // dd(isset($request->tour_id[$request->paginate]), $request);
        }
    }
    public function showmore_promotion(Request $request){
        $month =['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        if(isset($request->tour_id[$request->paginate]) && is_array($request->tour_id[$request->paginate])){
            $tour_id = array_unique($request->tour_id[$request->paginate]);
            $check_data = isset($request->tour_id[$request->paginate+1])?true:false;
            $pre_id = isset($request->period_id[$request->paginate]) && is_array($request->period_id[$request->paginate]) ? array_unique($request->period_id[$request->paginate]) : [];
            
            $data = [
                'tour' => TourModel::wherein('id',$tour_id)->get(),
                'period_id' => $pre_id,
                // 'period_data' => TourPeriodModel::whereIn('tour_id',$tour_id)->whereIn('id',$pre_id)->get(),
                'month' => $month,
                'page' => $request->paginate,
                'check_data' => $check_data,
            ];
            return view('frontend.show-more-promo',$data);
        } else {
            return response()->json(false);
            // dd(isset($request->tour_id[$request->paginate]), $request);
        }
      return response()->json($request->data);
    }
    public function showmore_promotion_hot(Request $request){
        
        $month =['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        if(isset($request->tour_id[$request->paginate]) && is_array($request->tour_id[$request->paginate])){
            $tour_id = array_unique($request->tour_id[$request->paginate]);
            $check_data = isset($request->tour_id[$request->paginate+1])?true:false;
            $pre_id = isset($request->period_id[$request->paginate]) && is_array($request->period_id[$request->paginate]) ? array_unique($request->period_id[$request->paginate]) : [];
            $data = [
                'tour' => TourModel::wherein('id',$tour_id)->get(),
                'period_id' => $pre_id,
                'month' => $month,
                'page' => $request->paginate,
                'check_data' => $check_data,
            ];
            return view('frontend.show-more-promohot',$data);
        } else {
            // dd(isset($request->tour_id[$request->paginate]), $request);
            return response()->json(false);
        }
      return response()->json($request->data);
    }
    public function package($id)
    {
        // $pack = PackageModel::where('country_id','like','%"'.$id.'"%')->where('status','on')->get();
        $pack = PackageModel::where('status','on')->where('deleted_at',null)->get();
        $arr  = array();
        foreach($pack as $r){
           $arr = array_merge($arr,json_decode($r->country_id,true));
        }
        $arr = array_unique($arr);

        $country = CountryModel::whereIn('id',$arr)->get();

        $banner = BannerModel::where('id',3)->first();
        if($id){
            $row = PackageModel::where('country_id','like','%"'.$id.'"%')->where('status','on')->where('deleted_at',null)->orderBy('id','desc')->paginate(12);
        }else{
            $row = PackageModel::where('status','on')->where('deleted_at',null)->orderBy('id','desc')->paginate(12);
        }
        //dd($row);
        $data = array(
            'row' => $row,
            'banner' => $banner,
            'country' => $country,
            'id' => $id,
        );
        return view('frontend.package',$data);
    }
    public function package_detail($id)
    {
        $pack = PackageModel::where('status','on')->where('deleted_at',null)->get();
        $arr  = array();
        foreach($pack as $r){
           $arr = array_merge($arr,json_decode($r->country_id,true));
        }
        $arr = array_unique($arr);
        $country_all = CountryModel::whereIn('id',$arr)->get();

        $row = PackageModel::find($id);
        $country = CountryModel::whereIn('id',json_decode($row->country_id,true))->get();
        foreach($country as $c){
            $recom = PackageModel::where('id','!=',$id)->where('country_id','like','%"'.$c->id.'"%')->orderBy('id','desc')->limit(4)->get();
        }
        $data = array(
            'row' => $row,
            'recom' => $recom,
            'country' => $country,
            'country_all' => $country_all,
            'id' => $id,
        );
        return view('frontend.package_detail',$data);
    }
    public function organizetour()
    {
        $banner = BannerModel::where('id',4)->first();
        $content = GroupContentModel::find(1);
        $list    = GroupListModel::orderBy('id','asc')->get();
        $row     = GroupModel::where('status','on')->orderBy('id','desc')->get();
        $data = array(
            'banner' => $banner,
            'content' => $content,
            'list' => $list,
            'row' => $row,

        );
        return view('frontend.organizetour',$data);
    }
    public function wishlist()
    {
        $data = array(
        );
        return view('frontend.wishlist',$data);
    }
    public function tour_summary($id)
    {
        $data = array(
        );
        return view('frontend.tour_summary',$data);
    }
   
    public function forgotpassword()
    {
        // dd('ok');
        return view('frontend.pages.forgot-password');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function get_district($id)
    {
        $district = DB::table('tb_amphurs')
            ->where('province_id', $id)
            ->select('*')
            ->get();
        $data = array(
            'district'     => $district,
        );
        echo json_encode($data);
    }

    public function get_sub_district($id)
    {
        $sub_district = DB::table('tb_districts')
            ->where('amphure_id', $id)
            ->select('*')
            ->get();
        $data = array(
            'sub_district'     => $sub_district,
        );
        echo json_encode($data);
    }

    public function get_zip_code($id)
    {
        $zip_code = DB::table('tb_districts')
            ->where('id', $id)
            ->select('zip_code')
            ->first();
        $data = array(
            'zip_code'     => $zip_code,
        );
        echo json_encode($data);
    }

    public function search_zip_code($id)
    {
        $sub_district = DB::table('tb_districts')
            ->where('zip_code', $id)
            ->select('*')
            ->get();
        $get_dis = DB::table('tb_districts')
            ->where('zip_code', $id)
            ->select('amphure_id')
            ->first();
        if ($get_dis !== null) {
            $district = DB::table('tb_amphurs')
                ->where('id', $get_dis->amphure_id)
                ->select('*')
                ->first();
            if ($district !== null) {
                $province = DB::table('tb_provinces')
                    ->where('id', $district->province_id)
                    ->select('*')
                    ->first();
            } else {
                $province = '';
            }
        } else {
            $district = '';
            $province = '';
        }
       
        $data = array(
            'sub_district'      => $sub_district,
            'district'          => $district,
            'province'          => $province,
        );
        echo json_encode($data);
    }

    public function search_capacity($id)
    {
        $capacity = DB::table('tb_product_prices as pro_pri')
            ->leftjoin('tb_product_capacities as cap', 'pro_pri.pro_pri_capacity', 'cap.id')
            ->where('pro_pri.pro_pri_product_id', $id)
            ->where('cap.pro_cap_status', '1')
            ->select('cap.*', 'pro_pri.id as pro_pri_id')
            ->get();
        $data = array(
            'capacity'  =>$capacity,
        );
        echo json_encode($data);

    }

    public function search_color($id)
    {
        $color = DB::table('tb_product_price_colors as pri_col')
            ->leftjoin('tb_product_colors as color', 'pri_col.color_id', 'color.id')
            ->where('pri_col.product_price_id', $id)
            ->select('color.*', 'pri_col.id as pro_pri_col_id')
            ->get();
        $ins = DB::table('tb_product_prices')
            ->where('id', $id)
            ->select('pro_pri_installment')
            ->first();
        $data = array(
            'color' =>$color,
            'ins' =>$ins,
        );
        echo json_encode($data);
        
    }
    public function weekend()
    {
        $dat = CalendarModel::whereDate('end_date','>=',date('Y-m-d'))
        ->where(['status'=>'on','deleted_at'=>null])
        ->orderby('start_date')
        ->get();
        $banner = BannerModel::where('id',2)->first();
        $data = array(
            'data' => $dat,
            'banner' => $banner,
        );
        return view('frontend.weekend',$data);
    }
    public function weekend_landing(Request $request,$id)
    {
        // Platform check 
        $isWin = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "windows")):0; 
                $isMac = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "macintosh"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "macintosh")):0; 
                $isAndroid = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android")):0; 
                $isIPhone = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "iphone")):0; 
                $isIPad = is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad"))?is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "ipad")):0; 
        $calendar = CalendarModel::find($id);
        $data = array(
            'data' => $calendar,
            'calen_id' => $id,
            'isWin' => $isWin,
            'isMac' => $isMac,
            'isAndroid' => $isAndroid,
            'isIPhone' => $isIPhone,
            'isIPad' => $isIPad,
        );
        return view('frontend.weekend_landing',$data);
    }
    public function filter_data(Request $request){
        $dat = CalendarModel::find($request->id);
        $orderby_data = '';
        $calen_start = strtotime($dat->start_date);
        $calen_end = strtotime($dat->end_date);
        $calendar = ceil(($calen_end - $calen_start)/86400);
        $arrayDate = array();
        $arrayDate[] = date('Y-m-d',$calen_start); 
        for($x = 1; $x <= $calendar; $x++){
            $arrayDate[] = date('Y-m-d',($calen_start+(86400*$x)));
        }   
        $pe_data = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')
        ->whereIn('tb_tour_period.start_date',$arrayDate);
        // ->whereDate('tb_tour_period.start_date','>=',$dat->start_date)
        // ->whereDate('tb_tour_period.end_date','<=',$dat->end_date);
        if($request->slug != 0){
            $pe_data = $pe_data->where('tb_tour.country_id','like','%"'.$request->slug.'"%');
        }
        if($request->data){
            if(isset($request->data['day'])){
                $pe_data = $pe_data->whereIn('tb_tour_period.day',$request->data['day']);
                // dd($request->data);
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
        $pe_data = $pe_data->where('tb_tour.status','on')
            ->where('tb_tour_period.status_display','on')
            ->orderby('tb_tour_period.start_date','asc')
            ->orderby('tb_tour.rating','desc')
            ->select('tb_tour_period.*')
            ->get()
            ->groupBy('tour_id');
        // dd($pe_data);
        foreach($pe_data as $k => $pe){
            $period[$k]['period']  = $pe;
            $period[$k]['recomand'] = TourPeriodModel::where('tour_id',$k)
            ->where('status_display','on')->where('deleted_at',null)
            ->orderby('start_date','asc')
            ->limit(2)->get()->groupBy('group_date');
            $period[$k]['soldout'] = TourPeriodModel::where('tour_id',$k)
            ->whereDate('start_date','>=',$dat->start_date)
            ->whereDate('end_date','<=',$dat->end_date)
            ->where('status_period',3)->where('status_display','on')
            ->where('deleted_at',null)
            ->orderby('start_date','asc')
            ->get()->groupBy('group_date');
                $tour = TourModel::find($k);
                $period[$k]['tour'] = $tour;
                $period[$k]['country_id'] = json_decode($tour->country_id,true);
                $period[$k]['city_id'] = json_decode($tour->city_id,true);
        }
        // dd($period);
        $filter = array();
        foreach($period as $i => $per){
            if(isset($request->data['country'])){
                if(count(array_intersect($request->data['country'],$per['country_id'])) != count($request->data['country'])){
                    unset($period[$i]);
                }
                // if(!count(array_intersect($request->data['country'],$per['country_id']))){
                //     unset($period[$i]);
                // }
                
            }
            if(isset($request->data['city'])){
                if(count(array_intersect($request->data['city'],$per['city_id'])) != count($request->data['city'])){
                    unset($period[$i]);
                }
            }
           if(isset($period[$i])){
            // dd($per['tour']);
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
                    if(isset($filter['airline'][$per['tour']->airline_id])){
                        if(!in_array($per['tour']->id,$filter['airline'][$per['tour']->airline_id])){
                            $filter['airline'][$per['tour']->airline_id][] = $per['tour']->id;
                        }
                    }else{
                        $filter['airline'][$per['tour']->airline_id][] = $per['tour']->id;
                    }
                }
               
                //ดาว
                // if($per['tour']->rating){
                    $filter['rating'][$per['tour']->rating][] = $per['tour']->rating ;
                    // $filter['rating'] = array_unique($filter['rating']);
                    // krsort($filter['rating']);
                    // array_values($filter['rating'][$per['tour']->rating]);
                // }
           }
        }

        // dd($filter);
        $num_price = 0;
        if(isset($filter['price'])){
            foreach($filter['price'] as $p){
                $num_price =  $num_price + count($p);
            }
        }
      
        $row = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')
        ->whereDate('tb_tour_period.start_date','>=',$dat->start_date)
        ->whereDate('tb_tour_period.end_date','<=',$dat->end_date)
        ->where('tb_tour.status','on')
        ->where('tb_tour_period.status_display','on')
        ->orderby('tb_tour_period.start_date','asc')
        ->select('tb_tour_period.*')
        ->get()
        ->groupBy('tour_id');

        $count_pe = count($period);
        $data = array(
            'data' => $dat,
            'period' => $period,
            'calen_id' => $request->id,
            'slug' => $request,
            'row' => $row,
            'filter' => $filter,
            'airline_data' => TravelTypeModel::/* where('status','on')-> */where('deleted_at',null)->get(),
            'num_price' => $num_price,
            'tour_list' => $this->search_weekend($dat,$period),
            'tour_grid' => $this->search_filter_grid($dat,$period),
            'count_pe' => $count_pe,
            'orderby_data' => $orderby_data,
        );
        return response()->json($data);
       
    }
    public function search_weekend($request,$period){
        
       try {
       
        $calen_start = strtotime($request->start_date);
        $calen_end = strtotime($request->end_date);
        $calendar = ceil(($calen_end - $calen_start)/86400);
        $calen_data = array();
        $calen_data[] = date('Y-m-d',$calen_start); 
        for($x = 1; $x < $calendar; $x++){
            $calen_data[] = date('Y-m-d',($calen_start+(86400*$x)));
        }
        $calen_data[] = date('Y-m-d',$calen_end);
        $data = "";
        $month = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        $allSoldOut = array();
        $checkSold = false;
        foreach($period as $pre){
            $numday = 0;
            foreach ($pre['period'] as $p){ 
                $numday = $p->day;
                $checkSold = false;
                if($p->count == 0 && $p->status_period == 3){
                    $allSoldOut[$p->tour_id][] = $p->id;
                }  
                if(isset($allSoldOut[$pre['tour']->id])){
                    if(count($pre['period']) == count($allSoldOut[$pre['tour']->id])){
                        $checkSold = true;
                    } 
                }
            
            }
            $country = CountryModel::whereIn('id',json_decode($pre['tour']->country_id,true))->get();
            $province = ProvinceModel::whereIn('id',json_decode($pre['tour']->province_id,true))->get();
            $airline =  TravelTypeModel::find($pre['tour']->airline_id);
            $type =  TourTypeModel::find(@$pre['tour']->type_id);
            if($pre['tour']){
            $data .= "<div class='boxwhiteshd'><div class='toursmainshowGroup  hoverstyle'><div class='row'>";
            $data .= "<div class='col-lg-12 col-xl-3 pe-0'><div class='covertourimg'><figure>";
            $data .= "<a href='".url('tour/'.$pre['tour']->slug)."'><img src='".asset(@$pre['tour']->image)."' alt=''></a>";
            $data .= "</figure><div class='d-block d-sm-block d-md-block d-lg-none d-xl-none'>";
            $data .= "<a href='javascript:void(0);' class='tagicbest'  name='type_data' onclick='OrderByType(".@$pre['tour']->type_id.")'><img src='".asset(@$type->image)."'class='img-fluid' alt=''></a></div>";
            if($pre['tour']->special_price > 0){
                $data .= "<div class='saleonpicbox'><span> ลดราคาพิเศษ</span> <br>".number_format($pre['tour']->special_price,0)." บาท</div>";
            }
            if($checkSold){
                $data .= "<div class='soldfilter'><div class='soldop'><span class='bigSold'>SOLD OUT </span> <br><span class='textsold'> ว้า! หมดแล้ว คุณตัดสินใจช้าไป</span> <br>";
                $data .= "<a href='".url('tour/'.$pre['tour']->slug)."' target='_blank' class='btn btn-second mt-3'><i class='fi fi-rr-search'></i> หาโปรแกรมทัวร์ใกล้เคียง</a></div></div>";
            }
            $data .= "<div class='priceonpic'>";
            if($pre['tour']->special_price > 0){
                $price = $pre['tour']->price - $pre['tour']->special_price; 
                $data .= "<span class='originalprice'>ปกติ ".number_format($pre['tour']->price,0)." </span><br>";
                $data .= "เริ่ม<span class='saleprice'>".number_format(@$price,0)." บาท</span>";
            }else{
                $data .= "<span class='saleprice'>".number_format($pre['tour']->price,0)." บาท</span>";
            }                         
            $data .= "</div><div class='addwishlist'>";
            $data .= "<a href='javascript:void(0);' class='wishlist' data-tour-id='".$pre['tour']->id."'><i class='bi bi-heart-fill' id='likeButton' onclick='likedTour(".@$pre['tour']->id.")'></i></a>";
            $data .= "</div></div></div>";
            $data .= "<div class='col-lg-12 col-xl-9'><div class='codeandhotel Cropscroll mt-1'>";
            $data .= "<li class='bgwhite'>"; 
            if($country){
                $data .= "<a href='".url('oversea/'.@$country[0]->slug)."'><i class='fi fi-rr-marker' style='color:#f15a22;'></i>";
                foreach ($country as $coun){
                    $data .= $coun->country_name_th?$coun->country_name_th:$coun->country_name_en ;
                }  
            }else{
                $data .= "<a href='".url('inthai/'.@$province[0]->slug)."'><i class='fi fi-rr-marker' style='color:#f15a22;'></i>";
                foreach ($province as $prov){
                    $data .= $prov->name_th?$prov->name_th:$prov->name_en ;
                }  
            }
            $data .= "</a></li>";
            $data .= "<li>รหัสทัวร์ : <span class='bluetext'>";
            if(@$pre['tour']->code1_check){
                $data .= @$pre['tour']->code1;
            }else{
                $data .= @$pre['tour']->code;
            } 
            $data .= "</span> </li>";
            $data .= "<li class='rating'>โรงแรม: <a href='".url('tour/'.$pre['tour']->slug)."'>";
            if($pre['tour']->rating > 0){
                $data .= "<a href='javascript:void(0);' onclick='Check_filter(".$pre['tour']->rating.",\"rating\")'>";
                for($i=1; $i <= $pre['tour']->rating; $i++){
                    $data .= "<i class='bi bi-star-fill'></i>";
                }
                $data .= "</a>";
            }else{
                $data .= "<a href='javascript:void(0);' onclick='Check_filter(0,\"rating\")'>";
            }     
            $data .= "</a></li><li>สายการบิน: <a href='javascript:void(0);' onclick='Check_filter(".@$airline->id.",\"airline\")'><img src='".asset(@$airline->image)."' alt=''></a></li>";
            $data .= "<li><div class='d-none d-sm-none d-md-none d-lg-block d-xl-block'><a href='javascript:void(0);' class='tagicbest'  name='type_data' onclick='OrderByType(".@$pre['tour']->type_id.")'><img src='".asset(@$type->image)."' class='img-fluid' alt=''></a></div></li>";
            $data .= "<li class='bgor'>ระยะเวลา <a href='javascript:void(0);' onclick='Check_filter($numday,\"day\")'>".$pre['tour']->num_day."</a></li></div>";
            $data .= "<div class='nameTop'><h3> <a href='".url('tour/'.$pre['tour']->slug)."'>".$pre['tour']->name."</a></h3></div>";
            $data .= "<div class='pricegroup text-end'>";
            if($pre['tour']->special_price > 0){
                $price = $pre['tour']->price - $pre['tour']->special_price; 
                $data .= "<span class='originalprice'>ปกติ ".number_format($pre['tour']->price,0)." </span><br>เริ่ม<span class='saleprice'> ".number_format(@$price,0)." บาท</span>";
            }else{
                $data .= "เริ่ม<span class='saleprice'> ".number_format($pre['tour']->price,0)." บาท</span>";
            }
            $data .=  "</div>";
            if($pre['tour']->description){
                $data .= "<div class='highlighttag'> <span><i class='fi fi-rr-tags'></i> </span> ".@$pre['tour']->description."</div>";
            }
            $count_hilight = 0;
            if($pre['tour']->travel){  $count_hilight++; }
            if($pre['tour']->shop){  $count_hilight++; }
            if($pre['tour']->eat){  $count_hilight++; }
            if($pre['tour']->special){  $count_hilight++;}
            if($pre['tour']->stay){  $count_hilight++;}
            if($count_hilight > 0){
                $data .= "<div class='hilight mt-2'><div class='readMore'><div class='readMoreWrapper'><div class='readMoreText2'>";
                if($pre['tour']->travel){
                    $data .= "<li><div class='iconle'><span><i class='bi bi-camera-fill'></i></span></div><div class='topiccenter'><b>เที่ยว</b></div><div class='details'> ".@$pre['tour']->travel."</div></li>";
                }
                if($pre['tour']->shop){
                    $data .= "<li><div class='iconle'><span><i class='bi bi-bag-fill'></i></span></div><div class='topiccenter'><b>ช้อป </b></div><div class='details'> ".@$pre['tour']->shop."</div></li>";
                }
                if($pre['tour']->eat){
                    $data .= "<li><div class='iconle'><span><svg xmlns='http://www.w3.org/2000/svg' width='22' height='22' fill='currentColor' class='bi bi-cup-hot-fill'viewBox='0 0 16 16'>";
                    $data .= "<path fill-rule='evenodd' d='M.5 6a.5.5 0 0 0-.488.608l1.652 7.434A2.5 2.5 0 0 0 4.104 16h5.792a2.5 2.5 0 0 0 2.44-1.958l.131-.59a3 3 0 0 0 1.3-5.854l.221-.99A.5.5 0 0 0 13.5 6H.5ZM13 12.5a2.01 2.01 0 0 1-.316-.025l.867-3.898A2.001 2.001 0 0 1 13 12.5Z' />";
                    $data .= "<path d='m4.4.8-.003.004-.014.019a4.167 4.167 0 0 0-.204.31 2.327 2.327 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.31 3.31 0 0 1-.202.388 5.444 5.444 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 3.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 3.6 2.8l-.01-.012a5.099 5.099 0 0 1-.37-.543A1.53 1.53 0 0 1 3 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a5.446 5.446 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 4.4.8Zm3 0-.003.004-.014.019a4.167 4.167 0 0 0-.204.31 2.327 2.327 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.31 3.31 0 0 1-.202.388 5.444 5.444 0 0 1-.253.382l-.018.025-.005.008-.002.002A.5.5 0 0 1 6.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 6.6 2.8l-.01-.012a5.099 5.099 0 0 1-.37-.543A1.53 1.53 0 0 1 6 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a5.446 5.446 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 7.4.8Zm3 0-.003.004-.014.019a4.077 4.077 0 0 0-.204.31 2.337 2.337 0 0 0-.141.267c-.026.06-.034.092-.037.103v.004a.593.593 0 0 0 .091.248c.075.133.178.272.308.445l.01.012c.118.158.26.347.37.543.112.2.22.455.22.745 0 .188-.065.368-.119.494a3.198 3.198 0 0 1-.202.388 5.385 5.385 0 0 1-.252.382l-.019.025-.005.008-.002.002A.5.5 0 0 1 9.6 4.2l.003-.004.014-.019a4.149 4.149 0 0 0 .204-.31 2.06 2.06 0 0 0 .141-.267c.026-.06.034-.092.037-.103a.593.593 0 0 0-.09-.252A4.334 4.334 0 0 0 9.6 2.8l-.01-.012a5.099 5.099 0 0 1-.37-.543A1.53 1.53 0 0 1 9 1.5c0-.188.065-.368.119-.494.059-.138.134-.274.202-.388a5.446 5.446 0 0 1 .253-.382l.025-.035A.5.5 0 0 1 10.4.8Z' /></svg></span> </div>";
                    $data .= "<div class='topiccenter'><b>กิน </b></div><div class='details'>".@$pre['tour']->eat."</div> </li>";
                }
                if($pre['tour']->special){
                    $data .= "<li><div class='iconle'><span><i class='bi bi-bookmark-heart-fill'></i></span></div><div class='topiccenter'><b>พิเศษ </b></div><div class='details'>".@$pre['tour']->special."</div></li>";
                }
                if($pre['tour']->stay){
                    $data .= "<li><div class='iconle'><span><svg xmlns='http://www.w3.org/2000/svg' width='22' height='22'fill='currentColor' class='bi bi-buildings-fill' viewBox='0 0 16 16'>";
                    $data .= "<path d='M15 .5a.5.5 0 0 0-.724-.447l-8 4A.5.5 0 0 0 6 4.5v3.14L.342 9.526A.5.5 0 0 0 0 10v5.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V14h1v1.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5V.5ZM2 11h1v1H2v-1Zm2 0h1v1H4v-1Zm-1 2v1H2v-1h1Zm1 0h1v1H4v-1Zm9-10v1h-1V3h1ZM8 5h1v1H8V5Zm1 2v1H8V7h1ZM8 9h1v1H8V9Zm2 0h1v1h-1V9Zm-1 2v1H8v-1h1Zm1 0h1v1h-1v-1Zm3-2v1h-1V9h1Zm-1 2h1v1h-1v-1Zm-2-4h1v1h-1V7Zm3 0v1h-1V7h1Zm-2-2v1h-1V5h1Zm1 0h1v1h-1V5Z' /></svg></span> </div>";
                    $data .= "<div class='topiccenter'><b>พัก </b></div><div class='details'>".@$pre['tour']->stay."</div></li>";
                }
                $data .= "</div><div class='readMoreGradient'></div></div><a class='readMoreBtn2'></a><span class='readLessBtnText' style='display: none;'>Read Less</span>";
                $data .= "<span class='readMoreBtnText' style='display: none;'>Read More </span></div></div>";
            }
            $data .=  "</div></div>";
            if(!$checkSold){
                $sold_tour = array();
                $data .= "<div class='periodtime'><div class='readMore'><div class='readMoreWrapper'><div class='readMoreText'><div class='listperiod_moredetails'>";
                $data .= "<div class='tagmonth'><span class='month'>".$month[date('n',strtotime($pre['period'][0]->start_date))]."</span></div><div class='splgroup'>";
                if(!in_array($pre['tour']->id,$allSoldOut)){    
                        foreach ($pre['period'] as $p){ 
                            if($p->count == 0 && $p->status_period == 3){
                                $sold_tour[] = $p->id;
                            }  
                        
                            $start = strtotime($p->start_date);
                                ${'holliday'.$p->id} = 0;
                                while ($start <= strtotime($p->end_date)) {
                                    if(isset($calen_data) && in_array(date('Y-m-d',$start),$calen_data) || date('N',$start) >= 6){
                                        if($p->count <= 10){
                                        }else{
                                            ${'holliday'.$p->id}++;
                                        }
                                    }
                                $start = $start + 86400;
                            }
                            $data .= "<li>"; 
                            $data .= "<a ";
                            if(${'holliday'.$p->id} > 0){
                                $data .= "data-tooltip='".${'holliday'.$p->id}." วัน'";
                            }
                            $data .= "id='staydate".$p->id."' class='staydate'>";
                            $start = strtotime($p->start_date); 
                                $chk_price = true;
                                while ($start <= strtotime($p->end_date)) {
                                    if(isset($calen_data) && in_array(date('Y-m-d',$start),$calen_data) || date('N',$start) >= 6){
                                        $chk_price = false;
                                        if($p->count <= 10){
                                            $data .= "<span class='fulltext'>*</span>";
                                            break;
                                        }else{
                                            $data .= "<span class='staydate'>-</span>";
                                        }
                                    }
                                    $start = $start + 86400;
                                }
                                if($chk_price){
                                    if($p->special_price1 && $p->special_price1 > 0){
                                        $price = $p->price1 - $p->special_price1;
                                    }else{
                                        $price = $p->price1;
                                    }
                                $data .= "<span class='saleperiod'>".number_format($price,0)."฿ </span>";
                                }
                            $data .= "</a><br>";
                            $data .= date('d',strtotime($p->start_date))." - ".date('d',strtotime($p->end_date));
                            $data .= "</li>";
                            }
                        }
                        $data .= "</div><hr></div></div><div class='readMoreGradient'></div></div>";
                        if(count($pre['period']) > 30){
                            $data .= "<a class='readMoreBtn'></a>";
                            $data .= "<span class='readLessBtnText' style='display: none;'>Read Less</span><span class='readMoreBtnText' style='display: none;'>Read More</span>";
                        }
                        $data .= "</div></div><div class='remainsFull'><li>* ใกล้เต็ม</li><li><span class='noshowpad'>-</span><span class='showpad'>-</span> จำนวนวันหยุด</li></div>";
                        $data .= "<div class='row'><div class='col-md-9'>";
                        $data .= "<div class='fullperiod'>";
                        if(count($sold_tour)){
                            $data .= "<h6>พีเรียดที่เต็มแล้ว (".count($sold_tour).")</h6>";
                            foreach ($pre['period'] as $sold){
                                if($sold->count == 0 && $sold->status_period == 3){
                                    $data .= "<span class='monthsold'>".$month[date('n',strtotime($pre['period'][0]->start_date))]."</span>";
                                    $data .= "<li>".date('d',strtotime($sold->start_date))." - ".date('d',strtotime($sold->end_date))."</li>";
                                }
                            }
                        }          
                        $data .=  "</div>";
                        $data .= "</div><div class='col-md-3 text-md-end'><a href='".url('tour/'.$pre['tour']->slug)."' class='btn-main-og  morebtnog'>รายละเอียด</a>";
                        $data .= "</div></div>";
                }
            }
            $data .= "<br></div></div>";
        }
        
        return $data ;

       } catch (\Throwable $th) {
            // dd($th);
       }  
    }
    public function search_filter_grid($request,$period)
    {
        try {
            $data = "";
            foreach($period as  $pre){
                $numday = 0;
                foreach ($pre['period'] as $p){ 
                    $numday = $p->day;
                }
                $type = TourTypeModel::find(@$pre['tour']->type_id);
                $airline = TravelTypeModel::find(@$pre['tour']->airline_id);
                @$province = ProvinceModel::whereIn('id',json_decode($pre['tour']->province_id,true))->first();
                @$country_search = CountryModel::whereIn('id',json_decode($pre['tour']->country_id,true))->first();
                // dd($country_search,$pre['tour']->country_id);
                $data .= "<tr><td><div class='row'><div class='col-5 col-lg-4'>";
                $data .= "<a href='".url('tour/'.$pre['tour']->slug)."' target='_blank'><img src='".asset(@$pre['tour']->image)."' class='img-fluid' alt=''></a>";
                $data .= "</div><div class='col-7 col-lg-8 titlenametab'>";
                $data .= "<h3><a href='".url('tour/'.$pre['tour']->slug)."' target='_blank'>".$pre['tour']->name."</a></h3>";
                $data .= "</div></div></td>";
                if($country_search){
                    $data .=    "<td><a href='".url('oversea/'.@$country_search->slug)."'>".$country_search->country_name_th."</a> </td>";
                }else{
                    $data .=    "<td><a href='".url('inthai/'.@$province->slug)."'>".$province->name_th."</a> </td>";
                }
                $data .=    "<td><a href='javascript:void(0);' onclick='Check_filter($numday,\"day\")'>".$pre['tour']->num_day."</a> </td>";
                $data .=    "<td><a href='javascript:void(0);' onclick='Check_filter(".@$airline->id.",\"airline\")'><img src='".asset(@$airline->image)."' alt=''></a> </td>";
                $data .=    "<td>";
                if($pre['tour']->special_price > 0){
                    $price = $pre['tour']->price - $pre['tour']->special_price; 
                    $data .=  "เริ่ม ".number_format(@$price,0) ." บาท";
                }else{
                    $data .=  "เริ่ม ".number_format($pre['tour']->price,0)." บาท";
                } 
                $data .=    "</td>";
                $data .=    "<td><div class='rating'>";
                if($pre['tour']->rating > 0){
                    $data .= "<a href='javascript:void(0);' onclick='Check_filter(".$pre['tour']->rating.",\"rating\")'>";
                    for($i=1; $i <= @$pre['tour']->rating; $i++){
                        $data .=  "<i class='bi bi-star-fill'></i>";
                    }
                }else{
                    $data .= "<a href='javascript:void(0);' onclick='Check_filter(0,\"rating\")'>";
                }
                $data .=    "</div></td>";
                $data .=    "<td><a href='javascript:void(0);' class='tagicbest'  name='type_data' onclick='OrderByType(".@$pre['tour']->type_id.")'><img src='".asset(@$type->image)."' class='img-fluid' alt=''></a></td>";
                $data .=    "<td><a href='".url('tour/'.$pre['tour']->slug)."' target='_blank' class='link'><i class='bi bi-chevron-right'></i></a></td></tr>";
            }
            return $data ;

        } catch (\Throwable $th) {
            // dd($th);
        }
        
    }
    public function search_airline(Request $request){
        
        $air = TravelTypeModel::whereIn('id',json_decode($request->id,true));
        if($request->text != null){
            $air = $air->where('travel_name','like','%'.$request->text.'%');
        }
       
        $air = $air->orderBy('id','desc')->get();
        $data = '';
        $num = json_decode($request->num,true);
        foreach( $air as $a){
            $data .=  "<li><label class='check-container'>";
            if($a->image){
                $data .=  "<img src='".asset($a->image)."' alt=''> ";
            }   
            $data .=  $a->travel_name;
            $data .=  "<input type='radio' name='airline' id='airline$a->id' onclick='UncheckdAirline($a->id)' value='$a->id'>";
            $data .=  "<span class='checkmark'></span> <div class='count'>";
            $data .= "(".$num[$a->id].")";
            $data .=  "</div></label></li>";
          
        }

        return $data;
        // dd($data);
    }
    public function search_price(Request $request){
        // $data = TourModel::get();
        // foreach($data as $d){
        //     if($d->price){
        //         if($d->price <= 10000 ){
        //             $d->price_group = 1;
        //         }else if($d->price  > 10000 && $d->price <= 20000  ){
        //             $d->price_group = 2;
        //         }else if($d->price  > 20000 && $d->price <= 30000  ){
        //             $d->price_group = 3;
        //         }
        //         else if($d->price  > 30000 && $d->price <= 50000  ){
        //             $d->price_group = 4;
        //         }
        //         else if($d->price  > 50000 && $d->price <= 80000  ){
        //             $d->price_group = 5;
        //         }else if($d->price  > 80000  ){
        //             $d->price_group = 6;
        //         }
        //     }else{
        //         $d->price_group = 0;
        //     }
        //     $d->save();
        // }
        $dat = CalendarModel::find($request->calen_id);
        $period = array();
        $filter = array();
        $pe_data = TourPeriodModel::leftjoin('tb_tour','tb_tour_period.tour_id','=','tb_tour.id')
            ->whereDate('tb_tour_period.start_date','>=',$dat->start_date)
            ->whereDate('tb_tour_period.end_date','<=',$dat->end_date);
        if($request->price){
            $pe_data = $pe_data->whereIn('tb_tour.price_group',$request->price);
        }
        if($request->slug != 0){
            $pe_data = $pe_data->where('tb_tour.country_id','like','%"'.$request->slug.'"%');
        }
        $pe_data = $pe_data->where('tb_tour.status','on')
            ->where('tb_tour_period.status_display','on')
            ->orderby('tb_tour_period.start_date','asc')
            ->select('tb_tour_period.*')
            ->get()
            ->groupBy('tour_id');
        foreach($pe_data as $k => $pe){
            $period[$k]['period']  = $pe;
            if(count($period[$k]['period'])){ 
                $tour = TourModel::find($k);
                $period[$k]['tour'] = $tour;
                //จำนวนวัน
                foreach($pe as $p){
                    if($p->day){
                        if(isset($filter[$p->day])){
                            if(!in_array($tour->id,$filter[$p->day])){
                                $filter[$p->day][] = $tour->id;
                            }
                        }else{
                            $filter[$p->day][] = $tour->id;
                        }
                    }
                }
            }else{
                unset($period[$k]);
            }
        }
        $text = '';
        foreach ($filter as $day => $num){
            $text .= "<li><label class='check-container'> $day วัน";
            $text .= "<input type='checkbox' name='day' id='day$day' onclick='UncheckdDay($day)' value='$day'>";
            $text .= "<span class='checkmark'></span><div class='count'>(".count($num).")</div></label></li>";
        }
        return $text;
    }
}
