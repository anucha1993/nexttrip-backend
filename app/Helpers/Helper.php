<?php

namespace App\helpers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Models\Backend\TranslateModel;

use App\Models\Backend\MemberModel;
use App\Models\Backend\ProvinceModel;
use App\Models\Backend\AmupurModel;
use App\Models\Backend\TambonModel;

class Helper 
{
    protected $prefix = 'back-end';
    //==== Menu Active ====
    public static function auth_menu()
    {
        return view("back-end.alert.alert",[
            'url'=> "webpanel",
            'title' => "เกิดข้อผิดพลาด",
            'text' => "คุณไม่ได้รับสิทธิ์ในการใช้เมนูนี้ ! ",
            'icon' => 'error'
        ]); 
    }
    
    public static function menu_active($menu_id)
    {
        $member_id = Auth::guard('')->id();
        $member = \App\Models\Backend\User::find($member_id);
        $role = \App\Models\Backend\RoleModel::find($member->role);
        $list_role = \App\Models\Backend\Role_listModel::where(['role_id'=>$role->id, 'menu_id'=>$menu_id])->first();
        return $list_role;
    }

    public static function getRandomID($size, $table, $column_name)
    {
        $check_status = 0;
        while ($check_status == 0) 
        {
            $random_id = Helper::randomCode($size);

            $data = DB::table($table)->where("$column_name","$random_id")->get();
            if($data->count() == 0)
            {
                $check_status = 1;
            }
        }
        return $random_id;
    }

    public static function randomCode($length)
    {
        $possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghigklmnopqrstuvwxyz"; //ตัวอักษรที่ต้องการสุ่ม
        $str = "";
        while (strlen($str) < $length) {
            $str .= substr($possible, (rand() % strlen($possible)), 1);
        }
        return $str;
    }

    public static function translate($id)
    {
        $lang = Session('lang');
        $data = TranslateModel::select("tb_translate.*", "name_$lang as name")->find($id);
        return $data->name;
    }

    public static function convertThaiDate($date, $type = 'date')
    {
        $thai_months = [
            1 => 'ม.ค.',
            2 => 'ก.พ.',
            3 => 'มี.ค.',
            4 => 'เม.ย.',
            5 => 'พ.ค.',
            6 => 'มิ.ย.',
            7 => 'ก.ค.',
            8 => 'ส.ค.',
            9 => 'ก.ย.',
            10 => 'ต.ค.',
            11 => 'พ.ย.',
            12 => 'ธ.ค.',
        ];
        $date = Carbon::parse($date);
        $month = $thai_months[$date->month];
        $year = $date->year + 543;

        if ($type == 'datetime') {
            return $date->format("j $month $year H:i:s");
        }

        return $date->format("j $month $year");
    }

    public static function DayMonthYearthai($strDate)
	{
		$strYear = date("Y",strtotime($strDate))+543;
		$strMonth= date("n",strtotime($strDate));
		$strDay= date("j",strtotime($strDate));
		$strHour= date("H",strtotime($strDate));
		$strMinute= date("i",strtotime($strDate));
		$strSeconds= date("s",strtotime($strDate));
		$strMonthCut = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฏาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
		$strMonthThai=$strMonthCut[$strMonth];
		return "$strDay $strMonthThai $strYear";
	}

    public static function Daythai($strDate)
	{
        $carbonDate = Carbon::createFromFormat('Y-m-d', $strDate)->locale('th');
        $day = $carbonDate->isoFormat('dddd');
		return "$day";
	}

    public static function Monththai($strDate)
	{
        $carbonDate = Carbon::createFromFormat('Y-m-d', $strDate)->locale('th');
        $month = $carbonDate->isoFormat('MMMM');
		return "$month";
	}

    public static function Yearthai($strDate)
	{
        $strYear = date("Y",strtotime($strDate))+543;
		return "$strYear";
	}

