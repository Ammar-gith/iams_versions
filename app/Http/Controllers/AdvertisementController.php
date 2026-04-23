<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Office;
use App\Models\Status;
use App\Models\AdvAgency;
use App\Models\INFSeries;
use App\Models\Newspaper;
use App\Models\AdCategory;
use App\Models\Department;
use App\Models\AdChangeLog;
use App\Models\NewsPosRate;
use Illuminate\Http\Request;
use App\Models\Advertisement;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AdWorthParameter;
use App\Models\BillClassifiedAd;
use App\Models\ClassifiedAdType;
use App\Models\AdRejectionReason;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Events\AdvertisementCreated;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdvertisementsExport;
use Spatie\Permission\Traits\hasRole;
use App\Events\AdvertisementSubmitted;
use App\Notifications\AdvertisementNotification;



class AdvertisementController extends Controller
{
    // Show All Ads
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
                    ->orWhere('publish_on_or_before', 'LIKE', "%{$search}%");
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

    // public function index(Request $request)
    // {
    //     // Page Title
    //     $pageTitle = 'New Ads &#x2053; DG&#8211;IPR IAMS';

    //     // Breadcrumb
    //     $breadcrumbs = [
    //         ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
    //         ['label' => 'New Ads', 'url' => null], // The current page (no URL)
    //     ];

    //     $user = auth()->user();
    //     $userId = $user->id;
    //     // dd($userId);

    //     // Determine user type
    //     $is_department_user = ($user->department_id && is_null($user->office_id));
    //     $is_office_user = ($user->department_id && !is_null($user->office_id));

    //     // Get status IDs
    //     $new_status = Status::where('title', 'New')->value('id');
    //     $forwarded_status = Status::where('title', 'Forwarded')->value('id');
    //     $approved_status = Status::where('title', 'Approved')->value('id');
    //     $pending_department_status = Status::where('title', 'Pending Department Approval')->value('id');
    //     $rejected_department_status = Status::where('title', 'Rejected by Department')->value('id');
    //     $sent_back_status = Status::where('title', 'Sent Back to Office')->value('id');
    //     $draft_status = Status::where('title', 'Draft')->value('id');

    //     // Show only not archived
    //     $advertisements = Advertisement::query()
    //         ->whereNull('archived_at');

    //     $advertisements->where(function ($query) use ($user, $is_office_user, $is_department_user) {

    //         if ($user->hasRole('Super Admin')) {

    //             $query->where('status_id', 3);
    //         } elseif ($user->hasRole('Client Office')) {

    //             if ($is_office_user) {

    //                 $query->where(function ($q) use ($user) {

    //                     $q->where(function ($sub) use ($user) {
    //                         $sub->where('office_id', $user->office_id)
    //                             ->where('status_id', 3);
    //                     })
    //                         ->orWhere(function ($sub) {
    //                             $sub->where('status_id', 10)
    //                                 ->where('forwarded_to_role_id', 2)
    //                                 ->where('forwarded_by_role_id', 3);
    //                         });
    //                 });
    //             } elseif ($is_department_user) {

    //                 $query->whereNull('office_id')
    //                     ->where(function ($q) {

    //                         $q->where(function ($sub) {
    //                             $sub->where('status_id', 3)
    //                                 ->where('forwarded_by_role_id', 2)
    //                                 ->where('forwarded_to_role_id', 3);
    //                         })
    //                             ->orWhere('status_id', 12);
    //                     });
    //             }
    //         } elseif ($user->hasRole('Diary Dispatch')) {

    //             $query->where('status_id', 3)
    //                 ->where('forwarded_by_role_id', 9)
    //                 ->where('forwarded_to_role_id', 3);
    //         } elseif ($user->hasRole('Superintendent')) {

    //             $query->where(function ($q) {

    //                 $q->where(function ($sub) {
    //                     $sub->where('status_id', 3)
    //                         ->where('forwarded_by_role_id', 9)
    //                         ->where('forwarded_to_role_id', 3);
    //                 })
    //                     ->orWhere(function ($sub) {
    //                         $sub->where('status_id', 3)
    //                             ->where('forwarded_by_role_id', 2)
    //                             ->where('forwarded_to_role_id', 3);
    //                     });
    //             });
    //         } elseif ($user->hasRole('Deputy Director')) {

    //             $query->where('status_id', 4)
    //                 ->where('forwarded_by_role_id', 3)
    //                 ->where('forwarded_to_role_id', 11);
    //         } elseif ($user->hasRole('Director General')) {

    //             $query->where('status_id', 4)
    //                 ->where('forwarded_by_role_id', 11)
    //                 ->where('forwarded_to_role_id', 10);
    //         } elseif ($user->hasRole('Secretary')) {

    //             $query->where('status_id', 4)
    //                 ->where('forwarded_by_role_id', 10)
    //                 ->where('forwarded_to_role_id', 12);
    //         }
    //         elseif ($user->hasRole('Media')) {

    //             $query->where('status_id', 10)
    //                 ->where('forwarded_to_role_id', 4)
    //                 ->where('forwarded_by_role_id', 3)
    //                 ->where(function ($q) use ($user) {

    //                     // CASE 1: Advertisement Agency
    //                     if (!is_null($user->adv_agency_id)) {

    //                         $q->whereNotNull('adv_agency_id')
    //                             ->where('adv_agency_id', $user->adv_agency_id);
    //                     }


    //                     // CASE 2: Direct Newspaper
    //                     elseif (!is_null($user->newspaper_id)) {

    //                         $q->whereNull('adv_agency_id')
    //                             ->where(function ($sub) use ($user) {

    //                                 $sub->whereJsonContains('dg_NP_log', (string) $user->newspaper_id)
    //                                     ->orWhereJsonContains('dd_NP_log', (string) $user->newspaper_id)
    //                                     ->orWhereJsonContains('sec_NP_log', (string) $user->newspaper_id);
    //                             });

    //                     }

    //                     // No media mapping
    //                     else {
    //                         $q->whereRaw('1 = 0');
    //                     }
    //                 });
    //         }
    //     });


    //     // Apply search/filters
    //     $this->applyFilters($advertisements, $request);
    //     // ==========================
    //     // 📄 PAGINATION
    //     // ==========================
    //     $advertisements = $advertisements->orderBy('created_at', 'desc')->paginate(5)->appends($request->query());

    //     // ---- Archived count ----
    //     $ads = Advertisement::notArchived()->count();

    //     // Get data for dropdowns
    //     $statuses   = Status::all(); // all statuses
    //     $departments = Department::all(); // all departments (adjust as needed)
    //     $offices     = Office::all();     // all offices

    //     return view('advertisements.index', [
    //         'pageTitle'           => $pageTitle,
    //         'breadcrumbs'         => $breadcrumbs,
    //         'advertisements'      => $advertisements,
    //         'ads'                 => $ads,
    //         'is_department_user'  => $is_department_user,
    //         'is_office_user'      => $is_office_user,
    //         'statuses'            => $statuses,
    //         'departments'         => $departments,
    //         'offices'             => $offices,
    //     ]);
    // }

