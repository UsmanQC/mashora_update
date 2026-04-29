<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Fields align with {@see \App\Models\Invoice} / Mashorapwa-prod invoices migration.
 */
class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice')
                    ->components([
                        TextEntry::make('reference')->label('Reference')->placeholder('—'),
                        TextEntry::make('doctor.name')->label('Doctor'),
                        TextEntry::make('doctor.email')->label('Doctor email')->placeholder('—'),
                        TextEntry::make('doctor.phone')->label('Doctor phone')->placeholder('—'),
                        TextEntry::make('appointments_count')->label('Appointments #')->badge(),
                        TextEntry::make('issue_date')->date()->placeholder('—'),
                        TextEntry::make('from_date')->date()->placeholder('—'),
                        TextEntry::make('to_date')->date()->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make('Amounts')
                    ->components([
                        TextEntry::make('total_amount')->money('SAR'),
                        TextEntry::make('doctor_share')->money('SAR'),
                        TextEntry::make('mashora_share')->money('SAR'),
                        TextEntry::make('payment_status')->badge(),
                        TextEntry::make('paid_at')->dateTime()->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make('PDF')
                    ->components([
                        TextEntry::make('link_pdf')
                            ->label('PDF link')
                            ->url(fn (?string $state): ?string => filled($state) ? $state : null)
                            ->openUrlInNewTab()
                            ->placeholder('—'),
                    ])
                    ->collapsed(),
                Section::make('Timestamps')
                    ->components([
                        TextEntry::make('created_at')->dateTime(),
                        TextEntry::make('updated_at')->dateTime(),
                        TextEntry::make('deleted_at')->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
