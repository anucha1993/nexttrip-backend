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

use App\Models\Backend\MenuModel;
use App\Models\Backend\Role_listModel;


class MenuController extends Controller
{
    protected $prefix = 'backend';
    protected $segment = 'webpanel';
    protected $controller = 'menu';
    protected $folder = 'menu';
    protected $name_page = "รายการเมนู";

    public function datatable(Request $request)
    {
        $like = [
            'search_choice' => $request->search_choice,
            'search_type' => $request->search_type,
            'search_keyword' => $request->search_keyword,
        ];
        $sTable = MenuModel::where('position', 'main')->orderby('sort', 'asc')
            ->when($like, function ($query) use ($like) {
                if (@$like['search_choice'] != "") 
                {
                    if(@$like['search_type'] == "like")
                    {
                        $query->where(@$like['search_choice'], 'like', '%' . @$like['search_keyword'] . '%');
                    }
                    else
                    {
                        $query->where(@$like['search_choice'], @$like['search_type'], @$like['search_keyword']);
                    }
                }
            })
            ->get();
        return Datatables::of($sTable)
            ->addIndexColumn()
            ->addColumn('change_sort', function ($row) {
                $sorts = MenuModel::orderby('sort')->where('position','main')->get();

                $html = "";
                $html.='<select id="sort_'.$row->id.'" name="sort_'.$row->id.'" class="form-select w100" onchange="changesort('.$row->id.')">';
                foreach($sorts as $s)
                {
                    $select = '';
                    if($s->sort == $row->sort){ $select = 'selected'; }
                    $html.='<option value="'.$s->sort.'" '.$select.'>'.$s->sort.'</option>';
                }
                $html.='</select>';
    
                $data = $html;
                return $data;
            })
            ->editColumn('action_name', function ($row) {
                $secondary = \App\Models\Backend\MenuModel::where('_id', $row->id)->get();
                $data = "<span>$row->name</span>";
                if (count($secondary) > 0) {
                    $data .= "
                    <button type='button' data-tw-toggle='modal' data-tw-target='#show_modal' class='ml-3 btn btn-primary' onclick='show_submenu($row->id);' type='button'><i class='far fa-list-alt'></i></button>";
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
            
            ->editColumn('created', function ($row) {
                $data = date('d/m/Y', strtotime('+543 Years',strtotime($row->created)));
                return $data;
            })
            ->editColumn('action', function ($row) {
                return " <a href='$this->segment/$this->folder/edit/$row->id' class='mr-3 btn btn-sm btn-info' title='Edit'><i class='fa fa-edit w-4 h-4 mr-1'></i> Edit </a>                                                 
            <a href='javascript:' class='btn btn-sm btn-danger' onclick='deleteItem($row->id)' title='Delete'><i class='far fa-trash-alt w-4 h-4 mr-1'></i> Delete</a>
            ";
            })
            ->rawColumns(['action_name','status', 'change_sort', 'created', 'action'])
            ->make(true);
    }

    public function index(Request $request)
    {
        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/menu", 'name' => "Menu", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.index", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'rows' => MenuModel::where(['position' => 'main'])->orderby('sort', 'asc')->get(),
        ]);
    }

    public function add(Request $request)
    {
        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/menu", 'name' => "Menu", "last" => 0],
            '2' => ['url' => "$this->segment/menu/add", 'name' => "Menu add", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.add", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'main' => MenuModel::where('position', '=', 'main')->get(),
        ]);
    }

