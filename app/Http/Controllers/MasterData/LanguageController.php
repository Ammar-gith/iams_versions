<?php

namespace App\Http\Controllers\MasterData;

use App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LanguageController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Languages &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Languages', 'url' => null], // The current page (no URL)
        ];

        $languages = Language::all();

        return view('masterData.languages.index',[
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'languages' => $languages
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Language &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Languages List', 'url' => route('master.language.index')],
            ['label' => 'Add Language', 'url' => null], // The current page (no URL)
        ];

        return view('masterData.languages.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required'
        ]);

        $languages = Language::create($request->all());

        return redirect()->route('master.language.index')->with('success', 'Language added successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Language &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Languages List', 'url' => route('master.language.index')],
            ['label' => 'Update Language', 'url' => null], // The current page (no URL)
        ];

        $language = Language::findOrFail($id);

        return view('masterData.languages.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'language' => $language
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required'
        ]);

        $language = Language::findOrFail($id);
        $language->update($request->all());

        return redirect()->route('master.language.index')->with('success', 'language updated successfully.');
    }

    public function destroy($id)
    {
        $language = Language::findOrFail($id);

        if ($language) {
            $language->delete();

            return response()->json(['success', 'Language deleted successfully!']);
        } else {
            return response()->json(['error', 'Language is not found']);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Update Language &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Languages List', 'url' => route('master.language.index')],
            ['label' => 'Update Language', 'url' => null], // The current page (no URL)
        ];

        $language = Language::findOrFail($id);

        return view('masterData.languages.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'language' => $language
            ]
        );
    }
}
