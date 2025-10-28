<?php

namespace App\Filament\Central\Pages;

use App\Settings\ThemeSettings;
use App\Services\ThemeService;
use BackedEnum;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;

class ManageThemeSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static string $settings = ThemeSettings::class;

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('Theme & Design');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Einstellungen');
    }

    public function getTitle(): string
    {
        return __('Theme & Design Einstellungen');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vorgefertigte Themes')
                    ->description('Wählen Sie ein vorgefertigtes Theme - Farben werden automatisch übernommen')
                    ->schema([
                        Forms\Components\Select::make('active_theme')
                            ->label('Theme auswählen')
                            ->options(ThemeService::getThemesForSelect())
                            ->default('default')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if (!$state) return;

                                $theme = ThemeService::getTheme($state);
                                if ($theme) {
                                    $set('header_bg_color', $theme['header_bg']);
                                    $set('footer_bg_color', $theme['footer_bg']);
                                    $set('text_color', $theme['text_color']);
                                    $set('link_color', $theme['link_color']);
                                    $set('dark_mode_enabled', $state === 'dark_mode');

                                    Notification::make()
                                        ->success()
                                        ->title('Theme angewendet')
                                        ->body("Das Theme '{$theme['name']}' wurde angewendet!")
                                        ->send();
                                }
                            })
                            ->helperText('Nach dem Auswählen werden die Farben automatisch gesetzt. Speichern nicht vergessen!')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make(__('Farben'))
                    ->schema([
                        Forms\Components\ColorPicker::make('header_bg_color')
                            ->label(__('Header Hintergrund')),

                        Forms\Components\ColorPicker::make('footer_bg_color')
                            ->label(__('Footer Hintergrund')),

                        Forms\Components\ColorPicker::make('text_color')
                            ->label(__('Textfarbe')),

                        Forms\Components\ColorPicker::make('link_color')
                            ->label(__('Link Farbe')),
                    ])
                    ->columns(4),

                Section::make(__('Layout'))
                    ->schema([
                        Forms\Components\Select::make('font_family')
                            ->label(__('Schriftart'))
                            ->options([
                                'inter' => 'Inter (Standard)',
                                'roboto' => 'Roboto',
                                'poppins' => 'Poppins',
                                'open-sans' => 'Open Sans',
                                'lato' => 'Lato',
                                'montserrat' => 'Montserrat',
                            ])
                            ->default('inter')
                            ->columnSpan(1),

                        Forms\Components\Select::make('border_radius')
                            ->label(__('Ecken-Rundung'))
                            ->options([
                                'none' => 'Keine (0px)',
                                'sm' => 'Klein (4px)',
                                'md' => 'Mittel (8px)',
                                'lg' => 'Groß (12px)',
                                'xl' => 'Extra Groß (16px)',
                            ])
                            ->default('md')
                            ->columnSpan(1),

                        Forms\Components\Select::make('sidebar_width')
                            ->label(__('Sidebar-Breite'))
                            ->options([
                                'narrow' => 'Schmal (200px)',
                                'normal' => 'Normal (250px)',
                                'wide' => 'Breit (300px)',
                            ])
                            ->default('normal')
                            ->columnSpan(1),

                        Forms\Components\Select::make('button_style')
                            ->label(__('Button Stil'))
                            ->options([
                                'rounded' => __('Abgerundet'),
                                'square' => __('Eckig'),
                                'pill' => __('Pill (vollständig rund)'),
                            ])
                            ->default('rounded')
                            ->columnSpan(1),

                        Forms\Components\Select::make('layout_style')
                            ->label(__('Layout Stil'))
                            ->options([
                                'full-width' => __('Volle Breite'),
                                'boxed' => __('Boxed (begrenzt)'),
                            ])
                            ->default('full-width')
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('dark_mode_enabled')
                            ->label(__('Dark Mode aktiviert'))
                            ->inline(false)
                            ->columnSpan(1),
                    ])
                    ->columns(3),
            ]);
    }
}
