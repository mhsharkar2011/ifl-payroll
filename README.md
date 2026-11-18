🏗️ প্রজেক্ট আর্কিটেকচার ও টেক স্ট্যাক
প্রযুক্তি স্ট্যাক
Backend: Laravel 11.x (PHP 8.2+)

Frontend: Inertia.js + Vue.js 3 (SPA)

UI Framework: Tailwind CSS

Authentication: Laravel Breeze/Sanctum (API-ready)

Role Management: Spatie/laravel-permission

Build Tool: Vite

Mobile Support: PWA (Progressive Web App)

Database: MySQL/PostgreSQL

ZKTeco Integration: jmrashed/zkteco package


📁 প্রজেক্ট স্ট্রাকচার ডিজাইন

employee-attendance-payroll/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── EmployeeController.php
│   │   │   │   ├── AttendanceController.php
│   │   │   │   ├── PayrollController.php
│   │   │   │   ├── DeviceController.php
│   │   │   │   └── ReportController.php
│   │   │   └── Employee/
│   │   │       ├── DashboardController.php
│   │   │       ├── MyAttendanceController.php
│   │   │       └── MyPayrollController.php
│   │   ├── Middleware/
│   │   │   ├── RoleMiddleware.php
│   │   │   └── PermissionMiddleware.php
│   │   └── Requests/
│   │       ├── EmployeeRequest.php
│   │       ├── AttendanceRequest.php
│   │       └── PayrollRequest.php
│   ├── Models/
│   │   ├── User.php (extends Authenticatable)
│   │   ├── Employee.php
│   │   ├── Attendance.php
│   │   ├── Payroll.php
│   │   ├── Device.php
│   │   └── Leave.php
│   ├── Services/
│   │   ├── ZKTecoService.php
│   │   ├── AttendanceSyncService.php
│   │   ├── PayrollCalculationService.php
│   │   └── ReportService.php
│   └── Jobs/
│       ├── SyncAttendanceJob.php
│       └── ProcessPayrollJob.php
├── database/
│   └── migrations/
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   │   ├── Admin/
│   │   │   │   ├── EmployeeList.vue
│   │   │   │   ├── EmployeeForm.vue
│   │   │   │   ├── AttendanceTable.vue
│   │   │   │   ├── PayrollCalculator.vue
│   │   │   │   ├── DeviceConfig.vue
│   │   │   │   └── Reports.vue
│   │   │   ├── Employee/
│   │   │   │   ├── MyDashboard.vue
│   │   │   │   ├── MyAttendance.vue
│   │   │   │   └── MyPayroll.vue
│   │   │   └── Shared/
│   │   │       ├── Layouts/
│   │   │       ├── Navbar.vue
│   │   │       └── Sidebar.vue
│   │   ├── Composables/
│   │   │   └── usePermission.js
│   │   ├── Pages/
│   │   │   ├── Admin/
│   │   │   │   ├── Dashboard.vue
│   │   │   │   ├── Employees.vue
│   │   │   │   ├── Attendance.vue
│   │   │   │   ├── Payroll.vue
│   │   │   │   ├── Devices.vue
│   │   │   │   └── Reports.vue
│   │   │   └── Employee/
│   │   │       ├── Dashboard.vue
│   │   │       ├── Attendance.vue
│   │   │       └── Payroll.vue
│   │   ├── app.js
│   │   └── bootstrap.js
│   └── views/
│       └── app.blade.php
├── routes/
│   ├── web.php
│   └── api.php
├── public/
│   ├── service-worker.js
│   └── manifest.json
└── config/
    ├── permission.php
    ├── zkteco.php
    └── payroll.php
--------------------------------------------------------------------

🔧 ইনস্টলেশন ও কনফিগারেশন স্টেপ-বাই-স্টেপ
1. নতুন Laravel প্রজেক্ট তৈরি করুন

composer create-project laravel/laravel employee-attendance-payroll
cd employee-attendance-payroll
------------------------------------------------------------------
2. প্রয়োজনীয় প্যাকেজ ইনস্টল করুন

# Laravel Inertia & Vue.js Setup
composer require laravel/breeze --dev
composer require inertiajs/inertia-laravel

# Vue.js and Inertia Frontend
npm install @inertiajs/vue3 vue @vitejs/plugin-vue

# Role Management
composer require spatie/laravel-permission

# ZKTeco Integration
composer require jmrashed/zkteco

# Additional Utilities
composer require laravel/sanctum
composer require maatwebsite/excel
------------------------------------------------------------

