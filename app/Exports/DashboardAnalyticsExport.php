<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DashboardAnalyticsExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return [
            $this->data
        ];
    }

    public function headings(): array
    {
        return [
            'Period Filter',
            'Total Registered Parents',
            'Active Children Profiles',
            'Active Subscription Plans',
            'Executed Therapy Sessions',
            'Average Progress Percentage',
            'Total Revenue (SAR)',
        ];
    }
}
