<?php
namespace App\Mail;

use App\Models\Ad;
use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendBalanceAdMail extends Mailable
{
    use Queueable, SerializesModels;

    public $balance;

    public function __construct($balance)
    {
        $this->balance = $balance;
    }

    public function build()
    {
        return $this->subject(' اضافة رصيد ')
                    ->markdown('balance.add')
                    ->with([
                        'balance' => $this->balance,
                    ]);
    }
}
