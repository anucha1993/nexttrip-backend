<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogsModel extends Model
{
    use HasFactory;
    protected $table = 'tb_logs';
    protected $primaryKey = 'id';
    protected $fillable = ['type_log','error_log','error_line','error_url','login_id'
    ,'created','updated'];
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';
    public $timestamp = false;
}
