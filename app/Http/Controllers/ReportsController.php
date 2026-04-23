<?php

namespace App\Http\Controllers;

use App\Exports\AdvertisementsExport;
use App\Exports\AgencyPlaAmountExport;
use App\Exports\NewspaperPlaAmountExport;
use App\Models\AdvAgency;
use App\Models\Advertisement;
use App\Models\BillClassifiedAd;
use App\Models\ClassifiedAdType;
use App\Models\Department;
use App\Models\Newspaper;
use App\Models\Office;
use App\Models\PlaAccountItem;
use App\Models\Status;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereIn('inf_number[]', 'LIKE', "%{$search}%")
                    ->orWhereHas('department', fn($dq) => $dq->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('office', fn($oq) => $oq->where('ddo_name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('status', fn($sq) => $sq->where('title', 'LIKE', "%{$search}%"))
                    ->orWhere('created_at', 'LIKE', "%{$search}%")
                    ->orWhere('publish_on_or_before', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('inf_number[]')) {
            $query->whereIn('inf_number[]', $request->inf_number[]);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->filled('submission_from')) {
            $query->whereDate('created_at', '>=', $request->submission_from);
        }
        if ($request->filled('submission_to')) {
            $query->whereDate('created_at', '<=', $request->submission_to);
        }
        if ($request->filled('publication_from')) {
            $query->whereDate('publish_on_or_before', '>=', $request->publication_from);
        }
        if ($request->filled('publication_to')) {
            $query->whereDate('publish_on_or_before', '<=', $request->publication_to);
        }

        return $query;
    }

    protected $statusMap = [
        3 => 'New',
        4 => 'In progress',
        10 => 'Approved',
        8 => 'Published',
        7 => 'Rejected',
    ];

    public function index(Request $request)
    {
        $statusId = $request->get('status_id', 3); // default = New
        $search   = $request->get('search');
        $from     = $request->get('from');
        $to       = $request->get('to');

        // Get counts for each status
        $statusCounts = [];
        foreach ($this->statusMap as $id => $label) {
            if ($id == 8) {
                // Published (pivot relation)
                $statusCounts[$id] = DB::table('advertisement_newspaper')->distinct('advertisement_id')->count('advertisement_id');
            } else {
                $statusCounts[$id] = Advertisement::where('status_id', $id)->count();
            }
        }

        // Fetch ads
        if ($statusId == 8) {
            // Published ads from pivot
            $ads = Advertisement::whereHas('newspapers', function ($q) {
                $q->whereNotNull('advertisement_newspaper.id');
            })
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%$search%")
                            ->orWhere('client_name', 'like', "%$search%");
                    });
                })
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, $to]);
                })
                ->latest()
                ->paginate(10);
        } else {
            $ads = Advertisement::query()
                ->where('status_id', $statusId)
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%$search%")
                            ->orWhere('client_name', 'like', "%$search%");
                    });
                })
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, $to]);
                })
                ->latest()
                ->paginate(10);
        }

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Reports', 'url' => null],
            ['label' => 'Status Wise', 'url' => null],
        ];

        return view('reports.index', [
            'ads'          => $ads,
            'statuses'     => $this->statusMap,
            'statusCounts' => $statusCounts,
            'statusId'     => $statusId,
            'search'       => $search,
            'from'         => $from,
            'to'           => $to,
            'breadcrumbs'  => $breadcrumbs,
        ]);
    }

    public function exportExcel(Request $request, $statusId)
    {
        $search = $request->get('search');
        $from   = $request->get('from');
        $to     = $request->get('to');

        return Excel::download(
            new AdvertisementsExport($statusId, $search, $from, $to),
            'status_' . $statusId . '_report.xlsx'
        );
    }

    public function exportPdf(Request $request, $statusId)
    {

        $search = $request->get('search');
        $from   = $request->get('from');
        $to     = $request->get('to');

        $query = Advertisement::query();

        if ($statusId == 8) {
            $query->whereHas('newspapers', function ($q) {
                $q->whereNotNull('advertisement_newspaper.id');
            });
        } else {
            $query->where('status_id', $statusId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('client_name', 'like', "%$search%");
            });
        }

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        $ads = $query->latest()->get();

        // Get status name from the map
        $statusName = $this->statusMap[$statusId] ?? 'unknown';
        // Convert to lowercase and replace spaces with underscores (slug)
        $slug = Str::slug($statusName, '_');

        $pdf = Pdf::loadView('reports.status-wise-pdf', compact('ads', 'statusId'));
        return $pdf->download('status_wise_report.pdf');
    }




    // ------------------------------------------------------- //
    // 1. Status Wise Report
    // public function statusWise(Request $request)
    // {

    //     // Page title
    //     $pageTitle = 'Statuses Reports &#x2053; IAMS-IPR';

    //     // Breadcrumb
    //     $breadcrumbs = [
    //         ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
    //         ['label' => 'Statuses Reports', 'url' => null],
    //     ];

    //     $startDate = $request->start_date ?? now()->startOfYear()->toDateString();
    //     $endDate   = $request->end_date ?? now()->endOfYear()->toDateString();

    //     $summary = Advertisement::select('status_id', DB::raw('COUNT(*) as total'))
    //         ->whereBetween('created_at', [$startDate, $endDate])
    //         ->groupBy('status_id')
    //         ->get();

    //     $statusMap = [
    //         3 => 'New',
    //         4 => 'In progress',
    //         10 => 'Approved',
    //         8 => 'Published',
    //         7 => 'Rejected',
    //     ];

    //     $data = [];
    //     foreach ($summary as $row) {
    //         $statusName = $statusMap[$row->status_id] ?? "Other";
    //         $data[] = ['label' => $statusName, 'count' => $row->total];
    //     }

    //     if ($request->export === 'excel') {
    //         return Excel::download(new \App\Exports\ArrayExport($data), 'status_report.xlsx');
    //     }

    //     if ($request->export === 'pdf') {
    //         $pdf = Pdf::loadView('reports.export', ['title' => 'Status Wise Report', 'data' => $data]);
    //         return $pdf->download('status_wise_report.pdf');
    //     }

    //     return view('reports.status', compact('pageTitle', 'breadcrumbs', 'data', 'startDate', 'endDate'));
    // }

    // 2. Department Wise Report


    public function departmentWise(Request $request)
    {
        $department_id = $request->get('department_id');
        $from          = $request->get('from');
        $to            = $request->get('to');

        // Fetch all departments for dropdown
        $allDepartments = Department::all();

        // Base query
        $query = Advertisement::query();

        // Filter by specific department
        if ($department_id) {
            $query->where('department_id', $department_id);
        }

        // Date range filter
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        // Group by department
        $summary = $query->select('department_id', DB::raw('COUNT(*) as total'))->where('office_id', null)
            ->groupBy('department_id')
            ->with('department')
            ->get();

        $data = [];
        foreach ($summary as $row) {
            $data[] = [
                'label' => $row->department->name ?? "Unknown",
                'count' => $row->total
            ];
        }

        // Export handling
        if ($request->export === 'excel') {
            return Excel::download(new \App\Exports\ArrayExport($data), 'department_report.xlsx');
        }
        if ($request->export === 'pdf') {
            $pdf = Pdf::loadView('reports.department-wise-pdf', [
                'title'   => 'Department Wise Report',
                'data'    => $data,
                'from'    => $from,
                'to'      => $to,
            ]);
            return $pdf->download('department_wise_report.pdf');
        }

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Reports', 'url' => route('reports.index')],
            ['label' => 'Department Wise', 'url' => null],
        ];

        return view('reports.department', compact('data', 'allDepartments', 'department_id', 'from', 'to', 'breadcrumbs'));
    }

    // 3. Office Wise Report
    public function officeWise(Request $request)
    {
        $search = $request->get('search');
        $office_id = $request->office_id;
        $from   = $request->get('from');
        $to     = $request->get('to');

        $allOffices = Office::all();

        // Base query with optional filters
        $query = Advertisement::query();

        // Search by office name (ddo_name)
        // if ($search) {
        //     $query->whereHas('office', function ($q) use ($search) {
        //         $q->where('ddo_name', 'like', "%{$search}%");
        //     });
        // }

        // Filter by specific office (if selected)
        if ($office_id) {
            $query->where('office_id', $office_id);
        }

        // Date range filter
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        $summary = $query->select('office_id', DB::raw('COUNT(*) as total'))
            ->groupBy('office_id')
            ->with('office')
            ->get();


        $data = [];
        foreach ($summary as $row) {
            $data[] = [
                'label' => $row->office->ddo_name ?? "Unknown",
                'count' => $row->total
            ];
        }

        if ($request->export === 'excel') {
            return Excel::download(new \App\Exports\ArrayExport($data), 'office_report.xlsx');
        }
        if ($request->export === 'pdf') {
            $pdf = Pdf::loadView('reports.office-wise-pdf', [
                'title' => 'Office Wise Reports',
                'data'      => $data,
                'from'      => $from,
                'to'        => $to
            ]);
            return $pdf->download('office_wise_report.pdf');
        }

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Reports', 'url' => route('reports.index')],
            ['label' => 'Office Wise', 'url' => null],
        ];

        return view('reports.office', compact('data', 'allOffices', 'office_id', 'from', 'to', 'breadcrumbs'));
    }

    // 4. Category Wise Report
    // public function categoryWise(Request $request)
    // {
    //     $summary = Advertisement::select('classified_ad_type_id', DB::raw('COUNT(*) as total'))
    //         ->groupBy('classified_ad_type_id')
    //         ->with('classifiedAdType')
    //         ->get();

    //     $data = [];
    //     foreach ($summary as $row) {
    //         $data[] = [
    //             'label' => $row->classifiedAdType->title ?? "Unknown",
    //             'count' => $row->total
    //         ];
    //     }

    //     if ($request->export === 'excel') {
    //         return Excel::download(new \App\Exports\ArrayExport($data), 'category_report.xlsx');
    //     }
    //     if ($request->export === 'pdf') {
    //         $pdf = Pdf::loadView('reports.export', ['title' => 'Category Wise Report', 'data' => $data]);
    //         return $pdf->download('category_report.pdf');
    //     }

    //     return view('reports.category', compact('data'));
    // }

    public function categoryWise(Request $request)
    {
        $category_id = $request->get('category_id');
        $from        = $request->get('from');
        $to          = $request->get('to');

        // Fetch all categories for dropdown
        $allCategories = ClassifiedAdType::all();

        // Base query
        $query = Advertisement::query();

        // Filter by specific category
        if ($category_id) {
            $query->where('classified_ad_type_id', $category_id);
        }

        // Date range filter
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        // Group by category
        $summary = $query->select('classified_ad_type_id', DB::raw('COUNT(*) as total'))
            ->groupBy('classified_ad_type_id')
            ->with('classified_ad_type')
            ->get();


        $data = [];
        foreach ($summary as $row) {
            // Debuging to see data
            // dd($row->classified_ad_type);
            $data[] = [
                'label' => $row->classified_ad_type->type ?? "Unknown",
                'count' => $row->total
            ];
        }

        // dd($data);

        // Export handling
        if ($request->export === 'excel') {
            return Excel::download(new \App\Exports\ArrayExport($data), 'category_report.xlsx');
        }
        if ($request->export === 'pdf') {
            $pdf = Pdf::loadView('reports.category-wise-pdf', [
                'title' => 'Category Wise Report',
                'data'  => $data,
                'from'  => $from,
                'to'    => $to,
            ]);
            return $pdf->download('category_wise_report.pdf');
        }

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Reports', 'url' => route('reports.index')],
            ['label' => 'Category Wise', 'url' => null],
        ];

        return view('reports.category', compact('data', 'allCategories', 'category_id', 'from', 'to', 'breadcrumbs'));
    }

    // 5. Year Wise Report
    public function yearWise(Request $request)
    {
        $from = $request->get('from');
        $to   = $request->get('to');

        // Base query
        $query = Advertisement::query();

        // Date range filter
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        // Group by year
        $summary = $query->select(DB::raw('YEAR(created_at) as year'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('YEAR(created_at)'))
            ->orderBy('year', 'desc')
            ->get();

        $data = [];
        foreach ($summary as $row) {
            $data[] = [
                'label' => $row->year,
                'total' => $row->total
            ];
        }

        // Export handling
        if ($request->export === 'excel') {
            return Excel::download(new \App\Exports\ArrayExport($data), 'year_report.xlsx');
        }
        if ($request->export === 'pdf') {
            $pdf = Pdf::loadView('reports.year-wise-pdf', [
                'title' => 'Year Wise Report',
                'data'  => $data,
                'from'  => $from,
                'to'    => $to,
            ]);
            return $pdf->download('year_wise_report.pdf');
        }

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Reports', 'url' => route('reports.index')],
            ['label' => 'Year Wise', 'url' => null],
        ];

        return view('reports.year', compact('data', 'from', 'to', 'breadcrumbs'));
    }

    // public function officesAdvtList(Request $request)
    // {
    //     $pageTitle = 'Offices Ads List &#x2053; IAMS-IPR';

    //     // Breadcrumb
    //     $breadcrumbs = [
    //         ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
    //         ['label' => 'Offices Adverttisements', 'url' => route('reports.officesAdvtList')],
    //         ['label' => 'Offices Ads List', 'url' => null], // The current page (no URL)
    //     ];
    //     $user = auth()->user();
    //     $current_user_role = $user->roles->pluck('id')->first();
    //     // Determine user type based on department_id and office_id
    //     $is_department_user = ($user->department_id && is_null($user->office_id));
    //     $is_office_user = ($user->department_id && !is_null($user->office_id));
    //     $advertisements = Advertisement::all();


    //     return view('reports.offices-advt-list', [
    //         'pageTitle' =>  $pageTitle,
    //         'breadcrumbs' => $breadcrumbs,
    //         'advertisements' => $advertisements,
    //         'is_department_user' => $is_department_user,
    //         'is_office_user' => $is_office_user,
    //     ]);
    // }

    /**
     * Get office-level advertisements for the logged-in department user
     * This function fetches advertisements from offices that belong to the user's department
     */
    public function officesAdvtList(Request $request)
    {
        $pageTitle = 'Offices Ads List &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Offices Adverttisements', 'url' => route('reports.officesAdvtList')],
            ['label' => 'Offices Ads List', 'url' => null], // The current page (no URL)
        ];
        // Check if user is authenticated and is a department user
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        //     // Determine user type based on department_id and office_id
        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));

        // Check if user is a department user (you can adjust this based on your user roles)
        if (!$is_department_user) {
            abort(403, 'Unauthorized access. This page is for department users only.');
        }

        // Get the current department user's department ID
        $currentDepartmentId = $user->department_id;
        // dd($currentDepartmentId);

        // If user doesn't have a department ID, handle the error
        if (!$currentDepartmentId) {
            return back()->with('error', 'User is not associated with any department.');
        }

        // Get all office IDs that belong to this department
        // Assuming you have an Office model with a department_id field
        $officeIds = Office::where('department_id', $currentDepartmentId)
            ->pluck('id')
            ->toArray();

        // If no offices found under this department
        if (empty($officeIds)) {
            return view('reports.offices-advt-list', [
                'advertisements' => collect(),
                'department' => $user->department,
                'offices' => collect()
            ])->with('info', 'No offices found under your department.');
        }

        // Fetch advertisements from offices under this department
        $advertisements = Advertisement::query()
            ->whereNotNull('office_id')  // Only office-level advertisements
            ->whereIn('office_id', $officeIds)  // Only from offices in this department
            ->with(['office', 'status', 'department'])  // Eager load relationships
            ->when($request->filled('status'), function ($query) use ($request) {
                // Optional: Filter by status if provided
                $query->where('status_id', $request->status);
            })
            ->when($request->filled('office'), function ($query) use ($request) {
                // Optional: Filter by specific office if provided
                $query->where('office_id', $request->office);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                // Optional: Search functionality
                $query->where(function ($q) use ($request) {
                    $q->where('advertisement_number', 'like', '%' . $request->search . '%')
                        ->orWhere('title', 'like', '%' . $request->search . '%')
                        ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')  // Latest first
            ->paginate(15);  // Paginate results

        // Get list of offices for filter dropdown
        $offices = Office::where('department_id', $currentDepartmentId)
            ->orderBy('ddo_name')
            ->get();

        // Get available statuses for filter dropdown
        $statuses = Status::all();

        return view('reports.offices-advt-list', [
            'advertisements' => $advertisements,
            'offices' => $offices,
            'statuses' => $statuses,
            'department' => $user->department,
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'is_department_user' => $is_department_user,
            'is_office_user' => $is_office_user,
        ]);
    }

    // public function billingReport(Request $request)
    // {

    //     // Page Title
    //     $pageTitle = 'Reports &#x2053; DG&#8211;IPR IAMS';

    //     // Breadcrumb
    //     $breadcrumbs = [
    //         ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
    //         ['label' => 'Billing Reports', 'url' => null], // The current page (no URL)
    //     ];

    //     $search = $request->get('search');
    //     $newspaper_id = $request->get('newspaper_id');
    //     $advertisement_id = $request->get('advertisement_id');

    //     // Start query for BillClassifiedAd
    //     $query = BillClassifiedAd::with(['advertisement', 'user']);

    //     // Apply filters if provided
    //     if ($newspaper_id) {
    //         $query->where('newspaper_id', $newspaper_id);
    //     }

    //     if ($advertisement_id) {
    //         $query->where('advertisement_id', $advertisement_id);
    //     }

    //     if ($search) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('invoice_no', 'like', '%' . $search . '%')
    //                 ->orWhereHas('advertisement', function ($subQuery) use ($search) {
    //                     $subQuery->where('inf_number', 'like', '%' . $search . '%');
    //                 });
    //         });
    //     }

    //     // Apply search/filters

    //     $billings = $query->orderBy('created_at', 'desc')->paginate(15);
    //     $this->applyFilters($billings, $request);

    //     // Get data for dropdowns
    //     $statuses   = Status::all(); // all statuses
    //     $departments = Department::all(); // all departments (adjust as needed)
    //     $offices     = Office::all();     // all offices
    //     $users = User::all();
    //     return view('reports.billing-reports', [
    //         'billings' => $billings,
    //         'search' => $search,
    //         'newspaper_id' => $newspaper_id,
    //         'advertisement_id' => $advertisement_id,
    //         'pageTitle' => $pageTitle,
    //         'breadcrumbs' => $breadcrumbs,
    //         'statuses'            => $statuses,
    //         'departments'         => $departments,
    //         'offices'             => $offices,
    //         'users' =>  $users,
    //     ]);
    // }

    // new blade
    public function billingReport(Request $request)
    {
        // Page Title & Breadcrumbs
        $pageTitle = 'Reports &#x2053; DG&#8211;IPR IAMS';
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Billing Reports', 'url' => null],
        ];

        // Get filter values
        $search            = $request->get('search');
        $newspaper_id      = $request->get('newspaper_id');
        $advertisement_id  = $request->get('advertisement_id');
        $department_id     = $request->get('department_id');
        $office_id         = $request->get('office_id');
        $causer_id         = $request->get('causer_id');
        $created_from  = $request->get('from');
        $created_to    = $request->get('to');

        // Base query with necessary relations
        $query = BillClassifiedAd::with(['advertisement', 'user']);

        // Apply filters
        if ($newspaper_id) {
            $query->where('newspaper_id', $newspaper_id);
        }

        if ($advertisement_id) {
            $query->where('advertisement_id', $advertisement_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', '%' . $search . '%')
                    ->orWhereHas('advertisement', function ($sub) use ($search) {
                        $sub->where('inf_number', 'like', '%' . $search . '%');
                    });
            });
        }

        // Department filter (via advertisement relationship)
        if ($department_id) {
            $query->whereHas('advertisement', function ($q) use ($department_id) {
                $q->where('department_id', $department_id);
            });
        }

        // Office filter (via advertisement relationship)
        if ($office_id) {
            $query->whereHas('advertisement', function ($q) use ($office_id) {
                $q->where('office_id', $office_id);
            });
        }

        // User filter (causer_id on bill table)
        if ($causer_id) {
            $query->where('causer_id', $causer_id);
        }

        // Date range filters
        if ($created_from) {
            $query->whereDate('created_at', '>=', $created_from);
        }
        if ($created_to) {
            $query->whereDate('created_at', '<=', $created_to);
        }

        // ----- SUMMARY STATISTICS (calculated BEFORE pagination) -----
        $totalBills   = $query->count();                               // total number of bills
        $totalAmount  = $query->sum('printed_total_bill');             // sum of printed_total_bill
        $avgAmount    = $query->avg('printed_total_bill');             // average bill amount

        // Count by status (e.g., 'billed', 'unbilled')
        // Use a clone for the status count – this doesn't affect the original $query
        $statusCounts = (clone $query)->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ----- PAGINATED RESULTS -----
        $billings = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Data for dropdowns
        $statuses    = Status::all();
        $departments = Department::all();
        $offices     = Office::all();
        $users       = User::all();

        return view('reports.billing-reports', compact(
            'billings',
            'search',
            'newspaper_id',
            'advertisement_id',
            'department_id',
            'office_id',
            'causer_id',
            'created_from',
            'created_to',
            'pageTitle',
            'breadcrumbs',
            'statuses',
            'departments',
            'offices',
            'users',
            'totalBills',
            'totalAmount',
            'avgAmount',
            'statusCounts'
        ));
    }


    public function billingExportExcel(Request $request)
    {
        $search = $request->get('search');
        $newspaper_id = $request->get('newspaper_id');
        $advertisement_id = $request->get('advertisement_id');
        $billingType = $request->get('billing_type'); // 'newspaper' | 'agency'

        // Start query for BillClassifiedAd
        $query = BillClassifiedAd::with(['advertisement.newspapers', 'user.agency']);

        // Keep exports consistent with their respective index pages
        if ($billingType === 'agency') {
            $query->whereHas('user', function ($uq) {
                $uq->whereNotNull('adv_agency_id');
            });
        } elseif ($billingType === 'newspaper') {
            $query->whereHas('advertisement.newspapers', function ($nq) {
                $nq->whereNull('agency_id')->where('is_published', 1);
            });
        }

        // Apply filters if provided
        if ($newspaper_id) {
            $query->where('newspaper_id', $newspaper_id);
        }

        if ($advertisement_id) {
            $query->where('advertisement_id', $advertisement_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', '%' . $search . '%')
                    ->orWhereHas('advertisement', function ($subQuery) use ($search) {
                        $subQuery->where('inf_number', 'like', '%' . $search . '%');
                    });
            });
        }

        $billings = $query->orderBy('created_at', 'desc')->get();

        // Prepare data for export
        $exportData = $billings->map(function ($billing) {
            return [
                'S. No.' => '',
                'INF No.' => $billing->advertisement->inf_number ?? '',
                'Invoice No.' => $billing->invoice_no ?? '',
                'Invoice Date' => $billing->invoice_date ?  \Carbon\Carbon::parse($billing->invoice_date)->format('d M Y') : '',
                'Publication Date' => $billing->publication_date ?  \Carbon\Carbon::parse($billing->publication_date)->format('d M Y') : '',
                'Printed Total Bill' => number_format($billing->printed_total_bill ?? 0, 2),
                'Newspaper' => implode(', ', $billing->newspaper_titles),
                'Status' => $billing->status == 'billed' ? 'Billed' : ($billing->status == 'pending' ? 'Pending' : 'Unknown'),
                'Created At' => $billing->created_at->format('d M Y'),
            ];
        })->toArray();

        // Add serial numbers
        foreach ($exportData as $index => &$data) {
            $data['S. No.'] = $index + 1;
        }

        $filename = 'billing-reports-' . date('Y-m-d') . '.xlsx';

        return Excel::download(new class($exportData) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $data;

            public function __construct($data)
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

    public function billingExportPdf(Request $request)
    {
        $search = $request->get('search');
        $newspaper_id = $request->get('newspaper_id');
        $advertisement_id = $request->get('advertisement_id');
        $billingType = $request->get('billing_type'); // 'newspaper' | 'agency'

        // Start query for BillClassifiedAd
        $query = BillClassifiedAd::with(['advertisement.newspapers', 'user.agency']);

        // Keep exports consistent with their respective index pages
        if ($billingType === 'agency') {
            $query->whereHas('user', function ($uq) {
                $uq->whereNotNull('adv_agency_id');
            });
        } elseif ($billingType === 'newspaper') {
            $query->whereHas('advertisement.newspapers', function ($nq) {
                $nq->whereNull('agency_id')->where('is_published', 1);
            });
        }

        // Apply filters if provided
        if ($newspaper_id) {
            $query->where('newspaper_id', $newspaper_id);
        }

        if ($advertisement_id) {
            $query->where('advertisement_id', $advertisement_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', '%' . $search . '%')
                    ->orWhereHas('advertisement', function ($subQuery) use ($search) {
                        $subQuery->where('inf_number', 'like', '%' . $search . '%');
                    });
            });
        }

        $billings = $query->orderBy('created_at', 'desc')->get();

        $data = [
            'billings' => $billings,
            'search' => $search,
            'newspaper_id' => $newspaper_id,
            'advertisement_id' => $advertisement_id,
        ];

        // dd($data);

        $filename = 'billing-reports-' . date('Y-m-d') . '.pdf';

        $pdf = Pdf::loadView('reports.billing-pdf', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->download($filename);
    }

    // public function newspapersPlaAmount(Request $request)
    // {
    //     $search = $request->get('search');
    //     $from   = $request->get('from');
    //     $to     = $request->get('to');

    //     $query = PlaAccountItem::with('newspaper');

    //     // Filter by Newspaper Name
    //     if (!empty($search)) {
    //         $query->whereHas('newspaper', function ($q) use ($search) {
    //             $q->where('title', 'like', "%{$search}%");
    //         });
    //     }

    //     // Filter by Date Range
    //     if (!empty($from) && !empty($to)) {
    //         $query->whereBetween('created_at', [$from, $to]);
    //     }

    //     $newspapersPlaAmount = $query->latest()->paginate(2);

    //     return view('reports.newspapers-pla-amount', [
    //         'newspapersPlaAmount' => $newspapersPlaAmount,
    //         'search' => $search,
    //         'from' => $from,
    //         'to' => $to,
    //     ]);
    // }

    public function newspapersPlaAmount(Request $request)
    {
        $search = $request->search;
        $newspaper_id = $request->newspaper_id;
        $from   = $request->from;
        $to     = $request->to;

        $query = PlaAccountItem::with('newspaper');

        // Newspaper filter
        // if ($search) {
        //     $query->whereHas('newspaper', function ($q) use ($search) {
        //         $q->where('title', 'like', "%{$search}%");
        //     });
        // }

        // Fetch all newspapers for the dropdown
        $newspapers = Newspaper::all();

        // Filter by newspaper (if selected)
        if ($newspaper_id) {
            $query->where('newspaper_id', $newspaper_id);
        }


        // Date filter
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        // Pagination
        $newspapersPlaAmount = $query->latest()->paginate(10);

        // Total PLA Amount
        $totalAmount = (clone $query)->sum('newspaper_amount');

        // Newspaper wise totals
        $newspaperTotals = PlaAccountItem::with('newspaper')
            ->selectRaw('newspaper_id, SUM(newspaper_amount) as total')
            ->groupBy('newspaper_id')
            ->get();

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Reports', 'url' => route('reports.index')],
            ['label' => 'Newspapers PLA Amount', 'url' => null],
        ];

        return view('reports.newspapers-pla-amount', compact(
            'newspapersPlaAmount',
            'totalAmount',
            'newspaperTotals',
            'newspapers',
            'newspaper_id',
            'search',
            'from',
            'to',
            'breadcrumbs'
        ));
    }

    public function newspaperPlaAmountExportExcel()
    {
        return Excel::download(new NewspaperPlaAmountExport, 'pla-report.xlsx');
    }

    public function newspaperPlaAmountexportPdf()
    {
        $data = PlaAccountItem::with('newspaper')->get();

        $pdf = Pdf::loadView('reports.newspaper-pla-pdf', compact('data'));

        return $pdf->download('newspaper.pla-report.pdf');
    }


    public function advAgenciesPlaAmount(Request $request)
    {

        $search = $request->search;
        $adv_agency_id = $request->adv_agency_id;
        $from   = $request->from;
        $to     = $request->to;

        $query = PlaAccountItem::with(['agency', 'newspaper']);

        // AdvAgencies filter
        // if ($search) {
        //     $query->whereHas('AdvAgencies', function ($q) use ($search) {
        //         $q->where('title', 'like', "%{$search}%");
        //     });
        // }

        // Fetch all AdvAgencies for the dropdown
        $advAgencies = AdvAgency::all();

        // Filter by AdvAgencies (if selected)
        if ($adv_agency_id) {
            $query->where('adv_agency_id', $adv_agency_id);
        }


        // Date filter
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        // Pagination
        $advAgenciesPlaAmount = $query->whereNotNull('adv_agency_id')->latest()->paginate(10);
        // $advAgenciesPlaAmount = DB::table('pla_account_items')
        //     ->join('adv_agencies', 'pla_account_items.adv_agency_id', '=', 'adv_agencies.id')
        //     ->join('newspapers', 'pla_account_items.newspaper_id', '=', 'newspapers.id')
        //     ->whereNotNull('pla_account_items.adv_agency_id')
        //     ->select(
        //         'adv_agencies.id as agency_id',
        //         'adv_agencies.name as agency_name',
        //         DB::raw('SUM(pla_account_items.agency_commission_amount) as total_agency_commission'),
        //         DB::raw('GROUP_CONCAT(DISTINCT newspapers.title SEPARATOR ", ") as newspapers')
        //     )
        //     ->groupBy('adv_agencies.id', 'adv_agencies.name')
        //     ->orderBy('adv_agencies.name')  // or orderBy('total_amount', 'desc')
        //     ->paginate(10);

        // Total agency wise PLA Amount
        $totalAmount = (clone $query)->sum('agency_commission_amount');

        // AdvAgencies wise totals
        $advAgencyTotals =   PlaAccountItem::with('agency')->whereNotNull('adv_agency_id')->selectRaw('adv_agency_id, SUM(agency_commission_amount) as total')
            ->groupBy('adv_agency_id')
            ->get();

        $totalAgencies = $advAgencyTotals->count();

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Reports', 'url' => route('reports.index')],
            ['label' => 'Agency Wise PLA Amount', 'url' => null],
        ];

        return view('reports.adv-agencies-pla-amount', compact(
            'advAgenciesPlaAmount',
            'totalAmount',
            'advAgencyTotals',
            'advAgencies',
            'adv_agency_id',
            'search',
            'from',
            'to',
            'totalAgencies',
            'breadcrumbs'
        ));
    }

    public function agencyPlaAmountExportExcel()
    {
        return Excel::download(new AgencyPlaAmountExport, 'pla-report.xlsx');
    }

    public function agencyPlaAmountexportPdf()
    {
        $data = PlaAccountItem::with(['agency', 'newspaper'])->whereNotNull('adv_agency_id')->get();

        $pdf = Pdf::loadView('reports.agency-pla-pdf', compact('data'));

        return $pdf->download('agecny.pla-report.pdf');
    }
}
