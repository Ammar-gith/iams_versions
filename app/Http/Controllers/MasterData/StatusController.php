<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Status;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StatusController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Statuses List &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Statuses List', 'url' => null], // The current page (no URL)
        ];

        $statuses = Status::all();
        return view('masterData.statuses.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'statuses' => $statuses
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Status &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Statuses List', 'url' => route('master.status.index')],
            ['label' => 'Add Status', 'url' => null] // The current page (no URL)
        ];

        return view('masterData.statuses.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $status = Status::create($request->all());

        return redirect()->route('master.status.index')->with('success', 'Status added successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Statuses &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Statuses List', 'url' => route('master.status.index')],
            ['label' => 'Update Status', 'url' => null] // The current page (no URL)
        ];

        $status = Status::findOrFail($id);

        return view('masterData.statuses.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'status' => $status
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required'
        ]);

        $status = Status::findOrFail($id);
        $status->update($request->all());

        return redirect()->route('master.departmentStatus.index')->with('success', 'Status updated successfully.');
    }

    public function destroy($id)
    {
        $status = Status::findOrFail($id);

        if ($status) {
            $status->delete();
            return response()->json(['success', 'Status deleted successfully!']);
        }
        return response()->json(['error', 'Status is not found'], 404);
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Status &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Statuses List', 'url' => route('master.status.index')],
            ['label' => 'Status', 'url' => null] // The current page (no URL)
        ];

        $status = Status::findOrFail($id);

        return view('masterData.statuses.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'status' => $status
            ]
        );
    }
}
