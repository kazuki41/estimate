<?php

namespace App\Services;

use App\Models\EstimateItem;
use OpenAI\Laravel\Facades\OpenAI;

class EstimateService
{
    public function generate($userRequest)
    {
        // 1. 登録した単価マスタをすべて取得
        $items = EstimateItem::all(['name', 'price', 'description'])->toArray();
        $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE);

        // 2. AIへのプロンプト（指示）
        $prompt = "
        あなたはシステム開発の見積もり担当者です。
        【マスタデータ】にある項目名と価格を組み合わせて見積もりを作成してください。

        【出力ルール（厳守）】
        1. 返却は必ず純粋なJSON配列のみにしてください。
        2. JSONの形式は [{\"name\":\"項目名\", \"price\":10000, \"reason\":\"理由\"}] のようにしてください。
        3. 'estimates' や 'items' といった外枠のキーは絶対に付けず、[ ] から始めてください。

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
