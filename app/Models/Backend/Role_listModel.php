<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role_listModel extends Model
{
    use HasFactory;
    protected $table = 'tb_role_list';
    protected $primaryKey = 'id';
    protected $fillable = ['role_id','menu_id','read','add','edit','deleted'
    ,'created','updated'];
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    public $timestamp = false;
}
