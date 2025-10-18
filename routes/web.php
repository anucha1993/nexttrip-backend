<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webpanel as Webpanel;
use App\Http\Controllers\Member as Member;
use App\Http\Controllers\Frontend as Frontend;


Route::get('/', function () {
    $host = request()->getHost();

    // เช็คเฉพาะโดเมน nexttrip.asia หรือ www.nexttrip.asia
    if (in_array($host, ['nexttrip.asia', 'www.nexttrip.asia'])) {
        // ✅ รีไดเรกต์ไปหน้า /webpanel แบบ permanent
        return redirect()->to('https://nexttrip.asia/webpanel', 301);
    }

    // ถ้าไม่ใช่โดเมนนี้ ให้ทำงานปกติ
    return view('welcome'); // หรือจะโยนไป controller หลักของคุณก็ได้
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//========== Session Lang (กรณี 2 ภาษา) ============
// Route::get('/set/lang/{lang}', [Frontend\HomeController::class, 'setLang']);
// Route::get('/', function () {
//     $default = 'th';
//     $lang = Session('lang');
//     if ($lang == "") {
//         Session::put('lang', $default);
//         return redirect("/$default");
//     } else {
//         return redirect("/$lang");
//     }
// });
// Route::group(['middleware' => ['Language']], function () {
//     $locale = ['th', 'en', 'jp'];
//     foreach ($locale as $lang) {
//         Route::prefix($lang)->group(function () {
//             Route::get('', [Frontend\HomeController::class, 'index']);
//         });
//     }
// });
//========== Session Lang ============


// Route ภาษาเดียว
// Route::get('', [Frontend\HomeController::class, 'index']);

require('web-frontend.php');

require('web-frontend2.php');

require('web-backend.php');

require('web-backend2.php');


