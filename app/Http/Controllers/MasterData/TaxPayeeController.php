<?php

namespace App\Http\Controllers\MasterData;

use App\Models\TaxPayee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaxPayeeController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Tax Payee &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Tax Payee', 'url' => null], // The current page (no URL)
        ];

        $taxPayees = TaxPayee::all();

        return view('masterData.tax-payees.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'taxPayees' => $taxPayees
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Tax Payee &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Tax Payees List', 'url' => route('master.taxPayee.index')],
            ['label' => 'Add Tax Payee', 'url' => null], // The current page (no URL)
        ];
        return view('masterData.tax-payees.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $taxPayeeData = $request->all();
        TaxPayee::create($taxPayeeData);

        return redirect()->route('master.taxPayee.index')->with('success', 'Tax payee added successfully!');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Tax Payee &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Tax Payees List', 'url' => route('master.taxPayee.index')],
            ['label' => 'Update Tax Payee', 'url' => null], // The current page (no URL)
        ];

        $taxPayee = TaxPayee::findOrFail($id);

        return view('masterData.tax-payees.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'taxPayee' => $taxPayee
        ]);
    }

    public function update(Request $request, $id)
    {
        $taxPayee = TaxPayee::findOrFail($id);
        $taxPayee->update($request->all());

        return redirect()->route('master.taxPayee.index')->with('success', 'Tax payee updated successfully!');
    }

    public function destroy($id)
    {
        $taxPayee = TaxPayee::findOrFail($id);

        if ($taxPayee) {
            $taxPayee->delete();
            return response()->json(['success' => 'TaxPayee deleted successfully.']);
        } else {
            return response()->json(['error' => 'TaxPayee not found.'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Tax Payee &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Tax Payees List', 'url' => route('master.taxPayee.index')],
            ['label' => 'Tax Payee', 'url' => null], // The current page (no URL)
        ];

        $taxPayee = TaxPayee::findOrFail($id);

        return view('masterData.tax-payees.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'taxPayee' => $taxPayee
            ]
        );
    }
}
