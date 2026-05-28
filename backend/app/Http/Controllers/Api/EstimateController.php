<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EstimateService;
use Illuminate\Http\Request;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\EstimateDetail; // 💡 ★この1行が足りていない可能性が高いです！追記してください
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class EstimateController extends Controller
{
    public function generate(Request $request, EstimateService $service)
    {
        // 1. ユーザーからの入力を受け取る
        $userRequest = $request->input('userRequest');

        if (!$userRequest) {
            return response()->json(['error' => 'リクエストが空です'], 400);
        }

        try {
            // 2. AIサービス（以前作ったOpenAIの処理）を呼び出す
            $rawResponse = $service->generate($userRequest);
            
            // 3. 解析（以前のLivewireと同じ堅牢なロジック）
            $content = is_string($rawResponse) ? $rawResponse : json_encode($rawResponse);
            $json = preg_replace('/^```json\s*|```\s*$/', '', trim($content));
            $decoded = json_decode($json, true);

            if (is_null($decoded)) {
                preg_match('/\[.*\]/s', $content, $matches);
                if (isset($matches[0])) {
                    $decoded = json_decode($matches[0], true);
                }
            }

            // 4. データの整形
            $results = [];
            if (is_array($decoded)) {
                if (isset($decoded['estimates'])) {
                    $results = $decoded['estimates'];
                } elseif (isset($decoded['items'])) {
                    $results = $decoded['items'];
                } elseif (array_is_list($decoded)) {
                    $results = $decoded;
                }
            }

            $results = array_values(array_filter($results, function ($item) {
                return is_array($item)
                    && isset($item['name'])
                    && array_key_exists('price', $item);
            }));

            if (count($results) === 0) {
                $message = is_array($decoded) && isset($decoded['message'])
                    ? $decoded['message']
                    : '見積もり明細を生成できませんでした。マスタデータと要望を確認してください。';

                return response()->json(['error' => $message, 'message' => $message], 422);
            }

            // 5. JSON形式でデータを返す（これがNext.jsに届きます）
            return response()->json([
                'results' => $results,
                'total' => collect($results)->sum('price'),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => '生成エラー: ' . $e->getMessage()], 500);
        }
    }

    // 💾 見積もりを保存するAPI
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'total_amount' => 'required|integer',
        ]);

        // トランザクションをかけて安全に親子同時に保存
        $estimate = DB::transaction(function () use ($request) {
            // 1. 親（見積もり基本情報）を保存
            $estimate = Estimate::create([
                'user_id' => $request->user()->id, // ログイン中のユーザーID
                'title' => 'AI生成見積書_' . now()->format('Ymd_His'),
                'total_amount' => $request->total_amount,
            ]);

            // 2. 子（明細内訳）をループで全部保存
            foreach ($request->items as $item) {
                EstimateDetail::create([
                    'estimate_id' => $estimate->id,
                    'item_name' => $item['name'],
                    'category' => $item['category'] ?? '未分類',
                    'price' => (int)($item['price'] ?? 0),
                    'description' => $item['reason'] ?? '',
                ]);
            }

            return $estimate;
    });

        return response()->json([
            'message' => '見積もりを保存しました！',
            'estimate_id' => $estimate->id
        ]);
    }

    // 📄 保存されたデータからPDFを生成してダウンロードさせるAPI
    public function downloadPdf($id, Request $request)
    {
        // 該当の見積もりを明細付きで取得（他人の見積もりをダウンロードできないようガード）
        $estimate = Estimate::with('details')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        // PDFのレイアウト（Blade）にデータを渡してPDFファイルを生成
        // 日本語が文字化けしないよう、フォントに ipag (IPAゴシック) を指定するのがコツです
        $pdf = Pdf::loadView('pdf.estimate', compact('estimate'));
        
        return $pdf->download("estimate_{$id}.pdf");
    }
}