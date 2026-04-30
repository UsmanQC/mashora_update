<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Resources\Appointments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class DoctorAppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Your notes')
                    ->components([
                        TextEntry::make('doctor_notes')->label('Doctor notes')->columnSpanFull(),
                    ]),
                Section::make('Patient details')
                    ->components([
                        TextEntry::make('patient_name')->label('Patient name'),
                        TextEntry::make('patient_phone')->label('Phone'),
                        TextEntry::make('patient_email')->label('Email'),
                        TextEntry::make('patient_notes')->label('Patient notes')->columnSpan(2),
                    ])
                    ->columns(2),
                Section::make('Appointment')
                    ->components([
                        TextEntry::make('appointment_number')->label('Appointment #'),
                        TextEntry::make('appointment_type')->badge(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('scheduled_at')->dateTime(),
                        TextEntry::make('appointment_date')->date(),
                        TextEntry::make('start_time'),
                        TextEntry::make('end_time'),
                        TextEntry::make('duration')->suffix(' min'),
                        TextEntry::make('actual_start_at')->dateTime(),
                        TextEntry::make('actual_end_at')->dateTime(),
                        TextEntry::make('extend_at')->dateTime(),
                        TextEntry::make('extend_duration'),
                        TextEntry::make('cancel_status'),
                        TextEntry::make('appointment_for')->badge(),
                        TextEntry::make('instant_counseling')->columnSpan(2),
                        TextEntry::make('prescription_not_needed')->badge(),
                    ])
                    ->columns(2),
                Section::make('Account')
                    ->components([
                        TextEntry::make('user.name')->label('User account'),
                        TextEntry::make('user.email')->label('User email'),
                    ])
                    ->columns(2),
                Section::make('Payment')
                    ->components([
                        TextEntry::make('amount')->money('SAR'),
                        TextEntry::make('discount')->money('SAR'),
                        TextEntry::make('tax')->money('SAR'),
                        TextEntry::make('total')->money('SAR'),
                        TextEntry::make('doctor_share')->money('SAR'),
                        TextEntry::make('mashora_share')->money('SAR'),
                        TextEntry::make('payment_session_id'),
                        TextEntry::make('payment_invoice_id'),
                        TextEntry::make('payment_invoice_url')
                            ->url(fn (?string $state): ?string => filled($state) ? $state : null)
                            ->openUrlInNewTab(),
                        TextEntry::make('refund_payment_invoice_id'),
                    ])
                    ->columns(2),
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
