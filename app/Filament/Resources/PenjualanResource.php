<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualanResource\Pages;
use App\Models\Penjualan;
use App\Models\Barang;
use App\Models\PenjualanDetail;
use App\Models\Pembayaran; // Although not used in the provided code, keep if planning to use

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope; // Keep if using soft deletes

use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter; // Import the base Filter class for custom filters
use Filament\Forms\Components\Select as FormSelect; // Alias Select for form component
use Filament\Forms\Components\Grid; // For better layout in forms
use Filament\Forms\Components\Section; // Alias Forms\Components\Section

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // Corrected casing for Storage
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;    
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // Step 1: Data Transaksi (No changes needed here for this error)
                    Wizard\Step::make('Data')
                        ->icon('heroicon-m-calendar-days')
                        ->description('Data Transaksi')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->schema([
                            Forms\Components\Section::make('Informasi Transaksi')
                                ->icon('heroicon-m-document-duplicate')
                                ->schema([
                                    TextInput::make('no_transaksi')
                                        ->label('No Transaksi')
                                        ->default(fn () => \App\Models\Penjualan::getNoTransaksi()) // Use full namespace for static method
                                        ->readonly()
                                        ->required()
                                        ->columnSpan(1),
                                    DatePicker::make('tgl_transaksi')
                                        ->label('Tanggal Transaksi')
                                        ->default(now())
                                        ->required()
                                        ->columnSpan(1),
                                    Hidden::make('total_harga')
                                        ->default(0)
                                        ->dehydrated(),
                                    Hidden::make('status')
                                        ->default('Bayar'),
                                ])
                                ->columns(2),
                        ]),
                    // Step 2: Pemilihan Barang
                    Wizard\Step::make('Order')
                        ->icon('heroicon-m-shopping-bag')
                        ->description('Pemilihan Barang')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->schema([
                            Repeater::make('penjualanDetail')
                                ->label('Detail Barang')
                                ->relationship('penjualanDetail')
                                ->schema([
                                    Select::make('id_barang')
                                        ->label('Nama Barang')
                                        ->options(\App\Models\Barang::pluck('nama_barang', 'id')->toArray()) // Use full namespace
                                        ->required()
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->reactive()
                                        ->placeholder('Pilih Barang')
                                        ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                                            if ($state) {
                                                $barang = \App\Models\Barang::find($state); // Use full namespace
                                                $set('harga_barang', $barang ? $barang->harga_barang : 0);
                                                $set('total', ($barang ? $barang->harga_barang : 0) * (int)($get('jml_barang') ?? 1));
                                            } else {
                                                $set('harga_barang', 0);
                                                $set('total', 0);
                                            }
                                            static::updateTotalHarga($get, $set);
                                        })
                                        ->searchable(),
                                    TextInput::make('harga_barang')
                                        ->label('Harga Barang')
                                        ->numeric()
                                        ->default(fn(Get $get) => $get('id_barang') ? \App\Models\Barang::find($get('id_barang'))?->harga_barang ?? 0 : 0) // Use full namespace
                                        ->readOnly()
                                        ->dehydrated(),
                                    TextInput::make('jml_barang')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->reactive()
                                        ->default(1)
                                        ->required()
                                        ->minValue(1)
                                        ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                                            $harga = (int) $get('harga_barang');
                                            $jumlah = (int) $state;
                                            $set('total', $harga * $jumlah);
                                            static::updateTotalHarga($get, $set);
                                        }),
                                    TextInput::make('total')
                                        ->label('Total Harga Per Item')
                                        ->numeric()
                                        ->readOnly()
                                        ->dehydrated()
                                        ->default(0),
                                ])
                                ->columns([
                                    'md' => 4,
                                ])
                                ->addable()
                                ->deletable()
                                ->reorderable()
                                ->createItemButtonLabel('Tambah Barang')
                                ->minItems(1)
                                ->defaultItems(1)
                                ->required()
                                ->live(), // Keep this for reactivity within the repeater

                                // REMOVED: ->afterStateDehydrated(function (Get $get, Set $set) { ... })
                                // This is the line causing the error.
                        ]),
                    // Step 3: Status Transaksi and Summary (No changes needed here for this error)
                    Wizard\Step::make('Billing')
                        ->icon('heroicon-m-credit-card')
                        ->description('Status Transaksi & Ringkasan')
                        ->completedIcon('heroicon-m-hand-thumb-up')
                        ->schema([
                            Forms\Components\Grid::make(1) // Use Grid for vertical stacking if needed
                                ->schema([
                                    Placeholder::make('total_barang_dibeli')
                                        ->label('Total Barang Dibeli')
                                        ->content(function (Get $get): string {
                                            $detailItems = $get('penjualanDetail') ?? [];
                                            $total = collect($detailItems)->sum('jml_barang');
                                            return $total . ' Item';
                                        }),
                                    Placeholder::make('total_transaksi_rupiah')
                                        ->label('Total Pembayaran')
                                        ->content(function (Get $get): string {
                                            $detailItems = $get('penjualanDetail') ?? [];
                                            $total = collect($detailItems)->sum('total');
                                            return rupiah($total);
                                        }),
                                    Forms\Components\Select::make('status')
                                        ->label('Status Transaksi')
                                        ->options([
                                            'Pending' => 'Pending',
                                            'Bayar' => 'Bayar',
                                            'Dibatalkan' => 'Dibatalkan',
                                        ])
                                        ->required()
                                        ->default('Bayar')
                                        ->native(false),
                                ])->columns(1),
                        ]),
                ])
                ->columnSpanFull(),
            ]);
    }

    // Helper function to update total_harga
    public static function updateTotalHarga(Get $get, Set $set): void
    {
        $totalTransaksi = collect($get('penjualanDetail'))
            ->sum(fn($item) => (int)($item['total'] ?? 0)); // Sum the 'total' from each item
        $set('total_harga', $totalTransaksi);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_transaksi')
                    ->label('No Transaksi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('penjualanDetail.barang.nama_barang') // Direct access to barang name
                    ->label('Nama Barang')
                    ->listWithLineBreaks() // Display each item on a new line
                    ->limitList(2) // Limit displayed items
                    ->expandableLimitedList() // Allow expanding
                    ->searchable(),
                TextColumn::make('penjualanDetail.harga_barang')
                    ->label('Harga Satuan') // More accurate label
                    ->formatStateUsing(function ($state) {
                        return rupiah($state); // Format each item's price
                    })
                    ->listWithLineBreaks(),
                TextColumn::make('penjualanDetail.jml_barang')
                    ->label('Jumlah Barang')
                    ->listWithLineBreaks(),
                TextColumn::make('penjualanDetail.total')
                    ->label('Total per Item') // More accurate label
                    ->formatStateUsing(function ($state) {
                        return rupiah($state);
                    })
                    ->listWithLineBreaks(),
                TextColumn::make('tgl_transaksi')
                    ->label('Tanggal Transaksi')
                    ->date('l, d F Y')
                    ->sortable(),
                TextColumn::make('total_harga')
                    ->label('Total Transaksi')
                    ->formatStateUsing(fn (string|int|null $state): string => rupiah($state))
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make() // Change this line
                            ->label('Grand Total')
                            ->formatStateUsing(fn ($state) => rupiah($state)),
                    ]),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Bayar' => 'success',
                        'Pending' => 'warning', // Corrected typo (was 'Bayar' twice)
                        'Dibatalkan' => 'danger', // Added 'Dibatalkan' status
                        default => 'secondary', // Fallback color
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Bayar' => 'Bayar',
                        'Dibatalkan' => 'Dibatalkan',
                    ])
                    ->native(false) // For a more modern select appearance
                    ->searchable()
                    ->preload(),
                
                // --- Date Range Filter (Optional but highly recommended for transactions) ---
                Filter::make('tgl_transaksi')
                    ->form([
                        DatePicker::make('created_from')
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        DatePicker::make('created_until')
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tgl_transaksi', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tgl_transaksi', '<=', $date),
                            );
                    })
                    ->label('Filter Tanggal Transaksi'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Add Edit action
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Action::make('downloadPdf')
                    ->label('Cetak Penjualan')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function () {
                        // Ensure you're only getting sales for the current active filters if desired
                        // For a simple all sales PDF, the current implementation is fine.
                        // If you want filtered sales, you'd need to pass the filter state to the action.
                        $penjualan = Penjualan::all(); // Or apply filters here: Penjualan::query()->applyFilters()->get();

                        $pdf = Pdf::loadView('pdf.penjualan', ['penjualan' => $penjualan]);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'Data Penjualan Toko Sembako.pdf'
                        );
                    }),
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