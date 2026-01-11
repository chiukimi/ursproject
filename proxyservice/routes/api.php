<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OilPriceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| 這裡是你的 API 路由，會被自動載入並使用 'api' 中介層。
|
*/

// 測試 API 路由
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

// 使用 Sanctum 驗證的 API（如果有用 Sanctum）
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 油價 API 路由
Route::get('/oil-prices', [OilPriceController::class, 'getOilPrices']);
