<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EstimateItemResource\Pages;
use App\Models\EstimateItem;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EstimateItemResource extends Resource
{
    protected static ?string $model = EstimateItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '見積り項目';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\TextInput::make('name')->required()->label('項目名'),
                \Filament\Forms\Components\TextInput::make('price')->numeric()->prefix('¥')->required()->label('単価'),
                \Filament\Forms\Components\TextInput::make('category')->label('カテゴリ'),
                \Filament\Forms\Components\Textarea::make('description')->label('説明')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('項目名'),
                Tables\Columns\TextColumn::make('price')->label('単価')->money('JPY'),
                Tables\Columns\TextColumn::make('category')->label('カテゴリ'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // 💡 テーブルの右上（Newボタンの横）に設置するアクション
            ->headerActions([
                Action::make('importCsv')
                    ->label('CSVインポート')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->form([
                        FileUpload::make('csv_file')
                            ->label('CSVファイルを選択')
                            ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'text/plain'])
                            ->required()
                            ->disk('local')
                            ->directory('temp-imports'),
                    ])
                    ->action(function (array $data) {
                        $disk = Storage::disk('local');
                        $relativePath = $data['csv_file'];
                        $filePath = $disk->path($relativePath);

                        if (! $disk->exists($relativePath)) {
                            Notification::make()
                                ->title('インポートできませんでした')
                                ->body('アップロードされたファイルが見つかりません。')
                                ->danger()
                                ->send();

                            return;
                        }

                        $file = fopen($filePath, 'r');
                        fgetcsv($file); // 1行目（ヘッダー）をスキップ

                        // 💡 1. カウント用の変数をここで用意します
                        $count = 0;

                        Log::info('--- CSVインポート処理を開始します ---');

                        // 💡 2. `&$count` を渡すことで、ループ内の計算結果を外に持ち出せるようにします
                        DB::transaction(function () use ($file, &$count) {
                            while (($row = fgetcsv($file)) !== FALSE) {

                                $row = array_map(function ($value) {
                                    return mb_convert_encoding($value, 'UTF-8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-win');
                                }, $row);

                                if (empty($row[0])) {
                                    Log::info('➡️ 項目名が空のためスキップします。');
                                    continue;
                                }

                                // データベースへの保存・上書き
                                EstimateItem::updateOrCreate(
                                    [
                                        'name' => $row[0],
                                    ],
                                    [
                                        'category'    => $row[1] ?? '未分類',
                                        'price'       => isset($row[2]) ? (int)$row[2] : 0,
                                        'description' => $row[3] ?? '',
                                    ]
                                );

                                $count++; // 💡 3. 成功するたびに1ずつカウントアップ
                            }
                        });

                        fclose($file);
                        $disk->delete($relativePath);

                        Log::info("--- CSVインポート処理が終了しました。実際に登録・更新された件数: " . $count . " 件 ---");

                        // 💡 4. 通知文にも実際の件数を反映させます
                        Notification::make()
                            ->title('インポート完了')
                            ->body($count . ' 件の見積もり項目を処理しました（既存データは上書きされました）。')
                            ->success()
                            ->send();
                    })
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEstimateItems::route('/'),
            'create' => Pages\CreateEstimateItem::route('/create'),
            'edit' => Pages\EditEstimateItem::route('/{record}/edit'),
        ];
    }
}