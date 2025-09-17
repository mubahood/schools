<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\SupportMessage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Utils;

class SupportController extends Controller
{
    /**
     * Generate CAPTCHA image with random numbers
     */
    public function generateCaptcha()
    {
        // Check if GD extension is available
        if (!extension_loaded('gd')) {
            // Fallback: return simple text-based CAPTCHA
            $captchaText = rand(1000, 9999);
            Session::put('captcha_text', $captchaText);
            
            header('Content-Type: text/plain');
            echo "CAPTCHA: " . $captchaText;
            return;
        }
        
        // Generate random 4-digit number
        $captchaText = rand(1000, 9999);
        
        // Store in session for validation
        Session::put('captcha_text', $captchaText);
        
        // Create image
        $width = 120;
        $height = 40;
        $image = imagecreate($width, $height);
        
        // Colors
        $background = imagecolorallocate($image, 240, 240, 240);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        $lineColor = imagecolorallocate($image, 200, 200, 200);
        
        // Add noise lines
        for ($i = 0; $i < 5; $i++) {
            imageline($image, rand(0, $width), rand(0, $height), 
                     rand(0, $width), rand(0, $height), $lineColor);
        }
        
        // Add text with some randomization
        $font = 5; // Built-in font
        $x = ($width - strlen($captchaText) * 10) / 2 + rand(-5, 5);
        $y = ($height - 15) / 2 + rand(-3, 3);
        
        imagestring($image, $font, $x, $y, $captchaText, $textColor);
        
        // Add some dots for extra noise
        for ($i = 0; $i < 50; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $lineColor);
        }
        
        // Capture the image as a string
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        imagedestroy($image);
        
        // Return Laravel response with proper headers
        return response($imageData, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
    
    /**
     * Handle support form submission
     */
    public function submitSupportForm(Request $request)
    {
        // Validate the form data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'captcha' => 'required|numeric'
        ], [
            'name.required' => 'Please enter your full name.',
            'email.required' => 'Please enter a valid email address.',
            'email.email' => 'Please enter a valid email address.',
            'subject.required' => 'Please select a subject.',
            'message.required' => 'Please enter your message.',
            'message.max' => 'Message cannot exceed 5000 characters.',
            'captcha.required' => 'Please enter the CAPTCHA code.',
            'captcha.numeric' => 'CAPTCHA must be a number.'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Verify CAPTCHA
        $sessionCaptcha = Session::get('captcha_text');
        
        if (!$sessionCaptcha || (string)$request->captcha !== (string)$sessionCaptcha) {
            return redirect()->back()
                ->withErrors(['captcha' => 'Invalid CAPTCHA code. Please try again.'])
                ->withInput();
        }
        
        // Clear CAPTCHA from session
        Session::forget('captcha_text');
        
        try {
            // Create support message
            $supportMessage = SupportMessage::createFromRequest($request);
            
            // Send notification email to admin
            $this->sendAdminNotification($supportMessage);
            
            // Send confirmation email to user
            $this->sendUserConfirmation($supportMessage);
            
            return redirect()->back()->with('status', 
                'Thank you for your message! We have received your inquiry and will get back to you within 24 hours.');
                
        } catch (\Exception $e) {
            Log::error('Support form submission error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['general' => 'An error occurred while processing your request. Please try again.'])
                ->withInput();
        }
    }
    
    /**
     * Send notification email to admin
     */
    private function sendAdminNotification($supportMessage)
    {
        try {
            $adminEmail = Utils::email();
            $data = [
                'name' => $supportMessage->name,
                'email' => $supportMessage->email,
                'subject' => $supportMessage->subject,
                'message' => $supportMessage->message,
                'ip_address' => $supportMessage->ip_address,
                'created_at' => $supportMessage->formatted_date
            ];
            
            Mail::send('emails.support-admin-notification', $data, function ($m) use ($adminEmail, $supportMessage) {
                $m->to($adminEmail, 'Support Team')
                  ->subject('New Support Message: ' . $supportMessage->subject);
            });
            
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification: ' . $e->getMessage());
        }
    }
    
    /**
     * Send confirmation email to user
     */
    private function sendUserConfirmation($supportMessage)
    {
        try {
            $data = [
                'name' => $supportMessage->name,
                'subject' => $supportMessage->subject,
                'message' => $supportMessage->message,
                'ticket_id' => $supportMessage->id,
                'created_at' => $supportMessage->formatted_date
            ];
            
            Mail::send('emails.support-user-confirmation', $data, function ($m) use ($supportMessage) {
                $m->to($supportMessage->email, $supportMessage->name)
                  ->subject('We received your message - Ticket #' . $supportMessage->id);
            });
            
        } catch (\Exception $e) {
            Log::error('Failed to send user confirmation: ' . $e->getMessage());
        }
    }
    
    /**
     * Get all support messages for admin
     */
    public function adminIndex()
    {
        $messages = SupportMessage::recent()->paginate(20);
        $unreadCount = SupportMessage::getUnreadCount();
        
        return view('admin.support.index', compact('messages', 'unreadCount'));
    }
    
    /**
     * Show specific support message
     */
    public function adminShow($id)
    {
        $message = SupportMessage::findOrFail($id);
        
        // Mark as read if it's unread
        if ($message->is_new) {
            $message->markAsRead();
        }
        
        return view('admin.support.show', compact('message'));
    }
    
    /**
     * Reply to support message
     */
    public function adminReply(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reply' => 'required|string|max:5000'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        
        $message = SupportMessage::findOrFail($id);
        $message->reply($request->reply, auth()->id());
        
        // Send reply email to user
        $this->sendReplyEmail($message, $request->reply);
        
        return redirect()->back()->with('success', 'Reply sent successfully!');
    }
    
    /**
     * Send reply email to user
     */
    private function sendReplyEmail($supportMessage, $reply)
    {
        try {
            $data = [
                'name' => $supportMessage->name,
                'original_subject' => $supportMessage->subject,
                'original_message' => $supportMessage->message,
                'reply' => $reply,
                'ticket_id' => $supportMessage->id
            ];
            
            Mail::send('emails.support-reply', $data, function ($m) use ($supportMessage) {
                $m->to($supportMessage->email, $supportMessage->name)
                  ->subject('Reply to your support request - Ticket #' . $supportMessage->id);
            });
            
        } catch (\Exception $e) {
            Log::error('Failed to send reply email: ' . $e->getMessage());
        }
    }
}