<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'status' => $this->status,
            'phone' => $this->phone,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
                'balance' => $this->user->balanceInt / 100, // ✅ نفس الفكرة اللي في UserResource
                'role' => $this->user->getRoleNames()->first() ?: 'user',

                'affiliate' => [
                    'id' => optional($this->user->affiliate)->id,
                    'affiliate_code' => optional($this->user->affiliate)->affiliate_code,
                    'registrations' => optional($this->user->affiliate)->registrations,
                    'balance' => optional($this->user->affiliate)->balance,
                    'earnings' => optional($this->user->affiliate)->earnings ?? [],
                ],
            ],
        ];
    }
}
