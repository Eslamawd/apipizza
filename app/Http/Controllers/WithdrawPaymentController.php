<?php

namespace App\Http\Controllers;

use App\Http\Resources\WithdrawPaymentResource;
use App\Models\WithdrawPayment;
use Illuminate\Http\Request;

class WithdrawPaymentController extends Controller
{
    //

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'amount' => 'required|numeric|min:500' ,
            'phone' => 'required|string|max:15',
        ]);

        if ($user->balanceInt < $request->amount * 100) {
            return response()->json(['message' => 'Insufficient balance for this withdrawal.'], 400);
        }
        $withdrawPayment = WithdrawPayment::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'phone' => $request->phone,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Withdraw request submitted successfully.', 'withdraw_payment' => $withdrawPayment], 201);
    }

    public function index()
    {
        $user = auth()->user();
        $withdrawPayments = $user->withdrawPayments()->latest()->paginate(10);
        return response()->json(['withdraw_payments' => $withdrawPayments]);
    }
public function getByAdmin()
{
    $withdrawPayments = WithdrawPayment::with('user.affiliate.earnings')->paginate(10);

    return response()->json([
        'withdraw_payments' => WithdrawPaymentResource::collection($withdrawPayments),
        'current_page' => $withdrawPayments->currentPage(),
        'last_page' => $withdrawPayments->lastPage(),
        'total' => $withdrawPayments->total(),
    ]);
}

 public function update(Request $request, WithdrawPayment $withdrawPayment)
{
    $request->validate([
        'status' => 'required|in:pending,approved,rejected',
    ]);

    // ✅ فقط لو الحالة الجديدة هي approved والحالية ليست approved من قبل
    if ($request->status === 'approved' && $withdrawPayment->status !== 'approved') {

        // ✅ تحقق أن المستخدم مازال يملك الرصيد الكافي
        if ($withdrawPayment->user->balanceInt < $withdrawPayment->amount * 100) {
            return response()->json(['message' => 'User does not have enough balance.'], 400);
        }

        // ✅ سحب الرصيد من المستخدم
        $withdrawPayment->user->withdraw($withdrawPayment->amount * 100);
    }

    // ✅ تحديث الحالة فقط
    $withdrawPayment->update([
        'status' => $request->status,
    ]);

    return response()->json([
        'message' => 'Withdraw payment updated successfully.',
        'withdraw_payment' => $withdrawPayment
    ]);
}

}
