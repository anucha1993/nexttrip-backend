<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Backend\ProvinceModel;

class DistrictModel extends Model
{
    use HasFactory;
    protected $table = 'tb_amupur';
    protected $primaryKey = 'id';
    protected $fillable = ['province_code','code','name_th','name_en'];
    public $timestamp = false;

    public function province(){
        return $this->hasOne(ProvinceModel::class, 'code', 'province_code');
    }
}
