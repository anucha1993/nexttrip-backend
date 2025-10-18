<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuModel extends Model
{
    use HasFactory;
    protected $table = 'tb_menu';
    protected $primaryKey = 'id';
    protected $fillable = ['_id','name','url','icon','position'
    ,'sort','status','delete_status','created','updated','deleted'];
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    public $timestamp = false;
}
