<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Advertisement Bill - {{ $advertisement->inf_number ?? '' }}</title>
    <style>
        /* Basic print friendly styling */
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #222;
        }

        .header {
            display: flex;
            /* align-items: center; */
            /* vertically align logo and text */
            justify-content: flex-between;
            /* keep them aligned left */
            margin-bottom: 20px;
            text-align: center;
        }

        .logo {
            width: 60px;
            margin: 15px;
            display: inline-block;
            vertical-align: middle;
        }

        .title {
            font-size: 16px;
            font-weight: 700;
            color: #263f19;
            letter-spacing: 0.5px;
            line-height: 1.2;
            display: inline-block;
            vertical-align: middle;
        }

        .meta {
            margin: 8px 0 18px;
        }

        .meta .left {
            float: left;
            width: 60%;
        }

        .meta .right {
            float: right;
            width: 38%;
            text-align: right;
        }

        .meta .center {
            float: right;
            text-align: left;
            width: 60%;
            font-size: 15px;
        }

        .meta .left-memo {
            float: left;
            width: 100%;
            margin-top: 10px;
            text-align: justify;
        }

        .meta-signature {
            margin: 8px 0 0px;
        }

        .meta-signature .right {
            float: right;
            width: 38%;
            text-align: center;
        }

        .grandTotal {
            font-size: 12px;

        }

        .grandTotal .left {
            margin-top: -10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 7px;
        }

        th,
        td {
            padding: 8px 6px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f7f7f7;
            font-weight: 700;
        }

        .amount {
            text-align: right;
        }

        .footer {
            margin-top: 20px;
            font-size: 11px;
            color: #444;
        }

        .pending-row {
            background: #fff3cd;
        }

        /* yellow like table-warning */
        .text-center {
            text-align: center;
        }

        .clear {
            clear: both;
        }

        /* Page break rules if the list is long */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header">
        {{-- Use a server path (not asset()) — dompdf needs absolute path or base64 --}}
        @php
            $logoPath = public_path('assets/img/department-logo/kp-logo.png'); // put logo in public/images/logo.png
            $logoData = file_exists($logoPath)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                : null;
        @endphp

        @if ($logoData)
            <img class="logo" src="{{ $logoData }}" alt="logo">
        @endif


        <div class="title">DIRECTORATE GENERAL INFORMATION & PUBLIC RELATIONS, KHYBER PAKHTUNKHWA</div>

    </div>

    <div class="meta">
        <div class="left">
            <div>Bill No.<strong> INF/ADVT/{{ $advertisement->inf_number ?? '—' }}</strong> </div>
            <div>
                Dated:<strong>
                    {{ \Carbon\Carbon::parse($advertisement->created_at ?? now())->format('d/m/Y') }}</strong>
            </div>
        </div>
        <div class="right">
            <div>Office:<strong> {{ $advertisement->office->ddo_name ?? '—' }}</strong> </div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="meta">
        <div class="left">
            Subject:<strong> Advertisement Bill.</strong>
        </div>
        <div class="center">
            <strong>MOST IMMEDIATE | PRINT MEDIA BILL</strong>
        </div>
        <div class="clear"></div>

    </div>
    <div class="meta">
        <div class="left-memo">
            <strong>MEMO:</strong><br>
            A bill for the advertisement received from your Department/Office vide Memo/Endst No.02/M-5 dated
            {{ \Carbon\Carbon::parse($advertisement->memo_date ?? now())->format('d/m/Y') }} added below alongwith
            newspapers' cuttings of the advertisement published.
            <strong> KINDLY ARRANGE TO REMIT THE AMOUNT OF THE BILL TO THIS DEPARTMENT WITHIN 15 DAYS OF THE ISSUANCE OF
                THIS
                LETTER </strong>
            Payments should be forwarded by cross cheque or by bank draft in the name of <strong>DIRECTOR GENERAL
                INFORMATION AND PR's PESHAWAR</strong>. All charges for such remittances will be
            payable by the remitter.
            <strong> Our Order No. INF/ADVT/{{ $advertisement->inf_number ?? '—' }} dated
                {{ \Carbon\Carbon::parse($advertisement->created_at ?? now())->format('d/m/Y') }}</strong>.

        </div>
        <div class="clear"></div>
    </div>


    <h4>NEWSPAPERS</h4>

    <table>
        <thead>
            <tr>
                <th style="width:4%;">S#</th>
                <th style="width:40%;">Name of Newspaper</th>
                <th style="width:20%;">Category</th>
                <th style="width:12%;">Date Published</th>
                <th style="width:12%;">Status</th>
                <th style="width:12%;" class="amount">Amount Due</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; @endphp

            {{-- Show billed rows first --}}
            @foreach ($billdetails as $bill)
                @php
                    $no++;
                    $newspaperTitle = optional($bill->user)->newspaper->title ?? ($bill->newspaper->title ?? '—');
                    $category = optional($bill->advertisement->classified_ad_type)->type ?? '—';
                    $publicationDate = $bill->publication_date
                        ? \Carbon\Carbon::parse($bill->publication_date)->format('d/m/Y')
                        : '—';
                    $amount = $bill->printed_total_bill ?? 0;
                    $billStatus = $bill->status;
                    // $GrandTotaol += $totalAmount;
                @endphp
                <tr>
                    <td>{{ $no }}</td>
                    <td>{{ $newspaperTitle }}</td>
                    <td>{{ $category }}</td>
                    <td>{{ $publicationDate }}</td>
                    <td>{{ $billStatus }}</td>
                    <td class="amount">{{ number_format(round($amount)) }}</td>
                </tr>
            @endforeach

            {{-- Pending newspapers --}}
            {{-- @foreach ($pendingNewspapers as $pending)
                @php $no++; @endphp
                <tr class="pending-row">
                    <td>{{ $no }}</td>
                    <td>{{ $pending->title }}</td>
                    <td>{{ $advertisement->classified_ad_type->type ?? '—' }}</td>
                    <td>—</td>
                    <td class="amount">Pending</td>
                </tr>
            @endforeach --}}
        </tbody>
    </table>
    <div class="meta">
        <div class="left">
            <div style="margin-top:30px;"><strong>Grand Total: </strong>
                <span style="font-size: 14px;"> {{ number_format($grandTotal) }}</span>
            </div>
        </div>
        <div class="right">
            <div><strong>Sub Total:
                    <span style="font-size: 14px;"> {{ number_format($grandTotal) }}</span></strong>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="grandTotal">
        <div class="left"><strong>Amount In Words: </strong>
            <span>{{ $grandTotalWords }}</span>
        </div>
    </div>
    <div class="clear"></div>


    <div class="meta-signature" style="margin-top: 40px;">
        <div class="right">
            @if ($dd && $ddSignature)
                <img src="{{ $ddSignature }}" width="200px" style="display:block; margin-left:auto; margin-right:0;">
            @else
                <p><em>No signature found</em></p>
            @endif

            <div style="margin-top: 10px; font-weight: bold;">
                DEPUTY DIRECTOR (ADVT)
            </div>
        </div>

        <div class="clear"></div>
    </div>



    <div class="meta-signature">
        <div class="right">
            <div><strong> DIRECTOR GENERAL INFORMATION & PRs KHYBER PAKHTUNKHWA </strong>

            </div>
        </div>
        <div class="clear"></div>
    </div>


    <div class="footer">
        <p><strong>NOTE:</strong> <br> (1). System generated bills do not require Signature. <br>
            (2). Zero (0) amount in the bill denotes that the Newspaper concerned hasn't submitted the bill within the
            prescribed due date.<br>
            (3). 2% KPRA tax applied in accordance with Rule No. 6 of KPRA rules.<br>
            (4). Income tax @ 1.5% can be deducted by your office.
        </p>
    </div>

    <div class="text-center">

        {{-- Scanned Bill --}}
        {{-- <h4>Scanned Bill</h4> --}}

        {{-- @foreach ($scanned_bills as $mediaCollection)
            @foreach ($mediaCollection as $media)
                @if (Str::contains($media->mime_type, 'pdf'))
                    {{-- For PDF, we can't display as image, so we might just show a link? But in PDF, link might not work. Alternatively, we can skip PDFs in the PDF output? --}
                    <a href="{{ $media->getFullUrl() }}" target="_blank">View PDF</a>
                @else
                    @php
                        // For images, we convert to base64
                        $path = $media->getPath();
                        $base64 = 'data:' . $media->mime_type . ';base64,' . base64_encode(file_get_contents($path));
                    @endphp
                    <img src="{{ $base64 }}" width="520">
                @endif
            @endforeach
        @endforeach --}}

        {{-- Press Cutting --}}
        {{-- <h4>Press Cutting</h4> --}}

        @foreach ($press_cuttings as $billId => $mediaCollection)
            {{-- Show newspaper title(s) for this bill --}}
            <h2><strong>Newspaper:</strong> {{ implode(', ', $newspaperTitles[$billId] ?? []) }}</h2>

            @foreach ($mediaCollection as $media)
                @if (Str::contains($media->mime_type, 'pdf'))
                    {{-- For PDF, we can't display as image, so we might just show a link? But in PDF, link might not work. Alternatively, we can skip PDFs in the PDF output? --}}
                    <a href="{{ $media->getFullUrl() }}" target="_blank">View PDF</a>
                @else
                    @php
                        // For images, we convert to base64
                        $path = $media->getPath();
                        $base64 = 'data:' . $media->mime_type . ';base64,' . base64_encode(file_get_contents($path));
                    @endphp
                    <img src="{{ $base64 }}" width="420">
                @endif
            @endforeach
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
        @endforeach

    </div>
</body>

</html>
