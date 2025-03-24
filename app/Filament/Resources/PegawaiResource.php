<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PegawaiResource\Pages;
use App\Filament\Resources\PegawaiResource\RelationManagers;
use App\Models\Pegawai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;

class PegawaiResource extends Resource
{
    protected static ?string $model = Pegawai::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('kode_pegawai')
                    ->default(fn () => Pegawai::getKodePegawai()) // Ambil default dari method getKodeBarang
                    ->label('Kode Pegawai')
                    ->required()
//->readonly() // Membuat field menjadi read-only
                ,
                TextInput::make('nama')
                    ->required()
                    ->placeholder('Masukkan nama pegawai') // Placeholder untuk membantu pengguna
                ,
                TextInput::make('no_telp')
                    ->required()
                    ->placeholder('Masukkan nomor telephone') // Placeholder untuk membantu pengguna
                    ->minValue(0)
                ,
                Radio::make('jenis_kelamin')
                    ->required()
                    ->placeholder('Masukkan alamat lengkap') // Placeholder untuk membantu pengguna
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('kode_pegawai')
                ->searchable(),
            // agar bisa di search
            TextColumn::make('nama')
                ->searchable()
                ->sortable(),
            TextColumn::make('no_telp')
                ->searchable()
                ->sortable(),
            TextColumn::make('jenis_kelamin')
                ->searchable()
                ->sortable(),

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
            'index' => Pages\ListPegawais::route('/'),
            'create' => Pages\CreatePegawai::route('/create'),
            'edit' => Pages\EditPegawai::route('/{record}/edit'),
        ];
    }

    public static function getPluralLabel(): string
    {
        return 'Pegawai';
    }
}
