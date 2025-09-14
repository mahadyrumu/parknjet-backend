<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Calendar extends Component
{
    public $lotDetails;

    protected $listeners = [
        'get-selectedDate'      => 'setLotDetails',
        'get-updatedLotDetails' => 'getUpdatedLotDetails',
    ];

    public function render()
    {
        return view('livewire.calendar');
    }

    // receive current or selected date from frontend to send lot details
    // if lot details is available for current or selected date it will send that otherwise it will send default lot details
    public function setLotDetails($payload)
    {
        $date = $payload;
        $file = Storage::exists('/public/LOT_JSON/' . $date . '.json');

        if ($file) {
            $this->lotDetails = json_decode(Storage::disk('public')->get('/LOT_JSON/' . $date . '.json'), true);
        } else {
            $this->lotDetails = json_decode(Storage::disk('public')->get('/LOT_JSON/default_lot_details.json'), true);
        }
        $this->dispatch('lot-details', $this->lotDetails);
    }

    // get updated lot details and create json file
    public function getUpdatedLotDetails($payload)
    {
        $updatedLotDetails = json_decode($payload, true);
        $jsonFile = '/LOT_JSON/' . $updatedLotDetails['selectedDate'] . '.json';
        Storage::disk('public')->put($jsonFile, json_encode($updatedLotDetails, JSON_PRETTY_PRINT));
    }
}
