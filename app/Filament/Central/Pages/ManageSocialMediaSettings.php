<?php

namespace App\Filament\Central\Pages;

use App\Settings\SocialMediaSettings;
use BackedEnum;
use Filament\Forms;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ManageSocialMediaSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static string $settings = SocialMediaSettings::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Social Media';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Einstellungen';
    }

    public function getTitle(): string
    {
        return 'Social Media Einstellungen';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Social Media Links')
                    ->description('Verlinken Sie Ihre Social Media Profile')
                    ->schema([
                        Forms\Components\TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->prefixIcon('heroicon-o-globe-alt')
                            ->placeholder('https://facebook.com/ihr-profil')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->prefixIcon('heroicon-o-globe-alt')
                            ->placeholder('https://instagram.com/ihr-profil')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('twitter_url')
                            ->label('Twitter/X URL')
                            ->url()
                            ->prefixIcon('heroicon-o-globe-alt')
                            ->placeholder('https://twitter.com/ihr-profil')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->prefixIcon('heroicon-o-globe-alt')
                            ->placeholder('https://youtube.com/@ihr-kanal')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('linkedin_url')
                            ->label('LinkedIn URL')
                            ->url()
                            ->prefixIcon('heroicon-o-globe-alt')
                            ->placeholder('https://linkedin.com/company/ihr-unternehmen')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('tiktok_url')
                            ->label('TikTok URL')
                            ->url()
                            ->prefixIcon('heroicon-o-globe-alt')
                            ->placeholder('https://tiktok.com/@ihr-profil')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }
}
