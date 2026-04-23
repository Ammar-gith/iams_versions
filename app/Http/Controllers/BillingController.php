<?php

namespace App\Http\Controllers;

// use excel;
use App\Exports\AdvertisementsExport;
use App\Models\AdCategory;
use App\Models\AdvAgency;
use App\Models\Advertisement;
use App\Models\AdWorthParameter;
use App\Models\BillClassifiedAd;
use App\Models\ClassifiedAdType;
use App\Models\Department;
use App\Models\Newspaper;
use App\Models\NewsPosRate;
use App\Models\Office;
use App\Models\Status;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use NumberFormatter;





class BillingController extends Controller
{
    private function applyAgencyFilters($query, $request)
    {


        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('invoice_date', 'like', "%{$search}%")
                    ->orWhere('printed_bill_cost', 'like', "%{$search}%")
                    ->orWhere('printed_total_bill', 'like', "%{$search}%")

                    ->orWhereHas('advertisement.office', function ($sub) use ($search) {
                        $sub->where('ddo_name', 'like', "%{$search}%");
                    })

                    ->orWhereHas('user.agency', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }



        if ($request->filled('inf_number')) {
            $query->whereHas('advertisement', function ($q) use ($request) {
                $q->where('inf_number', 'like', '%' . $request->inf_number . '%');
            });
        }


        if ($request->filled('department_id')) {
            $query->whereHas('advertisement', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }



        if ($request->filled('office_id')) {
            $query->whereHas('advertisement', function ($q) use ($request) {
                $q->where('office_id', $request->office_id);
            });
        }



        if ($request->filled('status_id')) {
            $query->whereHas('advertisement', function ($q) use ($request) {
                $q->where('status_id', $request->status_id);
            });
        }



        if ($request->filled('publication_date')) {

            $dates = explode(' to ', $request->publication_date);

            if (count($dates) == 2) {

                $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $to   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

                $query->whereBetween('publication_date', [$from, $to]);
            }
        }


        if ($request->filled('submission_date')) {

            $dates = explode(' to ', $request->submission_date);

            if (count($dates) == 2) {

                $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $to   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

                $query->whereBetween('invoice_date', [$from, $to]);
            }
        }
    }
    // Show All Ads
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('inf_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('department', fn($dq) => $dq->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('office', fn($oq) => $oq->where('ddo_name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('classified_ad_type', fn($ct) => $ct->where('type', 'LIKE', "%{$search}%"));
            });
        }

        if ($request->filled('inf_number')) {
            $query->where('inf_number', $request->inf_number);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }
        if ($request->filled('classified_ad_type_id')) {
            $query->where('classified_ad_type_id', $request->classified_ad_type_id);
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->filled('submission_date')) {

            $dates = explode(' to ', $request->submission_date);

            if (count($dates) == 2) {
                $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $to = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

                $query->whereBetween('created_at', [$from, $to]);
            }
        }
        if ($request->filled('publication_date')) {

            $dates = explode(' to ', $request->publication_date);

            if (count($dates) == 2) {
                $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $to = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

                $query->whereBetween('publish_on_or_before', [$from, $to]);
            }
        }


        return $query;
    }
    // private function applyFilters($query, Request $request)
    // {
    //     if ($request->filled('search')) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('inf_number', 'LIKE', "%{$search}%")
    //                 ->orWhereHas('department', fn($dq) => $dq->where('name', 'LIKE', "%{$search}%"))
    //                 ->orWhereHas('office', fn($oq) => $oq->where('ddo_name', 'LIKE', "%{$search}%"))
    //                 ->orWhereHas('status', fn($sq) => $sq->where('title', 'LIKE', "%{$search}%"))
    //                 ->orWhereHas('newspapers', fn($nq) => $nq->where('publication_date', 'LIKE', "%{$search}%"));
    //         });
    //     }

    //     if ($request->filled('inf_number')) {
    //         $query->where('inf_number', $request->inf_number);
    //     }

    //     if ($request->filled('department_id')) {
    //         $query->where('department_id', $request->department_id);
    //     }

    //     if ($request->filled('office_id')) {
    //         $query->where('office_id', $request->office_id);
    //     }

    //     if ($request->filled('status_id')) {
    //         $query->where('status_id', $request->status_id);
    //     }

    //     // Submission date filter (related newspapers table)
    //     if ($request->filled('submission_date')) {
    //         $dates = explode(' to ', $request->submission_date);
    //         $from = $dates[0] ?? null;
    //         $to   = $dates[1] ?? null;

    //         if ($from && $to) {
    //             $query->whereHas('newspapers', function ($nq) use ($from, $to) {
    //                 $nq->whereBetween('submission_date', [$from, $to]);
    //             });
    //         }
    //     }

    //     // Publication date filter (from bill_classified_ads table)
    //     if ($request->filled('publication_date')) {

    //         $dates = explode(' to ', $request->publication_date);

    //         if (count($dates) == 2) {

    //             $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
    //             $to   = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

    //             $query->whereHas('billClassifiedAds', function ($q) use ($from, $to) {
    //                 $q->whereBetween('publication_date', [$from, $to]);
    //             });
    //         }
    //     }
    //     return $query;
    // }

    // Billing Newspapers
    // public function index(Request $request)
    // {
    //     // Page Title
    //     $pageTitle = 'Newspapers Bills Requests &#x2053; DG&#8211;IPR IAMS';

    //     // Breadcrumb
    //     $breadcrumbs = [
    //         ['label' => '<i class="menu-icon tf-icons bx-home-circle"></i>', 'url' => route('dashboard')],
    //         ['label' => 'Bills Requests', 'url' => null], // The current page (no URL)
    //     ];

    //     $user = auth()->user();
    //     $userofficeid = $user->office_id;

    //     $userRole = $user->roles->pluck('name')->first();

    //     if ($userRole == 'Client Office') {
    //         $billClassifiedAds = Advertisement::whereHas('newspapers', function ($query) use ($userofficeid) {
    //             $query->whereNull('agency_id')
    //                 ->where('office_id', $userofficeid)
    //                 ->where('is_published', 1)
    //                 ->where('bill_submitted_to_role_id', 2); // role id 2 for client office
    //         })->latest()->get();
    //     } else {
    //         $billClassifiedAds = Advertisement::whereHas('newspapers', function ($query) {
    //             $query->whereNull('agency_id')
    //                 ->where('is_published', 1);
    //         })->orderByRaw('CASE WHEN bill_submitted_to_role_id IS NULL THEN 0 ELSE 1 END') // NULL values first
    //             ->latest()
    //             ->get();
    //     }

    //     // Apply search/filters
    //     $this->applyFilters($billClassifiedAds, $request);
    //     // Get data for dropdowns
    //     $statuses   = Status::all(); // all statuses
    //     $departments = Department::all(); // all departments (adjust as needed)
    //     $offices     = Office::all();     // all offices

    //     return view('billing-newspapers.index', [
    //         'billClassifiedAds' => $billClassifiedAds,
    //         'pageTitle' => $pageTitle,
    //         'breadcrumbs' => $breadcrumbs,
    //         'statuses' => $statuses,
    //         'departments' => $departments,
    //         'offices' => $offices,
    //     ]);
    // }

    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        $userofficeid = $user->office_id;
        $userRole = $user->roles->pluck('name')->first();

        $pageTitle = 'Newspapers Bills Requests &#x2053; DG&#8211;IPR IAMS';

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Billings', 'url' => null],
            ['label' => 'Bills Requests', 'url' => null],
        ];

        $billClassifiedAdsQuery = Advertisement::query()
            ->with('billClassifiedAds')
            ->whereHas('newspapers', function ($query) use ($userRole, $userofficeid) {
                $query->whereNull('agency_id')
                    ->where('is_published', 1);

                if ($userRole === 'Client Office') {
                    $query->where('office_id', $userofficeid)
                        ->where('bill_submitted_to_role_id', 2);
                }
            });

        $this->applyFilters($billClassifiedAdsQuery, $request);

        if ($userRole !== 'Client Office') {
            $billClassifiedAdsQuery
                ->orderByRaw('CASE WHEN bill_submitted_to_role_id IS NULL THEN 0 ELSE 1 END');
        }

        $billClassifiedAds = $billClassifiedAdsQuery
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        $statuses = Status::all();
        $departments = Department::all();
        $offices = Office::all();
        $classifiedAdTypes = ClassifiedAdType::all();

        return view('billing-newspapers.index', [
            'billClassifiedAds' => $billClassifiedAds,
            'pageTitle'         => $pageTitle,
            'breadcrumbs'       => $breadcrumbs,
            'statuses'          => $statuses,
            'departments'       => $departments,
            'offices'           => $offices,
            'classifiedAdTypes' => $classifiedAdTypes,
        ]);
    }

    public function billDetail($advertisementId)
    {
        // Page Title
        $pageTitle = 'Newspapers Bills List &#x2053; DG&#8211;IPR IAMS';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Bills Requests', 'url' => route('billings.newspapers.index')],
            ['label' => 'Bills List', 'url' => null], // The current page (no URL)
        ];

        $advertisement = Advertisement::findOrFail($advertisementId);

        $billdetails = BillClassifiedAd::where('advertisement_id', $advertisement->id)->get();

        $inf_number = $billdetails->first()?->advertisement?->inf_number;

        return view('billing-newspapers.bills-details', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'billdetails' => $billdetails,
            'inf_number' =>     $inf_number,
        ]);
    }

    public function create($id)
    {
        // Page title
        $pageTitle = 'Media Edit Form &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Media Edit Form', 'url' => null], // The current page (no URL)
        ];

        $advertisement = Advertisement::with('newspapers')->findOrFail($id);
        $user = auth()->user();
        $loggedNewspaperId = $user->newspaper_id;
        // Determine which NP_log is filled
        $authorizedNewspapers = [];
        if (!empty($advertisement->sec_NP_log)) {
            //  Sec log exists → highest authority wins
            $authorizedNewspapers = $advertisement->sec_NP_log;
        } elseif (!empty($advertisement->dg_NP_log)) {
            //  If Sec not filled, then DG log wins
            $authorizedNewspapers = $advertisement->dg_NP_log;
        } elseif (!empty($advertisement->dd_NP_log)) {
            //  If DG not filled, then DD log wins
            $authorizedNewspapers = $advertisement->dd_NP_log;
        }

        // 🔑 Authorization Check
        if ($user->hasRole('Media')) {
            if ($loggedNewspaperId) {
                //  Newspaper Media user
                if (!in_array($loggedNewspaperId, $authorizedNewspapers)) {
                    abort(403, 'You are not authorized to edit this advertisement.');
                }
            } elseif ($loggedAgencyId) {
                //  Adv Agency Media user
                if ($advertisement->adv_agency_id != $loggedAgencyId) {
                    abort(403, 'You are not authorized to edit this advertisement.');
                }
            }
        }


        $rate = Newspaper::where('id', $loggedNewspaperId)->value('rate');
        $loggedAgencyId = $user->adv_agency_id;
        // $estimatedCost = Advertisement::whereJsonContains('newspaper_id', $id)->value('current_bill');
        $newspaperLanguage = Newspaper::where('id', $loggedNewspaperId)->value('language_id');

        if ($newspaperLanguage == 1) {
            //assuming 1 is Urdu
            $originalSize = $advertisement->urdu_size;
            $originalSpace = $advertisement->urdu_space;
        } elseif ($newspaperLanguage == 2) {
            // assuming 2 is english
            $originalSize = $advertisement->english_size;
            $originalSpace = $advertisement->english_space;
        } else {
            $originalSize = null;
            $originalSpace = null;
        }


        $loggedNewspaper = Newspaper::findOrFail($loggedNewspaperId);

        $uniqueNewspaperBill = 0;
        $rateWithPlacement = $rate; // By default no placement effects
        $kpraTax = 0;              // Default KPRA tax

        if ($loggedNewspaper && $originalSize) {
            if ($advertisement->news_pos_rate_id) {
                $placementRate = NewsPosRate::where('id', $advertisement->news_pos_rate_id)->value('rates');


                if ($placementRate) {
                    $ratePlacement = $rate *  ($placementRate / 100);
                    $rateWithPlacement = $rate + $ratePlacement;
                }
            }

            // newspaperBill = rate * size
            $uniqueNewspaperBill = $rateWithPlacement * $originalSize;

            // Apply kpra tax if register
            if ($loggedNewspaper->register_with_kapra === 'Yes') {
                $kpraTax = $uniqueNewspaperBill * 0.02;

                $uniqueFinalBill = $uniqueNewspaperBill + $kpraTax;
            } else {
                $uniqueFinalBill = $uniqueNewspaperBill;
            }
        }

        // Ad categories
        $ad_categories = AdCategory::all();

        // Ad worth parameters
        $ad_worth_parameters = AdWorthParameter::all();

        //Placement/Position
        $news_pos_rates = NewsPosRate::all();

        //Newspapers
        // $newspapers = Newspaper::all();

        //Adv Agencies
        $adv_agencies = AdvAgency::all();

        $statuses = Status::all();

        //by default insertion will be 1 for each each newspaper
        $insertion = 1; // Default insertion value

        if ($loggedNewspaperId) {
            // Newspaper Media user → only their newspaper
            $newspapers = Newspaper::where('id', $loggedNewspaperId)->get();
        } elseif ($loggedAgencyId) {
            // Adv Agency Media user → only their agency
            $newspapers = collect(); // no newspapers for agencies
            $adv_agencies = AdvAgency::where('id', $loggedAgencyId)->get();
        } else {
            // fallback: show nothing
            $newspapers = collect();
            $adv_agencies = collect();
        }

        return view('billing-newspapers.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advertisement' => $advertisement,
            'loggedNewspaperId' =>  $loggedNewspaperId,
            'loggedNewspaper' =>  $loggedNewspaper,
            'user' => $user,
            'ad_categories' => $ad_categories,
            'ad_worth_parameters' => $ad_worth_parameters,
            'news_pos_rates' => $news_pos_rates,
            'newspapers' => $newspapers,
            'adv_agencies' => $adv_agencies,
            'statuses' => $statuses,
            'rate' => $rate,
            'insertion' =>  $insertion,
            'kpraTax' =>  $kpraTax,
            'uniqueFinalBill' => $uniqueFinalBill,
            'originalSize' => $originalSize,
            'originalSpace' => $originalSpace,
            'placementRate' => $placementRate,
        ]);
    }

    public function store(Request $request, $id)
    {
        // dd($request->all());
        $user = auth()->user();
        // $userId = $user->id;
        $userName = $user->name;
        // dd($user);
        $newspaperId = (array) $user->newspaper_id;
        // $request->validate([
        //     'action' => 'required|in:publish,un-publish',
        //     'newspaper_id' => 'required|integer|exists:newspapers,id',
        //     'invoice_no' => 'nullable|integer',
        //     'invoice_date' => 'nullable|date',
        //     'size' => 'nullable|numeric',
        //     'printed_size' => 'nullable|integer',
        //     'rate' => 'nullable|integer',
        //     'printed_rate' => 'nullable|integer',
        //     'rate' => 'nullable|numeric',
        //     'no_of_insertion' => 'nullable|integer',
        //     'printed_no_of_insertion' => 'nullable|interger',
        //     'estimated_cost' => 'nullable|numeric',
        //     'printed_bill_cost' => 'nullable|numeric',
        //     'kpra_tax' => 'nullable|integer',
        //     'printed_total_bill' => 'nullable|numeric',
        //     'publication_date' => 'nullable|date',
        // ]);
        // Security check
        if ($request->newspaper_id != auth()->user()->newspaper_id) {
            abort(403, 'Unauthorized newspaper access.');
        }

        $advertisement = Advertisement::findOrFail($id);

        // Determine the clicked action
        $isPublished = $request->action === 'publish' ? 1 : 0;
        $publishedAt = $isPublished ? now() : null;

        // only update the specific newspaper
        $advertisement->newspapers()->syncWithoutDetaching([
            $request->newspaper_id => [
                'is_published' => $isPublished,
                'published_at' => $publishedAt,
            ]
        ]);
        // Store or update billing
        $billClassifiedAd = BillClassifiedAd::updateOrCreate(
            [
                'advertisement_id' => $advertisement->id,
                'user_id' => $user->id,
                // 'newspaper_id' => $newspaperId,
            ],
            [
                'inf_number' => $advertisement->inf_number,
                'invoice_no' => $request->invoice_no,
                'invoice_date' => $request->invoice_date,
                'original_space' =>  $request->original_space,
                'size' =>  $request->size,
                'printed_size' => $request->printed_size,
                'rate' => $request->rate,
                'printed_rate' => $request->printed_rate,
                'publication_date' => $request->publication_date,
                'no_of_insertion' => $request->no_of_insertion,
                'printed_no_of_insertion' => $request->printed_no_of_insertion,
                'estimated_cost' =>  $request->estimated_cost,
                'printed_bill_cost' =>  $request->printed_bill_cost,
                'kpra_tax' => $request->kpra_tax,
                'printed_total_bill' =>  $request->printed_total_bill,
                'scanned_bill' => $request->scanned_bill,
                'publication_date' =>  $request->publication_date,
                'advertisement_id' => $advertisement->id,
                'newspaper_id' => $newspaperId,
                'status' => 'billed',
            ]
        );

        $billClassifiedAd->addAllMediaFromTokens();

        dispatch(function () use ($billClassifiedAd, $userName) {

            foreach (User::Assistants() as $assistant) {
                notifyUser($assistant, [
                    'title' => 'New Bill Submitted.',
                    'message' => 'A new bill with invoice number ' . $billClassifiedAd->invoice_no . ' has been submitted by ' . $userName . ' .',
                    'url' => url('billing-newspapers/show', ['id' => $billClassifiedAd->id])
                ]);
            }
        });

        return redirect()->route('advertisements.index')->with('success', 'Bill generated successfully!');
    }

    public function edit()
    {
        return view('billing-newspapers.edit');
    }

    public function show($id)
    {
        // Page title
        $pageTitle = 'Bill Show &#x2053; IAMS-IPR';

        // Data
        $billDetailShow = BillClassifiedAd::findOrFail($id);
        $advertisement = $billDetailShow->advertisement;
        $inf_number = $advertisement->inf_number;

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Bills Requests', 'url' => route('billings.newspapers.index')],
            ['label' => 'Bills List', 'url' => route('billings.newspapers.bill.detail', $advertisement->id)],
            ['label' => 'Bill details', 'url' => null], // The current page (no URL)
        ];

        return view('billing-newspapers.show', [
            'billDetailShow' => $billDetailShow,
            'pageTitle' =>  $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'inf_number' => $inf_number,
        ]);
    }

    public function printNewspaperBill($billClassifiedAdId)
    {
        try {
            // $advertisement = Advertisement::with('newspapers')->findOrFail($id);

            // // All bills already submitted for this advertisement
            // $billdetails = BillClassifiedAd::where('advertisement_id', $id)
            //     ->with(['user', 'user.newspaper', 'advertisement'])
            //     ->get();

            // Get IDs of newspapers that already billed (adjust field based on your schema)
            // $billedNewspaperIds = $billdetails->pluck('user_id')->filter()->toArray();

            $advertisement = Advertisement::findOrFail($billClassifiedAdId);

            $billdetails = BillClassifiedAd::where('advertisement_id', $advertisement->id)->get();


            // Prepare arrays to store media for all newspapers
            $scanned_bills = [];
            $press_cuttings = [];
            $newspaperTitles = [];

            // Loop through each bill and collect media
            foreach ($billdetails as $bill) {
                $ids = $bill->newspaper_id ?? [];   // If null → empty array

                foreach ($ids as $nid) {
                    if ($nid) {
                        $newspaperTitles[$bill->id][] = Newspaper::find($nid)->title;
                    }
                }

                $scanned_bills[] = $bill->getMedia('scanned_bill');
                $press_cuttings[] = $bill->getMedia('press_cutting');
            }


            // $publicationDate = $billdetails->value('publication_date');

            $inf_number = $billdetails->first()?->advertisement?->inf_number;

            // Always fetch Deputy Director
            $dd = User::where('name', 'Deputy.D Zarali')->first();

            $ddSignature = $dd && $dd->image
                ? storage_path('app/public/' . $dd->image)
                : null;



            // Totals
            $grandTotal = $billdetails->sum(function ($b) {
                // change to your column for final amount
                return round($b->printed_total_bill) ?? 0;
            });

            // Split into rupees and paisa
            $rupees = floor($grandTotal);
            $paisa  = round(($grandTotal - $rupees) * 100);

            // Create formatter
            $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            // Convert to words
            $rupeesWords = ucfirst($formatter->format($rupees)) . ' rupees';
            $paisaWords  = $paisa > 0 ? ' and ' . $formatter->format($paisa) . ' paisa' : '';

            $grandTotalWords = $rupeesWords . ' only.';



            $data = [
                'advertisement' => $advertisement,
                'billdetails' => $billdetails,
                'newspaperTitles' => $newspaperTitles,
                'scanned_bills' => $scanned_bills,
                'press_cuttings' => $press_cuttings,
                'grandTotal' => $grandTotal,
                'grandTotalWords' => $grandTotalWords,
                // 'pendingNewspapers' => $pendingNewspapers,
                'inf_number' => $inf_number,
                'ddSignature' =>  $ddSignature,
                'dd' => $dd,
                // 'publicationDate' => $publicationDate,
                'generatedAt' => Carbon::now(),
            ];


            // If you need remote images enabled:
            // $pdf = Pdf::loadView('billing-newspapers.print', $data)
            //     ->setPaper('A3', 'portrait'); // or 'landscape'

            // Stream in browser to allow print/download from the browser PDF viewer
            $billNo = $advertisement->inf_number ?? 'Bill';

            // For display in the PDF (safe, keep /)
            $displayBillNo = $billNo;

            // For filename (replace / with - just for saving/streaming)
            $fileBillNo = str_replace(['/', '\\'], '-', $billNo);

            $pdf = Pdf::loadView('billing-newspapers.print', compact('advertisement', 'billdetails', 'newspaperTitles', 'scanned_bills', 'press_cuttings', 'displayBillNo', 'grandTotal', 'grandTotalWords', 'ddSignature', 'dd'))
                ->setPaper('A3', 'portrait'); // or 'landscape'

            // When streaming/downloading, use safe filename
            return $pdf->stream("Newspaper-bill-{$fileBillNo}.pdf");
            // or use ->download('Bill-....pdf') to force download
        } catch (\Exception $e) {
            // Log the full error for debugging
            Log::error('PDF Generation Failed for BillClassifiedAd ID: ' . $billClassifiedAdId, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Show a user-friendly message and redirect back
            return redirect()->back()->with('error', 'Unable to generate the newspaper bill. The required document images are missing. Please contact the administrator.');
        }
    }

    // Billing Agencies
    // public function agencyIndex(Request $request)
    // {
    //     // Page Title
    //     $pageTitle = 'Newspapers Bills Requests &#x2053; DG&#8211;IPR IAMS';

    //     // Breadcrumb
    //     $breadcrumbs = [
    //         ['label' => '<i class="menu-icon tf-icons bx-home-circle"></i>', 'url' => route('dashboard')],
    //         ['label' => 'Bills Requests', 'url' => null], // The current page (no URL)
    //     ];

    //     $user = auth()->user();
    //     // $userAgencyId = $user->adv_agency_id;

    //     // $billClassifiedAds = Advertisement::with('billClassifiedAds')
    //     //     ->whereHas('newspapers', function ($query) {
    //     //         $query->whereNotNull('agency_id')
    //     //             ->where('is_published', 1);
    //     //     })->get();
    //     if ($user->hasRole('Client Office')) {
    //         // Get the user's agency ID
    //         $userAgencyId = $user->adv_agency_id;
    //         $userofficeid = $user->office_id;

    //         // Get bills where the related advertisement's user has the same agency ID
    //         $billClassifiedAds = BillClassifiedAd::with(['advertisement.user'])
    //             ->whereHas('advertisement', function ($query) use ($userofficeid) {
    //                 $query->whereHas('user', function ($q) use ($userofficeid) {
    //                     $q->where('office_id', $userofficeid)
    //                         ->where('bill_submitted_to_role_id', 2); // role id 2 for client office
    //                 });
    //             })
    //             ->whereNotNull('total_amount_with_taxes')
    //             ->latest()
    //             ->get();
    //     } else {

    //         // Get non-submitted bills (where advertisement.bill_submitted_to_role_id is NULL)
    //         $nonSubmittedBills = BillClassifiedAd::with(['advertisement'])
    //             ->whereNotNull('total_amount_with_taxes')
    //             ->whereHas('advertisement', function ($query) {
    //                 $query->whereNull('bill_submitted_to_role_id');
    //             })
    //             ->latest()
    //             ->get();

    //         // Get submitted bills (where advertisement.bill_submitted_to_role_id is NOT NULL)
    //         $submittedBills = BillClassifiedAd::with(['advertisement'])
    //             ->whereNotNull('total_amount_with_taxes')
    //             ->whereHas('advertisement', function ($query) {
    //                 $query->whereNotNull('bill_submitted_to_role_id');
    //             })
    //             ->latest()
    //             ->get();

    //         // Combine them: non-submitted first, then submitted
    //         $billClassifiedAds = $nonSubmittedBills->concat($submittedBills);
    //     }
    //     // // Pull only agency-published bills
    //     // $billClassifiedAds = BillClassifiedAd::with('advertisement')
    //     //     ->whereNotNull('newspaper_id') // your JSON column
    //     //     ->get();

    //     // $billClassifiedAds = BillClassifiedAd::where('advertisement_id', $advertisement->id)->get();

    //     // Apply search/filters
    //     $this->applyFilters($billClassifiedAds, $request);
    //     // Get data for dropdowns
    //     $statuses   = Status::all(); // all statuses
    //     $departments = Department::all(); // all departments (adjust as needed)
    //     $offices     = Office::all();     // all offices

    //     // dd($billClassifiedAds);
    //     return view('billing-agencies.index', [
    //         'billClassifiedAds' => $billClassifiedAds,
    //         'pageTitle' => $pageTitle,
    //         'breadcrumbs' => $breadcrumbs,
    //         'statuses' => $statuses,
    //         'departments' => $departments,
    //         'offices' => $offices,
    //     ]);
    // }

    public function agencyIndex(Request $request)
    {
        $pageTitle = 'Newspapers Bills Requests &#x2053; DG&#8211;IPR IAMS';

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Billings', 'url' => null],
            ['label' => 'Bills Requests', 'url' => null],
        ];

        $user = auth()->user();



        $query = BillClassifiedAd::with([
            'advertisement.office',
            'advertisement.department',
            'advertisement.status',
            'advertisement.user',
            'user.agency'
        ])
            ->join('advertisements', 'bill_classified_ads.advertisement_id', '=', 'advertisements.id')
            ->whereNotNull('bill_classified_ads.total_amount_with_taxes')
            ->select('bill_classified_ads.*');



        if ($user->hasRole('Client Office')) {

            $userofficeid = $user->office_id;

            $query->whereHas('advertisement.user', function ($q) use ($userofficeid) {
                $q->where('office_id', $userofficeid)
                    ->where('bill_submitted_to_role_id', 2);
            });
        } else {

            $query->where(function ($q) {
                $q->whereHas('advertisement', function ($sub) {
                    $sub->whereNull('bill_submitted_to_role_id');
                })
                    ->orWhereHas('advertisement', function ($sub) {
                        $sub->whereNotNull('bill_submitted_to_role_id');
                    });
            });
        }



        $this->applyAgencyFilters($query, $request);

        $billClassifiedAds = $query->orderByRaw('advertisements.bill_submitted_to_role_id IS NOT NULL')->get();

        $statuses    = Status::all();
        $departments = Department::all();
        $offices     = Office::all();

        return view('billing-agencies.index', compact(
            'billClassifiedAds',
            'pageTitle',
            'breadcrumbs',
            'statuses',
            'departments',
            'offices'
        ));
    }


    // Agency bill details
    public function AgencyBillDetail($billClassifiedAId)
    {
        // Page Title
        $pageTitle = 'Agency Bills List &#x2053; DG&#8211;IPR IAMS';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Bills Requests', 'url' => route('billings.agencies.index')],
            ['label' => 'Bills List', 'url' => null], // The current page (no URL)
        ];

        $billClassifedAd = BillClassifiedAd::findOrFail($billClassifiedAId);
        // dd($billClassifiedAId);

        $billdetails = BillClassifiedAd::with('advertisement')
            ->where('advertisement_id', $billClassifedAd->advertisement_id)
            ->get();

        $inf_number = $billdetails->first()?->advertisement?->inf_number;

        return view('billing-agencies.bill-details', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'billdetails' => $billdetails,
            'inf_number' =>     $inf_number,
        ]);
    }


    public function agencyCreate($id)
    {

        // Page title
        $pageTitle = 'Agency Bill &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Newspaper bill details', 'url' => null], // The current page (no URL)
        ];

        $advertisement = Advertisement::with('newspapers')->findOrFail($id);
        $user = auth()->user();
        // get logged in agency
        $agency = AdvAgency::find($user->adv_agency_id);
        $loggedAgencyId = $user->adv_agency_id;




        // 🔑 Authorization Check
        if ($advertisement->adv_agency_id !== $loggedAgencyId) {
            abort(403, 'Unauthorized');
        }

        $billDate = $billClassifiedAd->bill_date ?? now()->toDateString();

        $inf_number = $advertisement->inf_number;

        $dgNewspaperIds = $advertisement->dg_NP_log ?? [];
        $ddNewspaperIds = $advertisement->dd_NP_log ?? [];

        if (!empty($dgNewspaperIds)) {
            $newspaperIds = $dgNewspaperIds;
        } elseif (!empty($ddNewspaperIds)) {
            $newspaperIds = $ddNewspaperIds;
        } else {
            $newspaperIds = [];
        }


        // Count insertions (1 per newspaper)
        $totalInsertions = count($newspaperIds);
        $totalCurrentBill = $advertisement->current_bill ?? 0;

        $kpraTaxNewspaper = 0.02; // 2% kpra tax on newspaper rates
        $kpraTaxAgency = 0.10; // 10% kpra tax on agency commission


        $newspapers = Newspaper::whereIn('id', $newspaperIds)->get();

        $newspaperDetails = [];
        $newspaperShare = 0;
        $agencyShare = 0;
        $newspaperTaxAmount = 0;
        $agencyTaxAmount = 0;
        $overAllNewspaperAmount = 0;
        // divdie the tatal current bill into number of newspapers by its rates to get each newspaper bill
        foreach ($newspapers as $newspaper) {
            $newspaperTaxAmount = 0;
            $agencyTaxAmount = 0;
            $placementRate = null;
            $placementPosition = null;
            $rateWithPlacement = $newspaper->rate;
            // pick correct size base on newpaper language
            if ($newspaper->language_id == 1) {
                // id 1 is urdu
                $size = $advertisement->urdu_size;
                $space = $advertisement->urdu_space;
            } elseif ($newspaper->language_id == 2) {
                $size = $advertisement->english_size;
                $space = $advertisement->english_space;
            }

            if ($advertisement->news_pos_rate_id) {
                $placement = NewsPosRate::select('rates', 'position')
                    ->where('id', $advertisement->news_pos_rate_id)
                    ->first();
                $placementRate = $placement->rates;
                $placementPosition = $placement->position;
                if ($placementRate) {
                    $ratePlacement = $newspaper->rate  *  ($placementRate / 100);
                    $rateWithPlacement = $newspaper->rate  + $ratePlacement;
                }
            }

            // base calculation of each newspaper bill
            $baseAmount = $rateWithPlacement * $size;
            $numberOfInsertions = 1;


            // Divide baseAmount into 85% and 15% for newspaper and agency respectively
            $newspaperShare = $baseAmount * 0.85; // 85% to newspaper
            $agencyShare = $baseAmount * 0.15;    // 15% to agency

            // dd($baseAmount, $newspaperShare, $agencyShare);
            $taxAmount = 0;
            $tatolBaseAmountWithTax = 0;
            // $baseAmountWithKpra = $baseAmount;
            // apply kpra tax if registered with kpra
            if ($newspaper->register_with_kapra === 'Yes') {
                $newspaperTaxAmount =  $newspaperShare * $kpraTaxNewspaper;

                $newspaperAmountWithKpra = $baseAmount + $newspaperTaxAmount;
                // dd($newspaperAmountWithKpra);
            }
            if ($agency->registered_with_kpra == 1) {
                $agencyTaxAmount = $agencyShare * $kpraTaxAgency;
                $agencyAmountWithKpra = $baseAmount + $agencyTaxAmount;
                // dd($agencyAmountWithKpra);
            }

            $totalTaxAmount = $newspaperTaxAmount + $agencyTaxAmount;
            $tatolBaseAmountWithTax = $baseAmount + $newspaperTaxAmount + $agencyTaxAmount;


            // Store details for frontend
            $newspaperDetails[] = [
                'id' => $newspaper->id,
                'title'        => $newspaper->title,
                'language'     => $newspaper->language,
                'rate'         => $newspaper->rate,
                'space_used' =>   $space,
                'size_used'    => $size,
                'placement_rates' => $placementRate,
                'placement_position' => $placementPosition,
                'rate_with_placement' => $rateWithPlacement,
                'numberOfInsertions' => $numberOfInsertions,
                'base_amount'  => $baseAmount,
                'newspaper_share_amounts' => $newspaperShare,
                'newspaper_tax_amount' => $newspaperTaxAmount,
                'agency_share_amounts' => $agencyShare,
                'agency_tax_amount' => $agencyTaxAmount,
                'tatolBaseAmountWithTax' => $tatolBaseAmountWithTax,
                'kpra'         => $newspaper->register_with_kpra,
                'agency_kpra' => $agency->registered_with_kpra,
                'total_tax_amount'  => $totalTaxAmount,
                // 'baseAmountWithKpra' => $baseAmountWithKpra,
            ];
        }

        // dd($newspaperDetails);
        $totalNewspaperTaxAmount = collect($newspaperDetails)->sum('newspaper_tax_amount');
        $totalAgencyTaxAmount = collect($newspaperDetails)->sum('agency_tax_amount');
        $allTotalTax = collect($newspaperDetails)->sum('total_tax_amount');

        $netDues = $totalCurrentBill + $allTotalTax;
        // dd($newspaperTaxAmount, $agencyTaxAmount, $allTotalTax);



        // dd($taxAmount);


        // dd($taxAmount, $baseAmount, $baseAmountWithKpra);


        return view('billing-agencies.create', [
            'pageTitle' =>  $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advertisement' => $advertisement,
            'billDate' => $billDate,
            'inf_number' => $inf_number,
            'newspapers' => $newspapers,
            'totalInsertions' => $totalInsertions,
            'totalCurrentBill' => $totalCurrentBill,
            'totalNewspaperTaxAmount' => $totalNewspaperTaxAmount,
            'totalAgencyTaxAmount' => $totalAgencyTaxAmount,
            'allTotalTax' => $allTotalTax,
            'netDues' => $netDues,
            'newspaperDetails' =>  $newspaperDetails
        ]);
    }

    public function agencyStore(Request $request, $id)
    {
        // dd($request->all());
        $user = auth()->user();
        $userId = $user->id;
        // dd($userId, $userAgencyId);
        $advertisement = Advertisement::findOrFail($id);
        // Authorization check
        if ($user->adv_agency_id != $advertisement->adv_agency_id) {
            abort(403, 'unauthorized');
        }
        // Determine action
        $isPublished = $request->action === 'publish' ? 1 : 0;
        $publishedAt = $isPublished ? now() : null;


        // Attach all newspapers at once with agency_id
        $syncData = [];
        foreach ($request->newspaper_id as $newspaperId) {
            $syncData[$newspaperId] = [
                'is_published' => $isPublished,
                'published_at' => $publishedAt,
                'agency_id'    => $user->adv_agency_id,
            ];
        }
        $advertisement->newspapers()->syncWithoutDetaching($syncData);

        // reuse $newspaperDetails array for storing details in database
        $advertisement = Advertisement::with('newspapers')->findOrFail($id);

        // get logged in agency

        // end of $newspaper array

        // Store or update billing
        $billClassifiedAd = BillClassifiedAd::updateOrCreate(
            [
                // conditions (how we check if record exists)
                'advertisement_id'  => $advertisement->id,
            ],
            [
                'inf_number' => $request->inf_number,
                'invoice_no' => $request->invoice_no,
                'invoice_date' => $request->invoice_date,
                'publication_date' => $request->publication_date ?? now()->toDateString(),
                'printed_no_of_insertion' => $request->printed_no_of_insertion,
                'estimated_cost' =>  $request->estimated_cost,
                'printed_bill_cost' =>  $request->printed_bill_cost,
                'kpra_tax' => $request->kpra_tax,
                'printed_total_bill' =>  $request->printed_total_bill,
                'scanned_bill' => $request->scanned_bill,
                'press_cutting' => $request->press_cutting,
                'user_id' =>     $user->id,
                'advertisement_id' => $advertisement->id,
                'newspaper_id' => $request->newspaper_id, // store as array
                'placements' => $request->placements, // store as array
                'rates_with_placement' => $request->rates_with_placement, // store as array
                'spaces' => $request->spaces, // store as array
                'total_spaces' => $request->total_spaces, // store as array
                'insertions' => $request->insertions, // store as array
                'total_cost_per_newspaper' => $request->total_cost_per_newspaper, // store as array
                'newspaper_share_amounts' => $request->newspaper_share_amounts, // store as array
                'kpra_2_percent_on_85_percent_newspaper' => $request->kpra_2_percent_on_85_percent_newspaper, // store as array
                'agency_share_amounts' => $request->agency_share_amounts, // store as array
                'kpra_10_percent_on_15_percent_agency' => $request->kpra_10_percent_on_15_percent_agency, // store as array
                'total_amount_with_taxes' => $request->total_amount_with_taxes, // store as array
                'total_newspapers_tax' => $request->total_newspapers_tax,
                'total_agency_tax' => $request->total_agency_tax,
                'status' => 'billed',
            ]
        );

        $billClassifiedAd->addAllMediaFromTokens();

        $advertisement->publication = 'Yes';
        $advertisement->save();
        return redirect()->route('advertisements.index')->with('success', 'Agency bill generated successfully!');
    }

    public function agencyShow($billDetailId)
    {
        // Page Title
        $pageTitle = 'Newspapers Bills List &#x2053; DG&#8211;IPR IAMS';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Bills Requests', 'url' => route('billings.newspapers.index')],
            ['label' => 'Bills List', 'url' => null], // The current page (no URL)
        ];

        // $advertisement = Advertisement::findOrFail($advertisementId);
        // dd($advertisement);

        $billdetails = BillClassifiedAd::get();

        $inf_number = $billdetails->first()?->advertisement?->inf_number;

        return view('billing-agencies.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'billdetails' => $billdetails,
            'inf_number' =>     $inf_number,
        ]);
    }

    public function printAgencyBill($billClassifiedAdId)
    {
        try {
            // $advertisement = Advertisement::with('newspapers')->findOrFail($id);

            // // All bills already submitted for this advertisement
            // $billdetails = BillClassifiedAd::where('advertisement_id', $id)
            //     ->with(['user', 'user.newspaper', 'advertisement'])
            //     ->get();

            // Get IDs of newspapers that already billed (adjust field based on your schema)
            // $billedNewspaperIds = $billdetails->pluck('user_id')->filter()->toArray();
            // dd($billClassifiedAdId);

            $billClassifiedAd = BillClassifiedAd::findOrFail($billClassifiedAdId);

            $billdetails = BillClassifiedAd::with('advertisement')
                ->where('advertisement_id', $billClassifiedAd->advertisement_id)
                ->get();

            // Prepare arrays to store media for all newspapers
            $scanned_bill_agency = [];
            $press_cutting_agency = [];
            $newspaperTitles = [];

            // Loop through each bill and collect media
            foreach ($billdetails as $bill) {
                $ids = (array)$bill->newspaper_id ?? [];   // If null → empty array

                foreach ($ids as $nid) {
                    if ($nid) {
                        $newspaperTitles[$bill->id][] = Newspaper::find($nid)->title;
                    }
                }
                $scanned_bill_agency[] = $bill->getMedia('scanned_bill_agency');
                $press_cutting_agency[] = $bill->getMedia('press_cutting_agency');
            }
            // Load advertisement (correct)
            $advertisement = Advertisement::findOrFail($billClassifiedAd->advertisement_id);

            $adType = $billdetails->first()?->advertisement?->classified_ad_type->type ?? '-';

            // Always fetch Deputy Director
            $dd = User::where('name', 'Deputy Director')->first();
            $ddSignature = $dd && $dd->image
                ? storage_path('app/public/' . $dd->image)
                : null;



            // $publicationDate = $billdetails->value('publication_date');

            $inf_number = $billdetails->first()?->advertisement?->inf_number;
            $grandTotal = round($billdetails->first()?->printed_total_bill);



            // Totals
            // $grandTotal = $billdetails->printed_total_bill;
            // Split into rupees and paisa
            $rupees = floor($grandTotal);
            $paisa  = round(($grandTotal - $rupees) * 100);

            // Create formatter
            $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            // Convert to words
            $rupeesWords = ucfirst($formatter->format($rupees)) . ' rupees';
            $paisaWords  = $paisa > 0 ? ' and ' . $formatter->format($paisa) . ' paisa' : '';

            $grandTotalWords = $rupeesWords . ' only.';



            $data = [

                'billdetails' => $billdetails,
                'newspaperTitles' => $newspaperTitles,
                'scanned_bill_agency' => $scanned_bill_agency,
                'press_cutting_agency' => $press_cutting_agency,
                'grandTotal' => $grandTotal,
                'grandTotalWords' => $grandTotalWords,
                // 'pendingNewspapers' => $pendingNewspapers,
                'inf_number' => $inf_number,
                'dd' => $dd,
                'adType' => $adType,
                'ddSignature' =>  $ddSignature,
                // 'publicationDate' => $publicationDate,
                'generatedAt' => Carbon::now(),
            ];

            // If you need remote images enabled:
            // $pdf = Pdf::loadView('billing-agencies.print', $data)
            //     ->setPaper('A3', 'portrait'); // or 'landscape'

            // Stream in browser to allow print/download from the browser PDF viewer
            $billNo = $advertisement->inf_number ?? 'Bill';

            // For display in the PDF (safe, keep /)
            $displayBillNo = $billNo;

            // For filename (replace / with - just for saving/streaming)
            $fileBillNo = str_replace(['/', '\\'], '-', $billNo);

            $pdf = Pdf::loadView('billing-agencies.print', compact('billdetails', 'scanned_bill_agency', 'press_cutting_agency', 'newspaperTitles',  'displayBillNo', 'grandTotal', 'grandTotalWords', 'inf_number', 'dd', 'adType', 'ddSignature'))
                ->setPaper('A3', 'portrait'); // or 'landscape'

            // When streaming/downloading, use safe filename
            return $pdf->stream("Agency-bill-{$fileBillNo}.pdf");
            // or use ->download('Bill-....pdf') to force download
        } catch (\Exception $e) {
            // Log the full error for debugging
            Log::error('PDF Generation Failed for Agency Bill ID: ' . $billClassifiedAdId, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Show user-friendly message and redirect back
            return redirect()->back()->with('error', 'Unable to generate the agency bill. The required document images are missing. Please contact the administrator.');
        }
    }

    public function newspaperBillSubmission($id)
    {
        // dd('here', $id);
        $user = auth()->user();
        $userRole = $user->roles->pluck('id')->first();
        $userName = $user->name;
        // $billClassifiedAd = BillClassifiedAd::findOrFail($id);

        $advertisement = Advertisement::findOrFail($id);

        // 🔑 Authorization Chec
        if ($user->hasRole('Superintendent')) {
            // Newspaper Media user
            $advertisement->bill_submitted_to_role_id = 2;
        }

        $advertisement->save();
        dispatch(function () use ($advertisement, $userName) {

            foreach (User::Assistants() as $assistant) {
                notifyUser($assistant, [
                    'title' => 'Bill Submitted By IPR Department.',
                    'message' => 'A new bill has been submitted by ' . $userName . ' .',
                    'url' => url('billing-newspapers/show', ['id' => $advertisement->id])
                ]);
            }
        });
        return redirect()->back()->with('success', 'Bill submitted successfully to Client Office!');
    }

    public function agencyBillSubmission($id)
    {
        // dd('here', $id);
        $user = auth()->user();
        $userRole = $user->roles->pluck('id')->first();
        $billClassifiedAd = BillClassifiedAd::findOrFail($id);


        $advertisement = Advertisement::findOrFail($billClassifiedAd->advertisement_id);
        // dd($advertisement->id);
        // 🔑 Authorization Chec
        if ($user->hasRole('Superintendent')) {
            // Newspaper Media user
            $advertisement->bill_submitted_to_role_id = 2;
        }

        $advertisement->save();
        return redirect()->back()->with('success', 'Bill submitted successfully to Client Office!');
    }




    //==========================//
    // Exports Methods
    //===========================//

    // public function exportPDFBillingNewspaperIndex(Request $request, $advertisementId)
    // {
    //     $advertisement = Advertisement::findOrFail($advertisementId);
    //     $billdetails = $this->buildNewspaperIndexQuery($request, $advertisementId);
    //     $advertisements = $billdetails->orderBy('created_at', 'desc')->get();

    //     $pdf = Pdf::loadView('exports.advertisements_pdf', [
    //         'advertisements' => $advertisements,
    //         'user' => auth()->user(),
    //         'is_department_user' => (auth()->user()->department_id && is_null(auth()->user()->office_id)),
    //         'is_office_user' => (auth()->user()->department_id && !is_null(auth()->user()->office_id)),
    //         'title' => 'Billing Advertisements'
    //     ]);

    //     return $pdf->download('Rejected_ads_' . now()->format('Y-m-d') . '.pdf');
    // }



    // public function exportExcelBillingNewspaperIndex(Request $request)
    // {
    //     $advertisements = $this->buildRejectedQuery($request);
    //     $advertisements = $advertisements->orderBy('created_at', 'desc')->get();

    //     return Excel::download(
    //         new AdvertisementsExport($advertisements),
    //         'rejected_ads_' . now()->format('Y-m-d') . '.xlsx'
    //     );
    // }

    // public function buildNewspaperIndexQuery(Request $request, $advertisementId)
    // {

    //     // Page Title
    //     $pageTitle = 'Newspapers Bills List &#x2053; DG&#8211;IPR IAMS';

    //     // Breadcrumb
    //     $breadcrumbs = [
    //         ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
    //         ['label' => 'Bills Requests', 'url' => route('billings.newspapers.index')],
    //         ['label' => 'Bills List', 'url' => null], // The current page (no URL)
    //     ];

    //     $advertisement = Advertisement::findOrFail($advertisementId);

    //     $billdetails = BillClassifiedAd::where('advertisement_id', $advertisement->id)->get();

    //     $inf_number = $billdetails->first()?->advertisement?->inf_number;

    //     return $billdetails;
    // }
}
