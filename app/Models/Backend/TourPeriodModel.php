<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourPeriodModel extends Model
{
    use HasFactory;
    protected $table = 'tb_tour_period';
    protected $primaryKey = 'id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    public $timestamp = false;
    
    protected $fillable = [
        'tour_id',
        'api_type', 
        'period_code',
        'period_api_id',
        'start_date',
        'end_date',
        'price1',
        'price2', 
        'price3',
        'price4',
        'count',
        'group',
        'special_price1'
    ];
}
