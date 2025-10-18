<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestformModel extends Model
{
    use HasFactory;
    protected $table = 'tb_testform';
    protected $primaryKey = 'id';
    public $timestamp = false;
}
