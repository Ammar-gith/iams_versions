<?php

namespace App\Http\Controllers;

use App\Models\NewspaperPeriodicity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\PaginatedResourceResponse;

class NewspaperPeriodicityController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Newspaper Periodicity &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspaper Periodicity', 'url' => null],
        ];

        $newspaper_periodicities = NewspaperPeriodicity::all();

        return view('newspaper-periodicity.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'newspaper_periodicities' => $newspaper_periodicities
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Newspaper Periodicity &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspapers Periodicity List', 'url' => route('newspaper.newspaperPeriodicity.index')],
            ['label' => 'Add Newspaper Periodicity', 'url' => null],
        ];

        return view('newspaper-periodicity.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        $newspaper_periodicity = NewspaperPeriodicity::create($request->all());

        return redirect()->route('newspaper.newspaperPeriodicity.index')->with('success', 'Newspaper periodicity added successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Newspaper Periodicity &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspapers Periodicity List', 'url' => route('newspaper.newspaperPeriodicity.index')],
            ['label' => 'Update Newspaper Periodicity', 'url' => null],
        ];

        $newspaper_periodicity = NewspaperPeriodicity::findOrFail($id);

        return view('newspaper-periodicity.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'newspaper_periodicity' => $newspaper_periodicity
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        $newspaper_periodicity = NewspaperPeriodicity::findOrFail($id);

        $newspaper_periodicity->update($request->all());

        return redirect()->route('newspaper.newspaperPeriodicity.index')->with('success', 'Newspaper periodicity updated successfully.');
    }

    public function destroy($id)
    {
        $newspaper_periodicity = NewspaperPeriodicity::findOrFail($id);

        if ($newspaper_periodicity) {
            $newspaper_periodicity->delete();

            return response()->json(['success', 'Newspaper periodicity deleted successfully']);
        } else {
            return response()->json(['error', 'Data not found'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Newspaper Periodicity &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspapers Periodicity List', 'url' => route('newspaper.newspaperPeriodicity.index')],
            ['label' => 'Newspaper Periodicity', 'url' => null], // The current page (no URL)
        ];

        $newspaperPeriodicity = NewspaperPeriodicity::findOrFail($id);

        return view('newspaper-periodicity.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'newspaperPeriodicity' => $newspaperPeriodicity
            ]
        );
    }
}
