<?php

namespace App\Http\Controllers\MasterData;

use App\Models\AdCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdCategoryController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Ads Categories &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Ads Categories', 'url' => null], // The current page (no URL)
        ];

        $ad_categories = AdCategory::all();
        return view('masterData.ad-categories.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'ad_categories' => $ad_categories
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Create Ad Category &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Ad Categories List', 'url' => route('master.adCategory.index')],
            ['label' => 'Create Ad Category', 'url' => null], // The current page (no URL)
        ];

        return view('masterData.ad-categories.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required'
        ]);

        $ad_category = AdCategory::create($request->all());

        return redirect()->route('master.adCategory.index')->with('success', 'Ad Category added successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Ad Category &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Ad Categories List', 'url' => route('master.adCategory.index')],
            ['label' => 'Update Ad Category', 'url' => null], // The current page (no URL)
        ];

        $ad_category = AdCategory::findOrFail($id);

        return view('masterData.ad-categories.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'ad_category' => $ad_category
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required'
        ]);

        $ad_category = AdCategory::findOrFail($id);
        $ad_category->update($request->all());

        return redirect()->route('master.adCategory.index')->with('success', 'Ad Category updated successfully.');
    }

    public function destroy($id)
    {
        $ad_category = AdCategory::findOrFail($id);

        if ($ad_category) {
            $ad_category->delete();
            return response()->json(['success', 'Ad Category deleted successfully!']);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Ad Category &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Ad Categories List', 'url' => route('master.adCategory.index')],
            ['label' => 'Ad Category', 'url' => null], // The current page (no URL)
        ];

        $adCategory = AdCategory::findOrFail($id);

        return view('masterData.ad-categories.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'adCategory' => $adCategory
            ]
        );
    }
}
