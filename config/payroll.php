<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payroll Configuration
    |--------------------------------------------------------------------------
    */

    'hourly_rate' => env('HOURLY_RATE', 100),
    'overtime_multiplier' => env('OVERTIME_MULTIPLIER', 1.5),
    'tax_rate' => env('TAX_RATE', 0.10),
    'insurance_amount' => env('INSURANCE_AMOUNT', 500),
    'working_hours_per_day' => env('WORKING_HOURS_PER_DAY', 8),
    'working_days_per_week' => env('WORKING_DAYS_PER_WEEK', 5),

    'statuses' => [
        'draft' => 'ড্রাফট',
        'approved' => 'অ্যাপ্রুভড',
        'paid' => 'পেইড',
    ],

    'leave_types' => [
        'sick' => 'সিক লিভ',
        'casual' => 'ক্যাজুয়াল লিভ',
        'annual' => 'বছরব্যাপী লিভ',
        'other' => 'অন্যান্য',
    ],
];
