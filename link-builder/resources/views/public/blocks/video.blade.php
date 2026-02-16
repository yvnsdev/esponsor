@php
    $videoUrl = $block['props']['videoUrl'] ?? '';
    $title = $block['props']['title'] ?? '';
    $aspectRatio = $block['props']['aspectRatio'] ?? '16:9';
    $autoplay = $block['props']['autoplay'] ?? false;
    $showControls = $block['props']['showControls'] ?? true;
    $width = $block['props']['width'] ?? '100';
    
    // Extract YouTube video ID
    $videoId = null;
    if ($videoUrl) {
        // Common YouTube URL patterns
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/',
            '/youtube\.com\/watch\?.*v=([^&\n?#]+)/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $videoUrl, $matches)) {
                $videoId = $matches[1];
                break;
            }
        }
    }
    
    // Calculate padding-bottom for aspect ratio
    $paddingBottoms = [
        '16:9' => '56.25%',
        '4:3' => '75%',
        '1:1' => '100%'
    ];
    $paddingBottom = $paddingBottoms[$aspectRatio] ?? '56.25%';
    
    // Build embed URL
    $embedUrl = null;
    if ($videoId) {
        $params = [
            'rel' => '0',
            'modestbranding' => '1'
        ];
        
        if ($autoplay) {
            $params['autoplay'] = '1';
        }
        
        if (!$showControls) {
            $params['controls'] = '0';
        }
        
        $queryString = http_build_query($params);
        $embedUrl = "https://www.youtube.com/embed/{$videoId}?{$queryString}";
    }
@endphp

<div class="video-block">
    @if($title)
        <h3 class="text-xl font-semibold mb-4 text-center text-gray-900">
            {{ $title }}
        </h3>
    @endif
    
    @if($embedUrl)
        <div class="mx-auto" style="width: {{ $width }}%; max-width: 100%;">
            <div 
                class="relative overflow-hidden rounded-lg shadow-lg"
                style="padding-bottom: {{ $paddingBottom }}; height: 0;"
            >
                <iframe
                    src="{{ $embedUrl }}"
                    title="{{ $title ?: 'YouTube Video' }}"
                    frameborder="0"
                    allowfullscreen
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    loading="lazy"
                    class="absolute top-0 left-0 w-full h-full"
                ></iframe>
            </div>
        </div>
    @else
        <div class="mx-auto" style="width: {{ $width }}%; max-width: 100%;">
            <div 
                class="relative overflow-hidden rounded-lg border-2 border-dashed border-gray-300 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center"
                style="padding-bottom: {{ $paddingBottom }}; height: 0;"
            >
                <div class="absolute inset-0 flex items-center justify-center p-8">
                    <div class="text-center">
                        <div class="text-4xl mb-2">📹</div>
                        <p class="text-gray-600 font-medium">
                            {{ $videoUrl ? 'Invalid YouTube URL' : 'No video URL provided' }}
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Please enter a valid YouTube URL
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>