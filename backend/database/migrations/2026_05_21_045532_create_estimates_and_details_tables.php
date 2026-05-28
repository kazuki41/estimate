<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    // ① 親テーブル：見積もりの基本情報
    Schema::create('estimates', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 誰の見積もりか
      $table->string('title'); // 見積もりタイトル
      $table->integer('total_amount'); // 合計金額
      $table->timestamps();
    });

    // ② 子テーブル：見積もりの明細内訳（1つの見積もりに複数の明細が紐づく）
    Schema::create('estimate_details', function (Blueprint $table) {
      $table->id();
      $table->foreignId('estimate_id')->constrained()->onDelete('cascade'); // どの見積もりか
      $table->string('item_name'); // 項目名
      $table->string('category')->nullable(); // カテゴリ
      $table->integer('price'); // 単価
      $table->text('description')->nullable(); // 理由・説明
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('estimate_details');
    Schema::dropIfExists('estimates');
  }
};
