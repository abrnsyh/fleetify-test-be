<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function attendanceHistories()
    {
        return $this->hasMany(AttendanceHistory::class, 'attendance_id', 'attendance_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}
