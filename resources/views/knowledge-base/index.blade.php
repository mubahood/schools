@extends('knowledge-base.layout')

@section('title', 'Knowledge Base | Help Center')

@section('meta_description', 'Find answers to common questions and learn how to use our school management system effectively. Browse our comprehensive knowledge base for step-by-step guides and tutorials.')

@section('kb-content')
<!-- Header Section -->
<div class="kb-header">
    <div class="kb-hero">
        <h1 class="kb-title">Knowledge Base</h1>
        <p class="kb-subtitle">Find answers and learn how to use {{ $company->app_name ?? 'our school management system' }} effectively</p>
    </div>
</div>

<!-- Categories Grid -->
<div class="kb-categories-section">
    <h2 class="section-title">Browse by Category</h2>
    <div class="categories-grid">
        @foreach($categories as $category)
        <div class="category-card">
            <a href="{{ route('knowledge-base.category', $category->slug) }}" class="category-link">
                <div class="category-icon">
                    <i class="fas {{ $category->icon }}"></i>
                </div>
                <div class="category-content">
                    <h3 class="category-name">{{ $category->name }}</h3>
                    <p class="category-description">{{ $category->description }}</p>
                    <span class="article-count-badge">{{ $category->articles_count }} {{ Str::plural('article', $category->articles_count) }}</span>
                </div>
                <div class="category-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>

@if($recentArticles->count() > 0)
<!-- Recent Articles Section -->
<div class="kb-recent-section">
    <h2 class="section-title">Recent Articles</h2>
    <div class="recent-articles-grid">
        @foreach($recentArticles as $article)
        <div class="recent-article-card">
            <a href="{{ route('knowledge-base.article', [$article->category->slug, $article->slug]) }}" class="article-link">
                @if($article->has_youtube_video)
                <div class="article-video-indicator">
                    <i class="fas fa-play-circle"></i>
                </div>
                @endif
                <div class="article-content">
                    <span class="article-category">{{ $article->category->name }}</span>
                    <h4 class="article-title">{{ $article->title }}</h4>
                    <p class="article-excerpt">{{ $article->excerpt }}</p>
                    <div class="article-meta">
                        <span class="reading-time">{{ $article->reading_time }}</span>
                        <span class="article-date">{{ $article->created_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Getting Started Section -->
<div class="kb-getting-started">
    <div class="getting-started-card">
        <div class="getting-started-content">
            <h3>New to {{ $company->app_name ?? 'our school management system' }}?</h3>
            <p>Get started with our comprehensive guides and tutorials designed to help you make the most of our platform.</p>
            <div class="getting-started-actions">
                @if($categories->where('slug', 'getting-started')->first())
                <a href="{{ route('knowledge-base.category', 'getting-started') }}" class="btn-primary">
                    <i class="fas fa-rocket"></i>
                    Getting Started Guide
                </a>
                @endif
                <a href="{{ route('knowledge-base.search') }}" class="btn-secondary">
                    <i class="fas fa-search"></i>
                    Search Help
                </a>
            </div>
        </div>
        <div class="getting-started-illustration">
            <i class="fas fa-graduation-cap"></i>
        </div>
    </div>
</div>

<style>
/* Header Styles */
.kb-header {
    text-align: center;
    margin-bottom: 3rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #e2e8f0;
}

.kb-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 1rem;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.kb-subtitle {
    font-size: 1.2rem;
    color: var(--text-muted);
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Section Titles */
.section-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 2rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-light);
}

/* Categories Grid */
.kb-categories-section {
    margin-bottom: 4rem;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

.category-card {
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.category-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.category-link {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
}

.category-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-light);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1.5rem;
    flex-shrink: 0;
}

.category-icon i {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.category-content {
    flex: 1;
}

.category-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.category-description {
    color: var(--text-muted);
    margin-bottom: 0.75rem;
    line-height: 1.5;
}

.article-count-badge {
    display: inline-block;
    background: var(--primary-light);
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.category-arrow {
    margin-left: 1rem;
    color: var(--text-muted);
    transition: all 0.3s ease;
}

.category-card:hover .category-arrow {
    color: var(--primary-color);
    transform: translateX(5px);
}

/* Recent Articles Grid */
.kb-recent-section {
    margin-bottom: 4rem;
}

.recent-articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.recent-article-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
}

.recent-article-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.article-link {
    display: block;
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
}

.article-video-indicator {
    position: absolute;
    top: 1rem;
    right: 1rem;
    color: var(--accent-color);
    font-size: 1.25rem;
}

.article-category {
    display: inline-block;
    background: var(--primary-light);
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.article-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

.article-excerpt {
    color: var(--text-muted);
    margin-bottom: 1rem;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.article-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.reading-time {
    background: #f1f5f9;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

/* Getting Started Section */
.kb-getting-started {
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--accent-light) 100%);
    border-radius: 16px;
    padding: 2rem;
    margin-top: 3rem;
}

.getting-started-card {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.getting-started-content h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 1rem;
}

.getting-started-content p {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.getting-started-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
    display: inline-flex;
    align-items: center;
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

.btn-primary i, .btn-secondary i {
    margin-right: 0.5rem;
}

.getting-started-illustration {
    flex-shrink: 0;
    width: 100px;
    height: 100px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .kb-title {
        font-size: 2rem;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .recent-articles-grid {
        grid-template-columns: 1fr;
    }
    
    .getting-started-card {
        flex-direction: column;
        text-align: center;
    }
    
    .getting-started-actions {
        justify-content: center;
    }
    
    .category-link {
        padding: 1rem;
    }
    
    .category-icon {
        width: 50px;
        height: 50px;
        margin-right: 1rem;
    }
}
</style>
@endsection