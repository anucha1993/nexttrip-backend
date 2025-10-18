<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourDuplicateModel extends Model
{
    use HasFactory;
    
    protected $table = 'tb_tour_duplicates';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'api_provider_id', 'sync_log_id', 'api_id', 'existing_tour_id',
        'api_data', 'comparison_result', 'status', 'processed_at'
    ];
    
    protected $casts = [
        'api_data' => 'array',
        'comparison_result' => 'array',
        'processed_at' => 'datetime',
    ];
    
    public function apiProvider()
    {
        return $this->belongsTo(ApiProviderModel::class, 'api_provider_id');
    }
    
    public function syncLog()
    {
        return $this->belongsTo(ApiSyncLogModel::class, 'sync_log_id');
    }
    
    public function existingTour()
    {
        return $this->belongsTo(TourModel::class, 'existing_tour_id');
    }
}