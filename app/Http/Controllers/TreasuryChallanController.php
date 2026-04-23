<?php

namespace App\Http\Controllers;

use App\Exports\TreasuryChallanExport;
use App\Models\Advertisement;
use App\Models\BillClassifiedAd;
use App\Models\Department;
use App\Models\Office;
use App\Models\PlaAccountItem;
use App\Models\PlaAcount;
use App\Models\Status;
use App\Models\TreasuryChallan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use NumberFormatter;


class TreasuryChallanController extends Controller
{
    // Apply filter on blade view
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('inf_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('department', fn($dq) => $dq->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('office', fn($oq) => $oq->where('ddo_name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('status', fn($sq) => $sq->where('title', 'LIKE', "%{$search}%"))
                    ->orWhere('created_at', 'LIKE', "%{$search}%")
                    ->orWhere('cheque_number', 'LIKE', "%{$search}%")
                    ->orWhere('total_amount', 'LIKE', "%{$search}%")
                    ->orWhere('memo_number', 'LIKE', "%{$search}%")
                    ->orWhere('sbp_verification_date', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('inf_number')) {
            $query->where('inf_number', $request->inf_number);
        }

        if ($request->filled('memo_number')) {
            $query->where('memo_number', $request->memo_number);
        }


        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }
        if ($request->filled('cheque_number')) {
            $query->where('cheque_number', $request->cheque_number);
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->filled('sbp_verification_date')) {

            $dates = explode(' to ', $request->sbp_verification_date);

            if (count($dates) == 2) {
                $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $to = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

                $query->whereBetween('sbp_verification_date', [$from, $to]);
            }
        }
        if ($request->filled('cheque_date')) {

            $dates = explode(' to ', $request->cheque_date);

            if (count($dates) == 2) {
                $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $to = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

                $query->whereBetween('cheque_date', [$from, $to]);
            }
        }


