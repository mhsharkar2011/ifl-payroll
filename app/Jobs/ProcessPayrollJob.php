<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PayrollCalculationService;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessPayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $month;
    protected $year;

    public function __construct($month = null, $year = null)
    {
        $this->month = $month ?? Carbon::now()->month;
        $this->year = $year ?? Carbon::now()->year;
    }

    public function handle(PayrollCalculationService $payrollService)
    {
        try {
            Log::info('Payroll processing started for ' . $this->month . '/' . $this->year);

            $employees = Employee::with('user')->get();

            foreach ($employees as $employee) {
                $payrollData = $payrollService->calculateMonthlyPayroll(
                    $employee->id,
                    $this->month,
                    $this->year
                );

                // Save payroll record
                \App\Models\Payroll::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'month' => $this->month,
                        'year' => $this->year
                    ],
                    $payrollData
                );
            }

            Log::info('Payroll processing completed successfully');
        } catch (\Exception $e) {
            Log::error('Payroll processing failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
