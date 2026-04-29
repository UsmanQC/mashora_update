<?php

namespace App\Filament\Resources\Thoughts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Fields aligned with Mashorapwa-prod {@see \App\Http\Controllers\Admin\ThoughtsController}:
 * description_en, description_ar, auth_en, auth_ar (required in prod), status (boolean).
 */
class ThoughtForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Content'))
                    ->columns(2)
                    ->components([
                        Textarea::make('description_en')
                            ->label(__('Description (English)'))
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('description_ar')
                            ->label(__('Description (Arabic)'))
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('auth_en')
                            ->label(__('Author (English)'))
                            ->required()
                            ->rows(2),
                        Textarea::make('auth_ar')
                            ->label(__('Author (Arabic)'))
                            ->required()
                            ->rows(2),
                        Toggle::make('status')
                            ->label(__('Published'))
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
