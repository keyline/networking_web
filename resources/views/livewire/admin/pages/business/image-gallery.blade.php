<div>
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

<div class="container mt-4">
    <h2 class="mb-4 text-center">Image Gallery</h2>
    <div class="row row-cols-2 row-cols-md-4 g-4">
        @foreach ($images as $img)
            <!-- Image -->
            <div class="col">
                <div class="position-relative">
                    <img src="{{ asset('/public/storage/upload/gallery/' . $img['ci_image_name']) }}"
                        class="gallery-img" alt="Quill Pen Logo">
                    <button type="button" class="remove-btn" wire:click="removeImage({{ $img['ci_id'] }})">X</button>
                </div>
            </div>
            <!-- Repeat similar blocks for additional images -->
        @endforeach

    </div>
</div>


</div>

