<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EstimateItemResource\Pages;
use App\Filament\Resources\EstimateItemResource\RelationManagers;
use App\Models\EstimateItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EstimateItemResource extends Resource
{
  protected static ?string $model = EstimateItem::class;

  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
