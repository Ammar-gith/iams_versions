<?php

namespace App\Http\Controllers;

use App\Models\NewspaperCategory;
use Illuminate\Http\Request;

class NewspaperCategoryController extends Controller
{
    public function index()
    {
        // Page title
        $pageTitle = 'Newspaper Categories &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspaper Categories', 'url' => null],
        ];

        $newspaper_categories = NewspaperCategory::all();

        return view('newspaper-categories.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'newspaper_categories' => $newspaper_categories
        ]);
    }

    public function create()
    {
        // Page title
        $pageTitle = 'Add Newspaper Category &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspapers Categories List', 'url' => route('newspaper.newspaperCategory.index')],
            ['label' => 'Add Newspaper Category', 'url' => null],
        ];

        return view('newspaper-categories.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        $newspaper_category = NewspaperCategory::create($request->all());

        return redirect()->route('newspaper.newspaperCategory.index')->with('success', 'Newspaper Category added successfully.');
    }

    public function edit($id)
    {
        // Page title
        $pageTitle = 'Update Newspaper Category &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspapers Categories List', 'url' => route('newspaper.newspaperCategory.index')],
            ['label' => 'Update Newspaper Category', 'url' => null],
        ];

        $newspaper_category = NewspaperCategory::findOrFail($id);

        return view('newspaper-categories.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'newspaper_category' => $newspaper_category
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        $newspaper_category = NewspaperCategory::findOrFail($id);
        $newspaper_category->update($request->all());

        return redirect()->route('newspaper.newspaperCategory.index')->with('success', 'Newspaper Category updated successfully.');
    }

    public function destroy($id)
    {
        $newspaper_category = NewspaperCategory::findOrFail($id);

        if ($newspaper_category) {
            $newspaper_category->delete();

            return response()->json(['success', 'Newspaper category deleted successfully']);
        } else {
            return response()->json(['error', 'Newspaper category Not found'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Newspaper Category &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspapers Categories List', 'url' => route('newspaper.newspaperCategory.index')],
            ['label' => 'Newspaper Category', 'url' => null],
        ];

        $newspaperCategory = NewspaperCategory::findOrFail($id);

        return view('newspaper-categories.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'newspaperCategory' => $newspaperCategory
        ]);
    }

}
