<?php

namespace App\Http\Controllers\Webpanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SimpleTourController extends Controller
{
    public function datatable(Request $request)
    {
        try {
            // Simple query without complex joins
            $tours = DB::table('tb_tour')
                ->select([
                    'id',
                    'name',
                    'code',
                    'code1', 
                    'status',
                    'updated_at'
                ])
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc')
                ->limit(100); // Limit for testing
                
            return DataTables::of($tours)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return $row->name ?: 'No Name';
                })
                ->editColumn('status', function ($row) {
                    return $row->status == 'on' 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->editColumn('updated_at', function ($row) {
                    return date('d/m/Y H:i', strtotime($row->updated_at));
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-primary">View</button>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
                
        } catch (\Exception $e) {
            // Return detailed error for debugging
            return response()->json([
                'error' => true,
                'message' => 'Database error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}