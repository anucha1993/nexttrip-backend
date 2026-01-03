<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

//=== Models ===
use App\Models\Backend\BrandModel;
use App\Models\Backend\Brand_listModel;
use App\Models\Backend\CategoryModel;
use App\Models\Backend\Sub_categoryModel;
use App\Models\Backend\ProvinceModel;
use App\Models\Backend\AmupurModel;
use App\Models\Backend\TambonModel;
use App\Models\Backend\MemberModel;


class AjaxController extends Controller
{
    public function brand_get(Request $request)
    {
        $id = $request->brand_id;
        $row = Brand_listModel::where('brand_id',$id)->get();
        $json_result = [];
        if($row)
        {
            foreach($row as $r)
            {
                $rowd = CategoryModel::find($r->category_id);
                if($rowd)
                {
                    $json_result[] = [
                        'id'=> $rowd->id,
                        'name_th'=> $rowd->name_th,
                        'name_en'=> $rowd->AjaxController,
                    ];
                }
                else
                {
                    
                    $json_result[] = [
                        'id'=> '',
                        'name_th'=> '',
                        'name_en'=> '',
                    ];
                }
            }
            echo json_encode($json_result);
        }
    }
    public function category_get(Request $request)
    {
        $id = $request->category_id;
        $row = Sub_categoryModel::where('category_id',$id)->get();
        $json_result = [];
        if($row)
        {
            $html='<div class="row">';
            foreach($row as $r)
            {
                $html.='<div class="col-md-6">
                <input type="checkbox" id="" name="sub_category_id[]" value="'.$r->id.'"> '.$r->name_th.'
                </div> ';
            }
            $html.='</div>';
            echo $html;
        }
    }

    public function province(Request $request)
    {
        $id = $request->province_id;
        $data = ProvinceModel::find($id);
        $row = AmupurModel::where('province_code',$data->code)->get();
        if($row)
        {
            foreach($row as $r)
            {
                $json_result[] = [
                    'id'=> $r->id,
                    'name_th'=> $r->name_th,
                    'name_en'=> $r->name_en,
                ];
            }
            echo json_encode($json_result);
        }
    }

    public function district(Request $request)
    {
        $id = $request->amupur_id;
        $data = AmupurModel::find($id);
        $row = TambonModel::where('amupur_code',$data->code)->get();
        if($row)
        {
            foreach($row as $r)
            {
                $json_result[] = [
                    'id'=> $r->id,
                    'name_th'=> $r->name_th,
                    'name_en'=> $r->name_en,
                ];
            }
            echo json_encode($json_result);
        }
    }

    public function subdistrict(Request $request)
    {
        $id = $request->tambon_id;
        $row = TambonModel::find($id);
        if($row)
        {
            echo json_encode($row->postcode);
        }
    }

    public function showaddress(Request $request)
    {
        $data = MemberModel::find(Auth::guard('Member')->id());
        return view("front-end.dashboard",[
            'prefix' => "front-end",
            'member' => $data,
        ]);
    }
}