        return $query;
    }

    // public function index(Request $request)
    // {
    //     $user = Auth::user();
    //     $userId = $user->id;
    //     $pendingChequeApprovalStatus = Status::where('title', 'Pending Cheque Approval')->first();
    //     $pendingChequeVerificationStatus = Status::where('title', 'Pending Cheque Verification')->first();
    //     $approvedStatus = Status::where('title', 'Approved')->first();
    //     $pendingStatus = Status::where('title', 'Pending Department Approval')->first();
    //     $receiptPendingChequeStatus = Status::where('title', 'Receipt Pending cheque')->first();



    //     if ($user->hasRole('Superintendent')) {
    //         // $treasuryChallans = TreasuryChallan::where('status_id', $pendingChequeVerificationStatus->id)->get();
    //         $treasuryChallans = TreasuryChallan::where(function ($query) use ($receiptPendingChequeStatus, $pendingChequeVerificationStatus, $pendingChequeApprovalStatus, $approvedStatus) {
    //             $query->where('status_id', $receiptPendingChequeStatus->id)
    //                 ->orWhere('status_id', $pendingChequeVerificationStatus->id)
    //                 ->orWhere('status_id', $pendingChequeApprovalStatus->id)
    //                 ->orWhere('status_id', $approvedStatus->id);
    //         })->orderByRaw("FIELD(status_id, ?, ?, ?)", [
    //             $pendingChequeVerificationStatus->id,
    //             $pendingChequeApprovalStatus->id,
    //             $approvedStatus->id
    //         ])->get();
    //     } elseif ($user->hasRole('Director General')) {
    //         $treasuryChallans = TreasuryChallan::where(function ($query) use ($pendingChequeApprovalStatus, $approvedStatus) {
    //             $query->where('status_id', $pendingChequeApprovalStatus->id)
    //                 ->orWhere('status_id', $approvedStatus->id);
    //         })->orderByRaw("FIELD(status_id, ?, ?, ?)", [
    //             $pendingChequeVerificationStatus->id,
    //             $pendingChequeApprovalStatus->id,
    //             $approvedStatus->id
    //         ])->get();
    //     } else {
    //         // show all or based on other logic
    //         $treasuryChallans = TreasuryChallan::all();
    //     }
    //     // Apply search/filters
    //     $this->applyFilters($treasuryChallans, $request);

    //     // $treasuryChallans = TreasuryChallan::latest()->get();
    //     // Get data for dropdowns
    //     $statuses   = Status::all(); // all statuses
    //     $departments = Department::all(); // all departments (adjust as needed)
    //     $offices     = Office::all();     // all offices
    //     return view('treasury-challans.index', [
    //         'treasuryChallans' => $treasuryChallans,
    //         'statuses'            => $statuses,
    //         'departments'         => $departments,
    //         'offices'             => $offices,
    //     ]);
    // }

    // for filteration
    public function index(Request $request)
    {
        $user = Auth::user();

        $pendingChequeApprovalStatus      = Status::where('title', 'Pending Cheque Approval')->first();
        $pendingChequeVerificationStatus  = Status::where('title', 'Pending Cheque Verification')->first();
        $approvedStatus                   = Status::where('title', 'Approved')->first();
        $pendingStatus                    = Status::where('title', 'Pending Department Approval')->first();
        $receiptPendingChequeStatus       = Status::where('title', 'Receipt Pending cheque')->first();

        // start query here
        $query = TreasuryChallan::query();

        // ROLE BASED FILTER
        if ($user->hasRole('Superintendent')) {

            $query->whereIn('status_id', [
                $receiptPendingChequeStatus?->id,
                $pendingChequeVerificationStatus?->id,
                $pendingChequeApprovalStatus?->id,
                $approvedStatus?->id,
            ]);

            $query->orderByRaw("FIELD(status_id, ?, ?, ?)", [
                $pendingChequeVerificationStatus?->id,
                $pendingChequeApprovalStatus?->id,
                $approvedStatus?->id
            ]);
        } elseif ($user->hasRole('Director General')) {

            $query->whereIn('status_id', [
                $pendingChequeApprovalStatus?->id,
                $approvedStatus?->id,
            ]);

            $query->orderByRaw("FIELD(status_id, ?, ?)", [
                $pendingChequeApprovalStatus?->id,
                $approvedStatus?->id
            ]);
        }

        // APPLY SEARCH / FILTERS
        $this->applyFilters($query, $request);

        // EXECUTE QUERY
        $treasuryChallans = $query->latest()->paginate(10)
            ->appends($request->query());

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Recovery & PLA', 'url' => null],
            ['label' => 'Treasury Challans', 'url' => null],
        ];

        return view('treasury-challans.index', [
            'treasuryChallans' => $treasuryChallans,
            'statuses'         => Status::all(),
            'departments'      => Department::all(),
            'offices'          => Office::all(),
            'breadcrumbs'      => $breadcrumbs,
        ]);
    }

    public function showDGChequeApproval() {}

    public function create()
    {

        $getDepartments = BillClassifiedAd::whereNotNull('status')
            ->where('status', 'like', '%billed%')
            ->whereHas('advertisement.department') // only records that have a department
            ->with('advertisement.department')   // eager load department
            ->get()
            ->pluck('advertisement.department')
            ->unique('id');

        // generate diary number
        $preview_diary_number = get_next_diary_number_preview();

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Treasury Challans', 'url' => route('billings.treasury-challans.index')],
            ['label' => 'Create', 'url' => null],
        ];

        return view('treasury-challans.create', [
            'getDepartments' => $getDepartments,
            'preview_diary_number' => $preview_diary_number,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }


    public function fetchOffices(Request $request)
    {
        $departmentId = $request->departmentId;

        $offices = Office::whereHas('advertisements.billClassifiedAds', function ($q) {})
            ->whereHas('advertisements', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->select('id', 'ddo_name')
            ->distinct()
            ->get();

        return response()->json($offices);
    }

    // get inf number on the basis of offices seletcts but only those who's data are in billclassifiedAds table
    public function getInfNumbers(Request $request)
    {
        $officeId = $request->officeId;
        $departmentId = $request->departmentId;

        // Only take ads that are in bill_classified_ads
        $advertisementIds = BillClassifiedAd::pluck('advertisement_id')->unique();

        $query = Advertisement::whereIn('id', $advertisementIds);
        // ->where('bill_submitted_to_role_id', 2); // Check if value is 2;

        if (!empty($officeId)) {
            // Case 1: User selected office
            $query->where('office_id', $officeId);
        } elseif (!empty($departmentId)) {
            // Case 2: No office, but department selected
            $query->where('department_id', $departmentId)
                ->whereNull('office_id');
        }

        $infNumbers = $query->pluck('inf_number')->unique();

        return response()->json($infNumbers);
    }

    public function getTotalBill(Request $request)
    {
        $infNumbers = $request->infNumbers ?? [];
        $officeId = $request->officeId;
        $departmentId = $request->departmentId;

        if (empty($infNumbers)) {
            return response()->json(['total' => 0]);
        }

        // Step 1
        // First, find advertisement IDs for the selected INF numbers
        $advertisements = Advertisement::whereIn('inf_number', $infNumbers)
            ->when($officeId, function ($q) use ($officeId) {
                $q->where('office_id', $officeId);
            })
            ->when(!$officeId && $departmentId, function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId)
                    ->whereNull('office_id');
            })
            ->select('id', 'inf_number')
            ->get()
            ->keyBy('id'); // id => inf_number

        $advertisementIds = $advertisements->keys();


        // Step 2
        // Now sum the printed_total_bill from bill_classified_ads
        $totalBill = BillClassifiedAd::whereIn('advertisement_id', $advertisementIds)
            ->sum('printed_total_bill');

        // step 3 INF wise newspaper amounts
        // $infWise = [];
        // $rows = BillClassifiedAd::whereIn('advertisement_id', $advertisementIds)
        //     ->select('advertisement_id', 'newspaper_id', 'printed_total_bill')
        //     ->get();

        // foreach ($rows as $row) {
        //     $infNumber = $advertisements[$row->advertisement_id]->inf_number;

        //     if (!isset($infWise[$infNumber])) {
        //         $infWise[$infNumber] = [];
        //     }

        //     // sum only within same INF
        //     $infWise[$infNumber][$row->newspaper_id] =
        //         ($infWise[$infNumber][$row->newspaper_id] ?? 0)
        //         + $row->printed_total_bill;

        // }

        return response()->json([
            'total' => $totalBill,
            // 'inf_wise_newspapers_amount' => $infWise
        ]);
    }

    //     // Also prepare newspaper-wise amounts for PLA account
    //     $newspaperAmounts = BillClassifiedAd::whereIn('advertisement_id', $advertisementIds)
    //         ->select('newspaper_id', 'printed_total_bill')
    //         ->get()
    //         ->groupBy('newspaper_id')
    //         ->map(function ($rows) {
    //             return $rows->sum('printed_total_bill');
    //         })
    //         ->toArray();
    //     // dd($plaAccounts);

    //     return response()->json([
    //         'total' => $totalBill,
    //         'newspaperAmounts' => $newspaperAmounts
    //     ]);
    // }



    public function store(Request $request)
    {

        // dd($request->all());
        // Submitt Challan
        $pendingChequeVerificationStatus = Status::where('title', 'Pending Cheque Verification')->first();

        $diary_number = generate_diary_number();


        $request->merge([
            'diary_number' => $diary_number,
            'challan_series_id' => $diary_number['challan_series_id'],
            'status_id' => $pendingChequeVerificationStatus->id,
            'created_by' => Auth::id(),
            'batch_no' => generate_batch_no(),

            // 'newspaper_amount' => $request->newspaper_amount, // array coming here
        ]);

        $treasuryChallan = TreasuryChallan::create($request->all());


        return redirect()->route('billings.treasury-challans.index')->with('success', 'Treasury Challan Added successfully!');
    }


    public function showOnlineCheque()
    {
        $user = Auth::user();
        $userId = $user->id;
        $receiptPendingChequeStatus = Status::where('title', 'Receipt Pending cheque')->first();
        $pendingChequeApprovalStatus = Status::where('title', 'Pending Cheque Approval')->first();
        $approvedCheque = Status::where('title', 'Approved')->first();

        $query = TreasuryChallan::where('created_by', $userId);

        if ($user->hasRole('Client Office')) {
            $query->where(function ($q) use ($receiptPendingChequeStatus, $pendingChequeApprovalStatus,  $approvedCheque) {
                $q->where('status_id', $receiptPendingChequeStatus->id)
                    ->orWhere('status_id', $pendingChequeApprovalStatus->id)
                    ->orWhere('status_id',  $approvedCheque->id);
            });
        }

        $treasuryChallans = $query->get();

        // $treasuryChallans = TreasuryChallan::latest()->get();
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Treasury Challans', 'url' => route('billings.treasury-challans.index')],
            ['label' => 'Cheque Submissions', 'url' => null],
        ];

        return view('treasury-challans.show-online-cheque', [
            'treasuryChallans' => $treasuryChallans,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }


    //   online cheque submission process
    public function createOnlineCheque()
    {
        $user = auth()->user();
        $userDepartmentId = $user->department_id;
        // dd($userDepartment);

        $getDepartments = BillClassifiedAd::whereNotNull('status')
            ->where('status', 'like', '%billed%')
            ->whereHas('advertisement.department', function ($query) use ($userDepartmentId) {
                $query->where('id', $userDepartmentId);
            })
            ->with('advertisement.department')
            ->get()
            ->pluck('advertisement.department')
            ->unique('id');

        // generate diary number
        $preview_diary_number = get_next_diary_number_preview();

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Treasury Challans', 'url' => route('billings.treasury-challans.index')],
            ['label' => 'Create Online Cheque', 'url' => null],
        ];

        return view('treasury-challans.create-online-cheque', [
            'getDepartments' => $getDepartments,
            'preview_diary_number' => $preview_diary_number,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }


    public function onlineFetchOffices(Request $request)
    {
        $user = Auth::user();
        $userOFfice = $user->office_id;

        // check validation
        $request->validate([
            'departmentId' => 'required|exists:departments,id',
        ]);

        $departmentId = $request->departmentId;

        $offices = Office::where('id', $user->office_id)
            ->whereHas('advertisements.billClassifiedAds', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId)
                    ->where('bill_submitted_to_role_id', 2);
            })
            ->select('id', 'ddo_name')
            // ->distinct()
            ->get();

        return response()->json($offices);
    }

    public function onlineGetInfNumbers(Request $request)
    {
        $officeId = $request->officeId;
        $departmentId = $request->departmentId;

        $advertisementIds = BillClassifiedAd::pluck('advertisement_id')->unique();

        $query = Advertisement::whereIn('id', $advertisementIds)
            ->where('bill_submitted_to_role_id', 2);

        if (!empty($officeId)) {
            $query->where('office_id', $officeId);
        } elseif (!empty($departmentId)) {
            $query->where('department_id', $departmentId)
                ->whereNull('office_id');
        }

        // Exclude those already in treasury_challans
        // $query->leftJoin('treasury_challans', function ($join) {
        //     $join->on('advertisements.inf_number', '=', 'treasury_challans.inf_number')
        //         ->on('advertisements.department_id', '=', 'treasury_challans.department_id');
        // })
        //     ->whereNull('treasury_challans.id')
        //     ->select('advertisements.inf_number')
        //     ->distinct();

        // Use the correct column name with table prefix
        $infNumbers = $query->pluck('advertisements.inf_number')->unique();

        return response()->json($infNumbers);
    }

    // get inf number on the basis of offices selects but only those who's data are in billclassifiedAds table
    // public function onlineGetInfNumbers(Request $request)
    // {
    //     $officeId = $request->officeId;
    //     $departmentId = $request->departmentId;

    //     // Only take ads that are in bill_classified_ads
    //     $advertisementIds = BillClassifiedAd::pluck('advertisement_id')->unique();

    //     $query = Advertisement::whereIn('id', $advertisementIds)
    //         ->where('bill_submitted_to_role_id', 2); // Check if value is 2;

    //     if (!empty($officeId)) {
    //         // Case 1: User selected office
    //         $query->where('office_id', $officeId);
    //     } elseif (!empty($departmentId)) {
    //         // Case 2: No office, but department selected
    //         $query->where('department_id', $departmentId)
    //             ->whereNull('office_id');
    //     }

    //

    //     $infNumbers = $query->pluck('inf_number')->unique();

    //     return response()->json($infNumbers);
    // }

    public function onlineGetTotalBill(Request $request)
    {
        $infNumbers = $request->infNumbers ?? [];
        $officeId = $request->officeId;
        $departmentId = $request->departmentId;

        if (empty($infNumbers)) {
            return response()->json(['total' => 0]);
        }

        // Step 1
        // First, find advertisement IDs for the selected INF numbers
        $advertisements = Advertisement::whereIn('inf_number', $infNumbers)
            ->when($officeId, function ($q) use ($officeId) {
                $q->where('office_id', $officeId);
            })
            ->when(!$officeId && $departmentId, function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId)
                    ->whereNull('office_id');
            })
            ->select('id', 'inf_number')
            ->get()
            ->keyBy('id'); // id => inf_number

        $advertisementIds = $advertisements->keys();


        // Step 2
        // Now sum the printed_total_bill from bill_classified_ads
        $totalBill = BillClassifiedAd::whereIn('advertisement_id', $advertisementIds)
            ->sum('printed_total_bill');

        // step 3 INF wise newspaper amounts
        // $infWise = [];
        // $rows = BillClassifiedAd::whereIn('advertisement_id', $advertisementIds)
        //     ->select('advertisement_id', 'newspaper_id', 'printed_total_bill')
        //     ->get();

        // foreach ($rows as $row) {
        //     $infNumber = $advertisements[$row->advertisement_id]->inf_number;

        //     if (!isset($infWise[$infNumber])) {
        //         $infWise[$infNumber] = [];
        //     }

        //     // sum only within same INF
        //     $infWise[$infNumber][$row->newspaper_id] =
        //         ($infWise[$infNumber][$row->newspaper_id] ?? 0)
        //         + $row->printed_total_bill;

        // }

        return response()->json([
            'total' => $totalBill,
            // 'inf_wise_newspapers_amount' => $infWise
        ]);
    }


    public function storeOnlineCheque(Request $request)
    {

        // dd($request->all());
        $user = auth()->user();
        $userName = $user->name;
        // Submitt Challan
        $diary_number = generate_diary_number();
        $receiptPendingChequeStatus = Status::where('title', 'Receipt Pending cheque')->first();

        $request->merge([
            'diary_number' => $diary_number,
            'challan_series_id' => $diary_number['challan_series_id'],
            'created_by' => Auth::id(),
            'status_id' => $receiptPendingChequeStatus->id,
            'batch_no' => generate_batch_no(),
            // 'newspaper_amount' => $request->newspaper_amount, // array coming here
        ]);

        $treasuryChallan = TreasuryChallan::create($request->all());

        dispatch(function () use ($treasuryChallan, $userName) {

            foreach (User::Assistants() as $assistant) {
                notifyUser($assistant, [
                    'title' => 'New Cheque Submission.',
                    'message' => 'A new Cheque with memo number ' . $treasuryChallan->memo_number . ' has been submitted by ' . $userName . ' .',
                    'url' => url('billing-newspapers/show', ['id' => $treasuryChallan->id])
                ]);
            }
        });

        return redirect()->route('billings.treasury-challans.showOnlinCheque')->with('success', 'Cheque Created and submitted to IPR Department successfully');
    }

    public function edit($id)
    {
        $treasuryChallan = TreasuryChallan::findOrFail($id);

        $getDepartments = BillClassifiedAd::whereNotNull('status')
            ->where('status', 'like', '%billed%')
            ->whereHas('advertisement.department') // only records that have a department
            ->with('advertisement.department')   // eager load department
            ->get()
            ->pluck('advertisement.department')
            ->unique('id');


        // If the saved record has a department_id, fetch its offices; otherwise an empty collection
        if ($treasuryChallan->department_id) {
            $getOffices = Office::where('department_id', $treasuryChallan->department_id)->get();
        } else {
            $getOffices = collect(); // empty collection to avoid "undefined"
        }

        $allInfNumbers = BillClassifiedAd::query()
            ->whereNotNull('status')
            ->where('status', 'like', '%billed%')
            ->when($treasuryChallan->department_id, function ($q) use ($treasuryChallan) {
                $q->whereHas('advertisement', function ($sub) use ($treasuryChallan) {
                    $sub->where('department_id', $treasuryChallan->department_id);
                });
            })
            ->when($treasuryChallan->office_id, function ($q) use ($treasuryChallan) {
                $q->whereHas('advertisement', function ($sub) use ($treasuryChallan) {
                    $sub->where('office_id', $treasuryChallan->office_id);
                });
            })
            ->with('advertisement:id,inf_number') // eager load only required cols
            ->get()
            ->pluck('advertisement.inf_number')
            ->unique()
            ->toArray();

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Treasury Challans', 'url' => route('billings.treasury-challans.index')],
            ['label' => 'Edit', 'url' => null],
        ];


        return view('treasury-challans.edit', [
            'getDepartments' =>  $getDepartments,
            'treasuryChallan' => $treasuryChallan,
            'getOffices' => $getOffices,
            'allInfNumbers' => $allInfNumbers,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function viewChallan($id)
    {
        $treasuryChallan = TreasuryChallan::findOrFail($id);

        $inf_number_array = $treasuryChallan->inf_number;
        $totalAmount = $treasuryChallan->total_amount;
        $roundedAmount = round($totalAmount);
        $finalAmount = floor($roundedAmount);

        // Create formatter
        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        // Convert to words
        $rupeesWords = ucfirst($formatter->format($finalAmount)) . ' rupees only.';

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Treasury Challans', 'url' => route('billings.treasury-challans.index')],
            ['label' => 'Challan Form', 'url' => null],
        ];

        return view('treasury-challans.view-challan', [
            'treasuryChallan' => $treasuryChallan,
            'finalAmount' => $finalAmount,
            'rupeesWords' => $rupeesWords,
            'infNumbers' =>  $inf_number_array,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function downloadChallanPdf($id)
    {
        $treasuryChallan = TreasuryChallan::findOrFail($id);

        $inf_number_array = $treasuryChallan->inf_number;
        $totalAmount = $treasuryChallan->total_amount;
        $roundedAmount = round($totalAmount);
        $finalAmount = floor($roundedAmount);

        // Create formatter
        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        // Convert to words
        $rupeesWords = ucfirst($formatter->format($finalAmount)) . ' rupees only.';

        // $pdf = app('dompdf.wrapper');
        $pdf = PDF::loadView('treasury-challans.challan-pdf', [
            'treasuryChallan' => $treasuryChallan,
            'finalAmount' => $finalAmount,
            'rupeesWords' => $rupeesWords,
            'infNumbers' =>  $inf_number_array,
        ])->setPaper('legal', 'landscape');;

        // 1. Get the memo number
        $memoNumber = $treasuryChallan->memo_number;
        $safeMemoNumber = str_replace(['/', '\\'], '-', $memoNumber);
        $fileName = 'Challan_form_' . $safeMemoNumber . '.pdf';

        return $pdf->download($fileName);
    }

    public function viewDepositSlip($id)
    {
        $treasuryChallan = TreasuryChallan::findOrFail($id);
        $totalAmount = $treasuryChallan->total_amount;
        $roundedAmount = round($totalAmount);
        $finalAmount = floor($roundedAmount);

        // Create formatter
        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        // Convert to words
        $rupeesWords = ucfirst($formatter->format($finalAmount)) . ' rupees only.';


        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Treasury Challans', 'url' => route('billings.treasury-challans.index')],
            ['label' => 'Deposit Slip', 'url' => null],
        ];

        return view('treasury-challans.view-deposit-slip', [
            'treasuryChallan' => $treasuryChallan,
            'finalAmount' => $finalAmount,
            'rupeesWords' => $rupeesWords,
            'breadcrumbs' => $breadcrumbs,

        ]);
    }

    public function downloadDepositSlipPdf($id)
    {
        $treasuryChallan = TreasuryChallan::findOrFail($id);

        $totalAmount = $treasuryChallan->total_amount;
        $roundedAmount = round($totalAmount);
        $finalAmount = floor($roundedAmount);

        // Create formatter
        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        // Convert to words
        $rupeesWords = ucfirst($formatter->format($finalAmount)) . ' rupees only.';

        // $pdf = app('dompdf.wrapper');
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Treasury Challans', 'url' => route('billings.treasury-challans.index')],
            ['label' => 'Deposit Slip', 'url' => null],
        ];

        $pdf = PDF::loadView('treasury-challans.deposit-slip-pdf', [
            'treasuryChallan' => $treasuryChallan,
            'finalAmount' => $finalAmount,
            'rupeesWords' => $rupeesWords,
            'breadcrumbs' => $breadcrumbs,
        ])->setPaper('legal', 'landscape');;

        // 1. Get the memo number
        $memoNumber = $treasuryChallan->memo_number;
        $safeMemoNumber = str_replace(['/', '\\'], '-', $memoNumber);

        $fileName = 'Deposit_slip_' . $safeMemoNumber . '.pdf';

        return $pdf->download($fileName);
    }


    // public function modalData(Request $request, $id)
    // {
    //     dd($request->all());
    //     $treasuryChallan = TreasuryChallan::findOrFail($id);
    //     $treasuryChallanData = $treasuryChallan->update($request->all());

    //     $plaAcount = PlaAcount::create([
    //         'inf_number' => $treasuryChallan->inf_number,
    //         'department_id' => $treasuryChallan->department_id,
    //         'office_id' => $treasuryChallan->office_id,
    //         'cheque_no' => $treasuryChallan->cheque_number,
    //         'cheque_date' => $treasuryChallan->cheque_date,
    //         'challan_no' => $treasuryChallan->challan_number,
    //         'total_cheque_amount' => $treasuryChallan->total_amount,
    //     ]);


    //     return redirect()->back()->with('success', 'Record verified and PLA Credit successfully.');
    // }

    public function uploadChallanImage(Request $request)
    {

        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $request->file('file')->store('tr_challan_images', 'public');

        return response()->json([
            'success'   => true,
            'file_path' => $imagePath,
            'url'       => asset('storage/' . $imagePath),
        ]);
    }

    public function modalData(Request $request, $id)
    {

        $request->validate([
            'tr_challan_verification_date' => 'required|date',
            'sbp_verification_date'        => 'required|date',
            'challan_number'                => 'required|string|max:255',
            'tr_challan_image'              => 'nullable|string',   // was 'image' – now string
        ]);

        DB::beginTransaction();

        try {
            $user = auth()->user();
            $userName = $user->name;
            $treasuryChallan = TreasuryChallan::findOrFail($id);
            $pendingChequeApprovalStatus = Status::where('title', 'Pending Cheque Approval')->firstOrFail();

            $updateData = [
                'tr_challan_verification_date' => $request->tr_challan_verification_date,
                'sbp_verification_date'        => $request->sbp_verification_date,
                'challan_number'                => $request->challan_number,
                'status_id'                     => $pendingChequeApprovalStatus->id,
                'forwarded_to_role_id'           => 10,
                'verified_by'                    => Auth::id(),
                'verified_at'                     => now(),
            ];


            $newImagePath = $request->input('tr_challan_image');
            if ($newImagePath && $newImagePath !== $treasuryChallan->tr_challan_image) {

                if ($treasuryChallan->tr_challan_image) {
                    Storage::disk('public')->delete($treasuryChallan->tr_challan_image);
                }
                $updateData['tr_challan_image'] = $newImagePath;
            } else {
                // Keep existing image (or null)
                $updateData['tr_challan_image'] = $treasuryChallan->tr_challan_image;
            }

            $treasuryChallan->update($updateData);
            dispatch(function () use ($treasuryChallan, $userName) {

                foreach (User::Assistants() as $assistant) {
                    notifyUser($assistant, [
                        'title' => 'New Cheque Submission for Approval.',
                        'message' => 'A new Cheque has been verified by ' . $userName . ' and has been submitted to Director General for approval.',
                        'url' => url('billing-newspapers/show', ['id' => $treasuryChallan->id])
                    ]);
                }
            });

            \Log::info('Update data:', $updateData);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Treasury challan verified successfully and forwarded to Director General for approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('modalData Error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // old logic
    // public function dgAprovePla(Request $request, $id)
    //
    //     $request->validate([
    //         'confirm_verification' => 'required|accepted',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         // 1. Get treasury challan
    //         $treasuryChallan = TreasuryChallan::findOrFail($id);
    //         $approvedStatus = Status::where('title', 'Approved')->first();
    //         $rejectedStatus = Status::where('title', 'Rejected')->first();

    //         // Check if already approved or rejected
    //         if ($treasuryChallan->status_id == 'Approved') {
    //             return back()->with('warning', 'This treasury challan is already approved.');
    //         }

    //         if ($treasuryChallan->status_id == 'Rejected') {
    //             return back()->with('warning', 'This treasury challan is already rejected.');
    //         }

    //         // If rejecting
    //         if ($request->has('reject')) {
    //             $treasuryChallan->update([
    //                 'status_id' =>   $rejectedStatus->id,
    //                 'rejection_reason' => $request->rejection_reason,
    //             ]);
    //             DB::commit();
    //             return redirect()->back()
    //                 ->with('info', 'Treasury challan rejected successfully.');
    //         }

    //         // 3. Get all bills for this treasury challan
    //         $infNumbers = is_array($treasuryChallan->inf_number)
    //             ? $treasuryChallan->inf_number
    //             : [$treasuryChallan->inf_number];

    //         $bills = BillClassifiedAd::whereIn('inf_number', $infNumbers)->get();

    //         // 4. Group by INF number FIRST
    //         $infGroups = [];

    //         foreach ($bills as $bill) {
    //             $infNumber = $bill->inf_number;

    //             if (!isset($infGroups[$infNumber])) {
    //                 $infGroups[$infNumber] = [];
    //             }

    //             // Add this bill to its INF group
    //             $infGroups[$infNumber][] = $bill;
    //         }

    //         // 5. Now process each INF group separately
    //         $formattedInfDetails = [];
    //         $allInfNumbers = []; // For flat array storage
    //         $allNewspaperIds = []; // For flat array storage
    //         $allNewspaperAmounts = []; // For flat array storage

    //         foreach ($infGroups as $infNumber => $billsForInf) {
    //             $infNewspapers = [];

    //             // Process each bill in this INF group
    //             foreach ($billsForInf as $bill) {
    //                 // Get newspaper IDs for this bill
    //                 $newspaperIds = is_array($bill->newspaper_id)
    //                     ? $bill->newspaper_id
    //                     : [$bill->newspaper_id];

    //                 // Get amounts for this bill
    //                 $amounts = is_array($bill->printed_total_bill)
    //                     ? $bill->printed_total_bill
    //                     : [$bill->printed_total_bill];

    //                 // Make sure arrays match
    //                 $count = max(count($newspaperIds), count($amounts));

    //                 for ($i = 0; $i < $count; $i++) {
    //                     $newspaperId = $newspaperIds[$i] ?? $newspaperIds[0] ?? null;
    //                     $amount = $amounts[$i] ?? $amounts[0] ?? 0;

    //                     if ($newspaperId) {
    //                         $infNewspapers[] = [
    //                             'newspaper_id' => $newspaperId,
    //                             'amount' => $amount
    //                         ];

    //                         // Add to flat arrays
    //                         $allInfNumbers[] = $infNumber;
    //                         $allNewspaperIds[] = $newspaperId;
    //                         $allNewspaperAmounts[] = $amount;
    //                     }
    //                 }
    //             }

    //             // Add this INF to formatted details
    //             $formattedInfDetails[] = [
    //                 'inf_number' => $infNumber,
    //                 'newspapers' => $infNewspapers
    //             ];
    //         }

    //         // Get unique INF numbers
    //         $uniqueInfNumbers = array_unique($allInfNumbers);
    //         $uniqueInfNumbers = array_values($uniqueInfNumbers); // Re-index

    //         // 6. Create PLA entry
    //         $plaAccount = PlaAcount::create([
    //             // Store unique INF numbers
    //             'inf_number'            => $uniqueInfNumbers,
    //             'department_id'         => $treasuryChallan->department_id,
    //             'office_id'             => $treasuryChallan->office_id,
    //             'cheque_no'             => $treasuryChallan->cheque_number,
    //             'cheque_date'           => $treasuryChallan->cheque_date,
    //             'challan_no'            => $treasuryChallan->challan_number,
    //             'total_cheque_amount'   => $treasuryChallan->total_amount,

    //             // Store parallel arrays (matching position by position)
    //             'newspaper_id'          => $allNewspaperIds,
    //             'newspaper_amount'      => $allNewspaperAmounts,

    //             // Store formatted details
    //             'inf_details'           => $formattedInfDetails,
    //             'created_by' => Auth::id(),
    //         ]);

    //         // Update treasury challan status
    //         $treasuryChallan->update([
    //             'status_id' =>     $approvedStatus->id,
    //             'forwarded_to_role_id' => 3,
    //             'approved_by' => Auth::id(),
    //             'approved_at' => now(),
    //         ]);

    //         DB::commit();

    //         return redirect()->back()
    //             ->with('success', 'Cheque verified and amount credit to PLA.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         \Log::error('modalData Error: ' . $e->getMessage(), [
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return back()->with('error', 'Error: ' . $e->getMessage());
    //     }
    // }


    // new logic
    public function dgAprovePla(Request $request, $id)
    {
        $request->validate([
            'confirm_verification' => 'required|accepted',
        ]);

        DB::beginTransaction();

        try {
            $treasuryChallan = TreasuryChallan::findOrFail($id);
            $approvedStatus = Status::where('title', 'Approved')->first();
            $rejectedStatus = Status::where('title', 'Rejected')->first();

            // Prevent duplicate approval
            if ($treasuryChallan->status_id == $approvedStatus->id) {
                return back()->with('warning', 'Already approved.');
            }
            if ($treasuryChallan->status_id == $rejectedStatus->id) {
                return back()->with('warning', 'Already rejected.');
            }

            // Handle rejection
            if ($request->has('reject')) {
                $treasuryChallan->update([
                    'status_id' => $rejectedStatus->id,
                    'rejection_reason' => $request->rejection_reason,
                ]);
                DB::commit();
                return back()->with('info', 'Treasury challan rejected.');
            }

            // Fetch bills from bill_classified_ads using inf_numbers
            $infNumbers = (array) $treasuryChallan->inf_number; // ensure it's an array
            $bills = BillClassifiedAd::whereIn('inf_number', $infNumbers)->get();

            if ($bills->isEmpty()) {
                throw new \Exception('No bills found for the provided INF numbers.');
            }

            // Create PLA account
            $plaAccount = PlaAcount::create([
                'inf_number' => $treasuryChallan->inf_number,
                'department_id' => $treasuryChallan->department_id,
                'office_id'     => $treasuryChallan->office_id,
                'cheque_no'     => $treasuryChallan->cheque_number,
                'cheque_date'   => $treasuryChallan->cheque_date,
                'challan_no'    => $treasuryChallan->challan_number,
                'total_cheque_amount' => $treasuryChallan->total_amount,
                'created_by'    => Auth::id(),
            ]);

            $totalAllocated = 0; // to verify sum matches challan total

            // Create PLA items per bill
            foreach ($bills as $bill) {
                // Determine if it's an agency bill: check for non-empty agency_share_amounts or if user has agency_id
                $agencyId = $bill->user->adv_agency_id ?? null;
                $isAgency = !empty($bill->agency_share_amounts) || $agencyId;

                if ($isAgency) {
                    // Agency case: multiple newspapers, amounts stored in arrays
                    $newspaperIds = $bill->newspaper_id; // already cast to array
                    $newspaperShares = $bill->newspaper_share_amounts ?? [];
                    $agencyShares = $bill->agency_share_amounts ?? [];
                    $grossAmounts = $bill->total_cost_per_newspaper ?? []; // <-- gross per newspaper
                    // Optionally use total_amount_with_taxes if needed for net payable
                    $finalAmounts = $bill->total_amount_with_taxes ?? [];

                    if (count($newspaperIds) !== count($newspaperShares)) {
                        throw new \Exception("Mismatch between newspaper count and shares for INF {$bill->inf_number}");
                    }

                    foreach ($newspaperIds as $index => $newspaperId) {
                        $newspaperAmount = $newspaperShares[$index] ?? 0;
                        $commission = $agencyShares[$index] ?? 0;
                        $gross = $grossAmounts[$index] ?? ($newspaperAmount + $commission); // fallback if gross missing
                        // If you want to use the final amount including taxes for net_payable, you can use $finalAmounts[$index]
                        $finalAmountsWithTax = $finalAmounts[$index];
                        $netPayable = $newspaperAmount; // or $finalAmounts[$index] if that's the actual newspaper payment

                        $plaItem = PlaAccountItem::create([
                            'pla_acount_id'          => $plaAccount->id,
                            'inf_number'               => $bill->inf_number,
                            'newspaper_id'             => $newspaperId,
                            'newspaper_amount'         => $newspaperAmount,
                            'adv_agency_id'            => $agencyId,
                            'agency_commission_amount' => $commission,
                            'net_payable'               => $netPayable,
                            'inf_details'               => ['bill_id' => $bill->id],
                        ]);

                        $totalAllocated += $finalAmountsWithTax; // or $netPayable, depending on what represents the newspaper's portion
                    }
                } else {
                    // Direct case: single newspaper
                    $newspaperId = $bill->newspaper_id; // scalar, not array
                    if (is_array($newspaperId)) {
                        $newspaperId = $newspaperId[0] ?? null;
                    }
                    $amount = $bill->printed_total_bill; // scalar

                    $plaItem = PlaAccountItem::create([
                        'pla_acount_id'          => $plaAccount->id,
                        'inf_number'               => $bill->inf_number,
                        'newspaper_id'             => $newspaperId,
                        'newspaper_amount'         => $amount,
                        'adv_agency_id'            => null,
                        'agency_commission_amount' => 0,
                        'net_payable'               => $amount,
                        'inf_details'               => ['bill_id' => $bill->id],
                    ]);

                    $totalAllocated += $amount;
                }
            }

            // Verify total allocated matches challan amount (allow small rounding difference)
            if (abs($totalAllocated - $treasuryChallan->total_amount) > 0.01) {
                throw new \Exception("Allocated amount ($totalAllocated) does not match challan total ({$treasuryChallan->total_amount})");
            }

            // Update treasury challan status to approved
            $treasuryChallan->update([
                'status_id'          => $approvedStatus->id,
                'forwarded_to_role_id' => 3,
                'approved_by'        => Auth::id(),
                'approved_at'        => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Cheque verified and credited to PLA.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('dgAprovePla error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', $e->getMessage());
        }
    }

    // public function plaIndex(Request $request)
    // {
    //     $query = PlaAcount::query();

    //     // Apply search/filters
    //     $this->applyFilters($query, $request);

    //     // Get the filtered query
    //     $plaAcounts = $query->with('plaAccountItems')->get();
    //     // Calculate total of all cheque amounts
    //     $totalAmountPla = $plaAcounts->sum('total_cheque_amount');

    //     // $plaAcounts = $plaAcounts->orderBy('created_at', 'desc')->paginate(10);

    //     // $statuses   = Status::all(); // all statuses
    //     $departments = Department::all(); // all departments (adjust as needed)
    //     $offices     = Office::all();     // all offices

    //     return view('pla-accounts.index', [
    //         'plaAcounts' => $plaAcounts,
    //         'totalAmountPla' => $totalAmountPla,
    //         // 'statuses'            => $statuses,
    //         'departments'         => $departments,
    //         'offices'             => $offices,
    //     ]);
    // }

    public function plaIndex(Request $request)
    {
        $query = PlaAcount::query()
            ->with(['department', 'office', 'plaAccountItems'])
            ->plaFilter($request);   // ← applies all filters

        $plaAcounts = $query->latest()
            ->paginate(15)
            ->appends($request->query());

        // Total of filtered records (not just current page)
        $totalAmountPla = $query->sum('total_cheque_amount');

        $departments = Department::all();
        $offices     = Office::all();

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Recovery & PLA', 'url' => route('billings.treasury-challans.plaIndex')],
            ['label' => 'PLA Account', 'url' => null],
        ];

        return view('pla-accounts.index', compact(
            'plaAcounts',
            'totalAmountPla',
            'departments',
            'offices',
            'breadcrumbs'
        ));
    }

    public function plaExportExcel(Request $request)
    {
        $query = PlaAcount::query()
            ->with(['office', 'plaAccountItems'])
            ->plaFilter($request);

        $plaAcounts = $query->latest()->get();

        $exportData = $plaAcounts->map(function ($pla) {
            $inf = $pla->plaAccountItems?->pluck('inf_number')->filter()->unique()->implode(', ') ?? '';

            return [
                'S. No.' => '',
                'ID' => $pla->id,
                'INF No.' => $inf,
                'Cheque Number' => $pla->cheque_no ?? '',
                'Cheque Date' => $pla->cheque_date ? \Carbon\Carbon::parse($pla->cheque_date)->format('d M Y') : '',
                'Office' => $pla->office?->ddo_name ?? '',
                'Challan Number' => $pla->challan_no ?? '',
                'Amount Received' => $pla->total_cheque_amount !== null ? number_format((float) $pla->total_cheque_amount, 0) : '',
            ];
        })->toArray();

        foreach ($exportData as $index => &$row) {
            $row['S. No.'] = $index + 1;
        }

        $filename = 'pla-accounts-' . date('Y-m-d') . '.xlsx';

        return Excel::download(new class($exportData) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            private array $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return array_keys($this->data[0] ?? []);
            }
        }, $filename);
    }

    public function plaExportPdf(Request $request)
    {
        $query = PlaAcount::query()
            ->with(['office', 'plaAccountItems'])
            ->plaFilter($request);

        $plaAcounts = $query->latest()->get();

        $pdf = Pdf::loadView('exports.pla_accounts_pdf', [
            'plaAcounts' => $plaAcounts,
            'generatedAt' => now(),
        ])->setPaper('A4', 'landscape');

        return $pdf->download('pla-accounts-' . date('Y-m-d') . '.pdf');
    }

    // public function plaView($id)
    // {
    //     $plaAcountData = PlaAcount::findOrFail($id);

    //     // orgnaize newspaper amounts by inf number
    //     $organizedData = [];

    //     // Check if we have the necessary arrays
    //     if (is_array($plaAcountData->inf_number) && is_array($plaAcountData->newspaper_amount)) {
    //         // Assuming inf_number and newspaper_amounts are parallel arrays
    //         // For example:
    //         // inf_number = ['INF123', 'INF456', 'INF789']
    //         // newspaper_amount = [5000, 7000, 3000]

    //         // Also, you might have newspaper_ids array if you store them
    //         $newspaperIds = $plaAcountData->newspaper_id ?? [];

    //         // Create a simple grouping by INF number
    //         // If each INF has multiple newspapers, you'll need to adjust this logic
    //         foreach ($plaAcountData->inf_number as $index => $infNumber) {
    //             if (!isset($organizedData[$infNumber])) {
    //                 $organizedData[$infNumber] = [
    //                     'newspaper_id' => [],
    //                     'newspaper_amount' => []
    //                 ];
    //             }

    //             // Add newspaper ID if available
    //             if (isset($newspaperIds[$index])) {
    //                 $organizedData[$infNumber]['newspaper_id'][] = $newspaperIds[$index];
    //             } else {
    //                 // Or just use a placeholder
    //                 $organizedData[$infNumber]['newspaper_id'][] = 'Newspaper ' . ($index + 1);
    //             }

    //             // Add amount
    //             if (isset($plaAcountData->newspaper_amount[$index])) {
    //                 $organizedData[$infNumber]['newspaper_amount'][] = $plaAcountData->newspaper_amount[$index];
    //             }
    //         }
    //     }

    //     return view('pla-accounts.show', [
    //         'plaAcountData' => $plaAcountData,
    //         'organizedData' => $organizedData
    //     ]);
    // }

    // public function plaView($id)
    // {
    //     $plaAcountData = PlaAcount::with('plaAccountItems.newspaper', 'office')->findOrFail($id);
    //     $total = PlaAccountItem::sum('newspaper_amount');
    //     // dd($plaAcountData->id);
    //     // Initialize
    //     $organizedData = [];

    //     // Check if we have inf_details
    //     if (isset($plaAcountData->inf_details) && is_array($plaAcountData->inf_details)) {
    //         $organizedData = $plaAcountData->inf_details;

    //         // Debug: Check structure
    //         // dd([
    //         //     'inf_details' => $organizedData,
    //         //     'count' => count($organizedData)
    //         // ]);
    //     }

    //     // If no inf_details, try to organize from parallel arrays
    //     if (empty($organizedData)) {
    //         $infNumbers = $plaAcountData->inf_number ?? [];
    //         $newspaperIds = $plaAcountData->newspaper_id ?? [];
    //         $newspaperAmounts = $plaAcountData->newspaper_amount ?? [];

    //         if (!empty($infNumbers) && !empty($newspaperIds) && !empty($newspaperAmounts)) {
    //             // Group by INF number
    //             $groupedData = [];

    //             // Each INF in infNumbers corresponds to one or more newspapers
    //             // infNumbers array might be: ['INF1', 'INF1', 'INF2', 'INF2', 'INF2']
    //             // newspaperIds array: [1, 2, 3, 4, 5]
    //             // newspaperAmounts: [100, 200, 300, 400, 500]

    //             for ($i = 0; $i < count($newspaperIds); $i++) {
    //                 $infNumber = isset($infNumbers[$i]) ? $infNumbers[$i] : $infNumbers[0];
    //                 $newspaperId = $newspaperIds[$i];
    //                 $amount = $newspaperAmounts[$i];

    //                 if (!isset($groupedData[$infNumber])) {
    //                     $groupedData[$infNumber] = [];
    //                 }

    //                 $groupedData[$infNumber][] = [
    //                     'newspaper_id' => $newspaperId,
    //                     'amount' => $amount
    //                 ];
    //             }

    //             // Convert to organized format
    //             foreach ($groupedData as $infNumber => $newspapers) {
    //                 $organizedData[] = [
    //                     'inf_number' => $infNumber,
    //                     'newspapers' => $newspapers
    //                 ];
    //             }
    //         }
    //     }

    //     // Fetch newspaper names
    //     if (!empty($organizedData)) {
    //         // Collect all newspaper IDs
    //         $allNewspaperIds = [];
    //         foreach ($organizedData as $infDetail) {
    //             foreach ($infDetail['newspapers'] as $newspaper) {
    //                 $allNewspaperIds[] = $newspaper['newspaper_id'];
    //             }
    //         }

    //         // Remove duplicates
    //         $allNewspaperIds = array_unique($allNewspaperIds);

    //         // Fetch newspaper names
    //         $newspaperRecords = \App\Models\Newspaper::whereIn('id', $allNewspaperIds)
    //             ->select('id', 'title')
    //             ->get()
    //             ->keyBy('id')
    //             ->toArray();

    //         // Add newspaper names to organized data
    //         foreach ($organizedData as &$infDetail) {
    //             foreach ($infDetail['newspapers'] as &$newspaper) {
    //                 $newspaperId = $newspaper['newspaper_id'];
    //                 $newspaper['newspaper_name'] = isset($newspaperRecords[$newspaperId]['title'])
    //                     ? $newspaperRecords[$newspaperId]['title']
    //                     : 'Newspaper ID: ' . $newspaperId;

    //                 if (isset($newspaperRecords[$newspaperId]['newspaper_code'])) {
    //                     $newspaper['newspaper_code'] = $newspaperRecords[$newspaperId]['newspaper_code'];
    //                 }
    //             }
    //         }
    //     }

    //     return view('pla-accounts.show', [
    //         'plaAcountData' => $plaAcountData,
    //         'organizedData' => $organizedData,
    //         'total' =>     $total
    //     ]);
    // }
    public function plaView($id)
    {
        $plaAcountData = PlaAcount::with([
            'plaAccountItems.newspaper',
            'plaAccountItems.agency', // assuming relationship name is 'agency'
            'office'
        ])->findOrFail($id);

        // fora
        // $newspaperKpraTax = $items->sum('kpra_2_percent_on_85_percent_newspaper');
        // dd($newspaperKpraTax);
        // $agencyKpraTax = $items->sum('kpra_10_percent_on_15_percent_agency');


        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'PLA Account', 'url' => route('billings.treasury-challans.plaIndex')],
            ['label' => 'PLA Account Details', 'url' => null],
        ];

        return view('pla-accounts.show', compact('plaAcountData', 'breadcrumbs'));
    }

    // Exports method define below

    // for excel
    public function exportExcel(Request $request)
    {
        $query = $this->buildIndexQuery($request);

        $data = $query->get();

        return Excel::download(
            new TreasuryChallanExport($data),
            'treasury_challans.xlsx'
        );
    }

    // for pdf
    public function exportPdf(Request $request)
    {
        $query = $this->buildIndexQuery($request);

        $data = $query->get();

        $title = 'Treasury Challans Report';
        $pdf = Pdf::loadView('treasury-challans.pdf', compact('data', 'title'));

        return $pdf->download('treasury_challans.pdf');
    }

    private function buildIndexQuery($request)
    {
        $query = TreasuryChallan::query();

        $user = Auth::user();

        $pendingChequeApprovalStatus      = Status::where('title', 'Pending Cheque Approval')->first();
        $pendingChequeVerificationStatus  = Status::where('title', 'Pending Cheque Verification')->first();
        $approvedStatus                   = Status::where('title', 'Approved')->first();
        $pendingStatus                    = Status::where('title', 'Pending Department Approval')->first();
        $receiptPendingChequeStatus       = Status::where('title', 'Receipt Pending cheque')->first();

        // start query here
        $query = TreasuryChallan::query();

        // ROLE BASED FILTER
        if ($user->hasRole('Superintendent')) {

            $query->whereIn('status_id', [
                $receiptPendingChequeStatus?->id,
                $pendingChequeVerificationStatus?->id,
                $pendingChequeApprovalStatus?->id,
                $approvedStatus?->id,
            ]);

            $query->orderByRaw("FIELD(status_id, ?, ?, ?)", [
                $pendingChequeVerificationStatus?->id,
                $pendingChequeApprovalStatus?->id,
                $approvedStatus?->id
            ]);
        } elseif ($user->hasRole('Director General')) {

            $query->whereIn('status_id', [
                $pendingChequeApprovalStatus?->id,
                $approvedStatus?->id,
            ]);

            $query->orderByRaw("FIELD(status_id, ?, ?)", [
                $pendingChequeApprovalStatus?->id,
                $approvedStatus?->id
            ]);
        }



        $this->applyFilters($query, $request);

        return $query;
    }
}
