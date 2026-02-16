@php
    $title = $block['props']['title'] ?? 'Important Announcement';
    $text = $block['props']['text'] ?? 'Check out our latest updates!';
    $buttonLabel = $block['props']['buttonLabel'] ?? 'Learn More';
    $buttonUrl = $block['props']['buttonUrl'] ?? '#';
    $style = $block['props']['style'] ?? 'light';
    
    // Icon color comes from block props (CTA background/style remains controlled by variant)
    $iconColor = $block['props']['iconColor'] ?? null;

    // CTA text / button colors (block-level)
    $customTextColor = $block['props']['ctaTextColor'] ?? $site->cta_text_color ?? null;
    $buttonColor = $block['props']['buttonColor'] ?? null;
    $buttonBorderColor = $block['props']['buttonBorderColor'] ?? null;

    // compute hover color: prefer explicit border color, else darken the button color
    if (! function_exists('hex_darken')) {
        function hex_darken($hex, $percent = 10) {
            $hex = ltrim($hex, '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
            }
            $r = max(0, min(255, round(hexdec(substr($hex, 0, 2)) * (100 - $percent) / 100)));
            $g = max(0, min(255, round(hexdec(substr($hex, 2, 2)) * (100 - $percent) / 100)));
            $b = max(0, min(255, round(hexdec(substr($hex, 4, 2)) * (100 - $percent) / 100)));
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        }
    }

    $buttonHover = $buttonBorderColor ?? ($buttonColor ? hex_darken($buttonColor, 10) : ($style === 'light' ? '#1a9585' : '#f5f7f6'));
    
    // Sanitize URL
    $buttonUrl = filter_var($buttonUrl, FILTER_SANITIZE_URL);
    if (!filter_var($buttonUrl, FILTER_VALIDATE_URL)) {
        $buttonUrl = '#';
    }
@endphp

<div class="cta-block cta-{{ $style }}" style="
    margin: 1.5rem 0;
    padding: 1.5rem;
    border-radius: 16px;
    border: 2px solid;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    transition: all 0.3s ease;
    @php
        // Background and border are determined by the 'style' variant only
        $bg = ($style === 'light' ? 'white' : 'linear-gradient(135deg, #22B8A6 0%, #1a9585 100%)');
        $border = ($style === 'light' ? '#e5e7eb' : '#22B8A6');
    @endphp
    background: {!! is_string($bg) && str_starts_with($bg, 'linear') ? $bg : ($bg ?? 'white') !!};
    border-color: {{ $border }};
">
    <!-- Icon -->
    <div style="
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: {{ $style === 'light' ? 'rgba(34, 184, 166, 0.1)' : 'rgba(255, 255, 255, 0.2)' }};
        color: {{ $iconColor ?? ($style === 'light' ? '#22B8A6' : 'white') }};
    ">
        <!-- Megaphone Icon -->
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
        </svg>
    </div>
    
    <!-- Content -->
    <div>
        <h3 style="
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.3;
            color: {{ $customTextColor ?? ($style === 'light' ? '#0B0B0B' : 'white') }};
        ">
            {{ $title }}
        </h3>
        <p style="
            font-size: 0.9375rem;
            line-height: 1.6;
            margin: 0;
            color: {{ $customTextColor ?? ($style === 'light' ? '#6b7280' : 'rgba(255, 255, 255, 0.9)') }};
        ">
            {{ $text }}
        </p>
    </div>
    
    <!-- Button -->
    @if($buttonUrl && $buttonUrl !== '#')
        <a 
            href="{{ $buttonUrl }}" 
            target="_blank"
            rel="noopener noreferrer"
            style="
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.75rem 1.5rem;
                border-radius: 12px;
                font-weight: 600;
                font-size: 0.9375rem;
                text-decoration: none;
                transition: all 0.2s ease;
                background: {{ $buttonColor ?? ($style === 'light' ? '#22B8A6' : 'white') }};
                border-color: {{ $buttonBorderColor ?? $buttonColor ?? ($style === 'light' ? '#22B8A6' : 'white') }};
                color: {{ $buttonColor ? 'white' : ($style === 'light' ? 'white' : '#22B8A6') }};
            "
            onmouseover="this.style.background='{{ $buttonHover }}'; this.style.borderColor='{{ $buttonHover }}'; this.style.transform='translateY(-1px)'; this.style.opacity='0.9';"
            onmouseout="this.style.background='{{ $buttonColor ?? ($style === 'light' ? '#22B8A6' : 'white') }}'; this.style.borderColor='{{ $buttonBorderColor ?? $buttonColor ?? ($style === 'light' ? '#22B8A6' : 'white') }}'; this.style.transform='translateY(0)'; this.style.opacity='1';"
        >
            {{ $buttonLabel }}
            <svg class="w-4 h-4" style="margin-left: 0.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    @endif
</div>
