<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseArticle;
use App\Models\Utils;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    /**
     * Display the knowledge base home page with categories
     */
    public function index()
    {
        $categories = KnowledgeBaseCategory::active()
            ->ordered()
            ->withCount('articles')
            ->get();

        $recentArticles = KnowledgeBaseArticle::published()
            ->with('category')
            ->ordered()
            ->limit(6)
            ->get();

        // Get company data for consistent branding
        $company = Utils::company();

        return view('knowledge-base.index', compact('categories', 'recentArticles', 'company'));
    }

    /**
     * Display articles in a specific category
     */
    public function category($categorySlug)
    {
        $category = KnowledgeBaseCategory::where('slug', $categorySlug)
            ->active()
            ->firstOrFail();

        $articles = KnowledgeBaseArticle::where('category_id', $category->id)
            ->published()
            ->ordered()
            ->paginate(15);

        $categories = KnowledgeBaseCategory::active()
            ->ordered()
            ->withCount('articles')
            ->get();

        // Get company data for consistent branding
        $company = Utils::company();

        return view('knowledge-base.category', compact('category', 'articles', 'categories', 'company'));
    }

    /**
     * Display a specific article
     */
    public function article($categorySlug, $articleSlug)
    {
        $category = KnowledgeBaseCategory::where('slug', $categorySlug)
            ->active()
            ->firstOrFail();

        $article = KnowledgeBaseArticle::where('slug', $articleSlug)
            ->where('category_id', $category->id)
            ->published()
            ->firstOrFail();

        $categories = KnowledgeBaseCategory::active()
            ->ordered()
            ->withCount('articles')
            ->get();

        // Get navigation articles
        $previousArticle = $article->getPreviousArticle();
        $nextArticle = $article->getNextArticle();

        // Get company data for consistent branding
        $company = Utils::company();

        return view('knowledge-base.article', compact(
            'article', 
            'category', 
            'categories', 
            'previousArticle', 
            'nextArticle',
            'company'
        ));
    }

    /**
     * Search knowledge base articles
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $categoryId = $request->get('category');

        if (empty($query)) {
            return redirect()->route('knowledge-base.index');
        }

        $articlesQuery = KnowledgeBaseArticle::published()
            ->with('category')
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%");
            });

        if ($categoryId) {
            $articlesQuery->where('category_id', $categoryId);
        }

        $articles = $articlesQuery->ordered()->paginate(15);

        $categories = KnowledgeBaseCategory::active()
            ->ordered()
            ->withCount('articles')
            ->get();

        // Get company data for consistent branding
        $company = Utils::company();

        return view('knowledge-base.search', compact('articles', 'categories', 'query', 'categoryId', 'company'));
    }
}