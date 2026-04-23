<?php

namespace App\Http\Controllers\MasterData;

use App\Models\NewsPosRate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NewsPosRateController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Newspapers Postions and Rates &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspapers Positions & Rates', 'url' => null], // The current page (no URL)
        ];

        $news_pos_rates = NewsPosRate::all();

        return view('masterData.news-pos-rates.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'news_pos_rates' => $news_pos_rates
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add NEWS Pos Rate &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'NEWS Pos Rates List', 'url' => route('master.newsPosRate.index')],
            ['label' => 'Add NEWS Pos Rate', 'url' => null], // The current page (no URL)
        ];

        return view('masterData.news-pos-rates.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'position' => 'required|string',
            'rates' => 'required|numeric'
        ]);

        $news_pos_rate = NewsPosRate::create($request->all());

        return redirect()->route('master.newsPosRate.index')->with('success', 'Newspaper position and rate added successfully');
    }


    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Newspapers Postion and Rate &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'NEWS Pos Rates List', 'url' => route('master.newsPosRate.index')],
            ['label' => 'Update Newspapers Position & Rate', 'url' => null], // The current page (no URL)
        ];

        $news_pos_rate = NewsPosRate::findOrFail($id);

        return view('masterData.news-pos-rates.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'news_pos_rate' => $news_pos_rate
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'position' => 'required|string',
            'rates' => 'required|numeric',
        ]);

        $news_pos_rate = NewsPosRate::findOrFail($id);
        $news_pos_rate->update($request->all());

        return redirect()->route('master.newsPosRate.index')->with('success', 'Newspaper position and rate updated successfully.');
    }

    public function destroy($id)
    {
        $news_pos_rate = NewsPosRate::findOrFail($id);

        if ($news_pos_rate) {
            $news_pos_rate->delete();
            return response()->json(['success' => 'Newspaper position and rate deleted successfully.']);
        } else {
            return response()->json(['error', 'Data not found!']);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Newspapers Postions and Rates &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'NEWS Pos Rates List', 'url' => route('master.newsPosRate.index')],
            ['label' => 'Newspapers Postions and Rates', 'url' => null], // The current page (no URL)
        ];

        $newsPosRate = NewsPosRate::findOrFail($id);

        return view('masterData.news-pos-rates.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'newsPosRate' => $newsPosRate
            ]
        );
    }
}
