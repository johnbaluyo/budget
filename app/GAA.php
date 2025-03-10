<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GAA extends Model
{
    protected $fillable = [
        'item_of_expenditure',
        'object_type',
        'fund_cluster',
        'budget_allocation',
        'remarks',
        'division_id',
        'parent_id',
        'approved_budget_id',
    ];

    protected $table = "gaa";

    public function approvedBudget() //eager loading function
    {
        return $this->belongsTo(ApprovedBudget::class, 'approved_budget_id', 'id');
    }

    public function gaaProjects()
    {
        return $this->hasMany(GAAProject::class, 'gaa_id', 'id');
    }

    public static function getDefaultCategories()
    {
        $default_categories = [
            ['object_type' => 'PS', 'category_name' => 'Civilian Personnel', 'parent_category' => null],
            ['object_type' => 'PS', 'category_name' => 'Permanent Positions', 'parent_category' => 'Civilian Personnel'],
            ['object_type' => 'PS', 'category_name' => 'Basic Salary', 'parent_category' => 'Civilian Personnel'],
            ['object_type' => 'PS', 'category_name' => 'Other Compensation Common to All', 'parent_category' => null],
            ['object_type' => 'PS', 'category_name' => 'Personnel Economic Relief Allowance (PERA)', 'parent_category' => 'Other Compensation Common to All'],
            ['object_type' => 'PS', 'category_name' => 'Representation Allowance', 'parent_category' => 'Other Compensation Common to All'],
            ['object_type' => 'PS', 'category_name' => 'Transportation Allowance', 'parent_category' => 'Other Compensation Common to All'],
            ['object_type' => 'PS', 'category_name' => 'Clothing and Uniform Allowance', 'parent_category' => 'Other Compensation Common to All'],
            ['object_type' => 'PS', 'category_name' => 'Mid-Year Bonus - Civilian', 'parent_category' => 'Other Compensation Common to All'],
            ['object_type' => 'PS', 'category_name' => 'Year End Bonus', 'parent_category' => 'Other Compensation Common to All'],
            ['object_type' => 'PS', 'category_name' => 'Cash Gift', 'parent_category' => 'Other Compensation Common to All'],
            ['object_type' => 'PS', 'category_name' => 'Productivity Enhancement Incentive', 'parent_category' => 'Other Compensation Common to All'],
            ['object_type' => 'PS', 'category_name' => 'Step Increment', 'parent_category' => 'Other Compensation Common to All'],
            ['object_type' => 'PS', 'category_name' => 'Other Benefits', 'parent_category' => 'Civilian Personnel'],
            ['object_type' => 'PS', 'category_name' => 'PAG-IBIG Contributions', 'parent_category' => 'Other Benefits'],
            ['object_type' => 'PS', 'category_name' => 'Philhealth Contributions', 'parent_category' => 'Other Benefits'],
            ['object_type' => 'PS', 'category_name' => 'Employees Compensation Insurance Premiums', 'parent_category' => 'Other Benefits'],
            ['object_type' => 'PS', 'category_name' => 'Loyalty Award - Civilian', 'parent_category' => 'Other Benefits'],
            ['object_type' => 'PS', 'category_name' => 'Non-Permanent Positions', 'parent_category' => null],
            ['object_type' => 'MOOE', 'category_name' => 'Travelling Expenses', 'parent_category' => null],
            ['object_type' => 'MOOE', 'category_name' => 'Training and Scholarship Expenses', 'parent_category' => null],
            ['object_type' => 'MOOE', 'category_name' => 'Supplies and Materials Expenses', 'parent_category' => null],
            ['object_type' => 'MOOE', 'category_name' => 'Utility Expenses', 'parent_category' => null],
            ['object_type' => 'MOOE', 'category_name' => 'Communication Expenses', 'parent_category' => null],
            ['object_type' => 'MOOE', 'category_name' => 'Confidential, Intelligence and Extraordinary Expenses', 'parent_category' => null],
            ['object_type' => 'MOOE', 'category_name' => 'Extraordinary and Miscellaneous Expenses', 'parent_category' => 'Confidential, Intelligence and Extraordinary Expenses'],
            ['object_type' => 'MOOE', 'category_name' => 'Professional Services', 'parent_category' => null],
            ['object_type' => 'MOOE', 'category_name' => 'Repairs and Maintenance', 'parent_category' => null],
            ['object_type' => 'MOOE', 'category_name' => 'Taxes, Insurance Premiums and Other Fees', 'parent_category' => null],
            ['object_type' => 'MOOE', 'category_name' => 'Other Maintenance and Operating Expenses', 'parent_category' => null],
            ['object_type' => 'MOOE', 'category_name' => 'Advertising Expenses', 'parent_category' => 'Other Maintenance and Operating Expenses'],
            ['object_type' => 'MOOE', 'category_name' => 'Printing and Publication Expenses', 'parent_category' => 'Other Maintenance and Operating Expenses'],
            ['object_type' => 'MOOE', 'category_name' => 'Representation Expenses', 'parent_category' => 'Other Maintenance and Operating Expenses'],
            ['object_type' => 'MOOE', 'category_name' => 'Transportation and Delivery Expenses', 'parent_category' => 'Other Maintenance and Operating Expenses'],
            ['object_type' => 'MOOE', 'category_name' => 'Rent/Lease Expenses', 'parent_category' => 'Other Maintenance and Operating Expenses'],
            ['object_type' => 'MOOE', 'category_name' => 'Membership Dues and Contributions to Organizations', 'parent_category' => 'Other Maintenance and Operating Expenses'],
            ['object_type' => 'MOOE', 'category_name' => 'Subscription Expenses', 'parent_category' => 'Other Maintenance and Operating Expenses'],
            ['object_type' => 'MOOE', 'category_name' => 'Other Maintenance and Operating Expenses', 'parent_category' => null],
            ['object_type' => 'CO', 'category_name' => 'Property, Plant and Equipment Outlay', 'parent_category' => null],
            ['object_type' => 'CO', 'category_name' => 'Machinery and Equipment Outlay', 'parent_category' => 'Property, Plant and Equipment Outlay'],
        ];
        return $default_categories;
    }

    public function division()
    {
        return $this->hasOne(Division::class, 'id', 'division_id');
    }
}
