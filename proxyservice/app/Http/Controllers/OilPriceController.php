<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OilPriceController extends Controller
{
    public function getOilPrices()
    {
        try {
            // 目標 API
            $apiUrl = 'https://vipmbr.cpc.com.tw/cpcstn/listpricewebservice.asmx/getCPCMainProdListPrice';

            // 發送 GET 請求
            $response = Http::get($apiUrl);

            // 檢查請求是否成功
            if ($response->failed()) {
                return response()->json(['error' => '獲取油價數據失敗'], 500);
            }

            // 回傳 XML 數據
            return response($response->body(), 200)->header('Content-Type', 'application/xml');
        } catch (\Exception $e) {
            return response()->json(['error' => '伺服器錯誤', 'details' => $e->getMessage()], 500);
        }
    }
}
