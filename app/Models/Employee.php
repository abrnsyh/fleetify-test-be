<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'address',
        'employee_id',
        'department_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
