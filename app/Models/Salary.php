<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    protected $fillable = [
        'employee_id', 'name',
        'gross_salary',
        'basic_salary', 'house_rent',
        'medical_allowance', 'conveyance', 'food_allowance',
        'total_gross_salary', 'bonus', 'bonus_note',
        'is_active',
    ];

    protected $casts = [
        'gross_salary'        => 'float',
        'basic_salary'        => 'float',
        'house_rent'          => 'float',
        'medical_allowance'   => 'float',
        'conveyance'          => 'float',
        'food_allowance'      => 'float',
        'total_gross_salary'  => 'float',
        'bonus'               => 'float',
        'is_active'           => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /* ============================================================
     |  Computation — the Bangladesh salary formula
     * ============================================================ */

    /**
     * Compute all derived columns from gross_salary + allowances.
     *
     *   Basic       = (Gross − (Medical + Conveyance + Food)) / 1.5
     *   House Rent  = 50% × Basic
     *   Total Gross = Basic + House Rent + Medical + Conveyance + Food
     *   Bonus       = caller-supplied (default 0)
     */
    public static function compute(
        float $gross,
        float $medical   = 750,
        float $conveyance = 450,
        float $food      = 1250,
        float $bonus     = 0
    ): array {
        $allowanceSum = $medical + $conveyance + $food;
        $baseForBasic = max(0, $gross - $allowanceSum);

        $basic       = round($baseForBasic / 1.5, 2);
        $houseRent   = round($basic * 0.5, 2);
        $totalGross  = round($basic + $houseRent + $medical + $conveyance + $food, 2);

        return [
            'basic_salary'       => $basic,
            'house_rent'         => $houseRent,
            'medical_allowance'  => $medical,
            'conveyance'         => $conveyance,
            'food_allowance'     => $food,
            'total_gross_salary' => $totalGross,
            'bonus'              => $bonus,
        ];
    }

    /**
     * Fill the model's computed columns from gross_salary + allowances.
     */
    public function recalculate(): void
    {
        $computed = self::compute(
            $this->gross_salary,
            $this->medical_allowance,
            $this->conveyance,
            $this->food_allowance,
            $this->bonus
        );

        $this->fill($computed);
    }
}
