<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends BaseController
{
    public function index(Request $request)
    {
        $params = array_merge($request->all(), [
            'search' => null,
            'page' => 1,
            'limit' => 10,
        ]);

        $departments = Department::query();

        if ($params['search']) {
            $departments->where('department_name', 'like', '%'.$params['search'].'%');
        }

        $departments = $departments->paginate($params['limit']);

        return $this->sendResponse('success', $departments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string|max:255|unique:departments,department_name',
            'max_clock_in_time' => 'required|date_format:H:i',
            'max_clock_out_time' => 'required|date_format:H:i',
        ]);

        try {
            $department = Department::create($request->only('department_name', 'max_clock_in_time', 'max_clock_out_time'));

            return $this->sendResponse('Department created successfully', $department, 201);
        } catch (\Exception $e) {
            return $this->sendError('Error creating department', $e->getMessage(), 500);
        }

    }

    public function update(Request $request,string $id)
    {
        $request->validate([
            'department_name' => 'sometimes|required|string|max:255|unique:departments,department_name,'.$id,
            'max_clock_in_time' => 'sometimes|required|date_format:H:i',
            'max_clock_out_time' => 'sometimes|required|date_format:H:i',
        ]);

        try {
            $department = Department::findOrFail($id);
            $department->update($request->only('department_name', 'max_clock_in_time', 'max_clock_out_time'));

            return $this->sendResponse('Department updated successfully', $department);
        } catch (\Exception $e) {
            return $this->sendError('Error updating department', $e->getMessage(), 500);
        }
    }

    public function destroy(Request $request,string $id)
    {
        try {
            $department = Department::findOrFail($id);
            $department->delete();

            return $this->sendResponse('Department deleted successfully', null);
        } catch (\Exception $e) {
            return $this->sendError('Error deleting department', $e->getMessage(), 500);
        }
    }
}
