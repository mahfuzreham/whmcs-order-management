<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Staff extends Model
{
    protected $fillable = ['name','email','password','role','active','permissions'];
    protected $hidden = ['password'];
    protected $casts = ['active'=>'boolean','permissions'=>'array'];

    public function checkPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    public function canAccess(string $permission): bool
    {
        if (!$this->active) return false;
        if ($this->role === 'super_admin') return true;
        return in_array($permission, $this->permissions ?? [], true);
    }
}
