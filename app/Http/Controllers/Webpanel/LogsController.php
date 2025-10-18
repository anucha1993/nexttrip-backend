<?php

namespace App\Http\Controllers\Webpanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use App\Models\Backend\LogsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class LogsController extends Controller
{
    public static function send_line($message, $token)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "message=$message");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $headers = array("Content-type: application/x-www-form-urlencoded", "Authorization: Bearer $token",);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }


    public static function save_logbackend($type_log, $error_log, $error_line, $error_url)
    {
        $data = new LogsModel;
        $data->type_log = $type_log;
        $data->error_log = $error_log;
        $data->error_line = $error_line;
        $data->error_url = $error_url;
        $data->login_id = Auth::Guard()->id()?Auth::Guard()->id():null;
        $data->created = date('Y-m-d H:i:s');
        $data->updated = date('Y-m-d H:i:s');
        if ($data->save()) {
            $message = "Nexttrip \n เกิดข้อผิดพลาดทางด้านโปรแกรม (Code:$data->id) \n Log ที่ได้รับแจ้ง : $error_log \n Website Link : $error_url \n บรรทัดที่มีปัญหา : $error_line";
            // $token = 'mwCBPQWfrpbhC102IvVuAB83pUYE5uQMnpGUbfg40Z7';
            $userId = 'U32c1e0873873f459f224ade28807b2ef';
            // LogsController::send_line($message, $token);
            LogsController::sendLineMessage($message, $userId);
            return $data->id;
        }
    }

    public static function sendLineMessage($message, $userId)
    {
        $channelToken = 'X5pYOqmLrZR0QStQ70fqK04BSdW+Ia6t4U5UH4Wo7VNHpsurdig56JyQcWyb0qJZRbsf+uherG9Tt1UKEafyk7zEKe8klvbU5lgxqqUBHTXRO1vhk5ZvgSesksSuy1mI1UMC/3KCCk41psNxb8jb6AdB04t89/1O/w1cDnyilFU=';

        Http::withHeaders([
            'Authorization' => "Bearer {$channelToken}",
            'Content-Type' => 'application/json',
        ])->post('https://api.line.me/v2/bot/message/push', [
            'to' => $userId,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message
                ]
            ]
        ]);
    }
}
