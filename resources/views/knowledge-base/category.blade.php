@extends('knowledge-base.layout')

@section('title', $category->name . ' | Knowledge Base | Help Center')

@section('meta_description', $category->description . ' Browse all articles in the ' . $category->name . ' category to learn more about our school management system.')

@section('kb-content')
<!-- Category Header -->
<div class="category-header">
    <div class="breadcrumb">
        <a href="{{ route('knowledge-base.index') }}" class="breadcrumb-link">
            <i class="fas fa-home"></i>
            Knowledge Base
        </a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">{{ $category->name }}</span>
    </div>
    
    <div class="category-hero">
        <div class="category-icon-large">
            <i class="fas {{ $category->icon }}"></i>
        </div>
        <div class="category-info">
            <h1 class="category-title">{{ $category->name }}</h1>
            <p class="category-description">{{ $category->description }}</p>
            <div class="category-stats">
                <span class="article-count">{{ $articles->total() }} {{ Str::plural('article', $articles->total()) }}</span>
            </div>
        </div>
    </div>
</div>

@if($articles->count() > 0)
<!-- Articles List -->
<div class="articles-section">
    <div class="articles-header">
        <h2 class="articles-title">Articles in {{ $category->name }}</h2>
        <div class="articles-meta">
            Showing {{ $articles->firstItem() }}-{{ $articles->lastItem() }} of {{ $articles->total() }} articles
        </div>
    </div>
    
    <div class="articles-list">
        @foreach($articles as $article)
        <article class="article-card">
            <a href="{{ route('knowledge-base.article', [$category->slug, $article->slug]) }}" class="article-card-link">
                <div class="article-card-content">
                    <div class="article-header">
                        <h3 class="article-title">{{ $article->title }}</h3>
                        @if($article->has_youtube_video)
                        <div class="video-indicator">
                            <i class="fas fa-play-circle"></i>
                            <span>Video</span>
                        </div>
                        @endif
                    </div>
                    
                    <p class="article-excerpt">{{ $article->excerpt }}</p>
                    
                    <div class="article-meta">
                        <span class="reading-time">
                            <i class="fas fa-clock"></i>
                            {{ $article->reading_time }}
                        </span>
                        <span class="article-date">
                            <i class="fas fa-calendar"></i>
                            {{ $article->created_at->format('M j, Y') }}
                        </span>
                    </div>
                </div>
                
                <div class="article-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
        </article>
        @endforeach
    </div>
    
    <!-- Pagination -->
    @if($articles->hasPages())
    <div class="pagination-wrapper">
        {{ $articles->links('knowledge-base.pagination') }}
    </div>
    @endif
</div>
@else
<!-- Empty State -->
<div class="empty-state">
    <div class="empty-icon">
        <i class="fas fa-file-alt"></i>
    </div>
    <h3 class="empty-title">No Articles Yet</h3>
    <p class="empty-description">
        There are no articles in the {{ $category->name }} category at the moment. 
        Check back later or browse other categories.
    </p>
    <a href="{{ route('knowledge-base.index') }}" class="btn-primary">
        <i class="fas fa-arrow-left"></i>
        Back to Knowledge Base
    </a>
</div>
@endif

<style>
/* Category Header Styles */
.category-header {
    margin-bottom: 3rem;
}

.breadcrumb {
    display: flex;
    align-items: center;
    margin-bottom: 2rem;
    font-size: 0.9rem;
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

.category-hero {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 2rem;
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--accent-light) 100%);
    border-radius: 16px;
    border: 2px solid var(--primary-color);
}

.category-icon-large {
    width: 80px;
    height: 80px;
    background: var(--primary-color);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    flex-shrink: 0;
}

.category-info {
    flex: 1;
}

.category-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 0.75rem;
}

.category-description {
    font-size: 1.1rem;
    color: var(--text-muted);
    margin-bottom: 1rem;
    line-height: 1.6;
}

.category-stats {
    display: flex;
    align-items: center;
}

.article-count {
    background: var(--primary-color);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.9rem;
}

/* Articles Section */
.articles-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.articles-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-dark);
}

.articles-meta {
    color: var(--text-muted);
    font-size: 0.9rem;
}

/* Article Cards */
.articles-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.article-card {
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.article-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.article-card-link {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
}

.article-card-content {
    flex: 1;
}

.article-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.article-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-dark);
    margin: 0;
    line-height: 1.4;
}

.video-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--accent-light);
    color: var(--accent-color);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    flex-shrink: 0;
}

.article-excerpt {
    color: var(--text-muted);
    margin-bottom: 1rem;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.article-meta {
    display: flex;
    gap: 1.5rem;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.reading-time, .article-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.article-arrow {
    margin-left: 2rem;
    color: var(--text-muted);
    font-size: 1.25rem;
    transition: all 0.3s ease;
}

.article-card:hover .article-arrow {
    color: var(--primary-color);
    transform: translateX(5px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #f8fafc;
    border-radius: 16px;
    border: 2px dashed #e2e8f0;
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: #e2e8f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: var(--text-muted);
    font-size: 2rem;
}

.empty-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 1rem;
}

.empty-description {
    color: var(--text-muted);
    margin-bottom: 2rem;
    line-height: 1.6;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: var(--primary-dark);
    color: white;
    text-decoration: none;
}

.btn-primary i {
    margin-right: 0.5rem;
}

/* Pagination Wrapper */
.pagination-wrapper {
    margin-top: 3rem;
    display: flex;
    justify-content: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .category-hero {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
    }
    
    .category-title {
        font-size: 1.75rem;
    }
    
    .articles-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .article-header {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
    }
    
    .video-indicator {
        align-self: flex-start;
    }
    
    .article-card-link {
        flex-direction: column;
        align-items: stretch;
    }
    
    .article-arrow {
        margin: 1rem 0 0 0;
        text-align: center;
    }
    
    .breadcrumb {
        flex-wrap: wrap;
    }
}
</style>
@endsection