    public function edit(Request $request, $id)
    {
        $data = MenuModel::find($id);
        $navs = [
            '0' => ['url' => "$this->segment", 'name' => "Dashboard", "last" => 0],
            '1' => ['url' => "$this->segment/menu", 'name' => "Menu", "last" => 0],
            '2' => ['url' => "$this->segment/menu/edit/$id", 'name' => "$data->name", "last" => 1],
        ];
        return view("$this->prefix.pages.$this->folder.edit", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'navs' => $navs,
            'row' => $data,
            'main' => MenuModel::where('position', '=', 'main')->get(),
        ]);
    }

    public function status($id = null)
    {
        $data = MenuModel::find($id);
        $data->status = ($data->status == 'off') ? 'on' : 'off';
        if ($data->save()) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function showsubmenu(Request $request)
    {
        $data = MenuModel::find($request->id);
        $rows = MenuModel::where(['_id'=>$data->id, 'position'=>'secondary'])->orderby('sort','asc')->get();
        return view("$this->prefix.pages.$this->folder.show_submenu", [
            'prefix' => $this->prefix,
            'folder' => $this->folder,
            'segment' => $this->segment,
            'name_page' => $this->name_page,
            'data' => $data,
            'rows' => $rows,
        ]);
    }

    public function destroy(Request $request)
    {
        $datas = MenuModel::find(explode(',', $request->id));
        if (@$datas) {
            foreach ($datas as $data) {
                MenuModel::where('_id', $data->id)->delete();
                //update sort
                MenuModel::where('sort', '>', $data->sort)->decrement('sort');
                //destroy
                $query = MenuModel::destroy($data->id);
            }
        }

        if (@$query) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function destroy_sub(Request $request)
    {
        $datas = MenuModel::find(explode(',', $request->id));
        if (@$datas) {
            foreach ($datas as $data) {
                MenuModel::where('id', $data->id)->delete();
                //update sort
                MenuModel::where('sort', '>', $data->sort)->where('_id',$data->_id)->decrement('sort');
                //destroy
                $query = MenuModel::destroy($data->id);
            }
        }

        if (@$query) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }

    public function changesort(Request $request)
    {
        $data = MenuModel::find($request->id);
        $checksort = MenuModel::where('id','!=',$data->id)
            ->where('position', 'main')
            ->where('sort',$request->sort)
            ->first();
        if($checksort)
        {
            $new_sort = MenuModel::where('sort',$request->sort)->where('position', 'main')->first();
            $new_sort->sort = $data->sort;
            $new_sort->save();
        }
        $data->sort = $request->sort;
        $data->save();
    }

    public function changesort_sub(Request $request)
    {
        $data = MenuModel::find($request->id);
        $checksort = MenuModel::where('id','!=',$data->id)->where('position', 'secondary')->where('_id',$data->_id)->where('sort',$request->sort)->first();
        if($checksort)
        {
            $new_sort = MenuModel::where('sort',$request->sort)->where('position', 'secondary')->where('_id',$data->_id)->first();
            $new_sort->sort = $data->sort;
            $new_sort->save();
        }
        $data->sort = $request->sort;
        $data->save();
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
            DB::beginTransaction();
            if ($id == null) {
                $data = new MenuModel();
                
                if($request->position == "main")
                {
                   $sort = MenuModel::where('position', 'main')->count();
                }
                else
                {
                    $sort = MenuModel::where('_id',$request->_id)->count();
                }
                $data->sort = $sort + 1;
                $data->created = date('Y-m-d H:i:s');
                $data->updated = date('Y-m-d H:i:s');
            } else {
                $data = MenuModel::find($id);
                $data->updated = date('Y-m-d H:i:s');
            }
            $data->name = $request->name;
            $data->_id = $request->_id;
            $data->url = $request->url;
            $data->icon = $request->icon;
            $data->position = $request->position;

            $menu = MenuModel::orderby("id","desc")->first();
            $data2 = new Role_listModel;
            $data2->role_id = 1;
            $data2->read = "on";
            $data2->edit = "on";
            $data2->add = "on";
            $data2->delete = "on";
            if($menu != null){
                $data2->menu_id = $menu->id+1;
            }else{
                $data2->menu_id = 1;
            }
            $data2->created = date('Y-m-d H:i:s');
            $data2->updated = date('Y-m-d H:i:s');
            $data2->save();

            
            if ($data->save()) {
                DB::commit();
                return view("$this->prefix.alert.success", ['url' => url("$this->segment/$this->folder")]);
            } else {
                DB::rollback();
                return view("$this->prefix.alert.error", ['url' => url("$this->segment/$this->folder")]);
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
