<div x-data>

    <style>
        .gallery-img {
            width: 100%;
            height: auto;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.3s ease-in-out;
        }

        .gallery-item:hover .gallery-img {
            transform: scale(1.05);
        }

        .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(255, 0, 0, 0.7);
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .remove-btn:hover {
            background-color: #ff1a1a;
        }
    </style>


    <form wire:submit.prevent="uploadImages" enctype="multipart/form-data">
        <div class="row mb-3">
            <label for="gallery_images" class="col-md-4 col-lg-3 col-form-label">Upload
                Images</label>
            <div class="col-md-8 col-lg-9">


                <input type="file" name="gallery_images[]" class="form-control" id="gallery_images"
                    wire:model="newImages" multiple>

                <small class="text-info">* Only JPG, JPEG, PNG files are allowed</small>
            </div>
        </div>

        <!-- Loading Indicator (Livewire handles file uploads asynchronously) -->
        <div wire:loading wire:target="newImages">Uploading...</div>

        @error('newImages.*')
            <span class="error">{{ $message }}</span>
        @enderror
        <br>
        <!-- Preview Newly Selected Images -->
        @if ($newImages)
            <div class="container mt-4">
                <h2 class="mb-4 text-center">Preview</h2>
                <div class="row row-cols-2 row-cols-md-4 g-4">
                    @foreach ($newImages as $index => $image)
                        <!-- Image -->
                        <div class="col">
                            <div class="position-relative">
                                <img src="{{ $image->temporaryUrl() }}" class="gallery-img" alt="Preview">
                                <button type="button" class="remove-btn"
                                    wire:click="removePreview({{ $index }})">X</button>
                            </div>
                        </div>
                        <!-- Repeat similar blocks for additional images -->
                    @endforeach

                </div>
            </div>


        @endif
        <br>
        {{-- <button type="submit">Upload Images</button> --}}



        <div class="text-center">

            {{-- <button type="submit" class="btn btn-primary">Save Gallery</button> --}}


            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="uploadImages">
                <!-- Content visible when not loading -->
                <span wire:loading.remove wire:target="uploadImages">Save Gallery Images</span>
                <!-- Content visible when loading -->
                <span wire:loading wire:target="uploadImages">
                    <i class="fas fa-spinner fa-spin"></i> Saving...
                </span>
            </button>

        </div>
    </form>


</div>
