<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiFieldMappingModel extends Model
{
    use HasFactory;
    
    protected $table = 'tb_api_field_mappings';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'api_provider_id', 'field_type', 'local_field', 'api_field', 
        'data_type', 'transformation_rules', 'is_required'
    ];
    
    protected $casts = [
        'transformation_rules' => 'array',
        'is_required' => 'boolean',
    ];
    
    public function apiProvider()
    {
        return $this->belongsTo(ApiProviderModel::class, 'api_provider_id');
    }
}