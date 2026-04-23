<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\ClassifiedAdType;
use App\Models\PasswordResetRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class DashboardController extends Controller
{
    // Dashboard data for IPR
    private function ipr_dashboard($from = null, $to = null)
    {
        // $from = $request->from;
        // $to = $request->to;
        // Page title
        $pageTitle = 'Dashboard &#x2053; DG&#8211;IPR IAMS';
        // $user = auth()->user();
        // $role = $user->roles->first();
        // $userIds  = $role->users()->pluck('id')->toArray();
        // Get Today's Date
        $today = Carbon::today()->format('jS F Y');
        // Use provided date range or default to current year
        if ($from && $to) {
            $startDate = Carbon::parse($from)->startOfDay();
            $endDate = Carbon::parse($to)->endOfDay();
        } else {
            $startDate = Carbon::now()->startOfYear(); // 2025-01-01 00:00:00
            $endDate   = Carbon::now()->endOfYear();   // 2025-12-31 23:59:59
        }

        $yearStart = $startDate;
        $yearEnd = $endDate;
        $currentYear = Carbon::now()->year;

        $currentMonth = Carbon::today()->format('F');

        // New Ads
        $newCount = Advertisement::where('status_id', 3)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->count();

        $adsIds = Advertisement::where('status_id', 3)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->get()->pluck('id')->toArray();
        // dd($adsIds);
        // % for new Ads
        $yesterdayStart = Carbon::yesterday()->startOfDay();
        $yesterdayEnd   = Carbon::yesterday()->endOfDay();

        $yesterdayNewCount = Advertisement::where('status_id', 3)
            ->whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])
            ->count();

        $newChangePercent = $yesterdayNewCount > 0
            ? round((($newCount - $yesterdayNewCount) / $yesterdayNewCount) * 100, 2)
            : 0;

        // Approved Ads (excluding published and unpublished)
        // Approved ads (status 10) that are NOT published (no pivot record with is_published = 1)
        $approvedCount = Advertisement::where('status_id', 10)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->whereDoesntHave('newspapers', function ($query) use ($yearStart, $yearEnd) {
                $query->where('is_published', 1)
                    ->whereBetween('advertisement_newspaper.created_at', [$yearStart, $yearEnd]);
            })
            ->count();

        // Approved Ads (including published and unpublished)
        // $approvedCount = Advertisement::where('status_id', 10)
        //     ->whereBetween('created_at', [$yearStart, $yearEnd])
        //     ->count();


        // $publishedCount = DB::table('advertisement_newspaper')
        //     ->where('is_published', 1)
        //             //     ->whereBetween('created_at', [$yearStart, $yearEnd])
        //     ->count();

        // Published ads – distinct advertisements published in the date range
        $publishedCount = DB::table('advertisement_newspaper')
            ->where('is_published', 1)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->distinct()
            ->count('advertisement_id');
        // dd($publishedCount);



        $inprogressCount = Advertisement::where('status_id', 4)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->count();

        $rejectedCount = Advertisement::where('status_id', 7)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->count();

        // ---- Status Monthly Chart Data ----
        // Use the date range for chart data
        $chartStartDate = $startDate->copy();
        $chartEndDate = $endDate->copy();

        // Generate days array for the date range
        $days = [];



        $monthAbbr = $chartStartDate->format('M');
        if (!$from && !$to) {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth   = Carbon::now()->endOfMonth();
            while ($startOfMonth <= $endOfMonth) {
                $days[] = $startOfMonth->day;
                $startOfMonth->addDay();
            }

            $monthAbbr = Carbon::today()->format('M');
        } else {
            $currentDate = $chartStartDate->copy();
            while ($currentDate <= $chartEndDate) {
                $days[] = $currentDate->day;
                $currentDate->addDay();
            }
        }

        $daysFormatted = array_map(fn($day) => $day . ' ' . $monthAbbr, $days);
        // New Ads
        $newAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
            ->where('status_id', 3)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->groupBy('day')
            ->pluck('total', 'day');

        // Rejected
        $rejectedAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
            ->where('status_id', 7)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->groupBy('day')
            ->pluck('total', 'day');

        // Approved (excluding published and unpublished)
        $approvedAds = Advertisement::selectRaw('DAY(advertisements.created_at) as day, COUNT(DISTINCT advertisements.id) as total')
            ->leftJoin('advertisement_newspaper as an', function ($join) {
                $join->on('advertisements.id', '=', 'an.advertisement_id')
                    ->whereIn('an.is_published', [0, 1]); // only join rows we want to exclude
            })
            ->whereNull('an.advertisement_id') // keep only ads that had NO such pivot row
            ->where('advertisements.status_id', 10)
            ->whereBetween('advertisements.created_at', [$chartStartDate, $chartEndDate])
            ->groupBy('day')
            ->pluck('total', 'day');


        // Approved (including  published and unpublished)
        // $approvedAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
        //     ->where('status_id', 10)
        //     ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
        //     ->groupBy('day')
        //     ->pluck('total', 'day');
        $adsIds = Advertisement::whereBetween('created_at', [$chartStartDate, $chartEndDate])->pluck('id')->toArray();

        // Published from pivot
        $publishedAds = DB::table('advertisement_newspaper')
            ->whereIn('advertisement_id', $adsIds)
            ->where('is_published', 1)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->selectRaw('DAY(created_at) as day, COUNT(DISTINCT advertisement_id) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        // Unpublished from pivot
        // $unpublishedAds = DB::table('advertisement_newspaper')
        //     ->selectRaw('DAY(created_at) as day, COUNT(*) as total')
        //     ->where('is_published', 0)
        //     ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
        //     ->groupBy('day')
        //     ->pluck('total', 'day');

        // Rejected
        $inprogressAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
            ->where('status_id', 4)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->groupBy('day')
            ->pluck('total', 'day');

        // Format series for chart
        $chartData = [
            [
                'name' => 'New Ads',
                'data' => collect($days)->map(fn($day) => $newAds[$day] ?? 0)
            ],
            [
                'name' => 'Inprogress',
                'data' => collect($days)->map(fn($day) => $inprogressAds[$day] ?? 0)
            ],
            [
                'name' => 'Approved',
                'data' => collect($days)->map(fn($day) => $approvedAds[$day] ?? 0)
            ],
            [
                'name' => 'Published',
                'data' => collect($days)->map(fn($day) => $publishedAds[$day] ?? 0)
            ],
            [
                'name' => 'Rejected',
                'data' => collect($days)->map(fn($day) => $rejectedAds[$day] ?? 0)
            ]
        ];

        // ---- Office/Department Chart Data ----
        $ads = Advertisement::with('office')
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->get();

        // Group ads by office name and count
        $adsByOffice = $ads->groupBy(fn($ad) => optional($ad->office)->ddo_name ?? 'Unknown')
            ->map(fn($group) => $group->count());

        $officeNames = $adsByOffice->keys()->map(function ($name) {
            return \Str::limit($name, 20);
        });
        // dd($officeNames);   // Office names
        $officeData = $adsByOffice->values();        // Count of ads
        // Count of ads

        // ---- Ad Category Chart Data ----
        $types = ClassifiedAdType::withCount('advertisements')->get();

        $categoryLabels = $types->pluck('type'); // X-axis: type names
        $categoryCounts = $types->pluck('advertisements_count'); // Y-axis: ad count

        // ---- Monthly Chart Data ----
        $monthlyAds = Advertisement::selectRaw('MONTH(created_at) as month, COUNT(*) as total')

            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill months with 0 if no data
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = $monthlyAds[$m] ?? 0;
        }

        // ---- Weekly Submission Chart Data ----
        $daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

        $timeBlocks = [
            ['start' => '00:00:00', 'end' => '01:00:00', 'label' => '12 AM  1 AM'],
            ['start' => '01:00:00', 'end' => '02:00:00', 'label' => '1 AM  2 AM'],
            ['start' => '02:00:00', 'end' => '03:00:00', 'label' => '2 AM  3 AM'],
            ['start' => '03:00:00', 'end' => '04:00:00', 'label' => '3 AM  4 AM'],
            ['start' => '04:00:00', 'end' => '05:00:00', 'label' => '4 AM  5 AM'],
            ['start' => '05:00:00', 'end' => '06:00:00', 'label' => '5 AM  6 AM'],
            ['start' => '06:00:00', 'end' => '07:00:00', 'label' => '6 AM  7 AM'],
            ['start' => '07:00:00', 'end' => '08:00:00', 'label' => '7 AM  8 AM'],
            ['start' => '08:00:00', 'end' => '09:00:00', 'label' => '8 AM  9 AM'],
            ['start' => '09:00:00', 'end' => '10:00:00', 'label' => '9 AM  10 AM'],
            ['start' => '10:00:00', 'end' => '11:00:00', 'label' => '10 AM  11 AM'],
            ['start' => '11:00:00', 'end' => '12:00:00', 'label' => '11 AM  12 PM'],
            ['start' => '12:00:00', 'end' => '13:00:00', 'label' => '12 PM  1 PM'],
            ['start' => '13:00:00', 'end' => '14:00:00', 'label' => '1 PM  2 PM'],
            ['start' => '14:00:00', 'end' => '15:00:00', 'label' => '2 PM  3 PM'],
            ['start' => '15:00:00', 'end' => '16:00:00', 'label' => '3 PM  4 PM'],
            ['start' => '16:00:00', 'end' => '17:00:00', 'label' => '4 PM  5 PM'],
            ['start' => '17:00:00', 'end' => '18:00:00', 'label' => '5 PM  6 PM'],
            ['start' => '18:00:00', 'end' => '19:00:00', 'label' => '6 PM  7 PM'],
            ['start' => '19:00:00', 'end' => '20:00:00', 'label' => '7 PM  8 PM'],
            ['start' => '20:00:00', 'end' => '21:00:00', 'label' => '8 PM  9 PM'],
            ['start' => '21:00:00', 'end' => '22:00:00', 'label' => '9 PM  10 PM'],
            ['start' => '22:00:00', 'end' => '23:00:00', 'label' => '10 PM  11 PM'],
            ['start' => '23:00:00', 'end' => '23:59:59', 'label' => '11 PM  12 AM'],
        ];

        $weeklyData = [];
        $dayTotals = [];
        $blockTotals = array_fill(0, count($timeBlocks), 0);
        foreach ($daysOfWeek as $day) {
            $dayData = [];
            foreach ($timeBlocks as $index => $block) {
                $count = Advertisement::whereBetween('created_at', [
                    Carbon::parse("this week $day {$block['start']}"),
                    Carbon::parse("this week $day {$block['end']}")
                ])
                    ->count();
                $dayData[] = $count;
                $blockTotals[$index] += $count;
            }
            $dayTotals[$day] = array_sum($dayData);
            $weeklyData[] = [
                'name' => $day,
                'data' => $dayData
            ];
        }
        // Labels for the chart (user-friendly)
        $timeCategories = array_column($timeBlocks, 'label');

        // Useful insights
        $busiestDay = array_keys($dayTotals, max($dayTotals))[0] ?? 'N/A';
        $busiestBlockIndex = array_keys($blockTotals, max($blockTotals))[0] ?? null;
        $busiestBlock = $busiestBlockIndex !== null ? $timeCategories[$busiestBlockIndex] : 'N/A';

        // ---- Top 3 unresolved password reset requests ----
        $requests = PasswordResetRequest::with('user')
            ->where('resolved', false)
            ->latest()
            ->take(3)
            ->get();

        // Count of all unresolved requests
        $requestsCount = PasswordResetRequest::where('resolved', false)->count();

        //  PLA Balance / receive from client offices and added to pla account
        $totalRecevieAble = DB::table('bill_classified_ads')->sum('printed_total_bill');

        $paidAmountPla = DB::table('paid_amounts')->sum('amount');
        // ----Recivieable amount / total cost in billings
        $totalChequeAmount = DB::table('pla_acounts')
            ->sum('total_cheque_amount');
        // dd($totalEstimatedCost);
        $totalChequeAmountPla = $totalChequeAmount - $paidAmountPla;

        // Recivieable amount will be $totalRecivieable - $totalChequeAmountPla\
        $totalRecevieAbleCost = $totalRecevieAble - $totalChequeAmount;

        // net payable amount in pla
        $newspaperAmountPla = DB::table('pla_account_items')->sum('newspaper_amount');
        $agencyAmountPla = DB::table('pla_account_items')->sum('agency_commission_amount');
        $netPayAbleAmount = ($newspaperAmountPla + $agencyAmountPla) - $paidAmountPla;

        // Newspapers
        $newspaperBills = DB::table('bill_classified_ads')
            ->whereNotNull('newspaper_id')
            ->sum('estimated_cost');

        // dd($newspaperBills);
        // Newspaper count in Pla
        $newspaperCount = DB::table('pla_account_items')
            ->distinct()
            ->count('newspaper_id');

        // AdvAgency count in Pla
        $advAgencyCount = DB::table('pla_account_items')
            ->distinct()
            ->count('adv_agency_id');


        // dd($newspaperCount);

        // Agencies
        $agencyBills = DB::table('bill_classified_ads')
            ->whereNull('newspaper_id')
            ->sum('estimated_cost');

        // ============================
        // ----- Current month counts for the status-wise card -----
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd   = Carbon::now()->endOfMonth();

        $monthlyNewCount = Advertisement::where('status_id', 3)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        $monthlyInprogressCount = Advertisement::where('status_id', 4)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        $monthlyApprovedCount = Advertisement::where('status_id', 10)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->whereDoesntHave('newspapers', function ($q) use ($monthStart, $monthEnd) {
                $q->where('is_published', 1)
                    ->whereBetween('advertisement_newspaper.created_at', [$monthStart, $monthEnd]);
            })
            ->count();

        $monthlyPublishedCount = DB::table('advertisement_newspaper')
            ->where('is_published', 1)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->distinct()
            ->count('advertisement_id');

        $monthlyRejectedCount = Advertisement::where('status_id', 7)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        // Build an array for the chart
        $statusMonthlyData = [
            ['name' => 'New', 'y' => $monthlyNewCount],
            ['name' => 'Inprogress', 'y' => $monthlyInprogressCount],
            ['name' => 'Approved', 'y' => $monthlyApprovedCount],
            ['name' => 'Published', 'y' => $monthlyPublishedCount],
            ['name' => 'Rejected', 'y' => $monthlyRejectedCount],
        ];

        // ========================

        return [
            'pageTitle' => $pageTitle,
            'today' => $today,
            'currentMonth' => $currentMonth,
            'newCount' => $newCount,
            'newChangePercent' => $newChangePercent,
            'approvedCount' => $approvedCount,
            'publishedCount' => $publishedCount,
            'inprogressCount' => $inprogressCount,
            'rejectedCount' => $rejectedCount,
            'chartData' => $chartData,
            'categories' => $daysFormatted,
            'officeNames' => $officeNames,
            'officeData' => $officeData,
            'categoryLabels' => $categoryLabels,
            'categoryCounts' => $categoryCounts,
            'monthAbbr' => $monthAbbr,
            'monthlyAds' => $months,
            'weeklyData' => $weeklyData,
            'timeCategories' => $timeCategories,
            'busiestDay' => $busiestDay,
            'busiestBlock' => $busiestBlock,
            'requests' => $requests,
            'requestsCount' => $requestsCount,
            'totalChequeAmountPla' => $totalChequeAmountPla,
            'totalRecevieAbleCost' => $totalRecevieAbleCost,
            'newspaperBills' => $newspaperBills,
            'agencyBills'    => $agencyBills,
            'newspaperCount' => $newspaperCount,
            'advAgencyCount' =>  $advAgencyCount,
            'netPayAbleAmount' => $netPayAbleAmount,
            'statusMonthlyData' => $statusMonthlyData,
        ];
    }

    // Dashboard data for Media
    private function media_dashboard($from = null, $to = null)
    {
        // $from = $request->from;
        // $to = $request->to;
        // Page title
        $pageTitle = 'Dashboard &#x2053; DG&#8211;IPR IAMS';

        $user = auth()->user();
        $userId = $user->id;
        $userName = $user->name;

        // $role = $user->roles->first();
        // $userIds  = $role->users()->pluck('id')->toArray();
        // Get Today's Date
        $today = Carbon::today()->format('jS F Y');
        // Use provided date range or default to current year
        if ($from && $to) {
            $startDate = Carbon::parse($from)->startOfDay();
            $endDate = Carbon::parse($to)->endOfDay();
        } else {
            $startDate = Carbon::now()->startOfYear(); // 2025-01-01 00:00:00
            $endDate   = Carbon::now()->endOfYear();   // 2025-12-31 23:59:59
        }

        $yearStart = $startDate;
        $yearEnd = $endDate;
        $currentYear = Carbon::now()->year;

        $currentMonth = Carbon::today()->format('F');

        // New Ads
        $newCount = Advertisement::where('status_id', 3)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->count();
        // dd($newCount);

        $adsIds = Advertisement::where('status_id', 3)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->get()->pluck('id')->toArray();
        // dd($adsIds);
        // % for new Ads
        $yesterdayStart = Carbon::yesterday()->startOfDay();
        $yesterdayEnd   = Carbon::yesterday()->endOfDay();

        $yesterdayNewCount = Advertisement::where('status_id', 3)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])
            ->count();

        $newChangePercent = $yesterdayNewCount > 0
            ? round((($newCount - $yesterdayNewCount) / $yesterdayNewCount) * 100, 2)
            : 0;

        // Approved Ads (excluding published and unpublished)
        // $approvedCount = Advertisement::where('status_id', 10)
        //     ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
        //     ->whereNotIn('id', function ($query) {
        //         $query->select('advertisement_id')
        //             ->from('advertisement_newspaper'); // exclude all ads that exist in pivot
        //     })
        //     ->count();

        // Approved Ads (including published and unpublished)
        $approvedCount = Advertisement::where('status_id', 10)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->count();


        // $publishedCount = DB::table('advertisement_newspaper')
        //     ->where('is_published', 1)
        //             //     ->whereBetween('created_at', [$yearStart, $yearEnd])
        //     ->count();

        $publishedCount = DB::table('advertisement_newspaper')
            // ->where('user_id', $userId)
            ->whereIn('advertisement_id', $adsIds)
            ->where('is_published', 1)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->distinct('advertisement_id')
            ->count('advertisement_id');
        // dd($publishedCount);

        $inprogressCount = Advertisement::where('status_id', 4)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->count();

        $rejectedCount = Advertisement::where('status_id', 7)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->count();

        // ---- Status Monthly Chart Data ----
        // Use the date range for chart data
        $chartStartDate = $startDate->copy();
        $chartEndDate = $endDate->copy();

        // Generate days array for the date range
        $days = [];



        $monthAbbr = $chartStartDate->format('M');
        if (!$from && !$to) {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth   = Carbon::now()->endOfMonth();
            while ($startOfMonth <= $endOfMonth) {
                $days[] = $startOfMonth->day;
                $startOfMonth->addDay();
            }

            $monthAbbr = Carbon::today()->format('M');
        } else {
            $currentDate = $chartStartDate->copy();
            while ($currentDate <= $chartEndDate) {
                $days[] = $currentDate->day;
                $currentDate->addDay();
            }
        }

        $daysFormatted = array_map(fn($day) => $day . ' ' . $monthAbbr, $days);
        // New Ads
        $newAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
            ->where('status_id', 3)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->groupBy('day')
            ->pluck('total', 'day');

        // Rejected
        $rejectedAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
            ->where('status_id', 7)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->groupBy('day')
            ->pluck('total', 'day');

        // Approved (excluding published and unpublished)
        $approvedAds = Advertisement::selectRaw('DAY(advertisements.created_at) as day, COUNT(DISTINCT advertisements.id) as total')
            ->leftJoin('advertisement_newspaper as an', function ($join) {
                $join->on('advertisements.id', '=', 'an.advertisement_id')

                    ->whereIn('an.is_published', [0, 1]); // only join rows we want to exclude
            })
            ->whereNull('an.advertisement_id') // keep only ads that had NO such pivot row
            ->where('advertisements.status_id', 10)
            ->whereBetween('advertisements.created_at', [$chartStartDate, $chartEndDate])
            ->where('user_id', $userId)
            ->groupBy('day')
            ->pluck('total', 'day');


        // Approved (including  published and unpublished)
        // $approvedAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
        //     ->where('status_id', 10)
        //     ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
        //     ->groupBy('day')
        //     ->pluck('total', 'day');

        // Published from pivot
        $publishedAds = DB::table('advertisement_newspaper')
            ->whereIn('advertisement_id', $adsIds)
            ->selectRaw('DAY(created_at) as day, COUNT(*) as total')
            ->where('is_published', 1)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->groupBy('day')
            ->pluck('total', 'day');

        // Unpublished from pivot
        // $unpublishedAds = DB::table('advertisement_newspaper')
        //     ->selectRaw('DAY(created_at) as day, COUNT(*) as total')
        //     ->where('is_published', 0)
        //     ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
        //     ->groupBy('day')
        //     ->pluck('total', 'day');

        // Rejected
        $inprogressAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
            ->where('status_id', 4)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->groupBy('day')
            ->pluck('total', 'day');

        // Format series for chart
        $chartData = [
            [
                'name' => 'New Ads',
                'data' => collect($days)->map(fn($day) => $newAds[$day] ?? 0)
            ],
            [
                'name' => 'Inprogress',
                'data' => collect($days)->map(fn($day) => $inprogressAds[$day] ?? 0)
            ],
            [
                'name' => 'Approved',
                'data' => collect($days)->map(fn($day) => $approvedAds[$day] ?? 0)
            ],
            [
                'name' => 'Published',
                'data' => collect($days)->map(fn($day) => $publishedAds[$day] ?? 0)
            ],
            [
                'name' => 'Rejected',
                'data' => collect($days)->map(fn($day) => $rejectedAds[$day] ?? 0)
            ]
        ];

        // ---- Office/Department Chart Data ----
        $ads = Advertisement::with('office')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->get();

        // Group ads by office name and count
        $adsByOffice = $ads->groupBy(fn($ad) => optional($ad->office)->ddo_name ?? 'Unknown')
            ->map(fn($group) => $group->count());

        $officeNames = $adsByOffice->keys()->map(function ($name) {
            return Str::limit($name, 18);
        });
        // Office names
        $officeData = $adsByOffice->values();        // Count of ads

        // ---- Ad Category Chart Data ----
        $types = ClassifiedAdType::withCount(['advertisements' => function ($q) use ($userId) {
            $q->where('user_id', $userId);
        }])->get();

        $categoryLabels = $types->pluck('type'); // X-axis: type names
        $categoryCounts = $types->pluck('advertisements_count'); // Y-axis: ad count

        // ---- Monthly Chart Data ----
        $monthlyAds = Advertisement::selectRaw('MONTH(created_at) as month, COUNT(*) as total')

            ->whereYear('created_at', $currentYear)
            ->where('user_id', $userId)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill months with 0 if no data
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = $monthlyAds[$m] ?? 0;
        }

        // ---- Weekly Submission Chart Data ----
        $daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

        $timeBlocks = [
            ['start' => '00:00:00', 'end' => '01:00:00', 'label' => '12 AM  1 AM'],
            ['start' => '01:00:00', 'end' => '02:00:00', 'label' => '1 AM  2 AM'],
            ['start' => '02:00:00', 'end' => '03:00:00', 'label' => '2 AM  3 AM'],
            ['start' => '03:00:00', 'end' => '04:00:00', 'label' => '3 AM  4 AM'],
            ['start' => '04:00:00', 'end' => '05:00:00', 'label' => '4 AM  5 AM'],
            ['start' => '05:00:00', 'end' => '06:00:00', 'label' => '5 AM  6 AM'],
            ['start' => '06:00:00', 'end' => '07:00:00', 'label' => '6 AM  7 AM'],
            ['start' => '07:00:00', 'end' => '08:00:00', 'label' => '7 AM  8 AM'],
            ['start' => '08:00:00', 'end' => '09:00:00', 'label' => '8 AM  9 AM'],
            ['start' => '09:00:00', 'end' => '10:00:00', 'label' => '9 AM  10 AM'],
            ['start' => '10:00:00', 'end' => '11:00:00', 'label' => '10 AM  11 AM'],
            ['start' => '11:00:00', 'end' => '12:00:00', 'label' => '11 AM  12 PM'],
            ['start' => '12:00:00', 'end' => '13:00:00', 'label' => '12 PM  1 PM'],
            ['start' => '13:00:00', 'end' => '14:00:00', 'label' => '1 PM  2 PM'],
            ['start' => '14:00:00', 'end' => '15:00:00', 'label' => '2 PM  3 PM'],
            ['start' => '15:00:00', 'end' => '16:00:00', 'label' => '3 PM  4 PM'],
            ['start' => '16:00:00', 'end' => '17:00:00', 'label' => '4 PM  5 PM'],
            ['start' => '17:00:00', 'end' => '18:00:00', 'label' => '5 PM  6 PM'],
            ['start' => '18:00:00', 'end' => '19:00:00', 'label' => '6 PM  7 PM'],
            ['start' => '19:00:00', 'end' => '20:00:00', 'label' => '7 PM  8 PM'],
            ['start' => '20:00:00', 'end' => '21:00:00', 'label' => '8 PM  9 PM'],
            ['start' => '21:00:00', 'end' => '22:00:00', 'label' => '9 PM  10 PM'],
            ['start' => '22:00:00', 'end' => '23:00:00', 'label' => '10 PM  11 PM'],
            ['start' => '23:00:00', 'end' => '23:59:59', 'label' => '11 PM  12 AM'],
        ];

        $weeklyData = [];
        $dayTotals = [];
        $blockTotals = array_fill(0, count($timeBlocks), 0);
        foreach ($daysOfWeek as $day) {
            $dayData = [];
            foreach ($timeBlocks as $index => $block) {
                $count = Advertisement::where('user_id', $userId)->whereBetween('created_at', [
                    Carbon::parse("this week $day {$block['start']}"),
                    Carbon::parse("this week $day {$block['end']}")
                ])
                    ->count();
                $dayData[] = $count;
                $blockTotals[$index] += $count;
            }
            $dayTotals[$day] = array_sum($dayData);
            $weeklyData[] = [
                'name' => $day,
                'data' => $dayData
            ];
        }
        // Labels for the chart (user-friendly)
        $timeCategories = array_column($timeBlocks, 'label');

        // Useful insights
        $busiestDay = array_keys($dayTotals, max($dayTotals))[0] ?? 'N/A';
        $busiestBlockIndex = array_keys($blockTotals, max($blockTotals))[0] ?? null;
        $busiestBlock = $busiestBlockIndex !== null ? $timeCategories[$busiestBlockIndex] : 'N/A';

        // ---- Top 3 unresolved password reset requests ----
        $requests = PasswordResetRequest::with('user')
            ->where('resolved', false)
            ->latest()
            ->take(3)
            ->get();

        // Count of all unresolved requests
        $requestsCount = PasswordResetRequest::where('resolved', false)->count();

        // ---- PLA Balance total cost in billings ----
        $totalEstimatedCost = DB::table('bill_classified_ads')
            ->sum('printed_total_bill');
        // dd($totalEstimatedCost);

        //  PLA Balance / receive from client offices and added to pla account
        $totalRecevieAbleCost = DB::table('pla_acounts')->sum('total_cheque_amount');
        // Newspapers
        $newspaperBills = DB::table('bill_classified_ads')
            ->whereNotNull('newspaper_id')
            ->sum('estimated_cost');

        // Agencies
        $agencyBills = DB::table('bill_classified_ads')
            ->whereNull('newspaper_id')
            ->sum('estimated_cost');

        return [
            'pageTitle' => $pageTitle,
            'today' => $today,
            'currentMonth' => $currentMonth,
            'newCount' => $newCount,
            'newChangePercent' => $newChangePercent,
            'approvedCount' => $approvedCount,
            'publishedCount' => $publishedCount,
            'inprogressCount' => $inprogressCount,
            'rejectedCount' => $rejectedCount,
            'chartData' => $chartData,
            'categories' => $daysFormatted,
            'officeNames' => $officeNames,
            'officeData' => $officeData,
            'categoryLabels' => $categoryLabels,
            'categoryCounts' => $categoryCounts,
            'monthAbbr' => $monthAbbr,
            'monthlyAds' => $months,
            'weeklyData' => $weeklyData,
            'timeCategories' => $timeCategories,
            'busiestDay' => $busiestDay,
            'busiestBlock' => $busiestBlock,
            'requests' => $requests,
            'requestsCount' => $requestsCount,
            'totalEstimatedCost' => $totalEstimatedCost,
            'totalRecevieAbleCost' => $totalRecevieAbleCost,
            'newspaperBills' => $newspaperBills,
            'agencyBills'    => $agencyBills
        ];
    }

    // Dashboard data for Client Office
    // private function client_dashboard($from = null, $to = null)
    // {

    //     // Page title
    //     $pageTitle = 'Dashboard &#x2053; DG&#8211;IPR IAMS';

    //     $user = auth()->user();
    //     // $role = $user->roles->first();
    //     // $userIds  = [$user->id]; //tmp fix
    //     $userId = $user->id;
    //     // Get Today's Date
    //     $today = Carbon::today()->format('jS F Y');

    //     // Use provided date range or default to current year
    //     if ($from && $to) {
    //         $startDate = Carbon::parse($from)->startOfDay();
    //         $endDate = Carbon::parse($to)->endOfDay();
    //     } else {
    //         $startDate = Carbon::now()->startOfYear(); // 2025-01-01 00:00:00
    //         $endDate   = Carbon::now()->endOfYear();   // 2025-12-31 23:59:59
    //     }

    //     $yearStart = $startDate;
    //     $yearEnd   = $endDate;
    //     $currentYear = Carbon::now()->year;

    //     // $startOfWeek = Carbon::now()->startOfWeek();
    //     // $endOfWeek = Carbon::now()->endOfWeek();

    //     $currentMonth = Carbon::today()->format('F');
    //     // dd($currentMonth);

    //     $ads = Advertisement::where('status_id', 3)
    //         ->get()
    //         ->pluck('id')->toArray();
    //     // New Ads
    //     $newCount = Advertisement::where('status_id', 3)
    //         ->whereBetween('created_at', [$yearStart, $yearEnd])
    //         ->where('user_id', $userId)
    //         ->count();

    //     // % for new Ads
    //     $yesterdayStart = Carbon::yesterday()->startOfDay();
    //     $yesterdayEnd   = Carbon::yesterday()->endOfDay();

    //     $yesterdayNewCount = Advertisement::where('status_id', 3)
    //         ->where('user_id', $userId)
    //         ->whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])
    //         ->count();

    //     $newChangePercent = $yesterdayNewCount > 0
    //         ? round((($newCount - $yesterdayNewCount) / $yesterdayNewCount) * 100, 2)
    //         : 0;

    //     // Approved Ads (excluding published and unpublished)
    //     $approvedCount = Advertisement::where('status_id', 10)
    //         ->where('user_id', $userId)
    //         ->whereBetween('created_at', [$startDate, $endDate])
    //         ->whereNotIn('id', function ($query) {
    //             $query->select('advertisement_id')
    //                 ->from('advertisement_newspaper'); // exclude all ads that exist in pivot
    //         })
    //         ->count();


    //     $publishedCount = DB::table('advertisement_newspaper')
    //         // ->where('user_id', $userId)
    //         ->where('is_published', 1)
    //         ->whereIn('advertisement_id', $ads)
    //         ->whereBetween('created_at', [$yearStart, $yearEnd])
    //         ->count();

    //     $inprogressCount = Advertisement::where('status_id', 4)
    //         ->where('user_id', $userId)
    //         ->whereBetween('created_at', [$yearStart, $yearEnd])
    //         ->count();

    //     $rejectedCount = Advertisement::where('status_id', 7)
    //         ->where('user_id', $userId)
    //         ->whereBetween('created_at', [$yearStart, $yearEnd])
    //         ->count();

    //     // ---- Status Monthly Chart Data ----
    //     // Use the date range for chart data
    //     $chartStartDate = $startDate->copy();
    //     $chartEndDate = $endDate->copy();

    //     // Generate days array for the date range
    //     $days = [];
    //     $currentDate = $chartStartDate->copy();
    //     while ($currentDate <= $chartEndDate) {
    //         $days[] = $currentDate->day;
    //         $currentDate->addDay();
    //     }

    //     $monthAbbr = $chartStartDate->format('M');
    //     $daysFormatted = array_map(fn($day) => $day . ' ' . $monthAbbr, $days);
    //     // dd($daysFormatted);

    //     // New Ads
    //     $newAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
    //         ->where('status_id', 3)
    //         ->where('user_id', $userId)
    //         ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
    //         ->groupBy('day')
    //         ->pluck('total', 'day');

    //     // Rejected
    //     $rejectedAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
    //         ->where('status_id', 7)
    //         ->where('user_id', $userId)
    //         ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
    //         ->groupBy('day')
    //         ->pluck('total', 'day');

    //     // Approved (excluding published and unpublished)
    //     $approvedAds = Advertisement::selectRaw('DAY(advertisements.created_at) as day, COUNT(DISTINCT advertisements.id) as total')
    //         ->leftJoin('advertisement_newspaper as an', function ($join) {
    //             $join->on('advertisements.id', '=', 'an.advertisement_id')
    //                 ->whereIn('an.is_published', [0, 1]); // only join rows we want to exclude
    //         })
    //         ->whereNull('an.advertisement_id') // keep only ads that had NO such pivot row
    //         ->where('advertisements.status_id', 10)
    //         ->whereBetween('advertisements.created_at', [$chartStartDate, $chartEndDate])
    //         ->groupBy('day')
    //         ->pluck('total', 'day');

    //     // Published from pivot
    //     $publishedAds = DB::table('advertisement_newspaper')
    //         ->selectRaw('DAY(created_at) as day, COUNT(*) as total')
    //         ->where('is_published', 1)
    //         ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
    //         ->groupBy('day')
    //         ->pluck('total', 'day');

    //     // Unpublished from pivot
    //     // $unpublishedAds = DB::table('advertisement_newspaper')
    //     //     ->selectRaw('DAY(created_at) as day, COUNT(*) as total')
    //     //     ->where('is_published', 0)
    //     //     ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
    //     //     ->groupBy('day')
    //     //     ->pluck('total', 'day');

    //     // Rejected
    //     $inprogressAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
    //         ->where('status_id', 4)
    //         ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
    //         ->groupBy('day')
    //         ->pluck('total', 'day');

    //     // Format series for chart
    //     $chartData = [
    //         [
    //             'name' => 'New Ads',
    //             'data' => collect($days)->map(fn($day) => $newAds[$day] ?? 0)
    //         ],
    //         [
    //             'name' => 'Inprogress',
    //             'data' => collect($days)->map(fn($day) => $inprogressAds[$day] ?? 0)
    //         ],
    //         [
    //             'name' => 'Approved',
    //             'data' => collect($days)->map(fn($day) => $approvedAds[$day] ?? 0)
    //         ],
    //         [
    //             'name' => 'Published',
    //             'data' => collect($days)->map(fn($day) => $publishedAds[$day] ?? 0)
    //         ],
    //         [
    //             'name' => 'Rejected',
    //             'data' => collect($days)->map(fn($day) => $rejectedAds[$day] ?? 0)
    //         ]
    //     ];

    //     // ---- Office/Department Chart Data ----
    //     $ads = Advertisement::with('office')
    //         ->where('user_id', $userId)
    //         ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
    //         ->get();

    //     // Group ads by office name and count
    //     $adsByOffice = $ads->groupBy(fn($ad) => optional($ad->office)->ddo_name ?? 'Unknown')
    //         ->map(fn($group) => $group->count());

    //     $officeNames = $adsByOffice->keys();    // Office names
    //     $officeData = $adsByOffice->values();        // Count of ads

    //     // ---- Ad Category Chart Data ----
    //     // $types = ClassifiedAdType::withCount('advertisements')->get();
    //     $types = ClassifiedAdType::withCount(['advertisements' => function ($q) use ($userId) {
    //         $q->where('user_id', $userId);
    //     }])->get();

    //     $categoryLabels = $types->pluck('type'); // X-axis: type names
    //     $categoryCounts = $types->pluck('advertisements_count'); // Y-axis: ad count

    //     // ---- Monthly Chart Data ----
    //     $monthlyAds = Advertisement::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
    //         ->whereYear('created_at', $currentYear)
    //         ->groupBy('month')
    //         ->orderBy('month')
    //         ->pluck('total', 'month')
    //         ->toArray();

    //     // Fill months with 0 if no data
    //     $months = [];
    //     for ($m = 1; $m <= 12; $m++) {
    //         $months[] = $monthlyAds[$m] ?? 0;
    //     }

    //     // ---- Weekly Submission Chart Data ----
    //     $daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

    //     $timeBlocks = [
    //         ['start' => '00:00:00', 'end' => '01:00:00', 'label' => '12 AM – 1 AM'],
    //         ['start' => '01:00:00', 'end' => '02:00:00', 'label' => '1 AM – 2 AM'],
    //         ['start' => '02:00:00', 'end' => '03:00:00', 'label' => '2 AM – 3 AM'],
    //         ['start' => '03:00:00', 'end' => '04:00:00', 'label' => '3 AM – 4 AM'],
    //         ['start' => '04:00:00', 'end' => '05:00:00', 'label' => '4 AM – 5 AM'],
    //         ['start' => '05:00:00', 'end' => '06:00:00', 'label' => '5 AM – 6 AM'],
    //         ['start' => '06:00:00', 'end' => '07:00:00', 'label' => '6 AM – 7 AM'],
    //         ['start' => '07:00:00', 'end' => '08:00:00', 'label' => '7 AM – 8 AM'],
    //         ['start' => '08:00:00', 'end' => '09:00:00', 'label' => '8 AM – 9 AM'],
    //         ['start' => '09:00:00', 'end' => '10:00:00', 'label' => '9 AM – 10 AM'],
    //         ['start' => '10:00:00', 'end' => '11:00:00', 'label' => '10 AM – 11 AM'],
    //         ['start' => '11:00:00', 'end' => '12:00:00', 'label' => '11 AM – 12 PM'],
    //         ['start' => '12:00:00', 'end' => '13:00:00', 'label' => '12 PM – 1 PM'],
    //         ['start' => '13:00:00', 'end' => '14:00:00', 'label' => '1 PM – 2 PM'],
    //         ['start' => '14:00:00', 'end' => '15:00:00', 'label' => '2 PM – 3 PM'],
    //         ['start' => '15:00:00', 'end' => '16:00:00', 'label' => '3 PM – 4 PM'],
    //         ['start' => '16:00:00', 'end' => '17:00:00', 'label' => '4 PM – 5 PM'],
    //         ['start' => '17:00:00', 'end' => '18:00:00', 'label' => '5 PM – 6 PM'],
    //         ['start' => '18:00:00', 'end' => '19:00:00', 'label' => '6 PM – 7 PM'],
    //         ['start' => '19:00:00', 'end' => '20:00:00', 'label' => '7 PM – 8 PM'],
    //         ['start' => '20:00:00', 'end' => '21:00:00', 'label' => '8 PM – 9 PM'],
    //         ['start' => '21:00:00', 'end' => '22:00:00', 'label' => '9 PM – 10 PM'],
    //         ['start' => '22:00:00', 'end' => '23:00:00', 'label' => '10 PM – 11 PM'],
    //         ['start' => '23:00:00', 'end' => '23:59:59', 'label' => '11 PM – 12 AM'],
    //     ];

    //     $weeklyData = [];
    //     $dayTotals = [];
    //     $blockTotals = array_fill(0, count($timeBlocks), 0);

    //     foreach ($daysOfWeek as $day) {
    //         $dayData = [];
    //         foreach ($timeBlocks as $index => $block) {
    //             $count = Advertisement::whereBetween('created_at', [
    //                 Carbon::parse("this week $day {$block['start']}"),
    //                 Carbon::parse("this week $day {$block['end']}")
    //             ])
    //                 ->count();
    //             $dayData[] = $count;
    //             $blockTotals[$index] += $count;
    //         }
    //         $dayTotals[$day] = array_sum($dayData);
    //         $weeklyData[] = [
    //             'name' => $day,
    //             'data' => $dayData
    //         ];
    //     }

    //     // Labels for the chart (user-friendly)
    //     $timeCategories = array_column($timeBlocks, 'label');

    //     // Useful insights
    //     $busiestDay = array_keys($dayTotals, max($dayTotals))[0] ?? 'N/A';
    //     $busiestBlockIndex = array_keys($blockTotals, max($blockTotals))[0] ?? null;
    //     $busiestBlock = $busiestBlockIndex !== null ? $timeCategories[$busiestBlockIndex] : 'N/A';

    //     // Top 3 unresolved password reset requests
    //     $requests = PasswordResetRequest::with('user')
    //         ->where('resolved', false)
    //         ->latest()
    //         ->take(3)
    //         ->get();

    //     // Count of all unresolved requests
    //     $requestsCount = PasswordResetRequest::where('resolved', false)->count();

    //     return [
    //         'pageTitle' => $pageTitle,
    //         'today' => $today,
    //         'currentMonth' => $currentMonth,
    //         'newCount' => $newCount,
    //         'newChangePercent' => $newChangePercent,
    //         'approvedCount' => $approvedCount,
    //         'publishedCount' => $publishedCount,
    //         'inprogressCount' => $inprogressCount,
    //         'rejectedCount' => $rejectedCount,
    //         'chartData' => $chartData,
    //         'categories' => $daysFormatted,
    //         'officeNames' => $officeNames,
    //         'officeData' => $officeData,
    //         'categoryLabels' => $categoryLabels,
    //         'categoryCounts' => $categoryCounts,
    //         'monthAbbr' => $monthAbbr,
    //         'monthlyAds' => $months,
    //         'weeklyData' => $weeklyData,
    //         'timeCategories' => $timeCategories,
    //         'busiestDay' => $busiestDay,
    //         'busiestBlock' => $busiestBlock,
    //         'requests' => $requests,
    //         'requestsCount' => $requestsCount
    //     ];
    // }

    // Dashboard data for Client Office
    private function Client_dashboard($from = null, $to = null)
    {
        // $from = $request->from;
        // $to = $request->to;
        // Page title
        $pageTitle = 'Dashboard &#x2053; DG&#8211;IPR IAMS';

        $user = auth()->user();
        $userId = $user->id;
        $userName = $user->name;
        // Get Today's Date
        $today = Carbon::today()->format('jS F Y');
        // Use provided date range or default to current year
        if ($from && $to) {
            $startDate = Carbon::parse($from)->startOfDay();
            $endDate = Carbon::parse($to)->endOfDay();
        } else {
            $startDate = Carbon::now()->startOfYear(); // 2025-01-01 00:00:00
            $endDate   = Carbon::now()->endOfYear();   // 2025-12-31 23:59:59
        }

        $yearStart = $startDate;
        $yearEnd = $endDate;
        $currentYear = Carbon::now()->year;

        $currentMonth = Carbon::today()->format('F');

        // New Ads
        $newCount = Advertisement::where('status_id', 3)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->count();

        $adsIds = Advertisement::where('user_id', $userId)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->get()->pluck('id')->toArray();
        // dd($adsIds);
        // % for new Ads
        $yesterdayStart = Carbon::yesterday()->startOfDay();
        $yesterdayEnd   = Carbon::yesterday()->endOfDay();

        $yesterdayNewCount = Advertisement::where('status_id', 3)
            ->whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])
            ->count();

        $newChangePercent = $yesterdayNewCount > 0
            ? round((($newCount - $yesterdayNewCount) / $yesterdayNewCount) * 100, 2)
            : 0;

        // Approved Ads (excluding published and unpublished)
        // Approved ads (status 10) that are NOT published (no pivot record with is_published = 1)
        $approvedCount = Advertisement::where('status_id', 10)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->whereDoesntHave('newspapers', function ($query) use ($yearStart, $yearEnd) {
                $query->where('is_published', 1)
                    ->whereBetween('advertisement_newspaper.created_at', [$yearStart, $yearEnd]);
            })
            ->count();

        // Approved Ads (including published and unpublished)
        // $approvedCount = Advertisement::where('status_id', 10)
        //     ->whereBetween('created_at', [$yearStart, $yearEnd])
        //     ->count();


        // $publishedCount = DB::table('advertisement_newspaper')
        //     ->where('is_published', 1)
        //             //     ->whereBetween('created_at', [$yearStart, $yearEnd])
        //     ->count();

        // Published ads – distinct advertisements published in the date range
        $publishedCount = DB::table('advertisement_newspaper')
            ->whereIn('advertisement_id', $adsIds)
            ->where('is_published', 1)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->distinct()
            ->count('advertisement_id');
        // dd($publishedCount);



        $inprogressCount = Advertisement::where('status_id', 4)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->count();

        $rejectedCount = Advertisement::where('status_id', 7)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->count();

        // ---- Status Monthly Chart Data ----
        // Use the date range for chart data
        $chartStartDate = $startDate->copy();
        $chartEndDate = $endDate->copy();

        // Generate days array for the date range
        $days = [];



        $monthAbbr = $chartStartDate->format('M');
        if (!$from && !$to) {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth   = Carbon::now()->endOfMonth();
            while ($startOfMonth <= $endOfMonth) {
                $days[] = $startOfMonth->day;
                $startOfMonth->addDay();
            }

            $monthAbbr = Carbon::today()->format('M');
        } else {
            $currentDate = $chartStartDate->copy();
            while ($currentDate <= $chartEndDate) {
                $days[] = $currentDate->day;
                $currentDate->addDay();
            }
        }

        $daysFormatted = array_map(fn($day) => $day . ' ' . $monthAbbr, $days);
        // New Ads
        $newAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
            ->where('user_id', $userId)
            ->where('status_id', 3)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->groupBy('day')
            ->pluck('total', 'day');

        // Rejected
        $rejectedAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
            ->where('user_id', $userId)
            ->where('status_id', 7)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->groupBy('day')
            ->pluck('total', 'day');

        // Approved (excluding published and unpublished)
        $approvedAds = Advertisement::selectRaw('DAY(advertisements.created_at) as day, COUNT(DISTINCT advertisements.id) as total')
            ->where('user_id', $userId)
            ->leftJoin('advertisement_newspaper as an', function ($join) {
                $join->on('advertisements.id', '=', 'an.advertisement_id')
                    ->whereIn('an.is_published', [0, 1]); // only join rows we want to exclude
            })
            ->whereNull('an.advertisement_id') // keep only ads that had NO such pivot row
            ->where('advertisements.status_id', 10)
            ->whereBetween('advertisements.created_at', [$chartStartDate, $chartEndDate])
            ->groupBy('day')
            ->pluck('total', 'day');


        // Approved (including  published and unpublished)
        // $approvedAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
        //     ->where('status_id', 10)
        //     ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
        //     ->groupBy('day')
        //     ->pluck('total', 'day');
        $adsIds = Advertisement::where('user_id', $userId)->whereBetween('created_at', [$chartStartDate, $chartEndDate])->pluck('id')->toArray();

        // Published from pivot
        $publishedAds = DB::table('advertisement_newspaper')
            ->whereIn('advertisement_id', $adsIds)
            ->where('is_published', 1)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->selectRaw('DAY(created_at) as day, COUNT(DISTINCT advertisement_id) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        // Unpublished from pivot
        // $unpublishedAds = DB::table('advertisement_newspaper')
        //     ->selectRaw('DAY(created_at) as day, COUNT(*) as total')
        //     ->where('is_published', 0)
        //     ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
        //     ->groupBy('day')
        //     ->pluck('total', 'day');

        // Rejected
        $inprogressAds = Advertisement::selectRaw('DAY(created_at) as day, COUNT(*) as total')
            ->where('user_id', $userId)
            ->where('status_id', 4)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->groupBy('day')
            ->pluck('total', 'day');

        // Format series for chart
        $chartData = [
            [
                'name' => 'New Ads',
                'data' => collect($days)->map(fn($day) => $newAds[$day] ?? 0)
            ],
            [
                'name' => 'Inprogress',
                'data' => collect($days)->map(fn($day) => $inprogressAds[$day] ?? 0)
            ],
            [
                'name' => 'Approved',
                'data' => collect($days)->map(fn($day) => $approvedAds[$day] ?? 0)
            ],
            [
                'name' => 'Published',
                'data' => collect($days)->map(fn($day) => $publishedAds[$day] ?? 0)
            ],
            [
                'name' => 'Rejected',
                'data' => collect($days)->map(fn($day) => $rejectedAds[$day] ?? 0)
            ]
        ];

        // ---- Office/Department Chart Data ----
        $ads = Advertisement::with('office')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$chartStartDate, $chartEndDate])
            ->get();

        // Group ads by office name and count
        $adsByOffice = $ads->groupBy(fn($ad) => optional($ad->office)->ddo_name ?? 'Unknown')
            ->map(fn($group) => $group->count());

        $officeNames = $adsByOffice->keys()->map(function ($name) {
            return \Str::limit($name, 20);
        });
        // dd($officeNames);   // Office names
        $officeData = $adsByOffice->values();        // Count of ads

        // ---- Ad Category Chart Data ----
        $types = ClassifiedAdType::withCount(['advertisements' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->whereHas('advertisements', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        $categoryLabels = $types->pluck('type');
        $categoryCounts = $types->pluck('advertisements_count');

        // ---- Monthly Chart Data ----
        $monthlyAds = Advertisement::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->where('user_id', $userId)
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill months with 0 if no data
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = $monthlyAds[$m] ?? 0;
        }

        // ---- Weekly Submission Chart Data ----
        $daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

        $timeBlocks = [
            ['start' => '00:00:00', 'end' => '01:00:00', 'label' => '12 AM  1 AM'],
            ['start' => '01:00:00', 'end' => '02:00:00', 'label' => '1 AM  2 AM'],
            ['start' => '02:00:00', 'end' => '03:00:00', 'label' => '2 AM  3 AM'],
            ['start' => '03:00:00', 'end' => '04:00:00', 'label' => '3 AM  4 AM'],
            ['start' => '04:00:00', 'end' => '05:00:00', 'label' => '4 AM  5 AM'],
            ['start' => '05:00:00', 'end' => '06:00:00', 'label' => '5 AM  6 AM'],
            ['start' => '06:00:00', 'end' => '07:00:00', 'label' => '6 AM  7 AM'],
            ['start' => '07:00:00', 'end' => '08:00:00', 'label' => '7 AM  8 AM'],
            ['start' => '08:00:00', 'end' => '09:00:00', 'label' => '8 AM  9 AM'],
            ['start' => '09:00:00', 'end' => '10:00:00', 'label' => '9 AM  10 AM'],
            ['start' => '10:00:00', 'end' => '11:00:00', 'label' => '10 AM  11 AM'],
            ['start' => '11:00:00', 'end' => '12:00:00', 'label' => '11 AM  12 PM'],
            ['start' => '12:00:00', 'end' => '13:00:00', 'label' => '12 PM  1 PM'],
            ['start' => '13:00:00', 'end' => '14:00:00', 'label' => '1 PM  2 PM'],
            ['start' => '14:00:00', 'end' => '15:00:00', 'label' => '2 PM  3 PM'],
            ['start' => '15:00:00', 'end' => '16:00:00', 'label' => '3 PM  4 PM'],
            ['start' => '16:00:00', 'end' => '17:00:00', 'label' => '4 PM  5 PM'],
            ['start' => '17:00:00', 'end' => '18:00:00', 'label' => '5 PM  6 PM'],
            ['start' => '18:00:00', 'end' => '19:00:00', 'label' => '6 PM  7 PM'],
            ['start' => '19:00:00', 'end' => '20:00:00', 'label' => '7 PM  8 PM'],
            ['start' => '20:00:00', 'end' => '21:00:00', 'label' => '8 PM  9 PM'],
            ['start' => '21:00:00', 'end' => '22:00:00', 'label' => '9 PM  10 PM'],
            ['start' => '22:00:00', 'end' => '23:00:00', 'label' => '10 PM  11 PM'],
            ['start' => '23:00:00', 'end' => '23:59:59', 'label' => '11 PM  12 AM'],
        ];

        $weeklyData = [];
        $dayTotals = [];
        $blockTotals = array_fill(0, count($timeBlocks), 0);
        foreach ($daysOfWeek as $day) {
            $dayData = [];
            foreach ($timeBlocks as $index => $block) {
                $count = Advertisement::where('user_id', $userId)->whereBetween('created_at', [
                    Carbon::parse("this week $day {$block['start']}"),
                    Carbon::parse("this week $day {$block['end']}")
                ])
                    ->count();
                $dayData[] = $count;
                $blockTotals[$index] += $count;
            }
            $dayTotals[$day] = array_sum($dayData);
            $weeklyData[] = [
                'name' => $day,
                'data' => $dayData
            ];
        }
        // Labels for the chart (user-friendly)
        $timeCategories = array_column($timeBlocks, 'label');

        // Useful insights
        $busiestDay = array_keys($dayTotals, max($dayTotals))[0] ?? 'N/A';
        $busiestBlockIndex = array_keys($blockTotals, max($blockTotals))[0] ?? null;
        $busiestBlock = $busiestBlockIndex !== null ? $timeCategories[$busiestBlockIndex] : 'N/A';

        // ---- Top 3 unresolved password reset requests ----
        $requests = PasswordResetRequest::with('user')
            ->where('resolved', false)
            ->latest()
            ->take(3)
            ->get();

        // Count of all unresolved requests
        $requestsCount = PasswordResetRequest::where('resolved', false)->count();

        //  PLA Balance / receive from client offices and added to pla account
        $totalRecevieAble = DB::table('bill_classified_ads')->sum('printed_total_bill');

        // ----Recivieable amount / total cost in billings
        $totalChequeAmount = DB::table('pla_acounts')
            ->sum('total_cheque_amount');
        // dd($totalEstimatedCost);

        // Recivieable amount will be $totalRecivieable - $totalChequeAmountPla\
        $totalRecevieAbleCost = $totalRecevieAble - $totalChequeAmount;

        // net payable amount in pla
        $newspaperAmountPla = DB::table('pla_account_items')->sum('newspaper_amount');
        $agencyAmountPla = DB::table('pla_account_items')->sum('agency_commission_amount');
        $netPayAbleAmount = $newspaperAmountPla + $agencyAmountPla;

        // Newspapers
        $newspaperBills = DB::table('bill_classified_ads')
            ->whereNotNull('newspaper_id')
            ->sum('estimated_cost');

        // dd($newspaperBills);
        // Newspaper count in Pla
        $newspaperCount = DB::table('pla_account_items')
            ->distinct()
            ->count('newspaper_id');

        // AdvAgency count in Pla
        $advAgencyCount = DB::table('pla_account_items')
            ->distinct()
            ->count('adv_agency_id');


        // dd($newspaperCount);

        // Agencies
        $agencyBills = DB::table('bill_classified_ads')
            ->whereNull('newspaper_id')
            ->sum('estimated_cost');

        return [
            'pageTitle' => $pageTitle,
            'today' => $today,
            'currentMonth' => $currentMonth,
            'newCount' => $newCount,
            'newChangePercent' => $newChangePercent,
            'approvedCount' => $approvedCount,
            'publishedCount' => $publishedCount,
            'inprogressCount' => $inprogressCount,
            'rejectedCount' => $rejectedCount,
            'chartData' => $chartData,
            'categories' => $daysFormatted,
            'officeNames' => $officeNames,
            'officeData' => $officeData,
            'categoryLabels' => $categoryLabels,
            'categoryCounts' => $categoryCounts,
            'monthAbbr' => $monthAbbr,
            'monthlyAds' => $months,
            'weeklyData' => $weeklyData,
            'timeCategories' => $timeCategories,
            'busiestDay' => $busiestDay,
            'busiestBlock' => $busiestBlock,
            'requests' => $requests,
            'requestsCount' => $requestsCount,
            'totalChequeAmount' => $totalChequeAmount,
            'totalRecevieAbleCost' => $totalRecevieAbleCost,
            'newspaperBills' => $newspaperBills,
            'agencyBills'    => $agencyBills,
            'newspaperCount' => $newspaperCount,
            'advAgencyCount' =>  $advAgencyCount,
            'netPayAbleAmount' => $netPayAbleAmount,
        ];
    }

    // Display Dashboard based on the Logedin Role
    public function index(Request $request)
    {
        $user = auth()->user();
        $roles = $user->getRoleNames()->toArray(); // all roles
        // dd($roles);

        // Get date parameters from request
        $from = $request->get('from');
        $to = $request->get('to');

        if (in_array('Media', $roles)) {

            // Fetch data
            $data = $this->media_dashboard($from, $to);


            return view('dashboard.media-dashboard', $data);
        }

        if (in_array('Client Office', $roles)) {

            // Fetch data
            $data = $this->client_dashboard($from, $to);


            return view('dashboard.client-office-dashboard', $data);
        }

        if (array_intersect($roles, [
            'Super Admin',
            'Diary Dispatch',
            'Superintendent',
            'Deputy Director',
            'Director General',
            'Secretary'
        ])) {

            // Fetch data
            $data = $this->ipr_dashboard($from, $to);
     
            return view('dashboard.ipr-dashboard', $data);
        }

        abort(403, 'Unauthorized access to dashboard.');
    }

    // Year Trend
    public function getYearlyAdsTrend()
    {
        $user = auth()->user();
        $userId = $user->id;
        $startYear = 2016;
        $currentYear = Carbon::now()->year;
        $futureYears = 1; // number of years to show ahead (e.g., 2026)

        $endYear = $currentYear + $futureYears;

        // Get ads count grouped by year
        $adsData = DB::table('advertisements')
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('COUNT(*) as total'))
            // ->where('user_id', $userId)
            ->whereYear('created_at', '>=', $startYear)
            ->whereYear('created_at', '<=', $endYear)
            ->groupBy('year')
            ->pluck('total', 'year')
            ->toArray();

        // Prepare all years with zero fill
        $years = range($startYear, $endYear);
        $counts = [];
        foreach ($years as $year) {
            $counts[] = $adsData[$year] ?? 0;
        }

        return response()->json([
            'years' => $years,
            'counts' => $counts
        ]);
    }
}
