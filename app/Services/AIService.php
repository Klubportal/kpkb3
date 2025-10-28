<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Anthropic\Anthropic;

class AIService
{
    /**
     * Generate text using OpenAI GPT
     */
    public function generateWithOpenAI(string $prompt, string $model = 'gpt-4o'): string
    {
        $result = OpenAI::chat()->create([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'Du bist ein hilfreicher Assistent für Fußballvereine.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 1000,
        ]);

        return $result->choices[0]->message->content;
    }

    /**
     * Generate text using Anthropic Claude
     */
    public function generateWithClaude(string $prompt, string $model = 'claude-3-5-sonnet-20241022'): string
    {
        $client = Anthropic::factory()
            ->withApiKey(config('services.anthropic.api_key'))
            ->withHttpHeader('anthropic-version', '2023-06-01')
            ->make();

        $result = $client->messages()->create([
            'model' => $model,
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $result->content[0]->text;
    }

    /**
     * Generate social media post for match results
     */
    public function generateMatchReport(array $matchData): string
    {
        $prompt = "Erstelle einen kurzen Social Media Post für folgendes Fußballspiel:\n\n";
        $prompt .= "Heimmannschaft: {$matchData['home_team']}\n";
        $prompt .= "Gastmannschaft: {$matchData['away_team']}\n";
        $prompt .= "Ergebnis: {$matchData['home_score']}:{$matchData['away_score']}\n";
        $prompt .= "Datum: {$matchData['date']}\n\n";
        $prompt .= "Der Post soll enthusiastisch und engagierend sein, max. 280 Zeichen für Twitter.";

        return $this->generateWithOpenAI($prompt);
    }

    /**
     * Generate player profile description
     */
    public function generatePlayerBio(array $playerData): string
    {
        $prompt = "Erstelle eine professionelle Spieler-Biografie:\n\n";
        $prompt .= "Name: {$playerData['name']}\n";
        $prompt .= "Position: {$playerData['position']}\n";
        $prompt .= "Alter: {$playerData['age']}\n";
        $prompt .= "Stärken: {$playerData['strengths']}\n\n";
        $prompt .= "Die Bio soll motivierend und professionell sein, ca. 100 Wörter.";

        return $this->generateWithClaude($prompt);
    }

    /**
     * Moderate user content (comments, posts)
     */
    public function moderateContent(string $content): array
    {
        $result = OpenAI::moderations()->create([
            'model' => 'text-moderation-latest',
            'input' => $content,
        ]);

        return [
            'flagged' => $result->results[0]->flagged,
            'categories' => $result->results[0]->categories->toArray(),
        ];
    }

    /**
     * Generate training plan suggestions
     */
    public function generateTrainingPlan(array $teamData): string
    {
        $prompt = "Erstelle einen Wochentrainingsplan für:\n\n";
        $prompt .= "Mannschaft: {$teamData['team_name']}\n";
        $prompt .= "Altersgruppe: {$teamData['age_group']}\n";
        $prompt .= "Spielniveau: {$teamData['level']}\n";
        $prompt .= "Nächstes Spiel: {$teamData['next_match']}\n\n";
        $prompt .= "Der Plan soll realistisch und altersgerecht sein.";

        return $this->generateWithOpenAI($prompt, 'gpt-4o');
    }
}
