<?php

namespace App\Models\Authuse;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable
{
    use Notifiable;

    protected $table = 'tb_member';

    // protected $fillable = ['firstname','lastname','email','password','display_name','mobile','gender','birth_day'];

    protected $hidden = ['password','remember_token'];
}
