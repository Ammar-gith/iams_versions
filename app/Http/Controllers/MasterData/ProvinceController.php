<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Province;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProvinceController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Provinces &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Provinces', 'url' => null], // The current page (no URL)
        ];

        $provinces = Province::all();

        return view('masterData.provinces.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'provinces' => $provinces
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Province &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Provinces List', 'url' => route('master.province.index')],
            ['label' => 'Add Province', 'url' => null], // The current page (no URL)
        ];

        return view('masterData.provinces.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $validated_Data = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255'
        ]);

        Province::create($validated_Data);

        return redirect()->route('master.province.index')->with('success', 'Province added successfully! ');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Province &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Provinces List', 'url' => route('master.province.index')],
            ['label' => 'Update Province', 'url' => null], // The current page (no URL)
        ];

        $province = Province::findOrFail($id);

        return view('masterData.provinces.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'province' => $province
        ]);
    }

    public function update(Request $request, $id)
    {
        $province = Province::findOrFail($id);

        $validated_Data = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255'
        ]);

        $province->update($validated_Data);

        return redirect()->route('master.province.index')->with('success', 'Province udpated successfully!');
    }

    public function destroy($id)
    {
        $province = Province::findOrFail($id);

        if ($province) {
            $province->delete();
            return response()->json(['success' => 'Province deleted successfully!']);
        } else {
            return response()->json(['error' => 'Province not found!']);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Province &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Provinces List', 'url' => route('master.province.index')],
            ['label' => 'Province', 'url' => null], // The current page (no URL)
        ];

        $province = Province::findOrFail($id);

        return view('masterData.provinces.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'province' => $province
            ]
        );
    }
}
