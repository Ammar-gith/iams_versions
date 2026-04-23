<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MediaBankDetail;
use App\Models\Newspaper;
use App\Models\NewspaperPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NewspaperPartnerController extends Controller
{
    private function assertActiveSharesTotalOneHundred(int $newspaperId): void
    {
        $active = NewspaperPartner::where('newspaper_id', $newspaperId)->where('is_active', true);
        if ($active->count() === 0) {
            return;
        }
        $sum = (float) $active->sum('share_percentage');
        if (abs($sum - 100.0) > 0.02) {
            throw ValidationException::withMessages([
                'share_percentage' => [
                    'Jis newspaper ke active partners hon, un ke shares ka majmoo 100% hona chahiye. Abhi: ' . round($sum, 2) . '%',
                ],
            ]);
        }
    }

    public function index()
    {
        $pageTitle = 'Newspaper partners — IAMS-IPR';
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspaper partners', 'url' => null],
        ];

        $partners = NewspaperPartner::with(['newspaper', 'mediaBankDetail'])
            ->orderBy('newspaper_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('masterData.newspaper-partners.index', compact('pageTitle', 'breadcrumbs', 'partners'));
    }

    public function create()
    {
        $pageTitle = 'Add newspaper partner — IAMS-IPR';
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspaper partners', 'url' => route('master.newspaperPartner.index')],
            ['label' => 'Add', 'url' => null],
        ];

        $newspapers = Newspaper::where('status', 1)->orderBy('title')->get();
        $banks = collect();

        return view('masterData.newspaper-partners.create', compact('pageTitle', 'breadcrumbs', 'newspapers', 'banks'));
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $data = $this->validatedData($request);
                NewspaperPartner::create($data);
                $this->assertActiveSharesTotalOneHundred($data['newspaper_id']);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('master.newspaperPartner.index')->with('success', 'Partner record saved.');
    }

    public function edit($id)
    {
        $partner = NewspaperPartner::findOrFail($id);
        $pageTitle = 'Edit newspaper partner — IAMS-IPR';
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspaper partners', 'url' => route('master.newspaperPartner.index')],
            ['label' => 'Edit', 'url' => null],
        ];

        $newspapers = Newspaper::where('status', 1)->orderBy('title')->get();
        $banks = MediaBankDetail::where('newspaper_id', $partner->newspaper_id)
            ->whereNull('agency_id')
            ->orderBy('bank_name')
            ->get();

        return view('masterData.newspaper-partners.edit', compact('pageTitle', 'breadcrumbs', 'newspapers', 'banks', 'partner'));
    }

    public function update(Request $request, $id)
    {
        $partner = NewspaperPartner::findOrFail($id);
        try {
            DB::transaction(function () use ($request, $partner) {
                $data = $this->validatedData($request, $partner->newspaper_id);
                $partner->update($data);
                $this->assertActiveSharesTotalOneHundred($partner->newspaper_id);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('master.newspaperPartner.index')->with('success', 'Partner record updated.');
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $partner = NewspaperPartner::findOrFail($id);
                $newspaperId = $partner->newspaper_id;
                $partner->delete();
                $this->assertActiveSharesTotalOneHundred($newspaperId);
            });
        } catch (ValidationException $e) {
            $msg = $e->errors()['share_percentage'][0] ?? 'Is newspaper ke active partners ka majmoo 100% hona zaroori hai.';

            return response()->json(['error' => $msg], 422);
        }

        return response()->json(['success' => 'Partner record deleted.']);
    }

    public function show($id)
    {
        $partner = NewspaperPartner::with(['newspaper', 'mediaBankDetail'])->findOrFail($id);
        $pageTitle = 'Newspaper partner — IAMS-IPR';
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspaper partners', 'url' => route('master.newspaperPartner.index')],
            ['label' => 'View', 'url' => null],
        ];

        return view('masterData.newspaper-partners.show', compact('pageTitle', 'breadcrumbs', 'partner'));
    }

    /**
     * AJAX: bank accounts for selected newspaper (newspaper-only rows in media_bank_details).
     */
    public function banksForNewspaper(Request $request)
    {
        $newspaperId = (int) $request->get('newspaper_id');
        if ($newspaperId < 1) {
            return response()->json(['banks' => []]);
        }
        $banks = MediaBankDetail::where('newspaper_id', $newspaperId)
            ->whereNull('agency_id')
            ->orderBy('bank_name')
            ->get(['id', 'bank_name', 'account_title', 'account_number']);

        return response()->json(['banks' => $banks]);
    }

    private function validatedData(Request $request, ?int $fixedNewspaperId = null): array
    {
        $rules = [
            'partner_name' => ['required', 'string', 'max:191'],
            'share_percentage' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'media_bank_detail_id' => ['required', 'integer', 'exists:media_bank_details,id'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];

        if ($fixedNewspaperId === null) {
            $rules['newspaper_id'] = ['required', 'integer', 'exists:newspapers,id'];
        }

        $validated = $request->validate($rules);

        $npId = $fixedNewspaperId ?? (int) $validated['newspaper_id'];

        $request->validate([
            'media_bank_detail_id' => [
                'required',
                Rule::exists('media_bank_details', 'id')->where(function ($q) use ($npId) {
                    $q->where('newspaper_id', $npId)->whereNull('agency_id');
                }),
            ],
        ]);

        if ($fixedNewspaperId !== null) {
            $validated['newspaper_id'] = $fixedNewspaperId;
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = isset($validated['sort_order']) ? (int) $validated['sort_order'] : 0;

        return $validated;
    }
}
