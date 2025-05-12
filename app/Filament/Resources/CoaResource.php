<?php

namespace App\Filament\Resources;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

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
        return $form->schema([
            Grid::make(2)->schema([
                TextInput::make('header_akun')
                    ->placeholder('Masukkan header akun')
                    ->live()
                    ->reactive()
                    ->required()
                    ->numeric()
                    ->validationMessages(['numeric'=>'Input harus angka!','required'=>'Kolom wajib di isi!'])
                    ->afterStateUpdated(function ($state, callable $set) {
                        if(empty($state)) {
                            $set('jenis_akun', null);
                            $set('error_jenis_akun', 'Silahkan isi kolom dulu!');
                        }elseif (!is_numeric($state)) {
                            $set('error_jenis_akun', 'Header akun harus berupa angka!');
                        }else{
                            $jenisakun=match((int) $state) {
                                1=>'Aktiva',
                                2=>'Liabilitas',
                                3=>'Ekuitas',
                                4=>'Pendapatan',
                                5=>'Beban',
                                default=> null,
                            };
                            $set('jenis_akun', $jenisakun);
                            $set('error_jenis_akun', $jenisakun ? null : 'Header tidak valid!');
                        }
                    }),
                TextInput::make('kode_akun')
                    ->label('Kode Akun')
                    ->required()
                    ->placeholder('Masukkan kode akun'),
                TextInput::make('nama_akun')
                    ->autocapitalize('words')
                    ->label('Nama akun')
                    ->required()
                    ->placeholder('Masukkan nama akun'),
                TextInput::make('jenis_akun')
                    ->label('Jenis Akun')
                    ->readonly()
                    ->placeholder('Jenis akun akan mengikuti headernya')
                    ->live()
                    ->reactive()
                    ->required(fn($get)=>!empty($get('header_akun'))&&$get('error_jenis_akun')===null)
                    ->validationMessages(['required'=>'Silahkan isi header terlebih dahulu!',])
                    ->helperText(fn($get)=>$get('error_jenis_akun')),            
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
                BadgeColumn::make('jenis_akun')
                ->label('Jenis Akun')
                ->color(fn($state)=>match($state){
                    'Aktiva'=>'aset',
                    'Liabilitas'=>'kewajiban',
                    'Ekuitas'=>'equity',
                    'Pendapatan'=>'penghasilan',
                    'Beban'=>'biaya'
                }),
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