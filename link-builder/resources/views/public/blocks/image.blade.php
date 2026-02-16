@php
    $imageUrl = $block['props']['imageUrl'] ?? 'https://via.placeholder.com/400x300?text=Your+Image';
    $altText = $block['props']['altText'] ?? 'Image description';
    $caption = $block['props']['caption'] ?? '';
    $width = $block['props']['width'] ?? '100';
    $alignment = $block['props']['alignment'] ?? 'center';
    $borderRadius = $block['props']['borderRadius'] ?? '0';
    $linkUrl = $block['props']['linkUrl'] ?? '';
    
    $alignmentClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right'
    ];
    $alignmentClass = $alignmentClasses[$alignment] ?? 'text-center';
    
    $imageStyles = "
        width: {$width}%;
        max-width: 100%;
        height: auto;
        border-radius: {$borderRadius}px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.2s ease;
    ";
@endphp

<div class="image-block {{ $alignmentClass }} py-2">
    @if($linkUrl)
        <a 
            href="{{ $linkUrl }}" 
            target="_blank" 
            rel="noopener noreferrer"
            class="inline-block transition-transform duration-200 hover:scale-[1.02]"
        >
            <img
                src="{{ $imageUrl }}"
                alt="{{ $altText }}"
                style="{{ $imageStyles }}"
                loading="lazy"
            />
        </a>
    @else
        <img
            src="{{ $imageUrl }}"
            alt="{{ $altText }}"
            style="{{ $imageStyles }}"
            loading="lazy"
        />
    @endif
    
    @if($caption)
        <p class="text-sm text-slate-600 italic mt-2">{{ $caption }}</p>
    @endif
</div>