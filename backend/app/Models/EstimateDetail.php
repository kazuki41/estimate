<?php

namespace App\Models; // 💡 ★超重要！このファイルの「住所」を宣言します

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // 💡 ★Modelを正しく使うためのインポート

class EstimateDetail extends Model
{
    use HasFactory;

    // 💡 テーブル名を明示的に指定（安全のため）
    protected $table = 'estimate_details';

    // 💡 保存（一括代入）を許可するカラムのホワイトリスト
    protected $fillable = [
        'estimate_id',
        'item_name',
        'category',
        'price',
        'description'
    ];

    // 💡 親である「見積もり基本情報（Estimate）」への繋がりを定義
    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
}