3. ZKTeco কানেকশন সেটআপ
.env ফাইলে নিচের কনফিগারেশন যোগ করুন:

ZK_TECO_IP=192.168.1.201
ZK_TECO_PORT=4370
ZK_TECO_PASSWORD=0
ZK_TECO_TIMEOUT=30
------------------------------------------------------------
4. Authentication ও Role System সেটআপ

php artisan breeze:install vue
php artisan migrate

# Spatie Permission Setup
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate

-------------------------------------------------------------
5. Permissions ও Roles কনফিগার করুন
database/seeders/RolePermissionSeeder.php তৈরি করুন:

<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view_dashboard',
            'view_employees', 'create_employees', 'edit_employees', 'delete_employees',
            'view_attendance', 'create_attendance', 'edit_attendance', 'delete_attendance',
            'view_payroll', 'create_payroll', 'edit_payroll', 'delete_payroll',
            'manage_devices',
            'view_reports'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        $employeeRole = Role::create(['name' => 'Employee']);
        $employeeRole->givePermissionTo([
            'view_dashboard',
            'view_attendance', 'create_attendance',
            'view_payroll'
        ]);

        // Create Super Admin (optional)
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());
    }
}

------------------------------------------------------------------------

6. Inertia + Vue.js কনফিগারেশন
vite.config.js:

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});
------------------------------------------------------------------

7. Service Worker (PWA Support) তৈরি করুন
public/service-worker.js:

const CACHE_NAME = 'attendance-app-v1';
const urlsToCache = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/manifest.json'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', event => {
    if (event.request.headers.get('X-Inertia') === 'true' && event.request.method === 'GET') {
        event.respondWith(
            caches.match(event.request)
                .then(response => response || fetch(event.request))
        );
    } else {
        event.respondWith(
            caches.match(event.request)
                .then(response => response || fetch(event.request))
        );
    }
});

-------------------------------------------------------------------------

public/manifest.json:

{
    "name": "Employee Attendance System",
    "short_name": "Attendance",
    "start_url": "/",
    "display": "standalone",
    "background_color": "#ffffff",
    "theme_color": "#4F46E5",
    "icons": [
        {
            "src": "/favicon-192x192.png",
            "sizes": "192x192",
            "type": "image/png"
        },
        {
            "src": "/favicon-512x512.png",
            "sizes": "512x512",
            "type": "image/png"
        }
    ]
}

------------------------------------------------------------------------------

💻 কোড ইমপ্লিমেন্টেশন
ZKTeco সার্ভিস ক্লাস
app/Services/ZKTecoService.php:

<?php
namespace App\Services;

use ZKTeco\ZKTeco;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class ZKTecoService
{
    protected $device;
    protected $ip;
    protected $port;
    protected $password;

    public function __construct()
    {
        $this->ip = config('zkteco.ip');
        $this->port = config('zkteco.port');
        $this->password = config('zkteco.password');
        $this->device = new ZKTeco($this->ip, $this->port);
    }

    public function connect()
    {
        try {
            if (!$this->device->connect()) {
                throw new \Exception('Device connection failed');
            }
            $this->device->disableDevice();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('ZKTeco connection error: ' . $e->getMessage());
        }
    }

    public function disconnect()
    {
        if ($this->device) {
            $this->device->enableDevice();
            $this->device->disconnect();
        }
    }

    public function syncAttendance()
    {
        $this->connect();
        $attendanceLogs = $this->device->getAttendance();
        $synced = [];

        foreach ($attendanceLogs as $log) {
            $employee = Employee::where('device_user_id', $log['uid'])->first();
            
            if ($employee) {
                $attendance = Attendance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'timestamp' => Carbon::parse($log['timestamp'])
                    ],
                    [
                        'status' => $this->getAttendanceStatus($log['type']),
                        'verified_by' => $log['type'] // 0=Manual, 1=Fingerprint, 2=Face
                    ]
                );
                $synced[] = $attendance;
            }
        }

        $this->disconnect();
        return $synced;
    }

    private function getAttendanceStatus($type)
    {
        // 0 = Check-in, 1 = Check-out, 2 = Break-in, 3 = Break-out
        return match($type) {
            0 => 'check_in',
            1 => 'check_out',
            2 => 'break_in',
            3 => 'break_out',
            default => 'unknown'
        };
    }

    public function getDeviceInfo()
    {
        $this->connect();
        $info = [
            'version' => $this->device->version(),
            'serial' => $this->device->serialNumber(),
            'platform' => $this->device->platform()
        ];
        $this->disconnect();
        return $info;
    }
}
------------------------------------------------------------------------------

