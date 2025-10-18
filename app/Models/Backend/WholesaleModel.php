<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WholesaleModel extends Model
{
    use HasFactory;
    protected $table = 'tb_wholesale';
    protected $primaryKey = 'id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
	   protected $fillable = [
        'code',
        'wholesale_name_th',
        'wholesale_name_en',
        'tel',
        'contact_person',
        'status',
        'email',
        'textid',
        'address',
    ];

    public $timestamp = false;
}
