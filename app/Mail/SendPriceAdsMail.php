<?php
namespace App\Mail;

use App\Models\Ad;
use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPriceAdsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ad;

    public function __construct(Ad $ad)
    {
        $this->ad = $ad;
    }

    public function build()
    {
        return $this->subject(' فاتورة خصم رصيد الإعلان')
                    ->markdown('ads.price')
                    ->with([
                        'ad' => $this->ad,
                    ]);
    }
}
