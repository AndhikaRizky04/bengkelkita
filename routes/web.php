<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\StatusController;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\QueueController as AdminQueue;
use App\Http\Controllers\Admin\CustomerController as AdminCustomer;
use App\Http\Controllers\Admin\VehicleController as AdminVehicle;
use App\Http\Controllers\Admin\ServiceOrderController as AdminServiceOrder;
use App\Http\Controllers\Admin\WashOrderController as AdminWashOrder;
use App\Http\Controllers\Admin\SparepartController as AdminSparepart;
use App\Http\Controllers\Admin\OilProductController as AdminOil;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoice;
use App\Http\Controllers\Admin\ReportController as AdminReport;
use App\Http\Controllers\Admin\SearchController as AdminSearch;
use App\Http\Controllers\Admin\HandoverController as AdminHandover;

use App\Http\Controllers\Mechanic\DashboardController as MechanicDashboard;
use App\Http\Controllers\Washer\DashboardController as WasherDashboard;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/api/brands/{brand}/models', [LandingController::class, 'getModels'])->name('public.models');
Route::post('/daftar-servis', [LandingController::class, 'registerService'])->name('public.register_service');
Route::get('/cek-status', [StatusController::class, 'index'])->name('public.status');

// Customer online payment (simulated) — reachable via queue number
Route::get('/pembayaran/{queueNumber}', [\App\Http\Controllers\Public\PaymentController::class, 'show'])->name('public.payment.show');
Route::post('/pembayaran/{queueNumber}', [\App\Http\Controllers\Public\PaymentController::class, 'pay'])->name('public.payment.pay');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Admin & Kasir Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,kasir'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/search', [AdminSearch::class, 'search'])->name('admin.search');

    // Queues
    Route::get('/antrean', [AdminQueue::class, 'index'])->name('admin.queues.index');
    Route::get('/antrean/buat', [AdminQueue::class, 'create'])->name('admin.queues.create');
    Route::post('/antrean', [AdminQueue::class, 'store'])->name('admin.queues.store');
    Route::patch('/antrean/{queue}/status', [AdminQueue::class, 'updateStatus'])->name('admin.queues.update_status');
    Route::post('/antrean/panggil', [AdminQueue::class, 'callNext'])->name('admin.queues.call_next');

    // Vehicle Handover (Pengambilan Kendaraan)
    Route::get('/penyerahan-kendaraan', [AdminHandover::class, 'index'])->name('admin.handover.index');
    Route::post('/penyerahan-kendaraan/{queue}', [AdminHandover::class, 'completeHandover'])->name('admin.handover.complete');

    // Customers & Vehicles
    Route::resource('pelanggan', AdminCustomer::class)->names('admin.customers');
    Route::resource('kendaraan', AdminVehicle::class)->only(['index', 'store', 'show', 'update'])->names('admin.vehicles');

    // Service Orders
    Route::get('/servis', [AdminServiceOrder::class, 'index'])->name('admin.service_orders.index');
    Route::get('/servis/{serviceOrder}', [AdminServiceOrder::class, 'show'])->name('admin.service_orders.show');
    Route::patch('/servis/{serviceOrder}/status', [AdminServiceOrder::class, 'updateStatus'])->name('admin.service_orders.update_status');
    Route::post('/servis/{serviceOrder}/assign-mechanic', [AdminServiceOrder::class, 'assignMechanic'])->name('admin.service_orders.assign_mechanic');
    Route::post('/servis/{serviceOrder}/add-service', [AdminServiceOrder::class, 'addService'])->name('admin.service_orders.add_service');
    Route::post('/servis/{serviceOrder}/add-sparepart', [AdminServiceOrder::class, 'addSparepart'])->name('admin.service_orders.add_sparepart');
    Route::post('/servis/{serviceOrder}/add-oil', [AdminServiceOrder::class, 'addOil'])->name('admin.service_orders.add_oil');
    Route::patch('/rekomendasi/{recommendation}', [AdminServiceOrder::class, 'updateRecommendation'])->name('admin.service_orders.update_recommendation');
    Route::post('/servis/{serviceOrder}/create-invoice', [AdminServiceOrder::class, 'createInvoice'])->name('admin.service_orders.create_invoice');

    // Wash Orders
    Route::get('/cuci', [AdminWashOrder::class, 'index'])->name('admin.wash_orders.index');
    Route::patch('/cuci/{washOrder}/status', [AdminWashOrder::class, 'updateStatus'])->name('admin.wash_orders.update_status');
    Route::post('/cuci/{washOrder}/assign-washer', [AdminWashOrder::class, 'assignWasher'])->name('admin.wash_orders.assign_washer');
    Route::post('/cuci/{washOrder}/create-invoice', [AdminWashOrder::class, 'createInvoice'])->name('admin.wash_orders.create_invoice');

    // Spareparts & Oils
    Route::get('/sparepart', [AdminSparepart::class, 'index'])->name('admin.spareparts.index');
    Route::post('/sparepart', [AdminSparepart::class, 'store'])->name('admin.spareparts.store');
    Route::put('/sparepart/{sparepart}', [AdminSparepart::class, 'update'])->name('admin.spareparts.update');
    Route::post('/sparepart/{sparepart}/add-stock', [AdminSparepart::class, 'addStock'])->name('admin.spareparts.add_stock');

    Route::get('/oli', [AdminOil::class, 'index'])->name('admin.oils.index');
    Route::post('/oli', [AdminOil::class, 'store'])->name('admin.oils.store');
    Route::put('/oli/{oilProduct}', [AdminOil::class, 'update'])->name('admin.oils.update');
    Route::post('/oli/{oilProduct}/add-stock', [AdminOil::class, 'addStock'])->name('admin.oils.add_stock');

    // Invoices & Payments (Kasir)
    Route::get('/transaksi', [AdminInvoice::class, 'index'])->name('admin.invoices.index');
    Route::get('/transaksi/{invoice}', [AdminInvoice::class, 'show'])->name('admin.invoices.show');
    Route::post('/transaksi/{invoice}/pembayaran', [AdminInvoice::class, 'processPayment'])->name('admin.invoices.payment');

    // Reports
    Route::get('/laporan', [AdminReport::class, 'index'])->name('admin.reports.index');
});

