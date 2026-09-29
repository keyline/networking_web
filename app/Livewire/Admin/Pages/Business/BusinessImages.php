<?php

namespace App\Livewire\Admin\Pages\Business;

use Livewire\Component;

class BusinessImages extends Component
{

    public $business_id;

    protected $listeners = ['loadImages' => 'refresh'];

    public function mount($business_id)
    {
        $this->business_id = $business_id;
    }

    public function refresh()
    {
        // Log::info('imageUploaded event received');
    }

    public function render()
    {
        return view('livewire.admin.pages.business.business-images');
    }
}
