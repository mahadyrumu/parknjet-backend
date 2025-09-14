<?php

namespace App\Livewire\Dashboard;

use App\Services\Driver\MemDriverService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Driver extends Component
{
    private $memDriverService;
    public function boot(MemDriverService $memDriverService)
    {
        $this->memDriverService = $memDriverService;
    }

    public $full_name, $email, $phone;
    public $createMode = false;
    public $isLoading = false;
    public $drivers = [];
    public $editIndex = null;

    protected $listeners = [
        'deleteDriver' => 'destroy'
    ];

    public function rules()
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'full_name.required' => 'Full name is required',
            'phone.required' => 'Phone numbmer is required',
            'email.required' => 'Email is required',
        ];
    }

    public function render()
    {
        $this->drivers = $this->memDriverService->getDriver(auth()->id())
            ->where('isDeleted', 0)
            ->orderBy('id', 'desc')
            ->get()->toArray();

        // return view('livewire.dashboard.driver.index')->layout('layouts.dashboard', ['title' => 'Dashboard']);
        return view('livewire.dashboard.driver.index', ['drivers' => $this->drivers])->extends('components.layouts.app')
            ->section('content');
    }

    public function enableCreateMode()
    {
        $this->createMode = true;
    }

    public function cancel()
    { 
        $this->createMode = false;
        $this->resetInputs();
        $this->editIndex = null;
    }

    public function resetInputs()
    {
        $this->full_name = "";
        $this->email = "";
        $this->phone = "";
    }

    function store()
    {
        $this->validate();
        try {
            $driver = [];
            $driver['full_name'] = $this->full_name;
            $driver['email'] = $this->email;
            $driver['phone'] = $this->phone;

            $this->memDriverService->createDriver(auth()->id(), $driver);
            $this->cancel();

            return redirect()->back()->with('success', 'Driver has been successfully added.');
        } catch (\Throwable $th) {
            Log::error("Error: " . $th->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while creating driver!!');
        }
    }

    public function edit($index)
    {
        $this->editIndex = $index;
    }

    public function update($index)
    {
        $this->validate(
            [
                'drivers.*.full_name' => 'required',
                'drivers.*.phone' => 'required',
                'drivers.*.email' => 'required|email',
            ],
            [
                'drivers.*.full_name.required' => 'Full name is required',
                'drivers.*.phone.required' => 'Phone number is required',
                'drivers.*.email.required' => 'Email is required',
            ]
        );

        try {
            $driver = $this->drivers[$index];

            if ($driver) {
                $this->memDriverService->updateDriver(auth()->id(), $driver);
            }

            $this->cancel();
            return redirect()->back()->with('success', 'Driver has been successfully updated.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something goes wrong while updating driver!!');
        }
    }
    public function destroy($id)
    {
        $this->isLoading = true;
        try {
            $driver = $this->memDriverService->getDriver(auth()->id())
                ->Where('id', $id)
                ->first();

            if ($driver) {
                $driver = $this->memDriverService->deleteDriver(auth()->id(), $driver);
            }
            $this->isLoading = false;
            redirect()->back()->with('success', "The driver has been successfully deleted.");
        } catch (\Exception $e) {
            $this->isLoading = false;
            redirect()->back()->with('error', "Unable to delete the driver. Please try again.");
        }
    }
}

