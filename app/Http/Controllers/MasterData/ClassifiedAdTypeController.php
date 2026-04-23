<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Models\ClassifiedAdType;
use App\Http\Controllers\Controller;

class ClassifiedAdTypeController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Classified Ad Types &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Classified Ad Types', 'url' => null], // The current page (no URL)
        ];

        $classified_ad_types = ClassifiedAdType::all();

        return view('masterData.classified-ad-types.index',[
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'classified_ad_types' => $classified_ad_types
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Classified Ad Types &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Classified Ad Types List', 'url' => route('master.classifiedAdType.index')],
            ['label' => 'Add Classified Ad Types', 'url' => null], // The current page (no URL)
        ];
        return view('masterData.classified-ad-types.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required'
        ]);

        $classified_ad_type = ClassifiedAdType::create($request->all());

        return redirect()->route('master.classifiedAdType.index')->with('success', 'Classified Ad Type added successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Classified Ad Type &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Classified Ad Types List', 'url' => route('master.classifiedAdType.index')],
            ['label' => 'Update Classified Ad Type', 'url' => null], // The current page (no URL)
        ];

        $classified_ad_type = ClassifiedAdType::findOrFail($id);

        return view('masterData.classified-ad-types.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'classified_ad_type' => $classified_ad_type
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required'
        ]);

        $classified_ad_type = ClassifiedAdType::findOrFail($id);
        $classified_ad_type->update($request->all());

        return redirect()->route('master.classifiedAdType.index')->with('success', 'Classified Ad Type updated successfully.');
    }

    public function destroy($id)
    {
        $classified_ad_type = ClassifiedAdType::findOrFail($id);

        if ($classified_ad_type) {
            $classified_ad_type->delete();
            return response()->json(['success', 'Classified Ad Type deleted successfully!']);
        } else {
            return response()->json(['error', 'Classified Ad Type not found!'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Classified Ad Type &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Classified Ad Types List', 'url' => route('master.classifiedAdType.index')],
            ['label' => 'Classified Ad Type', 'url' => null], // The current page (no URL)
        ];

        $classifiedAdType = ClassifiedAdType::findOrFail($id);

        return view('masterData.classified-ad-types.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'classifiedAdType' => $classifiedAdType
            ]
        );
    }
}
