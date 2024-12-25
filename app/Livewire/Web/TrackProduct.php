<?php

namespace App\Livewire\Web;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrackProduct extends Component
{
    public Product $product;

    public bool $tracking = false;

    private ?User $user = null;

    public function boot()
    {
        $this->user = Auth::user();
    }

    public function mount(Product $product) 
    {
        $this->product = $product;

        if ($this->user && $this->user->isTracking($product)) {
            $this->tracking = true;
        }
    }

    public function track()
    {
        abort_unless(403, Auth::user());

        $this->user->products()->syncWithoutDetaching($this->product);
        $this->tracking = true;
    }

    public function untrack()
    {
        abort_unless(403, Auth::user());

        $this->user->products()->detach($this->product);
        $this->tracking = false;
    }

    public function render()
    {
        return view('livewire.web.track-product');
    }
}
