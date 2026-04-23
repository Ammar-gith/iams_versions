<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
// use App\Models\TreasuryChallan;
// use App\Models\Payment;
use App\Models\Advertisement;
// use Illuminate\Support\Facades\Gate;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        // dd($request->all());
        $query = $request->get('q');

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Search', 'url' => null],
        ];

        if (empty($query)) {
            return view('search.results', [
                'results' => collect(),
                'query' => $query,
                'breadcrumbs' => $breadcrumbs,
            ]);
        }

        $results = collect();

        // 1. Users
        if (auth()->user()->hasRole('Superintendent') || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin')) {
            $users = User::where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->get()
                ->map(function ($user) {
                    $user->type = 'user';
                    return $user;
                });
            $results = $results->concat($users);
        }

        // // 2. Treasury Challans (financials)
        // if (Gate::allows('View financials')) {
        //     $challans = TreasuryChallan::where('challan_number', 'LIKE', "%{$query}%")
        //         ->orWhere('amount', 'LIKE', "%{$query}%")
        //         ->get()
        //         ->map(function ($challan) {
        //             $challan->type = 'treasury_challan';
        //             return $challan;
        //         });
        //     $results = $results->concat($challans);
        // }

        // // 3. Payments
        // if (Gate::allows('view payments')) {
        //     $payments = Payment::where('invoice_number', 'LIKE', "%{$query}%")
        //         ->orWhere('amount', 'LIKE', "%{$query}%")
        //         ->get()
        //         ->map(function ($payment) {
        //             $payment->type = 'payment';
        //             return $payment;
        //         });
        //     $results = $results->concat($payments);
        // }



        // Optional: sort results by relevance or date
        $results = $results->sortByDesc('created_at');

        return view('search.results', compact('results', 'query', 'breadcrumbs'));
    }
}
       // 4. Advertisements
        if (auth()->user()->hasRole('Superintendent') || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin')) {
            $ads = Advertisement::where('inf_number', 'LIKE', "%{$query}%")
                ->orWhere('memo_number', 'LIKE', "%{$query}%")
                ->orWhere('office_id', 'LIKE', "%{$query}%")
                ->get()
                ->map(function ($ad) {
                    $ad->type = 'advertisement';
                    return $ad;
                });
            $results = $results->concat($ads);
        }
