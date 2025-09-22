<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseArticle;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate XML sitemap for the website
     */
    public function index()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        // Main pages
        $mainPages = [
            [
                'url' => url('/'),
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '1.0'
            ],
            [
                'url' => url('/access-system'),
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.8'
            ],
            [
                'url' => route('knowledge-base.index'),
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.9'
            ]
        ];

        // Add onboarding pages
        for ($i = 1; $i <= 5; $i++) {
            $mainPages[] = [
                'url' => url("onboarding/step{$i}"),
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.6'
            ];
        }

        // Add main pages to sitemap
        foreach ($mainPages as $page) {
            $sitemap .= $this->addUrlToSitemap($page);
        }

        // Knowledge Base Categories
        $categories = KnowledgeBaseCategory::active()->get();
        foreach ($categories as $category) {
            $sitemap .= $this->addUrlToSitemap([
                'url' => route('knowledge-base.category', $category->slug),
                'lastmod' => $category->updated_at->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.7'
            ]);
        }

        // Knowledge Base Articles
        $articles = KnowledgeBaseArticle::published()->with('category')->get();
        foreach ($articles as $article) {
            $sitemap .= $this->addUrlToSitemap([
                'url' => route('knowledge-base.article', [$article->category->slug, $article->slug]),
                'lastmod' => $article->updated_at->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.6'
            ]);
        }

        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
    }

    /**
     * Add URL entry to sitemap XML
     */
    private function addUrlToSitemap($page)
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($page['url'], ENT_XML1, 'UTF-8') . "</loc>\n";
        $xml .= "    <lastmod>" . $page['lastmod'] . "</lastmod>\n";
        $xml .= "    <changefreq>" . $page['changefreq'] . "</changefreq>\n";
        $xml .= "    <priority>" . $page['priority'] . "</priority>\n";
        $xml .= "  </url>\n";
        
        return $xml;
    }

    /**
     * Generate robots.txt file
     */
    public function robots()
    {
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /auth/\n";
        $robots .= "Disallow: /api/\n";
        $robots .= "Disallow: /temp-import\n";
        $robots .= "Disallow: /clear\n";
        $robots .= "Disallow: /reset-marks\n";
        $robots .= "Disallow: /process-*\n";
        $robots .= "Disallow: /bill-afresh\n";
        $robots .= "Disallow: /reset-a-school\n";
        $robots .= "Disallow: /import-*\n";
        $robots .= "Disallow: /fees-data-import*\n";
        $robots .= "Disallow: /generate-school-report\n";
        $robots .= "Disallow: /send-report-card\n";
        $robots .= "Disallow: /termly-report\n";
        $robots .= "Disallow: /test-*\n";
        $robots .= "Disallow: /preview-*\n";
        $robots .= "\n";
        $robots .= "# Allow specific public areas\n";
        $robots .= "Allow: /knowledge-base/\n";
        $robots .= "Allow: /access-system\n";
        $robots .= "Allow: /onboarding/\n";
        $robots .= "\n";
        $robots .= "# Sitemap location\n";
        $robots .= "Sitemap: " . url('/sitemap.xml') . "\n";
        $robots .= "\n";
        $robots .= "# Crawl delay (optional)\n";
        $robots .= "Crawl-delay: 1\n";

        return response($robots, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Cache-Control', 'public, max-age=86400'); // Cache for 24 hours
    }
}