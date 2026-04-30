<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Resources\Invoices\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

final class DoctorInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('reference')
                    ->label(__('Reference'))
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('appointments_count')
                    ->label(__('Appointments'))
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label(__('Amount'))
                    ->money('SAR')
                    ->sortable(),
                TextColumn::make('doctor_share')
                    ->money('SAR')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('mashora_share')
                    ->money('SAR')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payment_status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('issue_date')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', direction: 'desc')
            ->filters([
                SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => __('Unpaid'),
                        'paid' => __('Paid'),
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
