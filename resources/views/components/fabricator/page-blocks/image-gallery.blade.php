@aware(['page'])

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 my-6">
    @if(isset($images) && is_array($images))
        @foreach($images as $image)
            <div class="aspect-square overflow-hidden rounded-lg">
                <img src="{{ Storage::url($image) }}"
                     alt="Gallery Image"
                     class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
            </div>
        @endforeach
    @endif
</div>
