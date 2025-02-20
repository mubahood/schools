<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $bodyContent;

    /**
     * Create a new message instance.
     *
     * @param string $subjectLine
     * @param string $bodyContent
     */
    public function __construct($subjectLine, $bodyContent)
    {
        $this->subjectLine = $subjectLine;
        $this->bodyContent = $bodyContent;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view(
                'mails.mail-2',
                [
                    'body' => 'Hello, This is a test email',
                    'title' => 'Test Email - Laravel 8',
                ],
            );
    }
}
