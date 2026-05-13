<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\EstimateService;

class EstimateGenerator extends Component
{
    public $userRequest = ''; // ユーザーの入力内容
    public $results = null;   // AIからの返答
    public $isLoading = false; // 読み込み中フラグ

    public function generate(EstimateService $service)
{
    $this->isLoading = true;
    
    // AIからの返答を受け取る
    $response = $service->generate($this->userRequest);

    // 【重要】もし$responseが「文字列」で届いていたら、配列に変換する
    if (is_string($response)) {
        $response = json_decode($response, true);
    }

    // データの階層を判定して結果に入れる
    if (isset($response['estimates'])) {
        $this->results = $response['estimates'];
    } elseif (isset($response['items'])) {
        $this->results = $response['items'];
    } else {
        // 直接配列が入っている場合
        $this->results = $response;
    }

    $this->isLoading = false;
}

    public function render()
    {
        return view('livewire.estimate-generator');
    }
}
