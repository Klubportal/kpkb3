<?php

namespace App\Filament\Club\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use App\Filament\Club\Widgets\LatestNewsWidget;
use App\Filament\Club\Widgets\TopPlayersWidget;
use App\Filament\Club\Widgets\NextMatchWidget;
use App\Filament\Club\Widgets\StandingsWidget;
use App\Filament\Club\Widgets\TopScorersWidget;
use App\Filament\Club\Widgets\MatchdayWidget;
use App\Filament\Club\Widgets\TeamStatisticsWidget;
use App\Filament\Club\Widgets\GalleryWidget;
use App\Filament\Club\Widgets\SponsorsWidget;
use App\Filament\Club\Widgets\SocialMediaWidget;
use App\Filament\Club\Widgets\NestIntegrationWidget;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;

class Senioren extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $title = 'Senioren';
    protected static ?string $navigationLabel = 'Senioren';
    protected static ?string $slug = 'senioren';
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.club.pages.senioren';

    // Filter-Properties
    public ?string $selectedCompetition = null;
    public ?string $selectedSeason = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    protected function getHeaderWidgets(): array
    {
        return [
            TeamStatisticsWidget::class,
            LatestNewsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            NestIntegrationWidget::class,
            NextMatchWidget::class,
            TopPlayersWidget::class,
            TopScorersWidget::class,
            MatchdayWidget::class,
            StandingsWidget::class,
            GalleryWidget::class,
            SponsorsWidget::class,
            SocialMediaWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
