<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiSyncLogModel extends Model
{
    use HasFactory;
    
    protected $table = 'tb_api_sync_logs';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'api_provider_id', 'sync_type', 'status', 'started_at', 'completed_at',
        'total_records', 'created_tours', 'updated_tours', 'duplicated_tours', 
        'error_count', 'error_message', 'summary'
    ];
    
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'summary' => 'array',
    ];
    
    public function apiProvider()
    {
        return $this->belongsTo(ApiProviderModel::class, 'api_provider_id');
    }
    
    public function duplicates()
    {
        return $this->hasMany(TourDuplicateModel::class, 'sync_log_id');
    }
}