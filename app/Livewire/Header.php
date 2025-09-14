<?php

namespace App\Livewire;

use Livewire\Component;

class Header extends Component
{
    protected $listeners = ['refreshComponent' => '$refresh'];
    
    public function render()
    {
        return view('livewire.header');
    }
}
