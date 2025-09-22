@extends('knowledge-base.layout')

@extends('knowledge-base.layout')

@section('title', $article->title . ' | Knowledge Base | Help Center')

@section('meta_description', $article->meta_description ?: $article->excerpt)
@section('meta_keywords', $article->meta_keywords ?: ($article->category->name . ', help, tutorial, guide'))

@section('og_type', 'article')
@section('og_title', $article->title)
@section('og_description', $article->meta_description ?: $article->excerpt)
@section('og_image', $article->featured_image ? \App\Models\Utils::img_url($article->featured_image) : ($company && $company->logo ? \App\Models\Utils::img_url($company->logo) : \App\Models\Utils::get_logo()))

@section('twitter_title', $article->title)
@section('twitter_description', $article->meta_description ?: $article->excerpt)

@push('structured-data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "{{ $article->title }}",
    "description": "{{ $article->meta_description ?: $article->excerpt }}",
    "image": "{{ $article->featured_image ? \App\Models\Utils::img_url($article->featured_image) : ($company && $company->logo ? \App\Models\Utils::img_url($company->logo) : \App\Models\Utils::get_logo()) }}",
    "datePublished": "{{ $article->created_at->toISOString() }}",
    "dateModified": "{{ $article->updated_at->toISOString() }}",
    "author": {
        "@type": "Organization",
        "name": "{{ $company->name ?? \App\Models\Utils::company_name() }}"
    },
    "publisher": {
        "@type": "Organization",
        "name": "{{ $company->name ?? \App\Models\Utils::company_name() }}",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ $company && $company->logo ? \App\Models\Utils::img_url($company->logo) : \App\Models\Utils::get_logo() }}"
        }
    },
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ url()->current() }}"
    },
    "articleSection": "{{ $article->category->name }}",
    "wordCount": "{{ str_word_count(strip_tags($article->content)) }}",
    "timeRequired": "PT{{ $article->reading_time }}",
    "url": "{{ url()->current() }}",
    "isPartOf": {
        "@type": "Website",
        "name": "{{ $company->app_name ?? \App\Models\Utils::app_name() }} Knowledge Base",
        "url": "{{ url('/knowledge-base') }}"
    },
    "about": {
        "@type": "Thing",
        "name": "{{ $article->category->name }}",
        "description": "{{ $article->category->description }}"
    }
    @if($article->has_youtube_video && $article->youtube_video_id)
    ,
    "video": {
        "@type": "VideoObject",
        "name": "{{ $article->title }} - Video Tutorial",
        "description": "{{ $article->meta_description ?: $article->excerpt }}",
        "thumbnailUrl": "https://img.youtube.com/vi/{{ $article->youtube_video_id }}/maxresdefault.jpg",
        "uploadDate": "{{ $article->created_at->toISOString() }}",
        "embedUrl": "{{ $article->youtube_embed_url }}",
        "duration": "PT10M"
    }
    @endif
}
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "{{ url('/') }}"
        },
        {
            "@type": "ListItem",
            "position": 2,
            "name": "Knowledge Base",
            "item": "{{ route('knowledge-base.index') }}"
        },
        {
            "@type": "ListItem",
            "position": 3,
            "name": "{{ $article->category->name }}",
            "item": "{{ route('knowledge-base.category', $article->category->slug) }}"
        },
        {
            "@type": "ListItem",
            "position": 4,
            "name": "{{ $article->title }}",
            "item": "{{ url()->current() }}"
        }
    ]
}
</script>
@endpush

