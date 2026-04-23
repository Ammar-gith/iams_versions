<?php

use App\Http\Controllers\AdvAgencyController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BatchPaymentController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ChequeReceiptNpController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\masterData\AdCategoryController;
use App\Http\Controllers\MasterData\AdRejectionReasonController;
use App\Http\Controllers\MasterData\AdWorthParameterController;
use App\Http\Controllers\masterData\ClassifiedAdTypeController;
use App\Http\Controllers\MasterData\DepartmentCategoryController;
use App\Http\Controllers\MasterData\DepartmentController;
use App\Http\Controllers\MasterData\DistrictController;
use App\Http\Controllers\masterData\LanguageController;
use App\Http\Controllers\MasterData\NewsPosRateController;
use App\Http\Controllers\MasterData\OfficeCategoryController;
use App\Http\Controllers\MasterData\OfficeController;
use App\Http\Controllers\MasterData\ProvinceController;
use App\Http\Controllers\MasterData\PublisherTypeController;
use App\Http\Controllers\masterData\StatusController;
use App\Http\Controllers\MasterData\TaxPayeeController;
use App\Http\Controllers\MasterData\NewspaperPartnerController;
use App\Http\Controllers\MasterData\MediaBankDetailController;
use App\Http\Controllers\MasterData\TaxTypeController;
use App\Http\Controllers\NewspaperCategoryController;
use App\Http\Controllers\NewspaperController;
use App\Http\Controllers\NewspaperPeriodicityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetRequestController;
use App\Http\Controllers\PaymentBatchController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TreasuryChallanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;


Route::get('/', function () {
    return view('auth.login');
});

// Route::get('/resgister', function () {
//     return redirect()->route(login);
// })->name('register');

Route::get('/file', function () {
    return view('advertisements.file');
});

// Dashboard routes:
// Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');


Route::group(['middleware' => ['isAdmin', 'auth']], function () {


    // User
    Route::get('users', [UserController::class, 'index'])->name('userManagement.user.index');
    Route::get('create-user', [UserController::class, 'create'])->name('userManagement.user.create');
    Route::post('store-user', [UserController::class, 'store'])->name('userManagement.user.store');
    Route::get('edit-user/{id}', [UserController::class, 'edit'])->name('userManagement.user.edit');
    Route::post('update-user/{id}', [UserController::class, 'update'])->name('userManagement.user.update');
    Route::delete('delete-user/{id}', [UserController::class, 'destroy'])->name('userManagement.user.delete');
    Route::get('show-user/{id}', [UserController::class, 'show'])->name('userManagement.user.show');

    // Roles
    Route::get('roles', [RoleController::class, 'index'])->name('userManagement.role.index');
    Route::get('create-role', [RoleController::class, 'create'])->name('userManagement.role.create');
    Route::post('store-role', [RoleController::class, 'store'])->name('userManagement.role.store');
    Route::get('edit-role/{id}', [RoleController::class, 'edit'])->name('userManagement.role.edit');
    Route::post('update-role/{id}', [RoleController::class, 'update'])->name('userManagement.role.update');
    Route::delete('delete-role/{id}', [RoleController::class, 'destroy'])->name('userManagement.role.delete');
    Route::get('show-role/{id}', [RoleController::class, 'show'])->name('userManagement.role.show');
    Route::get('role/{id}', [RoleController::class, 'addPermission'])->name('userManagement.role.addPermission');
    Route::post('role/{id}', [RoleController::class, 'assignPermission'])->name('userManagement.role.assignPermission');

    // Permissions
    Route::get('permissions', [PermissionController::class, 'index'])->name('userManagement.permission.index');
    Route::get('create-permission', [PermissionController::class, 'create'])->name('userManagement.permission.create');
    Route::post('store-permission', [PermissionController::class, 'store'])->name('userManagement.permission.store');
    Route::get('edit-permission/{id}', [PermissionController::class, 'edit'])->name('userManagement.permission.edit');
    Route::post('update-permission/{id}', [PermissionController::class, 'update'])->name('userManagement.permission.update');
    Route::delete('delete-permission/{id}', [PermissionController::class, 'destroy'])->name('userManagement.permission.delete');
    Route::get('show-permission/{id}', [PermissionController::class, 'show'])->name('userManagement.permission.show');
});

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile/{id}', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile-edit/{id}', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/{id}', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/{id}', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['role:Super Admin'])->group(function () {
    Route::get('/change-password/{user}', [UserController::class, 'changePassword']);
    Route::post('/change-password/{user}', [UserController::class, 'updatePassword']);
    Route::get('/password-change-request', [UserController::class, 'requestPasswordChange'])
        ->middleware('auth')->name('password.change.request');
});

// Password Reset Requests
Route::middleware(['auth'])->group(function () {
    Route::post('/password-request', [PasswordResetRequestController::class, 'store'])->name('password.request.store');
    Route::middleware('role:Super Admin')->group(function () {
        Route::get('/admin/password-requests', [PasswordResetRequestController::class, 'index'])->name('password.requests.index');
        Route::post('/admin/password-reset/{id}/request', [PasswordResetRequestController::class, 'resetPassword'])->name('password.reset.request');
    });
});
Route::get('/password-reset-request', [PasswordResetRequestController::class, 'create'])
    ->name('password-reset.form');
Route::post('/password-reset-request', [PasswordResetRequestController::class, 'store'])
    ->name('password-reset.request');

