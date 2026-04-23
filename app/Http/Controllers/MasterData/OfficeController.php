<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Office;
use App\Models\Status;
use App\Models\District;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\OfficeCategory;
use App\Http\Controllers\Controller;

class OfficeController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Offices &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Offices', 'url' => null], // The current page (no URL)
        ];

        $offices = Office::paginate(30);

        return view('masterData.offices.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'offices' => $offices
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Office &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Offices List', 'url' => route('master.office.index')],
            ['label' => 'Add Office', 'url' => null], // The current page (no URL)
        ];

        $officeCategories = OfficeCategory::all();
        $departments = Department::all();
        $districts = District::all();
        $office_statuses = Office::office_status;

        return view('masterData.offices.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'officeCategories' => $officeCategories,
            'office_statuses' => $office_statuses,
            'departments' => $departments,
            'districts' => $districts
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ddo_name'           => 'required|string',
            'ddo_code'           => 'required|string',
            'department_id'      => 'required|integer',
            'district_id'        => 'required|integer',
            'office_category_id' => 'required|integer',
            'status'             => 'required|in:0,1',  // restricts to Active (1) or Inactive (0)
            'opening_dues'       => 'required|numeric'
        ]);

        Office::create([
            'ddo_name' => $request->ddo_name,
            'ddo_code' => $request->ddo_code,
            'department_id' => $request->department_id,
            'district_id' => $request->district_id,
            'office_category_id' => $request->office_category_id,
            'status' => $request->status,
            'opening_dues' => $request->opening_dues,
        ]);

        return redirect()->route('master.office.index')
            ->with('success', 'Office created successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Office &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Offices List', 'url' => route('master.office.index')],
            ['label' => 'Update Office', 'url' => null], // The current page (no URL)
        ];

        $office = Office::findOrFail($id);
        $selected_category = $office->office_category_id;
        $selected_status = $office->status_id;
        $selected_department = $office->department_id;
        $selected_district = $office->district_id;
        $office_categories = OfficeCategory::all();
        $statuses = Status::all();
        $departments = Department::all();
        $districts = District::all();

        return view('masterData.offices.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'office' => $office,
            'office_categories' => $office_categories,
            'statuses' => $statuses,
            'departments' => $departments,
            'districts' => $districts,
            'selected_category' => $selected_category,
            'selected_status' => $selected_status,
            'selected_department' => $selected_department,
            'selected_district' => $selected_district
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'office_category_id' => 'required|',
            'status_id' => 'required',
            'department_id' => 'required',
            'district_id' => 'required',
            'opening_dues' => 'required|numeric',
            'deactivation_date' => 'nullable|date',
        ]);

        $office = Office::findOrFail($id);
        $office->update($request->all());

        return redirect()->route('master.office.index')
            ->with('success', 'Office updated successfully.');
    }

    public function destroy($id)
    {
        $office = Office::findOrFail($id);

        if ($office) {
            $office->delete();
            return response()->json(['success' => 'Office deleted successfully.']);
        } else {
            return response()->json(['error' => 'Office not found.'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Office &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Offices List', 'url' => route('master.office.index')],
            ['label' => 'Office', 'url' => null], // The current page (no URL)
        ];

        $office = Office::findOrFail($id);

        return view('masterData.offices.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'office' => $office
            ]
        );
    }
}