Attendance Sync Job
app/Jobs/SyncAttendanceJob.php:

<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ZKTecoService;

class SyncAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ZKTecoService $zkService)
    {
        try {
            $synced = $zkService->syncAttendance();
            \Log::info('Attendance synced successfully', ['count' => count($synced)]);
        } catch (\Exception $e) {
            \Log::error('Attendance sync failed: ' . $e->getMessage());
        }
    }
}
------------------------------------------------------------------------------

Payroll Calculation Service
app/Services/PayrollCalculationService.php:

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

-----------------------------------------------------------------------------

Vue.js Components
Permission Composable - resources/js/composables/usePermission.js:

import { usePage } from '@inertiajs/vue3';

export function usePermission() {
    const page = usePage();

    const can = (permission) => {
        const permissions = page.props.permissions || [];
        return permissions.includes(permission);
    };

    const is = (role) => {
        const roles = page.props.roles || [];
        return roles.includes(role);
    };

    return {
        can,
        is,
        permissions: page.props.permissions || [],
        roles: page.props.roles || []
    };
}

--------------------------------------------------------------------

Admin Dashboard - resources/js/Pages/Admin/Dashboard.vue:

<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Components/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const props = defineProps({
    stats: Object,
    recentAttendance: Array,
    payrollSummary: Object
});