    // old index method
    public function index(Request $request)
    {
        $pageTitle = 'New Ads &#x2053; DG&#8211;IPR IAMS';

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'New Ads', 'url' => null],
        ];

        $user = auth()->user();

        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));

        // Fetch status IDs (use these instead of hardcoded numbers)
        $new_status = Status::where('title', 'New')->value('id');
        $forwarded_status = Status::where('title', 'Forwarded')->value('id');
        $approved_status = Status::where('title', 'Approved')->value('id');
        $pending_department_status = Status::where('title', 'Pending Department Approval')->value('id');
        $rejected_department_status = Status::where('title', 'Rejected by Department')->value('id');
        $sent_back_status = Status::where('title', 'Sent Back to Office')->value('id');
        $draft_status = Status::where('title', 'Draft')->value('id');

        // Base query: exclude archived ads
        $advertisements = Advertisement::whereNull('archived_at');

        if ($user->hasRole('Super Admin')) {
            $advertisements->where('status_id', $new_status);
        } elseif ($user->hasRole('Client Office')) {
            if ($is_office_user) {
                // Properly group OR condition to avoid ambiguity
                $advertisements->where(function ($query) use ($user) {
                    $query->where('office_id', $user->office_id)
                        ->where('status_id', 3) // $new_status
                        ->orWhere(function ($sub) use ($user) {
                            $sub->where('status_id', 14) // (maybe a different status)
                                ->where('forwarded_to_role_id', 2)
                                ->where('forwarded_by_role_id', 3);
                        });
                });
            } elseif ($is_department_user) {
                $advertisements->where('office_id', null)
                    ->where(function ($query) {
                        $query->where(function ($q) {
                            $q->where('status_id', 3) // $new_status
                                ->where('forwarded_by_role_id', 2)
                                ->where('forwarded_to_role_id', 3);
                        })
                            ->orWhere('status_id', 12); // (another status)
                    });
            }
        } elseif ($user->hasRole('Diary Dispatch')) {
            $advertisements->where('status_id', 3) // $new_status
                ->where('forwarded_by_role_id', 9)
                ->where('forwarded_to_role_id', 3);
        } elseif ($user->hasRole('Superintendent')) {
            $advertisements->where(function ($query) {
                $query->where('status_id', 3) // $new_status
                    ->where('forwarded_by_role_id', 9)
                    ->where('forwarded_to_role_id', 3)
                    ->orWhere(function ($q) {
                        $q->where('status_id', 3)
                            ->where('forwarded_by_role_id', 2)
                            ->where('forwarded_to_role_id', 3);
                    });
            });
        } elseif ($user->hasRole('Deputy Director')) {
            $advertisements->where('status_id', 4) // $forwarded_status
                ->where('forwarded_by_role_id', 3)
                ->where('forwarded_to_role_id', 11);
        } elseif ($user->hasRole('Director General')) {
            $advertisements->where('status_id', 4) // $forwarded_status
                ->where('forwarded_by_role_id', 11)
                ->where('forwarded_to_role_id', 10);
        } elseif ($user->hasRole('Secretary')) {
            $advertisements->where('status_id', 4) // $forwarded_status
                ->where('forwarded_by_role_id', 10)
                ->where('forwarded_to_role_id', 12);
        } elseif ($user->hasRole('Media')) {
            // Complex media logic – extracted for readability (but kept inline)
            $advertisements->where('status_id', 10) // $approved_status
                ->where('forwarded_to_role_id', 4)
                ->where('forwarded_by_role_id', 3)
                ->where(function ($query) use ($user) {
                    $query->where(function ($q) use ($user) {
                        // Adv Agency case
                        $q->whereNotNull('adv_agency_id')
                            ->whereNull('publication')
                            ->where('adv_agency_id', $user->adv_agency_id);
                    })->orWhere(function ($q) use ($user) {
                        // Normal Media case
                        $q->whereNull('adv_agency_id')
                            ->where(function ($sub) use ($user) {
                                $sub->where(function ($sub1) use ($user) {
                                    $sub1->whereNull('sec_NP_log')
                                        ->whereNotNull('dg_NP_log')
                                        ->whereJsonContains('dg_NP_log', (string) $user->newspaper_id);
                                })->orWhere(function ($sub2) use ($user) {
                                    $sub2->whereNull('dg_NP_log')
                                        ->whereNotNull('dd_NP_log')
                                        ->whereJsonContains('dd_NP_log', (string) $user->newspaper_id);
                                })->orWhere(function ($sub3) use ($user) {
                                    $sub3->whereNotNull('sec_NP_log')
                                        ->whereJsonContains('sec_NP_log', (string) $user->newspaper_id);
                                });
                            });
                    });
                })
                ->whereDoesntHave('newspapers', function ($query) use ($user) {
                    $query->where('newspaper_id', $user->newspaper_id);
                });
        } else {
            $advertisements->whereRaw('0 = 1'); // No results for others
        }

        // Apply search/filters
        $this->applyFilters($advertisements, $request);

        $advertisements = $advertisements->orderBy('created_at', 'desc')->paginate(15);

        // Count of non-archived ads (if needed in view)
        $nonArchivedCount = Advertisement::notArchived()->count();

        // Get data for dropdowns
        $statuses   = Status::all(); // all statuses
        $departments = Department::all(); // all departments (adjust as needed)
        $offices     = Office::all();     // all offices

        return view('advertisements.index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advertisements' => $advertisements,
            'nonArchivedCount' => $nonArchivedCount,
            'is_department_user' => $is_department_user,
            'is_office_user' => $is_office_user,
            'statuses' => $statuses,
            'offices' => $offices,
            'departments' => $departments

        ]);
    }


    // Show Inprogress Ads
    // public function inprogress(Request $request)
    // {
    //     // Page title
    //     $pageTitle = 'Inprogress Ads &#x2053; IAMS-IPR';

    //     // breadcrumbs
    //     $breadcrumbs = [
    //         ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
    //         ['label' => 'Inprogress Ads', 'url' => null], // The current page (no URL)
    //     ];

    //     //Get logged in user
    //     $user = auth()->user();

    //     // Determine user type
    //     $is_department_user = ($user->department_id && is_null($user->office_id));
    //     $is_office_user = ($user->department_id && !is_null($user->office_id));

    //     $advertisements = Advertisement::query();

    //     // Role-based filtering
    //     $advertisements->where(function ($query) use ($user) {

    //         if ($user->hasRole('Super Admin')) {
    //             $query->where('status_id', 4);
    //         } elseif ($user->hasRole('Client Office')) {
    //             $query->where('status_id', 4)
    //                 ->where('department_id', $user->department_id);
    //         } elseif ($user->hasRole('Diary Dispatch')) {
    //             $query->where('status_id', 4);
    //         } elseif ($user->hasRole('Superintendent')) {
    //             $query->where('status_id', 4)
    //                 ->where(function ($q) {
    //                     $q->where([
    //                         ['forwarded_by_role_id', 3],
    //                         ['forwarded_to_role_id', 11]
    //                     ])
    //                         ->orWhere([
    //                             ['forwarded_by_role_id', 11],
    //                             ['forwarded_to_role_id', 10]
    //                         ])
    //                         ->orWhere([
    //                             ['forwarded_by_role_id', 10],
    //                             ['forwarded_to_role_id', 12]
    //                         ]);
    //                 });
    //         } elseif ($user->hasRole('Deputy Director')) {

    //             $query->where('status_id', 4)
    //                 ->where(function ($q) {
    //                     $q->where(function ($subq) {
    //                         $subq->where('forwarded_by_role_id', 11)
    //                             ->where('forwarded_to_role_id', 10);
    //                     })
    //                         ->orWhere(function ($subq) {
    //                             $subq->where('forwarded_by_role_id', 10)
    //                                 ->where('forwarded_to_role_id', 12);
    //                         });
    //                 });
    //         } elseif ($user->hasRole('Director General')) {

    //             $query->where(function ($q) {

    //                 $q->where(function ($sub) {
    //                     $sub->where('status_id', 10)
    //                         ->where('forwarded_by_role_id', 9)
    //                         ->where('forwarded_to_role_id', 4);
    //                 })
    //                     ->orWhere(function ($sub) {
    //                         $sub->where('status_id', 4)
    //                             ->where('forwarded_by_role_id', 10)
    //                             ->where('forwarded_to_role_id', 12);
    //                     });
    //             });
    //         }
    //     });
    //     // }elseif($user->hasRole('Secretary')){
    //     //         //Director General sees records updated by the director general
    //     //     $advertisements->where('status_id', 10)
    //     //         ->where('forwarded_by_role_id', 9)
    //     //         ->where('forwarded_to_role_id', 4);
    //     // }
    //     // Apply search/filters
    //     $this->applyFilters($advertisements, $request);

    //     $advertisements = $advertisements->orderBy('created_at', 'desc')->paginate(5);
    //     // Get data for dropdowns
    //     $statuses   = Status::all(); // all statuses
    //     $departments = Department::all(); // all departments (adjust as needed)
    //     $offices     = Office::all();     // all offices

    //     return view('advertisements.inprogress', [
    //         'pageTitle' => $pageTitle,
    //         'breadcrumbs' =>  $breadcrumbs,
    //         'advertisements' => $advertisements,
    //         'is_department_user' => $is_department_user,
    //         'is_office_user' => $is_office_user,
    //         'statuses' => $statuses,
    //         'departments' => $departments,
    //         'offices' => $offices,
    //     ]);
    // }
    public function inprogress(Request $request)
    {
        $pageTitle = 'Inprogress Ads &#x2053; IAMS-IPR';
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Inprogress Ads', 'url' => null],
        ];

        $user = auth()->user();
        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));

        $advertisements = Advertisement::query();

        // Role-based filtering
        if ($user->hasRole('Super Admin')) {
            $advertisements->where('status_id', 4);
        } elseif ($user->hasRole('Client Office')) {
            $advertisements->where('status_id', 4)
                ->where('department_id', $user->department_id);
        } elseif ($user->hasRole('Diary Dispatch')) {
            $advertisements->where('status_id', 4);
        } elseif ($user->hasRole('Superintendent')) {
            $advertisements->where('status_id', 4)
                ->where(function ($query) {
                    $query->where([
                        ['forwarded_by_role_id', 3],
                        ['forwarded_to_role_id', 11]
                    ])
                        ->orWhere([
                            ['forwarded_by_role_id', 11],
                            ['forwarded_to_role_id', 10]
                        ])
                        ->orWhere([
                            ['forwarded_by_role_id', 10],
                            ['forwarded_to_role_id', 12]
                        ]);
                });
        } elseif ($user->hasRole('Deputy Director')) {
            $advertisements->where('status_id', 4)
                ->where(function ($query) {
                    $query->where([
                        ['forwarded_by_role_id', 11],
                        ['forwarded_to_role_id', 10]
                    ])
                        ->orWhere([
                            ['forwarded_by_role_id', 10],
                            ['forwarded_to_role_id', 12]
                        ]);
                });
        } elseif ($user->hasRole('Director General')) {
            $advertisements->where(function ($query) {
                $query->where([
                    ['status_id', 10],
                    ['forwarded_by_role_id', 9],
                    ['forwarded_to_role_id', 4]
                ])
                    ->orWhere([
                        ['status_id', 4],
                        ['forwarded_by_role_id', 10],
                        ['forwarded_to_role_id', 12]
                    ]);
            });
        }
        // Add more roles (e.g., Secretary) as needed

        $this->applyFilters($advertisements, $request);

        $advertisements = $advertisements->orderBy('created_at', 'desc')->paginate(5);

        $statuses   = Status::all();
        $departments = Department::all();
        $offices     = Office::all();

        return view('advertisements.inprogress', compact(
            'pageTitle',
            'breadcrumbs',
            'advertisements',
            'is_department_user',
            'is_office_user',
            'statuses',
            'departments',
            'offices'
        ));
    }



    // Show Single Inprogress Ad
    public function showInprogress($id)
    {
        // dd($id);
        // Page title
        $pageTitle = 'Inprogress Ad Details &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Inpregress Ads', 'url' => route('advertisements.inprogress')],
            ['label' => 'Inprogress Ad Details', 'url' => null], // The current page (no URL)
        ];

        $advertisement = Advertisement::findOrFail($id);
        $covering_letter_files = $advertisement->getMedia('covering_letters');
        $urdu_ad_files = $advertisement->getMedia('urdu_ads');

        $english_ad_files = $advertisement->getMedia('english_ads');


        $newspaperNames = Newspaper::whereIn('id', $advertisement->newspaper_id ?? [])
            ->pluck('title')
            ->toArray();
        $suptdNewspaperNames = Newspaper::whereIn('id', $advertisement->suptd_NP_log ?? [])
            ->pluck('title')
            ->toArray();
        $ddNewspaperNames = Newspaper::whereIn('id', $advertisement->dd_NP_log ?? [])
            ->pluck('title')
            ->toArray();
        $dgNewspaperNames = Newspaper::whereIn('id', $advertisement->dg_NP_log ?? [])
            ->pluck('title')
            ->toArray();

        $secNewspaperNames = Newspaper::whereIn('id', $advertisement->sec_NP_log ?? [])
            ->pluck('title')
            ->toArray();

        $userName = User::where('id', $advertisement->user_id)->value('name');
        $departmentName = Department::where('id', $advertisement->department_id)->value('name');
        $officeName = Office::where('id', $advertisement->office_id)->value('ddo_name');
        $adWorthparameters = AdWorthParameter::where('id', $advertisement->ad_worth_id)->value('range');
        $classifiedAdType = ClassifiedAdType::where('id', $advertisement->classified_ad_type_id)->value('type');
        $advAgency = AdvAgency::where('id', $advertisement->adv_agency_id)->value('name');
        $newsposrate = NewsPosRate::where('id', $advertisement->news_pos_rate_id)->first(['position', 'rates']);
        $forwardedBy = Role::where('id', $advertisement->forwarded_by_role_id)->value('name');
        $forwardedTo = Role::where('id', $advertisement->forwarded_to_role_id)->value('name');

        $status = Status::where('id', $advertisement->status_id)->value('title');


        // Data Title
        $dataTitle = 'Inprogress Ad Details';

        return view('advertisements.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advertisement' => $advertisement,
            'covering_letter_files' => $covering_letter_files,
            'urdu_ad_files' => $urdu_ad_files,
            'english_ad_files' => $english_ad_files,
            'advAgency' => $advAgency,
            'newspaperNames' => $newspaperNames,
            'suptdNewspaperNames' => $suptdNewspaperNames,
            'ddNewspaperNames' => $ddNewspaperNames,
            'dgNewspaperNames' => $dgNewspaperNames,
            'secNewspaperNames' => $secNewspaperNames,
            'userName' => $userName,
            'departmentName' => $departmentName,
            'officeName' => $officeName,
            'adWorthparameters' => $adWorthparameters,
            'classifiedAdType' => $classifiedAdType,
            'newsposrate' =>  $newsposrate,
            'forwardedBy' => $forwardedBy,
            'forwardedTo' =>  $forwardedTo,
            'status' => $status,
        ]);
    }

    // Show Approved Ads
    public function approved(Request $request)
    {
        // Page title
        $pageTitle = 'Approved Ads &#x2053; IAMS-IPR';

        // Breadcrumbs
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Approved Ads', 'url' => null],
        ];

        $user = auth()->user();
        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));

        // Map statuses once
        $statusesMap = Status::pluck('id', 'title');
        $approved_status = $statusesMap['Approved'] ?? 10; // fallback 10 if not found

        $advertisements = Advertisement::query()
            ->where('status_id', $approved_status)
            ->whereNull('archived_at')
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('forwarded_by_role_id', 10)
                        ->where('forwarded_to_role_id', 3);
                })
                    ->orWhere(function ($sub) {
                        $sub->where('forwarded_by_role_id', 11)
                            ->where('forwarded_to_role_id', 3);
                    })
                    ->orWhere(function ($sub) {
                        $sub->where('forwarded_by_role_id', 3)
                            ->where('forwarded_to_role_id', 4);
                    })
                    ->orWhere(function ($sub) {
                        $sub->where('forwarded_by_role_id', 12)
                            ->where('forwarded_to_role_id', 3);
                    });
            });

        // Apply search/filters
        $this->applyFilters($advertisements, $request);

        // Pagination
        $advertisements = $advertisements->orderBy('created_at', 'desc')->paginate(5);

        // Dropdown data
        $statuses = Status::all();
        $departments = Department::all();
        $offices = Office::all();

        return view('advertisements.inprogress', [
            'advertisements' => $advertisements,
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'is_department_user' => $is_department_user,
            'is_office_user' => $is_office_user,
            'statuses' => $statuses,
            'departments' => $departments,
            'offices' => $offices,
        ]);
    }

    // Show Single Approved Ad
    public function showApproved($id)
    {
        // Page title
        $pageTitle = 'Approved Ad Details &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Approved Ads', 'url' => route('advertisements.approved')],
            ['label' => 'Approved Ad Details', 'url' => null], // The current page (no URL)
        ];

        $advertisement = Advertisement::findOrFail($id);
        $covering_letter_files = $advertisement->getMedia('covering_letters');
        $urdu_ad_files = $advertisement->getMedia('urdu_ads');
        $english_ad_files = $advertisement->getMedia('english_ads');

        // Data Title
        $dataTitle = 'Inprogress Ad Details';

        return view('advertisements.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advertisement' => $advertisement,
            'covering_letter_files' => $covering_letter_files,
            'urdu_ad_files' => $urdu_ad_files,
            'english_ad_files' => $english_ad_files,
            'dataTitle' => $dataTitle
        ]);
    }

    // Show Rejected Ads
    public function rejected(Request $request)
    {
        //Page title
        $pageTitle = 'Rejected Ads &#x2053; IAMS-IPR';

        // breadcrumbs
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Rejected Ads', 'url' => null], // The current page (no URL)
        ];

        $user = auth()->user();
        $userId = $user->id;
        // Determine user type
        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));

        //Get logged in user
        $user = auth()->user();
        $user_role = $user->roles->pluck('id');
        $user_role_id = $user_role->first();
        // dd($user_role_id);
        $ad_rejection_reasons = AdRejectionReason::all();

        $advertisements = Advertisement::query();
        if ($user->hasRole('Deputy Director')) {
            $advertisements->where('status_id', 7)
                ->where('forwarded_by_role_id', 10)
                ->where('forwarded_to_role_id', 11);
        } else {
            $advertisements->where('status_id', 7);
        }

        $this->applyFilters($advertisements, $request);
        $advertisements = $advertisements->orderBy('created_at', 'desc')->paginate(10);
        $statuses   = Status::all(); // all statuses
        $departments = Department::all(); // all departments (adjust as needed)
        $offices     = Office::all();     // all offices
        return view('advertisements.inprogress', [
            'advertisements' => $advertisements,
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'ad_rejection_reasons' => $ad_rejection_reasons,
            'is_department_user' => $is_department_user,
            'is_office_user' => $is_office_user,
            'statuses' => $statuses,
            'departments' => $departments,
            'offices' => $offices,
        ]);
    }

    // Show Single Rejected Ad
    public function showRejected($id)
    {
        // Page title
        $pageTitle = 'Rejected Ad Details &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Rejected Ads', 'url' => route('advertisements.rejected')],
            ['label' => 'Rejected Ad Details', 'url' => null], // The current page (no URL)
        ];

        // Eager load the relationship
        $advertisement = Advertisement::with('rejectionReasons')->findOrFail($id);
        $covering_letter_files = $advertisement->getMedia('covering_letters');
        $urdu_ad_files = $advertisement->getMedia('urdu_ads');
        $english_ad_files = $advertisement->getMedia('english_ads');

        // Data Title
        $dataTitle = 'Rejected Ad Details';

        return view('advertisements.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advertisement' => $advertisement,
            'covering_letter_files' => $covering_letter_files,
            'urdu_ad_files' => $urdu_ad_files,
            'english_ad_files' => $english_ad_files,
            'dataTitle' => $dataTitle
        ]);
    }

    // Show Published Ads
    public function publishedList(Request $request)
    {
        $pageTitle = 'Published Ads &#x2053; IAMS-IPR';
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Published Ads', 'url' => null],
        ];

        $user = auth()->user();
        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));

        // Start with a base query builder
        $publishedAdvertisements = Advertisement::query();

        // Role-based conditions
        if ($user->hasRole(['Director General', 'Deputy Director', 'Superintendent', 'Diary Dispatch', 'Super Admin'])) {
            // High-level roles: see all published ads (not archived)
            $publishedAdvertisements->whereHas('newspapers', function ($query) {
                $query->where('is_published', 1)
                    ->whereNull('archived_at');
            });
        } elseif ($user->hasRole('Media')) {
            if ($user->adv_agency_id) {
                // Media user belonging to an advertising agency
                $publishedAdvertisements->where('adv_agency_id', $user->adv_agency_id)
                    ->whereHas('newspapers', function ($query) {
                        $query->where('is_published', 1);
                    });
            } elseif ($user->newspaper_id) {
                // Normal media user (newspaper)
                $publishedAdvertisements->whereHas('newspapers', function ($query) use ($user) {
                    $query->where('newspaper_id', $user->newspaper_id)
                        ->where('is_published', 1)
                        ->whereNull('agency_id');
                });
            } else {
                // Media user with no newspaper_id or adv_agency_id – show nothing
                $publishedAdvertisements->whereRaw('0 = 1'); // Ensures empty result
            }
        } else {
            // All other roles – no access
            $publishedAdvertisements->whereRaw('0 = 1');
        }

        // Apply additional filters (search, date range, etc.)
        $this->applyFilters($publishedAdvertisements, $request);

        // Paginate and preserve query parameters
        $publishedAdvertisements = $publishedAdvertisements->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        // Get dropdown data
        $statuses   = Status::all();
        $departments = Department::all();
        $offices     = Office::all();

        return view('advertisements.publish', compact(
            'publishedAdvertisements',
            'pageTitle',
            'breadcrumbs',
            'is_department_user',
            'is_office_user',
            'statuses',
            'departments',
            'offices'
        ));
    }

    // Show Single Published Ad
    public function showPublished($id)
    {
        // Page title
        $pageTitle = 'Published Ad Details &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Published Ads', 'url' => route('advertisements.published')],
            ['label' => 'Published Ad Details', 'url' => null], // The current page (no URL)
        ];

        $advertisement = Advertisement::findOrFail($id);
        $covering_letter_files = $advertisement->getMedia('covering_letters');
        $urdu_ad_files = $advertisement->getMedia('urdu_ads');
        $english_ad_files = $advertisement->getMedia('english_ads');

        $newspaperNames = Newspaper::whereIn('id', $advertisement->newspaper_id ?? [])
            ->pluck('title')
            ->toArray();
        $suptdNewspaperNames = Newspaper::whereIn('id', $advertisement->suptd_NP_log ?? [])
            ->pluck('title')
            ->toArray();
        $ddNewspaperNames = Newspaper::whereIn('id', $advertisement->dd_NP_log ?? [])
            ->pluck('title')
            ->toArray();
        $dgNewspaperNames = Newspaper::whereIn('id', $advertisement->dg_NP_log ?? [])
            ->pluck('title')
            ->toArray();
        $secNewspaperNames = Newspaper::whereIn('id', $advertisement->sec_NP_log ?? [])
            ->pluck('title')
            ->toArray();

        $userName = User::where('id', $advertisement->user_id)->value('name');
        $departmentName = Department::where('id', $advertisement->department_id)->value('name');
        $officeName = Office::where('id', $advertisement->office_id)->value('ddo_name');
        $adWorthparameters = AdWorthParameter::where('id', $advertisement->ad_worth_id)->value('range');
        $classifiedAdType = ClassifiedAdType::where('id', $advertisement->classified_ad_type_id)->value('type');
        $advAgency = AdvAgency::where('id', $advertisement->adv_agency_id)->value('name');
        $newsposrate = NewsPosRate::where('id', $advertisement->news_pos_rate_id)->first(['position', 'rates']);
        $forwardedBy = Role::where('id', $advertisement->forwarded_by_role_id)->value('name');
        $forwardedTo = Role::where('id', $advertisement->forwarded_to_role_id)->value('name');

        $status = Status::where('id', $advertisement->status_id)->value('title');

        return view('advertisements.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advertisement' => $advertisement,
            'covering_letter_files' => $covering_letter_files,
            'urdu_ad_files' => $urdu_ad_files,
            'english_ad_files' => $english_ad_files,
            'newspaperNames' => $newspaperNames,
            'suptdNewspaperNames' => $suptdNewspaperNames,
            'ddNewspaperNames' => $ddNewspaperNames,
            'dgNewspaperNames' => $dgNewspaperNames,
            'secNewspaperNames' => $secNewspaperNames,
            'advAgency' =>   $advAgency,
            'userName' => $userName,
            'departmentName' => $departmentName,
            'officeName' => $officeName,
            'adWorthparameters' => $adWorthparameters,
            'classifiedAdType' => $classifiedAdType,
            'newsposrate' =>  $newsposrate,
            'forwardedBy' => $forwardedBy,
            'forwardedTo' =>  $forwardedTo,
            'status' => $status,

        ]);
    }

    // Show Unpublished Ads
    public function unpublishedList(Request $request)
    {
        $pageTitle = 'Unpublished Ads &#x2053; IAMS-IPR';

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Unpublished Ads', 'url' => null],
        ];

        $user = auth()->user();

        if ($user->hasRole(['Director General', 'Deputy Director', 'Superintendent', 'Diary Dispatch', 'Super Admin'])) {
            // Show all unpublished advertisements
            $unpublishedAdvertisements = Advertisement::whereHas('newspapers', function ($query) {
                $query->where('is_published', 0);
            })->orderBy('created_at', 'desc')->paginate(10);;
        }    // Media users should only see their OWN newspaper unpublished advertisements
        elseif ($user->hasRole('Media') && $user->newspaper_id) {
            $unpublishedAdvertisements = Advertisement::whereHas('newspapers', function ($query) use ($user) {
                $query->where('is_published', 0)
                    ->where('newspaper_id', $user->newspaper_id);
            })->orderBy('created_at', 'desc')->paginate(10);
        }
        // Other users see nothing or you can return abort(403)
        else {
            $unpublishedAdvertisements = collect(); // empty collection
        }

        return view('advertisements.unpublish', [
            'unpublishedAdvertisements' =>  $unpublishedAdvertisements,
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    // Show Single Unpublished Ad
    public function showUnpublished($id)
    {
        // Page title
        $pageTitle = 'Unpublished Ad Details &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Unpublished Ads', 'url' => route('advertisements.unpublished')],
            ['label' => 'Unpublished Ad Details', 'url' => null], // The current page (no URL)
        ];

        $advertisement = Advertisement::findOrFail($id);
        $covering_letter_files = $advertisement->getMedia('covering_letters');
        $urdu_ad_files = $advertisement->getMedia('urdu_ads');
        $english_ad_files = $advertisement->getMedia('english_ads');

        // Data Title
        $dataTitle = 'Unpublished Ad Details';

        return view('advertisements.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advertisement' => $advertisement,
            'covering_letter_files' => $covering_letter_files,
            'urdu_ad_files' => $urdu_ad_files,
            'english_ad_files' => $english_ad_files,
            'dataTitle' => $dataTitle
        ]);
    }

    // Show Ad Form
    public function create()
    {
        // Page Title
        $pageTitle = 'Create Ad &#x2053; IAMS-IPR';

        // breadcrumbs
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Create Ad', 'url' => null], // The current page (no URL)
        ];

        $user = Auth()->user();

        // generate inf number
        $preview_inf_number = get_next_inf_number_preview();

        // Departments
        $departments = Department::all();

        // Offices
        $offices = Office::all();

        // Ad worth parameters
        $ad_worth_parameters = AdWorthParameter::all();

        // Classified Ad Types
        $classifiedAdTypes = ClassifiedAdType::all();

        // Status
        $new_status = Status::where('title', 'New')->value('id');
        $draft_status = Status::where('title', 'Draft')->value('id');
        $pending_department_status = Status::where('title', 'Pending Department Approval')->value('id');

        return view('advertisements.create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'user_id' => $user,
            'departments' => $departments,
            'offices' => $offices,
            'ad_worth_parameters' => $ad_worth_parameters,
            'classifiedAdTypes' => $classifiedAdTypes,
            'new_status' => $new_status,
            'draft_status' => $draft_status,
            'pending_department_status' => $pending_department_status,
            'preview_inf_number' => $preview_inf_number,
        ]);
    }

    // Fetching offices on the basis of department
    public function getOffices(Request $request)
    {
        $offices = Office::where('department_id', $request->department_id)->get();
        return response()->json($offices);
    }

    // Store/Draft Ad
    public function store(Request $request)
    {
        // dd($request->all());

        $user = auth()->user();
        $user_name = $user->name;
        // dd($user_name);
        $current_user_role = $user->roles->pluck('id')->first();

        // Determine user type based on department_id and office_id
        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));

        // Shared validation rules
        $rules = [
            'memo_number' => 'nullable|string|max:255',
            'memo_date' => 'nullable|date',
            'department_id' => 'required|exists:departments,id',
            'office_id' => 'nullable|exists:offices,id',
            'ad_worth_id' => 'required|exists:ad_worth_parameters,id',
            'classified_ad_type_id' => 'required|exists:classified_ad_types,id',
            'publish_on_or_before' => 'required|date',
            'urdu_lines' => 'nullable|numeric|min:1',
            'english_lines' => 'nullable|numeric|min:1',
            'urdu_size' => 'nullable|numeric|min:1',
            'english_size' => 'nullable|numeric|min:1',
            'source_of_fund' => 'nullable|string|max:255',
            'adp_code' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
        ];

        // Additional validation for office users
        // if ($is_office_user) {
        //     $rules['office_id'] = 'required|exists:offices,id';
        //     // Ensure office belongs to user's department
        //     $rules['office_id'] .= '|exists:offices,id,department_id,' . $user->department_id;
        //     $rules['department_id'] = 'required|exists:departments,id|in:' . $user->department_id;
        // }

        // // Additional validation for department users
        // if ($is_department_user) {
        //     $rules['department_id'] = 'required|exists:departments,id|in:' . $user->department_id;
        // }

        $request->validate($rules);

        $action = $request->input('action');

        // Initialize common fields
        $commonData = [
            'memo_number' => $request->memo_number,
            'memo_date' => $request->memo_date,
            'department_id' => $request->department_id,
            'office_id' => $request->office_id,
            'ad_worth_id' => $request->ad_worth_id,
            'classified_ad_type_id' => $request->classified_ad_type_id,
            'urdu_lines' => $request->urdu_lines,
            'english_lines' => $request->english_lines,
            'urdu_size' => $request->urdu_size,
            'english_size' => $request->english_size,
            'publish_on_or_before' => $request->publish_on_or_before,
            'source_of_fund' => $request->source_of_fund,
            'adp_code' => $request->adp_code,
            'project_name' => $request->project_name,
            'user_id' => $user->id,
            'created_by_user_type' => $is_office_user ? 'office' : ($is_department_user ? 'department' : 'other'),
        ];

        // Save as Draft
        if ($action === 'save-draft') {
            $advertisement = Advertisement::create(array_merge($commonData, [
                'status_id' => $request->draft_status,
                'drafted_at' => now(),
            ]));

            $advertisement->addAllMediaFromTokens();
            $this->logWorkflowAction(
                $advertisement,
                $user,
                'draft_created',
                null, // from_status_id - no previous status for new draft
                $advertisement->status_id,
                'Advertisement saved as draft.',
                null
            );

            return redirect()
                ->route('advertisements.draft')
                ->with('success', 'Advertisement saved as draft.');
        }

        // Submitt Ad
        $inf_number = generate_inf_number();

        $advertisement = new Advertisement();
        $advertisement->fill($commonData);
        $advertisement->inf_series_id = $inf_number['inf_series_id'];
        $advertisement->inf_number = $inf_number['inf_number'];
        // $advertisement->status_id = $request->new_status;
        $advertisement->forwarded_by_role_id = $current_user_role;
        // $advertisement->forwarded_to_role_id = 3;
        $advertisement->user_id = $user->id;

        // Determine workflow based on user type
        // if ($is_office_user) {
        //     // Office user: Send to their department first
        //     $advertisement->status_id = $request->pending_department_status;
        //     $advertisement->forwarded_to_role_id = 2; // Department user role ID (Client Office but department type)
        //     $success_message = 'Advertisement created and sent to your department for approval.';
        // } elseif ($is_department_user) {
        //     // Department user: Send directly to IPR
        //     $advertisement->status_id = $request->new_status;
        //     $advertisement->forwarded_to_role_id = 3; // Superintendent role ID

        //     $success_message = 'Advertisement created and forwarded to IPR department successfully.';
        // } else {
        // IPR users (diary dispatch, etc.): Direct creation
        $advertisement->status_id = $request->new_status;
        $advertisement->forwarded_to_role_id = 3; // Superintendent role ID
        $success_message = 'Advertisement created and forwarded successfully.';
        // }
        $advertisement->save();

        $advertisement->addAllMediaFromTokens();

        dispatch(function () use ($advertisement, $user_name) {
            foreach (User::Assistants() as $assistant) {
                notifyUser($assistant, [
                    'title' => 'New Advertisement added.',
                    'message' => $user_name . ' has been sent new advertisement with inf number ' . $advertisement->inf_number,
                    'url' => url('advertisements-show', ['id' => $advertisement->id])
                ]);
            }
        });

        // --- Log the submission ---
        $logAction = $is_department_user ? 'submitted_to_ipr_by_department' : ($is_office_user ? 'submitted_to_ipr_by_office' : 'created');

        $this->logWorkflowAction(
            $advertisement,
            $user,
            $logAction,
            null, // from_status_id - no previous status for new advertisement
            $advertisement->status_id,
            'Advertisement submitted to IPR.',
            $advertisement->forwarded_to_role_id,
            [
                'inf_number' => $advertisement->inf_number,
                'department' => $advertisement->department_id,
                'office'     => $advertisement->office_id,
            ]
        );

        return redirect()
            ->route('advertisements.index')
            ->with('success',   $success_message);
    }

    /**
     * Get the first user with the 'Superintendent' role.
     */
    private function getSuperintendentUserId()
    {
        return User::role('Superintendent')->first()?->id;
    }


    // Archive Ad
    public function archive($id)
    {
        // dd($id);
        $ad = Advertisement::findOrFail($id);
        $ad->archived_at = now();
        $ad->save();

        return redirect()->back()->with('success', 'Advertisement archived successfully.');
    }



    // Show Archived Ads
    public function archived(Request $request)
    {
        // Page Title
        $pageTitle = 'Archived Ads &#x2053; IAMS-IPR';

        // breadcrumbs
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Archived Ads', 'url' => null], // The current page (no URL)
        ];


        $user = auth()->user();
        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));


        $archivedAdsQuery = Advertisement::query()->whereNotNull('archived_at');

        // Apply search/filters
        $this->applyFilters($archivedAdsQuery, $request);

        // Pagination
        $archivedAds = $archivedAdsQuery->latest()->paginate(10)->appends($request->query());

        // Get data for dropdowns
        $statuses   = Status::all(); // all statuses
        $departments = Department::all(); // all departments (adjust as needed)
        $offices     = Office::all();     // all offices
        return view('advertisements.archived', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'archivedAds' => $archivedAds,
            'is_department_user' => $is_department_user,
            'is_office_user' => $is_office_user,
            'statuses' => $statuses,
            'departments' => $departments,
            'offices' => $offices,
        ]);
    }


    // Show Single Archived Ad
    public function showArchived($id)
    {

        dd($id);
        // Page title
        $pageTitle = 'Archived Ad Details &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Archived Ads', 'url' => route('advertisements.archived')],
            ['label' => 'Archived Ad Details', 'url' => null], // The current page (no URL)
        ];

        $advertisement = Advertisement::findOrFail($id);
        $covering_letter_files = $advertisement->getMedia('covering_letters');
        $urdu_ad_files = $advertisement->getMedia('urdu_ads');
        $english_ad_files = $advertisement->getMedia('english_ads');

        // Data Title
        $dataTitle = 'Archived Ad Details';

        return view('advertisements.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advertisement' => $advertisement,
            'covering_letter_files' => $covering_letter_files,
            'urdu_ad_files' => $urdu_ad_files,
            'english_ad_files' => $english_ad_files,
            'dataTitle' => $dataTitle
        ]);
    }

    // Unarchive Ad
    public function unarchive($id)
    {
        $ad = Advertisement::findOrFail($id);
        $ad->archived_at = null;
        $ad->save();

        return redirect()->back()->with('success', 'Advertisement restored successfully.');
    }

    // Edit Ad
    public function edit($id)
    {
        // Page title
        $pageTitle = 'Edit Ad &#x2053; DG&#8212;IPR IAMS';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'New Ads', 'url' => route('advertisements.index')],
            ['label' => 'Edit Ad', 'url' => null], // The current page (no URL)
        ];

        $advertisement = Advertisement::with(['media', 'user', 'department', 'office'])->findOrFail($id);

        $adCreatedByUser  = $advertisement->user; // Get the user who created the advertisement

        $adCreatedByRole = $adCreatedByUser->roles->pluck('id')->first() ?? null; // Get the role of the user who created the advertisement

        // $advertisement = Advertisement::with('media')->findOrFail($id);

        $new_status = Status::where('title', 'New')->value('id');
        $draft_status = Status::where('title', 'Draft')->value('id');
        $pending_department_status = Status::where('title', 'Pending Department Approval')->value('id');
        $sent_back_status = Status::where('title', 'Sent Back to Office')->value('id');

        $user = Auth::user(); // return user object

        // Determine user type
        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));

        // Check if user is authorized to edit this advertisement
        if ($is_office_user && $advertisement->user_id != $user->id) {
            abort(403, 'You are not authorized to edit this advertisement.');
        }

        if ($is_department_user && $advertisement->department_id != $user->department_id) {
            abort(403, 'You are not authorized to edit this advertisement.');
        }

        // Determine which newspapers to be pre-filled in the dropdown
        $selected_newspapers = [];
        if ($user->hasRole('Director General')) {
            // Director General sees newspapers selected by deputy director
            $selected_newspapers = $advertisement->dd_NP_log ?? [];
        } elseif ($user->hasRole('Deputy Director')) {
            // Deputy Director sees newspapers selected byt superintendent
            $selected_newspapers = $advertisement->suptd_NP_log ?? [];
        } else {
            // Other roles see the newspapers directly associated with the advertisement
            $selected_newspapers = $advertisement->newspaper_id ?? [];
        }

        // Ad categories
        $ad_categories = AdCategory::all();

        //Ad Types
        $classifiedAdTypes = ClassifiedAdType::all();

        // Ad worth parameters
        $ad_worth_parameters = AdWorthParameter::all();

        // Placement/Position
        $news_pos_rates = NewsPosRate::all();

        // Newspapers
        $newspapers = Newspaper::all();

        // Adv Agencies
        $adv_agencies = AdvAgency::all();

        $statuses = Status::all();
        $ad_rejection_reasons = AdRejectionReason::all();
        $selected_reasons = $advertisement->ad_rejection_reasons_id ?? [];

        // Retrieve media files
        $covering_letter_files = $advertisement->getMedia('covering_letters');

        // Dynamically prepare ad_worth_limits for frontend (estimated cost => newspaper limit)
        $ad_worth_limits = [];
        foreach ($ad_worth_parameters as $param) {
            $range = strtolower(trim($param->range ?? ''));
            if ($range === 'others') {
                $ad_worth_limits[$param->id] = ['limit' => 999, 'is_unlimited' => true];
            } elseif ($range === 'upto 1.0m') {
                $ad_worth_limits[$param->id] = ['limit' => 2, 'is_unlimited' => false];
            } elseif ($range === '1.0m to 3.0m') {
                $ad_worth_limits[$param->id] = ['limit' => 3, 'is_unlimited' => false];
            } elseif ($range === '3.0m to 5.0m') {
                $ad_worth_limits[$param->id] = ['limit' => 4, 'is_unlimited' => false];
            } elseif ($range === '5.0m & above') {
                $ad_worth_limits[$param->id] = ['limit' => 5, 'is_unlimited' => false];
            } else {
                $ad_worth_limits[$param->id] = ['limit' => 0, 'is_unlimited' => false];
            }
        }

        // Find current ad_worth limit and unlimited status based on advertisement data
        $currentLimit = $ad_worth_limits[$advertisement->ad_worth_id] ?? ['limit' => 0, 'is_unlimited' => false];


        // Get departments for send back functionality (for IPR users)
        $departments = Department::all();
        $offices = Office::where('department_id', $advertisement->department_id)->get();

        return view('advertisements.edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advertisement' => $advertisement,
            'new_status' =>  $new_status,
            'draft_status' => $draft_status,
            'pending_department_status' => $pending_department_status,
            'sent_back_status' => $sent_back_status,
            'user' =>  $user,
            'ad_categories' => $ad_categories,
            'classifiedAdTypes' => $classifiedAdTypes,
            'ad_worth_parameters' => $ad_worth_parameters,
            'news_pos_rates' => $news_pos_rates,
            'newspapers' => $newspapers,
            'selected_newspapers' => $selected_newspapers,
            'adv_agencies' => $adv_agencies,
            'statuses' => $statuses,
            'ad_rejection_reasons' => $ad_rejection_reasons,
            'selected_reasons' => $selected_reasons,
            'covering_letter_files' => $covering_letter_files,
            'max_newspapers' => $currentLimit['limit'],
            'is_unlimited' => $currentLimit['is_unlimited'],
            'ad_worth_limits' => $ad_worth_limits,
            'adCreatedByRole' =>  $adCreatedByRole,
            'is_department_user' => $is_department_user,
            'is_office_user' => $is_office_user,
            'departments' => $departments,
            'offices' => $offices,
        ]);
    }

    // workflow action log
    private function logWorkflowAction($advertisement, $user, $action, $fromStatusId, $toStatusId, $comments = null, $assignedToId = null, $metadata = [])
    {
        // Get status names from status_id
        $fromStatus = $fromStatusId ? Status::find($fromStatusId)?->title : null;
        $toStatus = $toStatusId ? Status::find($toStatusId)?->title : null;

        AdChangeLog::create([
            'advertisement_id' => $advertisement->id,
            'user_id'          => $user->id,
            'role'             => $user->getRoleNames()->first(),
            'action'           => $action,
            'from_status'      => $fromStatus,
            'to_status'        => $toStatus,
            'assigned_to_id'   => $assignedToId,
            'field'            => null, // no specific field
            'old_value'        => null,
            'new_value'        => null,
            'metadata'         => $metadata,
            'comments'         => $comments,
            'changed_at'       => now(),
        ]);
    }

    // Update Ad
    public function update(Request $request, $id)
    {
        // dd($request->all());

        $user = auth()->user();
        $user_name = $user->name;
        $role = $user->getRoleNames()->first(); // Spatie roles
        $roleId = $user->roles->pluck('id'); // Returns a collection of role IDs
        $current_role  =  $roleId->first();

        // Determine user type
        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));

        $advertisement = Advertisement::findOrFail($id);
        // Check authorization
        if ($is_office_user && $advertisement->user_id != $user->id) {
            return redirect()->back()->with('error', 'You are not authorized to update this advertisement.');
        }

        if ($is_department_user && $advertisement->department_id != $user->department_id) {
            return redirect()->back()->with('error', 'You are not authorized to update this advertisement.');
        }

        $action = $request->input('action');


        // DEPARTMENT USER ACTIONS

        if ($is_department_user) {
            // Department approval
            if ($action === 'department_approve') {
                return $this->handleDepartmentApprove($request, $advertisement, $user);
            }

            // Department send back to office
            if ($action === 'department_send_back') {
                return $this->handleDepartmentSendBack($request, $advertisement, $user);
            }
        }


        // OFFICE USER RESUBMISSION

        if ($is_office_user && $action === 'resubmit_to_department') {
            return $this->handleOfficeResubmit($request, $advertisement, $user);
        }


        // IPR USERS SEND BACK TO DEPARTMENT/OFFICE

        if (
            in_array($action, ['send_back_to_department', 'send_back_to_office']) &&
            in_array($role, ['Superintendent', 'Deputy Director', 'Director General', 'Secretary'])
        ) {
            return $this->handleIprSendBack($request, $advertisement, $user, $action);
        }


        // Only run regular validation if not a special action
        if (!in_array($action, [
            'department_approve',
            'department_send_back',
            'resubmit_to_department',
            'send_back_to_department',
            'send_back_to_office'
        ])) {

            $request->validate([
                'urdu_space' => 'nullable|string|max:255',
                'urdu_size' => 'required|numeric|min:0.01',
                'english_space' => 'nullable|string|max:255',
                'english_size' => 'required|numeric|min:0.01',
                'urdu_lines' => 'required|numeric|min:1',
                'english_lines' => 'required|numeric|min:1',
                'classified_ad_type_id' => 'required|exists:classified_ad_types,id',
                'ad_worth_id' => 'required|exists:ad_worth_parameters,id',
                'adv_agency_id' => 'nullable|exists:adv_agencies,id',
                'news_pos_rate_id' => 'nullable|exists:news_pos_rates,id',
                'newspaper_id' => 'required|array|min:1',
                'newspaper_id.*' => 'integer|exists:newspapers,id',
                // 'remarks' => 'nullable|string|max:1000',
            ]);

            //  Newspaper limit logic based on ad_worth_id
            $adWorth = AdWorthParameter::find($request->ad_worth_id);
            $range = strtolower(trim($adWorth->range ?? ''));

            $maxLimit = match ($range) {
                'others' => null,
                'upto 1m', 'upto 1.0m' => 2,
                '1m to 3m', '1.0m to 3.0m' => 3,
                '3m to 5m', '3.0m to 5.0m' => 4,
                '5m and above', '5.0m & above' => 5,
                default => 0,
            };

            if ($maxLimit !== null) {
                $selectedCount = count($request->newspaper_id);

                if ($selectedCount !== $maxLimit) {
                    return back()->withInput()->withErrors([
                        'newspaper_id' => "You must select exactly {$maxLimit} newspaper(s) for this estimated cost range."
                    ]);
                }
            }
        }
        // Determine which button was clicked
        $action = $request->input('action');

        if ($user->hasRole('Diary Dispatch')) {
            if ($advertisement->status_id == 3) {
                $fromStatusId = $advertisement->status_id;
                $advertisement->status_id = 4;
                $advertisement->forwarded_to_role_id = 3; // role_id = 3 => Superintendent
                $advertisement->save();
                // --- LOG THIS ACTION ---
                $this->logWorkflowAction(
                    $advertisement,
                    $user,
                    'forwarded to superintendent',
                    $fromStatusId,
                    $advertisement->status_id,
                    $request->input('comments'),
                    $advertisement->forwarded_to_role_id
                );
            } elseif ($advertisement->status_id == 10 && $advertisement->forwarded_by_role_id == 10) {
                $fromStatusId = $advertisement->status_id;
                $advertisement->forwarded_to_role_id = 9;
                $advertisement->save();
                // --- LOG THIS ACTION ---
                $this->logWorkflowAction(
                    $advertisement,
                    $user,
                    'forwarded to superintendent',
                    $fromStatusId,
                    $advertisement->status_id,
                    $request->input('comments'),
                    $advertisement->forwarded_to_role_id
                );
            }
        } elseif ($user->hasRole('Superintendent')) {
            if (
                ($advertisement->status_id == 3 && $advertisement->forwarded_by_role_id == 2) ||
                ($advertisement->status_id == 3 && $advertisement->forwarded_by_role_id == 9)
            ) {
                $fromStatusId = $advertisement->status_id;
                // Track newspaper change for Superintendent
                $oldSuptdNP = is_array($advertisement->suptd_NP_log) ? $advertisement->suptd_NP_log : json_decode($advertisement->suptd_NP_log, true) ?? [];
                $oldSuptdNP = array_map('intval', $oldSuptdNP);
                $newSuptdNP = array_map('intval', $request->newspaper_id);
                sort($oldSuptdNP);
                sort($newSuptdNP);

                $advertisement->status_id = 4;
                $advertisement->forwarded_to_role_id = 11;
                $advertisement->suptd_NP_log = $request->newspaper_id;
                $advertisement->save();

                // Log newspaper change if different
                if ($oldSuptdNP !== $newSuptdNP) {
                    AdChangeLog::create([
                        'advertisement_id' => $advertisement->id,
                        'user_id' => $user->id,
                        'role' => $role,
                        'field' => 'suptd_NP_log',
                        'old_value' => implode(',', $oldSuptdNP),
                        'new_value' => implode(',', $newSuptdNP),
                        'changed_at' => now(),
                    ]);
                }

                // --- LOG THIS ACTION ---
                $this->logWorkflowAction(
                    $advertisement,
                    $user,
                    'forwarded to Deputy Director',
                    $fromStatusId,
                    $advertisement->status_id,
                    'Advertisement submitted to deputy director for approval.',
                    $advertisement->forwarded_to_role_id
                );
            } elseif ($advertisement->status_id == 10 && $advertisement->forwarded_by_role_id == 10) {
                $fromStatusId = $advertisement->status_id;
                $advertisement->forwarded_to_role_id = 9;
                $advertisement->save();
                // --- LOG THIS ACTION ---
                $this->logWorkflowAction(
                    $advertisement,
                    $user,
                    'forwarded to deputy director',
                    $fromStatusId,
                    $advertisement->status_id,
                    $request->input('comments'),
                    $advertisement->forwarded_to_role_id
                );
            }
        } elseif ($user->hasRole('Deputy Director')) {
            if ($action == 'forward' && $advertisement->forwarded_by_role_id == 3 && $advertisement->status_id == 4) {
                $fromStatusId = $advertisement->status_id;
                // Track newspaper change for Deputy Director
                $oldDdNP = is_array($advertisement->dd_NP_log) ? $advertisement->dd_NP_log : json_decode($advertisement->dd_NP_log, true) ?? [];
                $oldDdNP = array_map('intval', $oldDdNP);
                $newDdNP = array_map('intval', $request->newspaper_id);
                sort($oldDdNP);
                sort($newDdNP);

                $advertisement->forwarded_to_role_id = 10;
                $advertisement->dd_NP_log = $request->newspaper_id;
                $advertisement->save();

                // Log newspaper change if different
                if ($oldDdNP !== $newDdNP) {
                    AdChangeLog::create([
                        'advertisement_id' => $advertisement->id,
                        'user_id' => $user->id,
                        'role' => $role,
                        'field' => 'dd_NP_log',
                        'old_value' => implode(',', $oldDdNP),
                        'new_value' => implode(',', $newDdNP),
                        'changed_at' => now(),
                    ]);
                }

                // --- LOG THIS ACTION ---
                $this->logWorkflowAction(
                    $advertisement,
                    $user,
                    'forwarded to director general',
                    $fromStatusId,
                    $advertisement->status_id,
                    'Advertisement forwarded to director general for approval.',
                    $advertisement->forwarded_to_role_id
                );
            } elseif ($action == 'approve' && $advertisement->forwarded_by_role_id == 3 && $advertisement->status_id == 4) {
                $fromStatusId = $advertisement->status_id;
                // Track newspaper change for Deputy Director
                $oldDdNP = is_array($advertisement->dd_NP_log) ? $advertisement->dd_NP_log : json_decode($advertisement->dd_NP_log, true) ?? [];
                $oldDdNP = array_map('intval', $oldDdNP);
                $newDdNP = array_map('intval', $request->newspaper_id);
                sort($oldDdNP);
                sort($newDdNP);

                $advertisement->status_id = 10;
                $advertisement->dd_NP_log = $request->newspaper_id;
                $advertisement->forwarded_to_role_id = 3; // role_id = 3 => Superintendent
                $advertisement->save();

                // Log newspaper change if different
                if ($oldDdNP !== $newDdNP) {
                    AdChangeLog::create([
                        'advertisement_id' => $advertisement->id,
                        'user_id' => $user->id,
                        'role' => $role,
                        'field' => 'dd_NP_log',
                        'old_value' => implode(',', $oldDdNP),
                        'new_value' => implode(',', $newDdNP),
                        'changed_at' => now(),
                    ]);
                }

                // --- LOG THIS ACTION ---
                $this->logWorkflowAction(
                    $advertisement,
                    $user,
                    'approved and submitted to superintendent',
                    $fromStatusId,
                    $advertisement->status_id,
                    'Advertisement approved by deputy director and submitted to superintendent for PR.',
                    $advertisement->forwarded_to_role_id
                );
            }
        } elseif ($user->hasRole('Director General')) {
            if ($action == 'forward' && $advertisement->forwarded_by_role_id == 11 && $advertisement->status_id == 4) {
                $fromStatusId = $advertisement->status_id;
                // Track newspaper change for Director General
                $oldDgNP = is_array($advertisement->dg_NP_log) ? $advertisement->dg_NP_log : json_decode($advertisement->dg_NP_log, true) ?? [];
                $oldDgNP = array_map('intval', $oldDgNP);
                $newDgNP = array_map('intval', $request->newspaper_id);
                sort($oldDgNP);
                sort($newDgNP);

                $advertisement->forwarded_to_role_id = 12; // role_id =12 => secretary
                $advertisement->dg_NP_log = $request->newspaper_id;
                $advertisement->save();

                // Log newspaper change if different
                if ($oldDgNP !== $newDgNP) {
                    AdChangeLog::create([
                        'advertisement_id' => $advertisement->id,
                        'user_id' => $user->id,
                        'role' => $role,
                        'field' => 'dg_NP_log',
                        'old_value' => implode(',', $oldDgNP),
                        'new_value' => implode(',', $newDgNP),
                        'changed_at' => now(),
                    ]);
                }

                // --- LOG THIS ACTION ---
                $this->logWorkflowAction(
                    $advertisement,
                    $user,
                    'forwarded to secretary',
                    $fromStatusId,
                    $advertisement->status_id,
                    'Advertisement submitted to secretary for approval.',
                    $advertisement->forwarded_to_role_id
                );
            } elseif ($action == 'approve' && $advertisement->forwarded_by_role_id == 11 && $advertisement->status_id == 4) {
                $fromStatusId = $advertisement->status_id;
                // Track newspaper change for Director General
                $oldDgNP = is_array($advertisement->dg_NP_log) ? $advertisement->dg_NP_log : json_decode($advertisement->dg_NP_log, true) ?? [];
                $oldDgNP = array_map('intval', $oldDgNP);
                $newDgNP = array_map('intval', $request->newspaper_id);
                sort($oldDgNP);
                sort($newDgNP);

                $advertisement->status_id = 10;
                $advertisement->forwarded_to_role_id = 3; // role_id = 3 => Superintendent
                $advertisement->dg_NP_log = $request->newspaper_id;
                $advertisement->save();

                // Log newspaper change if different
                if ($oldDgNP !== $newDgNP) {
                    AdChangeLog::create([
                        'advertisement_id' => $advertisement->id,
                        'user_id' => $user->id,
                        'role' => $role,
                        'field' => 'dg_NP_log',
                        'old_value' => implode(',', $oldDgNP),
                        'new_value' => implode(',', $newDgNP),
                        'changed_at' => now(),
                    ]);
                }

                // --- LOG THIS ACTION ---
                $this->logWorkflowAction(
                    $advertisement,
                    $user,
                    'approved and submitted to superintendent',
                    $fromStatusId,
                    $advertisement->status_id,
                    'Advertisement approved by director general and submitted to superintendent for PR.',
                    $advertisement->forwarded_to_role_id
                );
            }
        } elseif ($user->hasRole('Secretary') && $advertisement->forwarded_by_role_id == 10 && $advertisement->status_id == 4) {
            $fromStatusId = $advertisement->status_id;
            // Track newspaper change for Secretary
            $oldSecNP = is_array($advertisement->sec_NP_log) ? $advertisement->sec_NP_log : json_decode($advertisement->sec_NP_log, true) ?? [];
            $oldSecNP = array_map('intval', $oldSecNP);
            $newSecNP = array_map('intval', $request->newspaper_id);
            sort($oldSecNP);
            sort($newSecNP);

            $advertisement->status_id = 10;
            $advertisement->forwarded_to_role_id = 3; // role_id = 3 => Superintendent
            $advertisement->sec_NP_log = $request->newspaper_id;
            $advertisement->save();

            // Log newspaper change if different
            if ($oldSecNP !== $newSecNP) {
                AdChangeLog::create([
                    'advertisement_id' => $advertisement->id,
                    'user_id' => $user->id,
                    'role' => $role,
                    'field' => 'sec_NP_log',
                    'old_value' => implode(',', $oldSecNP),
                    'new_value' => implode(',', $newSecNP),
                    'changed_at' => now(),
                ]);
            }

            // --- LOG THIS ACTION ---
            $this->logWorkflowAction(
                $advertisement,
                $user,
                'approved and submitted to superintendent',
                $fromStatusId,
                $advertisement->status_id,
                'Advertisement approved and submitted to superintendent for PR.',
                $advertisement->forwarded_to_role_id
            );
        }

        if (empty($advertisement->newspaper_id)) {
            $advertisement->newspaper_id = $request->newspaper_id;
        }

        // Ad Change Log
        // Store the old value before making any role-based updates
        $oldNewspapers = is_array($advertisement->newspaper_id)
            ? $advertisement->newspaper_id
            : json_decode($advertisement->newspaper_id, true) ?? [];

        // --- Your Superintendent role logic ---
        // if ($user->hasRole('Superintendent')) {
        //     $advertisement->status_id = 4;
        //     $advertisement->forwarded_to_role_id = 11;
        //     $advertisement->suptd_NP_log = $request->newspaper_id;

        //     // Do NOT update $advertisement->newspaper_id here before logging
        // }

        // --- Ad Change Log ---
        $fieldsToTrack = ['urdu_space', 'urdu_size', 'english_space', 'english_size', 'newspaper_id'];

        foreach ($fieldsToTrack as $field) {
            if ($field === 'newspaper_id') {
                $oldValue = array_map('intval', $oldNewspapers); // use stored old value
                $newValue = array_map('intval', $request->newspaper_id);

                sort($oldValue);
                sort($newValue);

                if ($oldValue !== $newValue) {
                    AdChangeLog::create([
                        'advertisement_id' => $advertisement->id,
                        'user_id' => $user->id,
                        'role' => $role,
                        'field' => $field,
                        'old_value' => implode(',', $oldValue),
                        'new_value' => implode(',', $newValue),
                        'changed_at' => now(),
                    ]);
                }
            } else {
                $oldValue = $advertisement->$field;
                $newValue = $request->$field;

                if ($oldValue != $newValue) {
                    AdChangeLog::create([
                        'advertisement_id' => $advertisement->id,
                        'user_id' => $user->id,
                        'role' => $role,
                        'field' => $field,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'changed_at' => now(),
                    ]);
                }
            }
        }

        $currentBill = $request->input('current_bill');

        // Now update the actual field after logging
        $advertisement->newspaper_id = $request->newspaper_id;
        $advertisement->urdu_space = $request->urdu_space;
        $advertisement->urdu_lines = $request->urdu_lines;
        $advertisement->english_space = $request->english_space;
        $advertisement->english_lines = $request->english_lines;
        $advertisement->urdu_size = $request->urdu_size;
        $advertisement->english_size = $request->english_size;
        // $advertisement->ad_category_id = $request->ad_category_id;
        $advertisement->ad_worth_id = $request->ad_worth_id;
        $advertisement->adv_agency_id = $request->adv_agency_id;
        $advertisement->news_pos_rate_id = $request->news_pos_rate_id;
        $advertisement->current_bill = $currentBill;
        $advertisement->forwarded_by_role_id = $current_role;



        $advertisement->save();

        dispatch(function () use ($advertisement, $user_name) {

            $forwardedByRoles = [12, 11, 10];
            $forwardedByRoleId = $advertisement->forwarded_by_role_id;
            $forwardedToRoleId = $advertisement->forwarded_to_role_id;

            foreach (User::getByRole($advertisement->forwarded_to_role_id) as $user) {
                if (in_array($forwardedByRoleId, $forwardedByRoles) && $forwardedToRoleId == 3) {
                    $message = 'Advertisement with inf number ' . $advertisement->inf_number . ' has been approved and forwarded by ' . $user_name . '.';
                } else {
                    $message = 'Advertisement with inf number ' . $advertisement->inf_number . ' has been Forwarded by ' . $user_name . '.';
                }
                notifyUser($user, [
                    'title' => 'Advertisement Forwarded.',
                    'message' => $message,
                    'url' => url('advertisements-show', ['id' => $advertisement->id])
                ]);
            }
        });

        return redirect()->route('advertisements.index')->with('success', 'Advertisement forwarded successfully.');
    }



    // HELPER METHODS FOR DEPARTMENT WORKFLOW


    /**
     * Handle department approval
     */
    private function handleDepartmentApprove(Request $request, $advertisement, $user)
    {
        // Check if advertisement is pending department approval
        $pendingStatus = Status::where('title', 'Pending Department Approval')->first();

        if ($advertisement->status_id != $pendingStatus->id) {
            return redirect()->back()->with('error', 'This advertisement is not pending department approval.');
        }

        // Update advertisement
        $newStatus = Status::where('title', 'New')->first();
        $fromStatusId = $advertisement->status_id;
        $advertisement->status_id = $newStatus->id;
        $advertisement->forwarded_by_role_id = $user->roles->pluck('id')->first();
        $advertisement->forwarded_to_role_id = 3; // Superintendent
        // $advertisement->department_approved_at = now();
        // $advertisement->department_approved_by = $user->id;
        // $advertisement->workflow_stage = 'ipr_review';
        $advertisement->save();

        // Log workflow action
        $this->logWorkflowAction(
            $advertisement,
            $user,
            'department_approved',
            $fromStatusId,
            $advertisement->status_id,
            $request->input('comments'),
            3 // Superintendent role_id
        );

        // // Store workflow history
        // WorkflowHistory::create([
        //     'advertisement_id' => $advertisement->id,
        //     'user_id' => $user->id,
        //     'action' => 'department_approved',
        //     'from_status_id' => $pendingStatus->id,
        //     'to_status_id' => $newStatus->id,
        //     'from_role_id' => $user->roles->pluck('id')->first(),
        //     'to_role_id' => 3,
        //     'remarks' => 'Approved by department',
        //     'created_at' => now(),
        // ]);

        return redirect()->route('advertisements.index')->with('success', 'Advertisement forwarded to IPR department.');
    }



    /**
     * Handle department send back to office
     */
    private function handleDepartmentSendBack(Request $request, $advertisement, $user)
    {
        // $request->validate([
        //     'remarks' => 'required|string|max:500',
        // ]);
        // dd($request);
        $sentBackStatus = Status::where('title', 'Sent Back to Office')->first();
        $fromStatusId = $advertisement->status_id;
        $advertisement->status_id = $sentBackStatus->id;
        $advertisement->remarks = $request->remarks;
        // $advertisement->sent_back_by = $user->id;
        // $advertisement->sent_back_at = now();
        // $advertisement->workflow_stage = 'sent_back_to_office';
        $advertisement->forwarded_to_role_id = $advertisement->user->roles->pluck('id')->first();
        $advertisement->save();

        // Log workflow action
        $this->logWorkflowAction(
            $advertisement,
            $user,
            'department_sent_back',
            $fromStatusId,
            $advertisement->status_id,
            $request->remarks,
            $advertisement->user->roles->pluck('id')->first(),
            ['rejection_reasons' => $request->ad_rejection_reasons_id ?? []]
        );

        // // Store workflow history
        // WorkflowHistory::create([
        //     'advertisement_id' => $advertisement->id,
        //     'user_id' => $user->id,
        //     'action' => 'department_sent_back',
        //     'from_status_id' => $advertisement->getOriginal('status_id'),
        //     'to_status_id' => $sentBackStatus->id,
        //     'from_role_id' => $user->roles->pluck('id')->first(),
        //     'to_role_id' => $advertisement->user->roles->pluck('id')->first(),
        //     'remarks' => $request->send_back_reason,
        //     'created_at' => now(),
        // ]);

        return redirect()->route('advertisements.index')->with('success', 'Advertisement sent back to office for corrections.');
    }

    /**
     * Handle office resubmission to department
     */
    private function handleOfficeResubmit(Request $request, $advertisement, $user)
    {
        $sentBackStatus = Status::where('title', 'Sent Back to Office')->first();

        if ($advertisement->status_id != $sentBackStatus->id) {
            return redirect()->back()->with('error', 'This advertisement is not sent back for corrections.');
        }

        // // Validate required fields for resubmission
        // $request->validate([
        //     'memo_number' => 'nullable|string|max:255',
        //     'memo_date' => 'nullable|date',
        //     'publish_on_or_before' => 'required|date',
        //     'source_of_fund' => 'nullable|string|max:255',
        //     'adp_code' => 'nullable|string|max:255',
        //     'project_name' => 'nullable|string|max:255',
        //     'remarks' => 'nullable|string|max:500',
        //     // 'urdu_lines' => 'required|numeric|min:1',
        //     // 'english_lines' => 'required|numeric|min:1',
        //     // 'urdu_size' => 'required|numeric|min:1',
        //     // 'english_size' => 'required|numeric|min:1',
        // ]);

        $pendingStatus = Status::where('title', 'Pending Department Approval')->first();
        $fromStatusId = $advertisement->status_id;
        $advertisement->status_id = $pendingStatus->id;
        $advertisement->memo_number = $request->memo_number;
        $advertisement->memo_date = $request->memo_date;
        $advertisement->publish_on_or_before = $request->publish_on_or_before;
        $advertisement->source_of_fund = $request->source_of_fund;
        $advertisement->adp_code = $request->adp_code;
        $advertisement->project_name = $request->project_name;
        $advertisement->remarks = 'resubmitted after corrections';
        // $advertisement->workflow_stage = 'department_review';
        $advertisement->forwarded_to_role_id = 2; // Department user role
        // $advertisement->resubmitted_at = now();
        $advertisement->save();

        // Log workflow action
        $this->logWorkflowAction(
            $advertisement,
            $user,
            'resubmitted_to_department',
            $fromStatusId,
            $advertisement->status_id,
            $request->input('comments'),
            2 // Department role_id
        );

        // // Store workflow history
        // WorkflowHistory::create([
        //     'advertisement_id' => $advertisement->id,
        //     'user_id' => $user->id,
        //     'action' => 'resubmitted_to_department',
        //     'from_status_id' => $sentBackStatus->id,
        //     'to_status_id' => $pendingStatus->id,
        //     'from_role_id' => $user->roles->pluck('id')->first(),
        //     'to_role_id' => 2,
        //     'remarks' => 'Resubmitted after corrections',
        //     'created_at' => now(),
        // ]);

        return redirect()->route('advertisements.index')->with('success', 'Advertisement resubmitted to department.');
    }

    /**
     * Handle IPR send back to department or office
     */
    private function handleIprSendBack(Request $request, $advertisement, $user, $action)
    {
        $request->validate([
            'send_back_reason' => 'required|string|max:500',
            'send_back_to_type' => 'required|in:department,office',
        ]);

        $sentBackStatus = Status::where('title', 'Sent Back')->first();
        $fromStatusId = $advertisement->status_id;
        $advertisement->status_id = $sentBackStatus->id;
        $advertisement->send_back_reason = $request->send_back_reason;
        // $advertisement->sent_back_by = $user->id;
        // $advertisement->sent_back_at = now();

        if ($request->send_back_to_type === 'department') {
            // $advertisement->workflow_stage = 'sent_back_to_department';
            $advertisement->forwarded_to_role_id = 2; // Department user
        } else {
            // $advertisement->workflow_stage = 'sent_back_to_office';
            $advertisement->forwarded_to_role_id = $advertisement->user->roles->pluck('id')->first();
        }

        $advertisement->save();

        // Log workflow action
        $this->logWorkflowAction(
            $advertisement,
            $user,
            $action,
            $fromStatusId,
            $advertisement->status_id,
            $request->send_back_reason,
            $advertisement->forwarded_to_role_id
        );

        // // Store workflow history
        // WorkflowHistory::create([
        //     'advertisement_id' => $advertisement->id,
        //     'user_id' => $user->id,
        //     'action' => 'ipr_sent_back',
        //     'from_status_id' => $advertisement->getOriginal('status_id'),
        //     'to_status_id' => $sentBackStatus->id,
        //     'from_role_id' => $user->roles->pluck('id')->first(),
        //     'to_role_id' => $advertisement->forwarded_to_role_id,
        //     'remarks' => "Sent back to {$request->send_back_to_type}: " . $request->send_back_reason,
        //     'created_at' => now(),
        // ]);

        return redirect()->route('advertisements.index')->with('success', "Advertisement sent back to {$request->send_back_to_type}.");
    }




    // Change Newspapers Limit based on Estimated Cost
    public function getAdWorthLimit($id)
    {
        $param = AdWorthParameter::find($id);
        if (!$param) {
            return response()->json(['limit' => 0, 'is_unlimited' => false]);
        }

        $range = strtolower(trim($param->range ?? ''));
        if ($range === 'others') {
            return response()->json(['limit' => 999, 'is_unlimited' => true]);
        } elseif ($range === 'upto 1.0m') {
            return response()->json(['limit' => 2, 'is_unlimited' => false]);
        } elseif ($range === '1.0m to 3.0m') {
            return response()->json(['limit' => 3, 'is_unlimited' => false]);
        } elseif ($range === '3.0m to 5.0m') {
            return response()->json(['limit' => 4, 'is_unlimited' => false]);
        } elseif ($range === '5.0m & above') {
            return response()->json(['limit' => 5, 'is_unlimited' => false]);
        }

        return response()->json(['limit' => 0, 'is_unlimited' => false]);
    }

    // Show Single Ad
    public function show($id)
    {
        // Page title
        $pageTitle = 'Ad Details &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'New Ads', 'url' => route('advertisements.index')],
            ['label' => 'Ad Details', 'url' => null], // The current page (no URL)
        ];

        $advertisement = Advertisement::findOrFail($id);
        $covering_letter_files = $advertisement->getMedia('covering_letters');
        $urdu_ad_files = $advertisement->getMedia('urdu_ads');
        $english_ad_files = $advertisement->getMedia('english_ads');

        // Data Title
        $dataTitle = 'New Ad Details';
        $newspaperNames = Newspaper::whereIn('id', $advertisement->newspaper_id ?? [])
            ->pluck('title')
            ->toArray();

        $suptdNewspaperNames = Newspaper::whereIn('id', $advertisement->suptd_NP_log ?? [])
            ->pluck('title')
            ->toArray();
        $ddNewspaperNames = Newspaper::whereIn('id', $advertisement->dd_NP_log ?? [])
            ->pluck('title')
            ->toArray();
        $dgNewspaperNames = Newspaper::whereIn('id', $advertisement->dg_NP_log ?? [])
            ->pluck('title')
            ->toArray();

        $secNewspaperNames = Newspaper::whereIn('id', $advertisement->sec_NP_log ?? [])
            ->pluck('title')
            ->toArray();
        $userName = User::where('id', $advertisement->user_id)->value('name');

        $departmentName = Department::where('id', $advertisement->department_id)->value('name');
        $officeName = Office::where('id', $advertisement->office_id)->value('ddo_name');
        $adWorthparameters = AdWorthParameter::where('id', $advertisement->ad_worth_id)->value('range');
        $classifiedAdType = ClassifiedAdType::where('id', $advertisement->classified_ad_type_id)->value('type');
        $newsposrate = NewsPosRate::where('id', $advertisement->news_pos_rate_id)->first(['position', 'rates']);
        $advAgency = AdvAgency::where('id', $advertisement->adv_agency_id)->value('name');
        $forwardedBy = Role::where('id', $advertisement->forwarded_by_role_id)->value('name');
        $forwardedTo = Role::where('id', $advertisement->forwarded_to_role_id)->value('name');

        $status = Status::where('id', $advertisement->status_id)->value('title');



        return view('advertisements.show', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advertisement' => $advertisement,
            'covering_letter_files' => $covering_letter_files,
            'urdu_ad_files' => $urdu_ad_files,
            'english_ad_files' => $english_ad_files,
            'advAgency' => $advAgency,
            'newspaperNames' => $newspaperNames,
            'suptdNewspaperNames' => $suptdNewspaperNames,
            'ddNewspaperNames' => $ddNewspaperNames,
            'dgNewspaperNames' => $dgNewspaperNames,
            'secNewspaperNames' => $secNewspaperNames,
            'userName' => $userName,
            'departmentName' => $departmentName,
            'officeName' => $officeName,
            'adWorthparameters' => $adWorthparameters,
            'classifiedAdType' => $classifiedAdType,
            'newsposrate' => $newsposrate,
            'forwardedBy' => $forwardedBy,
            'forwardedTo' => $forwardedTo,
            'status' => $status,

        ]);
    }

    // When click on any image its show details of each file with human readable form
    public function fileShow($advertisementId, $imageId)
    {

        // dd($id, $imageId);
        try {
            $advertisement = Advertisement::findOrFail($advertisementId);
            $file_image = $advertisement->media()->where('id', $imageId)->first();

            if (!$file_image) {
                return response()->json(['error' => 'File not found'], 404);
            }

            // Check if it's PDF and has conversion, otherwise use original
            if ($file_image->mime_type === 'application/pdf') {
                if ($file_image->hasGeneratedConversion('thumb')) {
                    $url = $file_image->getUrl('thumb');
                } else {
                    // If no conversion, return PDF icon
                    $url = asset('assets/img/pdf/pdficon.jpg');
                }
            } else {
                // For images, use original file
                $url = $file_image->getUrl();
            }

            return response()->json([
                'id' => $file_image->id,
                'name' => $file_image->name,
                'collection_name' => $file_image->collection_name,
                'mime_type' => $file_image->mime_type,
                'size' => $file_image->human_readable_size,
                'url' => $file_image->getUrl(),
                'Url' => $url // only URL that we use in modal
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error loading file'], 500);
        }
    }

    // Ad rejection reasons
    public function rejectionReason(Request $request, $id)
    {
        $user = auth()->user();
        $user_role = $user->roles->pluck('id');
        $current_role = $user_role->first();
        $request->validate([
            'ad_rejection_reasons_id' => 'required|exists:ad_rejection_reasons,id',
            'remarks' => 'string|nullable'
        ]);

        $advertisement = Advertisement::findOrFail($id);
        if ($user->hasRole('Secretary')) {
            // Update the advertisement record with rejection details
            $advertisement->ad_rejection_reasons_id = $request->ad_rejection_reasons_id;
            $advertisement->remarks = $request->remarks;
            $advertisement->status_id = 7;
            $advertisement->forwarded_to_role_id = 10;
            $advertisement->forwarded_by_role_id = $current_role;
        } elseif ($user->hasRole('Director General')) {

            $advertisement->ad_rejection_reasons_id = $request->ad_rejection_reasons_id;
            $advertisement->remarks = $request->remarks;
            $advertisement->status_id = 7;
            $advertisement->forwarded_to_role_id = 11;
            $advertisement->forwarded_by_role_id = $current_role;
        } elseif ($user->hasRole('Deputy Director')) {

            $advertisement->ad_rejection_reasons_id = $request->ad_rejection_reasons_id;
            $advertisement->remarks = $request->remarks;
            $advertisement->status_id = 7;
            $advertisement->forwarded_to_role_id = 3;
            $advertisement->forwarded_by_role_id = $current_role;
        }

        $advertisement->save();

        dispatch(function () use ($advertisement) {

            foreach (User::Assistants() as $assistant) {
                notifyUser($assistant, [
                    'title' => 'Advertisement Rejected!.',
                    'message' => 'Advertisement #' . $advertisement->id . ' has been rejected.',
                    'url' => url('advertisements-show', ['id' => $advertisement->id])
                ]);
            }
        });

        return redirect()->route('advertisements.index')
            ->with('success', 'Advertisement rejected successfully.');
    }

    public function media(Request $request, $id)
    {
        $user = auth()->user();

        $roleId = $user->roles->pluck('id'); // Returns a collection of role IDs
        $current_role  =  $roleId->first();
        $user_name = $user->name;

        $advertisement = Advertisement::findOrFail($id);
        if ($user->hasRole('Superintendent')) {
            if (
                $advertisement->status_id == 10 &&
                (
                    ($advertisement->forwarded_by_role_id == 12 && $advertisement->forwarded_to_role_id == 3) ||
                    ($advertisement->forwarded_by_role_id == 10 && $advertisement->forwarded_to_role_id == 3) ||
                    ($advertisement->forwarded_by_role_id == 11 && $advertisement->forwarded_to_role_id == 3)
                )
            ) {
                $fromStatusId = $advertisement->status_id;
                $advertisement->forwarded_to_role_id = 4; // Media
            }
            // --- LOG THIS ACTION ---
            $this->logWorkflowAction(
                $advertisement,
                $user,
                'Advertisement Sent To Media',
                $fromStatusId,
                $advertisement->status_id,
                'Advertisement Sent to Media for Publication.',
                $advertisement->forwarded_to_role_id
            );
        }

        $advertisement->forwarded_by_role_id = $current_role;


        // --- ADD SPATIE ACTIVITY LOG for the custom action ---
        activity()
            ->causedBy($user)
            ->performedOn($advertisement)
            ->withProperties([
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'old_forwarded_to' => 3,
                'new_forwarded_to' => 4,
            ])
            ->log('Advertisement forwarded to Media');

        $newspaperIds = [];
        if (!empty($advertisement->sec_NP_log)) {
            $newspaperIds = $advertisement->sec_NP_log;
        } elseif (empty($advertisement->sec_NP_log) && !empty($advertisement->dg_NP_log)) {
            $newspaperIds = $advertisement->dg_NP_log;
        } elseif (empty($advertisement->sec_NP_log && $advertisement->dg_NP_log) && !empty($advertisement->dd_NP_log)) {
            $newspaperIds = $advertisement->dd_NP_log;
        }

        if (!empty($newspaperIds) && empty($advertisement->adv_agency_id)) {
            foreach ($newspaperIds as $newspaper) {
                // find the newspaper's user
                $newspaperUser = User::where('newspaper_id', $newspaper)->first();

                if ($newspaperUser) {
                    BillClassifiedAd::firstOrCreate(
                        [
                            'advertisement_id' => $advertisement->id,
                            'user_id' => $newspaperUser->id, // 👈 save user_id instead of newspaper_id
                        ],
                        [
                            'printed_bill_cost' => 0,
                            'printed_total_bill' => 0,
                            'status' => 'pending',
                        ]
                    );
                }
            }
        }

        $advertisement->save();

        // After update, get the latest activity for this model
        $activity = $advertisement->activities()->latest()->first();
        if ($activity) {
            $properties = $activity->properties;
            $properties['page'] = $request->fullUrl();
            $properties['ip'] = $request->ip();
            $properties['user_agent'] = $request->userAgent();
            $activity->properties = $properties;
            $activity->save();
        }

        dispatch(function () use ($advertisement, $user_name) {


            foreach (User::getByRole($advertisement->forwarded_to_role_id) as $user) {
                notifyUser($user, [
                    'title' => 'Advertisement Forwarded.',
                    'message' => 'Advertisement with inf number ' . $advertisement->inf_number . ' has been Forwarded by DGIPR for publication.',
                    'url' => url('advertisements-show', ['id' => $advertisement->id])
                ]);
            }
        });
        return redirect()->route('advertisements.approved')->with('success', 'Advertisement sent to media for publication.');
    }


    // Show Draft Ads
    public function draftIndex()
    {
        // Page title
        $pageTitle = 'Draft Ads &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Draft Ads', 'url' => null],
        ];

        $user = auth()->user();

        // Get the ID of the "Draft" status
        $draftStatusId = Status::where('title', 'Draft')->value('id');

        // Get only the drafts created by the logged-in user with "Draft" status
        $draftAds = Advertisement::where('status_id', $draftStatusId)
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('advertisements.draft', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'draftAds' => $draftAds,
        ]);
    }

    // Edit Draft Ad
    public function editDraft($id)
    {
        // Page title
        $pageTitle = 'Edit Draft Ad &#x2053; DG&#8212;IPR IAMS';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Draft Ads', 'url' => route('advertisements.draft.index')],
            ['label' => 'Edit Draft Ad', 'url' => null], // The current page (no URL)
        ];

        $user = auth()->user();

        // Fetch only the current user's draft ad
        $advertisement = Advertisement::where('id', $id)
            ->where('status_id', Status::where('title', 'Draft')->value('id'))
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Load relationships
        $departments = Department::all();
        $classifiedAdTypes = ClassifiedAdType::all();
        $adWorths = AdWorthParameter::all();
        $ad_rejection_reasons = AdRejectionReason::all();

        $currentLimit = $ad_worth_limits[$advertisement->ad_worth_id] ?? ['limit' => 0, 'is_unlimited' => false];

        // Status
        $new_status = Status::where('title', 'New')->value('id');

        return view(
            'advertisements.edit-draft',
            [
                'pageTitle' => $pageTitle,
                'breadcrumbs' => $breadcrumbs,
                'user' => $user,
                'advertisement' => $advertisement,
                'departments' => $departments,
                'adWorths' => $adWorths,
                'classifiedAdTypes' => $classifiedAdTypes,
                'ad_rejection_reasons' => $ad_rejection_reasons,
                'max_newspapers' => $currentLimit['limit'],
                'is_unlimited' => $currentLimit['is_unlimited'],
                'new_status' => $new_status
            ]
        );
    }

    // Update Draft Ad
    public function updateDraft(Request $request, $id)
    {
        $user = auth()->user();

        $advertisement = Advertisement::where('id', $id)
            ->where('status_id', Status::where('title', 'Draft')->value('id'))
            ->where('user_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'memo_number' => 'nullable|string|max:255',
            'memo_date' => 'nullable|date',
            // 'department_id' => 'required|exists:departments,id',
            // 'office_id' => 'nullable|exists:offices,id',
            'ad_worth_id' => 'required|exists:ad_worth_parameters,id',
            'classified_ad_type_id' => 'required|exists:classified_ad_types,id',
            'publish_on_or_before' => 'required|date',
            'urdu_lines' => 'required|numeric|min:1',
            'english_lines' => 'required|numeric|min:1',
        ]);

        // Determine action: update or submit
        $action = $request->input('action');

        if ($action === 'submit-ad') {
            // Convert draft to submitted advertisement
            $advertisement->classified_ad_type_id = $request->classified_ad_type_id;
            $inf_number = generate_inf_number();
            $advertisement->inf_series_id = $inf_number['inf_series_id'];
            $advertisement->inf_number = $inf_number['inf_number'];
            $advertisement->status_id = $request->new_status;
            $advertisement->forwarded_by_role_id = $user->roles->pluck('id')->first();
            $advertisement->forwarded_to_role_id = 3;
        }

        $data = $request->except(['action', 'new_status']);

        // Convert memo_date to Y-m-d
        if (!empty($request->memo_date)) {
            $data['memo_date'] = \Carbon\Carbon::parse($request->memo_date)->format('Y-m-d');
        }

        $advertisement->fill($data);

        $advertisement->save();

        // Save media if any
        $advertisement->addAllMediaFromTokens();

        // return redirect()
        //     ->route('advertisements.draft')
        //     ->with('success', $action === 'submit-ad' ? 'Draft Advertisement submitted successfully.' : 'Draft Advertisement updated successfully.');

        return redirect()
            ->route($action === 'submit-ad' ? 'advertisements.inprogress' : 'advertisements.draft.index')
            ->with('success', $action === 'submit-ad'
                ? 'Draft Advertisement submitted successfully.'
                : 'Draft Advertisement updated successfully.');
    }

    // Show Single Draft Ad
    public function showDraftAd($id)
    {
        // Page title
        $pageTitle = 'Draft Ad Details &#x2053; IAMS-IPR';

        // Breadcrumb
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Draft Ads', 'url' => route('advertisements.draft.index')],
            ['label' => 'Draft Ad Details', 'url' => null], // The current page (no URL)
        ];

        // $advertisement = Advertisement::with([
        // 'department',
        // 'office',
        // 'adWorthParameter',
        // 'classifiedAdType',
        // 'clientOffice'
        // ])
        // ->where('drafted_at', true)
        // ->findOrFail($id);

        $advertisement = Advertisement::findOrFail($id)->where('drafted_at', true);

        // $covering_letter_files = $advertisement->getMedia('covering_letters');
        // $urdu_ad_files = $advertisement->getMedia('urdu_ads');
        // $english_ad_files = $advertisement->getMedia('english_ads');

        return view('advertisements.show-draft', compact(
            'pageTitle',
            'breadcrumbs',
            'advertisement',
            // 'covering_letter_files',
            // 'urdu_ad_files',
            // 'english_ad_files'
        ));

        $advertisement = Advertisement::findOrFail($id);
        $covering_letter_files = $advertisement->getMedia('covering_letters');
        $urdu_ad_files = $advertisement->getMedia('urdu_ads');
        $english_ad_files = $advertisement->getMedia('english_ads');

        return view('advertisements.show-draft', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'advertisement' => $advertisement,
            'covering_letter_files' => $covering_letter_files,
            'urdu_ad_files' => $urdu_ad_files,
            'english_ad_files' => $english_ad_files
        ]);
    }

    // Display All INF Serieses
    public function showSeries()
    {
        $pageTitle = 'INF Series &#x2053; IAMS-IPR';

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'INF Series', 'url' => null],
        ];

        $advertisements = Advertisement::all();

        $currentYear = now()->format('y');
        $currentSeries = INFSeries::where('series', 'like', "%/$currentYear")->first();
        $previousSeries = INFSeries::where('series', 'not like', "%/$currentYear")->get();

        return view(
            'inf_series.index',
            [
                'pageTitle' => $pageTitle,
                'breadcrumbs' =>  $breadcrumbs,
                'advertisements' => $advertisements,
                'currentSeries' => $currentSeries,
                'previousSeries' => $previousSeries
            ]
        );
    }


    // ======================
