@extends('knowledge-base.layout')

@section('title', 'Search Results: ' . $query . ' | Knowledge Base | Help Center')

@section('meta_description', 'Search results for "' . $query . '" in the knowledge base. Find answers and tutorials to help you use our platform effectively.')

@section('kb-content')
<!-- Search Header -->
<div class="search-header">
    <div class="breadcrumb">
        <a href="{{ route('knowledge-base.index') }}" class="breadcrumb-link">
            <i class="fas fa-home"></i>
            Knowledge Base
        </a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">Search Results</span>
    </div>
    
    <div class="search-hero">
        <h1 class="search-title">Search Results</h1>
        <p class="search-subtitle">
            @if($articles->total() > 0)
                Found {{ $articles->total() }} {{ Str::plural('result', $articles->total()) }} for "<strong>{{ $query }}</strong>"
            @else
                No results found for "<strong>{{ $query }}</strong>"
            @endif
        </p>
    </div>
    
    <!-- Enhanced Search Form -->
    <div class="enhanced-search">
        <form action="{{ route('knowledge-base.search') }}" method="GET" class="search-form">
            <div class="search-inputs">
                <div class="search-input-group">
                    <input type="text" 
                           name="q" 
                           value="{{ $query }}" 
                           placeholder="Search knowledge base..." 
                           class="search-input"
                           autofocus>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                </div>
                
                <div class="search-filters">
                    <select name="category" class="category-filter">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ $cat->articles_count }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

@if($articles->count() > 0)
<!-- Search Results -->
<div class="search-results">
    <div class="results-header">
        <h2 class="results-title">Articles</h2>
        <div class="results-meta">
            Showing {{ $articles->firstItem() }}-{{ $articles->lastItem() }} of {{ $articles->total() }} results
        </div>
    </div>
    
    <div class="results-list">
        @foreach($articles as $article)
        <article class="result-card">
            <a href="{{ route('knowledge-base.article', [$article->category->slug, $article->slug]) }}" class="result-link">
                <div class="result-content">
                    <div class="result-header">
                        <span class="result-category">
                            <i class="fas {{ $article->category->icon }}"></i>
                            {{ $article->category->name }}
                        </span>
                        @if($article->has_youtube_video)
                        <div class="video-indicator">
                            <i class="fas fa-play-circle"></i>
                            <span>Video</span>
                        </div>
                        @endif
                    </div>
                    
                    <h3 class="result-title">{{ $article->title }}</h3>
                    <p class="result-excerpt">{{ $article->excerpt }}</p>
                    
                    <div class="result-meta">
                        <span class="reading-time">
                            <i class="fas fa-clock"></i>
                            {{ $article->reading_time }}
                        </span>
                        <span class="result-date">
                            <i class="fas fa-calendar"></i>
                            {{ $article->created_at->format('M j, Y') }}
                        </span>
                    </div>
                </div>
                
                <div class="result-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
        </article>
        @endforeach
    </div>
    
    <!-- Pagination -->
    @if($articles->hasPages())
    <div class="pagination-wrapper">
        {{ $articles->appends(request()->query())->links('knowledge-base.pagination') }}
    </div>
    @endif
</div>
@else
<!-- No Results -->
<div class="no-results">
    <div class="no-results-icon">
        <i class="fas fa-search"></i>
    </div>
    <h3 class="no-results-title">No Results Found</h3>
    <p class="no-results-description">
        We couldn't find any articles matching "<strong>{{ $query }}</strong>". 
        Try using different keywords or browse our categories below.
    </p>
    
    <!-- Search Suggestions -->
    <div class="search-suggestions">
        <h4 class="suggestions-title">Search Suggestions:</h4>
        <ul class="suggestions-list">
            <li>Try using different or more general keywords</li>
            <li>Check your spelling</li>
            <li>Use fewer keywords</li>
            <li>Browse categories to find what you're looking for</li>
        </ul>
    </div>
    
    <!-- Popular Categories -->
    <div class="popular-categories">
        <h4 class="popular-title">Browse Popular Categories:</h4>
        <div class="categories-grid">
            @foreach($categories->take(4) as $category)
            <a href="{{ route('knowledge-base.category', $category->slug) }}" class="category-card">
                <div class="category-icon">
                    <i class="fas {{ $category->icon }}"></i>
                </div>
                <div class="category-info">
                    <h5 class="category-name">{{ $category->name }}</h5>
                    <span class="category-count">{{ $category->articles_count }} articles</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

