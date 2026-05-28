# AIシステム見積もりアプリ (AI Estimate Generator)

このプロジェクトは、Laravel + Filament + Livewire + OpenAI API を組み合わせた、AIによる自動見積もり生成システムです。
管理画面から登録した単価マスタ（項目名と金額）を元に、AIがユーザーの要望に最適な項目を選択し、見積もり表を自動作成します。

## 🚀 主な機能

- **AI自動見積もり生成**: ユーザーのテキスト入力（要望）から、最適な開発項目と金額をAIが選定。
- **単価マスタ管理**: Filamentを使用した管理画面から、見積もりの根拠となる項目と金額を自由に登録・編集。
- **リアルタイムUI**: Livewireにより、ページ遷移なしでスムーズに見積もり結果を表示。
- **専用管理URL**: セキュリティを考慮し、管理画面のURLをカスタマイズ済み。

## 🛠 使用技術

- **Backend**: Laravel 11 / PHP 8.2+
- **Frontend**: Livewire v3 / Tailwind CSS
- **Admin Panel**: Filament v3
- **AI**: OpenAI API (GPT-4o / GPT-4o-mini)
- **Database**: SQLite