// 📤 EXPORT METHODS
// ======================

    /**
     * Export New Ads (index) to Excel
     */
    public function exportExcelIndex(Request $request)
    {
        $user = auth()->user();
        $query = $this->buildIndexQuery($request);  // reuse the index query logic
        $advertisements = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(
            new AdvertisementsExport($advertisements),
            'new_ads_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export New Ads (index) to PDF
     */
    public function exportPDFIndex(Request $request)
    {
        $query = $this->buildIndexQuery($request);
        $advertisements = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('exports.advertisements_pdf', [
            'advertisements' => $advertisements,
            'user' => auth()->user(),
            'is_department_user' => (auth()->user()->department_id && is_null(auth()->user()->office_id)),
            'is_office_user' => (auth()->user()->department_id && !is_null(auth()->user()->office_id)),
            'title' => 'New Advertisements'
        ]);

        return $pdf->download('new_ads_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Inprogress Ads to Excel
     */
    public function exportExcelInprogress(Request $request)
    {
        $query = $this->buildInprogressQuery($request);
        $advertisements = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(
            new AdvertisementsExport($advertisements),
            'inprogress_ads_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export Inprogress Ads to PDF
     */
    public function exportPDFInprogress(Request $request)
    {
        $query = $this->buildInprogressQuery($request);
        $advertisements = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('exports.advertisements_pdf', [
            'advertisements' => $advertisements,
            'user' => auth()->user(),
            'is_department_user' => (auth()->user()->department_id && is_null(auth()->user()->office_id)),
            'is_office_user' => (auth()->user()->department_id && !is_null(auth()->user()->office_id)),
            'title' => 'Inprogress Advertisements'
        ]);

        return $pdf->download('inprogress_ads_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Approved Ads to Excel
     */
    public function exportExcelApproved(Request $request)
    {
        $query = $this->buildApprovedQuery($request);
        $advertisements = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(
            new AdvertisementsExport($advertisements),
            'approved_ads_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export Approved Ads to PDF
     */
    public function exportPDFApproved(Request $request)
    {
        $query = $this->buildApprovedQuery($request);
        $advertisements = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('exports.advertisements_pdf', [
            'advertisements' => $advertisements,
            'user' => auth()->user(),
            'is_department_user' => (auth()->user()->department_id && is_null(auth()->user()->office_id)),
            'is_office_user' => (auth()->user()->department_id && !is_null(auth()->user()->office_id)),
            'title' => 'Approved Advertisements'
        ]);

        return $pdf->download('approved_ads_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Published Ads to Excel
     */
    public function exportExcelPublished(Request $request)
    {
        $query = $this->buildPublishedQuery($request);
        $advertisements = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(
            new AdvertisementsExport($advertisements),
            'published_ads_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export Published Ads to PDF
     */
    public function exportPDFPublished(Request $request)
    {
        $query = $this->buildPublishedQuery($request);
        $advertisements = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('exports.advertisements_pdf', [
            'advertisements' => $advertisements,
            'user' => auth()->user(),
            'is_department_user' => (auth()->user()->department_id && is_null(auth()->user()->office_id)),
            'is_office_user' => (auth()->user()->department_id && !is_null(auth()->user()->office_id)),
            'title' => 'Published Advertisements'
        ]);

        return $pdf->download('published_ads_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcelArchived(Request $request)
    {
        $query = $this->buildArchivedQuery($request);
        $advertisements = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(
            new AdvertisementsExport($advertisements),
            'archived_ads_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportPDFArchived(Request $request)
    {
        $archivedAdsQuery = $this->buildArchivedQuery($request);
        $advertisements = $archivedAdsQuery->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('exports.advertisements_pdf', [
            'advertisements' => $advertisements,
            'user' => auth()->user(),
            'is_department_user' => (auth()->user()->department_id && is_null(auth()->user()->office_id)),
            'is_office_user' => (auth()->user()->department_id && !is_null(auth()->user()->office_id)),
            'title' => 'Archived Advertisements'
        ]);

        return $pdf->download('Archived_ads_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportPDFRejected(Request $request)
    {
        $advertisements = $this->buildRejectedQuery($request);
        $advertisements = $advertisements->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('exports.advertisements_pdf', [
            'advertisements' => $advertisements,
            'user' => auth()->user(),
            'is_department_user' => (auth()->user()->department_id && is_null(auth()->user()->office_id)),
            'is_office_user' => (auth()->user()->department_id && !is_null(auth()->user()->office_id)),
            'title' => 'Rejected Advertisements'
        ]);

        return $pdf->download('Rejected_ads_' . now()->format('Y-m-d') . '.pdf');
    }



    public function exportExcelRejected(Request $request)
    {
        $advertisements = $this->buildRejectedQuery($request);
        $advertisements = $advertisements->orderBy('created_at', 'desc')->get();

        return Excel::download(
            new AdvertisementsExport($advertisements),
            'rejected_ads_' . now()->format('Y-m-d') . '.xlsx'
        );
    }



    /**
     * Build query for New Ads (index)
     */
    private function buildIndexQuery(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;
        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));

        $new_status = Status::where('title', 'New')->value('id');
        $forwarded_status = Status::where('title', 'Forwarded')->value('id');
        $approved_status = Status::where('title', 'Approved')->value('id');
        $pending_department_status = Status::where('title', 'Pending Department Approval')->value('id');
        $rejected_department_status = Status::where('title', 'Rejected by Department')->value('id');
        $sent_back_status = Status::where('title', 'Sent Back to Office')->value('id');
        $draft_status = Status::where('title', 'Draft')->value('id');

        $query = Advertisement::whereNull('archived_at');

        if ($user->hasRole('Super Admin')) {
            $query->where('status_id', 3);
        } elseif ($user->hasRole('Client Office')) {
            if ($is_office_user) {
                $query->where('office_id', $user->office_id)
                    ->where('status_id', 3)
                    ->orWhere('status_id', 14)
                    ->where('forwarded_to_role_id', 2)
                    ->where('forwarded_by_role_id', 3);
            } elseif ($is_department_user) {
                $query->where('office_id', null)
                    ->where(function ($q) {
                        $q->where(function ($qq) {
                            $qq->where('status_id', 3)
                                ->where('forwarded_by_role_id', 2)
                                ->where('forwarded_to_role_id', 3);
                        })->orWhere('status_id', 12);
                    });
            }
        } elseif ($user->hasRole('Diary Dispatch')) {
            $query->where('status_id', 3)
                ->where('forwarded_by_role_id', 9)
                ->where('forwarded_to_role_id', 3);
        } elseif ($user->hasRole('Superintendent')) {
            $query->where(function ($q) {
                $q->where('status_id', 3)
                    ->where('forwarded_by_role_id', 9)
                    ->where('forwarded_to_role_id', 3);
            })->orWhere(function ($q) {
                $q->where('status_id', 3)
                    ->where('forwarded_by_role_id', 2)
                    ->where('forwarded_to_role_id', 3);
            });
        } elseif ($user->hasRole('Deputy Director')) {
            $query->where('status_id', 4)
                ->where('forwarded_by_role_id', 3)
                ->where('forwarded_to_role_id', 11);
        } elseif ($user->hasRole('Director General')) {
            $query->where('status_id', 4)
                ->where('forwarded_by_role_id', 11)
                ->where('forwarded_to_role_id', 10);
        } elseif ($user->hasRole('Secretary')) {
            $query->where('status_id', 4)
                ->where('forwarded_by_role_id', 10)
                ->where('forwarded_to_role_id', 12);
        } elseif ($user->hasRole('Media')) {
            $query->where('status_id', 10)
                ->where('forwarded_to_role_id', 4)
                ->where('forwarded_by_role_id', 3)
                ->where(function ($q) use ($user) {
                    $q->where(function ($qq) use ($user) {
                        $qq->whereNotNull('adv_agency_id')
                            ->whereNull('publication')
                            ->where('adv_agency_id', $user->adv_agency_id);
                    })->orWhere(function ($qq) use ($user) {
                        $qq->whereNull('adv_agency_id')
                            ->where(function ($qqq) use ($user) {
                                $qqq->whereNull('sec_NP_log')
                                    ->whereNotNull('dg_NP_log')
                                    ->whereJsonContains('dg_NP_log', (string)$user->newspaper_id)
                                    ->orWhere(function ($qqqq) use ($user) {
                                        $qqqq->whereNull('dg_NP_log')
                                            ->whereNotNull('dd_NP_log')
                                            ->whereJsonContains('dd_NP_log', (string)$user->newspaper_id);
                                    })->orWhere(function ($qqqq) use ($user) {
                                        $qqqq->whereNotNull('sec_NP_log')
                                            ->whereJsonContains('sec_NP_log', (string)$user->newspaper_id);
                                    });
                            });
                    });
                })
                ->whereDoesntHave('newspapers', function ($q) use ($user) {
                    $q->where('newspaper_id', $user->newspaper_id);
                });
        } else {
            $query->whereRaw('1 = 0');
        }

        $this->applyFilters($query, $request);
        return $query;
    }

    /**
     * Build query for Inprogress Ads
     */
    private function buildInprogressQuery(Request $request)
    {
        $user = auth()->user();
        $query = Advertisement::query();

        if ($user->hasRole('Super Admin')) {
            $query->where('status_id', 4);
        } elseif ($user->hasRole('Client Office')) {
            $query->where('status_id', 4)
                ->where('department_id', $user->department_id);
        } elseif ($user->hasRole('Diary Dispatch')) {
            $query->where('status_id', 4);
        } elseif ($user->hasRole('Superintendent')) {
            $query->where('status_id', 4)
                ->where('forwarded_by_role_id', 3)
                ->where('forwarded_to_role_id', 11)
                ->orWhere(function ($q) {
                    $q->where('status_id', 4)
                        ->where('forwarded_by_role_id', 11)
                        ->where('forwarded_to_role_id', 10);
                });
        } elseif ($user->hasRole('Deputy Director')) {
            $query->where('status_id', 4)
                ->where('forwarded_by_role_id', 11)
                ->where('forwarded_to_role_id', 10);
        } elseif ($user->hasRole('Director General')) {
            $query->where('status_id', 10)
                ->where('forwarded_by_role_id', 9)
                ->where('forwarded_to_role_id', 4)
                ->orWhere(function ($q) {
                    $q->where('status_id', 4)
                        ->where('forwarded_by_role_id', 10)
                        ->where('forwarded_to_role_id', 12);
                });
        } else {
            $query->whereRaw('1 = 0');
        }

        $this->applyFilters($query, $request);
        return $query;
    }

    /**
     * Build query for Approved Ads
     */
    private function buildApprovedQuery(Request $request)
    {
        $user = auth()->user();
        $query = Advertisement::query()
            ->where('status_id', 10)
            ->where('forwarded_by_role_id', 10)
            ->where('forwarded_to_role_id', 3)
            ->whereNull('archived_at')
            ->orWhere(function ($q) {
                $q->where('status_id', 10)
                    ->where('forwarded_by_role_id', 11)
                    ->where('forwarded_to_role_id', 3)
                    ->whereNull('archived_at')
                    ->orWhere(function ($qq) {
                        $qq->where('status_id', 10)
                            ->where('forwarded_by_role_id', 3)
                            ->where('forwarded_to_role_id', 4)
                            ->whereNull('archived_at')
                            ->orWhere(function ($qqq) {
                                $qqq->where('status_id', 10)
                                    ->where('forwarded_by_role_id', 12)
                                    ->where('forwarded_to_role_id', 3)
                                    ->whereNull('archived_at');
                            });
                    });
            });

        $this->applyFilters($query, $request);
        return $query;
    }

    /**
     * Build query for Published Ads
     */
    private function buildPublishedQuery(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole(['Director General', 'Deputy Director', 'Superintendent', 'Diary Dispatch', 'Super Admin'])) {
            $query = Advertisement::whereHas('newspapers', function ($q) {
                $q->where('is_published', 1)->whereNull('archived_at');
            });
        } elseif ($user->hasRole('Media')) {
            if ($user->adv_agency_id) {
                $query = Advertisement::where('adv_agency_id', $user->adv_agency_id)
                    ->whereHas('newspapers', function ($q) {
                        $q->where('is_published', 1);
                    });
            } elseif ($user->newspaper_id) {
                $query = Advertisement::whereHas('newspapers', function ($q) use ($user) {
                    $q->where('newspaper_id', $user->newspaper_id)
                        ->where('is_published', 1)
                        ->whereNull('agency_id');
                });
            } else {
                $query = Advertisement::whereRaw('1 = 0');
            }
        } else {
            $query = Advertisement::whereRaw('1 = 0');
        }

        $this->applyFilters($query, $request);
        return $query;
    }


    private function buildArchivedQuery(Request $request)
    {
        // Page Title
        $pageTitle = 'Archived Ads &#x2053; IAMS-IPR';

        // breadcrumbs
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Archived Ads', 'url' => null], // The current page (no URL)
        ];


        $user = auth()->user();
        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));


        $archivedAdsQuery = Advertisement::query()->whereNotNull('archived_at');

        // Apply search/filters
        $this->applyFilters($archivedAdsQuery, $request);

        return $archivedAdsQuery;
    }

    // Show Rejected Ads
    public function buildRejectedQuery(Request $request)
    {
        //Page title
        $pageTitle = 'Rejected Ads &#x2053; IAMS-IPR';

        // breadcrumbs
        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Rejected Ads', 'url' => null], // The current page (no URL)
        ];

        $user = auth()->user();
        $userId = $user->id;
        // Determine user type
        $is_department_user = ($user->department_id && is_null($user->office_id));
        $is_office_user = ($user->department_id && !is_null($user->office_id));

        //Get logged in user
        $user = auth()->user();
        $user_role = $user->roles->pluck('id');
        $user_role_id = $user_role->first();
        // dd($user_role_id);
        $ad_rejection_reasons = AdRejectionReason::all();

        $advertisements = Advertisement::query();
        if ($user->hasRole('Deputy Director')) {
            $advertisements->where('status_id', 7)
                ->where('forwarded_by_role_id', 10)
                ->where('forwarded_to_role_id', 11);
        } else {
            $advertisements->where('status_id', 7);
        }

        $this->applyFilters($advertisements, $request);

        return $advertisements;
    }
}
