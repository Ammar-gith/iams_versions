<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Http\Request;
use App\Models\PublisherType;
use App\Http\Controllers\Controller;

class PublisherTypeController extends Controller
{
    public function index()
    {
        // Page Title
        $pageTitle = 'Publisher Types &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Publisher Types', 'url' => null],
        ];

        $publisherTypes = PublisherType::all();

        return view('masterData.publisher-types.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'publisherTypes' => $publisherTypes
        ]);
    }

    public function create()
    {
        $pageTitle = 'Publisher Types &#x2053; IAMS-IPR';

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Publisher Types', 'url' => route('master.publisherType.index')],
            ['label' => 'Add Publisher Types', 'url' => null],
        ];

        return view('masterData.publisher-types.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function store(Request $request)
    {
        $validated_data = $request->validate([
            'code' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        PublisherType::create($validated_data);
        return redirect()->route('master.publisherType.index')->with('success', 'Publisher Type added successfully!');
    }

    public function edit($id)
    {
        $pageTitle = 'Update Publisher Type &#x2053; IAMS-IPR';

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Publisher Types List', 'url' => route('master.publisherType.index')],
            ['label' => 'Update Publisher Type', 'url' => null],
        ];

        $publisherType = PublisherType::findOrFail($id);

        return view('masterData.publisher-types.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'publisherType' =>  $publisherType
        ]);
    }

    public function update(Request $request, $id)
    {
        $publisherType = PublisherType::findOrFail($id);
        $validated_data = $request->validate([
            'code' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);
        $publisherType->update($validated_data);

        return redirect()->route('master.publisherType.index')->with('success', 'Publisher Type updated successfully!');
    }

    public function destroy($id)
    {
        $publisherType = PublisherType::findOrFail($id);

        if ($publisherType) {
            $publisherType->delete();
            return response()->json(['success' => 'Publisher Type deleted successfully.']);
        } else {
            return response()->json(['error' => 'Publisher Type not found.'], 404);
        }
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Publisher &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Publisher Types List', 'url' => route('master.publisherType.index')],
            ['label' => 'Publisher', 'url' => null], // The current page (no URL)
        ];

        $publisherType = PublisherType::findOrFail($id);

        return view('masterData.publisher-types.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'publisherType' => $publisherType
            ]
        );
    }
}
