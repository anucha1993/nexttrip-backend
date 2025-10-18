<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiProviderModel extends Model
{
    use HasFactory;
    
    protected $table = 'tb_api_providers';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name', 'code', 'url', 'period_endpoint', 'tour_detail_endpoint', 
        'requires_multi_step', 'url_parameters', 'headers', 'config', 'status', 'description'
    ];
    
    protected $casts = [
        'headers' => 'array',
        'config' => 'array',
        'url_parameters' => 'array',
        'requires_multi_step' => 'boolean',
    ];
    
    public function fieldMappings()
    {
        return $this->hasMany(ApiFieldMappingModel::class, 'api_provider_id');
    }
    
    public function conditions()
    {
        return $this->hasMany(ApiConditionModel::class, 'api_provider_id');
    }
    
    public function schedules()
    {
        return $this->hasMany(ApiScheduleModel::class, 'api_provider_id');
    }
    
    public function syncLogs()
    {
        return $this->hasMany(ApiSyncLogModel::class, 'api_provider_id');
    }
    
    public function duplicates()
    {
        return $this->hasMany(TourDuplicateModel::class, 'api_provider_id');
    }
    
    public function testResults()
    {
        return $this->hasMany(ApiTestResultModel::class, 'api_provider_id');
    }
    
    public function promotionRules()
    {
        return $this->hasMany(ApiPromotionRuleModel::class, 'api_provider_id');
    }
}