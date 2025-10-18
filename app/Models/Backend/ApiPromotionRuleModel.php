<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiPromotionRuleModel extends Model
{
    use HasFactory;
    
    protected $table = 'tb_api_promotion_rules';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'api_provider_id',
        'rule_name',
        'condition_field',
        'condition_operator',
        'condition_value',
        'promotion_type',
        'promotion1_value',
        'promotion2_value',
        'priority',
        'is_active',
        'description'
    ];
    
    protected $casts = [
        'condition_value' => 'decimal:2',
        'is_active' => 'boolean',
        'priority' => 'integer'
    ];
    
    public function apiProvider()
    {
        return $this->belongsTo(ApiProviderModel::class, 'api_provider_id');
    }
    
    /**
     * Scope to get active rules ordered by priority
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('priority');
    }
    
    /**
     * Scope to get rules for specific API provider
     */
    public function scopeForProvider($query, $providerId)
    {
        return $query->where('api_provider_id', $providerId);
    }
    
    /**
     * Check if condition matches given value
     */
    public function checkCondition($value)
    {
        switch ($this->condition_operator) {
            case '>=':
                return $value >= $this->condition_value;
            case '<=':
                return $value <= $this->condition_value;
            case '>':
                return $value > $this->condition_value;
            case '<':
                return $value < $this->condition_value;
            case '=':
            case '==':
                return $value == $this->condition_value;
            case '!=':
                return $value != $this->condition_value;
            default:
                return false;
        }
    }
    
    /**
     * Get promotion values as array
     */
    public function getPromotionValues()
    {
        return [
            'promotion1' => $this->promotion1_value,
            'promotion2' => $this->promotion2_value
        ];
    }
}