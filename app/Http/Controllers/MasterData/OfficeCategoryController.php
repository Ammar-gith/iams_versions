<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Models\OfficeCategory;
use App\Http\Controllers\Controller;

class OfficeCategoryController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Office Categories &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Office Categories', 'url' => null], // The current page (no URL)
        ];

        $office_categories = OfficeCategory::all();

        return view('masterData.office-categories.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'office_categories' => $office_categories
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Office Categories &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Office Categories List', 'url' => route('master.office.officeCategory.index')],
            ['label' => 'Add Office Categories', 'url' => null], // The current page (no URL)
        ];

        return view('masterData.office-categories.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
           'title' => 'required|string',
        ]);

        $office_category = OfficeCategory::create($request->all());
        return redirect()->route('master.office.officeCategory.index')->with('success', 'Office Category created successfully');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Office Categories &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Office Categories List', 'url' => route('master.office.officeCategory.index')],
            ['label' => 'Update Office Categories', 'url' => null], // The current page (no URL)
        ];

        $office_category = OfficeCategory::find($id);

        return view('masterData.office-categories.edit',[
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'office_category' => $office_category
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        $office_category = OfficeCategory::findOrFail($id);
        $office_category->update($request->all());

        return redirect()->route('master.office.officeCategory.index')->with('success', 'Office Category updated successfully');
    }

    public function destroy($id)
    {
        $office_category = OfficeCategory::findOrFail($id);

        if($office_category){
            $office_category->delete();
            return response()->json(['success' => 'Office Category deleted successfully']);
        }else{
            return response()->json(['error' => 'Office Category not found'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Office Category &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Office Categories List', 'url' => route('master.office.officeCategory.index')],
            ['label' => 'Office Category', 'url' => null], // The current page (no URL)
        ];

        $officeCategory = OfficeCategory::findOrFail($id);

        return view('masterData.office-categories.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'officeCategory' => $officeCategory
            ]
        );
    }
}
