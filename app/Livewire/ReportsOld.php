<?php

namespace App\Livewire;

use App\Services\ReportsService_old;
use Livewire\Component;

class ReportsOld extends Component
{
    protected $lotReports = [];
    public $item = "";
    public $week = "";

    public function render()
    {
        return view('livewire.reports_old', [
            'lotReports' => $this->lotReports,
        ]);
    }

    // get search query data and send reports data
    public function getSearchQuery()
    {
        $reportsService = new ReportsService_old;

        if ($this->item && $this->week) {
            $this->lotReports = $reportsService->getSearchQuery($this->item, $this->week);
        }
    }
}
