<?php

namespace App\Livewire\Dashboard;

use App\Models\Backend\MemVehicle;
use App\Services\Vehicle\MemVehicleService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AddNewVehicle extends Component
{
    public $makeModel;
    public $plate;
    public $vehicleLength = 'STANDARD';

    private $memVehicleService;
    public function boot(MemVehicleService $memVehicleService) {
        $this->memVehicleService = $memVehicleService;
    }

    public function render()
    {
        return view('livewire.dashboard.add-new-vehicle');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'makeModel' => $this->makeModel,
            'plate' => $this->plate,
            'vehicleLength' => $this->vehicleLength,
        ];

        $this->memVehicleService->createVehicle(auth()->id(), $data);

        return redirect()->route('dashboard.vehicles')->with('success', 'The vehicle ' . $data['makeModel'] . ' has been successfully added to your account.');
    }

    public function rules()
    {
        return [
            'makeModel' => ['required', 'max:255'],
            'plate' => [
                'required',
                'max:10',
                Rule::unique(MemVehicle::class)->where(function ($query) {
                    $query->where('owner_id', auth()->user()->id);
                    $query->where('isDeleted', 0);
                    
                    $existingRecords = MemVehicle::where('plate', $this->plate)
                    ->where('owner_id', auth()->user()->id)
                    ->get();
        
                    if ($existingRecords) {
                        foreach ($existingRecords as $existingRecord) {
                            if ($existingRecord->isDeleted == 1) {
                                MemVehicle::where('id', $existingRecord->id)->delete(); 
                                
                            }
                        }
                    }
                }),
            ],
            'vehicleLength' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'makeModel.required' => 'Make and Model is required',
            'plate.required' => 'License Plate is required',
            'plate.unique' => 'This License Plate Already Taken for the same owner.',
            'vehicleLength.required' => 'Vehicle Length is required',
        ];
    }
}