const { can } = usePermission();
const hasManageAccess = computed(() => can('manage_devices') || can('view_reports'));
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ড্যাশবোর্ড
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm text-gray-600">মোট কর্মচারী</div>
                        <div class="text-3xl font-bold text-indigo-600">{{ stats.totalEmployees }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm text-gray-600">আজকের উপস্থিতি</div>
                        <div class="text-3xl font-bold text-green-600">{{ stats.todayPresent }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm text-gray-600">অনুপস্থিত</div>
                        <div class="text-3xl font-bold text-red-600">{{ stats.todayAbsent }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm text-gray-600">অতিরিক্ত সময়</div>
                        <div class="text-3xl font-bold text-blue-600">{{ stats.totalOvertime }} ঘন্টা</div>
                    </div>
                </div>

                <!-- Recent Attendance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">সাম্প্রতিক উপস্থিতি</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">নাম</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">সময়</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">স্থিতি</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">যাচাই</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="attendance in recentAttendance" :key="attendance.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ attendance.employee.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ attendance.timestamp }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-green-100 text-green-800': attendance.status === 'check_in',
                                            'bg-red-100 text-red-800': attendance.status === 'check_out'
                                        }" class="px-2 py-1 text-xs rounded-full">
                                            {{ attendance.status === 'check_in' ? 'ইন' : 'আউট' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ attendance.verified_by === 1 ? 'ফিঙ্গারপ্রিন্ট' : 'ম্যানুয়াল' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

-----------------------------------------------------------------------------------------------------------------

⚙️ Database Schema (Key Tables)
Employees Table

Schema::create('employees', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('employee_id')->unique();
    $table->string('first_name');
    $table->string('last_name');
    $table->string('email')->unique();
    $table->string('phone');
    $table->string('department');
    $table->string('designation');
    $table->decimal('salary', 10, 2);
    $table->string('device_user_id')->nullable(); // ZKTeco user ID
    $table->date('joining_date');
    $table->timestamps();
});

--------------------------------------------------------------------------------------------------------------
Environment Variables (.env)

APP_ENV=production
APP_DEBUG=false

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance_db
DB_USERNAME=root
DB_PASSWORD=

# ZKTeco
ZK_TECO_IP=192.168.1.201
ZK_TECO_PORT=4370

# Queue (Redis)
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

-----------------------------------------------------------------

📱 PWA & Mobile Features
Offline Support: Service Worker ক্যাশিং ব্যবহার

Push Notifications: Laravel Web Push ব্যবহার করে নোটিফিকেশন

Installable: Manifest.json ফাইলের মাধ্যমে অ্যাপ ইনস্টলেশন

Responsive: Tailwind CSS এর responsive utilities ব্যবহার
-----------------------------------------------------------------
🎯 Key Features & Workflow
Attendance Flow
ZKTeco মেশিন থেকে ZKTecoService ব্যবহার করে Raw Data নিন

SyncAttendanceJob চালিয়ে ডাটাবেসে সংরক্ষণ করুন

Vue.js কম্পোনেন্টে Real-time উপস্থিতি দেখান

Payroll Flow
মাস শেষে ProcessPayrollJob চালান

PayrollCalculationService ব্যবহার করে বেতন গণনা করুন

Employee পোর্টালে Payroll স্লিপ দেখান

Report হিসেবে Export করুন (Excel/PDF)

----------------------------------------------------------------------
# Employee Attendance & Payroll System

## 📋 Overview
Laravel + Inertia.js + Vue.js based employee attendance system with ZKTeco biometric device integration and automated payroll processing.

## 🛠️ Tech Stack
- **Backend**: Laravel 11.x, PHP 8.2+
- **Frontend**: Inertia.js, Vue.js 3, Tailwind CSS
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Breeze + Sanctum
- **Roles**: Spatie/laravel-permission
- **Build Tool**: Vite
- **Device Integration**: ZKTeco SDK
- **Mobile Support**: PWA

## 🚀 Installation

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL/PostgreSQL
- Redis (optional)

### Step 1: Clone & Install
```bash
git clone https://github.com/yourusername/employee-attendance-payroll.git
cd employee-attendance-payroll
composer install
npm install


-----------------------------------------------------------------------------

📧 Support
Email: support@yourdomain.com


---

## 🎨 UI Preview (Tailwind CSS)

```vue
<!-- Modern Card Design -->
<div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Total Employees</h3>
            <p class="text-3xl font-bold text-indigo-600 mt-2">{{ totalEmployees }}</p>
        </div>
        <div class="p-3 bg-indigo-100 rounded-full">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
    </div>
</div>
----------------------------------------------------------------------------


✅ বাস্তবায়ন চেকলিস্ট
□ Laravel প্রজেক্ট তৈরি ও কনফিগারেশন
□ ZKTeco প্যাকেজ ইনস্টলেশন ও কনফিগারেশন
□ Authentication & Role Management সেটআপ
□ Database Migration & Seeding
□ ZKTeco Service Implementation
□ Attendance Sync Job
□ Payroll Calculation Service
□ Inertia + Vue.js Setup
□ PWA Support Configuration
□ Admin Panel UI Components
□ Employee Panel UI Components
□ Report Generation
□ Testing & Debugging
□ Deployment Setup
এই সম্পূর্ণ ডিজাইনটি অনুসরণ করলে আপনি একটি পূর্ণাঙ্গ Employee Attendance এবং Payroll System তৈরি করতে পারবেন যা ZKTeco ডিভাইসের সাথে সংযুক্ত থাকবে এবং PHP Laravel, Inertia.js, Vue.js ব্যবহার করে বিল্ড হবে। মোবাইল হিসেবে PWA সমর্থন থাকবে। 🎉

## Run the following command in your terminal:

php artisan ziggy:generate

Alternative: Direct Protocol Package (No Manual Export)
Manual upload এড়াতে চাইলে mb460 attendance log data manually upload এর জন্য একটি dedicated Laravel package ব্যবহার করতে পারেন যা সরাসরি device থেকে data টানে:

anascloud/zkteko Package

composer require anascloud/zkteko

composer require maatwebsite/excel


For Bata Backup
composer require spatie/laravel-backup

php artisan make:model Backup -m


Step 9: .gitignore এ Backups Exclude করুন
.gitignore ফাইলে যোগ করুন:

text
/storage/app/backups/*
!/storage/app/backups/.gitignore
storage/app/backups/.gitignore ফাইল তৈরি করুন:

text
*
!.gitignore

## For Running Schedule

php artisan schedule:work

## To See Schedule List

php artisan schedule:list


# Build images
docker compose build

# Start all services
docker compose up -d

# Install dependencies
docker compose exec app composer install

# Generate app key (first time)
docker compose exec app php artisan key:generate

# Run migrations
docker compose exec app php artisan migrate

# Create storage symlink
docker compose exec app php artisan storage:link

# Access app
# → http://localhost:8080

# View logs
docker compose logs -f app

# Shell into PHP container
docker compose exec app bash

# Stop everything
docker compose down

# Stop and remove volumes (⚠️ deletes DB data)
docker compose down -v

Menu → Comm → Cloud Server Setting (or ADMS)

  Server Address:    192.168.1.9        ← LAN IP, no http:// prefix
  Server Port:       8080
  Server Path:       (leave blank)
  Enable Push:       ON  ← critical
  Enable Real-Time:  ON
  Enable Domain Name: No
  Enable Proxy:      No
  Heartbeat:         10 seconds
  Save → Reboot
# ifl-payroll
