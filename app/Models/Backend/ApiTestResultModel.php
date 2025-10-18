<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiTestResultModel extends Model
{
    use HasFactory;
    
    protected $table = 'tb_api_test_results';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'api_provider_id', 'test_type', 'status', 'response_message',
        'response_data', 'response_time', 'response_size', 'tested_at'
    ];
    
    protected $casts = [
        'response_data' => 'array',
        'tested_at' => 'datetime',
    ];
    
    public function apiProvider()
    {
        return $this->belongsTo(ApiProviderModel::class, 'api_provider_id');
    }
}