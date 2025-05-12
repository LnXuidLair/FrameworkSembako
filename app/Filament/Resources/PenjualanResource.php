<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanResource\Pages;
use App\Models\Penjualan;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('no_transaksi')
                    ->label('No Transaksi')
                    ->default(fn () => Penjualan::getNoTransaksi())
                    ->disabled()
                    ->required(),
                Forms\Components\Select::make('id_barang')
                    ->label('Barang')
                    ->options(Barang::all()->pluck('nama_barang', 'id'))
                    ->searchable()
                    ->reactive() // penting untuk trigger perubahan
                    ->afterStateUpdated(function ($state, callable $set) {
                        $harga = Barang::find($state)?->harga ?? 0;
                        $set('harga_satuan', $harga);
                    })
                    ->required(),
                Forms\Components\DatePicker::make('tgl_transaksi')
                    ->label('Tanggal Transaksi')
                    ->default(now())
                    ->readonly()
                    ->required(),
                Forms\Components\TextInput::make('jml_barang')
                    ->label('Jumlah Barang')
                    ->numeric()
                    ->reactive()
                    ->afterStateUpdated(function (callable $get, callable $set) {
                        $qty = (int) $get('jml_barang');
                        $harga = (int) $get('harga_satuan');
                        $set('total_harga', $qty * $harga);
                    })
                    ->required(),
                Forms\Components\TextInput::make('harga_satuan')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->readOnly()
                    ->required(),
                Forms\Components\TextInput::make('total_harga')
                    ->label('Total Harga')
                    ->numeric()
                    ->readOnly()
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->default('Bayar')
                    ->readonly()
                    ->required(),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_transaksi')->label('No Transaksi')->searchable(),
                Tables\Columns\TextColumn::make('barang.nama')->label('Nama Barang')->searchable(),
                Tables\Columns\TextColumn::make('tgl_transaksi')->label('Tanggal')->dateTime(),
                Tables\Columns\TextColumn::make('jml_barang')->label('Jumlah')->sortable(),
                Tables\Columns\TextColumn::make('harga_satuan')->label('Harga Satuan')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('total_harga')->label('Total Harga')->money('IDR')->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label('Status')->colors([ 'success' => 'Bayar',]),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'edit' => Pages\EditPenjualan::route('/{record}/edit'),
        ];
    }
    public static function getPluralLabel(): string
    {
        return 'Penjualan';
    }   
}