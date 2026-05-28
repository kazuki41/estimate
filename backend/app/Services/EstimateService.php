<?php

namespace App\Services;

use App\Models\EstimateItem;
use OpenAI\Laravel\Facades\OpenAI;

class EstimateService
{
    public function generate($userRequest)
    {
        // 1. 登録した単価マスタをすべて取得
        $items = EstimateItem::all(['name', 'price', 'category', 'description'])->toArray();

        if (count($items) === 0) {
            throw new \RuntimeException(
                '見積マスタデータが未登録です。管理画面（/admin/estimate-items）から単価マスタを登録してください。'
            );
        }

        $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);

        // 2. AIへのプロンプト（指示）
        $prompt = "
        あなたは開発の見積もり担当者です。
        【マスタデータ】にある項目名と価格を組み合わせて見積もりを作成してください。

        【出力ルール（厳守）】
        1. 返却は必ず JSON オブジェクト 1 つにしてください。
        2. 形式は {\"items\":[{\"name\":\"項目名\",\"price\":10000,\"reason\":\"理由\",\"category\":\"カテゴリ\"}]} とし、明細は必ず items 配列に入れてください。
        3. 各要素には name（文字列）と price（数値）を必ず含めてください。

        【金額計算の厳格なルール】
        - 項目に「5ページ分」「3個」のように数量が含まれる場合は、必ず「単価 × 数量」を計算した【合計金額】を算出し、それを price に設定してください。単価のまま出力してはいけません。

        【マスタデータ】
        {$itemsJson}

        【ユーザー要望】
        {$userRequest}
        ";

        // 3. API呼び出し
        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant that outputs JSON.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
        ]);

        return json_decode($result->choices[0]->message->content, true);
    }
}
