<div>
    {{-- BREADCRUMB --}}
    <div class="bg-base-200 py-4">
        <div class="max-w-5xl mx-auto px-4">
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="/" wire:navigate>Home</a></li>
                    <li>{{ $page->title }}</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- PAGE CONTENT --}}
    <article class="max-w-5xl mx-auto px-4 py-12">
        <header class="mb-8">
            <h1 class="text-5xl font-bold text-base-content mb-4">{{ $page->title }}</h1>

            <div class="flex items-center gap-4 text-base-content/60 text-sm">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Aktualisiert: {{ $page->updated_at->format('d.m.Y') }}
                </div>
            </div>
        </header>

        <div class="prose prose-lg max-w-none">
            {!! $page->content !!}
        </div>

        <div class="mt-12 pt-8 border-t">
            <a href="/" wire:navigate class="btn btn-outline">
                ← Zurück zur Startseite
            </a>
        </div>
    </article>
</div>
