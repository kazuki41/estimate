<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>御見積書</title>
  <style>
    @font-face {
      font-family: 'SawarabiGothic';
      src: url('{{ storage_path("fonts/SawarabiGothic-Regular.ttf") }}') format('truetype');
      font-weight: normal;
      font-style: normal;
    }

    /* 💡 【超重要】太字用の指示が来ても、通常体を使うように重ねて登録 */
    @font-face {
      font-family: 'SawarabiGothic';
      src: url('{{ storage_path("fonts/SawarabiGothic-Regular.ttf") }}') format('truetype');
      font-weight: bold;
      font-style: normal;
    }

    body {
      font-family: 'SawarabiGothic', sans-serif;
      color: #1e293b;
      /* スレートグレー（真っ黒よりおしゃれになります） */
      line-height: 1.6;
    }

    /* 💡 【超重要】すべての見出しや太字タグの「font-weight: bold」を強制解除して文字化けを阻止 */
    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    strong,
    b,
    th,
    .title {
      font-weight: normal !important;
    }

    .header {
      text-align: center;
      margin-bottom: 40px;
    }

    /* 太字の代わりに、文字の大きさと2重線でタイトルを引き締めます */
    .title {
      font-size: 26px;
      letter-spacing: 6px;
      border-bottom: 3px double #1e293b;
      padding-bottom: 8px;
      display: inline-block;
      width: 40%;
      margin: 0 auto;
    }

    .meta-table {
      width: 100%;
      margin-bottom: 30px;
    }

    .meta-table td {
      border: none;
      font-size: 13px;
    }

    /* 合計金額ボックス */
    .total-box {
      background-color: #f1f5f9;
      border-left: 6px solid #2563eb;
      /* 左側にアクセントの青い線を入れます */
      padding: 15px 20px;
      text-align: left;
      font-size: 18px;
      margin-bottom: 40px;
    }

    .total-amount {
      float: right;
      font-size: 22px;
      color: #2563eb;
    }

    /* 明細テーブル */
    .section-title {
      font-size: 15px;
      border-bottom: 1px solid #cbd5e1;
      padding-bottom: 5px;
      margin-bottom: 15px;
      color: #334155;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th {
      background-color: #334155;
      color: white;
      padding: 12px 10px;
      font-size: 13px;
      text-align: left;
    }

    td {
      border-bottom: 1px solid #e2e8f0;
      padding: 14px 10px;
      font-size: 12px;
      vertical-align: top;
    }

    .item-name {
      font-size: 13px;
      color: #0f172a;
      margin-bottom: 4px;
    }

    .item-desc {
      color: #64748b;
      font-size: 11px;
    }

    .price {
      text-align: right;
      font-size: 13px;
    }
  </style>
</head>

<body>

  <div class="header">
    <div class="title">御見積書</div>
  </div>

  <table class="meta-table">
    <tr>
      <td>発行日: {{ $estimate->created_at->format('Y年m月d日') }}</td>
      <td style="text-align: right;">見積番号: No.{{ $estimate->id }}</td>
    </tr>
  </table>

  <div class="total-box">
    御見積合計金額（税別）
    <span class="total-amount">¥{{ number_format($estimate->total_amount) }}-</span>
  </div>

  <div class="section-title">【御見積明細内訳】</div>
  <table>
    <thead>
      <tr>
        <th style="width: 20%;">カテゴリ</th>
        <th style="width: 60%;">項目名 / 内容</th>
        <th style="width: 20%; text-align: right;">金額</th>
      </tr>
    </thead>
    <tbody>
      @foreach($estimate->details as $detail)
      <tr>
        <td>
          <span style="background:#e2e8f0; padding:2px 6px; border-radius:4px; font-size:10px; color:#475569;">
            {{ $detail->category }}
          </span>
        </td>
        <td>
          <div class="item-name">{{ $detail->item_name }}</div>
          <div class="item-desc">{{ $detail->description }}</div>
        </td>
        <td class="price">¥{{ number_format($detail->price) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

</body>

</html>
<?php
// ヘルパー関数（金額のカンマ区切り用）
function number_shadow($val)
{
  return number_format($val);
}
?>