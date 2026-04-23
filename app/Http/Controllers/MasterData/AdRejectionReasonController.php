<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Models\AdRejectionReason;
use App\Http\Controllers\Controller;

class AdRejectionReasonController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Ad Rejection Reasons &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Ad Rejection Reasons', 'url' => null], // The current page (no URL)
        ];

        $ad_rejection_reasons = AdRejectionReason::all();

        return view('masterData.ad-rejection-reasons.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'ad_rejection_reasons' => $ad_rejection_reasons
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Create Ad Rejection Reasons &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Ad Rejection Reasons List', 'url' => route('master.adRejectionReason.index')],
            ['label' => 'Create Ad Rejection Reasons', 'url' => null], // The current page (no URL)
        ];
        return view('masterData.ad-rejection-reasons.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required'
        ]);

        AdRejectionReason::create($request->all());
        return redirect()->route('master.adRejectionReason.index');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Ad Rejection Reasons &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Ad Rejection Reasons List', 'url' => route('master.adRejectionReason.index')],
            ['label' => 'Update Ad Rejection Reasons', 'url' => null], // The current page (no URL)
        ];

        $ad_rejection_reason = AdRejectionReason::find($id);
        return view('masterData.ad-rejection-reasons.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'ad_rejection_reason' => $ad_rejection_reason
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'description' => 'required'
        ]);

        $ad_rejection_reason = AdRejectionReason::find($id);
        $ad_rejection_reason->update($request->all());
        return redirect()->route('master.adRejectionReason.index');
    }

    public function destroy($id)
    {
        $ad_rejection_reason = AdRejectionReason::find($id);

        if ($ad_rejection_reason) {
            $ad_rejection_reason->delete();
            return response()->json(['success' => 'Ad Rejection Reason deleted successfully/']);
        } else {
            return response()->json(['error' => 'Ad Rejection Reason not found.'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Ad Rejection Reasons &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Ad Rejection Reasons List', 'url' => route('master.adRejectionReason.index')],
            ['label' => 'Ad Rejection Reasons', 'url' => null], // The current page (no URL)
        ];

        $adRejectionReason = AdRejectionReason::findOrFail($id);

        return view('masterData.ad-rejection-reasons.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'adRejectionReason' => $adRejectionReason
            ]
        );
    }
}
