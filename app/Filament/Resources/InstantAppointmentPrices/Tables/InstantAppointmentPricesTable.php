<?php

namespace App\Filament\Resources\InstantAppointmentPrices\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class InstantAppointmentPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('duration')
                    ->label(__('Minutes'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('durationOption.title')
                    ->label(__('Label'))
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('SAR')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('duration')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
                RestoreBulkAction::make(),
            ]);
    }
}
