<?php

namespace App\Mail;

use App\Models\Checkin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailCheckin extends Mailable implements ShouldQueue
{
    use Queueable;

    public $checkin;

    public $mail;

    public $settings;

    public function __construct(Checkin $checkin, $preview = false)
    {
        $this->checkin  = $checkin;
        $this->mail     = !$preview;
        $this->settings = json_decode(get_settings());
    }

    public function build()
    {
        return $this->subject(__('Checkin Details'))->view('mail.checkin');
    }
}
