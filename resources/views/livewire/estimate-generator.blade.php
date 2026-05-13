<div class="p-6 max-w-4xl mx-auto">
  <div class="bg-white shadow-xl rounded-lg p-8">
    <h1 class="text-2xl font-bold mb-4 text-gray-800">AIシステム見積もりくん</h1>

    <textarea
      wire:model="userRequest"
      class="w-full h-32 p-4 border rounded-lg focus:ring-2 focus:ring-blue-500 mb-4"
      placeholder="例：ECサイトを作りたい。ログイン機能と、クレジットカード決済、商品一覧画面が必要です。"></textarea>

    <button
      wire:click="generate"
      wire:loading.attr="disabled"
      class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
      <span wire:loading.remove>見積もりを生成する</span>
      <span wire:loading>計算中... 🤖</span>
    </button>

    @if($results)
    <div class="mt-8 border-t pt-6">
      <h2 class="text-xl font-bold mb-4">AIによる概算見積もり結果</h2>
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-3 border">項目名</th>
            <th class="p-3 border">価格</th>
            <th class="p-3 border">選定理由</th>
          </tr>
        </thead>
        <tbody>
          @foreach($results as $item)
          {{-- 配列であり、かつ name というキーを持っている場合のみ表示 --}}
          @if(is_array($item) && isset($item['name']))
          <tr>
            <td class="p-3 border">{{ $item['name'] }}</td>
            <td class="p-3 border">
              ¥{{ is_numeric($item['price'] ?? null) ? number_format($item['price']) : ($item['price'] ?? '0') }}
            </td>
            <td class="p-3 border text-sm text-gray-600">
              {{ $item['reason'] ?? ($item['description'] ?? '') }}
            </td>
          </tr>
          @else
          {{-- デバッグ用：もしデータが変な形なら、そのまま表示して確認できるようにする --}}
          <tr>
            <td colspan="3" class="p-2 text-xs text-red-400 bg-gray-50">
              読み込めない形式のデータが返されました: {{ is_array($item) ? json_encode($item) : $item }}
            </td>
          </tr>
          @endif
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>