<style>
/* Search Header */
.search-header {
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

.search-hero {
    text-align: center;
    margin-bottom: 2rem;
}

.search-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 1rem;
}

.search-subtitle {
    font-size: 1.1rem;
    color: var(--text-muted);
    line-height: 1.6;
}

/* Enhanced Search Form */
.enhanced-search {
    background: #f8fafc;
    padding: 2rem;
    border-radius: 16px;
    border: 2px solid #e2e8f0;
    margin-bottom: 3rem;
}

.search-inputs {
    display: flex;
    gap: 1rem;
    align-items: end;
}

.search-input-group {
    flex: 2;
    display: flex;
    gap: 0.5rem;
}

.search-input {
    flex: 1;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    outline: none;
    transition: all 0.3s ease;
}

.search-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1);
}

.search-btn {
    padding: 0.875rem 1.5rem;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.search-btn:hover {
    background: var(--primary-dark);
}

.search-filters {
    flex: 1;
}

.category-filter {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    font-size: 1rem;
    outline: none;
    transition: all 0.3s ease;
}

.category-filter:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1);
}

/* Search Results */
.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.results-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-dark);
}

.results-meta {
    color: var(--text-muted);
    font-size: 0.9rem;
}

/* Result Cards */
.results-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.result-card {
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.result-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.result-link {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
}

.result-content {
    flex: 1;
}

.result-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.result-category {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--primary-light);
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.video-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--accent-light);
    color: var(--accent-color);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    flex-shrink: 0;
}

.result-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

.result-excerpt {
    color: var(--text-muted);
    margin-bottom: 1rem;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.result-meta {
    display: flex;
    gap: 1.5rem;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.reading-time, .result-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.result-arrow {
    margin-left: 2rem;
    color: var(--text-muted);
    font-size: 1.25rem;
    transition: all 0.3s ease;
}

.result-card:hover .result-arrow {
    color: var(--primary-color);
    transform: translateX(5px);
}

/* No Results */
.no-results {
    text-align: center;
    padding: 4rem 2rem;
}

.no-results-icon {
    width: 100px;
    height: 100px;
    background: #e2e8f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    color: var(--text-muted);
    font-size: 2.5rem;
}

.no-results-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 1rem;
}

.no-results-description {
    color: var(--text-muted);
    margin-bottom: 3rem;
    line-height: 1.6;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

/* Search Suggestions */
.search-suggestions {
    background: #f8fafc;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 3rem;
    text-align: left;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.suggestions-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 1rem;
}

.suggestions-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.suggestions-list li {
    padding: 0.5rem 0;
    color: var(--text-muted);
    position: relative;
    padding-left: 1.5rem;
}

.suggestions-list li:before {
    content: "•";
    color: var(--primary-color);
    font-weight: bold;
    position: absolute;
    left: 0;
}

/* Popular Categories */
.popular-categories {
    max-width: 800px;
    margin: 0 auto;
}

.popular-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 2rem;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.category-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
}

.category-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: inherit;
}

.category-icon {
    width: 40px;
    height: 40px;
    background: var(--primary-light);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    flex-shrink: 0;
}

.category-name {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.25rem;
}

.category-count {
    font-size: 0.875rem;
    color: var(--text-muted);
}

/* Pagination */
.pagination-wrapper {
    margin-top: 3rem;
    display: flex;
    justify-content: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .search-inputs {
        flex-direction: column;
        gap: 1rem;
    }
    
    .search-input-group {
        flex-direction: column;
    }
    
    .search-btn {
        justify-content: center;
    }
    
    .results-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .result-header {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
    }
    
    .video-indicator {
        align-self: flex-start;
    }
    
    .result-link {
        flex-direction: column;
        align-items: stretch;
    }
    
    .result-arrow {
        margin: 1rem 0 0 0;
        text-align: center;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
    }
    
    .search-title {
        font-size: 1.75rem;
    }
}
</style>
@endsection