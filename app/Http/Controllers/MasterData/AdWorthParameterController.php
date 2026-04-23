<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Models\AdWorthParameter;
use App\Http\Controllers\Controller;

class AdWorthParameterController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Ad Worth Parameters &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Ad Worth Parameters', 'url' => null], // The current page (no URL)
        ];

        $ad_worth_parameters = AdWorthParameter::all();

        return view('masterData.ad-worth-parameters.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'ad_worth_parameters' =>   $ad_worth_parameters
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Create Ad Worth Parameter &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Ad Worth Parameters List', 'url' => route('master.adWorthParameter.index')],
            ['label' => 'Create Ad Worth Parameter', 'url' => null], // The current page (no URL)
        ];
        return view('masterData.ad-worth-parameters.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $validated_data = $request->validate([
            'range' => 'required|string',
            'formula' => 'required|string'
        ]);

        AdWorthParameter::create($validated_data);

        return redirect()->route('master.adWorthParameter.index')->with('success', 'Ad Worth Parameter added successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Ad Worth Parameter &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Ad Worth Parameters List', 'url' => route('master.adWorthParameter.index')],
            ['label' => 'Update Ad Worth Parameter', 'url' => null], // The current page (no URL)
        ];

        $ad_worth_parameter = AdWorthParameter::findOrFail($id);

        return view('masterData.ad-worth-parameters.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'ad_worth_parameter' => $ad_worth_parameter
        ]);
    }

    public function update(Request $request, $id)
    {
        $ad_worth_parameter = AdWorthParameter::findOrFail($id);
        $validated_data = $request->validate([
            'range' => 'required|string',
            'formula' => 'required|string'

        ]);
        $ad_worth_parameter->update($validated_data);
        return redirect()->route('master.adWorthParameter.index')->with('success', 'Ad Worth Parameter updated successfully.');
    }

    public function destroy($id)
    {
        $ad_worth_parameter = AdWorthParameter::findOrFail($id);

        if ($ad_worth_parameter) {
            $ad_worth_parameter->delete();
            return response()->json(['success' => 'Ad Worth Parameter deleted successfully.']);
        } else {
            return response()->json(['error' => 'Ad Worth Parameter not found.'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Ad Worth Parameters &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Ad Worth Parameters List', 'url' => route('master.adWorthParameter.index')],
            ['label' => 'Ad Worth Parameters', 'url' => null], // The current page (no URL)
        ];

        $adWorthParameter = AdWorthParameter::findOrFail($id);

        return view('masterData.ad-worth-parameters.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'adWorthParameter' => $adWorthParameter
            ]
        );
    }
}
