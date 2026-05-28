<?php

namespace Database\Seeders;

use App\Models\EstimateItem;
use Illuminate\Database\Seeder;

class EstimateItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => '会員登録・ログイン機能',
                'price' => 80000,
                'category' => '機能開発',
                'description' => 'ユーザー登録、ログイン、ログアウト、パスワードリセット',
            ],
            [
                'name' => 'お問い合わせフォーム',
                'price' => 30000,
                'category' => '機能開発',
                'description' => 'フォーム作成、バリデーション、メール通知',
            ],
            [
                'name' => '管理画面（CRUD）',
                'price' => 120000,
                'category' => '機能開発',
                'description' => 'データの一覧・登録・編集・削除',
            ],
            [
                'name' => 'レスポンシブ対応',
                'price' => 50000,
                'category' => 'デザイン',
                'description' => 'スマートフォン・タブレット表示の最適化',
            ],
            [
                'name' => '基本設計・要件定義',
                'price' => 100000,
                'category' => '設計',
                'description' => 'ヒアリング、画面設計、仕様書作成',
            ],
        ];

        foreach ($items as $item) {
            EstimateItem::firstOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
