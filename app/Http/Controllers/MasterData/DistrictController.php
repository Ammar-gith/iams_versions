<?php

namespace App\Http\Controllers\MasterData;

use App\Models\District;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DistrictController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Districts &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Districts', 'url' => null], // The current page (no URL)
        ];

        $districts = District::with('province')->get(); // select all columns from districts table and name column from provinces table using model relationship method
        return view('masterData.districts.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'districts' => $districts,

        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add District &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Districts List', 'url' => route('master.district.index')],
            ['label' => 'Add District', 'url' => null], // The current page (no URL)
        ];

        $provinces = Province::all();

        return view('masterData.districts.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'provinces' => $provinces
        ]);
    }

    public function Store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'province_id' => 'required'
        ]);

        $district = District::create($request->all());

        return redirect()->route('master.district.index')->with('success', 'District created successfully');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update District &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Districts List', 'url' => route('master.district.index')],
            ['label' => 'Update District', 'url' => null], // The current page (no URL)
        ];

        $district = District::findOrFail($id);

        return view('masterData.districts.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'district' => $district
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'province_id' => 'nullable'
        ]);

        $district = District::findOrFail($id);
        $district->update($request->all());

        return redirect()->route('master.district.index')->with('success', 'District updated successfully');
    }

    public function destroy($id)
    {
        $district = District::findOrFail($id);
        if ($district) {
            $district->delete();
            return response()->json(['success', 'District deleted successfully']);
        } else {
            return response()->json(['error', 'District not found', 404]);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'District &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Districts List', 'url' => route('master.district.index')],
            ['label' => 'District', 'url' => null], // The current page (no URL)
        ];

        $district = District::findOrFail($id);

        return view('masterData.districts.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'district' => $district
            ]
        );
    }
}
