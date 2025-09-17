<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'ip_address',
        'user_agent',
        'status',
        'admin_reply',
        'replied_by'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    public function scopeReplied($query)
    {
        return $query->where('status', 'replied');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Accessors & Mutators
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('M d, Y \a\t g:i A');
    }

    public function getIsNewAttribute()
    {
        return $this->status === 'unread';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'unread' => 'danger',
            'read' => 'warning',
            'replied' => 'success',
            'closed' => 'secondary',
            default => 'primary'
        };
    }

    // Methods
    public function markAsRead()
    {
        $this->update([
            'status' => 'read',
            'read_at' => now()
        ]);
    }

    public function reply($message, $adminId = null)
    {
        $this->update([
            'status' => 'replied',
            'admin_reply' => $message,
            'replied_by' => $adminId
        ]);
    }

    public function close()
    {
        $this->update(['status' => 'closed']);
    }

    // Static methods
    public static function getUnreadCount()
    {
        return static::unread()->count();
    }

    public static function createFromRequest($request)
    {
        return static::create([
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'unread'
        ]);
    }
}
