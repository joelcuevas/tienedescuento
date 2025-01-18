<?php

namespace App\Livewire\Web;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrackProduct extends Component
{
    public Product $product;

    public string $style;

    public bool $tracking = false;

    private ?User $user = null;

    public function boot()
    {
        abort_unless(403, Auth::user());
        
        $this->user = Auth::user();
    }

    public function mount(Product $product, string $style = 'button')
    {
        $this->product = $product;
        $this->style = $style;

        if ($this->user->isTracking($product)) {
            $this->tracking = true;
        }
    }

    public function toggle()
    {
        if ($this->tracking) {
            $this->user->untrack($this->product);
        } else {
            $this->user->track($this->product);
        }

        $this->tracking = !$this->tracking;
    }

    public function render()
    {
        return view('livewire.web.track-product');
    }
}
