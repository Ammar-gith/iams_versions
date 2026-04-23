<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\AdvAgency;
use App\Models\MediaBankDetail;
use App\Models\Newspaper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MediaBankDetailController extends Controller
{
    public function index()
    {
        $pageTitle = 'Media bank details — IAMS-IPR';
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Media bank details', 'url' => null],
        ];

        $rows = MediaBankDetail::with(['newspaper:id,title', 'agency:id,name'])
            ->orderByDesc('id')
            ->get();

        return view('masterData.media-bank-details.index', compact('pageTitle', 'breadcrumbs', 'rows'));
    }

    public function create()
    {
        $pageTitle = 'Add media bank detail — IAMS-IPR';
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Media bank details', 'url' => route('master.mediaBankDetail.index')],
            ['label' => 'Add', 'url' => null],
        ];

        $newspapers = Newspaper::orderBy('title')->get(['id', 'title']);
        $agencies = AdvAgency::orderBy('name')->get(['id', 'name']);

        return view('masterData.media-bank-details.create', compact('pageTitle', 'breadcrumbs', 'newspapers', 'agencies'));
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $data = $this->validated($request);
                MediaBankDetail::create($data);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('master.mediaBankDetail.index')->with('success', 'Bank detail saved.');
    }

    public function show($id)
    {
        $row = MediaBankDetail::with(['newspaper:id,title', 'agency:id,name'])->findOrFail($id);

        $pageTitle = 'Media bank detail — IAMS-IPR';
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Media bank details', 'url' => route('master.mediaBankDetail.index')],
            ['label' => 'View', 'url' => null],
        ];

        return view('masterData.media-bank-details.show', compact('pageTitle', 'breadcrumbs', 'row'));
    }

    public function edit($id)
    {
        $row = MediaBankDetail::findOrFail($id);

        $pageTitle = 'Edit media bank detail — IAMS-IPR';
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Media bank details', 'url' => route('master.mediaBankDetail.index')],
            ['label' => 'Edit', 'url' => null],
        ];

        $newspapers = Newspaper::orderBy('title')->get(['id', 'title']);
        $agencies = AdvAgency::orderBy('name')->get(['id', 'name']);

        return view('masterData.media-bank-details.edit', compact('pageTitle', 'breadcrumbs', 'row', 'newspapers', 'agencies'));
    }

    public function update(Request $request, $id)
    {
        $row = MediaBankDetail::findOrFail($id);

        try {
            DB::transaction(function () use ($request, $row) {
                $data = $this->validated($request, true);
                $row->update($data);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('master.mediaBankDetail.index')->with('success', 'Bank detail updated.');
    }

    public function destroy($id)
    {
        $row = MediaBankDetail::findOrFail($id);
        $row->delete();

        return response()->json(['success' => 'Bank detail deleted.']);
    }

    private function validated(Request $request, bool $isUpdate = false): array
    {
        $data = $request->validate([
            'media_type' => ['required', 'in:newspaper,agency'],
            'newspaper_id' => ['nullable', 'integer', 'exists:newspapers,id'],
            'agency_id' => ['nullable', 'integer', 'exists:adv_agencies,id'],
            'media_name' => ['nullable', 'string', 'max:191'],
            'bank_name' => ['required', 'string', 'max:191'],
            'account_title' => ['required', 'string', 'max:191'],
            'account_number' => ['required', 'string', 'max:191'],
        ]);

        $type = $data['media_type'];
        $np = $data['newspaper_id'] ?? null;
        $ag = $data['agency_id'] ?? null;

        if ($type === 'newspaper') {
            if (empty($np) || !empty($ag)) {
                throw ValidationException::withMessages([
                    'newspaper_id' => ['Newspaper select karna zaroori hai.'],
                    'agency_id' => ['Agency empty honi chahiye jab type Newspaper ho.'],
                ]);
            }
            $data['agency_id'] = null;
        } else {
            if (empty($ag) || !empty($np)) {
                throw ValidationException::withMessages([
                    'agency_id' => ['Agency select karna zaroori hai.'],
                    'newspaper_id' => ['Newspaper empty hona chahiye jab type Agency ho.'],
                ]);
            }
            $data['newspaper_id'] = null;
        }

        unset($data['media_type']);

        return $data;
    }
}

