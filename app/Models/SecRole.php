<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecRole extends Model
{
    protected $table = 'sec_role';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name', 'description', 'active'];

    public function users()
    {
        return $this->hasMany(SecUser::class, 'role_id');
    }
}
