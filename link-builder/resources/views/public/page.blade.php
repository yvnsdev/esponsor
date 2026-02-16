<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $site->name }}{{ $site->bio ? ' | ' . Str::limit($site->bio, 50) : ' | eSponsor' }}</title>
    <meta name="description" content="{{ Str::limit($site->bio ?? $site->name . ' - Find all my important links and updates in one place.', 160) }}">
    <meta name="keywords" content="{{ $site->name }}, links, profile, social media, {{ $site->slug }}">
    <meta name="author" content="{{ $site->name }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/@' . $site->slug) }}">
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="{{ $site->name }}">
    <meta property="og:description" content="{{ Str::limit($site->bio ?? $site->name . ' - Find all my important links in one place.', 200) }}">
    <meta property="og:type" content="profile">
    <meta property="og:url" content="{{ url('/@' . $site->slug) }}">
    <meta property="og:site_name" content="eSponsor">
    @if($site->avatar_url)
    <meta property="og:image" content="{{ $site->avatar_url }}">
    <meta property="og:image:alt" content="{{ $site->name }} - Profile Picture">
    <meta property="og:image:width" content="400">
    <meta property="og:image:height" content="400">
    @endif
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $site->name }}">
    <meta name="twitter:description" content="{{ Str::limit($site->bio ?? $site->name . ' - Links', 200) }}">
    @if($site->avatar_url)
    <meta name="twitter:image" content="{{ $site->avatar_url }}">
    @endif
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Favicon - use public nav logo if provided -->
    @if($site->nav_logo_url)
        <link rel="icon" href="{{ $site->nav_logo_url }}" type="image/png">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 rx=%2220%22 fill=%22%2322B8A6%22/><text x=%2250%22 y=%2270%22 font-size=%2260%22 text-anchor=%22middle%22 fill=%22white%22 font-weight=%22bold%22>{{ strtoupper(substr($site->name, 0, 1)) }}</text></svg>" type="image/svg+xml">
    @endif
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: {{ $site->theme_mode === 'dark' ? '#0f172a' : '#F5F7F6' }};
            min-height: 100vh;
            color: {{ $site->theme_mode === 'dark' ? '#e2e8f0' : '#0B0B0B' }};
            line-height: 1.6;
            transition: background 0.3s ease, color 0.3s ease;
        }
        
        .page-container {
            max-width: 520px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .page-card {
            background: {{ $site->theme_mode === 'dark' ? '#1e293b' : 'white' }};
            border-radius: 20px;
            box-shadow: 0 2px 8px {{ $site->theme_mode === 'dark' ? 'rgba(0, 0, 0, 0.3)' : 'rgba(11, 11, 11, 0.06)' }};
            overflow: hidden;
            border: 1px solid {{ $site->theme_mode === 'dark' ? '#334155' : '#e5e7eb' }};
            transition: all 0.3s ease;
        }
        
        /* Profile Card Header */
        .profile-header {
            position: relative;
            padding-bottom: 0.25rem;
        }
        
        .header-cover {
            height: 100px;
            @if($site->banner_image_url)
            background-image: url('{{ $site->banner_image_url }}');
            background-size: cover;
            background-position: center;
            @elseif($site->banner_color)
            background: {{ $site->banner_color }};
            @else
            background: linear-gradient(135deg, #22B8A6 0%, #1a9585 100%);
            @endif
            position: relative;
            overflow: hidden;
        }
        
        .header-cover::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
        }
        
        .profile-content {
            position: relative;
            text-align: center;
            padding: 0 1.5rem;
        }
        
        .avatar-container {
            position: relative;
            margin-top: -3.5rem;
            margin-bottom: 0.25rem;
            display: inline-block;
        }
        
        .avatar-ring {
            background: white;
            padding: 5px;
            border-radius: 9999px;
            box-shadow: 0 4px 16px rgba(11, 11, 11, 0.12);
        }
        
        .avatar-image {
            width: 100px;
            height: 100px;
            border-radius: 9999px;
            object-fit: cover;
            background: linear-gradient(135deg, #22B8A6 0%, #1a9585 100%);
            border: 3px solid white;
        }
        
        .avatar-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 9999px;
            background: linear-gradient(135deg, #22B8A6 0%, #1a9585 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            border: 3px solid white;
            box-shadow: 0 4px 16px rgba(34, 184, 166, 0.2);
        }
        
        .site-name {
            font-size: 1.75rem;
            font-weight: 800;
            color: {{ $site->theme_mode === 'dark' ? '#f1f5f9' : '#0B0B0B' }};
            margin-bottom: 0.125rem;
            line-height: 1.2;
        }
        
        .site-bio {
            color: {{ $site->theme_mode === 'dark' ? '#94a3b8' : '#6b7280' }};
            font-size: 0.9375rem;
            line-height: 1.6;
            max-width: 400px;
            margin: 0 auto 1.5rem;
        }
        
        /* Social Icons Row */
        .social-icons-header {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            padding-bottom: 1rem;
        }
        
        .social-icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: {{ $site->theme_mode === 'dark' ? '#334155' : '#F5F7F6' }};
            border: 2px solid {{ $site->theme_mode === 'dark' ? '#475569' : '#e5e7eb' }};
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .social-icon-btn:hover {
            background: {{ $site->social_hover_color ?? '#22B8A6' }};
            border-color: {{ $site->social_hover_color ?? '#22B8A6' }};
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(34, 184, 166, 0.3);
        }
        
        .content-block {
            transition: all 0.2s ease;
        }
        
        .link-button {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .link-button:hover {
            transform: translateY(-2px);
        }
        
        @media (max-width: 640px) {
            .page-container {
                padding: 1.5rem 0.75rem;
            }
            
            .page-card {
                border-radius: 16px;
            }
            
            .site-name {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-card">            <!-- Public Nav -->
            <header class="site-nav" style="padding: .75rem 1rem; display:flex; align-items:center; gap:1rem;">
                <a href="/{{ '@' . $site->slug }}" style="display:flex; align-items:center; gap:.75rem; text-decoration:none;">
                    @if($site->nav_logo_url)
                        <img src="{{ $site->nav_logo_url }}" alt="{{ $site->name }}" style="height:36px; width:auto; object-fit:contain; border-radius:6px;" />
                    @else
                        <div style="height:36px; width:36px; background:#22B8A6; border-radius:6px; display:flex; align-items:center; justify-content:center; color:white; font-weight:700;">{{ strtoupper(substr($site->name, 0, 1)) }}</div>
                    @endif
                    <span style="font-weight:700; color:{{ $site->theme_mode === 'dark' ? '#f1f5f9' : '#0B0B0B' }}">{{ $site->name }}</span>
                </a>
            </header>
            <!-- Profile Card Header -->
            <div class="profile-header">
                <!-- Header Cover -->
                <div class="header-cover">
                    @if($site->banner_image_url || $site->banner_color)
                    @else
                    <!-- Decorative gradient overlay -->
                    @endif
                </div>
                
                <!-- Profile Content -->
                <div class="profile-content">
                    <!-- Avatar -->
                    <div class="avatar-container">
                        @if($site->avatar_url)
                            <div class="avatar-ring">
                                <img 
                                    src="{{ $site->avatar_url }}" 
                                    alt="{{ $site->name }}"
                                    class="avatar-image"
                                    loading="lazy"
                                >
                            </div>
                        @else
                            <div class="avatar-ring">
                                <div class="avatar-placeholder">
                                    {{ strtoupper(substr($site->name, 0, 1)) }}
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Site Info -->
                    <h1 class="site-name">{{ $site->name }}</h1>
                    @if($site->bio)
                        <p class="site-bio">{{ $site->bio }}</p>
                    @endif
                    
                    <!-- Optional Social Icons (can be added later as Site model fields) -->
                    {{-- Example for future implementation:
                    @if($site->social_links)
                        <div class="social-icons-header">
                            @foreach($site->social_links as $social)
                                <a href="{{ $social['url'] }}" class="social-icon-btn" target="_blank" rel="noopener noreferrer">
                                    <svg>...</svg>
                                </a>
                            @endforeach
                        </div>
                    @endif
                    --}}
                </div>
            </div>

            <!-- Page Content -->
            <div class="p-2">
                @if(!$page)
                    <!-- Not Published State -->
                    <div class="text-center py-16">
                        <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-slate-100">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-900 mb-2">Coming Soon</h2>
                        <p class="text-slate-600 text-sm">This page hasn't been published yet.</p>
                    </div>
                @elseif(empty($blocks))
                    <!-- No Content State -->
                    <div class="text-center py-16">
                        <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-slate-100">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-900 mb-2">No Content Yet</h2>
                        <p class="text-slate-600 text-sm">This page is being worked on.</p>
                    </div>
                @else
                    <!-- Render Blocks -->
                    <div class="space-y-6">
                        @foreach($blocks as $block)
                            @if(isset($block['type']))
                                <div class="content-block">
                                    @includeWhen(
                                        view()->exists("public.blocks.{$block['type']}"),
                                        "public.blocks.{$block['type']}",
                                        ['block' => $block, 'theme' => $site->theme_mode ?? 'light', 'site' => $site]
                                    )
                                    
                                    @unless(view()->exists("public.blocks.{$block['type']}"))
                                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                                            <div class="flex items-center">
                                                <div class="w-6 h-6 bg-slate-200 rounded-lg flex items-center justify-center mr-2">
                                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <p class="text-xs font-medium text-slate-700">
                                                    Unsupported block: <code class="bg-slate-200 px-1.5 py-0.5 rounded text-xs">{{ $block['type'] }}</code>
                                                </p>
                                            </div>
                                        </div>
                                    @endunless
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
            
            <!-- Footer -->
            <div class="px-6 pb-4">
                <div class="text-center text-xs border-t pt-3" 
                     style="color: {{ $site->theme_mode === 'dark' ? '#64748b' : '#9ca3af' }}; border-color: {{ $site->theme_mode === 'dark' ? '#334155' : '#e5e7eb' }};">
                    <p>Powered by <span style="color: #22B8A6; font-weight: 600;">eSponsor</span></p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Simple analytics placeholder
        console.log('Public page viewed:', @json($site->slug));
        
        // Lazy load images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
        
        // YouTube player API for better video loading
        function loadYouTubePlayer(videoId, elementId) {
            const iframe = document.getElementById(elementId);
            if (iframe) {
                iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=0&rel=0&modestbranding=1`;
            }
        }
    </script>
</body>
</html>