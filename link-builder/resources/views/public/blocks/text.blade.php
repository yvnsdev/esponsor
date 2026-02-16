@php
    $title = $block['props']['title'] ?? '';
    $body = $block['props']['body'] ?? $block['props']['content'] ?? 'Your text content here';
    $textAlign = $block['props']['textAlign'] ?? 'left';
    $showDivider = $block['props']['showDivider'] ?? false;
    $isDark = ($theme ?? 'light') === 'dark';
@endphp

<div class="text-block-wrapper py-2">
    <div 
        class="text-block"
        style="text-align: {{ $textAlign }};"
    >
        @if($title)
            <h3 class="text-lg font-bold mb-2" style="color: {{ $isDark ? '#f1f5f9' : '#0B0B0B' }};">{{ $title }}</h3>
        @endif
        <p class="leading-relaxed" style="white-space: pre-wrap; font-size: 0.9375rem; color: {{ $isDark ? '#cbd5e1' : '#374151' }};">{!! nl2br(e($body)) !!}</p>
    </div>
    
    @if($showDivider)
        <hr style="margin-top: 1.5rem; border: none; border-top: 2px solid {{ $isDark ? '#334155' : '#e5e7eb' }}; opacity: 0.6;" />
    @endif
</div>