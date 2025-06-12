<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\TextInput; //kita menggunakan textinput
use Filament\Forms\Components\Grid;

use Filament\Tables\Columns\TextColumn;

use App\Filament\Resources\VendorResource\Pages;
use App\Filament\Resources\VendorResource\RelationManagers;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1) // Membuat hanya 1 kolom
                ->schema([
                    TextInput::make('id_vendor')
                    ->default(fn () => Vendor::generateIdVendor()) // Ambil default dari method getKodeBarang
                    ->label('Id Vendor')
                    ->required()
                    ->readonly() // Membuat field menjadi read-only
                    ,
                    TextInput::make('nama_vendor')
                        ->label('Nama Vendor')
                        ->required()
                        ->placeholder('Masukkan nama vendor')
                    ,
                    TextInput::make('alamat')
                        ->label('Alamat')
                        ->required()
                        ->placeholder('Masukkan alamat vendor')
                    ,
                    TextInput::make('barang_vendor')
                        ->label('Barang Vendor')
                        ->required()
                        ->placeholder('Masukkan nama barang')
                    ,
                    TextInput::make('harga_barang')
                        ->label('Harga Barang')
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, callable $set) =>
                            $set('harga_barang', number_format((int) str_replace(['.', ','], '', $state), 0, ',', '.'))
                        )
                        ->dehydrateStateUsing(fn ($state) =>
                            (int) str_replace(['.', ','], '', $state)
                        )
                        ->numeric()
                        ->placeholder('Masukkan harga barang')
                        ,

                    TextInput::make('rating')
                        ->required()
                        ->placeholder('')
                    ,
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_vendor'),
                TextColumn::make('nama_vendor'),
                TextColumn::make('alamat'), 
                TextColumn::make('barang_vendor'), 
                TextColumn::make('harga_barang')
                    ->label('Harga Barang')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.')),
                TextColumn::make('rating'),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
    public static function getPluralLabel(): string
    {
        return 'Vendor';
    }
}