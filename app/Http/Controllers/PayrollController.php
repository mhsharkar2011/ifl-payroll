<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Http\Requests\StorePayrollRequest;
use App\Http\Requests\UpdatePayrollRequest;
use Inertia\Inertia;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePayrollRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Payroll $payroll)
    {
        // Load employee details and their department
        $payroll->load([
            'employee:id,employee_id,first_name,last_name,email,designation_id,department_id',
            'employee.department:id,name',
            'employee.designation:id,name',
        ]);

        return Inertia::render('Admin/Payroll/Show', [
            'payroll' => $payroll,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payroll $payroll)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePayrollRequest $request, Payroll $payroll)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payroll $payroll)
    {
        //
    }

    
}
