<?php

namespace App\Http\Controllers;

use App\Models\BillClassifiedAd;
use App\Models\TreasuryChallan;
use Illuminate\Http\Request;

class ChequeReceiptNpController extends Controller
{
    public function index()
    {
        $chequeReceiptsNps = TreasuryChallan::whereNotNull('sbp_verification_date')->latest()->get();
        return view('cheque-receipt-newspapers.index', compact('chequeReceiptsNps'));
    }

    public function modalData(Request $request, $id)
    {
        // dd($request->all());
        $treasuryChallan = TreasuryChallan::findOrFail($id);
        $chequeReceiptsNps = $treasuryChallan->update($request->all());

        return redirect()->back()->with('success', 'Record verified successfully.');
    }

    public function receipt($id)
    {
        $treasuryChallan = TreasuryChallan::findOrFail($id);
        $inf_numbers = $treasuryChallan->inf_number ?? [];

        // Fetch all related receiptsNp ads for given INF numbers
        $receiptsNps = BillClassifiedAd::whereIn('inf_number', (array)$inf_numbers)
            ->with(['user.newspaper'])
            ->whereNotNull('estimated_cost') // eager load user and newspaper
            ->get();

        $receiptDetails = [];

        foreach ($receiptsNps as $receiptsNp) {
            // Get newspaper name safely
            $newspaperTitle = $receiptsNp->user->newspaper->title ?? 'N/A';

            // Bill amount
            $printed_total_bill = $receiptsNp->printed_total_bill ?? 0;

            // 1.5% income tax
            $income_tax_rate = 1.5;
            $income_tax_amount = $printed_total_bill * $income_tax_rate / 100;

            // Total after adding tax
            $total_after_tax = $printed_total_bill - $income_tax_amount;

            $receiptDetails[] = [
                'id' => $receiptsNp->id,
                'newspaper' => $newspaperTitle,
                'printed_total_bill' => round($printed_total_bill),
                'income_tax_rate' => $income_tax_rate,
                'income_tax_amount' => round($income_tax_amount),
                'total_after_tax' => round($total_after_tax),
            ];
        }


        return view('cheque-receipt-newspapers.receipt', [
            'receiptDetails' => $receiptDetails,
            'treasuryChallan' => $treasuryChallan,
            'inf_numbers' => $inf_numbers,

        ]);
    }
}
