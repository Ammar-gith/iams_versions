<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Models\DepartmentCategory;
use App\Http\Controllers\Controller;

class DepartmentCategoryController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Department Categories &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Department Categories', 'url' => null], // The current page (no URL)
        ];

        $department_categories = DepartmentCategory::all();
        return view('masterData.department-categories.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'department_categories' => $department_categories
        ]);
    }

    public function create(Request $request)
    {
        // Page title
        $pageTitle = 'Add Department Categories &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Department Categories List', 'url' => route('master.department.departmentCategory.index')],
            ['label' => 'Add Department Category', 'url' => null], // The current page (no URL)
        ];

        return view('masterData.department-categories.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required'
        ]);

        $validated_data = $request->validate([
            'title' => 'required'
        ]);

        DepartmentCategory::create($validated_data);

        return redirect()->route('master.department.departmentCategory.index')
            ->with('success', 'Department Category created successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Department Categories &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Department Categories List', 'url' => route('master.department.departmentCategory.index')],
            ['label' => 'Add Department Category', 'url' => null], // The current page (no URL)
        ];

        $department_category = DepartmentCategory::find($id);

        return view('masterData.department-categories.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'department_category' => $department_category
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required'
        ]);

        $department_category = DepartmentCategory::find($id);

        $department_category->update($request->all());

        return redirect()->route('master.department.departmentCategory.index')
            ->with('success', 'Department Category updated successfully');
    }

    public function destroy($id)
    {
        $department_category = DepartmentCategory::find($id);

        if ($department_category) {
            $department_category->delete();

            return response()->json(['success' => 'Department Category deleted successfully']);
        } else {
            return response()->json(['error' => 'Department Category not found'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Department Category &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Department Categories List', 'url' => route('master.department.departmentCategory.index')],
            ['label' => 'Add Department Category', 'url' => null], // The current page (no URL)
        ];

        $departmentCategory = DepartmentCategory::findOrFail($id);

        return view('masterData.department-categories.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'departmentCategory' => $departmentCategory
            ]
        );
    }
}
