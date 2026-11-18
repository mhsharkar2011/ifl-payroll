<?php
namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PayrollCalculationService
{
    public function calculateMonthlyPayroll($employeeId, $month, $year)
    {
        $employee = Employee::with('user')->find($employeeId);
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Get all attendance for the month
        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereBetween('timestamp', [$startDate, $endDate])
            ->get();

        // Calculate working days
        $workingDays = $this->calculateWorkingDays($startDate, $endDate);

        // Calculate total hours worked
        $totalHours = $this->calculateTotalHours($attendance);

        // Calculate overtime
        $overtime = $this->calculateOvertime($totalHours, $workingDays);

        // Calculate salary
        $basicSalary = $employee->salary;
        $dailyRate = $basicSalary / $workingDays;
        $totalSalary = $dailyRate * $workingDays + $overtime;

        // Deductions
        $deductions = $this->calculateDeductions($totalSalary);

        // Net salary
        $netSalary = $totalSalary - $deductions;

        return [
            'employee_id' => $employeeId,
            'month' => $month,
            'year' => $year,
            'basic_salary' => $basicSalary,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtime['hours'],
            'overtime_amount' => $overtime['amount'],
            'total_earnings' => $totalSalary,
            'deductions' => $deductions,
            'net_salary' => $netSalary,
            'generated_at' => now()
        ];
    }

    private function calculateWorkingDays($startDate, $endDate)
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $workingDays = 0;

        foreach ($period as $date) {
            if ($date->isWeekday()) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    private function calculateTotalHours($attendance)
    {
        $totalMinutes = 0;
        $grouped = $attendance->groupBy(function($item) {
            return Carbon::parse($item->timestamp)->format('Y-m-d');
        });

        foreach ($grouped as $date => $records) {
            $checkIn = $records->where('status', 'check_in')->first();
            $checkOut = $records->where('status', 'check_out')->first();

            if ($checkIn && $checkOut) {
                $minutes = Carbon::parse($checkIn->timestamp)
                    ->diffInMinutes(Carbon::parse($checkOut->timestamp));
                $totalMinutes += $minutes;
            }
        }

        return round($totalMinutes / 60, 2);
    }

    private function calculateOvertime($totalHours, $workingDays)
    {
        $regularHours = $workingDays * 8; // Assuming 8 hours per day
        $overtimeHours = max(0, $totalHours - $regularHours);

        // Overtime rate = 1.5x regular rate
        $overtimeAmount = $overtimeHours * (config('payroll.hourly_rate') * 1.5);

        return [
            'hours' => round($overtimeHours, 2),
            'amount' => round($overtimeAmount, 2)
        ];
    }

    private function calculateDeductions($grossSalary)
    {
        // Example: Tax, Insurance, etc.
        $tax = $grossSalary * 0.10; // 10% tax
        $insurance = 500; // Fixed insurance amount

        return round($tax + $insurance, 2);
    }
}
