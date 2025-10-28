# KI Integration - Klubportal

## Installierte KI-SDKs

1. **OpenAI (GPT-4, GPT-3.5, DALL-E)**
   - Package: `openai-php/laravel`
   - Models: gpt-4o, gpt-4-turbo, gpt-3.5-turbo, dall-e-3

2. **Anthropic (Claude)**
   - Package: `anthropic-ai/sdk`
   - Models: claude-3-5-sonnet, claude-3-opus, claude-3-haiku

## Konfiguration

### .env Einträge hinzufügen:

```env
# OpenAI
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=org-...

# Anthropic Claude
ANTHROPIC_API_KEY=sk-ant-...
```

## Verwendung

### 1. Basis-Nutzung (OpenAI)

```php
use OpenAI\Laravel\Facades\OpenAI;

$result = OpenAI::chat()->create([
    'model' => 'gpt-4o',
    'messages' => [
        ['role' => 'user', 'content' => 'Schreibe einen Spielbericht'],
    ],
]);

echo $result->choices[0]->message->content;
```

### 2. Mit AIService (empfohlen)

```php
use App\Services\AIService;

$ai = new AIService();

// Match Report generieren
$report = $ai->generateMatchReport([
    'home_team' => 'FC Bayern',
    'away_team' => 'Dortmund',
    'home_score' => 3,
    'away_score' => 1,
    'date' => '2025-10-25',
]);

// Spieler-Bio erstellen
$bio = $ai->generatePlayerBio([
    'name' => 'Max Mustermann',
    'position' => 'Mittelfeld',
    'age' => 23,
    'strengths' => 'Schnelligkeit, Technik',
]);

// Content Moderation
$moderation = $ai->moderateContent($userComment);
if ($moderation['flagged']) {
    // Kommentar ablehnen
}

// Trainingsplan
$plan = $ai->generateTrainingPlan([
    'team_name' => 'U19',
    'age_group' => '17-19 Jahre',
    'level' => 'Regionalliga',
    'next_match' => '2025-10-30',
]);
```

### 3. In Filament Forms

```php
use App\Services\AIService;
use Filament\Forms\Components\Actions\Action;

TextInput::make('description')
    ->suffixAction(
        Action::make('generate')
            ->icon('heroicon-o-sparkles')
            ->action(function (Set $set, Get $get) {
                $ai = new AIService();
                $description = $ai->generatePlayerBio([
                    'name' => $get('name'),
                    'position' => $get('position'),
                    'age' => $get('age'),
                    'strengths' => $get('strengths'),
                ]);
                $set('description', $description);
            })
    )
```

## Use Cases für Fußballvereine

### ✅ Automatisierung

1. **Match Reports**: Automatische Spielberichte nach Spielen
2. **Social Media**: Posts für Instagram/Twitter/Facebook
3. **Newsletter**: Wöchentliche Updates generieren
4. **Spielerprofile**: Biografien und Beschreibungen
5. **Trainingspläne**: KI-gestützte Trainingsvorschläge

### ✅ Content Moderation

- Kommentare filtern
- Hassrede erkennen
- Spam-Erkennung

### ✅ Chatbot / Support

- Mitglieder-FAQ beantworten
- Vereinsinformationen bereitstellen
- Ticket-Support

### ✅ Analyse

- Spielstatistiken interpretieren
- Gegner-Analysen erstellen
- Leistungsberichte

## Kosten-Übersicht

### OpenAI Preise (Oktober 2025)
- GPT-4o: $2.50 / 1M input tokens, $10 / 1M output tokens
- GPT-4-turbo: $10 / 1M input tokens, $30 / 1M output tokens
- GPT-3.5-turbo: $0.50 / 1M input tokens, $1.50 / 1M output tokens

### Anthropic Preise
- Claude 3.5 Sonnet: $3 / 1M input tokens, $15 / 1M output tokens
- Claude 3 Haiku: $0.25 / 1M input tokens, $1.25 / 1M output tokens

**Tipp**: Für einfache Aufgaben GPT-3.5-turbo oder Claude Haiku verwenden = günstiger!

## Sicherheit

1. **API Keys niemals committen** - nur in .env
2. **Rate Limiting** aktivieren
3. **Content Validation** vor KI-Nutzung
4. **Kosten-Monitoring** einrichten
5. **User-Prompts sanitizen**

## Filament Widget Beispiel

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Services\AIService;

class AIAssistantWidget extends Widget
{
    protected static string $view = 'filament.widgets.ai-assistant';
    
    public string $prompt = '';
    public string $result = '';

    public function generate()
    {
        $ai = new AIService();
        $this->result = $ai->generateWithOpenAI($this->prompt);
    }
}
```

## Laravel Command Beispiel

```bash
php artisan make:command GenerateMatchReports
```

```php
public function handle()
{
    $ai = new AIService();
    
    $matches = Match::whereDate('date', today())
        ->where('status', 'finished')
        ->get();
    
    foreach ($matches as $match) {
        $report = $ai->generateMatchReport([
            'home_team' => $match->homeTeam->name,
            'away_team' => $match->awayTeam->name,
            'home_score' => $match->home_score,
            'away_score' => $match->away_score,
            'date' => $match->date,
        ]);
        
        $match->update(['ai_report' => $report]);
    }
}
```

## Weitere Möglichkeiten

- **Bilder generieren**: DALL-E für Grafiken
- **Sprachausgabe**: TTS für Stadion-Ansagen
- **Übersetzungen**: Automatische Mehrsprachigkeit
- **Sentiment-Analyse**: Fan-Stimmung analysieren
