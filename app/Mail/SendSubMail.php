<?php
namespace App\Mail;

use App\Models\Ad;
use App\Models\Contact;
use App\Models\Subscripe;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendSubMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sub;

    public function __construct(Subscripe $sub)
    {
        $this->sub = $sub;
    }

    public function build()
    {
        return $this->subject(' الاشتراك    ')
                    ->markdown('sub.buy')
                    ->with([
                        'sub' => $this->sub,
                    ]);
    }
}
