<?php

namespace App\Jobs;

use App\Models\DirectMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $message;
    public $tries = 3; // Retry 3 times
    public $timeout = 60; // 60 seconds timeout

    /**
     * Create a new job instance.
     */
    public function __construct(DirectMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $result = DirectMessage::send_message_1($this->message);
            
            if ($result !== 'success') {
                // If it fails, log and potentially retry
                Log::warning("SMS failed for message ID {$this->message->id}: $result");
                throw new \Exception($result);
            }
            
            Log::info("SMS sent successfully for message ID {$this->message->id}");
        } catch (\Exception $e) {
            Log::error("SMS job failed for message ID {$this->message->id}: " . $e->getMessage());
            throw $e; // Will trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        // Update message status after all retries failed
        $this->message->status = 'Failed';
        $this->message->error_message_message = 'Failed after 3 attempts: ' . $exception->getMessage();
        $this->message->save();
        
        Log::error("SMS permanently failed for message ID {$this->message->id}");
    }
}
