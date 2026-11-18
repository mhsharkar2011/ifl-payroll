<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard
            'view_dashboard',

            // Employee Management
            'view_employees',
            'create_employees',
            'edit_employees',
            'delete_employees',

            // Attendance
            'view_attendance',
            'create_attendance',
            'edit_attendance',
            'delete_attendance',
            'sync_attendance',

            // Payroll
            'view_payroll',
            'create_payroll',
            'edit_payroll',
            'delete_payroll',
            'process_payroll',

            // Devices
            'manage_devices',

            // Reports
            'view_reports',
            'export_reports',

            // Settings
            'manage_settings',
            'manage_roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Admin Role
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions(Permission::all());

        // Create HR Role (limited)
        $hrRole = Role::firstOrCreate(['name' => 'HR']);
        $hrRole->syncPermissions([
            'view_dashboard',
            'view_employees', 'create_employees', 'edit_employees',
            'view_attendance', 'create_attendance', 'edit_attendance', 'sync_attendance',
            'view_payroll', 'create_payroll',
            'view_reports', 'export_reports',
        ]);

        // Create Employee Role
        $employeeRole = Role::firstOrCreate(['name' => 'Employee']);
        $employeeRole->syncPermissions([
            'view_dashboard',
            'view_attendance', 'create_attendance',
            'view_payroll',
        ]);

        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Admin');

        // Create demo employee
        $employee = User::firstOrCreate(
            ['email' => 'employee@mail.com'],
            [
                'name' => 'Demo Employee',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $employee->assignRole('Employee');

        $this->command->info('✅ Roles and permissions created successfully!');
        $this->command->info('📧 Admin: admin@mail.com / password');
        $this->command->info('📧 Employee: employee@mail.com / password');
    }
}
