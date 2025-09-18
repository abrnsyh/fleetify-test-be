<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceHistory;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceController extends BaseController
{
    public function clockIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
        ]);

        $employee = Employee::where('employee_id', $request->employee_id)->first();
        if (! $employee) {
            return $this->sendError('Employee not found');
        }

        DB::beginTransaction();
        try {
            // Cari attendance hari ini berdasarkan employee_id dan tanggal clock_in
            $attendance = Attendance::where('employee_id', $employee->employee_id)
                ->whereDate('clock_in', now()->toDateString())
                ->first();

            if ($attendance && $attendance->clock_in) {
                DB::rollBack();

                return $this->sendError('Already clocked in today');
            }

            if (! $attendance) {
                $attendance = new Attendance;
                $attendance->id = Str::uuid();
                $attendance->attendance_id = Str::uuid();
                $attendance->employee_id = $employee->employee_id;
            }

            $attendance->clock_in = now();
            $attendance->save();

            AttendanceHistory::recordHistory($attendance->attendance_id, 'in');

            DB::commit();

            return $this->sendResponse($attendance, 'Clock-in successful');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Clock-in failed', $e->getMessage());
        }
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
        ]);

        $employee = Employee::where('employee_id', $request->employee_id)->first();
        if (! $employee) {
            return $this->sendError('Employee not found');
        }

        DB::beginTransaction();
        try {
            $attendance = Attendance::where('employee_id', $employee->employee_id)
                ->whereDate('clock_in', now()->toDateString())
                ->first();

            if (! $attendance || ! $attendance->clock_in) {
                DB::rollBack();

                return $this->sendError('Belum clock in');
            }

            if ($attendance->clock_out) {
                DB::rollBack();

                return $this->sendError('Sudah clock out');
            }

            $attendance->clock_out = now();
            $attendance->save();

            AttendanceHistory::recordHistory($attendance->attendance_id, 'out');

            DB::commit();

            return $this->sendResponse($attendance, 'Clock-out successful');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Clock-out failed', $e->getMessage());
        }
    }

    public function attendanceList(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
            'department_id' => 'nullable|uuid',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $date = $request->query('date', now()->toDateString());
        $perPage = $request->query('per_page', 10);

        $query = DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.employee_id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('attendance_histories as ah_in', function ($join) {
                $join->on('attendances.attendance_id', '=', 'ah_in.attendance_id')
                    ->where('ah_in.attendance_type', '=', 'in');
            })
            ->leftJoin('attendance_histories as ah_out', function ($join) {
                $join->on('attendances.attendance_id', '=', 'ah_out.attendance_id')
                    ->where('ah_out.attendance_type', '=', 'out');
            })
            ->select(
                'employees.employee_id',
                'employees.name as employee_name',
                'departments.department_name',
                'departments.max_clock_in_time',
                'departments.max_clock_out_time',
                'attendances.clock_in',
                'ah_in.description as clock_in_status',
                'attendances.clock_out',
                'ah_out.description as clock_out_status',
                DB::raw('DATE(attendances.clock_in) as attendance_date'),
            )
            ->whereDate('attendances.clock_in', $date);

        if ($request->filled('department_id')) {
            $query->where('departments.id', $request->query('department_id'));
        }

        $data = $query->paginate($perPage);

        // Format time fields for each item
        $data->getCollection()->transform(function ($item) {
            return [
                'employee_id' => $item->employee_id,
                'employee_name' => $item->employee_name,
                'department_name' => $item->department_name,
                'max_clock_in_time' => $item->max_clock_in_time,
                'max_clock_out_time' => $item->max_clock_out_time,
                'attendance_date' => $item->attendance_date,
                'clock_in' => $item->clock_in ? date('H:i:s', strtotime($item->clock_in)) : null,
                'clock_in_status' => $item->clock_in_status,
                'clock_out' => $item->clock_out ? date('H:i:s', strtotime($item->clock_out)) : null,
                'clock_out_status' => $item->clock_out_status,
            ];
        });

        return $this->sendResponse('Attendance list', $data);
    }
}
