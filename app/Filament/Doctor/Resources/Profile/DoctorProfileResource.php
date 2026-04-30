<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Resources\Profile;

use App\Filament\Doctor\Pages\DoctorDashboard;
use App\Filament\Doctor\Resources\Profile\Pages\EditDoctorProfile;
use App\Filament\Doctor\Resources\Profile\Schemas\DoctorProfileForm;
use App\Models\Doctor;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

use function Filament\Support\original_request;

final class DoctorProfileResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $slug = 'profile';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?int $navigationSort = 30;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('Account');
    }

    public static function getNavigationLabel(): string
    {
        return __('Profile');
    }

    public static function getModelLabel(): string
    {
        return __('Profile');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Profile');
    }

    public static function registerNavigationItems(): void
    {
        if (filled(self::getCluster())) {
            return;
        }

        if (self::getParentResourceRegistration()) {
            return;
        }

        if (! self::shouldRegisterNavigation() || ! self::canAccess()) {
            return;
        }

        Filament::getCurrentOrDefaultPanel()->navigationItems(self::getNavigationItems());
    }

    /**
     * @return array<NavigationItem>
     */
    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make(self::getNavigationLabel())
                ->group(self::getNavigationGroup())
                ->parentItem(self::getNavigationParentItem())
                ->icon(self::getNavigationIcon())
                ->activeIcon(self::getActiveNavigationIcon())
                ->isActiveWhen(fn (): bool => original_request()->routeIs(self::getRouteBaseName().'.*'))
                ->badge(self::getNavigationBadge(), color: self::getNavigationBadgeColor())
                ->badgeTooltip(self::getNavigationBadgeTooltip())
                ->sort(self::getNavigationSort())
                ->url(self::getUrl('edit', ['record' => Filament::auth()->id()])),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return DoctorProfileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'edit' => EditDoctorProfile::route('/{record}/edit'),
        ];
    }

    /**
     * Singleton profile resource: Filament calls {@see getIndexUrl()} when resolving the resource base URL.
     */
    public static function getIndexUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        $id = Filament::auth()->id();

        if ($id === null) {
            return DoctorDashboard::getUrl(panel: $panel ?? 'doctor');
        }

        return self::getUrl('edit', [
            ...$parameters,
            'record' => $id,
        ], $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereKey(Filament::auth()->id());
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->whereKey(Filament::auth()->id());
    }

    public static function canAccess(): bool
    {
        return Filament::auth()->check();
    }

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return (int) $record->getKey() === (int) Filament::auth()->id();
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