// Protected Routes
Route::group(['middleware' => ['auth']], function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Year Trend
    Route::get('/yearly-ads-trend', [DashboardController::class, 'getYearlyAdsTrend']);

    // Advertisement
    Route::get('/advertisements/inprogress', [AdvertisementController::class, 'inprogress'])
        ->name('advertisements.inprogress');

    Route::get('/advertisements/approved', [AdvertisementController::class, 'approved'])
        ->name('advertisements.approved');

    Route::get('/advertisements/rejected', [AdvertisementController::class, 'rejected'])
        ->name('advertisements.rejected');



    Route::get('/advertisements/approved', [AdvertisementController::class, 'approved'])
        ->name('advertisements.approved');

    Route::get('/advertisements/rejected', [AdvertisementController::class, 'rejected'])
        ->name('advertisements.rejected');
    Route::get('advertisements', [AdvertisementController::class, 'index'])->name('advertisements.index');
    Route::get('advertisements-show/{id}', [AdvertisementController::class, 'show'])->name('advertisements.show');
    // Route for show full file image when click on image
    Route::get('/advertisements/{advertisementId}/file-show/{imageId}', [AdvertisementController::class, 'fileShow'])->name('advertisements.file.show');
    Route::get('/advertisement-get-offices', [AdvertisementController::class, 'getOffices'])->name('advertisements.getOffices');
    Route::get('/advertisement-inprogress', [AdvertisementController::class, 'inProgress'])->name('advertisements.inprogress');
    Route::get('advertisements-inprogress-show/{id}', [AdvertisementController::class, 'showInprogress'])->name('advertisements.show-inprogress');
    Route::get('/advertisement-approved', [AdvertisementController::class, 'approved'])->name('advertisements.approved');
    Route::get('/advertisement-rejected', [AdvertisementController::class, 'rejected'])->name('advertisements.rejected');
    Route::post('/advertisement-media/{id}', [AdvertisementController::class, 'media'])->name('advertisement.media');
    Route::get('advertisements-create', [AdvertisementController::class, 'create'])->name('advertisements.create');
    // Route::post('/upload', [AdvertisementController::class, 'upload'])->name('file_upload.route');
    Route::post('advertisements-store', [AdvertisementController::class, 'store'])->name('advertisements.store');
    Route::get('advertisements-edit/{id}', [AdvertisementController::class, 'edit'])->name('advertisements.edit');
    Route::get('/ad-worth-limit/{id}', [AdvertisementController::class, 'getAdWorthLimit'])->name('ad-worth-limit');
    Route::post('advertisements-update/{id}', [AdvertisementController::class, 'update'])->name('advertisements.update');

    // Ad publish
    Route::get('/advertisements-published', [AdvertisementController::class, 'publishedList'])->name('advertisements.published');
    Route::get('advertisements-published-show/{id}', [AdvertisementController::class, 'showPublished'])->name('advertisements.published.show');
    Route::get('/advertisements-unpublished', [AdvertisementController::class, 'unpublishedList'])->name('advertisements.unpublished');

    // Ad Rejection Reason
    Route::post('/advertisement-rejection-reason/{id}', [AdvertisementController::class, 'rejectionReason'])->name('advertisements.rejectionReason');

    // Draft Ads - Show All, Edit, Update & Show Single
    Route::get('advertisements-drafts', [AdvertisementController::class, 'draftIndex'])->name('advertisements.draft.index');
    Route::get('/advertisements-drafts/{id}', [AdvertisementController::class, 'editDraft'])->name('advertisements.draft.edit');
    Route::post('/advertisements-drafts/{id}', [AdvertisementController::class, 'updateDraft'])->name('advertisements.draft.update');
    Route::get('advertisement-draft-show/{id}', [AdvertisementController::class, 'showDraftAd'])->name('advertisements.draft.show');

    Route::get('/advertisements/media-edit-form/{id}', [AdvertisementController::class, 'mediaEditForm'])->name('advertisements.media-edit-form');
    Route::post('/advertisements/mediaForm-update/{id}', [AdvertisementController::class, 'mediaFormUpdate'])->name('advertisements.mediaForm-update');
    Route::get('generate-inf', [AdvertisementController::class, 'generateINF'])->name('advertisements.generateINF');
    Route::get('inf-series', [AdvertisementController::class, 'showSeries'])->name('advertisements.inf_series');

    // Archive/Unarchive Ads
    Route::get('/advertisements/archive', [AdvertisementController::class, 'archived'])->name('advertisements.archived');
    Route::post('/advertisements/{id}/archive', [AdvertisementController::class, 'archive'])->name('advertisements.archive');
    Route::post('/advertisements/{id}/unarchive', [AdvertisementController::class, 'unarchive'])->name('advertisements.unarchive');


    // Billing Newsppapers
    Route::get('billing-newspapers', [BillingController::class, 'index'])->name('billings.newspapers.index');
    Route::post('/billing-newspaper-submission/{id}', [BillingController::class, 'newspaperBillSubmission'])->name('billings.newspapers.newspaperBillSubmission');
    Route::get('billing-newspapers/create/{id}', [BillingController::class, 'create'])->name('billings.newspapers.create');
    Route::put('billing-newspapers/{id}', [BillingController::class, 'store'])->name('billings.newspapers.store');
    Route::get('billing-newspapers/{advertisementId}/bill-details', [BillingController::class, 'billDetail'])->name('billings.newspapers.bill.detail');
    Route::get('billing-newspapers/show/{id}', [BillingController::class, 'show'])->name('billings.newspapers.show');

    // Route for printing Newspaper Bill
    Route::get('newspaper-bill/{id}/print', [BillingController::class, 'printNewspaperBill'])->name('billings.newspapers.print');

    Route::get('/billing-newspapers/search', [BillingController::class, 'index'])
        ->name('billings.newspapers.search');

    // Billing Agencies
    Route::get('billing-agencies', [BillingController::class, 'agencyIndex'])->name('billings.agencies.index');
    Route::post('/billing-agency-submission/{id}', [BillingController::class, 'agencyBillSubmission'])->name('billings.newspapers.agencyBillSubmission');
    Route::get('billing-agencies/create/{id}', [BillingController::class, 'agencyCreate'])->name('billings.agencies.create');
    Route::put('billing-agencies/{id}', [BillingController::class, 'agencyStore'])->name('billings.agencies.store');
    Route::get('billing-agencies/{advertisementId}/bill-details', [BillingController::class, 'agencyBillDetail'])->name('billings.agencies.bill.detail');
    Route::get('billing-agencies/AgencyShow/{id}', [BillingController::class, 'agencyShow'])->name('billings.agencies.show');

    // Route for printing Agency Bill
    Route::get('agency-bill/{billClassifiedAdId}/print', [BillingController::class, 'printAgencyBill'])->name('billings.agencies.print');
    Route::get('/billing-agencies/search', [BillingController::class, 'agencyIndex'])
        ->name('billings.agencies.search');

    // Tresury challans route
    Route::get('billings-treasury-challans', [TreasuryChallanController::class, 'index'])->name('billings.treasury-challans.index');
    // Route::get('billings/show-cheques', [TreasuryChallanController::class, 'showDGChequeApproval'])->name('billings.treasury-challans.showDGChequeApproval');
    Route::get('treasury-challans/create', [TreasuryChallanController::class, 'create'])->name('billings.treasury-challans.create');
    Route::get('/get-offices-by-department', [TreasuryChallanController::class, 'fetchOffices'])->name('get.offices.by.department');
    // get inf numbers who data are in the billcalssifiedAds table
    Route::get('/get-inf-numbers', [TreasuryChallanController::class, 'getInfNumbers'])->name('get.inf.numbers');
    Route::get('/get-total-bill', [TreasuryChallanController::class, 'getTotalBill'])->name('get.total.bill');
    Route::post('/billings-treasury-challan', [TreasuryChallanController::class, 'store'])->name('billings.treasury-challans.store');




    // for online cheque submission
    Route::get('online-cheques', [TreasuryChallanController::class, 'showOnlineCheque'])->name('billings.treasury-challans.showOnlinCheque');
    Route::get('treasury-challans/create-online-cheque', [TreasuryChallanController::class, 'createOnlineCheque'])->name('billings.treasury-challans.createOnlineCheque');
    Route::get('/get-online-offices-by-department', [TreasuryChallanController::class, 'onlineFetchOffices'])->name('get.online.offices.by.department');
    // get inf numbers who data are in the billcalssifiedAds table
    Route::get('/get-online-inf-numbers', [TreasuryChallanController::class, 'onlineGetInfNumbers'])->name('get.online.inf.numbers');
    Route::get('/get-online-total-bill', [TreasuryChallanController::class, 'onlineGetTotalBill'])->name('get.online.total.bill');
    Route::post('/billings-online-cheque', [TreasuryChallanController::class, 'storeOnlineCheque'])->name('billings.treasury-challans.storeOnlineCheque');



    Route::get('/treasury-challans/edit/{id}', [TreasuryChallanController::class, 'edit'])->name('billings.treasury-challans.edit');
    Route::post('/treasury-challans', [TreasuryChallanController::class, 'update'])->name('billings.treasury-challans.update');
    Route::get('/treasury-challans/show/{id}', [TreasuryChallanController::class, 'show'])->name('billings.treasury-challans.show');
    Route::get('Pla-account/', [TreasuryChallanController::class, 'plaIndex'])->name('billings.treasury-challans.plaIndex');
    Route::get('Pla-account/export/excel', [TreasuryChallanController::class, 'plaExportExcel'])->name('pla-accounts.export.excel');
    Route::get('Pla-account/export/pdf', [TreasuryChallanController::class, 'plaExportPdf'])->name('pla-accounts.export.pdf');
    Route::get('/pla-account/detail/{id}', [TreasuryChallanController::class, 'plaView'])->name('pla-accounts.plaView');

    // update the data for verifiation challan in modal popup
    Route::post('treasury-challan/challan-file/upload', [TreasuryChallanController::class, 'uploadChallanImage'])->name('tr_challan_image.upload');
    Route::post('treasury-challan/update/{id}', [TreasuryChallanController::class, 'modalData'])->name('billings.treasury-challans.modalUpdate');
    // DG approval
    Route::post('/treasury-challan/dg-approve/{id}', [TreasuryChallanController::class, 'dgAprovePla'])
        ->name('treasury-challans.dg-approve');


    // View Challan forms and deposit slips
    Route::get('/treasury-challans/{id}/challan-form', [TreasuryChallanController::class, 'ViewChallan'])->name('billings.treasury-challans.viewChallan');
    Route::get('/treasury-challans/{id}/download-challan', [TreasuryChallanController::class, 'downloadChallanPdf'])->name('billings.treasury-challans.downloadChallanPdf');
    Route::get('/treasury-challans/{id}/deposit-slip', [TreasuryChallanController::class, 'ViewDepositSlip'])->name('billings.treasury-challans.viewDepositSlip');
    Route::get('/treasury-challans/{id}/download-deposit-slip', [TreasuryChallanController::class, 'downloadDepositSlipPdf'])->name('billings.treasury-challans.downloadDepositSlipPdf');

    // Cheque Receipts Newspapers Routes
    Route::get('cheque-receipts-newspapers', [ChequeReceiptNpController::class, 'index'])->name('billings.chequeReceipts.newspapers.index');
    Route::post('cheque-receipts-newspaper/update/{id}', [ChequeReceiptNpController::class, 'modalData'])->name('billings.chequeReceipts.newspapers.update');
    Route::get('cheque-receipts-newspaper/challan-ID/{id}', [ChequeReceiptNpController::class, 'receipt'])->name('billings.chequeReceipts.newspapers.receipt');

    // Route for payment of newspapers
    Route::get('payment-newspapers', [PaymentController::class, 'index'])->name('payment.newspapers.index');
    Route::get('payment-newspaper/challan-ID/{id}', [PaymentController::class, 'receipt'])->name('payment.newspapers.receipt');
    Route::post('payment-newspaper', [PaymentController::class, 'store'])->name('payment.newspapers.store');
    Route::get('payment-newspaper-bulkview', [PaymentController::class, 'newspaperBulkView'])->name('payment.newspapers.bulkview');
    Route::get('payment-newspaper-wise-summary', [PaymentController::class, 'newspaperWiseSummary'])->name('payment.newspapers.summary');
    Route::get('payments/bank-name-wise-summary', [PaymentController::class, 'bankNameWiseSummary'])->name('payment.newspapers.bank-name-wise');
    Route::get('payments/po-list-summary', [PaymentController::class, 'poList'])->name('payment.newspapers.po-list-summary');
    Route::get('payments/pay-order-list', [PaymentController::class, 'payOrderList'])->name('payment.newspapers.pay-order-list');
    Route::get('payments/view-gov-cheque', [PaymentController::class, 'viewGovCheque'])->name('payment.newspapers.viewGovCheque');
    Route::get('payments/view-gov-cheque/download-pdf', [PaymentController::class, 'downloadGovChequePdf'])->name('payment.newspapers.viewGovCheque.pdf');
    // Route::get('payments/amount-paid-for-newspaper/{id}', [PaymentController::class, 'amountPaidForNewspaper'])->name('payment.newspapers.amountPaidForNewspaper');

    // Paid Amount Tracking
    Route::get('payments/paid-amount', [PaymentController::class, 'paidAmount'])->name('payment.newspapers.paid-amount');
    Route::post('payments/paid-amount', [PaymentController::class, 'storePaidAmount'])->name('payment.newspapers.paid-amount.store');
    Route::get('payments/paid-amount/history', [PaymentController::class, 'paidAmountHistory'])->name('payment.newspapers.paid-amount.history');
    Route::get('payments/paid-amount/history/export/excel', [PaymentController::class, 'exportPaidAmountHistoryExcel'])->name('payment.newspapers.paid-amount.history.export.excel');
    Route::get('payments/paid-amount/history/export/pdf', [PaymentController::class, 'exportPaidAmountHistoryPdf'])->name('payment.newspapers.paid-amount.history.export.pdf');

    // Payment newspapers exports
    Route::get('payment-newspapers/export/excel', [PaymentController::class, 'exportLedgerExcel'])->name('payment.newspapers.export.excel');
    Route::get('payment-newspapers/export/pdf', [PaymentController::class, 'exportLedgerPdf'])->name('payment.newspapers.export.pdf');
    Route::get('payment-newspaper-bulkview/export/excel', [PaymentController::class, 'exportBulkExcel'])->name('payment.newspapers.bulkview.export.excel');
    Route::get('payment-newspaper-bulkview/export/pdf', [PaymentController::class, 'exportBulkPdf'])->name('payment.newspapers.bulkview.export.pdf');
    Route::get('payment-newspaper-wise-summary/export/excel', [PaymentController::class, 'exportSummaryExcel'])->name('payment.newspapers.summary.export.excel');
    Route::get('payment-newspaper-wise-summary/export/pdf', [PaymentController::class, 'exportSummaryPdf'])->name('payment.newspapers.summary.export.pdf');
    Route::get('payments/bank-name-wise-summary/export/excel', [PaymentController::class, 'exportBankWiseExcel'])->name('payment.newspapers.bank-name-wise.export.excel');
    Route::get('payments/bank-name-wise-summary/export/pdf', [PaymentController::class, 'exportBankWisePdf'])->name('payment.newspapers.bank-name-wise.export.pdf');
    Route::get('payments/po-list-summary/export/excel', [PaymentController::class, 'exportPoListExcel'])->name('payment.newspapers.po-list-summary.export.excel');
    Route::get('payments/po-list-summary/export/pdf', [PaymentController::class, 'exportPoListPdf'])->name('payment.newspapers.po-list-summary.export.pdf');
    Route::get('payments/pay-order-list/export/excel', [PaymentController::class, 'exportPayOrderListExcel'])->name('payment.newspapers.pay-order-list.export.excel');
    Route::get('payments/pay-order-list/export/pdf', [PaymentController::class, 'exportPayOrderListPdf'])->name('payment.newspapers.pay-order-list.export.pdf');


    // =========================================================
    // Route::prefix('payments')->group(function () {

    //     Route::get('/batches', [PaymentBatchController::class, 'index'])
    //         ->name('payment.batches.index');

    //     Route::get('/batches/create', [PaymentBatchController::class, 'create'])
    //         ->name('payment.batches.create');

    //     Route::post('/batches/store', [PaymentBatchController::class, 'store'])
    //         ->name('payment.batches.store');

    //     Route::get('/batches/{id}', [PaymentBatchController::class, 'show'])
    //         ->name('payment.batches.show');
    // });


    // =========================================================
    // Publisher Types
    Route::get('publisher-types', [PublisherTypeController::class, 'index'])->name('master.publisherType.index');
    Route::get('create-publisher-type', [PublisherTypeController::class, 'create'])->name('master.publisherType.create');
    Route::post('store-publisher-type', [PublisherTypeController::class, 'store'])->name('publisherType.store');
    Route::get('edit-publisher-type/{id}', [PublisherTypeController::class, 'edit'])->name('master.publisherType.edit');
    Route::post('update-publisher-type/{id}', [PublisherTypeController::class, 'update'])->name('publisherType.update');
    Route::delete('delete-publisher-type/{id}', [PublisherTypeController::class, 'destroy'])->name('publisherType.delete');
    Route::get('show-publisher-type/{id}', [PublisherTypeController::class, 'show'])->name('master.publisherType.show');

    // Tax Types
    Route::get('tax-types', [TaxTypeController::class, 'index'])->name('master.taxType.index');
    Route::get('create-tax-type', [TaxTypeController::class, 'create'])->name('master.taxType.create');
    Route::post('store-tax-type', [TaxTypeController::class, 'store'])->name('taxType.store');
    Route::get('edit-tax-type/{id}', [TaxTypeController::class, 'edit'])->name('master.taxType.edit');
    Route::post('update-tax-type/{id}', [TaxTypeController::class, 'update'])->name('taxType.update');
    Route::delete('delete-tax-type/{id}', [TaxTypeController::class, 'destroy'])->name('taxType.delete');
    Route::get('show-tax-type/{id}', [TaxTypeController::class, 'show'])->name('master.taxType.show');

    // Tax Payees
    Route::get('tax-payees', [TaxPayeeController::class, 'index'])->name('master.taxPayee.index');
    Route::get('create-tax-Payee', [TaxPayeeController::class, 'create'])->name('master.taxPayee.create');
    Route::post('store-tax-Payee', [TaxPayeeController::class, 'store'])->name('taxPayee.store');
    Route::get('edit-tax-payee/{id}', [TaxPayeeController::class, 'edit'])->name('master.taxPayee.edit');
    Route::post('update-tax-payee/{id}', [TaxPayeeController::class, 'update'])->name('taxPayee.update');
    Route::delete('delete-tax-payee/{id}', [TaxPayeeController::class, 'destroy'])->name('taxPayee.delete');
    Route::get('show-tax-payee/{id}', [TaxPayeeController::class, 'show'])->name('master.taxPayee.show');

    // Newspaper partners (ownership % + payout bank per partner)
    Route::get('newspaper-partners', [NewspaperPartnerController::class, 'index'])->name('master.newspaperPartner.index');
    Route::get('create-newspaper-partner', [NewspaperPartnerController::class, 'create'])->name('master.newspaperPartner.create');
    Route::post('store-newspaper-partner', [NewspaperPartnerController::class, 'store'])->name('newspaperPartner.store');
    Route::get('edit-newspaper-partner/{id}', [NewspaperPartnerController::class, 'edit'])->name('master.newspaperPartner.edit');
    Route::post('update-newspaper-partner/{id}', [NewspaperPartnerController::class, 'update'])->name('newspaperPartner.update');
    Route::delete('delete-newspaper-partner/{id}', [NewspaperPartnerController::class, 'destroy'])->name('newspaperPartner.delete');
    Route::get('show-newspaper-partner/{id}', [NewspaperPartnerController::class, 'show'])->name('master.newspaperPartner.show');
    Route::get('api/newspaper-partner-banks', [NewspaperPartnerController::class, 'banksForNewspaper'])->name('master.newspaperPartner.banks');

    // Media bank details (newspaper/agency bank accounts)
    Route::get('media-bank-details', [MediaBankDetailController::class, 'index'])->name('master.mediaBankDetail.index');
    Route::get('create-media-bank-detail', [MediaBankDetailController::class, 'create'])->name('master.mediaBankDetail.create');
    Route::post('store-media-bank-detail', [MediaBankDetailController::class, 'store'])->name('mediaBankDetail.store');
    Route::get('edit-media-bank-detail/{id}', [MediaBankDetailController::class, 'edit'])->name('master.mediaBankDetail.edit');
    Route::post('update-media-bank-detail/{id}', [MediaBankDetailController::class, 'update'])->name('mediaBankDetail.update');
    Route::delete('delete-media-bank-detail/{id}', [MediaBankDetailController::class, 'destroy'])->name('mediaBankDetail.delete');
    Route::get('show-media-bank-detail/{id}', [MediaBankDetailController::class, 'show'])->name('master.mediaBankDetail.show');

    // Newspaper Positions & Rates
    Route::get('news-pos-rates', [NewsPosRateController::class, 'index'])->name('master.newsPosRate.index');
    Route::get('create-news-pos-rate', [NewsPosRateController::class, 'create'])->name('master.newsPosRate.create');
    Route::post('store-news-pos-rate', [NewsPosRateController::class, 'store'])->name('newsPosRate.store');
    Route::get('edit-news-pos-rate/{id}', [NewsPosRateController::class, 'edit'])->name('master.newsPosRate.edit');
    Route::post('update-news-pos-rate/{id}', [NewsPosRateController::class, 'update'])->name('newsPosRate.update');
    Route::delete('delete-news-pos-rate/{id}', [NewsPosRateController::class, 'destroy'])->name('newsPosRate.delete');
    Route::get('news-pos-rate/{id}', [NewsPosRateController::class, 'show'])->name('master.newsPosRate.show');

    // Ad Worth Parameters
    Route::get('ad-worth-parameters', [AdWorthParameterController::class, 'index'])->name('master.adWorthParameter.index');
    Route::get('create-ad-worth-parameter', [AdWorthParameterController::class, 'create'])->name('master.adWorthParameter.create');
    Route::post('store-ad-worth-parameter', [AdWorthParameterController::class, 'store'])->name('adWorthParameter.store');
    Route::get('edit-ad-worth-parameter/{id}', [AdWorthParameterController::class, 'edit'])->name('master.adWorthParameter.edit');
    Route::post('update-ad-worth-parameter/{id}', [AdWorthParameterController::class, 'update'])->name('adWorthParameter.update');
    Route::delete('delete-ad-worth-parameter/{id}', [AdWorthParameterController::class, 'destroy'])->name('adWorthParameter.delete');
    Route::get('ad-worth-parameter/{id}', [AdWorthParameterController::class, 'show'])->name('master.adWorthParameter.show');

    // Classifieds Ad Types
    Route::get('classified-ad-types', [ClassifiedAdTypeController::class, 'index'])->name('master.classifiedAdType.index');
    Route::get('create-classified-ad-type', [ClassifiedAdTypeController::class, 'create'])->name('master.classifiedAdType.create');
    Route::post('store-classified-ad-type', [ClassifiedAdTypeController::class, 'store'])->name('classifiedAdType.store');
    Route::get('edit-classified-ad-type/{id}', [ClassifiedAdTypeController::class, 'edit'])->name('master.classifiedAdType.edit');
    Route::post('update-classified-ad-type/{id}', [ClassifiedAdTypeController::class, 'update'])->name('classifiedAdType.update');
    Route::delete('delete-classified-ad-type/{id}', [ClassifiedAdTypeController::class, 'destroy'])->name('classifiedAdType.delete');
    Route::get('classified-ad-type/{id}', [ClassifiedAdTypeController::class, 'show'])->name('master.classifiedAdType.show');

    // Ad Categories
    Route::get('ad-categories', [AdCategoryController::class, 'index'])->name('master.adCategory.index');
    Route::get('create-ad-category', [AdCategoryController::class, 'create'])->name('master.adCategory.create');
    Route::post('store-ad-category', [AdCategoryController::class, 'store'])->name('adCategory.store');
    Route::get('edit-ad-category/{id}', [AdCategoryController::class, 'edit'])->name('master.adCategory.edit');
    Route::post('update-ad-category/{id}', [AdCategoryController::class, 'update'])->name('adCategory.update');
    Route::delete('delete-ad-category/{id}', [AdCategoryController::class, 'destroy'])->name('adCategory.delete');
    Route::get('ad-cateory/{id}', [AdCategoryController::class, 'show'])->name('master.adCategory.show');

    // Ad Rejection Reasons
    Route::get('ad-rejection-reasons', [AdRejectionReasonController::class, 'index'])->name('master.adRejectionReason.index');
    Route::get('create-ad-rejection-reason', [AdRejectionReasonController::class, 'create'])->name('master.adRejectionReason.create');
    Route::post('store-ad-rejection-reason', [AdRejectionReasonController::class, 'store'])->name('adRejectionReason.store');
    Route::get('edit-ad-rejection-reason/{id}', [AdRejectionReasonController::class, 'edit'])->name('master.adRejectionReason.edit');
    Route::post('update-ad-rejection-reason/{id}', [AdRejectionReasonController::class, 'update'])->name('adRejectionReason.update');
    Route::delete('delete-ad-rejection-reason/{id}', [AdRejectionReasonController::class, 'destroy'])->name('adRejectionReason.delete');
    Route::get('show-ad-rejection-reason/{id}', [AdRejectionReasonController::class, 'show'])->name('master.adRejectionReason.show');

    // Department
    Route::get('departments', [DepartmentController::class, 'index'])->name('master.department.index');
    Route::get('create-department', [DepartmentController::class, 'create'])->name('master.department.create');
    Route::post('store-department', [DepartmentController::class, 'store'])->name('department.store');
    Route::get('edit-department/{id}', [DepartmentController::class, 'edit'])->name('master.department.edit');
    Route::post('update-department/{id}', [DepartmentController::class, 'update'])->name('department.update');
    Route::delete('delete-department/{id}', [DepartmentController::class, 'destroy'])->name('department.delete');
    Route::get('show-department/{id}', [DepartmentController::class, 'show'])->name('master.department.show');

    // Department Categories
    Route::get('department-categories', [DepartmentCategoryController::class, 'index'])->name('master.department.departmentCategory.index');
    Route::get('create-department-category', [DepartmentCategoryController::class, 'create'])->name('master.department.departmentCategory.create');
    Route::post('store-department-category', [DepartmentCategoryController::class, 'store'])->name('department.departmentCategory.store');
    Route::get('edit-department-category/{id}', [DepartmentCategoryController::class, 'edit'])->name('master.department.departmentCategory.edit');
    Route::post('update-department-category/{id}', [DepartmentCategoryController::class, 'update'])->name('department.departmentCategory.update');
    Route::delete('delete-department-category/{id}', [DepartmentCategoryController::class, 'destroy'])->name('department.departmentCategory.delete');
    Route::get('show-department-category/{id}', [DepartmentCategoryController::class, 'show'])->name('master.department.departmentCategory.show');

    // Offices
    Route::get('offices', [OfficeController::class, 'index'])->name('master.office.index');
    Route::get('create-office', [OfficeController::class, 'create'])->name('master.office.create');
    Route::post('store-office', [OfficeController::class, 'store'])->name('office.store');
    Route::get('edit-office/{id}', [OfficeController::class, 'edit'])->name('master.office.edit');
    Route::post('update-office/{id}', [OfficeController::class, 'update'])->name('office.update');
    Route::delete('delete-office/{id}', [OfficeController::class, 'destroy'])->name('office.delete');
    Route::get('show-office/{id}', [OfficeController::class, 'show'])->name('master.office.show');

    // Offices Categories
    Route::get('office-categories', [OfficeCategoryController::class, 'index'])->name('master.office.officeCategory.index');
    Route::get('create-office-category', [OfficeCategoryController::class, 'create'])->name('master.office.officeCategory.create');
    Route::post('store-office-category', [OfficeCategoryController::class, 'store'])->name('office.officeCategory.store');
    Route::get('edit-office-category/{id}', [OfficeCategoryController::class, 'edit'])->name('master.office.officeCategory.edit');
    Route::post('update-office-category/{id}', [OfficeCategoryController::class, 'update'])->name('office.officeCategory.update');
    Route::delete('delete-office-category/{id}', [OfficeCategoryController::class, 'destroy'])->name('office.officeCategory.delete');
    Route::get('show-office-category/{id}', [OfficeCategoryController::class, 'show'])->name('master.office.officeCategory.show');

    // Provinces
    Route::get('provinces', [ProvinceController::class, 'index'])->name('master.province.index');
    Route::get('create-province', [ProvinceController::class, 'create'])->name('master.province.create');
    Route::post('store-province', [ProvinceController::class, 'store'])->name('province.store');
    Route::get('edit-province/{id}', [ProvinceController::class, 'edit'])->name('master.province.edit');
    Route::post('update-province/{id}', [ProvinceController::class, 'update'])->name('province.update');
    Route::delete('delete-province/{id}', [ProvinceController::class, 'destroy'])->name('province.delete');
    Route::get('show-province/{id}', [ProvinceController::class, 'show'])->name('master.province.show');

    // Districts
    Route::get('districts', [DistrictController::class, 'index'])->name('master.district.index');
    Route::get('create-district', [DistrictController::class, 'create'])->name('master.district.create');
    Route::post('store-district', [DistrictController::class, 'store'])->name('district.store');
    Route::get('edit-district/{id}', [DistrictController::class, 'edit'])->name('master.district.edit');
    Route::post('update-district', [DistrictController::class, 'update'])->name('district.update');
    Route::delete('delete-district/{id}', [DistrictController::class, 'destroy'])->name('district.delete');
    Route::get('show-district/{id}', [DistrictController::class, 'show'])->name('master.district.show');

    // Language
    Route::get('languages', [LanguageController::class, 'index'])->name('master.language.index');
    Route::get('create-language', [LanguageController::class, 'create'])->name('master.language.create');
    Route::post('store-language', [LanguageController::class, 'store'])->name('language.store');
    Route::get('edit-language/{id}', [LanguageController::class, 'edit'])->name('master.language.edit');
    Route::post('update-language/{id}', [LanguageController::class, 'update'])->name('language.update');
    Route::delete('delete-language/{id}', [LanguageController::class, 'destroy'])->name('language.delete');
    Route::get('show-language/{id}', [LanguageController::class, 'show'])->name('master.language.show');

    // Newspapers
    Route::get('newspapers', [NewspaperController::class, 'index'])->name('newspaper.index');
    Route::get('create-newspaper', [NewspaperController::class, 'create'])->name('newspaper.create');
    Route::get('/newspaper-get-districts', [NewspaperController::class, 'getDistricts'])->name('districts.getDistricts');
    Route::post('store-newspaper', [NewspaperController::class, 'store'])->name('newspaper.store');
    Route::get('edit-newspaper/{id}', [NewspaperController::class, 'edit'])->name('newspaper.edit');
    Route::post('update-newspapers/{id}', [NewspaperController::class, 'update'])->name('newspaper.update');
    Route::delete('delete-newspaper/{id}', [NewspaperController::class, 'destroy'])->name('newspaper.delete');
    Route::get('show-newspaper/{id}', [NewspaperController::class, 'show'])->name('newspaper.show');

    // Newspaper Categories
    Route::get('newspaper-categories', [NewspaperCategoryController::class, 'index'])->name('newspaper.newspaperCategory.index');
    Route::get('create-newspaper-category', [NewspaperCategoryController::class, 'create'])->name('newspaper.newspaperCategory.create');
    Route::post('store-newspaper-category', [NewspaperCategoryController::class, 'store'])->name('newspaperCategory.store');
    Route::get('edit-newspaper-category/{id}', [NewspaperCategoryController::class, 'edit'])->name('newspaper.newspaperCategory.edit');
    Route::post('update-newspaper-category/{id}', [NewspaperCategoryController::class, 'update'])->name('newspaperCategory.update');
    Route::delete('delete-newspaper-category/{id}', [NewspaperCategoryController::class, 'destroy'])->name('newspaperCategory.delete');
    Route::get('show-newspaper-category/{id}', [NewspaperCategoryController::class, 'show'])->name('newspaper.newspaperCategory.show');

    // Newspaper Periodicity
    Route::get('newspaper-periodicity', [NewspaperPeriodicityController::class, 'index'])->name('newspaper.newspaperPeriodicity.index');
    Route::get('create-newspaper-periodicity', [NewspaperPeriodicityController::class, 'create'])->name('newspaper.newspaperPeriodicity.create');
    Route::post('store-newspaper-periodicity', [NewspaperPeriodicityController::class, 'store'])->name('newspaperPeriodicity.store');
    Route::get('edit-newspaper-periodicity/{id}', [NewspaperPeriodicityController::class, 'edit'])->name('newspaper.newspaperPeriodicity.edit');
    Route::post('update-newspaper-periodicity/{id}', [NewspaperPeriodicityController::class, 'update'])->name('newspaperPeriodicity.update');
    Route::delete('delete-newspaper-periodicity/{id}', [NewspaperPeriodicityController::class, 'destroy'])->name('newspaperPeriodicity.delete');
    Route::get('newspaper-periodicity/{id}', [NewspaperPeriodicityController::class, 'show'])->name('newspaper.newspaperPeriodicity.show');

    // Advertising Agencies
    Route::get('adv-agencies', [AdvAgencyController::class, 'index'])->name('advAgency.index');
    Route::get('create-adv-agency', [AdvAgencyController::class, 'create'])->name('advAgency.create');
    Route::post('store-adv-agency', [AdvAgencyController::class, 'store'])->name('advAgency.store');
    Route::get('edit-adv-agency/{id}', [AdvAgencyController::class, 'edit'])->name('advAgency.edit');
    Route::post('update-adv-agency/{id}', [AdvAgencyController::class, 'update'])->name('advAgency.update');
    Route::delete('delete-adv-agency/{id}', [AdvAgencyController::class, 'destroy'])->name('advAgency.delete');
    Route::get('show-adv-agency/{id}', [AdvAgencyController::class, 'show'])->name('advAgency.show');

    // Digital Agencies
    Route::get('digital-agencies', [AdvAgencyController::class, 'index'])->name('digitalAgency.index');

    // Ad Submission Threshold
    // Route::get('ad-submission-threshold', [::class, 'index'])->name('master.submission-threshold.index');

    // Status
    Route::get('statuses', [StatusController::class, 'index'])->name('master.status.index');
    Route::get('create-status', [StatusController::class, 'create'])->name('master.status.create');
    Route::post('store-status', [StatusController::class, 'store'])->name('master.status.store');
    Route::get('edit-status/{id}', [StatusController::class, 'edit'])->name('master.status.edit');
    Route::post('update-status/{id}', [StatusController::class, 'update'])->name('master.status.update');
    Route::delete('delete-status/{id}', [StatusController::class, 'destroy'])->name('master.status.delete');
    Route::get('show-status/{id}', [StatusController::class, 'show'])->name('master.status.show');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/status', [ReportsController::class, 'statusWise'])->name('status');
        Route::get('/departments', [ReportsController::class, 'departmentWise'])->name('departments');
        Route::get('/offices', [ReportsController::class, 'officeWise'])->name('offices');
        Route::get('/categories', [ReportsController::class, 'categoryWise'])->name('categories');
        Route::get('/years', [ReportsController::class, 'yearWise'])->name('years');
        Route::get('/offices-advertisement-list', [ReportsController::class, 'OfficesAdvtList'])->name('officesAdvtList');
        Route::get('/billing', [ReportsController::class, 'billingReport'])->name('billing');

        Route::get('/newspaper/pla/amount', [ReportsController::class, 'newspapersPlaAmount'])->name('newspaper.pla.amount');

        Route::get('/newspaper/pla/export/excel', [ReportsController::class, 'newspaperPlaAmountExportExcel'])
            ->name('newspaper.pla.export.excel');

        Route::get('/newspaper/pla/export/pdf', [ReportsController::class, 'newspaperPlaAmountexportPdf'])
            ->name('newspaper.pla.export.pdf');

        Route::get('/adv-agency/pla/amount', [ReportsController::class, 'advAgenciesPlaAmount'])->name('agency.pla.amount');

        Route::get('/adv-agency/pla/export/excel', [ReportsController::class, 'agencyPlaAmountExportExcel'])
            ->name('agency.pla.export.excel');

        Route::get('/adv-agency/pla/export/pdf', [ReportsController::class, 'agencyPlaAmountexportPdf'])
            ->name('agency.pla.export.pdf');

        Route::get('/billing/export/excel', [ReportsController::class, 'billingExportExcel'])->name('billing.export.excel');
        Route::get('/billing/export/pdf', [ReportsController::class, 'billingExportPdf'])->name('billing.export.pdf');
    });

    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-excel/{statusId}', [ReportsController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/export-pdf/{statusId}', [ReportsController::class, 'exportPdf'])->name('reports.export.pdf');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
    });
    Route::get('/Reports/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');




    // Export routes for New Ads (index)
    Route::get('/advertisements/export-excel', [AdvertisementController::class, 'exportExcelIndex'])->name('advertisements.export.excel');
    Route::get('/advertisements/export-pdf', [AdvertisementController::class, 'exportPDFIndex'])->name('advertisements.export.pdf');

    // Export routes for Inprogress Ads
    Route::get('/advertisements/inprogress/export-excel', [AdvertisementController::class, 'exportExcelInprogress'])->name('advertisements.inprogress.export.excel');
    Route::get('/advertisements/inprogress/export-pdf', [AdvertisementController::class, 'exportPDFInprogress'])->name('advertisements.inprogress.export.pdf');

    // Export routes for Approved Ads
    Route::get('/advertisements/approved/export-excel', [AdvertisementController::class, 'exportExcelApproved'])->name('advertisements.approved.export.excel');
    Route::get('/advertisements/approved/export-pdf', [AdvertisementController::class, 'exportPDFApproved'])->name('advertisements.approved.export.pdf');

    // Export routes for Published Ads
    Route::get('/advertisements/published/export-excel', [AdvertisementController::class, 'exportExcelPublished'])->name('advertisements.published.export.excel');
    Route::get('/advertisements/published/export-pdf', [AdvertisementController::class, 'exportPDFPublished'])->name('advertisements.published.export.pdf');

    // Export routes for Archived Ads
    Route::get('/advertisements/archived/export-excel', [AdvertisementController::class, 'exportExcelArchived'])->name('advertisements.archived.export.excel');
    Route::get('/advertisements/archived/export-pdf', [AdvertisementController::class, 'exportPDFArchived'])->name('advertisements.archived.export.pdf');

    // Export routes for Rejected Ads
    Route::get('/advertisements/rejected/export-excel', [AdvertisementController::class, 'exportExcelRejected'])->name('advertisements.rejected.export.excel');
    Route::get('/advertisements/rejected/export-pdf', [AdvertisementController::class, 'exportPDFRejected'])->name('advertisements.rejected.export.pdf');

    // Export routes for Rejected Ads
    //     Route::get('/billing-newspaper/export-excel', [BillingController::class, 'exportExcelBillingNewspaperIndex'])->name('billing.index.export.excel');
    //     Route::get('/billing-newspaper/export-pd/{advertisementId}', [BillingController::class, 'exportPDFBillingNewspaperIndex'])->name('billing.index.export.pdf');

    Route::get('/treasury-challans/export-excel', [TreasuryChallanController::class, 'exportExcel'])
        ->name('treasury.export.excel');

    Route::get('/treasury-challans/export-pdf', [TreasuryChallanController::class, 'exportPdf'])
        ->name('treasury.export.pdf');


    Route::get('/search', [GlobalSearchController::class, 'search'])->name('global.search');
});

require __DIR__ . '/auth.php';


//search
Route::get('/advertisements/published', [AdvertisementController::class, 'publishedList'])
    ->name('advertisements.published.list');
Route::get('/billings/newspapers', [BillingController::class, 'index'])
    ->name('billings.newspapers.index');


// Route::view('/test-upload', 'test-upload');
// Route::post('/test-upload', function (Illuminate\Http\Request $request) {
//     $path = $request->file('file')->store('test', 'public');
//     return response()->json(['path' => $path, 'url' => Storage::disk('public')->url($path)]);
// });
