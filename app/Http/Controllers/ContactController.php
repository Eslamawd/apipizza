<?php

namespace App\Http\Controllers;

use App\Jobs\SendContactMailJob;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    //


    public function index() {
        $conacts = Contact::latest()->paginate(6);
        return response()->json(['contacts' => $conacts ]);

    }

    public function store (Request $request) {

        $valitad =  $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:15',
            'message'=> 'required|string',
        ]);

      $contact = Contact::create($valitad);

       SendContactMailJob::dispatch($contact);

      return response()->json(['message'=> 'Send Your Data']);

    }
}
