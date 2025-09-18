<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AttendanceHistory extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'attendance_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public static function recordHistory(string $attendance_id, string $attendance_type)
    {
        $attendance = Attendance::where('attendance_id', $attendance_id)->first();

        if ($attendance) {
            $employee = $attendance->employee;
            $department = $employee ? $employee->department : null;
            $description = null;

            if ($department) {
                if ($attendance_type === 'in') {
                    $clockInTime = $attendance->clock_in ? date('H:i:s', strtotime($attendance->clock_in)) : null;
                    $clockInMax = $department->max_clock_in_time;
                    if ($clockInTime && $clockInMax) {
                        if ($clockInTime > $clockInMax) {
                            $description = 'late';
                        } else {
                            $description = 'ontime';
                        }
                    }
                } elseif ($attendance_type === 'out') {
                    $clockOutTime = $attendance->clock_out ? date('H:i:s', strtotime($attendance->clock_out)) : null;
                    $clockOutMax = $department->max_clock_out_time;
                    if ($clockOutTime && $clockOutMax) {
                        if ($clockOutTime < $clockOutMax) {
                            $description = 'early';
                        } else {
                            $description = 'ontime';
                        }
                    }
                }
            }

            self::create([
                'attendance_id' => $attendance->attendance_id,
                'employee_id' => $attendance->employee_id,
                'date_attendance' => now()->toDateString(),
                'attendance_type' => $attendance_type,
                'description' => $description,
            ]);
        }
    }
}
