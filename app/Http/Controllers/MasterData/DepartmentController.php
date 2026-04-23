<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Status;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\DepartmentCategory;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Departments &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Departments', 'url' => null], // The current page (no URL)
        ];

        $departments = Department::all();

        return view('masterData.departments.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'departments' => $departments,

        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Department &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Departments List', 'url' => route('master.department.index')],
            ['label' => 'Add Department', 'url' => null], // The current page (no URL)
        ];

        $department_categories = DepartmentCategory::all();
        $department_statuses = Department::department_statuses;
        return view('masterData.departments.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'department_categories' => $department_categories,
            'department_statuses' => $department_statuses
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category_id' => 'required|exists:department_categories,id',
            'status_id' => 'required|exists:statuses,id',
        ]);

        Department::create($request->all());

        return redirect()->route('master.department.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Department &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Departments List', 'url' => route('master.department.index')],
            ['label' => 'Update Department', 'url' => null], // The current page (no URL)
        ];

        $department = Department::findOrFail($id);
        $selected_category = $department->category_id;
        $selected_status = $department->status_id;
        $department_categories = DepartmentCategory::all();
        $statuses = Status::all();

        return view('masterData.departments.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'department' => $department,
            'department_categories' =>  $department_categories,
            'statuses' => $statuses,
            'selected_category' => $selected_category,
            'selected_status' => $selected_status
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'category_id' => 'required',
            'status_id' => 'required',
        ]);

        $department = Department::findOrFail($id);

        $department->update($request->all());

        return redirect()->route('master.department.index')
            ->with('success', 'Department updated successfully');
    }

    public function destroy($id)
    {
        $department = Department::find($id);

        if ($department) {
            $department->delete();
            return response()->json(['success' => 'Department deleted successfully']);
        } else {
            return response()->json(['error' => 'Department not found'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Department &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Departments List', 'url' => route('master.department.index')],
            ['label' => 'Department', 'url' => null], // The current page (no URL)
        ];

        $department = Department::findOrFail($id);

        return view('masterData.departments.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'department' => $department
            ]
        );
    }
}
