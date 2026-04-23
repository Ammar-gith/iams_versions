<?php

namespace App\Http\Controllers;

use Log;
use App\Models\Office;
use App\Models\Status;
use App\Models\Payment;
use App\Models\Newspaper;
use App\Models\NewspaperPartner;
use App\Models\Department;
use App\Models\PaidAmount;
use App\Models\TaxPayee;
use Illuminate\Http\Request;
use App\Models\AgencyPayment;
use App\Models\MediaBankDetail;
use App\Models\TreasuryChallan;
use App\Models\BillClassifiedAd;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PaymentController extends Controller
{
    /**
     * Split a newspaper's aggregated totals across its active partners (if any),
     * and return rows grouped by partner bank (media_bank_detail bank_name).
     *
     * IMPORTANT: We split amounts proportionally using share_percentage.
     * Last partner absorbs rounding so the sum stays exactly the same.
     *
     * @param  \Illuminate\Support\Collection  $newspaperPayments  Payment rows for ONE newspaper (may span many INFs)
     * @param  array<string> $keysToSplit
     * @return array<int, array{bank_name:string, bank_detail:?, partner_name:?string, share_percentage:?, totals:array}>
     */
    private function splitNewspaperTotalsByPartners($newspaperPayments, array $keysToSplit = ['payable', 'kpra_inf', 'kpra_dept', 'it_inf', 'it_dept']): array
    {
        $firstPayment = $newspaperPayments->first();
        $newspaper = $firstPayment?->newspaper;
        $newspaperId = (int) ($firstPayment?->newspaper_id ?? 0);

        $baseTotals = [
            'payable'   => (float) $newspaperPayments->sum('net_dues'),
            'kpra_inf'  => (float) $newspaperPayments->sum('kpra_inf'),
            'kpra_dept' => (float) $newspaperPayments->sum('kpra_department'),
            'it_inf'    => (float) $newspaperPayments->sum('it_inf'),
            'it_dept'   => (float) $newspaperPayments->sum('it_department'),
        ];

        $partners = $newspaperId
            ? NewspaperPartner::activeForNewspaper($newspaperId)->with('mediaBankDetail')->get()
            : collect();

        // No partners → single row using the newspaper's own bank_detail from payment
        if ($partners->isEmpty()) {
            $bankDetail = $firstPayment?->mediaBankDetail;
            $bankName = $bankDetail?->bank_name ?? 'Unknown Bank';

            return [[
                'bank_name' => $bankName,
                'bank_detail' => $bankDetail,
                'partner_name' => null,
                'share_percentage' => null,
                'totals' => $baseTotals,
            ]];
        }

        $rows = [];
        $count = $partners->count();
        $allocated = array_fill_keys($keysToSplit, 0.0);

        foreach ($partners as $idx => $partner) {
            $pct = (float) ($partner->share_percentage ?? 0);
            $factor = $pct / 100.0;

            $partnerTotals = [];
            foreach ($keysToSplit as $k) {
                $raw = (float) ($baseTotals[$k] ?? 0);
                if ($idx === $count - 1) {
                    // absorb rounding
                    $partnerTotals[$k] = round($raw - (float) $allocated[$k], 2);
                } else {
                    $piece = round($raw * $factor, 2);
                    $allocated[$k] += $piece;
                    $partnerTotals[$k] = $piece;
                }
            }

            $bankDetail = $partner->mediaBankDetail;
            $bankName = $bankDetail?->bank_name ?? 'Unknown Bank';

            $rows[] = [
                'bank_name' => $bankName,
                'bank_detail' => $bankDetail,
                'partner_name' => $partner->partner_name,
                'share_percentage' => $partner->share_percentage,
                'totals' => $partnerTotals,
                'newspaper' => $newspaper,
                'payments' => $newspaperPayments,
            ];
        }

        return $rows;
    }
    // Show All Ads
    // private function applyFilters($query, Request $request)
    // {
    //     if ($request->filled('search')) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('inf_number', 'LIKE', "%{$search}%")
    //                 ->orWhereHas('department', fn($dq) => $dq->where('name', 'LIKE', "%{$search}%"))
    //                 ->orWhereHas('office', fn($oq) => $oq->where('ddo_name', 'LIKE', "%{$search}%"))
    //                 ->orWhereHas('status', fn($sq) => $sq->where('title', 'LIKE', "%{$search}%"))
    //                 ->orWhere('created_at', 'LIKE', "%{$search}%")

    //                 ->orWhere('challan_number', 'LIKE', "%{$search}%")
    //                 ->orWhere('cheque_number', 'LIKE', "%{$search}%");
    //             // ->orWhere('status', 'LIKE', "%{$search}%");
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
    //     if ($request->filled('submission_from')) {
    //         $query->whereDate('created_at', '>=', $request->submission_from);
    //     }
    //     if ($request->filled('submission_to')) {
    //         $query->whereDate('created_at', '<=', $request->submission_to);
    //     }
    //     // if ($request->filled('publication_from')) {
    //     //     $query->whereDate('publish_on_or_before', '>=', $request->publication_from);
    //     // }
    //     // if ($request->filled('publication_to')) {
    //     //     $query->whereDate('publish_on_or_before', '<=', $request->publication_to);
    //     // }

    //     return $query;
    // }

    // code with scope filter apply
    public function index(Request $request)
    {
        $query = TreasuryChallan::with('payments')
            ->whereNotNull('approved_by')
            ->latest();

        // ────────── FILTERS ON treasury_challans TABLE (direct columns) ──────────

        // Global search that includes both treasury_challans and payments
        if ($request->filled('search')) {
            $search = $request->search;
            $numericSearch = preg_replace('/[^\d.-]/', '', $search);
            \Log::debug('Numeric search: ' . $numericSearch); // Should output "39332"
            $isNumeric = is_numeric($numericSearch);
            $query->where(function ($q) use ($search,  $numericSearch,  $isNumeric) {
                // Search on treasury_challans columns
                $q->where('treasury_challans.cheque_number', 'LIKE', "%{$search}%")
                    ->orWhere('treasury_challans.challan_number', 'LIKE', "%{$search}%")
                    ->orWhere('treasury_challans.memo_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('department', fn($dq) => $dq->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('office', fn($oq) => $oq->where('ddo_name', 'LIKE', "%{$search}%"))


                    // Search on related payments
                    ->orWhereHas('payments', function ($pq) use ($search) {
                        $pq->where('inf_number', 'LIKE', "%{$search}%")
                            ->orWhere('rt_number', 'LIKE', "%{$search}%")
                            ->orWhere('batch_no', 'LIKE', "%{$search}%")
                            ->orWhereHas('newspaper', fn($nq) => $nq->where('title', 'LIKE', "%{$search}%"))
                            ->orWhereHas('mediaBankDetail', fn($bq) => $bq->where('bank_name', 'LIKE', "%{$search}%"));
                    });
                // Add numeric search for total_amount if the search term looks like a number
                if ($isNumeric) {
                    $q->orWhere('treasury_challans.total_amount', $numericSearch);
                }
            });
        }

        // Exact cheque number
        if ($request->filled('cheque_number')) {
            $query->where('cheque_number', 'LIKE', "%{$request->cheque_number}%");
        }

        // Exact challan number
        if ($request->filled('challan_number')) {
            $query->where('challan_number', 'LIKE', "%{$request->challan_number}%");
        }

        // Department (direct column on treasury_challans)
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Office (direct column on treasury_challans)
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        // Status (direct column on treasury_challans)
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

        if ($request->filled('tr_challan_verification_date')) {

            $dates = explode(' to ', $request->tr_challan_verification_date);

            if (count($dates) == 2) {
                $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $to = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();

                $query->whereBetween('tr_challan_verification_date', [$from, $to]);
            }
        }

        // ────────── FILTERS ON payments TABLE (via whereHas) ──────────

        // Apply payment-specific filters (inf_number, newspaper_id, batch_no, submission_date)
        if ($request->anyFilled(['inf_number', 'newspaper_id', 'batch_no', 'submission_date'])) {
            $query->whereHas('payments', function ($q) use ($request) {
                $q->paymentFilter($request);
            });
        }

        // Paginate and preserve all filters
        $paymentNewspapers = $query->paginate(15)->appends($request->query());

        // Data for dropdowns
        $statuses    = Status::all();
        $departments = Department::all();
        $offices     = Office::all();

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Payments', 'url' => route('payment.newspapers.index')],
            ['label' => 'Ledgerization', 'url' => null],
        ];

        return view('payment-newspapers.index', [
            'paymentNewspapers' => $paymentNewspapers,
            'statuses'          => $statuses,
            'departments'       => $departments,
            'offices'           => $offices,
            'breadcrumbs'       => $breadcrumbs,
        ]);
    }

    private function buildLedgerQuery(Request $request)
    {
        $query = TreasuryChallan::with(['payments.newspaper', 'payments.mediaBankDetail', 'department', 'office', 'status'])
            ->whereNotNull('approved_by')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $numericSearch = preg_replace('/[^\d.-]/', '', $search);
            $isNumeric = is_numeric($numericSearch);
            $query->where(function ($q) use ($search, $numericSearch, $isNumeric) {
                $q->where('treasury_challans.cheque_number', 'LIKE', "%{$search}%")
                    ->orWhere('treasury_challans.challan_number', 'LIKE', "%{$search}%")
                    ->orWhere('treasury_challans.memo_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('department', fn($dq) => $dq->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('office', fn($oq) => $oq->where('ddo_name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('payments', function ($pq) use ($search) {
                        $pq->where('inf_number', 'LIKE', "%{$search}%")
                            ->orWhere('rt_number', 'LIKE', "%{$search}%")
                            ->orWhere('batch_no', 'LIKE', "%{$search}%")
                            ->orWhereHas('newspaper', fn($nq) => $nq->where('title', 'LIKE', "%{$search}%"))
                            ->orWhereHas('mediaBankDetail', fn($bq) => $bq->where('bank_name', 'LIKE', "%{$search}%"));
                    });
                if ($isNumeric) {
                    $q->orWhere('treasury_challans.total_amount', $numericSearch);
                }
            });
        }

        if ($request->filled('cheque_number')) {
            $query->where('cheque_number', 'LIKE', "%{$request->cheque_number}%");
        }
        if ($request->filled('challan_number')) {
            $query->where('challan_number', 'LIKE', "%{$request->challan_number}%");
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

        if ($request->filled('sbp_verification_date')) {
            $dates = explode(' to ', $request->sbp_verification_date);
            if (count($dates) === 2) {
                $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $to = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                $query->whereBetween('sbp_verification_date', [$from, $to]);
            }
        }

        if ($request->filled('tr_challan_verification_date')) {
            $dates = explode(' to ', $request->tr_challan_verification_date);
            if (count($dates) === 2) {
                $from = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
                $to = \Carbon\Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
                $query->whereBetween('tr_challan_verification_date', [$from, $to]);
            }
        }

        return $query;
    }

    public function exportLedgerExcel(Request $request)
    {
        $challans = $this->buildLedgerQuery($request)->get();

        $exportData = $challans->map(function ($c) {
            $infNumbers = collect($c->payments ?? [])->pluck('inf_number')->filter()->unique()->values()->implode(', ');
            $isParked = (collect($c->payments ?? [])->first() !== null);
            $uiStatus = $isParked ? 'Parked' : 'Unparked';
            return [
                'S. No.' => '',
                'INF No.' => $infNumbers,
                'Office' => $c->office?->ddo_name ?? '',
                'Cheque Number' => $c->cheque_number ?? '',
                'Cheque Amount' => $c->total_amount !== null ? number_format((float) $c->total_amount, 0) : '',
                'Treasury Verify Date' => $c->tr_challan_verification_date?->format('d M Y') ?? '',
                'Challan No.' => $c->challan_number ?? '',
                'Bank Verify Date' => $c->sbp_verification_date?->format('d M Y') ?? '',
                'Status' => $uiStatus,
            ];
        })->toArray();

        foreach ($exportData as $i => &$row) {
            $row['S. No.'] = $i + 1;
        }

        $filename = 'payment-ledger-' . date('Y-m-d') . '.xlsx';

        return Excel::download(new class($exportData) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private array $data) {}
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

    public function exportLedgerPdf(Request $request)
    {
        $challans = $this->buildLedgerQuery($request)->get();
        $pdf = Pdf::loadView('exports.payment_newspapers_ledger_pdf', [
            'challans' => $challans,
            'generatedAt' => now(),
        ])->setPaper('A4', 'landscape');

        return $pdf->download('payment-ledger-' . date('Y-m-d') . '.pdf');
    }

    public function exportBulkExcel(Request $request)
    {
        $query = Payment::unpaid()
            ->with(['newspaper', 'bill:id,inf_number,invoice_no,invoice_date'])
            ->paymentFilter($request);

        $rows = $query->orderBy('newspaper_id')->get();

        // Grouped export like blade (rows + subtotal per newspaper)
        $exportData = [];
        $sr = 1;
        foreach ($rows->groupBy('newspaper_id') as $newspaperId => $group) {
            $npTitle = $group->first()?->newspaper?->title ?? '';

            $totalAmount = (float) $group->sum('total_amount');
            $totalPayable = (float) $group->sum('net_dues');
            $totalGrossAmount = (float) $group->sum('gross_amount_100_or_85_percent');
            $totalKpraInf = (float) $group->sum('kpra_inf');
            $totalKpraDept = (float) $group->sum('kpra_dept');
            $totalItInf = (float) $group->sum('it_inf');
            $totalItDept = (float) $group->sum('it_dept');

            foreach ($group as $row) {
                $exportData[] = [
                    'S. No.' => $sr++,
                    'Newspaper' => $npTitle,
                    'INF Number' => $row->inf_number ?? '',
                    'RT Number' => $row->rt_number ?? '',
                    'Invoice Number' => $row->bill?->invoice_no ?? '',
                    'Invoice Date' => $row->bill?->invoice_date ? \Carbon\Carbon::parse($row->bill->invoice_date)->format('d M Y') : '',
                    'Through Media' => $row->payment_type === 'direct' ? 'Newspaper' : 'Agency',
                    'Grand Amount' => $row->total_amount !== null ? number_format((float) $row->total_amount, 0) : '',
                    'Pay(%)' => $row->payment_type === 'direct' ? '100' : '85',
                    'Gross Amount' => $row->gross_amount_100_or_85_percent !== null ? number_format((float) $row->gross_amount_100_or_85_percent, 0) : '',
                    'KPRA Tax By Inf' => $row->kpra_inf !== null ? number_format((float) $row->kpra_inf, 0) : '',
                    'KPRA Tax By Dept' => $row->kpra_dept !== null ? number_format((float) $row->kpra_dept, 0) : '',
                    'I.T Tax By Inf' => $row->it_inf !== null ? number_format((float) $row->it_inf, 0) : '',
                    'I.T Tax By Dept' => $row->it_dept !== null ? number_format((float) $row->it_dept, 0) : '',
                    'Payable Amount' => $row->net_dues !== null ? number_format((float) $row->net_dues, 0) : '',
                ];
            }

            // Subtotal row like blade footer
            $exportData[] = [
                'S. No.' => '',
                'Newspaper' => $npTitle,
                'INF Number' => '',
                'RT Number' => '',
                'Invoice Number' => 'Total Amounts :',
                'Invoice Date' => '',
                'Through Media' => '',
                'Grand Amount' => number_format($totalAmount, 0),
                'Pay(%)' => '',
                'Gross Amount' => number_format($totalGrossAmount, 0),
                'KPRA Tax By Inf' => number_format($totalKpraInf, 0),
                'KPRA Tax By Dept' => number_format($totalKpraDept, 0),
                'I.T Tax By Inf' => number_format($totalItInf, 0),
                'I.T Tax By Dept' => number_format($totalItDept, 0),
                'Payable Amount' => number_format($totalPayable, 0),
            ];
        }

        // AgencyWise section like blade (includes the same odd column order as UI)
        $agencyQuery = AgencyPayment::unpaid()->with(['agency', 'payments.bill:id,invoice_no,invoice_date']);
        if ($request->filled('search')) {
            $agencyQuery->whereHas('agency', fn($q) => $q->where('name', 'LIKE', "%{$request->search}%"));
        }
        $agencyPayments = $agencyQuery->orderBy('agency_id')->get()->groupBy('agency_id');

        if ($agencyPayments->isNotEmpty()) {
            $exportData[] = [
                'S. No.' => '',
                'Newspaper' => 'AgencyWise Total Amount',
                'INF Number' => '',
                'RT Number' => '',
                'Invoice Number' => '',
                'Invoice Date' => '',
                'Through Media' => '',
                'Grand Amount' => '',
                'Pay(%)' => '',
                'Gross Amount' => '',
                'KPRA Tax By Inf' => '',
                'KPRA Tax By Dept' => '',
                'I.T Tax By Inf' => '',
                'I.T Tax By Dept' => '',
                'Payable Amount' => '',
            ];
        }

        foreach ($agencyPayments as $agencyId => $agencyPaymentRecords) {
            foreach ($agencyPaymentRecords as $agencyPayment) {
                $infNumbers = $agencyPayment->payments
                    ->pluck('inf_number')
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $rtNumbers = $agencyPayment->payments
                    ->pluck('rt_number')
                    ->filter()
                    ->unique()
                    ->implode(', ');

                $firstPayment = $agencyPayment->payments->first();
                $exportData[] = [
                    'S. No.' => $sr++,
                    'Newspaper' => $agencyPayment->agency?->name ?? 'Unknown Agency',
                    'INF Number' => $infNumbers ?: 'N/A',
                    'RT Number' => $rtNumbers ?: 'N/A',
                    'Invoice Number' => $firstPayment?->bill?->invoice_no ?? '',
                    'Invoice Date' => $firstPayment?->bill?->invoice_date ? \Carbon\Carbon::parse($firstPayment->bill->invoice_date)->format('d M Y') : '',
                    'Through Media' => 'Agency',
                    'Grand Amount' => $agencyPayment->grand_amount !== null ? number_format((float) $agencyPayment->grand_amount, 0) : '',
                    'Pay(%)' => '15',
                    'Gross Amount' => $agencyPayment->gross_amount_15_percent !== null ? number_format((float) $agencyPayment->gross_amount_15_percent, 0) : '',
                    // Keep the same order as blade (KPRA columns show IT, and IT columns show KPRA)
                    'KPRA Tax By Inf' => $agencyPayment->it_inf !== null ? number_format((float) $agencyPayment->it_inf, 0) : '',
                    'KPRA Tax By Dept' => $agencyPayment->it_department !== null ? number_format((float) $agencyPayment->it_department, 0) : '',
                    'I.T Tax By Inf' => $agencyPayment->kpra_inf !== null ? number_format((float) $agencyPayment->kpra_inf, 0) : '',
                    'I.T Tax By Dept' => $agencyPayment->kpra_department !== null ? number_format((float) $agencyPayment->kpra_department, 0) : '',
                    'Payable Amount' => $agencyPayment->net_dues !== null ? number_format((float) $agencyPayment->net_dues, 0) : '',
                ];
            }
        }

        return Excel::download(new class($exportData) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private array $data) {}
            public function collection()
            {
                return collect($this->data);
            }
            public function headings(): array
            {
                return array_keys($this->data[0] ?? []);
            }
        }, 'payment-bulk-' . date('Y-m-d') . '.xlsx');
    }

    public function exportBulkPdf(Request $request)
    {
        $rows = Payment::unpaid()
            ->with(['newspaper', 'bill:id,inf_number,invoice_no,invoice_date'])
            ->paymentFilter($request)
            ->orderBy('newspaper_id')
            ->get();

        $agencyQuery = AgencyPayment::unpaid()->with(['agency', 'payments.bill:id,invoice_no,invoice_date']);
        if ($request->filled('search')) {
            $agencyQuery->whereHas('agency', fn($q) => $q->where('name', 'LIKE', "%{$request->search}%"));
        }
        $agencyPayments = $agencyQuery->orderBy('agency_id')->get()->groupBy('agency_id');

        $pdf = Pdf::loadView('exports.payment_newspapers_bulk_pdf', [
            'rows' => $rows,
            'agencyPayments' => $agencyPayments,
            'generatedAt' => now(),
        ])->setPaper('A4', 'landscape');

        return $pdf->download('payment-bulk-' . date('Y-m-d') . '.pdf');
    }

    public function exportSummaryExcel(Request $request)
    {
        $payments = Payment::unpaid()
            ->with(['newspaper', 'mediaBankDetail', 'bill:id,inf_number,invoice_no,invoice_date'])
            ->paymentFilter($request)
            ->get()
            ->groupBy('newspaper_id');

        $agencies = AgencyPayment::unpaid()
            ->with(['agency', 'mediaBankDetail'])
            ->orderBy('agency_id')
            ->get()
            ->groupBy('agency_id');

        $rows = [];
        $sr = 1;

        foreach ($payments as $newspaperId => $group) {
            $rows[] = [
                'S. No.' => $sr++,
                'Type' => 'Newspaper',
                'Name' => $group->first()?->newspaper?->title ?? '',
                'Account No.' => $group->first()?->mediaBankDetail?->account_number ?? '',
                'Bank Name' => $group->first()?->mediaBankDetail?->bank_name ?? '',
                'KPRA Tax (INF)' => number_format((float) $group->sum('kpra_inf'), 0),
                'KPRA Tax (Dept)' => number_format((float) $group->sum('kpra_department'), 0),
                'IT Tax (INF)' => number_format((float) $group->sum('it_inf'), 0),
                'IT Tax (Dept)' => number_format((float) $group->sum('it_department'), 0),
                'Payable Amount' => number_format((float) $group->sum('net_dues'), 0),
            ];
        }

        foreach ($agencies as $agencyId => $group) {
            $rows[] = [
                'S. No.' => $sr++,
                'Type' => 'Agency',
                'Name' => $group->first()?->agency?->name ?? '',
                'Account No.' => $group->first()?->mediaBankDetail?->account_number ?? '',
                'Bank Name' => $group->first()?->mediaBankDetail?->bank_name ?? '',
                'KPRA Tax (INF)' => number_format((float) $group->sum('kpra_inf'), 0),
                'KPRA Tax (Dept)' => number_format((float) $group->sum('kpra_department'), 0),
                'IT Tax (INF)' => number_format((float) $group->sum('it_inf'), 0),
                'IT Tax (Dept)' => number_format((float) $group->sum('it_department'), 0),
                'Payable Amount' => number_format((float) $group->sum('net_dues'), 0),
            ];
        }

        // KPRA & FBR rows (same as blade)
        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee = TaxPayee::where('type', 'fbr')->first();
        $kpraInf = (float) ($payments->flatten()->sum('kpra_inf') + $agencies->flatten()->sum('kpra_inf'));
        $kpraDept = (float) ($payments->flatten()->sum('kpra_department') + $agencies->flatten()->sum('kpra_department'));
        $fbrInf = (float) ($payments->flatten()->sum('it_inf') + $agencies->flatten()->sum('it_inf'));
        $fbrDept = (float) ($payments->flatten()->sum('it_department') + $agencies->flatten()->sum('it_department'));

        if (($kpraInf + $kpraDept) > 0) {
            $rows[] = [
                'S. No.' => $sr++,
                'Type' => 'KPRA',
                'Name' => $kpraPayee->description ?? 'KPRA',
                'Account No.' => $kpraPayee->account_number ?? '',
                'Bank Name' => $kpraPayee->bank_name ?? '',
                'KPRA Tax (INF)' => number_format($kpraInf, 0),
                'KPRA Tax (Dept)' => number_format($kpraDept, 0),
                'IT Tax (INF)' => '0',
                'IT Tax (Dept)' => '0',
                'Payable Amount' => number_format($kpraInf + $kpraDept, 0),
            ];
        }
        if (($fbrInf + $fbrDept) > 0) {
            $rows[] = [
                'S. No.' => $sr++,
                'Type' => 'FBR',
                'Name' => $fbrPayee->description ?? 'FBR',
                'Account No.' => $fbrPayee->account_number ?? '',
                'Bank Name' => $fbrPayee->bank_name ?? '',
                'KPRA Tax (INF)' => '0',
                'KPRA Tax (Dept)' => '0',
                'IT Tax (INF)' => number_format($fbrInf, 0),
                'IT Tax (Dept)' => number_format($fbrDept, 0),
                'Payable Amount' => number_format($fbrInf + $fbrDept, 0),
            ];
        }

        return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private array $rows) {}
            public function collection()
            {
                return collect($this->rows);
            }
            public function headings(): array
            {
                return array_keys($this->rows[0] ?? []);
            }
        }, 'payment-summary-' . date('Y-m-d') . '.xlsx');
    }

    public function exportSummaryPdf(Request $request)
    {
        $payments = Payment::unpaid()
            ->with(['newspaper', 'mediaBankDetail', 'bill:id,inf_number,invoice_no,invoice_date'])
            ->paymentFilter($request)
            ->get()
            ->groupBy('newspaper_id');

        $agencies = AgencyPayment::unpaid()
            ->with(['agency', 'mediaBankDetail'])
            ->orderBy('agency_id')
            ->get()
            ->groupBy('agency_id');

        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee = TaxPayee::where('type', 'fbr')->first();
        $kpraTotalInf  = (float) ($payments->flatten()->sum('kpra_inf') + $agencies->flatten()->sum('kpra_inf'));
        $kpraTotalDept = (float) ($payments->flatten()->sum('kpra_department') + $agencies->flatten()->sum('kpra_department'));
        $fbrTotalInf   = (float) ($payments->flatten()->sum('it_inf') + $agencies->flatten()->sum('it_inf'));
        $fbrTotalDept  = (float) ($payments->flatten()->sum('it_department') + $agencies->flatten()->sum('it_department'));

        $pdf = Pdf::loadView('exports.payment_newspapers_summary_pdf', [
            'payments' => $payments,
            'agencies' => $agencies,
            'kpraPayee' => $kpraPayee,
            'fbrPayee' => $fbrPayee,
            'kpraTotalInf' => $kpraTotalInf,
            'kpraTotalDept' => $kpraTotalDept,
            'fbrTotalInf' => $fbrTotalInf,
            'fbrTotalDept' => $fbrTotalDept,
            'generatedAt' => now(),
        ])->setPaper('A4', 'landscape');

        return $pdf->download('payment-summary-' . date('Y-m-d') . '.pdf');
    }

    public function exportBankWiseExcel(Request $request)
    {
        $payments = Payment::unpaid()
            ->with(['newspaper', 'mediaBankDetail'])
            ->paymentFilter($request)
            ->get();

        $agencyPayments = AgencyPayment::unpaid()
            ->with(['agency', 'mediaBankDetail'])
            ->get();

        // Build partner-split rows by bank name
        $partnerBankBuckets = [];
        foreach ($payments->groupBy('newspaper_id') as $newspaperId => $npPayments) {
            $splitRows = $this->splitNewspaperTotalsByPartners($npPayments);
            foreach ($splitRows as $r) {
                $bName = $r['bank_name'] ?? 'Unknown Bank';
                $partnerBankBuckets[$bName][] = $r;
            }
        }

        $agenciesByBank = $agencyPayments->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank');

        $bankNames = collect(array_keys($partnerBankBuckets))
            ->merge($agenciesByBank->keys())
            ->unique()
            ->sort()
            ->values();

        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee = TaxPayee::where('type', 'fbr')->first();
        $kpraTotal = (float) ($payments->sum('kpra_inf') + $payments->sum('kpra_department') + $agencyPayments->sum('kpra_inf') + $agencyPayments->sum('kpra_department'));
        $fbrTotal = (float) ($payments->sum('it_inf') + $payments->sum('it_department') + $agencyPayments->sum('it_inf') + $agencyPayments->sum('it_department'));

        $rows = [];
        $sr = 1;
        $overallPayable = 0.0;
        foreach ($bankNames as $bankName) {
            $bankPayable = 0.0;
            $bankSplitRows = collect($partnerBankBuckets[$bankName] ?? []);

            foreach ($bankSplitRows as $r) {
                $bankDetail = $r['bank_detail'] ?? null;
                $payable = (float) ($r['totals']['payable'] ?? 0);
                $bankPayable += $payable;
                $rows[] = [
                    'S. No.' => $sr++,
                    'Media Name' => $r['newspaper']?->title ?? ($bankDetail?->media_name ?? ''),
                    'Partner' => $r['partner_name'] ?? '—',
                    'Share %' => $r['share_percentage'] !== null ? $r['share_percentage'] : '—',
                    'Account No.' => $bankDetail?->account_number ?? '',
                    'Account Title' => $bankDetail?->account_title ?? '',
                    'Bank Name' => $bankName,
                    'Payable Amount' => number_format($payable, 0),
                ];
            }

            $bankAgencyPayments = $agenciesByBank->get($bankName, collect());
            $agencyGroups = $bankAgencyPayments->groupBy('agency_id');
            foreach ($agencyGroups as $agencyId => $arows) {
                $first = $arows->first();
                $apay = (float) $arows->sum('net_dues');
                $bankPayable += $apay;
                $rows[] = [
                    'S. No.' => $sr++,
                    'Media Name' => $first?->agency?->name ?? '',
                    'Partner' => '—',
                    'Share %' => '—',
                    'Account No.' => $first?->mediaBankDetail?->account_number ?? '',
                    'Account Title' => $first?->mediaBankDetail?->account_title ?? '',
                    'Bank Name' => $bankName,
                    'Payable Amount' => number_format($apay, 0),
                ];
            }

            // Bank total row like blade
            $overallPayable += $bankPayable;
            $rows[] = [
                'S. No.' => '',
                'Media Name' => 'Total for ' . $bankName . ':',
                'Partner' => '—',
                'Share %' => '—',
                'Account No.' => '',
                'Account Title' => '',
                'Bank Name' => $bankName,
                'Payable Amount' => number_format($bankPayable, 0),
            ];
        }

        // KPRA / FBR / GRAND TOTAL rows like blade footer
        if ($kpraTotal > 0) {
            $rows[] = [
                'S. No.' => '',
                'Media Name' => $kpraPayee->description ?? 'KPRA',
                'Partner' => '—',
                'Share %' => '—',
                'Account No.' => $kpraPayee->account_number ?? '',
                'Account Title' => $kpraPayee->account_title ?? '',
                'Bank Name' => $kpraPayee->bank_name ?? '',
                'Payable Amount' => number_format($kpraTotal, 0),
            ];
        }
        if ($fbrTotal > 0) {
            $rows[] = [
                'S. No.' => '',
                'Media Name' => $fbrPayee->description ?? 'FBR',
                'Partner' => '—',
                'Share %' => '—',
                'Account No.' => $fbrPayee->account_number ?? '',
                'Account Title' => $fbrPayee->account_title ?? '',
                'Bank Name' => $fbrPayee->bank_name ?? '',
                'Payable Amount' => number_format($fbrTotal, 0),
            ];
        }
        $grandTotal = (float) ($overallPayable + $kpraTotal + $fbrTotal);
        $rows[] = [
            'S. No.' => '',
            'Media Name' => 'GRAND TOTAL (All Banks):',
            'Partner' => '—',
            'Share %' => '—',
            'Account No.' => '',
            'Account Title' => '',
            'Bank Name' => '',
            'Payable Amount' => number_format($grandTotal, 0),
        ];

        return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private array $rows) {}
            public function collection()
            {
                return collect($this->rows);
            }
            public function headings(): array
            {
                return array_keys($this->rows[0] ?? []);
            }
        }, 'payment-bank-wise-' . date('Y-m-d') . '.xlsx');
    }

    public function exportBankWisePdf(Request $request)
    {
        // Reuse the same build approach as Excel to keep output consistent
        $payments = Payment::unpaid()->with(['newspaper', 'mediaBankDetail'])->paymentFilter($request)->get();
        $agencyPayments = AgencyPayment::unpaid()->with(['agency', 'mediaBankDetail'])->get();

        $partnerBankBuckets = [];
        foreach ($payments->groupBy('newspaper_id') as $newspaperId => $npPayments) {
            $splitRows = $this->splitNewspaperTotalsByPartners($npPayments);
            foreach ($splitRows as $r) {
                $bName = $r['bank_name'] ?? 'Unknown Bank';
                $partnerBankBuckets[$bName][] = $r;
            }
        }
        $agenciesByBank = $agencyPayments->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank');
        $bankNames = collect(array_keys($partnerBankBuckets))->merge($agenciesByBank->keys())->unique()->sort()->values();

        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee = TaxPayee::where('type', 'fbr')->first();
        $kpraTotal = (float) ($payments->sum('kpra_inf') + $payments->sum('kpra_department') + $agencyPayments->sum('kpra_inf') + $agencyPayments->sum('kpra_department'));
        $fbrTotal = (float) ($payments->sum('it_inf') + $payments->sum('it_department') + $agencyPayments->sum('it_inf') + $agencyPayments->sum('it_department'));

        // compute overall payable from buckets
        $overallPayable = 0.0;
        foreach ($bankNames as $bn) {
            $p = collect($partnerBankBuckets[$bn] ?? [])->sum(fn($r) => (float) ($r['totals']['payable'] ?? 0));
            $a = (float) (($agenciesByBank[$bn] ?? collect())->sum('net_dues'));
            $overallPayable += ($p + $a);
        }
        $grandTotal = (float) ($overallPayable + $kpraTotal + $fbrTotal);

        $pdf = Pdf::loadView('exports.payment_newspapers_bankwise_pdf', [
            'bankNames' => $bankNames,
            'partnerBankBuckets' => $partnerBankBuckets,
            'agenciesByBank' => $agenciesByBank,
            'kpraPayee' => $kpraPayee,
            'fbrPayee' => $fbrPayee,
            'kpraTotal' => $kpraTotal,
            'fbrTotal' => $fbrTotal,
            'grandTotal' => $grandTotal,
            'generatedAt' => now(),
        ])->setPaper('A4', 'landscape');

        return $pdf->download('payment-bank-wise-' . date('Y-m-d') . '.pdf');
    }

    public function exportPoListExcel(Request $request)
    {
        $payments = Payment::unpaid()->with(['mediaBankDetail'])->paymentFilter($request)->get();
        $agencyPayments = AgencyPayment::unpaid()->with(['mediaBankDetail'])->get();

        $paymentsByNewspaper = $payments->groupBy('newspaper_id');
        $partnerBankTotals = [];
        foreach ($paymentsByNewspaper as $newspaperId => $newspaperPayments) {
            $splitRows = $this->splitNewspaperTotalsByPartners($newspaperPayments, ['payable']);
            foreach ($splitRows as $r) {
                $bName = $r['bank_name'] ?? 'Unknown Bank';
                $partnerBankTotals[$bName] = ($partnerBankTotals[$bName] ?? 0) + (float) ($r['totals']['payable'] ?? 0);
            }
        }

        $agenciesByBank = $agencyPayments->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank');
        $bankNames = collect(array_keys($partnerBankTotals))->merge($agenciesByBank->keys())->unique()->sort()->values();

        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee = TaxPayee::where('type', 'fbr')->first();
        $kpraTotal = (float) ($payments->sum('kpra_inf') + $payments->sum('kpra_department') + $agencyPayments->sum('kpra_inf') + $agencyPayments->sum('kpra_department'));
        $fbrTotal = (float) ($payments->sum('it_inf') + $payments->sum('it_department') + $agencyPayments->sum('it_inf') + $agencyPayments->sum('it_department'));

        $rows = [];
        $sr = 1;
        $overallPayable = 0.0;
        foreach ($bankNames as $bankName) {
            $newspaperTotal = (float) ($partnerBankTotals[$bankName] ?? 0);
            $agencyTotal = (float) $agenciesByBank->get($bankName, collect())->sum('net_dues');
            $overallPayable += ($newspaperTotal + $agencyTotal);
            $rows[] = [
                'S. No.' => $sr++,
                'Bank Name' => 'Manager ' . $bankName,
                'Payable Amount' => number_format($newspaperTotal + $agencyTotal, 0),
            ];
        }

        if ($kpraTotal > 0) {
            $rows[] = [
                'S. No.' => $sr++,
                'Bank Name' => $kpraPayee->description ?? 'KPRA',
                'Payable Amount' => number_format($kpraTotal, 0),
            ];
        }
        if ($fbrTotal > 0) {
            $rows[] = [
                'S. No.' => $sr++,
                'Bank Name' => $fbrPayee->description ?? 'FBR',
                'Payable Amount' => number_format($fbrTotal, 0),
            ];
        }
        $rows[] = [
            'S. No.' => '',
            'Bank Name' => 'GRAND TOTAL (All Banks):',
            'Payable Amount' => number_format((float) ($overallPayable + $kpraTotal + $fbrTotal), 0),
        ];

        return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private array $rows) {}
            public function collection()
            {
                return collect($this->rows);
            }
            public function headings(): array
            {
                return array_keys($this->rows[0] ?? []);
            }
        }, 'po-list-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPoListPdf(Request $request)
    {
        $payments = Payment::unpaid()->with(['mediaBankDetail'])->paymentFilter($request)->get();
        $agencyPayments = AgencyPayment::unpaid()->with(['mediaBankDetail'])->get();

        $paymentsByNewspaper = $payments->groupBy('newspaper_id');
        $partnerBankTotals = [];
        foreach ($paymentsByNewspaper as $newspaperId => $newspaperPayments) {
            $splitRows = $this->splitNewspaperTotalsByPartners($newspaperPayments, ['payable']);
            foreach ($splitRows as $r) {
                $bName = $r['bank_name'] ?? 'Unknown Bank';
                $partnerBankTotals[$bName] = ($partnerBankTotals[$bName] ?? 0) + (float) ($r['totals']['payable'] ?? 0);
            }
        }
        $agenciesByBank = $agencyPayments->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank');
        $bankNames = collect(array_keys($partnerBankTotals))->merge($agenciesByBank->keys())->unique()->sort()->values();

        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee = TaxPayee::where('type', 'fbr')->first();
        $kpraTotal = (float) ($payments->sum('kpra_inf') + $payments->sum('kpra_department') + $agencyPayments->sum('kpra_inf') + $agencyPayments->sum('kpra_department'));
        $fbrTotal = (float) ($payments->sum('it_inf') + $payments->sum('it_department') + $agencyPayments->sum('it_inf') + $agencyPayments->sum('it_department'));

        $overallPayable = 0.0;
        foreach ($bankNames as $bn) {
            $overallPayable += (float) ($partnerBankTotals[$bn] ?? 0) + (float) (($agenciesByBank[$bn] ?? collect())->sum('net_dues'));
        }
        $grandTotal = (float) ($overallPayable + $kpraTotal + $fbrTotal);

        $pdf = Pdf::loadView('exports.payment_newspapers_polist_pdf', [
            'bankNames' => $bankNames,
            'partnerBankTotals' => $partnerBankTotals,
            'agenciesByBank' => $agenciesByBank,
            'kpraPayee' => $kpraPayee,
            'fbrPayee' => $fbrPayee,
            'kpraTotal' => $kpraTotal,
            'fbrTotal' => $fbrTotal,
            'grandTotal' => $grandTotal,
            'generatedAt' => now(),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('po-list-' . date('Y-m-d') . '.pdf');
    }

    public function exportPayOrderListExcel(Request $request)
    {
        $payments = Payment::unpaid()->with(['newspaper', 'mediaBankDetail'])->paymentFilter($request)->get();
        $agencyPayments = AgencyPayment::unpaid()->with(['mediaBankDetail'])->get();

        // Build totals per bank_detail_id (partner split) and then merge by bank_name
        $byBankId = [];
        foreach ($payments->groupBy('newspaper_id') as $newspaperId => $npPayments) {
            $splitRows = $this->splitNewspaperTotalsByPartners($npPayments, ['payable', 'kpra_inf', 'kpra_dept', 'it_inf', 'it_dept']);
            foreach ($splitRows as $r) {
                $bankDetail = $r['bank_detail'] ?? null;
                $bankId = (int) ($bankDetail?->id ?? 0);
                if ($bankId < 1) continue;
                $bankName = $bankDetail?->bank_name ?? ($r['bank_name'] ?? 'Unknown Bank');
                if (!isset($byBankId[$bankId])) {
                    $byBankId[$bankId] = [
                        'bank_name' => $bankName,
                        'totals' => ['payable' => 0, 'kpra_inf' => 0, 'kpra_dept' => 0, 'it_inf' => 0, 'it_dept' => 0],
                    ];
                }
                foreach ($byBankId[$bankId]['totals'] as $k => $_) {
                    $byBankId[$bankId]['totals'][$k] += (float) ($r['totals'][$k] ?? 0);
                }
            }
        }

        $mergedBanks = [];
        foreach ($byBankId as $bankId => $row) {
            $bankName = $row['bank_name'] ?? 'Unknown Bank';
            if (!isset($mergedBanks[$bankName])) {
                $mergedBanks[$bankName] = [
                    'bank_name' => $bankName,
                    'totals' => ['payable' => 0, 'kpra_inf' => 0, 'kpra_dept' => 0, 'it_inf' => 0, 'it_dept' => 0],
                ];
            }
            foreach ($mergedBanks[$bankName]['totals'] as $k => $_) {
                $mergedBanks[$bankName]['totals'][$k] += (float) ($row['totals'][$k] ?? 0);
            }
        }

        $agencyTotalsByBankName = $agencyPayments
            ->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank')
            ->map(fn($rows) => $rows->sum('net_dues'));

        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee = TaxPayee::where('type', 'fbr')->first();
        $kpraTotal = (float) ($payments->sum('kpra_inf') + $payments->sum('kpra_department') + $agencyPayments->sum('kpra_inf') + $agencyPayments->sum('kpra_department'));
        $fbrTotal = (float) ($payments->sum('it_inf') + $payments->sum('it_department') + $agencyPayments->sum('it_inf') + $agencyPayments->sum('it_department'));

        $rows = [];
        $sr = 1;
        $overallPayable = 0.0;
        foreach ($mergedBanks as $bankName => $bankData) {
            $payable = (float) ($bankData['totals']['payable'] ?? 0);
            $agencyPayable = (float) ($agencyTotalsByBankName[$bankName] ?? 0);
            $overallPayable += ($payable + $agencyPayable);
            $rows[] = [
                'S. No.' => $sr++,
                'Payee Name' => 'Manager ' . $bankName,
                'Payable Amount' => number_format($payable + $agencyPayable, 0),
            ];
        }

        // keep sort stable
        usort($rows, fn($a, $b) => strcmp((string) $a['Payee Name'], (string) $b['Payee Name']));

        if ($kpraTotal > 0) {
            $rows[] = [
                'S. No.' => $sr++,
                'Payee Name' => $kpraPayee->description ?? 'KPRA',
                'Payable Amount' => number_format($kpraTotal, 0),
            ];
        }
        if ($fbrTotal > 0) {
            $rows[] = [
                'S. No.' => $sr++,
                'Payee Name' => $fbrPayee->description ?? 'FBR',
                'Payable Amount' => number_format($fbrTotal, 0),
            ];
        }
        $rows[] = [
            'S. No.' => '',
            'Payee Name' => 'GRAND TOTAL (All Banks):',
            'Payable Amount' => number_format((float) ($overallPayable + $kpraTotal + $fbrTotal), 0),
        ];

        return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private array $rows) {}
            public function collection()
            {
                return collect($this->rows);
            }
            public function headings(): array
            {
                return array_keys($this->rows[0] ?? []);
            }
        }, 'pay-order-list-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPayOrderListPdf(Request $request)
    {
        $payments = Payment::unpaid()->with(['newspaper', 'mediaBankDetail'])->paymentFilter($request)->get();
        $agencyPayments = AgencyPayment::unpaid()->with(['mediaBankDetail'])->get();

        $byBankId = [];
        foreach ($payments->groupBy('newspaper_id') as $newspaperId => $npPayments) {
            $splitRows = $this->splitNewspaperTotalsByPartners($npPayments, ['payable']);
            foreach ($splitRows as $r) {
                $bankDetail = $r['bank_detail'] ?? null;
                $bankId = (int) ($bankDetail?->id ?? 0);
                if ($bankId < 1) continue;
                $bankName = $bankDetail?->bank_name ?? ($r['bank_name'] ?? 'Unknown Bank');
                $byBankId[$bankId] = $byBankId[$bankId] ?? ['bank_name' => $bankName, 'payable' => 0];
                $byBankId[$bankId]['payable'] += (float) ($r['totals']['payable'] ?? 0);
            }
        }

        $merged = [];
        foreach ($byBankId as $row) {
            $bn = $row['bank_name'] ?? 'Unknown Bank';
            $merged[$bn] = ($merged[$bn] ?? 0) + (float) ($row['payable'] ?? 0);
        }
        $agencyTotalsByBankName = $agencyPayments
            ->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank')
            ->map(fn($rows) => $rows->sum('net_dues'));

        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee = TaxPayee::where('type', 'fbr')->first();
        $kpraTotal = (float) ($payments->sum('kpra_inf') + $payments->sum('kpra_department') + $agencyPayments->sum('kpra_inf') + $agencyPayments->sum('kpra_department'));
        $fbrTotal = (float) ($payments->sum('it_inf') + $payments->sum('it_department') + $agencyPayments->sum('it_inf') + $agencyPayments->sum('it_department'));

        $overallPayable = (float) array_sum($merged) + (float) $agencyTotalsByBankName->sum();
        $grandTotal = (float) ($overallPayable + $kpraTotal + $fbrTotal);

        $pdf = Pdf::loadView('exports.payment_newspapers_payorderlist_pdf', [
            'mergedBanks' => $merged,
            'agencyTotalsByBankName' => $agencyTotalsByBankName,
            'kpraPayee' => $kpraPayee,
            'fbrPayee' => $fbrPayee,
            'kpraTotal' => $kpraTotal,
            'fbrTotal' => $fbrTotal,
            'grandTotal' => $grandTotal,
            'generatedAt' => now(),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('pay-order-list-' . date('Y-m-d') . '.pdf');
    }

    // old code
    // public function index(Request $request)
    // {
    //     // dd($request->all());

    //     $query = TreasuryChallan::with('payments')
    //         ->whereNotNull('approved_by')
    //         ->latest();

    //     // Apply payment filters via whereHas
    //     if ($request->anyFilled(['search', 'inf_number', 'newspaper_id', 'batch_no', 'cheque_number', 'challan_number', 'submission_date'])) {
    //         $query->whereHas('payments', function ($q) use ($request) {
    //             $q->paymentFilter($request);  // ← apply the scope on Payment
    //         });
    //     }

    //     // Paginate and preserve filters
    //     $paymentNewspapers = $query->paginate(15)->appends($request->query());


    //     // Get data for dropdowns
    //     $statuses   = Status::all(); // all statuses
    //     $departments = Department::all(); // all departments (adjust as needed)
    //     $offices     = Office::all();     // all offices
    //     return view('payment-newspapers.index', [
    //         'paymentNewspapers' => $paymentNewspapers,
    //         'statuses'            => $statuses,
    //         'departments'         => $departments,
    //         'offices'             => $offices,
    //     ]);
    // }



    // old code for receipt payments
    // public function receipt($id)
    // {

    //     $treasuryChallan = TreasuryChallan::with(['payments' => function ($query) {
    //         $query->orderBy('created_at', 'desc');
    //     }])->findOrFail($id);

    //     $inf_numbers = $treasuryChallan->inf_number ?? [];
    //     $inf_numbers = (array) $inf_numbers;

    //     // Fetch all receipts grouped by INF number
    //     $groupedReceipts = [];

    //     foreach ($inf_numbers as $inf) {
    //         // Fetch all related receiptsNp ads for given INF numbers
    //         $receiptsNps = BillClassifiedAd::where('inf_number', $inf)
    //             ->with(['user.newspaper'])
    //             // ->whereNotNull('estimated_cost')
    //             ->get();

    //         $receiptDetails = [];
    //         $processedNewspapers = [];

    //         foreach ($receiptsNps as $receiptsNpIndex => $receiptsNp) {

    //             $user = $receiptsNp->user;
    //             // $userId = $user->adv_agency_id;
    //             // dd($userId);
    //             $newspaper = $user->newspaper;
    //             // dd($newspaper);
    //             // $newspaperId = $newspaper->id;
    //             // $newspaperTitle = $newspaper->title;
    //             // direct newspaper advertisment check
    //             if (
    //                 is_null($user->adv_agency_id) &&
    //                 !is_null($receiptsNp->estimated_cost)
    //             ) {
    //                 $inf_number = $receiptsNp->inf_number ?? 'N/A';
    //                 // dd($inf_number);


    //                 // dd('im here');

    //                 // $newspaperId = $newspaper->id;
    //                 // $newspaperTitle = $newspaper->title;
    //                 $newspaperTitle = $receiptsNp->user->newspaper->title ?? 'N/A';
    //                 // dd($newspaperTitle);
    //                 $newspaperId = $receiptsNp->user->newspaper->id ?? null;

    //                 // Skip if we already processed this newspaper for this INF
    //                 if (in_array($newspaperId, $processedNewspapers)) {
    //                     continue;
    //                 }
    //                 $processedNewspapers[] = $newspaperId;

    //                 $infNumbers = $receiptsNp->inf_number ?? 'N/A';

    //                 // Bill amount
    //                 $printed_total_bill = $receiptsNp->printed_total_bill ?? 0;

    //                 // 1.5% income tax
    //                 $income_tax_rate = 1.5;
    //                 $income_tax_amount = $printed_total_bill * $income_tax_rate / 100;
    //                 // dd($income_tax_amount);

    //                 // Total after adding tax
    //                 $total_after_income_tax = $printed_total_bill - $income_tax_amount;

    //                 $kpra_tax_rate = 2; // KPRA tax rate of 2%
    //                 $kpra_tax_amount = $printed_total_bill * $kpra_tax_rate / 100;
    //                 $total_after_kpra_tax = $printed_total_bill - $kpra_tax_amount;

    //                 // Find the payment for this specific newspaper and INF
    //                 $existingPayment = $treasuryChallan->payments
    //                     ->where('inf_number', $infNumbers)
    //                     ->where('newspaper_id', $newspaperId)
    //                     ->first();

    //                 // Debug: Log the matching
    //                 // \Log::info('Payment match for INF ' . $inf . ':', [
    //                 //     'newspaper' => $newspaperTitle,
    //                 //     'newspaper_id' => $newspaperId,
    //                 //     'found_payment_id' => $existingPayment ? $existingPayment->id : 'none',
    //                 //     'payment_balance' => $existingPayment ? $existingPayment->balance : 'none',
    //                 //     'payment_status' => $existingPayment ? $existingPayment->status : 'none',
    //                 // ]);

    //                 $receiptDetails[] = [
    //                     'inf_number' => $infNumbers,
    //                     'id' => $receiptsNp->id,
    //                     'newspaper' => $newspaperTitle,
    //                     'newspaper_id' => $newspaperId,
    //                     'printed_total_bill' => round($printed_total_bill),
    //                     'income_tax_rate' => $income_tax_rate,
    //                     'income_tax_amount' => round($income_tax_amount),
    //                     'kpra_tax_amount' => round($kpra_tax_amount),
    //                     'total_after_income_tax' => round($total_after_income_tax),
    //                     'total_after_kpra_tax' => round($total_after_kpra_tax),
    //                     'existing_payment' => $existingPayment,
    //                 ];
    //                 // if ad run by agency
    //             } elseif (
    //                 !is_null($user->adv_agency_id) &&
    //                 is_null($receiptsNp->estimated_cost)
    //             ) {
    //                 // dd($user->adv_agency_id);


    //                 // $newspaperId = $newspaper->id;
    //                 // $newspaperTitle = $newspaper->title;
    //                 $newspaperTitle = $user->newspaper->title ?? 'N/A';
    //                 $newspaperId = $user->newspaper->id ?? null;
    //                 // $newspaperShareAmounts = $receiptsNp->newspaper_share_amounts ?? [];

    //                 // Skip if we already processed this newspaper for this INF
    //                 if (in_array($newspaperId, $processedNewspapers)) {
    //                     continue;
    //                 }
    //                 $processedNewspapers[] = $newspaperId;

    //                 $infNumbers = $receiptsNp->inf_number ?? 'N/A';

    //                 // Bill amount
    //                 $printed_total_bill = $receiptsNp->total_cost_per_newspaper[$index] ?? [];

    //                 // 100%, 85% and 15% gross amount distribution
    //                 $gross_amount = $receiptsNp->newspaper_share_amounts[$index] ?? [];
    //                 // // 1.5% income tax
    //                 $income_tax_rate = 1.5;
    //                 $income_tax_amount = $gross_amount * $income_tax_rate / 100;


    //                 // // Total after adding tax
    //                 // $total_after_income_tax = $printed_total_bill - $income_tax_amount;

    //                 // $kpra_tax_rate = 2; // KPRA tax rate of 2%
    //                 // $kpra_tax_amount = $printed_total_bill * $kpra_tax_rate / 100;
    //                 $kpra_tax_amount = $receiptsNp->kpra_2_percent_on_85_percent_newsppaer[$index] ?? [];
    //                 // $total_after_kpra_tax =  $gross_amount - $kpra_tax_amount;

    //                 // Find the payment for this specific newspaper and INF
    //                 $existingPayment = $treasuryChallan->payments
    //                     ->where('inf_number', $infNumbers)
    //                     ->where('newspaper_id', $newspaperId)
    //                     ->first();

    //                 // Debug: Log the matching
    //                 // \Log::info('Payment match for INF ' . $inf . ':', [
    //                 //     'newspaper' => $newspaperTitle,
    //                 //     'newspaper_id' => $newspaperId,
    //                 //     'found_payment_id' => $existingPayment ? $existingPayment->id : 'none',
    //                 //     'payment_balance' => $existingPayment ? $existingPayment->balance : 'none',
    //                 //     'payment_status' => $existingPayment ? $existingPayment->status : 'none',
    //                 // ]);

    //                 $receiptDetails[] = [
    //                     'inf_number' => $infNumbers,
    //                     'id' => $receiptsNp->id,
    //                     'newspaper' => $newspaperTitle,
    //                     'newspaper_id' => $newspaperId,
    //                     'printed_total_bill' => round($printed_total_bill),
    //                     'gross_amount' => round($gross_amount),
    //                     'income_tax_rate' => $income_tax_rate,
    //                     'income_tax_amount' => round($income_tax_amount),
    //                     'kpra_tax_amount' => round($kpra_tax_amount),
    //                     'total_after_income_tax' => round($total_after_income_tax),
    //                     'total_after_kpra_tax' => round($total_after_kpra_tax),
    //                     'existing_payment' => $existingPayment,
    //                 ];

    //                 dd($receiptDetails['gross_amount']);
    //             }
    //         }
    //         // dd($income_tax_amount);
    //         // save under its INF number
    //         $groupedReceipts[$inf] = $receiptDetails;
    //     }

    //     // Debug: Check actual counts
    //     // \Log::info('Actual counts after fixing duplicates:', [
    //     //     'total_payments' => $treasuryChallan->payments->count(),
    //     //     '01/25_payments' => $treasuryChallan->payments->where('inf_number', '01/25')->count(),
    //     //     '04/25_payments' => $treasuryChallan->payments->where('inf_number', '04/25')->count(),
    //     //     '01/25_receipts' => isset($groupedReceipts['01/25']) ? count($groupedReceipts['01/25']) : 0,
    //     //     '04/25_receipts' => isset($groupedReceipts['04/25']) ? count($groupedReceipts['04/25']) : 0,
    //     // ]);

    //     return view('payment-newspapers.receipt', [
    //         'groupedReceipts' => $groupedReceipts,
    //         'treasuryChallan' => $treasuryChallan,
    //         'inf_numbers' => $inf_numbers,
    //     ]);
    // }

    // new code for receipt payments
    public function receipt($id)
    {
        $treasuryChallan = TreasuryChallan::with(['payments' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        $inf_numbers = $treasuryChallan->inf_number ?? [];
        $inf_numbers = (array) $inf_numbers;

        $groupedReceipts = [];

        foreach ($inf_numbers as $inf) {
            // Fetch all bill records for this INF
            $bills = BillClassifiedAd::where('inf_number', $inf)
                ->with(['user.newspaper'])
                ->get();

            $receiptDetails = [];
            $processedRecords = [];

            foreach ($bills as $bill) {
                $user = $bill->user;

                // SCENARIO 1: DIRECT NEWSPAPER ADVERTISEMENT
                if (is_null($user->adv_agency_id) && !is_null($bill->estimated_cost)) {
                    $this->processDirectNewspaperAd($bill, $treasuryChallan, $processedRecords, $receiptDetails);
                }
                // SCENARIO 2: AGENCY ADVERTISEMENT
                elseif (!is_null($user->adv_agency_id) && is_null($bill->estimated_cost)) {
                    $this->processAgencyAd($bill, $treasuryChallan, $processedRecords, $receiptDetails);
                }
            }

            $groupedReceipts[$inf] = $receiptDetails;
        }

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Payments', 'url' => route('payment.newspapers.index')],
            ['label' => 'Payment Distribution', 'url' => null],
        ];

        return view('payment-newspapers.receipt', [
            'groupedReceipts' => $groupedReceipts,
            'treasuryChallan' => $treasuryChallan,
            'inf_numbers' => $inf_numbers,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    // Process Direct Newspaper Advertisement
    private function processDirectNewspaperAd($bill, $treasuryChallan, &$processedRecords, &$receiptDetails)
    {
        $newspaperTitle = $bill->user->newspaper->title ?? 'N/A';
        $newspaperId = $bill->user->newspaper->id ?? null;
        $infNumber = $bill->inf_number ?? 'N/A';
        $kpraRegistered = $bill->user->newspaper->register_with_kapra ?? false;
        // $kpraRegistered = ($bill->user->newspaper->register_with_kpra ?? '') === 'yes';
        // dd($kpraRegistered);

        // Create unique key for tracking
        $recordKey = $infNumber . '_' . $newspaperId;
        if (in_array($recordKey, $processedRecords)) {
            return;
        }
        $processedRecords[] = $recordKey;

        // Get bill amount // printed total bill are amount with kpra tax included
        $printedTotalBill = $bill->printed_total_bill ?? 0;


        // $kpraTaxAmount = $printedTotalBill * $kpraTaxRate / 100;
        $kpraTax = $bill->kpra_tax ?? 0;
        // dd($kpraTaxAmount);

        // amount without kpra taxes
        $totalNetDuesWithOutKpraTax = $printedTotalBill - $kpraTax;
        // dd($totalNetDuesWithOutKpraTax);

        $kpraTaxRate = 2;
        $kpraTaxAmount = $totalNetDuesWithOutKpraTax * $kpraTaxRate / 100;
        // dd($kpraTaxAmount);
        // Calculate taxes
        $incomeTaxRate = 1.5;
        $incomeTaxAmount = $totalNetDuesWithOutKpraTax * $incomeTaxRate / 100;
        // dd($incomeTaxAmount);// dd($incomeTaxAmount);
        // total dues are dues without krpra tax added
        $totalDues =   $totalNetDuesWithOutKpraTax;
        // dd($totalDues);

        // If KPRA registered, subtract tax
        // if ($kpraRegistered) {
        //     $totalDues = $printedTotalBill - $kpraTaxAmount;
        // }

        // dd($totalDues);


        // Calculate totals after tax
        // $totalAfterIncomeTax = $totalDues - $incomeTaxAmount;
        // $totalAfterKpraTax = $totalDues - $kpraTaxAmount;
        // dd($totalAfterKpraTax);

        // Find existing payment
        $existingPayment = $this->findExistingPayment($treasuryChallan, $infNumber, $newspaperId);

        // Add to receipt details
        $receiptDetails[] = [
            'inf_number' => $infNumber,
            'id' => $bill->id,
            'newspaper' => $newspaperTitle,
            'newspaper_id' => $newspaperId,
            'printed_total_bill' => round($printedTotalBill),
            'total_dues' => round($totalDues),
            'income_tax_rate' => round($incomeTaxRate),
            'income_tax_amount' => round($incomeTaxAmount),
            'kpra_tax_amount' => round($kpraTaxAmount),
            'kpra_registered' => $kpraRegistered,
            // 'total_after_income_tax' => round($totalAfterIncomeTax),
            // 'total_after_kpra_tax' => round($totalAfterKpraTax),
            'existing_payment' => $existingPayment,
            'ad_type' => 'direct',
        ];

        // dd($receiptDetails);
        // return $receiptDetails;
    }

    // Process Agency Advertisement
    private function processAgencyAd($bill, $treasuryChallan, &$processedRecords, &$receiptDetails)
    {
        $infNumber = $bill->inf_number ?? 'N/A';

        // Get arrays (already casted in model)
        $newspaperIds = $bill->newspaper_id ?? [];
        $totalCosts = $bill->total_cost_per_newspaper ?? [];
        $totalCostsWithTaxes = $bill->total_amount_with_taxes ?? [];
        $shareAmounts = $bill->newspaper_share_amounts ?? [];
        $kpraTaxes = $bill->kpra_2_percent_on_85_percent_newspaper ?? [];

        $agencyKpraTaxes = $bill->kpra_10_percent_on_15_percent_agency ?? [];

        $agency = $bill->user->agency ?? null;
        $agencyKpraRegistered = $agency ? $agency->registered_with_kpra : 0;


        // Ensure we have arrays (safety check)
        if (!is_array($newspaperIds) || empty($newspaperIds)) {
            return;
        }

        // Process each newspaper in the arrays
        foreach ($newspaperIds as $index => $newspaperId) {
            // Create unique key for tracking
            $recordKey = $infNumber . '_' . $newspaperId;
            if (in_array($recordKey, $processedRecords)) {
                continue;
            }
            $processedRecords[] = $recordKey;

            // Get newspaper name and registration
            $newspaper = Newspaper::find($newspaperId);
            $newspaperTitle = $newspaper ? $newspaper->title : 'N/A';
            $kpraRegistered = $newspaper ? $newspaper->kpra_registered : false;

            // Get values for this newspaper
            $printedTotalBill = $totalCosts[$index] ?? 0;
            // dd($printedTotalBill);
            // $newspaperShareAmountWithTaxes = $totalCostsWithTaxes[$index] * 85 / 100;

            // $agencyShareAmountWithTaxes = $totalCostsWithTaxes[$index] * 15 / 100;

            // new code
            // $totalWithTax = $totalCostsWithTaxes[$index] ?? 0;

            // $newspaperShareAmountWithTaxes = $totalWithTax * 85 / 100;
            // $agencyShareAmountWithTaxes = $totalWithTax * 15 / 100;

            // $grossAmount = $newspaperShareAmountWithTaxes;

            $kpraTaxAmount = $kpraTaxes[$index] ?? 0;

            $agencyKpraTaxAmount = $agencyKpraTaxes[$index] ?? 0;

            // Adjust for KPRA registration
            $totalDues = $totalCosts[$index] ?? 0;
            // if (!$kpraRegistered) {
            //     $totalDues -= $kpraTaxAmount;
            //     $kpraTaxAmount = 0; // Don't show in field, already subtracted
            // }
            // $totalDues = $shareAmounts[$index] ?? 0;

            $newspaperShareAmount = $totalDues * 85 / 100;
            // dd($newspaperShareAmount);
            $agencyShareAmount = $totalDues * 15 / 100;
            // dd($agencyShareAmount);

            // Calculate income tax (1.5% of total dues)
            $incomeTaxRate = 1.5;
            $incomeTaxAmount = $newspaperShareAmount * $incomeTaxRate / 100;

            // calculate kpra tax
            $kpraTaxRate = 2;
            $kpraTaxAmount = $newspaperShareAmount * $kpraTaxRate / 100;
            // dd($kpraTaxAmount);

            // Calculate totals after tax
            $totalAfterIncomeTax = $totalDues - $incomeTaxAmount;
            $totalAfterKpraTax = $totalDues - $kpraTaxAmount;

            // Find existing payment
            $existingPayment = $this->findExistingPayment($treasuryChallan, $infNumber, $newspaperId);

            // Add to receipt details
            $receiptDetails[] = [
                'inf_number' => $infNumber,
                'id' => $bill->id,
                'newspaper' => $newspaperTitle,
                'newspaper_id' => $newspaperId,
                'agency_kpra_registered' => $agencyKpraRegistered,
                'printed_total_bill' => round($printedTotalBill),
                'total_dues' => round($totalDues),
                'gross_amount' => round($newspaperShareAmount),
                'income_tax_rate' => round($incomeTaxRate),
                'income_tax_amount' => round($incomeTaxAmount),
                'kpra_tax_amount' => round($kpraTaxAmount),
                'agency_kpra_tax_amount' => round($agencyKpraTaxAmount),
                'kpra_registered' => $kpraRegistered,
                'total_after_income_tax' => round($totalAfterIncomeTax),
                'total_after_kpra_tax' => round($totalAfterKpraTax),
                'existing_payment' => $existingPayment,
                'ad_type' => 'agency',
                'agency_id' => $bill->user->adv_agency_id,
                'agency_name' => $bill->user->agency->name ?? 'N/A',
            ];
        }
    }

    // Helper function to find existing payment
    private function findExistingPayment($treasuryChallan, $infNumber, $newspaperId)
    {
        return $treasuryChallan->payments
            ->where('inf_number', $infNumber)
            ->where('newspaper_id', $newspaperId)
            ->first();
    }


    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'challan_id' => 'required|integer|exists:treasury_challans,id',
            'receipts' => 'required|array',
            'receipts.*.newspaper_id' => 'nullable|integer',
            'receipts.*.rt_number' => 'nullable|string',
            'receipts.*.inf_number' => 'nullable|string',
            'receipts.*.payment_type' => 'required|string|in:direct,agency',
            'receipts.*.total_amount' => 'nullable|numeric',
            'receipts.*.gross_amount_100_or_85_percent' => 'nullable|numeric',
            'receipts.*.it_inf' => 'nullable|numeric',
            'receipts.*.it_department' => 'nullable|numeric',
            'receipts.*.kpra_inf' => 'nullable|numeric',
            'receipts.*.kpra_department' => 'nullable|numeric',
            'receipts.*.sbp_charges' => 'nullable|numeric',
            'receipts.*.adjustment' => 'nullable|numeric',
            'receipts.*.net_dues' => 'nullable|numeric',
            'receipts.*.received' => 'nullable|numeric',
            'receipts.*.balance' => 'nullable|numeric',
            'receipts.*.remarks' => 'nullable|string',
            'agency_data' => 'nullable|array',
            'agency_data.agency_id' => 'nullable|integer|exists:adv_agencies,id',
            'agency_data.grand_amount' => 'nullable|numeric',
            'agency_data.gross_amount_15_percent' => 'nullable|numeric',
            'agency_data.it_inf' => 'nullable|numeric',
            'agency_data.it_department' => 'nullable|numeric',
            'agency_data.kpra_inf' => 'nullable|numeric',
            'agency_data.kpra_department' => 'nullable|numeric',
            'agency_data.sbp_charges' => 'nullable|numeric',
            'agency_data.adjustment' => 'nullable|numeric',
            'agency_data.net_dues' => 'nullable|numeric',
            'agency_data.received' => 'nullable|numeric',
            'agency_data.balance' => 'nullable|numeric',
            'agency_data.remarks' => 'nullable|string',
        ]);

        // dd($validated['receipts']);

        DB::beginTransaction();

        try {
            $agencyPaymentId = null;
            $agencyBankAccountId = null;

            // Fetch the treasury challan to get its batch_no
            $challan = TreasuryChallan::find($validated['challan_id']);
            $challanBatchNo = $challan ? $challan->batch_no : null;

            // ── Batch No increment logic (based on paid_amounts) ─────────────
            // Requirement:
            // - If paid_amounts is empty → first receipt batch is "Mon-YYYY-1"
            // - If paid_amounts already contains current batch_no → use next "Mon-YYYY-2", and so on.
            // - Always check paid_amounts before saving receipts.
            $currentPrefix = now()->format('M-Y'); // e.g. "Apr-2026"
            $resolveNextBatchNo = function (?string $candidate) use ($currentPrefix): string {
                // If challan batch is missing or from a different month, start from current month sequence 1.
                $start = $candidate && str_starts_with($candidate, $currentPrefix . '-') ? $candidate : ($currentPrefix . '-1');

                // Extract sequence, default 1
                $seq = 1;
                if (preg_match('/^' . preg_quote($currentPrefix, '/') . '\-(\d+)$/i', $start, $m)) {
                    $seq = max(1, (int) $m[1]);
                }

                // Move forward while the batch is already present in paid_amounts
                // (check both new `batch_no` and legacy `ledger_batch_no` just in case).
                while (true) {
                    $check = $currentPrefix . '-' . $seq;
                    $alreadyPaid = PaidAmount::query()
                        ->where('batch_no', $check)
                        ->orWhere('ledger_batch_no', $check)
                        ->exists();
                    if (!$alreadyPaid) {
                        return $check;
                    }
                    $seq++;
                }
            };

            $challanBatchNo = $resolveNextBatchNo($challanBatchNo);

            // Keep challan batch_no in sync so later saves reuse the same open batch.
            if ($challan && $challan->batch_no !== $challanBatchNo) {
                $challan->update(['batch_no' => $challanBatchNo]);
            }

            //  Check if ANY receipt is agency type
            $hasAgencyReceipts = false;
            foreach ($validated['receipts'] as $receipt) {
                // \Log::info('Processing receipt', $receipt);
                if (($receipt['payment_type'] ?? 'direct') === 'agency') {
                    $hasAgencyReceipts = true;
                    break;
                }
            }

            // Step 1: first check if there is any receipt for agency and then create agency payment if agency data exists
            if ($hasAgencyReceipts && isset($validated['agency_data'])) {
                // Determine agency bank account
                $agencyBankAccount = MediaBankDetail::where('agency_id', $validated['agency_data']['agency_id'])
                    ->whereNull('newspaper_id')
                    ->first();

                $agencyBankAccountId = $agencyBankAccount ? $agencyBankAccount->id : null;
                $agencyPayment = AgencyPayment::create([
                    'agency_id' => $validated['agency_data']['agency_id'] ?? null,
                    'batch_no' => $challanBatchNo,
                    'grand_amount' => $validated['agency_data']['grand_amount'] ?? 0,
                    'gross_amount_15_percent' => $validated['agency_data']['gross_amount_15_percent'] ?? 0,
                    'it_inf' => $validated['agency_data']['it_inf'] ?? 0,
                    'it_department' => $validated['agency_data']['it_department'] ?? 0,
                    'kpra_inf' => $validated['agency_data']['kpra_inf'] ?? 0,
                    'kpra_department' => $validated['agency_data']['kpra_department'] ?? 0,
                    'sbp_charges' => $validated['agency_data']['sbp_charges'] ?? 0,
                    'adjustment' => $validated['agency_data']['adjustment'] ?? 0,
                    'net_dues' => $validated['agency_data']['net_dues'] ?? 0,
                    'received' => $validated['agency_data']['received'] ?? 0,
                    'balance' => $validated['agency_data']['balance'] ?? 0,
                    'remarks' => $validated['agency_data']['remarks'] ?? 0,
                    'media_bank_detail_id' => $agencyBankAccountId, // Agency's bank account

                ]);

                $agencyPaymentId = $agencyPayment->id;
            }

            foreach ($validated['receipts'] as $receipt) {
                \Log::info('Processing receipt', $receipt);
                if (empty($receipt['newspaper_id'])) {
                    continue;
                }
                $status = 'UNPAID';

                if ($receipt['balance'] == 0) {
                    $status = 'PAID';
                } elseif ($receipt['balance'] < 0) {
                    $status = 'OVER_PAID';
                } elseif ($receipt['received'] > 0 && $receipt['balance'] > 0) {
                    $status = 'PARTIALLY_PAID';
                }


                // Determine payment type and agency_payment_id for THIS receipt
                $paymentType = $receipt['payment_type'] ?? 'direct';

                // Only set agency_payment_id for agency type receipts
                $receiptAgencyPaymentId = null;
                if ($paymentType === 'agency' && $agencyPaymentId) {
                    $receiptAgencyPaymentId = $agencyPaymentId;
                }

                // Determine newspaper bank account
                $newspaperBankAccount = MediaBankDetail::where('newspaper_id', $receipt['newspaper_id'])
                    ->whereNull('agency_id')
                    ->first();

                $newspaperBankAccountId = $newspaperBankAccount ? $newspaperBankAccount->id : null;

                // Check if payment already exists for this combination
                $existingPayment = Payment::where([
                    'challan_id' => $validated['challan_id'],
                    'inf_number' => $receipt['inf_number'],
                    'newspaper_id' => $receipt['newspaper_id'] ?? null,
                ])->first();

                if ($existingPayment) {
                    // Update existing payment
                    $existingPayment->update([
                        'rt_number' => $receipt['rt_number'],
                        'total_amount' => $receipt['total_amount'],
                        'gross_amount_100_or_85_percent' => $receipt['gross_amount_100_or_85_percent'],
                        'it_inf' => $receipt['it_inf'],
                        'it_department' => $receipt['it_department'],
                        'kpra_inf' => $receipt['kpra_inf'],
                        'kpra_department' => $receipt['kpra_department'],
                        'sbp_charges' => $receipt['sbp_charges'],
                        'adjustment' => $receipt['adjustment'],
                        'net_dues' => $receipt['net_dues'],
                        'received' => $receipt['received'],
                        'balance' => $receipt['balance'],
                        'status' => $status,
                        'remarks' => $receipt['remarks'] ?? null,
                        'agency_payment_id' => $receiptAgencyPaymentId,
                        'media_bank_detail_id' => $newspaperBankAccountId, // Newspaper's bank account
                        'payment_type' =>  $paymentType,
                        'batch_no' => $challanBatchNo,
                    ]);
                } else {
                    // Create new payment
                    Payment::create([
                        'challan_id' => $validated['challan_id'],
                        'batch_no' => $challanBatchNo,
                        'inf_number' => $receipt['inf_number'],
                        'rt_number' => $receipt['rt_number'],
                        'newspaper_id' => $receipt['newspaper_id'] ?? null,
                        'total_amount' => $receipt['total_amount'],
                        'gross_amount_100_or_85_percent' => $receipt['gross_amount_100_or_85_percent'],
                        'it_inf' => $receipt['it_inf'],
                        'it_department' => $receipt['it_department'],
                        'kpra_inf' => $receipt['kpra_inf'],
                        'kpra_department' => $receipt['kpra_department'],
                        'sbp_charges' => $receipt['sbp_charges'],
                        'adjustment' => $receipt['adjustment'],
                        'net_dues' => $receipt['net_dues'],
                        'received' => $receipt['received'],
                        'balance' => $receipt['balance'],
                        'status' => $status,
                        'remarks' => $receipt['remarks'] ?? null,
                        'agency_payment_id' => $receiptAgencyPaymentId,
                        'media_bank_detail_id' => $newspaperBankAccountId, // Newspaper's bank account
                        'payment_type' =>  $paymentType,

                    ]);
                }
            }

            DB::commit();

            return redirect()->route('payment.newspapers.index')->with('success', 'Ledger saved successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }


    // old code
    // public function newspaperBulkView()
    // {
    //     // Load payments with newspaper (Eloquent way)
    //     $payments = Payment::unpaid()->with(['newspaper', 'bill:id,inf_number,invoice_no,invoice_date'])
    //         ->orderBy('newspaper_id')
    //         ->get()
    //         ->groupBy('newspaper_id');

    //     // Get agency payments grouped by agency_id
    //     $agencyPayments = AgencyPayment::unpaid()->with(['agency', 'payments'])
    //         ->orderBy('agency_id')
    //         ->get()
    //         ->groupBy('agency_id');


    //     // $this->applyFilters($query, $request);

    //     return view('payment-newspapers.newspaper-bulk-view', compact(['payments', 'agencyPayments']));
    // }
    // new code with filteration applied

    // new code with filteration applaied
    public function newspaperBulkView(Request $request)
    {
        $query = Payment::unpaid()
            ->with(['newspaper', 'bill:id,inf_number,invoice_no,invoice_date'])
            ->paymentFilter($request);   // apply filters

        $payments = $query->orderBy('newspaper_id')
            ->paginate(20)
            ->appends($request->query());

        // Group by newspaper_id for display (works on current page only)
        $groupedPayments = $payments->groupBy('newspaper_id');

        // Agency payments – also filter by batch_no if needed? For now, separate.
        $agencyQuery = AgencyPayment::unpaid()
            ->with(['agency', 'payments']);
        // Optionally apply same search filters (if you add scope to AgencyPayment)
        if ($request->filled('search')) {
            $agencyQuery->whereHas('agency', fn($q) => $q->where('name', 'LIKE', "%{$request->search}%"));
        }
        $agencyPayments = $agencyQuery->orderBy('agency_id')->get()->groupBy('agency_id');

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Payments', 'url' => route('payment.newspapers.index')],
            ['label' => 'NewspaperWise Total Amount', 'url' => null],
        ];

        return view('payment-newspapers.newspaper-bulk-view', [
            'payments' => $groupedPayments,
            'agencyPayments' => $agencyPayments,
            'paginator' => $payments,   // pass paginator for links
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    // old code
    // public function newspaperWiseSummary()
    // {
    //     // Load payments with newspaper (Eloquent way)
    //     $payments = Payment::unpaid()
    //         ->with(['newspaper', 'mediaBankDetail',   'bill:id,inf_number,invoice_no,invoice_date'])
    //         ->orderBy('newspaper_id')
    //         ->get()
    //         ->groupBy('newspaper_id');

    //     // Agency-wise summary (same screen, after newspapers)
    //     $agencies = AgencyPayment::unpaid()
    //         ->with(['agency', 'mediaBankDetail'])
    //         ->orderBy('agency_id')
    //         ->get()
    //         ->groupBy('agency_id');

    //     // KPRA / FBR totals + bank details (from tax_payees)
    //     $kpraPayee = TaxPayee::where('type', 'kpra')->first();
    //     $fbrPayee  = TaxPayee::where('type', 'fbr')->first();

    //     $kpraTotalInf  = $payments->flatten()->sum('kpra_inf') + $agencies->flatten()->sum('kpra_inf');
    //     $kpraTotalDept = $payments->flatten()->sum('kpra_department') + $agencies->flatten()->sum('kpra_department');
    //     $fbrTotalInf   = $payments->flatten()->sum('it_inf') + $agencies->flatten()->sum('it_inf');
    //     $fbrTotalDept  = $payments->flatten()->sum('it_department') + $agencies->flatten()->sum('it_department');



    //     foreach ($payments as $newspaperId => $paymentGroup) {
    //         $totalNetDues = $paymentGroup->sum('net_dues');
    //         // You can calculate other totals similarly

    //         // Store totals in the group for easy access in the view
    //         $payments[$newspaperId]->total_net_dues = $totalNetDues;
    //         // Add other totals as needed
    //     }

    //     return view('payment-newspapers.newspaper-wise-summary', compact([
    //         'payments',
    //         'agencies',
    //         'kpraPayee',
    //         'fbrPayee',
    //         'kpraTotalInf',
    //         'kpraTotalDept',
    //         'fbrTotalInf',
    //         'fbrTotalDept',
    //     ]));
    // }

    // new code with filteraion applied
    public function newspaperWiseSummary(Request $request)
    {
        $query = Payment::unpaid()
            ->with(['newspaper', 'mediaBankDetail', 'bill:id,inf_number,invoice_no,invoice_date'])
            ->paymentFilter($request);

        $payments = $query->orderBy('newspaper_id')
            ->get()
            ->groupBy('newspaper_id');

        $agencies = AgencyPayment::unpaid()
            ->with(['agency', 'mediaBankDetail'])
            ->orderBy('agency_id')
            ->get()
            ->groupBy('agency_id');

        // KPRA / FBR totals (filtered payments only)
        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee  = TaxPayee::where('type', 'fbr')->first();

        $kpraTotalInf  = $payments->flatten()->sum('kpra_inf') + $agencies->flatten()->sum('kpra_inf');
        $kpraTotalDept = $payments->flatten()->sum('kpra_department') + $agencies->flatten()->sum('kpra_department');
        $fbrTotalInf   = $payments->flatten()->sum('it_inf') + $agencies->flatten()->sum('it_inf');
        $fbrTotalDept  = $payments->flatten()->sum('it_department') + $agencies->flatten()->sum('it_department');


        foreach ($payments as $newspaperId => $paymentGroup) {
            $payments[$newspaperId]->total_net_dues = $paymentGroup->sum('net_dues');
        }

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Payments', 'url' => route('payment.newspapers.index')],
            ['label' => 'Newspaper Wise Total Amount', 'url' => null],
        ];

        return view('payment-newspapers.newspaper-wise-summary', compact(
            'payments',
            'agencies',
            'kpraPayee',
            'fbrPayee',
            'kpraTotalInf',
            'kpraTotalDept',
            'fbrTotalInf',
            'fbrTotalDept',
            'breadcrumbs'
        ));
    }

    // old code
    // public function bankNameWiseSummary()
    // {
    //     // Newspapers (payments)
    //     $payments = Payment::unpaid()
    //         ->with(['newspaper', 'mediaBankDetail'])
    //         ->whereHas('mediaBankDetail')
    //         ->orderBy('media_bank_detail_id')
    //         ->orderBy('newspaper_id')
    //         ->get();

    //     // Agencies (agency_payments)
    //     $agencyPayments = AgencyPayment::unpaid()
    //         ->with(['agency', 'mediaBankDetail'])
    //         ->whereHas('mediaBankDetail')
    //         ->orderBy('media_bank_detail_id')
    //         ->orderBy('agency_id')
    //         ->get();

    //     // Note: partner newspapers will be re-grouped by partner bank below.
    //     $newspapersByBank = $payments->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank');
    //     $agenciesByBank   = $agencyPayments->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank');

    //     $bankNames = $newspapersByBank->keys()
    //         ->merge($agenciesByBank->keys())
    //         ->unique()
    //         ->sort()
    //         ->values();

    //     $bankWiseData = [];
    //     $overallTotals = [
    //         'payable'   => 0,
    //         'kpra_inf'  => 0,
    //         'kpra_dept' => 0,
    //         'it_inf'    => 0,
    //         'it_dept'   => 0,
    //     ];

    //     // KPRA / FBR bank details (from tax_payees)
    //     $kpraPayee = TaxPayee::where('type', 'kpra')->first();
    //     $fbrPayee  = TaxPayee::where('type', 'fbr')->first();

    //     // Pre-group payments by newspaper for partner splitting
    //     $paymentsByNewspaper = $payments->groupBy('newspaper_id');
    //     $partnerBankBuckets = [];

    //     foreach ($paymentsByNewspaper as $newspaperId => $newspaperPayments) {
    //         $splitRows = $this->splitNewspaperTotalsByPartners($newspaperPayments);
    //         foreach ($splitRows as $r) {
    //             $bName = $r['bank_name'] ?? 'Unknown Bank';
    //             $partnerBankBuckets[$bName][] = $r;
    //         }
    //     }

    //     // bankNames recompute including partner banks
    //     $bankNames = collect(array_keys($partnerBankBuckets))
    //         ->merge($agenciesByBank->keys())
    //         ->unique()
    //         ->sort()
    //         ->values();

    //     foreach ($bankNames as $bankName) {
    //         $bankTotals = [
    //             'payable'   => 0,
    //             'kpra_inf'  => 0,
    //             'kpra_dept' => 0,
    //             'it_inf'    => 0,
    //             'it_dept'   => 0,
    //         ];

    //         $newspaperData = [];
    //         $agencyData    = [];

    //         // ── Newspapers for this bank ───────────────────────────────────
    //         $bankSplitRows = collect($partnerBankBuckets[$bankName] ?? []);
    //         foreach ($bankSplitRows as $row) {
    //             $totals = $row['totals'] ?? [];
    //             foreach ($bankTotals as $key => $_) {
    //                 $v = (float) ($totals[$key] ?? 0);
    //                 $bankTotals[$key] += $v;
    //                 $overallTotals[$key] += $v;
    //             }

    //             $newspaperData[] = [
    //                 'newspaper' => $row['newspaper'] ?? null,
    //                 'payments' => $row['payments'] ?? collect(),
    //                 'bank_detail' => $row['bank_detail'] ?? null,
    //                 'partner_name' => $row['partner_name'] ?? null,
    //                 'share_percentage' => $row['share_percentage'] ?? null,
    //                 'totals' => $totals,
    //             ];
    //         }

    //         // ── Agencies for this bank ─────────────────────────────────────
    //         $bankAgencyPayments = $agenciesByBank->get($bankName, collect());
    //         $agencyGroups = $bankAgencyPayments->groupBy('agency_id');

    //         foreach ($agencyGroups as $agencyId => $rows) {
    //             $first = $rows->first();

    //             $agencyTotals = [
    //                 'payable'   => $rows->sum('net_dues'),
    //                 'kpra_inf'  => $rows->sum('kpra_inf'),
    //                 'kpra_dept' => $rows->sum('kpra_department'),
    //                 'it_inf'    => $rows->sum('it_inf'),
    //                 'it_dept'   => $rows->sum('it_department'),
    //             ];

    //             foreach ($agencyTotals as $key => $value) {
    //                 $bankTotals[$key] += $value;
    //                 $overallTotals[$key] += $value;
    //             }

    //             $agencyData[] = [
    //                 'agency'      => $first->agency,
    //                 'payments'    => $rows,
    //                 'bank_detail' => $first->mediaBankDetail,
    //                 'totals'      => $agencyTotals,
    //             ];
    //         }

    //         $bankWiseData[] = [
    //             'bank_name'  => $bankName,
    //             'newspapers' => $newspaperData,
    //             'agencies'   => $agencyData,
    //             'totals'     => $bankTotals,
    //         ];
    //     }

    //     $kpraTotal = (float) ($overallTotals['kpra_inf'] + $overallTotals['kpra_dept']);
    //     $fbrTotal  = (float) ($overallTotals['it_inf'] + $overallTotals['it_dept']);
    //     $grandTotal = (float) ($overallTotals['payable'] + $kpraTotal + $fbrTotal);

    //     return view('payment-newspapers.bank-wise-view', compact(
    //         'bankWiseData',
    //         'overallTotals',
    //         'kpraPayee',
    //         'fbrPayee',
    //         'kpraTotal',
    //         'fbrTotal',
    //         'grandTotal'
    //     ));
    // }

    // new code with filteration applied
    public function bankNameWiseSummary(Request $request)
    {
        // Filtered payments
        $payments = Payment::unpaid()
            ->with(['newspaper', 'mediaBankDetail'])
            ->paymentFilter($request)
            ->orderBy('media_bank_detail_id')
            ->orderBy('newspaper_id')
            ->get();

        $agencyPayments = AgencyPayment::unpaid()
            ->with(['agency', 'mediaBankDetail'])
            ->orderBy('media_bank_detail_id')
            ->orderBy('agency_id')
            ->get();

        // ... rest of the method unchanged (partner splitting logic) ...
        // (Keep all the existing code for splitting and grouping)
        //     // Note: partner newspapers will be re-grouped by partner bank below.
        $newspapersByBank = $payments->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank');
        $agenciesByBank   = $agencyPayments->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank');

        $bankNames = $newspapersByBank->keys()
            ->merge($agenciesByBank->keys())
            ->unique()
            ->sort()
            ->values();

        $bankWiseData = [];
        $overallTotals = [
            'payable'   => 0,
            'kpra_inf'  => 0,
            'kpra_dept' => 0,
            'it_inf'    => 0,
            'it_dept'   => 0,
        ];

        // KPRA / FBR bank details (from tax_payees)
        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee  = TaxPayee::where('type', 'fbr')->first();

        // Pre-group payments by newspaper for partner splitting
        $paymentsByNewspaper = $payments->groupBy('newspaper_id');
        $partnerBankBuckets = [];

        foreach ($paymentsByNewspaper as $newspaperId => $newspaperPayments) {
            $splitRows = $this->splitNewspaperTotalsByPartners($newspaperPayments);
            foreach ($splitRows as $r) {
                $bName = $r['bank_name'] ?? 'Unknown Bank';
                $partnerBankBuckets[$bName][] = $r;
            }
        }

        // bankNames recompute including partner banks
        $bankNames = collect(array_keys($partnerBankBuckets))
            ->merge($agenciesByBank->keys())
            ->unique()
            ->sort()
            ->values();

        foreach ($bankNames as $bankName) {
            $bankTotals = [
                'payable'   => 0,
                'kpra_inf'  => 0,
                'kpra_dept' => 0,
                'it_inf'    => 0,
                'it_dept'   => 0,
            ];

            $newspaperData = [];
            $agencyData    = [];

            // ── Newspapers for this bank ───────────────────────────────────
            $bankSplitRows = collect($partnerBankBuckets[$bankName] ?? []);
            foreach ($bankSplitRows as $row) {
                $totals = $row['totals'] ?? [];
                foreach ($bankTotals as $key => $_) {
                    $v = (float) ($totals[$key] ?? 0);
                    $bankTotals[$key] += $v;
                    $overallTotals[$key] += $v;
                }

                $newspaperData[] = [
                    'newspaper' => $row['newspaper'] ?? null,
                    'payments' => $row['payments'] ?? collect(),
                    'bank_detail' => $row['bank_detail'] ?? null,
                    'partner_name' => $row['partner_name'] ?? null,
                    'share_percentage' => $row['share_percentage'] ?? null,
                    'totals' => $totals,
                ];
            }

            // ── Agencies for this bank ─────────────────────────────────────
            $bankAgencyPayments = $agenciesByBank->get($bankName, collect());
            $agencyGroups = $bankAgencyPayments->groupBy('agency_id');

            foreach ($agencyGroups as $agencyId => $rows) {
                $first = $rows->first();

                $agencyTotals = [
                    'payable'   => $rows->sum('net_dues'),
                    'kpra_inf'  => $rows->sum('kpra_inf'),
                    'kpra_dept' => $rows->sum('kpra_department'),
                    'it_inf'    => $rows->sum('it_inf'),
                    'it_dept'   => $rows->sum('it_department'),
                ];

                foreach ($agencyTotals as $key => $value) {
                    $bankTotals[$key] += $value;
                    $overallTotals[$key] += $value;
                }

                $agencyData[] = [
                    'agency'      => $first->agency,
                    'payments'    => $rows,
                    'bank_detail' => $first->mediaBankDetail,
                    'totals'      => $agencyTotals,
                ];
            }

            $bankWiseData[] = [
                'bank_name'  => $bankName,
                'newspapers' => $newspaperData,
                'agencies'   => $agencyData,
                'totals'     => $bankTotals,
            ];
        }

        $kpraTotal = (float) ($overallTotals['kpra_inf'] + $overallTotals['kpra_dept']);
        $fbrTotal  = (float) ($overallTotals['it_inf'] + $overallTotals['it_dept']);
        $grandTotal = (float) ($overallTotals['payable'] + $kpraTotal + $fbrTotal);

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Payments', 'url' => route('payment.newspapers.index')],
            ['label' => 'Bank Wise Payment Summary', 'url' => null],
        ];

        return view('payment-newspapers.bank-wise-view', compact(
            'bankWiseData',
            'overallTotals',
            'kpraPayee',
            'fbrPayee',
            'kpraTotal',
            'fbrTotal',
            'grandTotal',
            'breadcrumbs'
        ));
    }


    // old code
    // public function poList()
    // {
    //     // Newspapers (payments)
    //     $payments = Payment::unpaid()
    //         ->with(['mediaBankDetail'])
    //         ->whereHas('mediaBankDetail')
    //         ->get();

    //     // Agencies (agency_payments)
    //     $agencyPayments = AgencyPayment::unpaid()
    //         ->with(['mediaBankDetail'])
    //         ->whereHas('mediaBankDetail')
    //         ->get();

    //     // KPRA / FBR bank details (from tax_payees)
    //     $kpraPayee = TaxPayee::where('type', 'kpra')->first();
    //     $fbrPayee  = TaxPayee::where('type', 'fbr')->first();

    //     // Overall KPRA/FBR totals (newspaper + agency unpaid)
    //     $kpraTotal = (float) ($payments->sum('kpra_inf') + $payments->sum('kpra_department')
    //         + $agencyPayments->sum('kpra_inf') + $agencyPayments->sum('kpra_department'));

    //     $fbrTotal = (float) ($payments->sum('it_inf') + $payments->sum('it_department')
    //         + $agencyPayments->sum('it_inf') + $agencyPayments->sum('it_department'));

    //     // Partner newspapers will be re-grouped by partner bank.
    //     $newspapersByBank = $payments->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank');
    //     $agenciesByBank   = $agencyPayments->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank');

    //     // Rebuild bank totals from split rows
    //     $paymentsByNewspaper = $payments->groupBy('newspaper_id');
    //     $partnerBankTotals = [];
    //     foreach ($paymentsByNewspaper as $newspaperId => $newspaperPayments) {
    //         $splitRows = $this->splitNewspaperTotalsByPartners($newspaperPayments, ['payable']);
    //         foreach ($splitRows as $r) {
    //             $bName = $r['bank_name'] ?? 'Unknown Bank';
    //             if (!isset($partnerBankTotals[$bName])) {
    //                 $partnerBankTotals[$bName] = 0.0;
    //             }
    //             $partnerBankTotals[$bName] += (float) ($r['totals']['payable'] ?? 0);
    //         }
    //     }

    //     $bankNames = collect(array_keys($partnerBankTotals))
    //         ->merge($agenciesByBank->keys())
    //         ->unique()
    //         ->sort()
    //         ->values();

    //     $bankWiseData = [];
    //     $overallTotals = [
    //         'payable'        => 0,
    //         'newspaper_total' => 0,
    //         'agency_total'   => 0,
    //     ];

    //     foreach ($bankNames as $bankName) {
    //         $newspaperTotal = (float) ($partnerBankTotals[$bankName] ?? 0);
    //         $agencyTotal    = $agenciesByBank->get($bankName, collect())->sum('net_dues');

    //         $bankTotals = [
    //             'newspaper_total' => $newspaperTotal,
    //             'agency_total'    => $agencyTotal,
    //             'payable'         => $newspaperTotal + $agencyTotal,
    //         ];

    //         $overallTotals['newspaper_total'] += $newspaperTotal;
    //         $overallTotals['agency_total']    += $agencyTotal;
    //         $overallTotals['payable']         += $bankTotals['payable'];

    //         $bankWiseData[] = [
    //             'bank_name' => $bankName,
    //             'totals'    => $bankTotals,
    //         ];
    //     }

    //     $grandTotal = (float) ($overallTotals['payable'] + $kpraTotal + $fbrTotal);

    //     return view('payment-newspapers.po-list', compact(
    //         'bankWiseData',
    //         'overallTotals',
    //         'kpraPayee',
    //         'fbrPayee',
    //         'kpraTotal',
    //         'fbrTotal',
    //         'grandTotal'
    //     ));
    // }


    // new code with filteration applied
    public function poList(Request $request)
    {
        $payments = Payment::unpaid()
            ->with(['mediaBankDetail'])
            ->paymentFilter($request)
            ->get();

        $agencyPayments = AgencyPayment::unpaid()
            ->with(['mediaBankDetail'])
            ->get();

        // ... rest of the method unchanged ...
        // KPRA / FBR bank details (from tax_payees)
        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee  = TaxPayee::where('type', 'fbr')->first();

        // Overall KPRA/FBR totals (newspaper + agency unpaid)
        $kpraTotal = (float) ($payments->sum('kpra_inf') + $payments->sum('kpra_department')
            + $agencyPayments->sum('kpra_inf') + $agencyPayments->sum('kpra_department'));

        $fbrTotal = (float) ($payments->sum('it_inf') + $payments->sum('it_department')
            + $agencyPayments->sum('it_inf') + $agencyPayments->sum('it_department'));

        // Partner newspapers will be re-grouped by partner bank.
        $newspapersByBank = $payments->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank');
        $agenciesByBank   = $agencyPayments->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank');

        // Rebuild bank totals from split rows
        $paymentsByNewspaper = $payments->groupBy('newspaper_id');
        $partnerBankTotals = [];
        foreach ($paymentsByNewspaper as $newspaperId => $newspaperPayments) {
            $splitRows = $this->splitNewspaperTotalsByPartners($newspaperPayments, ['payable']);
            foreach ($splitRows as $r) {
                $bName = $r['bank_name'] ?? 'Unknown Bank';
                if (!isset($partnerBankTotals[$bName])) {
                    $partnerBankTotals[$bName] = 0.0;
                }
                $partnerBankTotals[$bName] += (float) ($r['totals']['payable'] ?? 0);
            }
        }

        $bankNames = collect(array_keys($partnerBankTotals))
            ->merge($agenciesByBank->keys())
            ->unique()
            ->sort()
            ->values();

        $bankWiseData = [];
        $overallTotals = [
            'payable'        => 0,
            'newspaper_total' => 0,
            'agency_total'   => 0,
        ];

        foreach ($bankNames as $bankName) {
            $newspaperTotal = (float) ($partnerBankTotals[$bankName] ?? 0);
            $agencyTotal    = $agenciesByBank->get($bankName, collect())->sum('net_dues');

            $bankTotals = [
                'newspaper_total' => $newspaperTotal,
                'agency_total'    => $agencyTotal,
                'payable'         => $newspaperTotal + $agencyTotal,
            ];

            $overallTotals['newspaper_total'] += $newspaperTotal;
            $overallTotals['agency_total']    += $agencyTotal;
            $overallTotals['payable']         += $bankTotals['payable'];

            $bankWiseData[] = [
                'bank_name' => $bankName,
                'totals'    => $bankTotals,
            ];
        }

        $grandTotal = (float) ($overallTotals['payable'] + $kpraTotal + $fbrTotal);

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Payments', 'url' => route('payment.newspapers.index')],
            ['label' => 'PO List Summary', 'url' => null],
        ];

        return view('payment-newspapers.po-list', compact(
            'bankWiseData',
            'overallTotals',
            'kpraPayee',
            'fbrPayee',
            'kpraTotal',
            'fbrTotal',
            'grandTotal',
            'breadcrumbs'
        ));
    }

    // Pay Order List old code
    // public function payOrderList()
    // {
    //     $payments = Payment::unpaid()
    //         ->with(['newspaper', 'mediaBankDetail'])
    //         ->whereHas('mediaBankDetail')
    //         ->get();

    //     // Agencies (used only for totals on this screen)
    //     $agencyPayments = AgencyPayment::unpaid()
    //         ->with(['mediaBankDetail'])
    //         ->whereHas('mediaBankDetail')
    //         ->get();

    //     // KPRA / FBR bank details (from tax_payees)
    //     $kpraPayee = TaxPayee::where('type', 'kpra')->first();
    //     $fbrPayee  = TaxPayee::where('type', 'fbr')->first();

    //     // STEP 1: Split newspaper totals by partners and bucket by bank_detail_id
    //     $byBankId = [];
    //     $newspaperDataByBankName = [];

    //     $paymentsByNewspaper = $payments->groupBy('newspaper_id');
    //     foreach ($paymentsByNewspaper as $newspaperId => $newspaperPayments) {
    //         $splitRows = $this->splitNewspaperTotalsByPartners($newspaperPayments, ['payable', 'kpra_inf', 'kpra_dept', 'it_inf', 'it_dept']);
    //         foreach ($splitRows as $r) {
    //             $bankDetail = $r['bank_detail'] ?? null;
    //             $bankId = (int) ($bankDetail?->id ?? 0);
    //             $bankName = $bankDetail?->bank_name ?? ($r['bank_name'] ?? 'Unknown Bank');
    //             if ($bankId < 1) {
    //                 continue;
    //             }
    //             if (!isset($byBankId[$bankId])) {
    //                 $byBankId[$bankId] = [
    //                     'bank_id' => $bankId,
    //                     'bank_name' => $bankName,
    //                     'bank_detail' => $bankDetail,
    //                     'totals' => ['payable' => 0, 'kpra_inf' => 0, 'kpra_dept' => 0, 'it_inf' => 0, 'it_dept' => 0],
    //                 ];
    //             }
    //             foreach ($byBankId[$bankId]['totals'] as $k => $_) {
    //                 $byBankId[$bankId]['totals'][$k] += (float) ($r['totals'][$k] ?? 0);
    //             }

    //             // For debugging/optional UI use: keep per-bank-name newspaper rows (not used by blade right now)
    //             if (!isset($newspaperDataByBankName[$bankName])) {
    //                 $newspaperDataByBankName[$bankName] = [];
    //             }
    //             $newspaperDataByBankName[$bankName][] = [
    //                 'newspaper' => $r['newspaper'] ?? null,
    //                 'payments' => $r['payments'] ?? collect(),
    //                 'bank_detail' => $bankDetail,
    //                 'partner_name' => $r['partner_name'] ?? null,
    //                 'share_percentage' => $r['share_percentage'] ?? null,
    //                 'totals' => $r['totals'] ?? [],
    //             ];
    //         }
    //     }

    //     // STEP 2: Merge by bank_name (keep existing "manager bank" behavior)
    //     $mergedBanks = [];
    //     foreach ($byBankId as $bankId => $row) {
    //         $bankName = $row['bank_name'] ?? 'Unknown Bank';
    //         if (!isset($mergedBanks[$bankName])) {
    //             $mergedBanks[$bankName] = [
    //                 'bank_name' => $bankName,
    //                 'bank_ids' => [],
    //                 'totals' => ['payable' => 0, 'kpra_inf' => 0, 'kpra_dept' => 0, 'it_inf' => 0, 'it_dept' => 0],
    //                 'newspapers' => [],
    //             ];
    //         }
    //         $mergedBanks[$bankName]['bank_ids'][] = (int) $bankId;
    //         foreach ($mergedBanks[$bankName]['totals'] as $k => $_) {
    //             $mergedBanks[$bankName]['totals'][$k] += (float) ($row['totals'][$k] ?? 0);
    //         }
    //         $mergedBanks[$bankName]['newspapers'] = collect($mergedBanks[$bankName]['newspapers'])
    //             ->merge($newspaperDataByBankName[$bankName] ?? [])
    //             ->all();
    //     }

    //     // Attach agency totals by bank name (keep cheque behavior unchanged)
    //     $agencyTotalsByBankName = $agencyPayments
    //         ->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank')
    //         ->map(fn($rows) => $rows->sum('net_dues'));

    //     $bankWiseData = [];
    //     $overallTotals = [
    //         'payable' => 0,
    //         'kpra_inf' => 0,
    //         'kpra_dept' => 0,
    //         'it_inf' => 0,
    //         'it_dept' => 0,
    //         'agency_payable' => 0,
    //     ];

    //     // Include agency taxes in overall totals too
    //     $overallTotals['kpra_inf']  += $agencyPayments->sum('kpra_inf');
    //     $overallTotals['kpra_dept'] += $agencyPayments->sum('kpra_department');
    //     $overallTotals['it_inf']    += $agencyPayments->sum('it_inf');
    //     $overallTotals['it_dept']   += $agencyPayments->sum('it_department');

    //     // STEP 3: Process merged data
    //     foreach ($mergedBanks as $bankName => $bankData) {

    //         $bankTotals = [
    //             'payable' => 0,
    //             'kpra_inf' => 0,
    //             'kpra_dept' => 0,
    //             'it_inf' => 0,
    //             'it_dept' => 0,
    //         ];
    //         foreach ($bankTotals as $k => $_) {
    //             $bankTotals[$k] = (float) ($bankData['totals'][$k] ?? 0);
    //             $overallTotals[$k] += $bankTotals[$k];
    //         }

    //         $newspaperData = $bankData['newspapers'] ?? [];

    //         $agencyPayable = (float) ($agencyTotalsByBankName[$bankName] ?? 0);
    //         $bankTotals['agency_payable'] = $agencyPayable;
    //         $bankTotals['payable'] += $agencyPayable;
    //         $overallTotals['payable'] += $agencyPayable;
    //         $overallTotals['agency_payable'] += $agencyPayable;

    //         // FINAL OUTPUT (ONE ROW PER BANK NAME)
    //         $bankWiseData[] = [
    //             'bank_name' => $bankName,
    //             'bank_ids' => $bankData['bank_ids'], // 🔥 important for backend
    //             'newspapers' => $newspaperData,
    //             'totals' => $bankTotals,
    //         ];
    //     }

    //     // Sort by bank name
    //     usort($bankWiseData, fn($a, $b) => strcmp($a['bank_name'], $b['bank_name']));

    //     $kpraTotal = (float) ($overallTotals['kpra_inf'] + $overallTotals['kpra_dept']);
    //     $fbrTotal  = (float) ($overallTotals['it_inf'] + $overallTotals['it_dept']);
    //     $grandTotal = (float) ($overallTotals['payable'] + $kpraTotal + $fbrTotal);

    //     return view('payment-newspapers.pay-order-list', compact(
    //         'bankWiseData',
    //         'overallTotals',
    //         'kpraPayee',
    //         'fbrPayee',
    //         'kpraTotal',
    //         'fbrTotal',
    //         'grandTotal'
    //     ));
    // }

    // new code with filteration applied
    public function payOrderList(Request $request)
    {
        $payments = Payment::unpaid()
            ->with(['newspaper', 'mediaBankDetail'])
            ->paymentFilter($request)
            ->get();

        $agencyPayments = AgencyPayment::unpaid()
            ->with(['mediaBankDetail'])
            ->get();

        // ... rest of the method unchanged (splitting and grouping) ...
        // KPRA / FBR bank details (from tax_payees)
        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee  = TaxPayee::where('type', 'fbr')->first();

        // STEP 1: Split newspaper totals by partners and bucket by bank_detail_id
        $byBankId = [];
        $newspaperDataByBankName = [];

        $paymentsByNewspaper = $payments->groupBy('newspaper_id');
        foreach ($paymentsByNewspaper as $newspaperId => $newspaperPayments) {
            $splitRows = $this->splitNewspaperTotalsByPartners($newspaperPayments, ['payable', 'kpra_inf', 'kpra_dept', 'it_inf', 'it_dept']);
            foreach ($splitRows as $r) {
                $bankDetail = $r['bank_detail'] ?? null;
                $bankId = (int) ($bankDetail?->id ?? 0);
                $bankName = $bankDetail?->bank_name ?? ($r['bank_name'] ?? 'Unknown Bank');
                if ($bankId < 1) {
                    continue;
                }
                if (!isset($byBankId[$bankId])) {
                    $byBankId[$bankId] = [
                        'bank_id' => $bankId,
                        'bank_name' => $bankName,
                        'bank_detail' => $bankDetail,
                        'totals' => ['payable' => 0, 'kpra_inf' => 0, 'kpra_dept' => 0, 'it_inf' => 0, 'it_dept' => 0],
                    ];
                }
                foreach ($byBankId[$bankId]['totals'] as $k => $_) {
                    $byBankId[$bankId]['totals'][$k] += (float) ($r['totals'][$k] ?? 0);
                }

                // For debugging/optional UI use: keep per-bank-name newspaper rows (not used by blade right now)
                if (!isset($newspaperDataByBankName[$bankName])) {
                    $newspaperDataByBankName[$bankName] = [];
                }
                $newspaperDataByBankName[$bankName][] = [
                    'newspaper' => $r['newspaper'] ?? null,
                    'payments' => $r['payments'] ?? collect(),
                    'bank_detail' => $bankDetail,
                    'partner_name' => $r['partner_name'] ?? null,
                    'share_percentage' => $r['share_percentage'] ?? null,
                    'totals' => $r['totals'] ?? [],
                ];
            }
        }

        // STEP 2: Merge by bank_name (keep existing "manager bank" behavior)
        $mergedBanks = [];
        foreach ($byBankId as $bankId => $row) {
            $bankName = $row['bank_name'] ?? 'Unknown Bank';
            if (!isset($mergedBanks[$bankName])) {
                $mergedBanks[$bankName] = [
                    'bank_name' => $bankName,
                    'bank_ids' => [],
                    'totals' => ['payable' => 0, 'kpra_inf' => 0, 'kpra_dept' => 0, 'it_inf' => 0, 'it_dept' => 0],
                    'newspapers' => [],
                ];
            }
            $mergedBanks[$bankName]['bank_ids'][] = (int) $bankId;
            foreach ($mergedBanks[$bankName]['totals'] as $k => $_) {
                $mergedBanks[$bankName]['totals'][$k] += (float) ($row['totals'][$k] ?? 0);
            }
            $mergedBanks[$bankName]['newspapers'] = collect($mergedBanks[$bankName]['newspapers'])
                ->merge($newspaperDataByBankName[$bankName] ?? [])
                ->all();
        }

        // Attach agency totals by bank name (keep cheque behavior unchanged)
        $agencyTotalsByBankName = $agencyPayments
            ->groupBy(fn($p) => $p->mediaBankDetail?->bank_name ?? 'Unknown Bank')
            ->map(fn($rows) => $rows->sum('net_dues'));

        $bankWiseData = [];
        $overallTotals = [
            'payable' => 0,
            'kpra_inf' => 0,
            'kpra_dept' => 0,
            'it_inf' => 0,
            'it_dept' => 0,
            'agency_payable' => 0,
        ];

        // Include agency taxes in overall totals too
        $overallTotals['kpra_inf']  += $agencyPayments->sum('kpra_inf');
        $overallTotals['kpra_dept'] += $agencyPayments->sum('kpra_department');
        $overallTotals['it_inf']    += $agencyPayments->sum('it_inf');
        $overallTotals['it_dept']   += $agencyPayments->sum('it_department');

        // STEP 3: Process merged data
        foreach ($mergedBanks as $bankName => $bankData) {

            $bankTotals = [
                'payable' => 0,
                'kpra_inf' => 0,
                'kpra_dept' => 0,
                'it_inf' => 0,
                'it_dept' => 0,
            ];
            foreach ($bankTotals as $k => $_) {
                $bankTotals[$k] = (float) ($bankData['totals'][$k] ?? 0);
                $overallTotals[$k] += $bankTotals[$k];
            }

            $newspaperData = $bankData['newspapers'] ?? [];

            $agencyPayable = (float) ($agencyTotalsByBankName[$bankName] ?? 0);
            $bankTotals['agency_payable'] = $agencyPayable;
            $bankTotals['payable'] += $agencyPayable;
            $overallTotals['payable'] += $agencyPayable;
            $overallTotals['agency_payable'] += $agencyPayable;

            // FINAL OUTPUT (ONE ROW PER BANK NAME)
            $bankWiseData[] = [
                'bank_name' => $bankName,
                'bank_ids' => $bankData['bank_ids'], // 🔥 important for backend
                'newspapers' => $newspaperData,
                'totals' => $bankTotals,
            ];
        }

        // Sort by bank name
        usort($bankWiseData, fn($a, $b) => strcmp($a['bank_name'], $b['bank_name']));

        $kpraTotal = (float) ($overallTotals['kpra_inf'] + $overallTotals['kpra_dept']);
        $fbrTotal  = (float) ($overallTotals['it_inf'] + $overallTotals['it_dept']);
        $grandTotal = (float) ($overallTotals['payable'] + $kpraTotal + $fbrTotal);

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Payments', 'url' => route('payment.newspapers.index')],
            ['label' => 'Pay Order List', 'url' => null],
        ];

        return view('payment-newspapers.pay-order-list', compact(
            'bankWiseData',
            'overallTotals',
            'kpraPayee',
            'fbrPayee',
            'kpraTotal',
            'fbrTotal',
            'grandTotal',
            'breadcrumbs'
        ));
    }

    // new view govrnment cheques inclided agency amount now
    public function viewGovCheque(Request $request)
    {
        // Tax cheque (KPRA / FBR) — no bank_ids needed
        if ($request->filled('tax_type') && in_array($request->tax_type, ['kpra', 'fbr'], true)) {
            $taxType = $request->tax_type;
            $taxPayee = TaxPayee::where('type', $taxType)->first();

            if (!$taxPayee) {
                abort(404, 'Tax payee not found');
            }

            $payments = collect();
            $agencyPayments = collect();

            if ($taxType === 'kpra') {
                $totalAmount = (float) (
                    Payment::unpaid()->sum('kpra_inf') + Payment::unpaid()->sum('kpra_department') +
                    AgencyPayment::unpaid()->sum('kpra_inf') + AgencyPayment::unpaid()->sum('kpra_department')
                );
            } else {
                $totalAmount = (float) (
                    Payment::unpaid()->sum('it_inf') + Payment::unpaid()->sum('it_department') +
                    AgencyPayment::unpaid()->sum('it_inf') + AgencyPayment::unpaid()->sum('it_department')
                );
            }

            return view('payment-newspapers.view-gov-cheque', compact('payments', 'agencyPayments', 'totalAmount', 'taxPayee'));
        }

        $bankIds = array_values(array_filter(array_map('intval', explode(',', (string) $request->bank_ids))));

        // dd($bankIds);

        // For cheque: total must match payOrderList bank split totals.
        // Build the set of relevant unpaid payments (including partner newspapers whose partner bank is in bankIds).
        $allPayments = Payment::unpaid()->with(['newspaper', 'mediaBankDetail'])->get();

        // Quick map: active partners grouped by newspaper
        $npIds = $allPayments->pluck('newspaper_id')->filter()->unique()->values()->all();
        $partnersByNewspaper = \App\Models\NewspaperPartner::with('mediaBankDetail')
            ->whereIn('newspaper_id', $npIds)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('newspaper_id');

        $relevantNewspaperIds = [];
        $payableSplitTotal = 0.0;

        foreach ($allPayments->groupBy('newspaper_id') as $newspaperId => $npPayments) {
            $newspaperId = (int) $newspaperId;
            if ($newspaperId < 1) continue;

            $partners = $partnersByNewspaper[$newspaperId] ?? collect();
            if ($partners->isNotEmpty()) {
                // Split net_dues across partners and include only the parts whose bank_detail_id is in bankIds
                $payable = (float) $npPayments->sum('net_dues');
                $allocated = 0.0;
                $count = $partners->count();
                foreach ($partners as $idx => $partner) {
                    $bankId = (int) ($partner->media_bank_detail_id ?? 0);
                    $pct = (float) ($partner->share_percentage ?? 0);
                    $piece = $idx === $count - 1 ? round($payable - $allocated, 2) : round($payable * ($pct / 100), 2);
                    if ($idx !== $count - 1) $allocated += $piece;

                    if (in_array($bankId, $bankIds, true)) {
                        $payableSplitTotal += $piece;
                        $relevantNewspaperIds[] = $newspaperId;
                    }
                }
            } else {
                // No partners: include if the newspaper's payment bank_id is in bankIds
                $bankId = (int) ($npPayments->first()?->media_bank_detail_id ?? 0);
                if (in_array($bankId, $bankIds, true)) {
                    $payableSplitTotal += (float) $npPayments->sum('net_dues');
                    $relevantNewspaperIds[] = $newspaperId;
                }
            }
        }

        $relevantNewspaperIds = array_values(array_unique($relevantNewspaperIds));
        $payments = $allPayments->whereIn('newspaper_id', $relevantNewspaperIds)->values();

        // dd($payments);
        // Get batch nos from payments for agency payments (keep existing behavior)
        $batchNos = $payments->pluck('batch_no')->unique();

        // Get agency payments by batch nos
        $agencyPayments = AgencyPayment::whereIn('batch_no', $batchNos)
            ->with(['agency', 'mediaBankDetail'])
            ->get();
        // dd($agencyPayments);

        if ($payments->isEmpty() && $agencyPayments->isEmpty()) {
            abort(404, 'No payments found for this bank');
        }

        // Final total (match pay-order list bank split payable)
        $payable = (float) $payableSplitTotal + (float) $agencyPayments->sum('net_dues');
        $kpraTotal = $payments->sum('kpra_inf') + $payments->sum('kpra_department')
            + $agencyPayments->sum('kpra_inf') + $agencyPayments->sum('kpra_department');
        $fbrTotal = $payments->sum('it_inf') + $payments->sum('it_department')
            + $agencyPayments->sum('it_inf') + $agencyPayments->sum('it_department');

        // $totalAmount = (float) ($payable + $kpraTotal + $fbrTotal);
        $totalAmount = (float) $payable;
        // dd($totalAmount);

        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Payments', 'url' => route('payment.newspapers.index')],
            ['label' => 'Government Cheque', 'url' => null],
        ];

        return view('payment-newspapers.view-gov-cheque', compact(
            'payments',
            'agencyPayments',
            'totalAmount',
            'breadcrumbs'
        ));
    }

    public function downloadGovChequePdf(Request $request)
    {
        // Tax cheque (KPRA / FBR)
        if ($request->filled('tax_type') && in_array($request->tax_type, ['kpra', 'fbr'], true)) {
            $taxType = $request->tax_type;
            $taxPayee = TaxPayee::where('type', $taxType)->first();
            if (!$taxPayee) {
                abort(404, 'Tax payee not found');
            }

            $payments = collect();
            $agencyPayments = collect();

            if ($taxType === 'kpra') {
                $totalAmount = (float) (
                    Payment::unpaid()->sum('kpra_inf') + Payment::unpaid()->sum('kpra_department') +
                    AgencyPayment::unpaid()->sum('kpra_inf') + AgencyPayment::unpaid()->sum('kpra_department')
                );
            } else {
                $totalAmount = (float) (
                    Payment::unpaid()->sum('it_inf') + Payment::unpaid()->sum('it_department') +
                    AgencyPayment::unpaid()->sum('it_inf') + AgencyPayment::unpaid()->sum('it_department')
                );
            }

            $pdf = Pdf::loadView('exports.payment_newspapers_gov_cheque_pdf', [
                'payments' => $payments,
                'agencyPayments' => $agencyPayments,
                'totalAmount' => $totalAmount,
                'taxPayee' => $taxPayee,
                'generatedAt' => now(),
            ])->setPaper('A4', 'landscape');

            return $pdf->download('gov-cheque-' . $taxType . '-' . date('Y-m-d') . '.pdf');
        }

        // Bank cheque
        $bankIds = array_values(array_filter(array_map('intval', explode(',', (string) $request->bank_ids))));

        $allPayments = Payment::unpaid()->with(['newspaper', 'mediaBankDetail'])->get();

        $npIds = $allPayments->pluck('newspaper_id')->filter()->unique()->values()->all();
        $partnersByNewspaper = \App\Models\NewspaperPartner::with('mediaBankDetail')
            ->whereIn('newspaper_id', $npIds)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('newspaper_id');

        $relevantNewspaperIds = [];
        $payableSplitTotal = 0.0;

        foreach ($allPayments->groupBy('newspaper_id') as $newspaperId => $npPayments) {
            $newspaperId = (int) $newspaperId;
            if ($newspaperId < 1) continue;

            $partners = $partnersByNewspaper[$newspaperId] ?? collect();
            if ($partners->isNotEmpty()) {
                $payable = (float) $npPayments->sum('net_dues');
                $allocated = 0.0;
                $count = $partners->count();
                foreach ($partners as $idx => $partner) {
                    $bankId = (int) ($partner->media_bank_detail_id ?? 0);
                    $pct = (float) ($partner->share_percentage ?? 0);
                    $piece = $idx === $count - 1 ? round($payable - $allocated, 2) : round($payable * ($pct / 100), 2);
                    if ($idx !== $count - 1) $allocated += $piece;

                    if (in_array($bankId, $bankIds, true)) {
                        $payableSplitTotal += $piece;
                        $relevantNewspaperIds[] = $newspaperId;
                    }
                }
            } else {
                $bankId = (int) ($npPayments->first()?->media_bank_detail_id ?? 0);
                if (in_array($bankId, $bankIds, true)) {
                    $payableSplitTotal += (float) $npPayments->sum('net_dues');
                    $relevantNewspaperIds[] = $newspaperId;
                }
            }
        }

        $relevantNewspaperIds = array_values(array_unique($relevantNewspaperIds));
        $payments = $allPayments->whereIn('newspaper_id', $relevantNewspaperIds)->values();

        $batchNos = $payments->pluck('batch_no')->unique();
        $agencyPayments = AgencyPayment::whereIn('batch_no', $batchNos)
            ->with(['agency', 'mediaBankDetail'])
            ->get();

        if ($payments->isEmpty() && $agencyPayments->isEmpty()) {
            abort(404, 'No payments found for this bank');
        }

        $payable = (float) $payableSplitTotal + (float) $agencyPayments->sum('net_dues');
        $totalAmount = (float) $payable;

        $pdf = Pdf::loadView('exports.payment_newspapers_gov_cheque_pdf', [
            'payments' => $payments,
            'agencyPayments' => $agencyPayments,
            'totalAmount' => $totalAmount,
            'generatedAt' => now(),
        ])->setPaper('A4', 'landscape');

        return $pdf->download('gov-cheque-' . date('Y-m-d') . '.pdf');
    }

    // Veiw government Cheques
    // public function viewGovCheque(Request $request)
    // {
    //     $bankIds = explode(',', $request->bank_ids);
    //     // $payments = Payment::with(['newspaper', 'mediaBankDetail'])
    //     //     ->where('media_bank_detail_id', $bankId)
    //     //     ->get();

    //     $payments = Payment::whereIn('media_bank_detail_id', $bankIds)
    //         ->with(['newspaper', 'mediaBankDetail'])
    //         ->get();

    //     if ($payments->isEmpty()) {
    //         abort(404, 'No payments found for this bank');
    //     }

    //     return view('payment-newspapers.view-gov-cheque', compact('payments'));
    // }
    // public function viewGovCheque($id)
    // {
    //     // $treasuryChallan = TreasuryChallan::findOrFail($id);
    //     // $totalAmount = $treasuryChallan->total_amount;
    //     // $roundedAmount = round($totalAmount);
    //     // $finalAmount = floor($roundedAmount);

    //     // // Create formatter
    //     // $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
    //     // // Convert to words
    //     // $rupeesWords = ucfirst($formatter->format($finalAmount)) . ' rupees only.';


    //     return view('payment-newspapers.view-gov-cheque', [
    //         // 'treasuryChallan' => $treasuryChallan,
    //         // 'finalAmount' => $finalAmount,
    //         // 'rupeesWords' => $rupeesWords,

    //     ]);
    // }


    // public function amountPaidForNewspaper($id)
    // {
    //     $newspaper = Newspaper::find($id);
    //     return view('payment-newspapers.amount-paid-for-newspaper', compact('newspaper'));
    // }

    // -----------------------------------------------------------------------
    // Paid Amount Tracking Module
    // -----------------------------------------------------------------------

    // old code
    public function paidAmount()
    {
        $batchNos = Payment::whereNotNull('batch_no')->distinct()->pluck('batch_no')
            ->merge(AgencyPayment::whereNotNull('batch_no')->distinct()->pluck('batch_no'))
            ->unique()->sort()->values();

        $batchList = $batchNos->values();
        $lastBatchNo = $batchList->last(); // ✅ ONLY LAST BATCH

        $kpraPayee = TaxPayee::where('type', 'kpra')->first();
        $fbrPayee  = TaxPayee::where('type', 'fbr')->first();

        $kpraPayeeId = $kpraPayee?->id;
        $fbrPayeeId  = $fbrPayee?->id;

        $pendingBatches = [];

        $putRow = function (&$rowsMap, string $ledgerBatch, bool $carry, array $base) {
            $pid = $base['payee_id'] ?? null;
            $key = $ledgerBatch . '|' . $base['payee_type'] . '|' . ($pid ?? '');

            $rowsMap[$key] = array_merge($base, [
                'ledger_batch_no' => $ledgerBatch,
                'carry_forward'   => $carry,
            ]);
        };

        $appendKpraFbrRows = function (string $ledgerBatch, bool $carry) use (
            &$rowsMap,
            $putRow,
            $kpraPayeeId,
            $fbrPayeeId,
            $kpraPayee,
            $fbrPayee
        ) {
            if ($kpraPayeeId && $kpraPayee) {
                $kpraAmount = (float) Payment::where('batch_no', $ledgerBatch)
                    ->selectRaw('COALESCE(SUM(kpra_inf), 0) + COALESCE(SUM(kpra_department), 0) as t')
                    ->value('t')
                    + (float) AgencyPayment::where('batch_no', $ledgerBatch)
                        ->selectRaw('COALESCE(SUM(kpra_inf), 0) + COALESCE(SUM(kpra_department), 0) as t')
                        ->value('t');

                if ($kpraAmount > 0 && !PaidAmount::isLedgerPaid($ledgerBatch, 'kpra', $kpraPayeeId)) {
                    $putRow($rowsMap, $ledgerBatch, $carry, [
                        'payee_id'             => $kpraPayeeId,
                        'payee_type'           => 'kpra',
                        'payee_name'           => $kpraPayee->description ?? 'KPRA',
                        'media_bank_detail_id' => null,
                        'bank_name'            => $kpraPayee->bank_name ?? 'N/A',
                        'account_number'       => $kpraPayee->account_number ?? '',
                        'amount'               => $kpraAmount,
                    ]);
                }
            }

            if ($fbrPayeeId && $fbrPayee) {
                $fbrAmount = (float) Payment::where('batch_no', $ledgerBatch)
                    ->selectRaw('COALESCE(SUM(it_inf), 0) + COALESCE(SUM(it_department), 0) as t')
                    ->value('t')
                    + (float) AgencyPayment::where('batch_no', $ledgerBatch)
                        ->selectRaw('COALESCE(SUM(it_inf), 0) + COALESCE(SUM(it_department), 0) as t')
                        ->value('t');

                if ($fbrAmount > 0 && !PaidAmount::isLedgerPaid($ledgerBatch, 'fbr', $fbrPayeeId)) {
                    $putRow($rowsMap, $ledgerBatch, $carry, [
                        'payee_id'             => $fbrPayeeId,
                        'payee_type'           => 'fbr',
                        'payee_name'           => $fbrPayee->description ?? 'FBR',
                        'media_bank_detail_id' => null,
                        'bank_name'            => $fbrPayee->bank_name ?? 'N/A',
                        'account_number'       => $fbrPayee->account_number ?? '',
                        'amount'               => $fbrAmount,
                    ]);
                }
            }
        };

        $reversedKeys = PaidAmount::where('status', 'reversed')
            ->get()
            ->map(function ($p) {
                return $p->ledger_batch_no . '|' . $p->payee_type . '|' . $p->payee_id;
            })
            ->flip()
            ->toArray();

        foreach ($batchList as $idx => $batchNo) {

            $rowsMap = [];

            // ================== PREVIOUS (Carry Forward) ==================
            for ($i = 0; $i < $idx; $i++) {
                $bp = $batchList[$i];

                $this->appendNewspaperPayeeRowsForLedgerBatch($bp, true, $rowsMap, $putRow);

                // old code
                AgencyPayment::with(['agency', 'mediaBankDetail'])
                    ->where('batch_no', $bp)
                    ->get()
                    ->groupBy('agency_id')
                    ->each(function ($group, $agencyId) use ($bp, &$rowsMap, $putRow) {

                        if (PaidAmount::isLedgerPaid($bp, 'agency', $agencyId)) return;

                        $first = $group->first();
                        $bank  = $first->mediaBankDetail;

                        $putRow($rowsMap, $bp, true, [
                            'payee_id' => $agencyId,
                            'payee_type' => 'agency',
                            'payee_name' => optional($first->agency)->name ?? 'Unknown Agency',
                            'media_bank_detail_id' => $bank?->id,
                            'bank_name' => $bank?->bank_name ?? 'N/A',
                            'account_number' => $bank?->account_number ?? '',
                            'amount' => $group->sum('net_dues'),
                        ]);
                    });


                // old code
                $appendKpraFbrRows($bp, true);
            }

            // ================== CURRENT BATCH ==================
            $this->appendNewspaperPayeeRowsForLedgerBatch($batchNo, false, $rowsMap, $putRow);
            // new code
            // $this->appendNewspaperPayeeRowsForLedgerBatch(
            //     $batchNo,
            //     false,
            //     $rowsMap,
            //     $putRow,
            //     $reversedKeys
            // );

            // old code
            AgencyPayment::with(['agency', 'mediaBankDetail'])
                ->where('batch_no', $batchNo)
                ->get()
                ->groupBy('agency_id')
                ->each(function ($group, $agencyId) use ($batchNo, &$rowsMap, $putRow) {

                    if (PaidAmount::isLedgerPaid($batchNo, 'agency', $agencyId)) return;

                    $first = $group->first();
                    $bank  = $first->mediaBankDetail;

                    $putRow($rowsMap, $batchNo, false, [
                        'payee_id' => $agencyId,
                        'payee_type' => 'agency',
                        'payee_name' => optional($first->agency)->name ?? 'Unknown Agency',
                        'media_bank_detail_id' => $bank?->id,
                        'bank_name' => $bank?->bank_name ?? 'N/A',
                        'account_number' => $bank?->account_number ?? '',
                        'amount' => $group->sum('net_dues'),
                    ]);
                });



            // old code
            $appendKpraFbrRows($batchNo, false);

            // ✅ ONLY LAST BATCH WILL BE SHOWN
            if ($batchNo === $lastBatchNo && !empty($rowsMap)) {
                $pendingBatches[$batchNo] = array_values($rowsMap);
            }
        }




        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Payments', 'url' => route('payment.newspapers.index')],
            ['label' => 'Pay Amount', 'url' => null],
        ];

        return view('payment-newspapers.paid-amount', compact('pendingBatches', 'breadcrumbs'));
    }



    public function storePaidAmount(Request $request)
    {
        $validBatches = Payment::whereNotNull('batch_no')->distinct()->pluck('batch_no')
            ->merge(AgencyPayment::whereNotNull('batch_no')->distinct()->pluck('batch_no'))
            ->unique()
            ->values()
            ->all();

        $request->validate([
            'batches'                         => 'required|array|min:1',
            'batches.*.batch_no'              => 'required|string',
            'batches.*.rows'                  => 'required|array|min:1',
            'batches.*.rows.*.payee_id'       => 'nullable|integer',
            'batches.*.rows.*.payee_type'     => 'required|string|in:newspaper,newspaper_partner,agency,kpra,fbr',
            'batches.*.rows.*.ledger_batch_no' => ['required', 'string', 'max:30', Rule::in($validBatches)],
            'batches.*.rows.*.media_bank_detail_id' => 'nullable|integer|exists:media_bank_details,id',
            'batches.*.rows.*.removed'        => 'nullable|in:0,1',
            // cheque fields are required only for non-removed rows (enforced below)
            'batches.*.rows.*.cheque_no'      => 'nullable|string|max:100',
            'batches.*.rows.*.cheque_date'    => 'nullable|date',
            // 'batches.*.rows.*.paid_amount'    => 'required|numeric|min:0',
            'batches.*.rows.*.amount'         => 'required|numeric|min:0',
        ]);

        // Enforce cheque fields for rows that are not removed.
        foreach ((array) $request->batches as $bIdx => $batch) {
            foreach ((array) ($batch['rows'] ?? []) as $rIdx => $row) {
                $removed = (string) ($row['removed'] ?? '0') === '1';
                if ($removed) {
                    continue;
                }
                if (empty($row['cheque_no']) || empty($row['cheque_date'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "batches.$bIdx.rows.$rIdx.cheque_no"   => ['Cheque no is required.'],
                        "batches.$bIdx.rows.$rIdx.cheque_date" => ['Cheque date is required.'],
                    ]);
                }
            }
        }

        DB::beginTransaction();

        try {
            $savedBatchNos = [];

            foreach ($request->batches as $batch) {
                $batchNo = $batch['batch_no'];

                foreach ($batch['rows'] as $row) {
                    $ledgerBatch = $row['ledger_batch_no'] ?? $batchNo;
                    $isRemoved = (string) ($row['removed'] ?? '0') === '1';

                    PaidAmount::updateOrCreate(
                        [
                            'ledger_batch_no' => $ledgerBatch,
                            'payee_type'      => $row['payee_type'],
                            'payee_id'        => $row['payee_id'] ?? null,
                        ],
                        [
                            'batch_no'             => $batchNo,
                            'media_bank_detail_id' => $row['media_bank_detail_id'] ?? null,
                            // Removed rows are treated as "reversed" so they don't reappear in unpaid listings/summaries.
                            'cheque_no'            => $isRemoved ? null : ($row['cheque_no'] ?? null),
                            'cheque_date'          => $isRemoved ? null : ($row['cheque_date'] ?? null),
                            'amount'               => $isRemoved ? 0 : $row['amount'],
                            'status'               => $isRemoved ? 'reversed' : 'paid',
                        ]
                    );
                }

                $savedBatchNos[] = $batchNo;
            }

            DB::commit();

            $list = implode(', ', array_unique($savedBatchNos));

            return redirect()
                ->route('payment.newspapers.paid-amount.history')
                ->with('success', 'Payments saved for batch(es): ' . $list);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }


    // old code
    // public function paidAmountHistory()
    // {
    //     $history = PaidAmount::with(['mediaBankDetail', 'newspaperPartner.newspaper'])
    //         ->orderByDesc('created_at')
    //         ->get();

    //     return view('payment-newspapers.paid-amount-history', compact('history'));
    // }

    // new code with filteration applied
    public function paidAmountHistory(Request $request)
    {
        $query = PaidAmount::with(['mediaBankDetail', 'newspaperPartner.newspaper'])
            ->orderByDesc('created_at')
            ->paidAmountFilter($request);   // apply filters

        $history = $query->paginate(10)->appends($request->query());

        $banks = PaidAmount::join('media_bank_details', 'paid_amounts.media_bank_detail_id', '=', 'media_bank_details.id')
            ->select('media_bank_details.bank_name')
            ->distinct()
            ->orderBy('media_bank_details.bank_name')
            ->pluck('media_bank_details.bank_name');

        // $newspapersName = PaidAmount::where('payee_type', 'newspaper')->join('newspapers', 'paid_amounts.payee_id', '=', 'newspapers.id')
        //     ->select('newspapers.title')
        //     ->distinct()
        //     ->orderBy('newspapers.title')
        //     ->pluck('newspapers.title');

        // this code is with newspaper and newspaper partner both involved
        $directNewspapers = PaidAmount::where('payee_type', 'newspaper')
            ->join('newspapers', 'paid_amounts.payee_id', '=', 'newspapers.id')
            ->select('newspapers.title');

        $partnerNewspapers = PaidAmount::where('payee_type', 'newspaper_partner')
            ->join('newspaper_partners', 'paid_amounts.payee_id', '=', 'newspaper_partners.id')
            ->join('newspapers', 'newspaper_partners.newspaper_id', '=', 'newspapers.id')
            ->select('newspapers.title');

        $newspapersName = $directNewspapers->union($partnerNewspapers)
            ->distinct()
            ->orderBy('title')
            ->pluck('title');

        $advAgenceisName = PaidAmount::where('payee_type', 'agency')->join('adv_agencies', 'paid_amounts.payee_id', '=', 'adv_agencies.id')
            ->select('adv_agencies.name')
            ->distinct()
            ->orderBy('adv_agencies.name')
            ->pluck('adv_agencies.name');


        $breadcrumbs = [
            ['label' => '<i class="menu-icon tf-icons bx bx-home-circle"></i>', 'url' => route('dashboard')],
            ['label' => 'Payments', 'url' => route('payment.newspapers.index')],
            ['label' => 'Paid Amount History', 'url' => null],
        ];

        return view('payment-newspapers.paid-amount-history', compact('history', 'banks', 'newspapersName', 'advAgenceisName', 'breadcrumbs'));
    }

    private function buildPaidAmountHistoryQuery(Request $request)
    {
        return PaidAmount::with(['mediaBankDetail', 'newspaper', 'newspaperPartner.newspaper', 'agency'])
            ->orderByDesc('created_at')
            ->paidAmountFilter($request);
    }

    public function exportPaidAmountHistoryExcel(Request $request)
    {
        $history = $this->buildPaidAmountHistoryQuery($request)->get();

        $typeLabels = [
            'newspaper' => 'NP',
            'newspaper_partner' => 'NP partner',
            'agency' => 'Agency',
            'kpra' => 'KPRA',
            'fbr' => 'FBR',
        ];

        $rows = $history->map(function ($entry) use ($typeLabels) {
            return [
                'S. No.' => '',
                'Batch No' => $entry->batch_no ?? '',
                'Payee Name' => $entry->payee_name ?? '',
                'Type' => $typeLabels[$entry->payee_type] ?? ($entry->payee_type ?? ''),
                'Bank' => $entry->mediaBankDetail?->bank_name ?? '—',
                'Cheque No' => $entry->cheque_no ?? '—',
                'Amount Paid (Rs)' => $entry->amount !== null ? number_format((float) $entry->amount, 0) : '—',
                'Total Amount (Rs)' => $entry->amount !== null ? number_format((float) $entry->amount, 0) : '—',
                'Status' => $entry->status ?? '',
                'Cheque Date' => $entry->created_at ? $entry->created_at->format('d M Y') : '',
            ];
        })->toArray();

        foreach ($rows as $i => &$r) {
            $r['S. No.'] = $i + 1;
        }

        return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private array $rows) {}
            public function collection()
            {
                return collect($this->rows);
            }
            public function headings(): array
            {
                return array_keys($this->rows[0] ?? []);
            }
        }, 'paid-amount-history-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPaidAmountHistoryPdf(Request $request)
    {
        $history = $this->buildPaidAmountHistoryQuery($request)->get();

        $pdf = Pdf::loadView('exports.payment_newspapers_paid_amount_history_pdf', [
            'history' => $history,
            'generatedAt' => now(),
        ])->setPaper('A4', 'landscape');

        return $pdf->download('paid-amount-history-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Build payee line(s) for newspaper net_dues: either one "newspaper" row (no partner setup)
     * or one row per active partner with split amounts (last partner absorbs rounding).
     */

    //  old function working good but shows reversed rowsn in paid amount ui
    private function appendNewspaperPayeeRowsForLedgerBatch(string $ledgerBatch, bool $carryForward, array &$rowsMap, callable $putRow): void
    {
        Payment::with(['newspaper', 'mediaBankDetail'])
            ->where('batch_no', $ledgerBatch)
            ->whereNotNull('newspaper_id')
            ->get()
            ->groupBy('newspaper_id')
            ->each(function ($group, $newspaperId) use ($ledgerBatch, &$rowsMap, $putRow, $carryForward) {

                // ✅ Skip reversed rows ONLY in current batch
                $group = $group->filter(function ($item) use ($carryForward) {
                    if ($carryForward) {
                        return true; // carry forward always include
                    }

                    return $item->status !== 'reversed'; // current batch exclude reversed
                });

                if (PaidAmount::isNewspaperPayeePaidForLedger($ledgerBatch, $newspaperId)) {
                    return;
                }

                $first = $group->first();
                $npTitle = optional($first->newspaper)->title ?? 'Unknown Newspaper';
                $totalNet = (float) $group->sum('net_dues');

                $partners = NewspaperPartner::activeForNewspaper($newspaperId)->with('mediaBankDetail')->get();

                if ($partners->isEmpty()) {
                    $bank = $first->mediaBankDetail;
                    $putRow($rowsMap, $ledgerBatch, $carryForward, [
                        'payee_id' => $newspaperId,
                        'payee_type' => 'newspaper',
                        'payee_name' => $npTitle,
                        'media_bank_detail_id' => $bank?->id,
                        'bank_name' => $bank?->bank_name ?? 'N/A',
                        'account_number' => $bank?->account_number ?? '',
                        'amount' => $totalNet,
                    ]);

                    return;
                }

                $count = $partners->count();
                $allocated = 0.0;
                foreach ($partners as $idx => $partner) {
                    if ($idx === $count - 1) {
                        $share = round($totalNet - $allocated, 2);
                    } else {
                        $piece = round($totalNet * ((float) $partner->share_percentage / 100.0), 2);
                        $allocated += $piece;
                        $share = $piece;
                    }

                    $bank = $partner->mediaBankDetail;
                    $pctLabel = rtrim(rtrim(number_format((float) $partner->share_percentage, 2, '.', ''), '0'), '.');

                    $putRow($rowsMap, $ledgerBatch, $carryForward, [
                        'payee_id' => $partner->id,
                        'payee_type' => 'newspaper_partner',
                        'payee_name' => $npTitle . ' — ' . $partner->partner_name . ' (' . $pctLabel . '%)',
                        'media_bank_detail_id' => $bank?->id,
                        'bank_name' => $bank?->bank_name ?? 'N/A',
                        'account_number' => $bank?->account_number ?? '',
                        'amount' => $share,
                    ]);
                }
            });
    }

    // new fucntion to hide deleted rows to show in paid amount seperately
    // private function appendNewspaperPayeeRowsForLedgerBatch(
    //     string $ledgerBatch,
    //     bool $carryForward,
    //     array &$rowsMap,
    //     callable $putRow,
    //     array $reversedKeys = []
    // ): void {
    //     Payment::with(['newspaper', 'mediaBankDetail'])
    //         ->where('batch_no', $ledgerBatch)
    //         ->whereNotNull('newspaper_id')
    //         ->get()
    //         ->groupBy('newspaper_id')
    //         ->each(function ($group, $newspaperId) use (
    //             $ledgerBatch,
    //             &$rowsMap,
    //             $putRow,
    //             $carryForward,
    //             $reversedKeys
    //         ) {

    //             // 🔥 ONLY FILTER REVERSED IN CURRENT BATCH
    //             if (!$carryForward) {

    //                 $group = $group->filter(function ($row) use ($ledgerBatch, $reversedKeys) {

    //                     $key = $ledgerBatch . '|newspaper|' . $row->newspaper_id;

    //                     return !isset($reversedKeys[$key]);
    //                 });
    //             }

    //             if ($group->isEmpty()) {
    //                 return;
    //             }

    //             // already fully paid → skip
    //             if (PaidAmount::isNewspaperPayeePaidForLedger($ledgerBatch, $newspaperId)) {
    //                 return;
    //             }

    //             $first = $group->first();
    //             $npTitle = optional($first->newspaper)->title ?? 'Unknown Newspaper';
    //             $totalNet = (float) $group->sum('net_dues');

    //             $partners = NewspaperPartner::activeForNewspaper($newspaperId)
    //                 ->with('mediaBankDetail')
    //                 ->get();

    //             // ================= SINGLE PAYEE =================
    //             if ($partners->isEmpty()) {

    //                 $bank = $first->mediaBankDetail;

    //                 $putRow($rowsMap, $ledgerBatch, $carryForward, [
    //                     'payee_id' => $newspaperId,
    //                     'payee_type' => 'newspaper',
    //                     'payee_name' => $npTitle,
    //                     'media_bank_detail_id' => $bank?->id,
    //                     'bank_name' => $bank?->bank_name ?? 'N/A',
    //                     'account_number' => $bank?->account_number ?? '',
    //                     'amount' => $totalNet,
    //                 ]);

    //                 return;
    //             }

    //             // ================= PARTNER SPLIT =================
    //             $count = $partners->count();
    //             $allocated = 0.0;

    //             foreach ($partners as $idx => $partner) {

    //                 if ($idx === $count - 1) {
    //                     $share = round($totalNet - $allocated, 2);
    //                 } else {
    //                     $piece = round($totalNet * ($partner->share_percentage / 100), 2);
    //                     $allocated += $piece;
    //                     $share = $piece;
    //                 }

    //                 $bank = $partner->mediaBankDetail;

    //                 $pctLabel = rtrim(rtrim(
    //                     number_format((float)$partner->share_percentage, 2, '.', ''),
    //                     '0'
    //                 ), '.');

    //                 $putRow($rowsMap, $ledgerBatch, $carryForward, [
    //                     'payee_id' => $partner->id,
    //                     'payee_type' => 'newspaper_partner',
    //                     'payee_name' => $npTitle . ' — ' . $partner->partner_name . " ({$pctLabel}%)",
    //                     'media_bank_detail_id' => $bank?->id,
    //                     'bank_name' => $bank?->bank_name ?? 'N/A',
    //                     'account_number' => $bank?->account_number ?? '',
    //                     'amount' => $share,
    //                 ]);
    //             }
    //         });
    // }
}
