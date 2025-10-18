<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourModel extends Model
{
    use HasFactory;
    protected $table = 'tb_tour';
    protected $primaryKey = 'id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    public $timestamp = false;
    
    protected $fillable = [
        'api_id', 'api_type', 'data_type', 'code', 'code1', 'name', 'description',
        'rating', 'num_day', 'image', 'pdf_file', 'country_name', 'airline_code',
        'group_id', 'wholesale_id', 'type_id', 'landmass_id', 'country_id',
        'city_id', 'province_id', 'district_id', 'tag_id', 'price', 'price_group',
        'special_price', 'travel', 'shop', 'eat', 'special', 'stay', 'video',
        'video_cover', 'pdf_file_size', 'word_file', 'tour_detail', 'promotion1',
        'promotion2', 'status', 'tab_status', 'slug', 'tour_views', 'date_mod_pdf',
        'image_check_change', 'country_check_change', 'airline_check_change',
        'name_check_change', 'description_check_change', 'code1_check', 'airline_id'
    ];

    public function period()
    {
        return $this->hasMany(TourPeriodModel::class, 'tour_id'); 
    }
}
