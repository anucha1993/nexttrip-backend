<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webpanel as Webpanel;
//====================  ====================
//================  Backend ================
//====================  ====================


Route::get('webpanel/login', [Webpanel\AuthController::class, 'getLogin']);
Route::post('webpanel/login', [Webpanel\AuthController::class, 'postLogin']);
Route::get('webpanel/logout', [Webpanel\AuthController::class, 'logOut']);
Route::get('member/logout', [Webpanel\AuthController::class, 'logOut']);

Route::group(['middleware' => ['Webpanel']], function () {

    Route::prefix('webpanel')->group(function () {
        Route::get('/', [Webpanel\HomeController::class, 'index']);
        Route::post('/uploadimage_text', [Webpanel\HomeController::class, 'uploadimage_text'])->name('upload');
        Route::post('/store_image', [Webpanel\HomeController::class, 'store_image'])->name('store_image');
        

        // BACKEND CONTROLLER
        Route::prefix('testform')->group(function () {
            Route::get('/', [Webpanel\TestformController::class, 'index']);
            Route::post('/datatable', [Webpanel\TestformController::class, 'datatable']);
            Route::get('/add', [Webpanel\TestformController::class, 'add']);
            Route::post('/add', [Webpanel\TestformController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\TestformController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\TestformController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\TestformController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\TestformController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
       
        Route::prefix('slide')->group(function () {
            Route::get('/', [Webpanel\MainPageController::class, 'index']);
            Route::post('/datatable', [Webpanel\MainPageController::class, 'datatable']);
            Route::get('/add', [Webpanel\MainPageController::class, 'add']);
            Route::post('/add', [Webpanel\MainPageController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\MainPageController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\MainPageController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\MainPageController::class, 'destroy'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\MainPageController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/edit-status', [Webpanel\MainPageController::class, 'status_slide']);

        });
        Route::prefix('banner-cover')->group(function () {
            Route::get('/', [Webpanel\BannerController::class, 'index']);
            Route::post('/datatable', [Webpanel\BannerController::class, 'datatable']);
            Route::get('/edit/{id}', [Webpanel\BannerController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\BannerController::class, 'update'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('banner-ads')->group(function () {
            Route::get('/', [Webpanel\BannerAdsController::class, 'index']);
            Route::post('/datatable', [Webpanel\BannerAdsController::class, 'datatable']);
            Route::get('/add', [Webpanel\BannerAdsController::class, 'add']);
            Route::post('/add', [Webpanel\BannerAdsController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\BannerAdsController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\BannerAdsController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\BannerAdsController::class, 'destroy'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\BannerAdsController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/edit-status', [Webpanel\BannerAdsController::class, 'status_slide']);
        });
        Route::prefix('travel')->group(function () {
                Route::get('/', [Webpanel\TravelInfoController::class, 'index']);
                Route::post('/datatable', [Webpanel\TravelInfoController::class, 'datatable']);
                Route::get('/add', [Webpanel\TravelInfoController::class, 'add']);
                Route::post('/add', [Webpanel\TravelInfoController::class, 'insert']);
                Route::get('/{id}', [Webpanel\TravelInfoController::class, 'edit'])->where(['id' => '[0-9]+']);
                Route::post('/{id}', [Webpanel\TravelInfoController::class, 'update'])->where(['id' => '[0-9]+']);
                Route::get('/status/{id}', [Webpanel\TravelInfoController::class, 'status'])->where(['id' => '[0-9]+']);
                Route::get('/destroy/{id}', [Webpanel\TravelInfoController::class, 'destroy'])->where(['id' => '[0-9]+']);
                Route::post('/get-tag', [Webpanel\TravelInfoController::class, 'get_tag'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('tag-content')->group(function () {
            Route::get('/', [Webpanel\TagContentController::class, 'index']);
            Route::post('/datatable', [Webpanel\TagContentController::class, 'datatable']);
            Route::get('/add', [Webpanel\TagContentController::class, 'add']);
            Route::post('/add', [Webpanel\TagContentController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\TagContentController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\TagContentController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\TagContentController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('type-new')->group(function () {
            Route::get('/', [Webpanel\TypeNewController::class, 'index']);
            Route::post('/datatable', [Webpanel\TypeNewController::class, 'datatable']);
            Route::get('/add', [Webpanel\TypeNewController::class, 'add']);
            Route::post('/add', [Webpanel\TypeNewController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\TypeNewController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\TypeNewController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\TypeNewController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('type-article')->group(function () {
            Route::get('/', [Webpanel\TypeArticleController::class, 'index']);
            Route::post('/datatable', [Webpanel\TypeArticleController::class, 'datatable']);
            Route::get('/add', [Webpanel\TypeArticleController::class, 'add']);
            Route::post('/add', [Webpanel\TypeArticleController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\TypeArticleController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\TypeArticleController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\TypeArticleController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });

        // API Management Routes
        Route::prefix('api-management')->name('api-management.')->group(function () {
            Route::get('/', [Webpanel\ApiManagementController::class, 'index'])->name('index');
            Route::get('/test-index', function() { return view('backend.pages.api-management.test_index'); })->name('test-index');
            Route::get('/create', [Webpanel\ApiManagementController::class, 'create'])->name('create');
            Route::post('/', [Webpanel\ApiManagementController::class, 'store'])->name('store');
            Route::get('/{id}', [Webpanel\ApiManagementController::class, 'show'])->name('show')->where(['id' => '[0-9]+']);
            Route::get('/{id}/edit', [Webpanel\ApiManagementController::class, 'edit'])->name('edit')->where(['id' => '[0-9]+']);
            Route::put('/{id}', [Webpanel\ApiManagementController::class, 'update'])->name('update')->where(['id' => '[0-9]+']);
            Route::delete('/{id}', [Webpanel\ApiManagementController::class, 'destroy'])->name('destroy')->where(['id' => '[0-9]+']);
            
            // Additional API Management Actions
            Route::post('/{id}/toggle-status', [Webpanel\ApiManagementController::class, 'toggleStatus'])->name('toggle-status')->where(['id' => '[0-9]+']);
            Route::post('/{id}/test-connection', [Webpanel\ApiManagementController::class, 'testConnection'])->name('test-connection')->where(['id' => '[0-9]+']);
            Route::post('/{id}/sync-manual', [Webpanel\ApiManagementController::class, 'syncManual'])->name('sync-manual')->where(['id' => '[0-9]+']);
            Route::get('/sync', [Webpanel\ApiManagementController::class, 'syncFromProvider'])->name('sync');
            Route::get('/{id}/logs', [Webpanel\ApiManagementController::class, 'logs'])->name('logs')->where(['id' => '[0-9]+']);
            Route::get('/logs/{logId}', [Webpanel\ApiManagementController::class, 'logDetails'])->name('logs.details')->where(['logId' => '[0-9]+']);
            Route::post('/logs/{logId}/delete', [Webpanel\ApiManagementController::class, 'deleteLog'])->name('logs.delete')->where(['logId' => '[0-9]+']);
            Route::post('/{id}/clear-logs', [Webpanel\ApiManagementController::class, 'clearLogs'])->name('clear-logs')->where(['id' => '[0-9]+']);
            Route::get('/{id}/duplicates', [Webpanel\ApiManagementController::class, 'duplicates'])->name('duplicates')->where(['id' => '[0-9]+']);
            Route::post('/duplicates/{id}/merge', [Webpanel\ApiManagementController::class, 'mergeDuplicate'])->name('duplicates.merge')->where(['id' => '[0-9]+']);
            Route::post('/duplicates/{id}/ignore', [Webpanel\ApiManagementController::class, 'ignoreDuplicate'])->name('duplicates.ignore')->where(['id' => '[0-9]+']);
            
            // Schedule Management Routes
            Route::get('/{id}/schedules/{scheduleId}', [Webpanel\ApiManagementController::class, 'getSchedule'])->name('schedules.get')->where(['id' => '[0-9]+', 'scheduleId' => '[0-9]+']);
            Route::post('/{id}/schedules', [Webpanel\ApiManagementController::class, 'createSchedule'])->name('schedules.create')->where(['id' => '[0-9]+']);
            Route::put('/{id}/schedules/{scheduleId}', [Webpanel\ApiManagementController::class, 'updateSchedule'])->name('schedules.update')->where(['id' => '[0-9]+', 'scheduleId' => '[0-9]+']);
            Route::delete('/{id}/schedules/{scheduleId}', [Webpanel\ApiManagementController::class, 'deleteSchedule'])->name('schedules.delete')->where(['id' => '[0-9]+', 'scheduleId' => '[0-9]+']);
            Route::post('/{id}/schedules/{scheduleId}/test', [Webpanel\ApiManagementController::class, 'testSchedule'])->name('schedules.test')->where(['id' => '[0-9]+', 'scheduleId' => '[0-9]+']);
        });
        Route::prefix('customer-info')->group(function () {
            Route::get('/', [Webpanel\CustomerInfoController::class, 'index']);
            Route::post('/datatable', [Webpanel\CustomerInfoController::class, 'datatable']);
            Route::post('/edit-status', [Webpanel\CustomerInfoController::class, 'status_slide']);
            Route::get('/add', [Webpanel\CustomerInfoController::class, 'add']);
            Route::post('/add', [Webpanel\CustomerInfoController::class, 'insert']);
            Route::get('/{id}',[Webpanel\CustomerInfoController::class, 'edit']);
            Route::post('/{id}',[Webpanel\CustomerInfoController::class, 'update']);
            Route::get('/destroy/{id}', [Webpanel\CustomerInfoController::class, 'destroy']);
            Route::post('/{id}/datatable-gallery', [Webpanel\CustomerInfoController::class, 'datatable_gallery']);
            Route::get('/destroy-gallery/{id}', [Webpanel\CustomerInfoController::class, 'destroy_gallery']);
            Route::get('/edit-gallery/{id}',[Webpanel\CustomerInfoController::class, 'edit_gallery']);
            Route::post('/edit-gallery/{id}',[Webpanel\CustomerInfoController::class, 'update_gallery']);
           
        });
        Route::prefix('news')->group(function () {
            Route::get('/', [Webpanel\NewsController::class, 'index']);
            Route::post('/datatable', [Webpanel\NewsController::class, 'datatable']);
            Route::get('/add', [Webpanel\NewsController::class, 'add']);
            Route::post('/add', [Webpanel\NewsController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\NewsController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\NewsController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\NewsController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\NewsController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('video')->group(function () {
            Route::get('/', [Webpanel\VideoController::class, 'index']);
            Route::post('/datatable', [Webpanel\VideoController::class, 'datatable']);
            Route::get('/add', [Webpanel\VideoController::class, 'add']);
            Route::post('/add', [Webpanel\VideoController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\VideoController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\VideoController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\VideoController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\VideoController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('review')->group(function () {
            Route::get('/', [Webpanel\ReviewController::class, 'index']);
            Route::post('/datatable', [Webpanel\ReviewController::class, 'datatable']);
            Route::get('/add', [Webpanel\ReviewController::class, 'add']);
            Route::post('/add', [Webpanel\ReviewController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\ReviewController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\ReviewController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\ReviewController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\ReviewController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('about-us')->group(function () {  
            Route::get('/{id}', [Webpanel\AboutUsController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/{id}', [Webpanel\AboutUsController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/', [Webpanel\AboutUsController::class, 'index']);
            Route::post('/{id}/datatable-gallery', [Webpanel\AboutUsController::class, 'datatable_gallery']);
            Route::get('/add-gallery', [Webpanel\AboutUsController::class, 'add_gallery']);
            Route::post('/add-gallery', [Webpanel\AboutUsController::class, 'insert_gallery']);
            Route::get('/edit-gallery/{id}',[Webpanel\AboutUsController::class, 'edit_gallery']);
            Route::post('/edit-gallery/{id}',[Webpanel\AboutUsController::class, 'update_gallery']);
            Route::get('/destroy-gallery/{id}', [Webpanel\AboutUsController::class, 'destroy_gallery']);
            Route::post('/{id}/datatable-licens', [Webpanel\AboutUsController::class, 'datatable_licens']);
            Route::get('/add-licens', [Webpanel\AboutUsController::class, 'add_licens']);
            Route::post('/add-licens', [Webpanel\AboutUsController::class, 'insert_licens']);
            Route::get('/edit-licens/{id}',[Webpanel\AboutUsController::class, 'edit_licens']);
            Route::post('/edit-licens/{id}',[Webpanel\AboutUsController::class, 'update_licens']);
            Route::get('/destroy-licens/{id}', [Webpanel\AboutUsController::class, 'destroy_licens']);
        });
        Route::prefix('business-info')->group(function () {
            Route::get('/', [Webpanel\BusinessInfoController::class, 'index']);
            Route::post('/datatable', [Webpanel\BusinessInfoController::class, 'datatable']);
            Route::get('/add', [Webpanel\BusinessInfoController::class, 'add']);
            Route::post('/add', [Webpanel\BusinessInfoController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\BusinessInfoController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\BusinessInfoController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\BusinessInfoController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('customer-groups')->group(function () {
            Route::get('/', [Webpanel\CustomerGroupsController::class, 'index']);
            Route::post('/datatable', [Webpanel\CustomerGroupsController::class, 'datatable']);
            Route::get('/add', [Webpanel\CustomerGroupsController::class, 'add']);
            Route::post('/add', [Webpanel\CustomerGroupsController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\CustomerGroupsController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\CustomerGroupsController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\CustomerGroupsController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\CustomerGroupsController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('question')->group(function () {
            Route::get('/', [Webpanel\QuestionController::class, 'index']);
            Route::post('/datatable', [Webpanel\QuestionController::class, 'datatable']);
            Route::get('/add', [Webpanel\QuestionController::class, 'add']);
            Route::post('/add', [Webpanel\QuestionController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\QuestionController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\QuestionController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\QuestionController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\QuestionController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('contact')->group(function () {
            Route::get('/{id}', [Webpanel\ContactUsController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/{id}', [Webpanel\ContactUsController::class, 'update'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('contact-form')->group(function () {
            Route::get('/', [Webpanel\ContacFormController::class, 'index']);
            Route::post('/datatable', [Webpanel\ContacFormController::class, 'datatable']);
            Route::get('/view/{id}', [Webpanel\ContacFormController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/view/{id}', [Webpanel\ContacFormController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\ContacFormController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('group')->group(function () {  
            Route::get('/{id}', [Webpanel\GroupController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/{id}', [Webpanel\GroupController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/', [Webpanel\GroupController::class, 'index']);
            Route::post('/{id}/datatable-group', [Webpanel\GroupController::class, 'datatable_group']);
            Route::get('/add-group', [Webpanel\GroupController::class, 'add_group']);
            Route::post('/add-group', [Webpanel\GroupController::class, 'insert_group']);
            Route::get('/edit-group/{id}',[Webpanel\GroupController::class, 'edit_group']);
            Route::post('/edit-group/{id}',[Webpanel\GroupController::class, 'update_group']);
            Route::get('/destroy-group/{id}', [Webpanel\GroupController::class, 'destroy_group']);
            Route::get('/status/{id}', [Webpanel\GroupController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/{id}/datatable-gallery', [Webpanel\GroupController::class, 'datatable_gallery']);
            Route::get('/add-gallery', [Webpanel\GroupController::class, 'add_gallery']);
            Route::post('/add-gallery', [Webpanel\GroupController::class, 'insert_gallery']);
            Route::get('/edit-gallery/{id}',[Webpanel\GroupController::class, 'edit_gallery']);
            Route::post('/edit-gallery/{id}',[Webpanel\GroupController::class, 'update_gallery']);
            Route::get('/destroy-gallery/{id}', [Webpanel\GroupController::class, 'destroy_gallery']);
            Route::post('/{id}/datatable-list', [Webpanel\GroupController::class, 'datatable_list']);
            Route::get('/edit-list/{id}',[Webpanel\GroupController::class, 'edit_list']);
            Route::post('/edit-list/{id}',[Webpanel\GroupController::class, 'update_list']);
        });
        Route::prefix('promotion')->group(function () {
            Route::get('/', [Webpanel\PromotionController::class, 'index']);
            Route::post('/datatable', [Webpanel\PromotionController::class, 'datatable']);
            Route::get('/add', [Webpanel\PromotionController::class, 'add']);
            Route::post('/add', [Webpanel\PromotionController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\PromotionController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\PromotionController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\PromotionController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\PromotionController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('promotion-tag')->group(function () {
            Route::get('/', [Webpanel\PromotionTagController::class, 'index']);
            Route::post('/datatable', [Webpanel\PromotionTagController::class, 'datatable']);
            Route::get('/add', [Webpanel\PromotionTagController::class, 'add']);
            Route::post('/add', [Webpanel\PromotionTagController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\PromotionTagController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\PromotionTagController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\PromotionTagController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('travel-type')->group(function () {
            Route::get('/', [Webpanel\TravelTypeController::class, 'index']);
            Route::post('/datatable', [Webpanel\TravelTypeController::class, 'datatable']);
            Route::get('/add', [Webpanel\TravelTypeController::class, 'add']);
            Route::post('/add', [Webpanel\TravelTypeController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\TravelTypeController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\TravelTypeController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\TravelTypeController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\TravelTypeController::class, 'destroy'])->where(['id' => '[0-9]+']);
        });
        Route::prefix('footer')->group(function () {  
            Route::get('/{id}', [Webpanel\FooterController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/{id}', [Webpanel\FooterController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/', [Webpanel\FooterController::class, 'index']);
            Route::post('/{id}/datatable-list', [Webpanel\FooterController::class, 'datatable_list']);
            Route::get('/edit-list/{id}',[Webpanel\FooterController::class, 'edit_list']);
            Route::post('/edit-list/{id}',[Webpanel\FooterController::class, 'update_list']);
        });
        Route::prefix('calendar')->group(function () {  
            Route::get("/gen-calendar", [Webpanel\CalendarController::class, 'calendar']);
            Route::get('/', [Webpanel\CalendarController::class, 'index']);
            Route::post('/datatable', [Webpanel\CalendarController::class, 'datatable']);
            Route::get('/add', [Webpanel\CalendarController::class, 'add']);
            Route::post('/add', [Webpanel\CalendarController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\CalendarController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\CalendarController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\CalendarController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\CalendarController::class, 'destroy'])->where(['id' => '[0-9]+']);
            Route::post('/edit-status', [Webpanel\CalendarController::class, 'status_slide']);
        });
        Route::prefix('package')->group(function () {
            Route::get('/', [Webpanel\PackageController::class, 'index']);
            Route::post('/datatable', [Webpanel\PackageController::class, 'datatable']);
            Route::get('/add', [Webpanel\PackageController::class, 'add']);
            Route::post('/add', [Webpanel\PackageController::class, 'insert']);
            Route::get('/{id}', [Webpanel\PackageController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/{id}', [Webpanel\PackageController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\PackageController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/destroy/{id}', [Webpanel\PackageController::class, 'destroy'])->where(['id' => '[0-9]+']);
            Route::get('/destroy-pdf/{id}', [Webpanel\PackageController::class, 'destroy_pdf']);
        });
        // END

        // System Dev
        Route::prefix('menu')->group(function () {
            Route::get('/', [Webpanel\MenuController::class, 'index']);
            Route::get('/showsubmenu', [Webpanel\MenuController::class, 'showsubmenu']);
            Route::get('/datatable', [Webpanel\MenuController::class, 'datatable']);
            Route::get('/add', [Webpanel\MenuController::class, 'add']);
            Route::post('/add', [Webpanel\MenuController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\MenuController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\MenuController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/icon', [Webpanel\MenuController::class, 'icon']);
            Route::get('/status/{id}', [Webpanel\MenuController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::post('/changesort', [Webpanel\MenuController::class, 'changesort'])->where(['id' => '[0-9]+']);
            Route::post('/changesort_sub', [Webpanel\MenuController::class, 'changesort_sub'])->where(['id' => '[0-9]+']);
            Route::get('/destroy', [Webpanel\MenuController::class, 'destroy']);
            Route::get('/destroy_sub', [Webpanel\MenuController::class, 'destroy_sub']);
        });

        Route::prefix('role')->group(function () {
            Route::get('/', [Webpanel\RoleController::class, 'index']);
            Route::get('/datatable', [Webpanel\RoleController::class, 'datatable']);
            Route::get('/add', [Webpanel\RoleController::class, 'add']);
            Route::post('/add', [Webpanel\RoleController::class, 'insert']);
            Route::post('/menu/{id}', [Webpanel\RoleController::class, 'update_active_menu'])->where(['id' => '[0-9]+']);
            Route::get('/edit/{id}', [Webpanel\RoleController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\RoleController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\RoleController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/destroy', [Webpanel\RoleController::class, 'destroy']);
        });

        Route::prefix('user')->group(function () {
            Route::get('/', [Webpanel\UserController::class, 'index']);
            Route::post('/datatable', [Webpanel\UserController::class, 'datatable']);
            Route::get('/add', [Webpanel\UserController::class, 'add']);
            Route::post('/add', [Webpanel\UserController::class, 'insert']);
            Route::get('/edit/{id}', [Webpanel\UserController::class, 'edit'])->where(['id' => '[0-9]+']);
            Route::post('/edit/{id}', [Webpanel\UserController::class, 'update'])->where(['id' => '[0-9]+']);
            Route::get('/status/{id}', [Webpanel\UserController::class, 'status'])->where(['id' => '[0-9]+']);
            Route::get('/destroy', [Webpanel\UserController::class, 'destroy']);
        });

    });
});
?>