@section('kb-content')
<!-- Article Header -->
<div class="article-header">
    <div class="breadcrumb">
        <a href="{{ route('knowledge-base.index') }}" class="breadcrumb-link">
            <i class="fas fa-home"></i>
            Knowledge Base
        </a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('knowledge-base.category', $category->slug) }}" class="breadcrumb-link">
            {{ $category->name }}
        </a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">{{ $article->title }}</span>
    </div>
    
    <div class="article-hero">
        <div class="article-info">
            <span class="article-category">
                <i class="fas {{ $category->icon }}"></i>
                {{ $category->name }}
            </span>
            <h1 class="article-title">{{ $article->title }}</h1>
            <div class="article-meta">
                <span class="reading-time">
                    <i class="fas fa-clock"></i>
                    {{ $article->reading_time }}
                </span>
                <span class="article-date">
                    <i class="fas fa-calendar"></i>
                    {{ $article->created_at->format('F j, Y') }}
                </span>
                @if($article->has_youtube_video)
                <span class="video-badge">
                    <i class="fas fa-play-circle"></i>
                    Includes Video
                </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Article Content -->
<div class="article-content-wrapper">
    @if($article->has_youtube_video && $article->youtube_embed_url)
    <!-- YouTube Video Section -->
    <div class="video-section">
        <div class="video-container">
            <iframe 
                src="{{ $article->youtube_embed_url }}" 
                title="{{ $article->title }} - Video Tutorial"
                frameborder="0" 
                allowfullscreen
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            ></iframe>
        </div>
    </div>
    @endif
    
    <!-- Article Body -->
    <div class="article-body">
        {!! $article->content !!}
    </div>
</div>

<!-- Article Navigation -->
@if($previousArticle || $nextArticle)
<div class="article-navigation">
    <div class="nav-section">
        @if($previousArticle)
        <a href="{{ route('knowledge-base.article', [$category->slug, $previousArticle->slug]) }}" class="nav-link prev-link">
            <div class="nav-direction">
                <i class="fas fa-chevron-left"></i>
                Previous Article
            </div>
            <div class="nav-title">{{ $previousArticle->title }}</div>
        </a>
        @endif
    </div>
    
    <div class="nav-center">
        <a href="{{ route('knowledge-base.category', $category->slug) }}" class="back-to-category">
            <i class="fas fa-th-list"></i>
            Back to {{ $category->name }}
        </a>
    </div>
    
    <div class="nav-section">
        @if($nextArticle)
        <a href="{{ route('knowledge-base.article', [$category->slug, $nextArticle->slug]) }}" class="nav-link next-link">
            <div class="nav-direction">
                Next Article
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-title">{{ $nextArticle->title }}</div>
        </a>
        @endif
    </div>
</div>
@endif

<!-- Helpful Section -->
<div class="helpful-section">
    <div class="helpful-card">
        <h3 class="helpful-title">Was this article helpful?</h3>
        <p class="helpful-description">Let us know if you found this article useful or if you need additional help.</p>
        <div class="helpful-actions">
            <a href="mailto:{{ $company->email ?? 'support@schooldynamics.ug' }}?subject=Feedback on: {{ $article->title }}" class="btn-primary">
                <i class="fas fa-envelope"></i>
                Send Feedback
            </a>
            <a href="{{ route('knowledge-base.search') }}" class="btn-secondary">
                <i class="fas fa-search"></i>
                Search for More Help
            </a>
        </div>
    </div>
</div>

<style>
/* Article Header Styles */
.article-header {
    margin-bottom: 3rem;
}

.breadcrumb {
    display: flex;
    align-items: center;
    margin-bottom: 2rem;
    font-size: 0.9rem;
    flex-wrap: wrap;
}

.breadcrumb-link {
    color: var(--primary-color);
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.breadcrumb-link:hover {
    color: var(--primary-dark);
    text-decoration: none;
}

.breadcrumb-link i {
    margin-right: 0.5rem;
}

.breadcrumb-separator {
    margin: 0 0.75rem;
    color: var(--text-muted);
}

.breadcrumb-current {
    color: var(--text-muted);
    font-weight: 500;
}

.article-hero {
    padding: 2rem 0;
    border-bottom: 2px solid #e2e8f0;
}

.article-category {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--primary-light);
    color: var(--primary-color);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 1rem;
}

.article-title {
    font-size: 2.25rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 1.5rem;
    line-height: 1.3;
}

.article-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    font-size: 0.9rem;
    color: var(--text-muted);
}

