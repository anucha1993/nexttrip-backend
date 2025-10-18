<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webpanel as Webpanel;
use App\Http\Controllers\Functions as Functions;
//====================  ====================
//================  Backend ================
//====================  ====================

Route::get('/package_tour', [Functions\ApiController::class, 'package_tour']);
Route::get('/test_api', [Functions\ApiController::class, 'package_tour']);
Route::get('/test_api_liw', [Functions\TestApiController::class, 'test_api']);
Route::get('/check-send', [Webpanel\SubscribeController::class, 'check_send']);

Route::group(['middleware' => ['Webpanel']], function () {

    Route::prefix('webpanel')->group(function () {

        // Route::get('/api_package_tour', [Functions\ApiController::class, 'package_tour']);
        Route::post('call-period', [Webpanel\BookingFormController::class, 'call_period']);
        
        Route::prefix('terms')->group(function () {
            Route::get('/', [Webpanel\TermsController::class, 'index']);
            Route::post('/datatable', [Webpanel\TermsController::class, 'datatable']);
            Route::get('/add', [Webpanel\TermsController::class, 'add']);
            Route::post('/add', [Webpanel\TermsController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\TermsController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\TermsController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\TermsController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/delete', [Webpanel\TermsController::class, 'destroy']);
        });

        Route::prefix('subscribe')->group(function () {

            Route::prefix('email')->group(function () {
                Route::get('/', [Webpanel\SubscribeController::class, 'mail']);
                Route::post('/', [Webpanel\SubscribeController::class, 'sendmail']);
            });

            Route::prefix('data')->group(function () {
                Route::get('/', [Webpanel\SubscribeController::class, 'index']);
                Route::post('/datatable', [Webpanel\SubscribeController::class, 'datatable']);
                Route::post('/delete', [Webpanel\SubscribeController::class, 'destroy']);
                Route::get('/export-excel', [Webpanel\SubscribeController::class, 'export_excel']);
                Route::get('/export-csv', [Webpanel\SubscribeController::class, 'export_csv']);
            });
        });

        Route::prefix('landmass')->group(function () {
            Route::get('/', [Webpanel\LandmassController::class, 'index']);
            Route::post('/datatable', [Webpanel\LandmassController::class, 'datatable']);
            Route::get('/add', [Webpanel\LandmassController::class, 'add']);
            Route::post('/add', [Webpanel\LandmassController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\LandmassController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\LandmassController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\LandmassController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/delete', [Webpanel\LandmassController::class, 'destroy']);
        });

        Route::prefix('country')->group(function () {
            Route::get('/', [Webpanel\CountryController::class, 'index']);
            Route::post('/datatable', [Webpanel\CountryController::class, 'datatable']);
            Route::get('/add', [Webpanel\CountryController::class, 'add']);
            Route::post('/add', [Webpanel\CountryController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\CountryController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\CountryController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\CountryController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/delete', [Webpanel\CountryController::class, 'destroy']);
        });

        Route::prefix('city')->group(function () {
            Route::get('/', [Webpanel\CityController::class, 'index']);
            Route::post('/datatable', [Webpanel\CityController::class, 'datatable']);
            Route::get('/add', [Webpanel\CityController::class, 'add']);
            Route::post('/add', [Webpanel\CityController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\CityController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\CityController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\CityController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/delete', [Webpanel\CityController::class, 'destroy']);
        });

        Route::prefix('province')->group(function () {
            Route::get('/', [Webpanel\ProvinceController::class, 'index']);
            Route::post('/datatable', [Webpanel\ProvinceController::class, 'datatable']);
            Route::get('/add', [Webpanel\ProvinceController::class, 'add']);
            Route::post('/add', [Webpanel\ProvinceController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\ProvinceController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\ProvinceController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\ProvinceController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/delete', [Webpanel\ProvinceController::class, 'destroy']);
        });

        Route::prefix('district')->group(function () {
            Route::get('/', [Webpanel\DistrictController::class, 'index']);
            Route::post('/datatable', [Webpanel\DistrictController::class, 'datatable']);
            Route::get('/add', [Webpanel\DistrictController::class, 'add']);
            Route::post('/add', [Webpanel\DistrictController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\DistrictController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\DistrictController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\DistrictController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/delete', [Webpanel\DistrictController::class, 'destroy']);
        });

        Route::prefix('tour-type')->group(function () {
            Route::get('/', [Webpanel\TourTypeController::class, 'index']);
            Route::post('/datatable', [Webpanel\TourTypeController::class, 'datatable']);
            Route::get('/add', [Webpanel\TourTypeController::class, 'add']);
            Route::post('/add', [Webpanel\TourTypeController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\TourTypeController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\TourTypeController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\TourTypeController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/delete', [Webpanel\TourTypeController::class, 'destroy']);
        });

        Route::prefix('wholesale')->group(function () {
            Route::get('/', [Webpanel\WholesaleController::class, 'index']);
            Route::post('/datatable', [Webpanel\WholesaleController::class, 'datatable']);
            Route::get('/add', [Webpanel\WholesaleController::class, 'add']);
            Route::post('/add', [Webpanel\WholesaleController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\WholesaleController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\WholesaleController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\WholesaleController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/delete', [Webpanel\WholesaleController::class, 'destroy']);
        });

        Route::prefix('tour')->group(function () {
            Route::get('/manual', [Webpanel\TourController::class, 'updateManual']);
            Route::get('/download-and-save', [Webpanel\TourController::class, 'downloadAndSave']);
            Route::get('/', [Webpanel\TourController::class, 'index']);
            Route::post('/datatable', [Webpanel\TourController::class, 'datatable']);
            Route::get('/add', [Webpanel\TourController::class, 'add']);
            Route::post('/add', [Webpanel\TourController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\TourController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\TourController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status-edit/{id}', [Webpanel\TourController::class, 'status_edit'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\TourController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/tab_status/{id}', [Webpanel\TourController::class, 'tab_status'])->where(['id' => '[0-9]+']);
            Route::post('/delete', [Webpanel\TourController::class, 'destroy']);
            Route::get('/destroy-pdf/{id}', [Webpanel\TourController::class, 'destroy_pdf']);
            Route::get('/destroy-word/{id}', [Webpanel\TourController::class, 'destroy_word']);
            Route::get('/change-status-display', [Webpanel\TourController::class, 'change_status_display']);
            Route::get('/change-status-period', [Webpanel\TourController::class, 'change_status_period']);
            Route::get('/edit-period/{id}',[Webpanel\TourController::class, 'edit_period']);
            Route::post('/edit-period/{id}',[Webpanel\TourController::class, 'update_period']);
            Route::get('/destroy-period/{id}', [Webpanel\TourController::class, 'destroy_period']);
            
            Route::post('/{id}/datatable-gallery', [Webpanel\TourController::class, 'datatable_gallery']);
            Route::get('/destroy-gallery/{id}', [Webpanel\TourController::class, 'destroy_gallery']);
            Route::get('/edit-gallery/{id}',[Webpanel\TourController::class, 'edit_gallery']);
            Route::post('/edit-gallery/{id}',[Webpanel\TourController::class, 'update_gallery']);

            Route::get('/edit-pdf', [Webpanel\TourController::class, 'edit_pdf']);
            Route::post('/edit-pdf', [Webpanel\TourController::class, 'edit_data_pdf']);
        });

        Route::prefix('booking-form')->group(function () {
            Route::get('/', [Webpanel\BookingFormController::class, 'index']);
            Route::post('/datatable', [Webpanel\BookingFormController::class, 'datatable']);
            Route::get('/add', [Webpanel\BookingFormController::class, 'add']);
            Route::post('/add', [Webpanel\BookingFormController::class, 'insert']);
            Route::get('/view/{id}', [Webpanel\BookingFormController::class, 'view'])->where(['id' => '[0-9]+']);
            Route::get('/edit/{id}', [Webpanel\BookingFormController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\BookingFormController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::post('/load-price', [Webpanel\BookingFormController::class, 'loadPrice']);
            Route::post('/edit/{id}/load-price', [Webpanel\BookingFormController::class, 'loadPrice'])->where(['id' => '[0-9]+']);
            Route::get('/confirm/{id}', [Webpanel\BookingFormController::class, 'confirm'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\BookingFormController::class, 'destroy'])->where(['id' => '[0-9]+']);
            Route::get('/edit-detail/{id}', [Webpanel\BookingFormController::class, 'edit_detail']);
            Route::post('/edit-detail/{id}', [Webpanel\BookingFormController::class, 'update_detail']);
        });
        Route::prefix('member')->group(function () {
            Route::get('/', [Webpanel\MemberController::class, 'index']);
            Route::post('/datatable', [Webpanel\MemberController::class, 'datatable']);
            Route::get('/add', [Webpanel\MemberController::class, 'add']);
            Route::post('/add', [Webpanel\MemberController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\MemberController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\MemberController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\MemberController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/delete', [Webpanel\MemberController::class, 'destroy']);
        });
    });
});
?>