<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Tables\Components\SelectColumn;
use Filament\Forms\Components\TextInput;
// use Filament\Forms\Components\InputMask;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload; //untuk tipe file

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('kode_barang')
                    ->default(fn () => Barang::getKodeBarang()) // Ambil default dari method getKodeBarang
                    ->label('Kode Barang')
                    ->required()
                    ->readonly()
                ,
                TextInput::make('nama_barang')
                    ->required()
                    ->placeholder('Masukkan nama barang') // Placeholder untuk membantu pengguna
                ,
                Select::make('satuan_barang')
                ->label('Satuan Barang')
                ->options([
                    'pcs' => 'Pcs',
                    'kg' => 'Kilogram',
                    'ltr' => 'Liter',
                ])
                ->required()
                ->placeholder('Pilih satuan barang') // Placeholder untuk membantu pengguna
                ,
                TextInput::make('harga_barang')
                    ->required()
                    ->minValue(0) // Nilai minimal 0 (opsional jika tidak ingin ada harga negatif)
                    ->reactive() // Menjadikan input reaktif terhadap perubahan
                    ->extraAttributes(['id' => 'harga-barang']) // Tambahkan ID untuk pengikatan JavaScript
                    ->placeholder('Masukkan harga barang') // Placeholder untuk membantu pengguna
                ,
                FileUpload::make('foto')
                    ->directory('foto')
                    ->preserveFilenames()
                    ->required()
                ,
                TextInput::make('stok')
                    ->required()
                    ->placeholder('Masukkan stok barang') // Placeholder untuk membantu pengguna
                    ->minValue(0)
                ,
                TextInput::make('deskripsi')
                ->required()
                ->placeholder('Masukan deskripsi barang') // Placeholder untuk membantu pengguna
                ->minValue(0)
            ,
                TextInput::make('rating')
                    ->required()
                    ->placeholder('Masukkan rating barang') // Placeholder untuk membantu pengguna
                    ->minValue(0)
                ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_barang')
                ->searchable(),
            // agar bisa di search
            TextColumn::make('nama_barang')
                ->searchable()
                ->sortable(),
            TextColumn::make('harga_barang')
                ->label('Harga Barang')
                ->formatStateUsing(fn (string|int|null $state): string => rupiah($state))
                ->extraAttributes(['class' => 'text-right']) // Tambahkan kelas CSS untuk rata kanan
                ->sortable(),
            TextColumn::make('satuan_barang')   
                ->label('Satuan Barang')
            ,
            ImageColumn::make('foto'),
            TextColumn::make('stok'),
            TextColumn::make('deskripsi'),
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
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }

    public static function getPluralLabel(): string
    {
        return 'Barang';
    }
}