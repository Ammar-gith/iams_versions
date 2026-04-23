<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\AdvAgency;
use Illuminate\Http\Request;

class AdvAgencyController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Advertising Agencies &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Advertising Agencies', 'url' => null], // The current page (no URL)
        ];

        // $adv_agencies = AdvAgency::all();
        $adv_agencies = AdvAgency::orderBy('created_at', 'desc')->paginate(20);

        return view('adv-agencies.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'adv_agencies' => $adv_agencies
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Advertising Agency &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Advertising Agencies List', 'url' => route('advAgency.index')],
            ['label' => 'Add Advertising Agency', 'url' => null], // The current page (no URL)
        ];

        $statuses = Status::all();

        return view('adv-agencies.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'statuses' => $statuses
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'registration_date' => 'nullable',
            'registered_with_kpra' => 'nullable|boolean',
            'website' => 'nullable|string',
            'profile_pba' => 'nullable|string',
            'status_id' => 'nullable|exists:statuses,id',
            'phone_local' => 'nullable|integer',
            'email_local' => 'nullable|string',
            'fax_local' => 'nullable|string',
            'mailing_address_local' => 'nullable|string',
            'person_name_local' => 'nullable|string',
            'person_cell_local' => 'nullable|integer',
            'phone_hq' => 'nullable|integer',
            'email_hq' => 'nullable|string',
            'fax_hq' => 'nullable|string',
            'mailing_address_hq' => 'nullable|string',
            'person_name_hq' => 'nullable|string',
            'person_cell_hq' => 'nullable|integer',
        ]);

        $adv_agency = AdvAgency::create($request->all());

        return redirect()->route('advAgency.index')->with('success', 'Adv. Agency added successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Advertising Agency &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Advertising Agencies List', 'url' => route('advAgency.index')],
            ['label' => 'Update Advertising Agency', 'url' => null], // The current page (no URL)
        ];

        $adv_agency = AdvAgency::findOrFail($id);
        $statuses = Status::all();
        $selectedStatus =  $adv_agency->status_id;

        return view('adv-agencies.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'adv_agency' => $adv_agency,
            'statuses' => $statuses,
            'selectedStatus' => $selectedStatus,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'registration_date' => 'nullable',
            'registered_with_kpra' => 'nullable|boolean',
            'website' => 'nullable|string',
            'profile_pba' => 'nullable|string',
            'status_id' => 'nullable',
            'phone_local' => 'nullable|integer',
            'email_local' => 'nullable|string',
            'fax_local' => 'nullable|string',
            'mailing_address_local' => 'nullable|string',
            'person_name_local' => 'nullable|string',
            'person_cell_local' => 'nullable|integer',
            'phone_hq' => 'nullable|integer',
            'email_hq' => 'nullable|string',
            'fax_hq' => 'nullable|string',
            'mailing_address_hq' => 'nullable|string',
            'person_name_hq' => 'nullable|string',
            'person_cell_hq' => 'nullable|integer',
        ]);

        $adv_agency = AdvAgency::findOrFail($id);

        $adv_agency->update($request->all());

        return redirect()->route('advAgency.index')->with('success', 'Adv. Agency updated successfully');
    }

    public function destroy($id)
    {
        $adv_agency = AdvAgency::findOrFail($id);

        if ($adv_agency) {
            $adv_agency->delete();

            return response()->json(['success', 'Adv. Agency deleted successfully.']);
        } else {
            return response()->json(['success', 'Adv. Agency not found'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Advertising Agency &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Advertising Agencies List', 'url' => route('advAgency.index')],
            ['label' => 'Advertising Agency', 'url' => null], // The current page (no URL)
        ];

        $advAgency = AdvAgency::findOrFail($id);

        return view('adv-agencies.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advAgency' => $advAgency
            ]
        );
    }
}
