<?php

namespace App\Livewire\Web;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.web')]
class ShowHome extends Component
{
    public function render()
    {
        return view('livewire.web.show-home');
    }
}
