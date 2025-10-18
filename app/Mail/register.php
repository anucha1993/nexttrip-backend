<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class register extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('แจ้งสมัครสมาชิก')
        ->from('mikety-x2@hotmail.com')
        ->to('pipat.pimnnot@gmail.com')
        ->markdown('emails.register')->with([
            // 'name' => $this->data['name'],
            // 'email' => $this->data['email'],
        ]);
    }
}
