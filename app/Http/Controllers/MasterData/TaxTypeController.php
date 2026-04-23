<?php

namespace App\Http\Controllers\MasterData;

use App\Models\TaxType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaxTypeController extends Controller
{
    //
    public function index()
    {
        // Page title
        $pageTitle = 'Tax Types &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Tax Types', 'url' => null], // The current page (no URL)
        ];

        $taxTypes = TaxType::all();
        return view('masterData.tax-types.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'taxTypes' => $taxTypes
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Tax Type &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Tax Types List', 'url' => route('master.taxType.index')],
            ['label' => 'Add Tax Type', 'url' => null], // The current page (no URL)
        ];

        return view('masterData.tax-types.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $taxData = $request->all();
        TaxType::create($taxData);
        return redirect()->route('master.taxType.index')->with('success', 'Tax Type added successfully!');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Tax Type &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Tax Types List', 'url' => route('master.taxType.index')],
            ['label' => 'Update Tax Type', 'url' => null], // The current page (no URL)
        ];

        $taxType = TaxType::findOrFail($id);
        return view('masterData.tax-types.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'taxType' => $taxType
        ]);
    }

    public function update(Request $request, $id)
    {
        $taxType = TaxType::findOrFail($id);
        $taxType->update($request->all());
        return redirect()->route('master.taxType.index')->with('success', 'Tax Type updated successfully!');
    }

    public function destroy($id)
    {
        $taxType = TaxType::findOrFail($id);

        if ($taxType) {
            $taxType->delete();
            return response()->json(['success' => 'Tax Type deleted successfully.']);
        } else {
            return response()->json(['error' => 'Tax Type not found.'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Tax Type &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Tax Types List', 'url' => route('master.taxType.index')],
            ['label' => 'Tax Type', 'url' => null], // The current page (no URL)
        ];

        $taxType = TaxType::findOrFail($id);

        return view('masterData.tax-types.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'taxType' => $taxType
            ]
        );
    }
}
