<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Resources\Profile\Schemas;

use App\Filament\Doctor\Resources\Profile\Pages\EditDoctorProfile;
use App\Models\Doctor;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

final class DoctorProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(1)
                ->gap(true)
                ->schema([
                    Section::make(__('Personal account'))
                        ->description(__('Names, gender, and profile photo'))
                        ->secondary()
                        ->columns(2)
                        ->footerActionsAlignment(Alignment::End)
                        ->footerActions([
                            self::saveSectionAction('saveDoctorProfilePersonal'),
                        ])
                        ->schema([
                            TextInput::make('name_ar')
                                ->label(__('Arabic name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('name')
                                ->label(__('English name'))
                                ->required()
                                ->maxLength(255),
                            Select::make('gender')
                                ->label(__('Sex'))
                                ->options([
                                    'male' => __('Male'),
                                    'female' => __('Female'),
                                ])
                                ->required()
                                ->native(false),
                            FileUpload::make('profile_photo_path')
                                ->label(__('Profile photo'))
                                ->image()
                                ->disk('public')
                                ->directory('doctor-profiles')
                                ->visibility('public')
                                ->maxSize(10240)
                                ->columnSpanFull(),
                        ]),
                    Section::make(__('Phone number'))
                        ->secondary()
                        ->footerActionsAlignment(Alignment::End)
                        ->footerActions([
                            self::saveSectionAction('saveDoctorProfilePhone'),
                        ])
                        ->schema([
                            TextInput::make('phone')
                                ->label(__('Phone number'))
                                ->tel()
                                ->required()
                                ->maxLength(255)
                                ->unique(table: Doctor::class, column: 'phone', ignoreRecord: true),
                        ]),
                    Section::make(__('Change password'))
                        ->description(__('Leave blank to keep your current password.'))
                        ->secondary()
                        ->footerActionsAlignment(Alignment::End)
                        ->footerActions([
                            self::saveSectionAction('saveDoctorProfilePassword'),
                        ])
                        ->schema([
                            TextInput::make('current_password')
                                ->label(__('Current password'))
                                ->password()
                                ->revealable()
                                ->dehydrated(false),
                            TextInput::make('new_password')
                                ->label(__('New password'))
                                ->password()
                                ->revealable()
                                ->dehydrated(fn (?string $state): bool => filled($state)),
                            TextInput::make('new_password_confirmation')
                                ->label(__('Confirm new password'))
                                ->password()
                                ->revealable()
                                ->dehydrated(false),
                        ]),
                    Section::make(__('Bank account'))
                        ->secondary()
                        ->columns(2)
                        ->footerActionsAlignment(Alignment::End)
                        ->footerActions([
                            self::saveSectionAction('saveDoctorProfileBank'),
                        ])
                        ->schema([
                            TextInput::make('bank_account_holder_name')
                                ->label(__('Account holder name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('bank_account_number')
                                ->label(__('Account number'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('bank_iban_number')
                                ->label(__('IBAN'))
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ]),
                    Section::make(__('Language'))
                        ->description(__('Panel language for this browser session.'))
                        ->secondary()
                        ->schema([
                            SchemaView::make('filament.doctor.profile.language-tab'),
                        ]),
                    Section::make(__('Support'))
                        ->secondary()
                        ->schema([
                            SchemaView::make('filament.doctor.profile.support-tab'),
                        ]),
                    Section::make(__('Privacy policy'))
                        ->secondary()
                        ->schema([
                            SchemaView::make('filament.doctor.profile.privacy-tab'),
                        ]),
                ]),
        ]);
    }

    /**
     * Save only the fields inside this section’s subtree (via {@see EditDoctorProfile::saveFormComponentOnly()}).
     */
    private static function saveSectionAction(string $name): Action
    {
        return Action::make($name)
            ->label(__('Save'))
            ->action(function (Section $component): void {
                $livewire = $component->getLivewire();
                if ($livewire instanceof EditDoctorProfile) {
                    $livewire->saveFormComponentOnly($component);
                }
            });
    }
}