.reading-time, .article-date, .video-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.video-badge {
    background: var(--accent-light);
    color: var(--accent-color);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 500;
}

/* Video Section */
.video-section {
    margin-bottom: 3rem;
    background: #f8fafc;
    padding: 2rem;
    border-radius: 16px;
    border: 2px solid #e2e8f0;
}

.video-container {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Article Body */
.article-body {
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--text-dark);
}

.article-body h1, .article-body h2, .article-body h3, .article-body h4, .article-body h5, .article-body h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
    color: var(--text-dark);
}

.article-body h1 {
    font-size: 2rem;
    border-bottom: 2px solid var(--primary-light);
    padding-bottom: 0.5rem;
}

.article-body h2 {
    font-size: 1.75rem;
    color: var(--primary-color);
}

.article-body h3 {
    font-size: 1.5rem;
}

.article-body h4 {
    font-size: 1.25rem;
}

.article-body p {
    margin-bottom: 1.5rem;
}

.article-body ul, .article-body ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.article-body li {
    margin-bottom: 0.5rem;
}

.article-body blockquote {
    background: var(--primary-light);
    border-left: 4px solid var(--primary-color);
    padding: 1rem 1.5rem;
    margin: 2rem 0;
    border-radius: 0 8px 8px 0;
    font-style: italic;
}

.article-body code {
    background: #f1f5f9;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    color: var(--primary-color);
}

.article-body pre {
    background: #1e293b;
    color: #e2e8f0;
    padding: 1.5rem;
    border-radius: 8px;
    overflow-x: auto;
    margin: 2rem 0;
}

.article-body pre code {
    background: none;
    color: inherit;
    padding: 0;
}

.article-body img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 2rem 0;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.article-body table {
    width: 100%;
    border-collapse: collapse;
    margin: 2rem 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.article-body th, .article-body td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.article-body th {
    background: var(--primary-light);
    color: var(--primary-color);
    font-weight: 600;
}

/* Article Navigation */
.article-navigation {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 2rem;
    margin: 4rem 0;
    padding: 2rem;
    background: #f8fafc;
    border-radius: 16px;
    border: 2px solid #e2e8f0;
}

.nav-section {
    display: flex;
}

.nav-section:last-child {
    justify-content: flex-end;
}

.nav-link {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
    text-decoration: none;
    color: var(--text-dark);
    transition: all 0.3s ease;
    max-width: 250px;
}

.nav-link:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: var(--text-dark);
}

.nav-direction {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--primary-color);
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.nav-title {
    font-weight: 600;
    line-height: 1.4;
}

.nav-center {
    display: flex;
    align-items: center;
    justify-content: center;
}

.back-to-category {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--primary-color);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.back-to-category:hover {
    background: var(--primary-dark);
    color: white;
    text-decoration: none;
}

/* Helpful Section */
.helpful-section {
    margin-top: 4rem;
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--accent-light) 100%);
    border-radius: 16px;
    padding: 2rem;
}

.helpful-card {
    text-align: center;
}

.helpful-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 1rem;
}

.helpful-description {
    color: var(--text-muted);
    margin-bottom: 2rem;
    line-height: 1.6;
}

.helpful-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    color: white;
    text-decoration: none;
}

.btn-secondary {
    background: white;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.btn-secondary:hover {
    background: var(--primary-color);
    color: white;
    text-decoration: none;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .article-navigation {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .nav-section {
        justify-content: center;
    }
    
    .nav-link {
        max-width: none;
        width: 100%;
    }
    
    .nav-center {
        order: 3;
    }
}

@media (max-width: 768px) {
    .article-title {
        font-size: 1.75rem;
    }
    
    .article-meta {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .video-section {
        padding: 1rem;
    }
    
    .article-body {
        font-size: 1rem;
    }
    
    .helpful-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-primary, .btn-secondary {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
    
    .breadcrumb {
        font-size: 0.8rem;
    }
    
    .breadcrumb-separator {
        margin: 0 0.5rem;
    }
}
</style>
@endsection