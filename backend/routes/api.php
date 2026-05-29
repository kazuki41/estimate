<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\EstimateController;

// ログインしているユーザーだけが通れるエリア
Route::middleware(['auth:sanctum' , 'throttle:3,1'])->group(function () {

    // 現在のユーザー情報を取得する（元からあったやつ）
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

    // 2. ここに見積もりルートを復活！
    // api.php に書くことで、自動的にURLは「/api/estimate」になります
    Route::post('/estimate', [EstimateController::class, 'generate']);
    // ※ 'generate' の部分は、コントローラー内の関数名（indexやstoreなど）に合わせてください

    // 💡 ログイン中のみ叩けるエリアの中に追記してください
    Route::post('/estimates', [App\Http\Controllers\Api\EstimateController::class, 'store']);
    Route::get('/estimates/{id}/pdf', [App\Http\Controllers\Api\EstimateController::class, 'downloadPdf']);

});

// ログインの壁（group）の外側に書きます
// 
// Route::get('/test', function () {
//     return response()->json(['message' => 'Laravelと繋がったよ！']);
// });