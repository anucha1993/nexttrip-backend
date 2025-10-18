<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend as Frontend;
//====================  ====================
//================  Frontend ===============
//====================  ====================


Route::get('/wishlist/{id}', [Frontend\FrontController::class, 'wishlist']);
Route::post('/wishlist-country', [Frontend\FrontController::class, 'wishlist_country']);
Route::post('/get-like-tours', [Frontend\FrontController::class, 'getLikedTours']);
Route::post('/subscribe', [Frontend\FrontController::class, 'subscribe']);
Route::get('/policy', [Frontend\FrontController::class, 'policy']);
Route::get('/oversea/{main_slug}', [Frontend\FrontController::class, 'oversea'])->name('oversea_route');
Route::get('/inthai/{main_slug}', [Frontend\FrontController::class, 'inthai'])->name('inthai_route');
Route::get('/tour/{detail_slug}', [Frontend\FrontController::class, 'tour_detail']);
Route::post('/record-country-view', [Frontend\FrontController::class, 'recordPageView']);
Route::get('/booking/{detail_slug}/{id}', [Frontend\FrontController::class, 'tour_summary']);
Route::post('/load-price', [Frontend\FrontController::class, 'loadPrice']);
Route::post('/booking', [Frontend\FrontController::class, 'booking']);
Route::get('/booking-success', [Frontend\FrontController::class, 'booking_success']);
Route::post('/search-filter', [Frontend\FrontController::class, 'search_filter']);
Route::post('/search-airline', [Frontend\FrontController::class, 'search_airline']);
Route::post('/search-country', [Frontend\FrontController::class, 'search_country']);
Route::post('/search-city', [Frontend\FrontController::class, 'search_city']);
Route::post('/search-amupur', [Frontend\FrontController::class, 'search_amupur']);
Route::post('/search-inthai', [Frontend\FrontController::class, 'search_inthai']);
Route::post('/search-tour', [Frontend\FrontController::class, 'search_total']);
Route::get('/search-tour', [Frontend\FrontController::class, 'search_total']);
Route::post('/clear-search', [Frontend\FrontController::class, 'clear_search']);
Route::post('/filter-oversea', [Frontend\FrontController::class, 'filter_oversea']);
Route::post('/filter-inthai', [Frontend\FrontController::class, 'filter_inthai']);
Route::post('/filter-search', [Frontend\FrontController::class, 'filter_search']);
?>