<?php

namespace App\Http\Controllers\Webpanel;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;
use App\Models\Backend\User;

class HomeController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'home';
    protected $folder = 'home';

    public function index(Request $request)
    {
        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "ใบPO", "last" => 0],
        ];
        return view("$this->prefix.pages.$this->folder.index", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'navs' => $navs,
        ]);
    }

    public function add()
    {
        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "เพิ่มข้อมูลใบPO", "last" => 0],
        ];
        return view("$this->prefix.pages.$this->folder.add", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'navs' => $navs,
        ]);
    }


    public static function uploadimage_text(Request $request)
    {
        if ($request->hasFile('upload')) {
            $originName = $request->file('upload')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;

            $request->file('upload')->move(public_path('uploads/texteditor/'), $fileName);

            $CKEditorFuncNum = $request->input('CKEditorFuncNum');
            $url = asset('uploads/texteditor/' . $fileName);
            $msg = "อัพโหลดรูปภาพสำเร็จ";
            $response = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";

            @header('Content-type: text/html; charset=utf-8');
            echo $response;
        }
    }

    function store_image(Request $request)
    {
        if ($request->hasFile('upload')) {
            $extension = $request->file('upload')->getClientOriginalExtension();
            $filenametostore = time() . (rand(10, 100)) .  '.' . $extension;
            $request->file('upload')->move(public_path('upload/texteditor/image'), $filenametostore);
            echo json_encode([
                '500' => asset('upload/texteditor/image/' . $filenametostore)
            ]);
        }
    }
}
