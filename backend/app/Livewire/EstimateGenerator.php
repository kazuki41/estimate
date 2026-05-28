<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\EstimateService;

class EstimateGenerator extends Component
{
    public $userRequest = ''; // ユーザーの入力内容
    public $results = null;   // AIからの返答（最終的なリスト）
    public $isLoading = false; // 読み込み中フラグ
    
    public function generate(EstimateService $service)
    {
        $this->isLoading = true;
        $this->results = null;

        try {
            $rawResponse = $service->generate($this->userRequest);
            
            // 1. 文字列を抽出
            $content = is_string($rawResponse) ? $rawResponse : json_encode($rawResponse);
            
            // 2. 記号などを掃除してJSONとして解析
            $json = preg_replace('/^```json\s*|```\s*$/', '', trim($content));
            $decoded = json_decode($json, true);

            // 3. 解析失敗時の再トライ（[ ] の中身だけ抜く）
            if (is_null($decoded)) {
                preg_match('/\[.*\]/s', $content, $matches);
                if (isset($matches[0])) {
                    $decoded = json_decode($matches[0], true);
                }
            }

            // 4. 【重要】データの形を「リスト」に強制変換
            if (is_array($decoded)) {
                // 'estimates' や 'items' というキーの中に配列が入っている場合の取り出し
                if (isset($decoded['estimates']) && is_array($decoded['estimates'])) {
                    $this->results = $decoded['estimates'];
                } elseif (isset($decoded['items']) && is_array($decoded['items'])) {
                    $this->results = $decoded['items'];
                } elseif (array_is_list($decoded)) {
                    // 直接リスト [ {}, {} ] が入っている場合
                    $this->results = $decoded;
                } else {
                    // それ以外（単一のオブジェクトなど）
                    $this->results = [$decoded];
                }
            }

        } catch (\Exception $e) {
            session()->flash('error', '生成エラー');
        }

        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.estimate-generator');
    }
}