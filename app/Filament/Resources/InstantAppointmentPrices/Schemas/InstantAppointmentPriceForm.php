<?php

namespace App\Filament\Resources\InstantAppointmentPrices\Schemas;

use App\Models\InstantAppointmentPrice;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Fields aligned with Mashorapwa-prod admin {@see \App\Http\Controllers\Admin\InstantAppointmentPricesController}:
 * duration (minutes, FK to durations — allowed values 15, 30, 45, 60), price (≥ 0).
 */
class InstantAppointmentPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Pricing'))
                    ->description(__('Duration slots come from the durations table; each slot may only appear once.'))
                    ->components([
                        Select::make('duration')
                            ->label(__('Duration (minutes)'))
                            ->relationship(
                                name: 'durationOption',
                                titleAttribute: 'title',
                                modifyQueryUsing: fn ($query) => $query->orderBy('duration'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (\App\Models\Duration $record): string => $record->title
                                ? "{$record->duration} — {$record->title}"
                                : (string) $record->duration)
                            ->required()
                            ->unique(
                                table: InstantAppointmentPrice::class,
                                column: 'duration',
                                ignoreRecord: true,
                            )
                            ->native(false),
                        TextInput::make('price')
                            ->label(__('Price'))
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix(__('SAR')),
                    ])
                    ->columns(2),
            ]);
    }
}
