<?php

namespace App\Http\Controllers\Webpanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class TourControllerTest extends Controller
{
    public function datatableSimple(Request $request){
        try {
            // ทดสอบ query แบบง่าย ๆ ก่อน
            $tours = DB::table('tb_tour')
                ->select('id', 'name', 'code', 'status', 'updated_at')
                ->whereNull('deleted_at')
                ->limit(10)
                ->get();

            return DataTables::of($tours)
                ->addIndexColumn()
                ->editColumn('updated_at', function ($row) {
                    return date('d/m/Y H:i:s', strtotime($row->updated_at));
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-primary">ดู</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
                
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}