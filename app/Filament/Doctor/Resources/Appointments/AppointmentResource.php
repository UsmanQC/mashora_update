<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Resources\Appointments;

use App\Filament\Doctor\Resources\Appointments\Pages\ListAppointments;
use App\Filament\Doctor\Resources\Appointments\Pages\ViewAppointment;
use App\Filament\Doctor\Resources\Appointments\Schemas\DoctorAppointmentInfolist;
use App\Filament\Doctor\Resources\Appointments\Tables\DoctorAppointmentsTable;
use App\Models\Appointment;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

final class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $recordTitleAttribute = 'appointment_number';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationLabel = 'Appointments';

    protected static ?string $modelLabel = 'Appointment';

    protected static ?string $pluralModelLabel = 'Appointments';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Practice');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DoctorAppointmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoctorAppointmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppointments::route('/'),
            'view' => ViewAppointment::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('doctor_id', Filament::auth()->id())
            ->with([
                'user:id,name,email',
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('doctor_id', Filament::auth()->id())
            ->with([
                'user:id,name,email',
            ]);
    }

    public static function canViewAny(): bool
    {
        return Filament::auth()->check();
    }

    public static function canView(Model $record): bool
    {
        return (int) $record->doctor_id === (int) Filament::auth()->id();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function canRestore(Model $record): bool
    {
        return false;
    }

    public static function canRestoreAny(): bool
    {
        return false;
    }

    public static function canReplicate(Model $record): bool
    {
        return false;
    }
}
