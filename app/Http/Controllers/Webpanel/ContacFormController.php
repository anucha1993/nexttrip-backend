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

use App\Models\Backend\ContactFormModel;
use App\Models\Backend\TopicContactModel;

class ContacFormController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'contact-form';
    protected $folder = 'contact-form';
    protected $menu_id = '19';
    protected $name_page = "แบบฟอร์มติดต่อสอบถาม";

    public function auth_menu()
    {
        return view("$this->prefix.alert.alert",[
            'url'=> "webpanel",
            'title' => "เกิดข้อผิดพลาด",
            'text' => "คุณไม่ได้รับอนุญาตให้ใช้เมนูนี้! ",
            'icon' => 'error'
        ]); 
    }
    public function index(Request $request)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->read  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "แบบฟอร์มติดต่อสอบถาม", "last" => 0],
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
        $sTable = ContactFormModel::whereNull('deleted_at')
        ->orderby('id', 'desc')
        ->when($like, function ($query) use ($like) {
            $query->where(function ($query) use ($like) {
                if (@$like['name'] != "") {
                    $query->where('name', 'like', '%' . $like['name'] . '%')
                    ->orwhere('surname', 'like', '%' . $like['name'] . '%')
                    ->orwhere('company', 'like', '%' . $like['name'] . '%');
                }
            });
        })
        ->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                $data = date('d/m/Y H:i', strtotime('+543 Years', strtotime($row->created_at)));
                return $data;
            })
            ->addColumn('action', function ($row) {
                $data = "";
                $menu_control = Helper::menu_active($this->menu_id);
                if($menu_control->edit == "on")
                {
                    $data.= " <a href='$this->segment/$this->folder/view/$row->id' class='mr-3 mb-2 btn btn-sm btn-info' title='Edit'><i class='fa fa-eye w-4 h-4 mr-1'></i> ดูเพิ่มเติม </a>  ";  
                }
                if($menu_control->delete == "on")
                {
                    $data.= " <a href='javascript:' class='mr-3 mb-2 btn btn-sm btn-danger' onclick='deleteItem($row->id)' title='Delete'><i class='far fa-trash-alt w-4 h-4 mr-1'></i> ลบ </a>";  
                }
                return $data;
            })
            ->rawColumns(['created_at', 'action'])
            ->make(true);
    }
    public function edit(Request $request, $id)
    {
        $menu_control = Helper::menu_active($this->menu_id);
        if($menu_control){ if($menu_control->edit  == "off") { return $this->auth_menu(); } } else { return $this->auth_menu();}
        $data = ContactFormModel::find($id);
        $navs = [
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "แบบฟอร์มติดต่อสอบถาม", "last" => 0],
            '2' => ['url' => "$this->segment/$this->folder/view/$id", 'name' => "ติดต่อจากคุณ $data->name $data->surname ", "last" => 1],
        ];
        $topic = TopicContactModel::where('id',$data->topic_id)->first();
        return view("$this->prefix.pages.$this->folder.view", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'row' => $data,
            'navs' => $navs,
            'id' => $id,
            'topic' => $topic,
        ]);
    }
    public function destroy(Request $request,$id)
    {
        $datas = ContactFormModel::find($id);
        if (@$datas) {
                $datas->deleted_at = date('Y-m-d H:i:s'); // soft delete
                if($datas->save()){
                    return response()->json(true);
                } else {
                    return response()->json(false);
                }
            
        } else {
            return response()->json(false);
        }
    }
}
