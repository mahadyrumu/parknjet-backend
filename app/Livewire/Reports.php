<?php

namespace App\Livewire;

use App\Services\ReportsService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Reports extends Component
{
    protected $lotReports = [];
    public $report = "";
    public $start_date = "";
    public $last_date = "";

    public function render()
    {
        return view('livewire.reports', [
            'lotReports' => $this->lotReports,
        ]);
    }

    // get search query data and send reports data
    public function getSearchQuery()
    {
        Log::debug("report : ".$this->report);
        Log::debug("start_date : ".$this->start_date);
        Log::debug("last_date : ".$this->last_date);
        $reportsService = new ReportsService;

        if ($this->report && $this->start_date && $this->last_date) {
            $this->lotReports = $reportsService->getSearchQuery($this->report, $this->start_date, $this->last_date);
        }
    }
}
