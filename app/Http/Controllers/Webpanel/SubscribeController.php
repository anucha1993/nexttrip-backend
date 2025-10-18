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
// use Maatwebsite\Excel\Excel;
// use App\Exports\SubscribeExport;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

use App\Models\Backend\SubscribeModel;
use App\Models\Backend\ContactModel;
use App\Models\Backend\MessageModel;
use App\Models\Backend\SubscribeMessageModel;

class SubscribeController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'subscribe';
    protected $folder = 'subscribe';
    protected $menu_id = '21';
    protected $name_page = "Subscribe";

    public function auth_menu()
    {
        return view("$this->prefix.alert.alert",[
            'url'=> "webpanel",
            'title' => "เกิดข้อผิดพลาด",
            'text' => "คุณไม่ได้รับอนุญาตให้ใช้เมนูนี้! ",
            'icon' => 'error'
        ]); 
    }

    // public function __construct(Excel $excel)
    // {
    //     $this->excel = $excel;
    // }
    // public function export_excel (Request $request)
    // {
    //     $data = SubscribeModel::orderby('id','desc')->get();
    //     return $this->excel->download(new SubscribeExport($data), "Subscribe-Report-".date('ymdHis').".xlsx");
    // }
    // public function export_csv (Request $request)
    // {
    //     $data = SubscribeModel::orderby('id','desc')->get();
    //     return $this->excel->download(new SubscribeExport($data), "Subscribe-Report-".date('ymdHis').".csv");
    // }
    public function export_excel($sTable,$type)
    {    
    if($type == 'excel'){
        $pathread = "public/excel/template/subscribe_report.xlsx";  // Server Location    
        $pathwriter = "public/excel/export/subscribe_report"."_".strtotime("now").".xlsx";
    }else if($type == 'csv'){
        $pathread = "public/excel/template/subscribe_report.csv";  // Server Location    
        $pathwriter = "public/excel/export/subscribe_report"."_".strtotime("now").".csv";
    }
     
     
     $reader = \PhpOffice\PhpSpreadsheet\IOFactory::load($pathread);   
     $sheet  = $reader->getActiveSheet();
    //  dd($sheet);
     if($sRow = $sTable){
        // dd($sTable);
      $iRow = 4;
      foreach($sRow AS $iNum=>$r){
       $iRow++;
       $sheet->setCellValue('A'.$iRow, ($iNum+1));
       $sheet->setCellValue('B'.$iRow, (is_null($r->email)) ? "-" : $r->email);
       $sheet->setCellValue('C'.$iRow, (is_null(($r->created_at))) ? "-" : date('d/m/Y  H:i:s',strtotime($r->created_at)));
      }
     }
     
     // $iRow++;
     // $sheet->removeRow($iRow,1990-$iRow);
     $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($reader);
     
     $writer->save($pathwriter);
     return response(['status' => 'success', 'redirect' => $pathwriter ]); 
    }

    public function items($parameters)
    {
        $search = Arr::get($parameters, 'search');
        $sort = Arr::get($parameters, 'sort', 'desc');
        $paginate = Arr::get($parameters, 'total', 15);
        $query = new SubscribeModel;
        if ($search) {
            $query = $query->where('title', 'LIKE', '%' . trim($search) . '%');
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
        return view("$this->prefix.pages.$this->folder.data.index", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'items' => $items,
            'menu_control' => $menu_control,
        ]);
    }

    public function datatable(Request $request){
			
        $like = $request->Like;

        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}

        $sTable = SubscribeModel::orderby('id','desc')
            ->when($like, function ($query) use ($like) {
                if (@$like['search_email'] != "") {
                    $query->where('email', 'like', '%' . $like['search_email'] . '%');
                }
            });
        
        if(request('excel')){
            return $this->export_excel($sTable->get(),'excel');
        }
        if(request('csv')){
            return $this->export_excel($sTable->get(),'csv');
        }
        $sQuery = DataTables::of($sTable);
        return $sQuery
        ->addIndexColumn()
        ->addColumn('created_at', function ($row) {
            $data = date('d/m/Y', strtotime('+543 Years', strtotime($row->created_at)));
            return $data;
        })
        ->addColumn('action', function ($row) {
            $data = "";
            $menu_control = Helper::menu_active($this->menu_id);
            if($menu_control->delete == "on")
            {
                $data.= " <a href='javascript:' class='mr-3 mb-2 btn btn-sm btn-danger' onclick='deleteItem($row->id)' title='Delete'><i class='far fa-trash-alt w-4 h-4 mr-1'></i> ลบ </a>";  
            }
            return $data;
        })
        ->rawColumns(['created_at','action'])
        ->make(true);
    }

    public function destroy(Request $request)
    {
        $query = SubscribeModel::destroy($request->id);
        if ($query) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function mail(Request $request){

        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}

        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => $this->name_page, "last" => 1],
        ];

        return view("$this->prefix.pages.$this->folder.email.index", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
        ]);
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
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent;padding-top:1rem; padding-bottom:.5rem;"><b>ชื่อเรื่อง : </b> <span style="margin-left:15px;">'.$data['subject'].'</span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent; padding-top:.5rem; padding-bottom:.5rem;"><b>รายละเอียด </b> <span style="margin-left:15px;"></span></td>
                                        </tr>
                                        <tr style="font-size:13px;">
                                            <td style="font-family: Sarabun, sans-serif;border: 1px solid transparent; padding-top:.5rem; padding-bottom:.5rem;">'.$data['message'].'</td>
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
    //ฟังก์ชั่นส่งเมลเดิม
    // public function sendmail(Request $request)
    // {
    //     $addresses = SubscribeModel::get();

    //     if($request->type == 1){
            
    //         $data['subject'] = $request->subject;
    //         $data['message'] = $request->message;
    
    //         $contact = ContactModel::find(1);
    
    //         $mail = new PHPMailer(true);
    //         try {
    //             //Server settings
    //             $mail->CharSet = 'UTF-8';
    //             $mail->isSMTP();
    //             $mail->SMTPAuth   = true;
    //             $mail->SMTPOptions = array(
    //                 'ssl' => array(
    //                     'verify_peer' => false,
    //                     'verify_peer_name' => false,
    //                     'allow_self_signed' => true
    //                 )
    //             );
    
    //             //Recipients
    //             $mail->setFrom('noreply@liw.orangeworkshop.info', 'แจ้งข้อมูลข่าวสารเกี่ยวกับเว็บไซต์');
    //             // $mail->addAddress('mutita@ots.co.th');
    //             // $mail->addAddress('liwza12@gmail.com');
    //             foreach ($addresses as $address) {
    //                 $mail->addAddress($address->email);
    //             }
    
    //             // Content
    //             $mail->isHTML(true);
    //             $mail->Subject = 'แจ้งข้อมูลข่าวสารเกี่ยวกับเว็บไซต์';
    //             $mail->Body    = '';
    //             $mail->Body .= $this->contact_sendmailv_html_header();
    //             $mail->Body .= $this->contact_sendmailv_html_center($data);
    //             $mail->Body .= $this->contact_sendmailv_html_footer($contact);
    
    //             if($mail->send()){
    //                 return view("$this->prefix.alert.alert", [
    //                     'url' => url("$this->segment/$this->folder/email"),
    //                     'title' => "สำเร็จ",
    //                     'text' => "ระบบได้ทำการส่งอีเมลเรียบร้อยแล้ว",
    //                     'icon' => 'success'
    //                 ]);
    //                 echo 'Message has been sent';
    
    //             }else{
    //                 return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder/email")]);
    //             }
    
    //         } catch (Exception $e) {
    //             echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    //         }
    //     }else if($request->type == 2){
           
    //         try {
    //             $data = new MessageModel();
    //             $data->subject = $request->subject;
    //             $data->message = $request->message;
    //             $data->read_status = 'N';
    //             if($data->save()){
    //                 DB::commit();
    //                 return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder/email")]);
    //             }
    //         } catch (\Exception $e) {
    //             DB::rollback();
    //             $error_log = $e->getMessage();
    //             $error_line = $e->getLine();
    //             $type_log = 'backend';
    //             $error_url = url()->current();
    //             $log_id = LogsController::save_logbackend($type_log, $error_log, $error_line, $error_url);
    
    //             return view("$this->prefix.alert.alert", [
    //                 'url' => $error_url,
    //                 'title' => "เกิดข้อผิดพลาดทางโปรแกรม",
    //                 'text' => "กรุณาแจ้งรหัส Code : $log_id ให้ทางผู้พัฒนาโปรแกรม ",
    //                 'icon' => 'error'
    //             ]);
    //         }
    //     }
    // }
    public function sendmail(Request $request)
    {
        if($request->type == 1){
            
            $data['subject'] = $request->subject;
            $data['message'] = $request->message;
            $data['message'] = preg_replace_callback('/<img\s+[^>]*src="([^"]*)"/i', function ($matches) {
                $relativePath = $matches[1];
                $absolutePath = str_replace('../../public/moxieupload/', rtrim(url('/moxieupload/'), '/') . '/', $relativePath);
                return str_replace($relativePath, $absolutePath, $matches[0]);
            }, $data['message']);
           
            $sub_message = new SubscribeMessageModel;
            $sub_message->sub_id = '[]';
            $sub_message->type = 'subscribe';
            $sub_message->subject = $request->subject;
            $sub_message->message = $data['message'];
            $sub_message->save();
            if($sub_message->save()){
                return view("$this->prefix.alert.alert", [
                    'url' => url("$this->segment/$this->folder/email"),
                    'title' => "สำเร็จ",
                    'text' => "ระบบได้ทำการส่งอีเมลเรียบร้อยแล้ว",
                    'icon' => 'success'
                ]);
            }else{
                return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder/email")]);
            } 
            //dd($sub_message);
        }else if($request->type == 2){
           
            try {
                $data = new MessageModel();
                $data->subject = $request->subject;
                $data->message = $request->message;
                $data->read_status = 'N';
                if($data->save()){
                    DB::commit();
                    return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder/email")]);
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
    }
    public function check_send (){  
        $data = SubscribeMessageModel::where('status','N')->get();
        foreach ($data as $value) {
           $id =  json_decode($value->sub_id,true);
           $mail = SubscribeModel::whereNotIn('id',$id)->where('email', 'LIKE', '%@%')->limit(2)->get();
           $email = array();
           $sub_id = array();
           foreach ($mail as  $value2) {
                $email[] = $value2->email;
                $sub_id[] = $value2->id;
            }
            $result = $this->confirm_send_mail($email,$value);
            if($result){
                $id = array_merge($sub_id,$id);
                $value->sub_id = json_encode($id);
                if(count($id) >= SubscribeModel::where('email', 'LIKE', '%@%')->count()){
                    $value->status = 'Y';
                }
                $value->save();
            }
        }
    }
    public function confirm_send_mail ($email,$content){ 
            
            $contact = ContactModel::find(1);
            try {
                foreach ($email as $address) {
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
                    $mail->setFrom('noreply@nexttripholiday.com', 'แจ้งข้อมูลข่าวสารเกี่ยวกับเว็บไซต์');
                    $mail->addAddress($address);
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'แจ้งข้อมูลข่าวสารเกี่ยวกับเว็บไซต์';
                    $mail->Body    = '';
                    $mail->Body .= $this->contact_sendmailv_html_header();
                    $mail->Body .= $this->contact_sendmailv_html_center($content);
                    $mail->Body .= $this->contact_sendmailv_html_footer($contact);
        
                    if($mail->send()){
                        return true;
                    }else{
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                        return false;
                    }
                }
            } catch (Exception $e) {
                // $error_log = $e->getMessage();
                // $error_line = $e->getLine();
                // $type_log = 'backend';
                // $error_url = url()->current();
                // $log_id = LogsController::save_logbackend($type_log, $error_log, $error_line, $error_url);
                return false;
            }
    }
}
