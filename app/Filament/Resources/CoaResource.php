<?php

namespace App\Filament\Resources;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;

use Filament\Tables\Columns\TextColumn;

use App\Filament\Resources\CoaResource\Pages;
use App\Filament\Resources\CoaResource\RelationManagers;
use App\Models\Coa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CoaResource extends Resource
{
    protected static ?string $model = Coa::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)
                ->schema([
                    TextInput::make('header_akun')
                    ->required()
                    ->placeholder('Masukkan header akun'),
                    TextInput::make('kode_akun')
                    ->required()
                    ->placeholder('Masukkan kode akun'),
                    TextInput::make('nama_akun')
                    ->autocapitalize('words')
                    ->label('Nama akun')
                    ->required()
                    ->placeholder('Masukkan nama akun'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_akun')->label('Kode Akun'),
                TextColumn::make('nama_akun')->label('Nama Akun'),
                TextColumn::make('header_akun')->label('Header Akun'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('header_akun')
                ->options([
                    1=>'Aset/Aktiva',
                    2=>'Utang',
                    3=>'Modal',
                    4=>'Pendapatan',
                    5=>'Beban',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoas::route('/'),
            'create' => Pages\CreateCoa::route('/create'),
            'edit' => Pages\EditCoa::route('/{record}/edit'),
        ];
    }

    public static function getPluralLabel(): string
    {
        return 'Coa';
    }
}