    /**
     * Detect country from tour name keywords
     * Returns array of country IDs (as strings for JSON encoding)
     * 
     * @param string $tourName
     * @return array
     */
    public static function detectCountryFromName($tourName)
    {
        if (empty($tourName)) {
            return [];
        }

        $tourNameUpper = strtoupper($tourName);
        $countryIds = [];

        // Japan keywords
        if (preg_match('/(TOKYO|OSAKA|KYOTO|FUKUOKA|HOKKAIDO|NAGOYA|HIROSHIMA|OKINAWA|FUJI|YOKOHAMA|NIKKO|KAWAGOE|NARA|KOBE|HAKONE|YUFUIN|FUJINOMIYA|IBARAKI|โตเกียว|โอซาก้า|เกียวโต|ฟูกูโอกะ|ฮอกไกโด|นาโงย่า|ฮิโรชิม่า|โอกินาว่า|ฟูจิ|นาร่า|ฮาโกเน่|ยูฟุอิน)/iu', $tourName)) {
            $countryIds[] = '109'; // Japan
        }
        
        // China keywords
        if (preg_match('/(เซี่ยงไฮ้|เซียงไฮ้|ปักกิ่ง|เซี่ยงฮาย|SHANGHAI|BEIJING|GUANGZHOU|SHENZHEN|CHENGDU|XIAN|HANGZHOU|LIJIANG|GUILIN|ZHANGJIAJIE|SUZHOU|WUXI|จางเจียเจี้ย|ฉางซา|หางโจว|ซูโจว|อู๋ซี|ภูเขาหิมะมังกรหยก|แชงกรีล่า|ลี่เจียง|ฟูหรงเจิ้น|เทียนเหมินซาน|ต้าหลี่)/iu', $tourName)) {
            $countryIds[] = '45'; // China
        }
        
        // Vietnam keywords
        if (preg_match('/(ดานัง|DANANG|ฮานอย|HANOI|ฮาลอง|HALONG|ฮอยอัน|HOIAN|HOI AN|ซาปา|SAPA|ดาลัด|DALAT|นาตราง|NHA TRANG|ฟานเทียต|ฟานซิปัน|เว้|ฮิว|เมืองไฮฟอง|บานาฮิลล์)/iu', $tourName)) {
            $countryIds[] = '240'; // Vietnam
        }
        
        // Hong Kong keywords
        if (preg_match('/(ฮ่องกง|HONGKONG|HONG KONG|HK|รีพลัสเบย์|REPULSE BAY)/iu', $tourName)) {
            $countryIds[] = '98'; // Hong Kong
        }
        
        // UK keywords
        if (preg_match('/(LONDON|ENGLAND|UK|EDINBURGH|STONEHENGE|BATHS|BATH|MANCHESTER|LIVERPOOL|OXFORD|CAMBRIDGE|แมนเชสเตอร์|ลิเวอร์พูล)/iu', $tourName)) {
            $countryIds[] = '232'; // United Kingdom
        }
        
        // France keywords
        if (preg_match('/(PARIS|FRANCE|NICE|LYON|MARSEILLE|ปารีส|แห่งฝรั่งเศส)/iu', $tourName)) {
            $countryIds[] = '75'; // France
        }
        
        // Switzerland keywords
        if (preg_match('/(SWITZERLAND|ZURICH|GENEVA|LUCERNE|INTERLAKEN|ZERMATT|GLACIER|ซูริค|เจนีวา|ลูเซิร์น|อินเทอร์ลาเกน|เซอร์แมท)/iu', $tourName)) {
            $countryIds[] = '214'; // Switzerland
        }
        
        // USA keywords
        if (preg_match('/(USA|AMERICA|NEW YORK|LOS ANGELES|SAN FRANCISCO|LAS VEGAS|CALIFORNIA|FLORIDA|WASHINGTON|CHICAGO|BOSTON|ลอสแองเจลิส|ซานฟรานซิสโก|ลาสเวกัส|อเมริกา)/iu', $tourName)) {
            $countryIds[] = '233'; // United States
        }
        
        // Singapore keywords
        if (preg_match('/(SINGAPORE|สิงคโปร์)/iu', $tourName)) {
            $countryIds[] = '199'; // Singapore
        }
        
        // Thailand keywords
        if (preg_match('/(เชียงใหม่|CHIANG MAI|ภูเก็ต|PHUKET|กระบี่|KRABI|พัทยา|PATTAYA|เกาะสมุย|SAMUI|หัวหิน|HUA HIN)/iu', $tourName)) {
            $countryIds[] = '219'; // Thailand
        }
        
        // South Korea keywords
        if (preg_match('/(SEOUL|BUSAN|KOREA|JEJU|โซล|ปูซาน|เกาหลี|เชจู)/iu', $tourName)) {
            $countryIds[] = '116'; // South Korea
        }
        
        // Taiwan keywords
        if (preg_match('/(TAIPEI|TAIWAN|TAICHUNG|KAOHSIUNG|ไทเป|ไต้หวัน)/iu', $tourName)) {
            $countryIds[] = '216'; // Taiwan
        }
        
        // Malaysia keywords
        if (preg_match('/(MALAYSIA|KUALA LUMPUR|PENANG|LANGKAWI|มาเลเซีย|กัวลาลัมเปอร์|ปีนัง)/iu', $tourName)) {
            $countryIds[] = '132'; // Malaysia
        }
        
        // Indonesia keywords
        if (preg_match('/(BALI|JAKARTA|INDONESIA|YOGYAKARTA|LOMBOK|บาหลี|จาการ์ตา)/iu', $tourName)) {
            $countryIds[] = '102'; // Indonesia (check this ID)
        }

        // Remove duplicates and return only first country (most relevant)
        $uniqueIds = array_unique($countryIds);
        return array_slice($uniqueIds, 0, 1); // Take only first matched country
    }


    //=====================
}