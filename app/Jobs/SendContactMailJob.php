<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Mail\ContactMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendContactMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contact;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

 public function handle()
{
    // إرسال الإيميل للـ user الذي أدخل بريده
    Mail::to($this->contact->email)
        ->send(new ContactMail($this->contact));
}
}

