<?php

namespace App\Observers;

use App\Models\Central\News;

class NewsObserver
{
    public function saving(News $news): void
    {
        // Debugging: Log alle eingehenden Daten
        \Log::info('NewsObserver saving', [
            'attributes' => $news->getAttributes(),
            'dirty' => $news->getDirty(),
        ]);
    }

    public function saved(News $news): void
    {
        \Log::info('NewsObserver saved', [
            'id' => $news->id,
            'title' => $news->title,
            'content' => substr(json_encode($news->content), 0, 100),
        ]);
    }
}
