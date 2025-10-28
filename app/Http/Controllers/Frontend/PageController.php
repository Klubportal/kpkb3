<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Central\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)
            ->published()
            ->with('media')
            ->firstOrFail();

        // Determine view based on template
        $view = match($page->template) {
            'about' => 'frontend.pages.about',
            'contact' => 'frontend.pages.contact',
            'custom' => 'frontend.pages.custom',
            default => 'frontend.pages.default',
        };

        return view($view, compact('page'));
    }
}
