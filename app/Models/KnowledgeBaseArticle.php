<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KnowledgeBaseArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'order_number',
        'has_youtube_video',
        'youtube_video_link',
        'is_published',
        'meta_title',
        'meta_description'
    ];

    protected $casts = [
        'has_youtube_video' => 'boolean',
        'is_published' => 'boolean',
        'order_number' => 'integer'
    ];

    /**
     * Boot function to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
                
                // Ensure uniqueness
                $count = 1;
                $originalSlug = $article->slug;
                while (static::where('slug', $article->slug)->exists()) {
                    $article->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($article) {
            if ($article->isDirty('title') && empty($article->slug)) {
                $article->slug = Str::slug($article->title);
                
                // Ensure uniqueness (excluding current record)
                $count = 1;
                $originalSlug = $article->slug;
                while (static::where('slug', $article->slug)->where('id', '!=', $article->id)->exists()) {
                    $article->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Relationship: Article belongs to a category
     */
    public function category()
    {
        return $this->belongsTo(KnowledgeBaseCategory::class, 'category_id');
    }

    /**
     * Scope: Get only published articles
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope: Order by custom order number
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_number', 'asc')->orderBy('title', 'asc');
    }

    /**
     * Scope: Articles with YouTube videos
     */
    public function scopeWithVideo($query)
    {
        return $query->where('has_youtube_video', true);
    }

    /**
     * Get the article's URL
     */
    public function getUrlAttribute()
    {
        return route('knowledge-base.article', [
            'categorySlug' => $this->category->slug,
            'articleSlug' => $this->slug
        ]);
    }

    /**
     * Get the article's admin edit URL
     */
    public function getEditUrlAttribute()
    {
        return admin_url('knowledge-base/articles/' . $this->id . '/edit');
    }

    /**
     * Get YouTube video embed URL
     */
    public function getYoutubeEmbedUrlAttribute()
    {
        if (!$this->has_youtube_video || !$this->youtube_video_link) {
            return null;
        }

        // Extract video ID from various YouTube URL formats
        $videoId = null;
        if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $this->youtube_video_link, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $this->youtube_video_link, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $this->youtube_video_link, $matches)) {
            $videoId = $matches[1];
        }

        return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
    }

    /**
     * Get article excerpt or auto-generate from content
     */
    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Auto-generate excerpt from content
        $content = strip_tags($this->content);
        return Str::limit($content, 150);
    }

    /**
     * Get meta title or fallback to article title
     */
    public function getMetaTitleAttribute($value)
    {
        return $value ?: $this->title;
    }

    /**
     * Get meta description or fallback to excerpt
     */
    public function getMetaDescriptionAttribute($value)
    {
        return $value ?: $this->excerpt;
    }

    /**
     * Get reading time estimate (words per minute)
     */
    public function getReadingTimeAttribute()
    {
        $wordsPerMinute = 200;
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / $wordsPerMinute);
        
        return $minutes . ' min read';
    }

    /**
     * Get previous article in the same category
     */
    public function getPreviousArticle()
    {
        return static::where('category_id', $this->category_id)
            ->where('is_published', true)
            ->where('order_number', '<', $this->order_number)
            ->orderBy('order_number', 'desc')
            ->first();
    }

    /**
     * Get next article in the same category
     */
    public function getNextArticle()
    {
        return static::where('category_id', $this->category_id)
            ->where('is_published', true)
            ->where('order_number', '>', $this->order_number)
            ->orderBy('order_number', 'asc')
            ->first();
    }
}
