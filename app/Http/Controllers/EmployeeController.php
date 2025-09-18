<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends BaseController
{
    public function index(Request $request)
    {
        $params = array_merge($request->all(), [
            'search' => null,
            'page' => 1,
            'limit' => 10,
        ]);

        // Implement employee listing logic here
        $employees = Employee::query()->with('department');
        if ($params['search']) {
            $employees->where('employee_name', 'like', '%'.$params['search'].'%');
        }
        $employees = $employees->paginate($params['limit']);

        return $this->sendResponse('success', $employees);

    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:employees,name',
            'employee_id' => 'required|string|max:11|unique:employees,employee_id',
            'department_id' => 'required|exists:departments,id',
            'address' => 'nullable|string',
        ]);

        try {
            $employee = Employee::create($request->only('name', 'department_id', 'employee_id', 'address'));

            return $this->sendResponse('Employee created successfully', $employee, 201);
        } catch (\Exception $e) {
            return $this->sendError('Error creating employee', $e->getMessage(), 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:employees,name,'.$id,
            'employee_id' => 'sometimes|required|string|max:11|unique:employees,employee_id,'.$id,
            'department_id' => 'sometimes|required|exists:departments,id',
            'address' => 'nullable|string',
        ]);

        try {
            $employee = Employee::findOrFail($id);
            $employee->update($request->only('name', 'department_id', 'employee_id', 'address'));

            return $this->sendResponse('Employee updated successfully', $employee);
        } catch (\Exception $e) {
            return $this->sendError('Error updating employee', $e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->delete();

            return $this->sendResponse('Employee deleted successfully', null);
        } catch (\Exception $e) {
            return $this->sendError('Error deleting employee', $e->getMessage(), 500);
        }
    }
}
