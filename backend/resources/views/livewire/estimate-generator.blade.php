<div class="p-6">
    {{-- 入力フォーム部分 --}}
    <div class="mb-6">
        <textarea wire:model="userRequest" class="w-full p-3 border rounded-lg" rows="4" placeholder="例：お問い合わせフォームと会員登録機能があるサイトを作りたい"></textarea>
        <button wire:click="generate" class="mt-2 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" wire:loading.attr="disabled">
            <span wire:loading.remove>見積もりを生成する</span>
            <span wire:loading>生成中...</span>
        </button>
    </div>

    {{-- 結果表示部分 --}}
    @if($results)
        <div class="mt-8">
            <h2 class="text-xl font-bold mb-4">AIによる概算見積もり結果</h2>
            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">項目名</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">概算金額</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">選定理由</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($results as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $item['name'] ?? '不明な項目' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ isset($item['price']) ? number_format($item['price']) : '0' }}円
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $item['reason'] ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 p-4 bg-blue-50 text-blue-800 rounded-lg">
                <p class="font-bold">合計金額：{{ number_format(collect($results)->sum('price')) }}円（税別）</p>
            </div>
        </div>
    @endif

    {{-- エラー表示 --}}
    @if (session()->has('error'))
        <div class="mt-4 p-4 bg-red-100 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif
</div>