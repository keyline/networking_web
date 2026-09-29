<div>
    <form wire:submit.prevent="save" enctype="multipart/form-data">
        @csrf
        <!-- Success Message -->
        <div x-data="{ message: '', show: false }" x-show="show" x-init="$wire.on('successMessage', (msg) => {
            message = msg;
            show = true;
            setTimeout(() => show = false, 3000);
        })" class="alert alert-success" x-transition>
            <span x-text="message"></span>
        </div>


        <!-- Facebook -->
        <div class="row mb-3">
            <label for="facebook_link" class="col-md-4 col-lg-3 col-form-label">Facebook</label>
            <div class="col-md-8 col-lg-9">
                <input type="url" wire:model.defer="facebook_link" class="form-control" id="facebook_link">
                @error('facebook_link')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Twitter -->
        <div class="row mb-3">
            <label for="twitter_link" class="col-md-4 col-lg-3 col-form-label">Twitter</label>
            <div class="col-md-8 col-lg-9">
                <input type="url" wire:model.defer="twitter_link" class="form-control" id="twitter_link">
                @error('twitter_link')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Instagram -->
        <div class="row mb-3">
            <label for="instagram_link" class="col-md-4 col-lg-3 col-form-label">Instagram</label>
            <div class="col-md-8 col-lg-9">
                <input type="url" wire:model.defer="instagram_link" class="form-control" id="instagram_link">
                @error('instagram_link')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- LinkedIn -->
        <div class="row mb-3">
            <label for="linkedin_link" class="col-md-4 col-lg-3 col-form-label">LinkedIn</label>
            <div class="col-md-8 col-lg-9">
                <input type="url" wire:model.defer="linkedin_link" class="form-control" id="linkedin_link">
                @error('linkedin_link')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                <!-- Content visible when not loading -->
                <span wire:loading.remove wire:target="save">Save Social Links</span>
                <!-- Content visible when loading -->
                <span wire:loading wire:target="save">
                    <i class="fas fa-spinner fa-spin"></i> Saving...
                </span>
            </button>
        </div>
    </form>
</div>
