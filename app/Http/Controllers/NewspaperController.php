<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Language;
use App\Models\Province;
use App\Models\Newspaper;
use Illuminate\Http\Request;
use App\Models\NewspaperCategory;
use App\Models\NewspaperPeriodicity;

class NewspaperController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Newspapers &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspapers', 'url' => null],
        ];

        $newspapers = Newspaper::orderBy('created_at', 'desc')->paginate(20);

        return view('newspapers.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'newspapers' => $newspapers
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Newspaper &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspapers List', 'url' => route('newspaper.index')],
            ['label' => 'Add Newspaper', 'url' => null],
        ];

        $newspaper_periodicities = NewspaperPeriodicity::all();
        $languages = Language::all();
        $newspaper_categories = NewspaperCategory::all();
        $provinces = Province::all();
        $districts = District::all();
        $is_combined = Newspaper::is_combined;
        $statuses = Newspaper::STATUS;
        $kpraReg = Newspaper::KPRA;

        return view('newspapers.create', [
            'newspaper_periodicities' => $newspaper_periodicities,
            'languages' => $languages,
            'newspaper_categories' => $newspaper_categories,
            'provinces' => $provinces,
            'districts' => $districts,
            'is_combined' => $is_combined,
            'statuses' => $statuses,
            'kpraReg' => $kpraReg,
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'title' => 'required'
        ]);

        $newspaper = Newspaper::create($request->all());

        return redirect()->route('newspaper.index')->with('success', 'Newspaper added successfully.');
    }

    public function getDistricts(Request $request)
    {
        $districts = District::where('province_id', $request->province_id)->get();
        return response()->json($districts);
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Newspaper &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspapers List', 'url' => route('newspaper.index')],
            ['label' => 'Update Newspaper', 'url' => null],
        ];

        $newspaper = Newspaper::findOrFail($id);

        $newspaper_periodicities = NewspaperPeriodicity::all();
        $languages = Language::all();
        $newspaper_categories = NewspaperCategory::all();
        $provinces = Province::all();
        $districts = District::all();
        $is_combined = Newspaper::is_combined;
        $statuses = Newspaper::STATUS;
        $kpraReg = Newspaper::KPRA;

        return view('newspapers.edit', [
            'newspaper' => $newspaper,
            'newspaper_periodicities' => $newspaper_periodicities,
            'languages' => $languages,
            'newspaper_categories' => $newspaper_categories,
            'provinces' => $provinces,
            'districts' => $districts,
            'is_combined' => $is_combined,
            'statuses' => $statuses,
            'kpraReg' => $kpraReg,
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'newspaper' => $newspaper
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required'
        ]);

        $newspaper = Newspaper::findOrFail($id);
        $newspaper->update($request->all());

        return redirect()->route('newspaper.index', 'Newspaper updated successfully.');
    }

    public function destroy($id)
    {
        $newspaper = Newspaper::findOrFail($id);

        if ($newspaper) {
            $newspaper->delete();
            return response()->json(['success' => 'Newspaper deleted successfully.']);
        } else {
            return response()->json(['error', 'Data not found!'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Newspaper &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspapers List', 'url' => route('newspaper.index')],
            ['label' => 'Newspaper', 'url' => null], // The current page (no URL)
        ];

        $newspaper = Newspaper::findOrFail($id);

        return view(
            'newspapers.show',
            [
                'pageTitle' => $pageTitle,
                'breadcrumbs' => $breadcrumbs,
                'newspaper' => $newspaper
            ]
        );
    }
}
