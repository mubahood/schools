@extends('layouts.modern-public')

@section('content')
<div class="knowledge-base-layout">
    <div class="container-modern">
        <div class="kb-layout-grid">
            <!-- Sidebar -->
            <aside class="kb-sidebar">
                <!-- Search Section -->
                <div class="kb-search-section">
                    <form action="{{ route('knowledge-base.search') }}" method="GET" class="kb-search-form">
                        <div class="search-input-group">
                            <input type="text" 
                                   name="q" 
                                   value="{{ request('q') }}" 
                                   placeholder="Search knowledge base..." 
                                   class="search-input">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Categories Navigation -->
                <nav class="kb-nav">
                    <h3 class="kb-nav-title">Categories</h3>
                    <ul class="kb-nav-list">
                        <li class="kb-nav-item">
                            <a href="{{ route('knowledge-base.index') }}" 
                               class="kb-nav-link {{ request()->routeIs('knowledge-base.index') ? 'active' : '' }}">
                                <i class="fas fa-home"></i>
                                <span>All Categories</span>
                            </a>
                        </li>
                        @foreach($categories as $cat)
                        <li class="kb-nav-item">
                            <a href="{{ route('knowledge-base.category', $cat->slug) }}" 
                               class="kb-nav-link {{ (isset($category) && $category->id == $cat->id) ? 'active' : '' }}">
                                <i class="fas {{ $cat->icon }}"></i>
                                <span>{{ $cat->name }}</span>
                                <span class="article-count">{{ $cat->articles_count }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </nav>

                <!-- Support Links -->
                <div class="kb-support-section">
                    <h4 class="support-title">Need More Help?</h4>
                    <div class="support-links">
                        <a href="mailto:{{ $company->email ?? 'support@schooldynamics.ug' }}" class="support-link">
                            <i class="fas fa-envelope"></i>
                            Contact Support
                        </a>
                        <a href="tel:{{ $company->phone ?? '+256700000000' }}" class="support-link">
                            <i class="fas fa-phone"></i>
                            Call Us
                        </a>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="kb-main-content">
                @yield('kb-content')
            </main>
        </div>
    </div>
</div>

<!-- Knowledge Base Specific CSS -->
<style>
.knowledge-base-layout {
    min-height: 100vh;
    padding: 2rem 0;
    background: linear-gradient(135deg, var(--background-light) 0%, #f8fafc 100%);
}

.kb-layout-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 3rem;
    max-width: 1400px;
    margin: 0 auto;
}

/* Sidebar Styles */
.kb-sidebar {
    background: #ffffff;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.kb-search-section {
    margin-bottom: 2rem;
}

.search-input-group {
    position: relative;
    display: flex;
}

.search-input {
    flex: 1;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9rem;
    outline: none;
    transition: all 0.3s ease;
}

.search-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1);
}

.search-btn {
    margin-left: 0.5rem;
    padding: 0.875rem 1rem;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-btn:hover {
    background: var(--primary-dark);
}

/* Navigation Styles */
.kb-nav-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e2e8f0;
}

.kb-nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.kb-nav-item {
    margin-bottom: 0.5rem;
}

.kb-nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: var(--text-muted);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.kb-nav-link:hover, .kb-nav-link.active {
    background: var(--primary-light);
    color: var(--primary-color);
    text-decoration: none;
}

.kb-nav-link i {
    margin-right: 0.75rem;
    width: 16px;
    text-align: center;
}

.article-count {
    margin-left: auto;
    background: #e2e8f0;
    color: var(--text-muted);
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.kb-nav-link.active .article-count {
    background: var(--primary-color);
    color: white;
}

/* Support Section */
.kb-support-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #e2e8f0;
}

.support-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 1rem;
}

.support-links {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.support-link {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: #f8fafc;
    color: var(--text-muted);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.support-link:hover {
    background: var(--primary-light);
    color: var(--primary-color);
    text-decoration: none;
}

.support-link i {
    margin-right: 0.75rem;
    width: 16px;
    text-align: center;
}

/* Main Content */
.kb-main-content {
    background: #ffffff;
    border-radius: 12px;
    padding: 2.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    min-height: 600px;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .kb-layout-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .kb-sidebar {
        position: static;
        order: 2;
    }
    
    .kb-main-content {
        order: 1;
    }
}

@media (max-width: 768px) {
    .knowledge-base-layout {
        padding: 1rem 0;
    }
    
    .kb-sidebar, .kb-main-content {
        padding: 1.5rem;
    }
    
    .kb-layout-grid {
        gap: 1rem;
    }
}
</style>
@endsection