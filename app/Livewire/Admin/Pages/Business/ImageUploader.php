<?php

namespace App\Livewire\Admin\Pages\Business;

use App\Models\Companies\CompanyImages;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;


class ImageUploader extends Component
{
    use WithFileUploads;

    public $newImages = [];
    public $business_id;
    protected $uploadPath = 'public/upload/gallery/';

    public function mount($business_id)
    {
        $this->business_id = $business_id;
    }

    public function uploadImages()
    {
        $this->validate([
            'newImages.*' => 'image|max:2048', // Adjust rules as needed
        ]);

        foreach ($this->newImages as $image) {
            // Generate a unique name for each image
            $name = time() . $this->business_id . '.' . $image->getClientOriginalExtension();
            // Store the image in the 'business-images' folder using the generated name
            $image->storeAs($this->uploadPath, $name);

            // Save to database with the business id and stored image path
            CompanyImages::create([
                'ci_cmp_id'       => $this->business_id,
                'ci_image_name'   => $name,
            ]);
        }

        // Clear the new images array after successful upload
        $this->reset('newImages');
        // Notify parent/gallery to refresh
        $this->dispatch('loadImages', business_id: $this->business_id);
    }

    public function removePreview($index)
    {
        unset($this->newImages[$index]);
        $this->newImages = array_values($this->newImages);
    }


    public function render()
    {
        return view('livewire.admin.pages.business.image-uploader');
    }
}
