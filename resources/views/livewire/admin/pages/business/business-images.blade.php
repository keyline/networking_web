<div>
    <h2>Manage Images</h2>

    <!-- Uploader Component -->
    <livewire:admin.pages.business.image-uploader :business_id="$business_id" />

    <!-- Gallery Component -->
    <livewire:admin.pages.business.image-gallery :business_id="$business_id" :wire:key="'image-gallery-' . now()->timestamp" />
</div>

</div>
