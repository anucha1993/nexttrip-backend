<?php

namespace App\Http\Controllers\Webpanel;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Functions\MenuControl;
use App\Http\Controllers\Functions\FunctionControl;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Webpanel\LogsController;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\DB;

use App\Models\Backend\ProvinceModel;
use App\Models\Backend\AmupurModel;

class AmupurController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'setting-address/amupur';
    protected $folder = 'setting-address/amupur';
    protected $menu_id = '1';
    protected $name_page = "Amupurs";

    public function items($parameters)
    {
        $search = Arr::get($parameters, 'search');
        $sort = Arr::get($parameters, 'sort', 'asc');
        $paginate = Arr::get($parameters, 'total', config('project.limit_rows'));
        $query = new AmupurModel;
        if ($search) {
            $query = $query->where('name', 'LIKE', '%' . trim($search) . '%');
        }
        $query = $query->where('deleted_at',null);
        $query = $query->orderBy('id', $sort);
        $results = $query->paginate($paginate);
        return $results;
    }

    public function index(Request $request)
    {
        $items = $this->items($request->all());
        $items->pages = new \stdClass();
        $items->pages->start = ($items->perPage() * $items->currentPage()) - $items->perPage();

        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/$this->folder", 'name' => "District", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.index", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'items' => $items,
        ]);
    }

    public function create_modal(Request $request)
    {
        $code = AmupurModel::select('code')->orderby('id','desc')->first();
        $code = (int)$code->code + 1;
         return view("$this->prefix.pages.$this->folder.modal_create", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'code' => $code,
            'provinces' => ProvinceModel::get(),
        ]);
    }

    public function edit_modal(Request $request)
    {
         return view("$this->prefix.pages.$this->folder.modal_edit", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'item' => AmupurModel::find($request->id),
            'provinces' => ProvinceModel::get(),
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
        try {
            $check_code = "";
            $check = AmupurModel::get();
            DB::beginTransaction();
            if ($id == null) {
                $data = new AmupurModel();
                $data->created_at = date('Y-m-d H:i:s');
                $data->updated_at = date('Y-m-d H:i:s');
                if($check->where('code',$request->code)->first()){
                    $check_code = 'NOT NULL';
                }
            } else {
                $data = AmupurModel::find($id);
                $data->updated_at = date('Y-m-d H:i:s');
                if($check->where('code',$request->code)->where('id','!=',$id)->first()){
                    $check_code = 'NOT NULL';
                }
            }
            if($check_code != 'NOT NULL'){
                $data->code = $request->code;
                $data->name_th = $request->name_th;
                $data->name_en = $request->name_en;
                $data->province_code = $request->province_code;
                if ($data->save()) 
                {
                    DB::commit();
                    $arr = [
                        'status' => '200',
                        'result' => 'success',
                        'message' => 'ดำเนินการสำเร็จ'
                    ];
                } else {
                    DB::rollback();
                    $arr = [
                        'status' => '500',
                        'result' => 'error',
                        'message' => 'เกิดข้อผิดพลาด'
                    ];
                }
            }else{
                DB::rollback();
                $arr = [
                    'status' => '500',
                    'result' => 'error',
                    'message' => 'เกิดข้อผิดพลาด มีข้อมูล Code นี้อยู่แล้ว'
                ];
            }
            echo json_encode($arr);
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

    public function status($id = null)
    {
        $data = AmupurModel::find($id);
        $data->status = ($data->status == 'off') ? 'on' : 'off';
        if ($data->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function destroy(Request $request, $id)
    {
        $data = AmupurModel::find($id);
        if (@$data) {
            $data->deleted_at = date('Y-m-d H:i:s');
            $data->save();
            $query = true;
            // $query = AmupurModel::destroy($data->id);
        }
        if (@$query) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
}
