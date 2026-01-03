<?php

namespace App\Http\Controllers\Frontend;

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
use Illuminate\Support\Arr;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

use App\Models\Backend\ContactFormModel;
use App\Models\Backend\ContactModel;

class ContacFormController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';

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
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>ชื่อผู้ติดต่อ : </b> <span style="margin-left:15px;">'.$data['name'].' '.$data['surname'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>บริษัท : </b> <span style="margin-left:15px;">'.$data['company'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>อีเมล : </b> <span style="margin-left:15px;">'.$data['mail'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>เบอร์โทรศัพท์ : </b> <span style="margin-left:15px;">'.$data['phone'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent; padding-top:.5rem; padding-bottom:.5rem;"><b>รายละเอียด : </b> <span style="margin-left:15px;">'.$data['detail'].'</span></td>
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

    public function sendmail(Request $request)
    {
        $mail = new PHPMailer(true);
        try {
            $recaptcha = $request['g-recaptcha-response'];
            $url = 'https://www.google.com/recaptcha/api/siteverify?secret=6LdQYyIqAAAAAGFTw3OBhEZwsete72cClVP705o_&response=' . $recaptcha . '&remoteip=' . $_SERVER['REMOTE_ADDR'];
            // $url = 'https://www.google.com/recaptcha/api/siteverify?secret=6Le6CQopAAAAAM5FUeFatNKC7Rqc5ziE1FbTuJiY&response=' . $recaptcha . '&remoteip=' . $_SERVER['REMOTE_ADDR'];
            $reponse = json_decode(file_get_contents($url), true);
            
            if ($reponse['success'] == true && $reponse['score']>=0.5) {
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
                
                $row = new ContactFormModel();
                $row->name     = $request->name;
                $row->surname     = $request->surname;
                $row->company  = $request->company;
                $row->phone  = $request->phone;
                $row->mail    = $request->mail;
                $row->topic_id    = $request->topic_id;
                $row->detail     = strip_tags($request->detail);
                $row->created_at = date('Y-m-d H:i:s');
                $row->updated_at = date('Y-m-d H:i:s');
                $row->save();

                $contact = ContactModel::find(1);
                $data['name'] = $request->name;
                $data['surname'] = $request->surname;
                $data['phone'] = $request->phone;
                $data['company'] = $request->company;
                $data['mail'] = $request->mail;
                $data['detail'] = strip_tags($request->detail);

                //Recipients
                $mail->setFrom('noreply@nexttripholiday.com', 'ติดต่อจากเว็บไซต์ Nexttripholiday');
                $mail->addAddress('nexttripholiday@gmail.com');
            
                $mail->isHTML(true);
                $mail->Subject = 'แจ้งข้อมูลติดต่อสอบถาม';
                $mail->Body    = '';
                $mail->Body .= $this->contact_sendmailv_html_header();
                $mail->Body .= $this->contact_sendmailv_html_center($data);
                $mail->Body .= $this->contact_sendmailv_html_footer($contact);
                // dd($request);
                if($mail->send()){
                    $mes = 'ระบบได้ทำการส่งอีเมลเรียบร้อยแล้ว';
                    $yourURL= url('contact');
                    echo ("<script>alert('$mes'); location.href='$yourURL'; </script>");
                }else{
                    $mes = 'เกิดข้อผิดพลาด กรุณาส่งใหม่อีกครั้ง';
                    $yourURL= url('contact');
                    echo ("<script>alert('$mes'); location.href='$yourURL'; </script>");
                }
            }else{
                //dd(2);
                return back();
            }

        } catch (\Exception $e) {
            //dd($e->getMessage());
            $mes = 'เกิดข้อผิดพลาด'.$mail->ErrorInfo;
            $yourURL= url('admin/contact');
            echo ("<script>alert('$mes'); location.href='$yourURL'; </script>");
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
