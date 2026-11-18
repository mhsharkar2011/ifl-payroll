<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Spatie\Permission\Models\Role;

class EmployeesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public $imported = 0;
    public $skipped = 0;
    public $importErrors = [];

    /**
     * Header aliases - different variations map
     */
    protected array $headerAliases = [
        // Employee ID
        'employeeid'        => 'employee_id',
        'emp_id'            => 'employee_id',
        'emp_code'          => 'employee_id',
        'employee_code'     => 'employee_id',
        'staff_id'          => 'employee_id',
        'id'                => 'employee_id',
        'pin'               => 'employee_id',
        'enroll_number'     => 'employee_id',

        // First Name
        'firstname'         => 'first_name',
        'fname'             => 'first_name',
        'given_name'        => 'first_name',
        'name'              => 'first_name',

        // Last Name
        'lastname'          => 'last_name',
        'lname'             => 'last_name',
        'surname'           => 'last_name',

        // Email
        'email_address'     => 'email',
        'emailaddress'      => 'email',
        'e_mail'            => 'email',
        'mail'              => 'email',

        // Phone
        'mobile'            => 'phone',
        'phone_number'      => 'phone',
        'mobile_number'     => 'phone',
        'contact'           => 'phone',

        // Department
        'dept'              => 'department_id',
        'department'        => 'department_id',

        // Designation
        'position'          => 'designation_id',
        'designation'       => 'designation_id',
        'job_title'         => 'designation_id',

        // Salary
        'basic_salary'      => 'salary',
        'monthly_salary'    => 'salary',
        'wage'              => 'salary',

        // Dates
        'join_date'         => 'joining_date',
        'date_of_joining'   => 'joining_date',
        'doj'               => 'joining_date',
        'dob'               => 'date_of_birth',
        'birth_date'        => 'date_of_birth',

        // Device ID
        'device_id'         => 'device_user_id',
        'deviceuid'         => 'device_user_id',
        'device_uid'        => 'device_user_id',
        'zkteco_id'         => 'device_user_id',
        'biometric_id'      => 'device_user_id',

        // Address
        'full_address'      => 'address',
        'home_address'      => 'address',
    ];

    /**
     * Process each row.
     */
    public function model(array $row): \Illuminate\Database\Eloquent\Model|array|null
    {
        // Normalize headers
        $row = $this->normalizeRow($row);

        // Cast numeric values to string
        $row = $this->castToStrings($row);

        // Debug log
        Log::info('=== Import Row ===', [
            'keys' => array_keys($row),
            'data' => $row,
        ]);

        // Skip completely empty rows
        if (empty(array_filter($row))) {
            return null;
        }

        // Required field check
        if (empty($row['employee_id']) || empty($row['first_name']) || empty($row['email'])) {
            $this->skipped++;
            Log::warning('Row skipped - missing required fields', [
                'employee_id' => $row['employee_id'] ?? 'MISSING',
                'first_name'  => $row['first_name'] ?? 'MISSING',
                'email'       => $row['email'] ?? 'MISSING',
                'all_data'    => $row,
            ]);
            return null;
        }

        // Duplicate check
        $exists = Employee::where('employee_id', $row['employee_id'])
            ->orWhere('email', $row['email'])
            ->first();

        if ($exists) {
            $this->skipped++;
            Log::info("Duplicate: {$row['employee_id']} - {$row['email']}");
            return null;
        }

        try {
            $userId = null;
            $userExists = User::where('email', $row['email'])->first();

            if (!$userExists) {
                $user = User::create([
                    'name'     => trim($row['first_name'] . ' ' . ($row['last_name'] ?? '')),
                    'email'    => $row['email'],
                    'password' => Hash::make($row['password'] ?? 'password'),
                ]);

                try {
                    $role = Role::firstOrCreate(['name' => 'Employee']);
                    $user->assignRole($role);
                } catch (\Exception $e) {
                    Log::warning('Role assign failed: ' . $e->getMessage());
                }

                $userId = $user->id;
            } else {
                $userId = $userExists->id;
            }

            // ✅ Handle department_id & designation_id (FK columns)
            $departmentId = $this->resolveDepartment($row['department_id'] ?? null);
            $designationId = $this->resolveDesignation($row['designation_id'] ?? null);

            $this->imported++;
            Log::info("✅ Importing: {$row['employee_id']} - {$row['first_name']}");

            return new Employee([
                'user_id'        => $userId,
                'employee_id'    => $row['employee_id'],
                'first_name'     => $row['first_name'],
                'last_name'      => $row['last_name'] ?? '',
                'email'          => $row['email'],
                'phone'          => $row['phone'] ?? null,
                'department_id'  => $departmentId,    // ✅ FK
                'designation_id' => $designationId,   // ✅ FK
                'salary'         => $row['salary'] ?? 0,
                'joining_date'   => $this->parseDate($row['joining_date'] ?? null),
                'date_of_birth'  => $this->parseDate($row['date_of_birth'] ?? null),
                'address'        => $row['address'] ?? null,
                'device_user_id' => $row['device_user_id'] ?? null,
                'status'         => $row['status'] ?? 'active',
            ]);

        } catch (\Exception $e) {
            $this->skipped++;
            $this->importErrors[] = "Row error ({$row['employee_id']}): " . $e->getMessage();
            Log::error('Import row error: ' . $e->getMessage(), [
                'row' => $row,
            ]);
            return null;
        }
    }

    /**
     * Resolve department ID from name or ID.
     */
    protected function resolveDepartment($value): ?int
    {
        if (empty($value)) return null;

        // If numeric, verify it exists
        if (is_numeric($value)) {
            $exists = \App\Models\Department::find((int) $value);
            return $exists ? (int) $value : null;
        }

        // Otherwise, find or create by name
        try {
            $dept = \App\Models\Department::firstOrCreate(
                ['name' => trim((string) $value)],
                ['name' => trim((string) $value)]
            );
            return $dept->id;
        } catch (\Exception $e) {
            Log::warning("Department resolve failed: {$value} — " . $e->getMessage());
            return null;
        }
    }

    /**
     * Resolve designation ID from name or ID.
     */
    protected function resolveDesignation($value): ?int
    {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            $exists = \App\Models\Designation::find((int) $value);
            return $exists ? (int) $value : null;
        }

        try {
            $desig = \App\Models\Designation::firstOrCreate(
                ['name' => trim((string) $value)],
                ['name' => trim((string) $value)]
            );
            return $desig->id;
        } catch (\Exception $e) {
            Log::warning("Designation resolve failed: {$value} — " . $e->getMessage());
            return null;
        }
    }

    /**
     * Normalize header keys.
     */
    protected function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $cleanKey = strtolower(trim((string) $key));
            $cleanKey = str_replace([' ', '-', '.', '/'], '_', $cleanKey);
            $cleanKey = preg_replace('/_+/', '_', $cleanKey);
            $cleanKey = trim($cleanKey, '_');

            // Apply alias map
            $cleanKey = $this->headerAliases[$cleanKey] ?? $cleanKey;

            $normalized[$cleanKey] = $value;
        }

        return $normalized;
    }

    /**
     * Cast numeric values to strings where needed.
     */
    protected function castToStrings(array $row): array
    {
        $stringFields = [
            'employee_id', 'first_name', 'last_name', 'phone',
            'address', 'device_user_id', 'status', 'password',
        ];

        foreach ($stringFields as $field) {
            if (isset($row[$field]) && $row[$field] !== null && $row[$field] !== '') {
                if (is_numeric($row[$field])) {
                    $row[$field] = (string) $row[$field];
                    if (strpos($row[$field], '.') !== false) {
                        $row[$field] = rtrim(rtrim($row[$field], '0'), '.');
                    }
                } else {
                    $row[$field] = trim((string) $row[$field]);
                }
            }
        }

        return $row;
    }

    /**
     * Parse dates.
     */
    protected function parseDate($date)
    {
        if (empty($date)) return null;

        try {
            if (is_numeric($date)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
            }
            return Carbon::parse($date);
        } catch (\Exception $e) {
            Log::warning('Date parse failed: ' . $e->getMessage());
            return null;
        }
    }
}
