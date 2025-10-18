<?php 


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend as Frontend;
//====================  ====================
//================  Frontend ================
//====================  ====================


Route::get('/error-page', [Frontend\HomeController::class, 'error_page']);
Route::group(['middleware' => ['Member']], function () {
    Route::get('/member-booking', [Frontend\HomeController::class, 'member']);
});
Route::post('/login', [Frontend\HomeController::class, 'LogIn']);
Route::get('/logout', [Frontend\HomeController::class, 'LogOut']);

Route::get('/', [Frontend\HomeController::class, 'index']);
Route::get('/about', [Frontend\HomeController::class, 'about']);
Route::get('/aroundworld/{id}/{tyid}/{tid}', [Frontend\HomeController::class, 'aroundworld']);
Route::get('/around-detail/{id}', [Frontend\HomeController::class, 'around_detail']);
Route::get('/clients-company/{id}', [Frontend\HomeController::class, 'clients_company']);
Route::get('/clients-detail/{id}', [Frontend\HomeController::class, 'clients_detail']);
Route::get('/clients-review/{id}/{cid}', [Frontend\HomeController::class, 'clients_review']);
Route::get('/clients-govern/{id}', [Frontend\HomeController::class, 'clients_govern']);
Route::get('/news/{tyid}/{tid}', [Frontend\HomeController::class, 'news']);
Route::get('/news-detail/{id}', [Frontend\HomeController::class, 'news_detail']);
Route::get('/video/{id}/{cid}', [Frontend\HomeController::class, 'video']);
Route::post('/search-video', [Frontend\HomeController::class, 'search_video']);
Route::get('/faq', [Frontend\HomeController::class, 'faq']);
Route::get('/contact', [Frontend\HomeController::class, 'contact']);
// Route::get('/policy', [Frontend\HomeController::class, 'policy']);
// Route::get('/oversea/{id}', [Frontend\HomeController::class, 'oversea']);
// Route::get('/inthai/{id}', [Frontend\HomeController::class, 'inthai']);
Route::get('/promotiontour/{id}/{tid}', [Frontend\HomeController::class, 'promotiontour']);
Route::post('/promotiontour-filter', [Frontend\HomeController::class, 'promotion_filter']);
Route::get('/weekend', [Frontend\HomeController::class, 'weekend']);
Route::get('/weekend-landing/{id}', [Frontend\HomeController::class, 'weekend_landing']);
Route::post('/search-weekend', [Frontend\HomeController::class, 'search_weekend']);
Route::post('/search-airline', [Frontend\HomeController::class, 'search_airline']);
Route::get('/package/{id}', [Frontend\HomeController::class, 'package']);
Route::get('/package-detail/{id}', [Frontend\HomeController::class, 'package_detail']);
Route::get('/organizetour', [Frontend\HomeController::class, 'organizetour']);
Route::get('/wishlist', [Frontend\HomeController::class, 'wishlist']);
Route::get('/tour-summary', [Frontend\HomeController::class, 'tour_summary']);
Route::post('/record-view/{id}', [Frontend\HomeController::class, 'recordPageView']);
Route::post('/send-email', [Frontend\ContacFormController::class, 'sendmail']);
Route::get('/search-price', [Frontend\HomeController::class, 'search_price']);
Route::post('/data-filter', [Frontend\HomeController::class, 'filter_data']);

Route::get('/get-data', [Frontend\HomeController::class, 'get_data']);
Route::get('/pdf-data', [Frontend\HomeController::class, 'file_pdf']);
Route::get('auth/facebook', [Frontend\HomeController::class, 'redirectToFacebook'])->name('auth.facebook');
Route::get('auth/facebook/callback', [Frontend\HomeController::class, 'handleFacebookCallback']);
Route::get('/google', [Frontend\HomeController::class, 'redirectToGoogle'])->name('google');
Route::get('/google/callback', [Frontend\HomeController::class, 'handleGoogleCallback']);
Route::post('/line-login', [Frontend\HomeController::class, 'loginLine']);
Route::get('/line/callback', [Frontend\HomeController::class, 'line_callback']);
Route::post('/register', [Frontend\HomeController::class, 'register']);
Route::post('/update-message', [Frontend\HomeController::class, 'update_message']);
Route::post('/update-member', [Frontend\HomeController::class, 'update_member']);
Route::post('/forgot-password', [Frontend\HomeController::class, 'forgot']);
Route::post('/showmore', [Frontend\HomeController::class, 'showmore_promotion']);
Route::get('/showmore', [Frontend\HomeController::class, 'showmore_promotion']);
Route::post('/showmore-hot', [Frontend\HomeController::class, 'showmore_promotion_hot']);
Route::get('/showmore-hot', [Frontend\HomeController::class, 'showmore_promotion_hot']);
?>