/*
|--------------------------------------------------------------------------
| Mechanic Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:mekanik,admin'])->prefix('mekanik')->group(function () {
    Route::get('/dashboard', [MechanicDashboard::class, 'index'])->name('mechanic.dashboard');
    Route::get('/pekerjaan/{serviceOrder}', [MechanicDashboard::class, 'showJob'])->name('mechanic.job_detail');
    Route::post('/pekerjaan/{serviceOrder}/pemeriksaan-mulai', [MechanicDashboard::class, 'startInspection'])->name('mechanic.start_inspection');
    Route::post('/pekerjaan/{serviceOrder}/pemeriksaan-simpan', [MechanicDashboard::class, 'storeInspection'])->name('mechanic.store_inspection');
    Route::post('/pekerjaan/{serviceOrder}/rekomendasi', [MechanicDashboard::class, 'addRecommendation'])->name('mechanic.add_recommendation');
    Route::post('/pekerjaan/{serviceOrder}/pengerjaan-mulai', [MechanicDashboard::class, 'startWork'])->name('mechanic.start_work');
    Route::post('/pekerjaan/{serviceOrder}/pengerjaan-selesai', [MechanicDashboard::class, 'completeWork'])->name('mechanic.complete_work');
});

/*
|--------------------------------------------------------------------------
| Washer Routes (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:cuci,admin'])->prefix('cuci')->group(function () {
    Route::get('/dashboard', [WasherDashboard::class, 'index'])->name('washer.dashboard');
    Route::patch('/cuci/{washOrder}/status', [WasherDashboard::class, 'updateStatus'])->name('washer.update_status');
});
