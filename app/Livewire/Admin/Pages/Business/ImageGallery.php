<?php

namespace App\Livewire\Admin\Pages\Business;

use App\Models\Companies\CompanyImages;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;


class ImageGallery extends Component
{
    public $business_id;
    public $images = [];


    public function imageList()
    {
        return CompanyImages::where('ci_status',1)-> where('ci_cmp_id', $this->business_id)->get()->toArray();
    }

    public function mount($business_id)
    {
        $this->business_id = $business_id;

        $this->images = $this->imageList();
    }

    public function loadImages()
    {
        $this->images = $this->imageList();
    }

    public function removeImage($id)
    {
        $image = CompanyImages::where('ci_id', $id)->first();
        if ($image) {
            $image->ci_status = 3;
            $image->save();    // $image->delete();
            $this->loadImages();
        }
    }

    public function render()
    {
        return view('livewire.admin.pages.business.image-gallery');
    }
}
