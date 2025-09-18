<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasUuids;

    protected $fillable = [
        'department_name',
        'max_clock_in_time',
        'max_clock_out_time',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
