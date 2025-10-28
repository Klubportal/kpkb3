@extends('templates.fcbm.layout')

@section('title', $settings->website_name ?? 'Willkommen')

@section('hero')
<!-- Hero Slider -->
<div class="hero-swiper swiper">
    <div class="swiper-wrapper">
        <!-- Slide 1 -->
        <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?w=1920&h=600&fit=crop');">
            <div class="slide-content">
                <div class="container mx-auto px-4">
                    <span class="inline-block bg-badge text-badge px-4 py-1 rounded-full text-sm font-semibold mb-3">BREAKING NEWS</span>
                    <h2 class="text-4xl md:text-6xl font-bold mb-4">Sensationeller 3:1 Sieg im Derby!</h2>
                    <p class="text-xl mb-6 text-gray-200">Unser Team zeigt eine überragende Leistung und sichert sich drei wichtige Punkte.</p>
                    <a href="#" class="inline-block bg-primary text-white px-8 py-3 rounded-full font-semibold hover:opacity-90 transition">Zum Spielbericht</a>
                </div>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1522778119026-d647f0596c20?w=1920&h=600&fit=crop');">
            <div class="slide-content">
                <div class="container mx-auto px-4">
                    <span class="inline-block bg-secondary text-white px-4 py-1 rounded-full text-sm font-semibold mb-3">TRANSFERS</span>
                    <h2 class="text-4xl md:text-6xl font-bold mb-4">Neuzugang verstärkt die Offensive</h2>
                    <p class="text-xl mb-6 text-gray-200">Herzlich Willkommen in unserem Team! Wir freuen uns auf die Zusammenarbeit.</p>
                    <a href="#" class="inline-block bg-primary text-white px-8 py-3 rounded-full font-semibold hover:opacity-90 transition">Mehr erfahren</a>
                </div>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="swiper-slide" style="background-image: url('https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=1920&h=600&fit=crop');">
            <div class="slide-content">
                <div class="container mx-auto px-4">
                    <span class="inline-block bg-accent text-gray-900 px-4 py-1 rounded-full text-sm font-semibold mb-3">HEIMSPIEL</span>
                    <h2 class="text-4xl md:text-6xl font-bold mb-4">Nächstes Spiel: Samstag 15:00 Uhr</h2>
                    <p class="text-xl mb-6 text-gray-200">Sichern Sie sich jetzt Ihre Tickets für das kommende Heimspiel!</p>
                    <a href="#" class="inline-block bg-primary text-white px-8 py-3 rounded-full font-semibold hover:opacity-90 transition">Tickets kaufen</a>
                </div>
            </div>
        </div>
    </div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-pagination"></div>
</div>

@push('scripts')
<script>
    var heroSwiper = new Swiper(".hero-swiper", {
        cssMode: false,
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        keyboard: true,
    });
</script>
@endpush
@endsection

@section('content')
<!-- News Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900">Aktuelle News</h2>
            <a href="#" class="text-primary hover:underline font-semibold">Alle News →</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- News Card 1 -->
            <article class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition group">
                <div class="relative h-48 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=600&h=400&fit=crop" alt="News" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute top-4 left-4">
                        <span class="bg-primary text-white px-3 py-1 rounded-full text-xs font-semibold">MATCH</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="text-sm text-gray-500 mb-2">{{ now()->format('d.m.Y') }}</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900 group-hover:text-primary transition">Spielbericht: Souveräner Heimsieg</h3>
                    <p class="text-gray-600 mb-4">Mit einer starken Mannschaftsleistung sichern wir uns drei wichtige Punkte im Kampf um die Tabellenspitze.</p>
                    <a href="#" class="text-primary font-semibold hover:underline">Weiterlesen →</a>
                </div>
            </article>

            <!-- News Card 2 -->
            <article class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition group">
                <div class="relative h-48 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1587329310686-91414b8e3cb7?w=600&h=400&fit=crop" alt="News" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute top-4 left-4">
                        <span class="bg-secondary text-white px-3 py-1 rounded-full text-xs font-semibold">TRAINING</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="text-sm text-gray-500 mb-2">{{ now()->subDays(1)->format('d.m.Y') }}</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900 group-hover:text-primary transition">Vorbereitung läuft auf Hochtouren</h3>
                    <p class="text-gray-600 mb-4">Das Team bereitet sich intensiv auf das kommende Auswärtsspiel vor. Die Stimmung ist hervorragend.</p>
                    <a href="#" class="text-primary font-semibold hover:underline">Weiterlesen →</a>
                </div>
            </article>

            <!-- News Card 3 -->
            <article class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition group">
                <div class="relative h-48 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1517466787929-bc90951d0974?w=600&h=400&fit=crop" alt="News" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute top-4 left-4">
                        <span class="bg-accent text-gray-900 px-3 py-1 rounded-full text-xs font-semibold">JUGEND</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="text-sm text-gray-500 mb-2">{{ now()->subDays(2)->format('d.m.Y') }}</div>
                    <h3 class="text-xl font-bold mb-3 text-gray-900 group-hover:text-primary transition">U19 erreicht Pokalfinale</h3>
                    <p class="text-gray-600 mb-4">Großer Erfolg für unsere Jugendarbeit: Die U19 steht im Finale des regionalen Pokals!</p>
                    <a href="#" class="text-primary font-semibold hover:underline">Weiterlesen →</a>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- Next Match Section (Seniors) -->
<section class="py-16 gradient-primary text-white">
    <div class="container mx-auto px-4">
        @if($nextMatch)
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-2">Nächstes Spiel</h2>
                <p class="text-xl opacity-90">Spieltag {{ $nextMatch->match_day ?? '–' }} &middot; {{ \Illuminate\Support\Carbon::parse($nextMatch->date_time_local)->translatedFormat('d.m.Y, H:i') }}</p>
            </div>

            <div class="max-w-4xl mx-auto bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                    <!-- Home Team -->
                    <div class="text-center">
                        @php($homeLogo = $nextMatch->team_logo_home)
                        @if($homeLogo)
                            <img src="{{ $homeLogo }}" alt="{{ $nextMatch->team_name_home }}" class="h-24 w-24 mx-auto mb-4 object-contain">
                        @elseif($settings->logo && $nextMatch->team_name_home === ($settings->website_name ?? ''))
                            <img src="{{ asset('storage/' . $settings->logo) }}" alt="{{ $settings->website_name }}" class="h-24 w-24 mx-auto mb-4 object-contain">
                        @else
                            <div class="h-24 w-24 mx-auto mb-4 bg-white rounded-full flex items-center justify-center">
                                <span class="text-primary font-bold text-3xl">{{ strtoupper(substr($nextMatch->team_name_home, 0, 2)) }}</span>
                            </div>
                        @endif
                        <h3 class="text-2xl font-bold">{{ $nextMatch->team_name_home }}</h3>
                    </div>

                    <!-- Match Info -->
                    <div class="text-center">
                        @if(!is_null($nextMatch->team_score_home) && !is_null($nextMatch->team_score_away))
                            <div class="text-6xl font-bold mb-4">{{ $nextMatch->team_score_home }} : {{ $nextMatch->team_score_away }}</div>
                        @else
                            <div class="text-6xl font-bold mb-4">VS</div>
                        @endif
                        <div class="text-xl mb-2">{{ \Illuminate\Support\Carbon::parse($nextMatch->date_time_local)->translatedFormat('l, d.m.Y') }}</div>
                        <div class="text-2xl font-bold">{{ \Illuminate\Support\Carbon::parse($nextMatch->date_time_local)->format('H:i') }} Uhr</div>
                    </div>

                    <!-- Away Team -->
                    <div class="text-center">
                        @php($awayLogo = $nextMatch->team_logo_away)
                        @if($awayLogo)
                            <img src="{{ $awayLogo }}" alt="{{ $nextMatch->team_name_away }}" class="h-24 w-24 mx-auto mb-4 object-contain">
                        @elseif($settings->logo && $nextMatch->team_name_away === ($settings->website_name ?? ''))
                            <img src="{{ asset('storage/' . $settings->logo) }}" alt="{{ $settings->website_name }}" class="h-24 w-24 mx-auto mb-4 object-contain">
                        @else
                            <div class="h-24 w-24 mx-auto mb-4 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-3xl">{{ strtoupper(substr($nextMatch->team_name_away, 0, 2)) }}</span>
                            </div>
                        @endif
                        <h3 class="text-2xl font-bold">{{ $nextMatch->team_name_away }}</h3>
                    </div>
                </div>
            </div>
        @elseif($lastMatch)
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-2">Letztes Spiel</h2>
                <p class="text-xl opacity-90">Spieltag {{ $lastMatch->match_day ?? '–' }} &middot; {{ \Illuminate\Support\Carbon::parse($lastMatch->date_time_local)->translatedFormat('d.m.Y, H:i') }}</p>
            </div>
            <div class="max-w-4xl mx-auto bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold">{{ $lastMatch->team_name_home }}</h3>
                    </div>
                    <div class="text-center">
                        <div class="text-6xl font-bold mb-4">{{ $lastMatch->team_score_home }} : {{ $lastMatch->team_score_away }}</div>
                        <div class="text-xl">{{ \Illuminate\Support\Carbon::parse($lastMatch->date_time_local)->translatedFormat('l, d.m.Y') }}</div>
                    </div>
                    <div class="text-center">
                        <h3 class="text-2xl font-bold">{{ $lastMatch->team_name_away }}</h3>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-4">Spiele</h2>
                <p class="text-xl opacity-90">Keine Spiele gefunden.</p>
            </div>
        @endif
    </div>
</section>

@if(isset($matchdayMatches) && $matchdayMatches->count())
<!-- Matchday Schedule (Seniors) -->
<section class="py-12 bg-white border-t border-gray-200">
    <div class="container mx-auto px-4">
        <div class="flex items-end justify-between mb-6">
            <h2 class="text-3xl font-bold text-gray-900">
                {{ $matchdayLabel ? $matchdayLabel . ':' : 'Spieltag' }}
                <span class="text-primary">{{ $matchday }}</span>
            </h2>
        </div>

        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <tbody>
                    @foreach($matchdayMatches as $m)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 w-28 text-sm text-gray-500 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($m->date_time_local)->format('d.m. H:i') }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex-1 text-right font-medium">{{ $m->team_name_home }}</div>
                                    <div class="w-20 text-center font-bold">
                                        @if(!is_null($m->team_score_home) && !is_null($m->team_score_away))
                                            {{ $m->team_score_home }} : {{ $m->team_score_away }}
                                        @else
                                            - : -
                                        @endif
                                    </div>
                                    <div class="flex-1 text-left font-medium">{{ $m->team_name_away }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endif

<!-- Table Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-gray-900 mb-12 text-center">Tabelle (Seniors)</h2>

        @if(isset($standings) && $standings->count())
            @php($clubId = $settings->club_fifa_id ?? null)
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-900 text-white">
                        <tr>
                            <th class="py-4 px-6 text-left">#</th>
                            <th class="py-4 px-6 text-left">Verein</th>
                            <th class="py-4 px-6 text-center hidden md:table-cell">Sp</th>
                            <th class="py-4 px-6 text-center hidden md:table-cell">S</th>
                            <th class="py-4 px-6 text-center hidden md:table-cell">U</th>
                            <th class="py-4 px-6 text-center hidden md:table-cell">N</th>
                            <th class="py-4 px-6 text-center">Tore</th>
                            <th class="py-4 px-6 text-center font-bold">Pkt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($standings as $row)
                            @php($isClub = $clubId && (int)$row->team_fifa_id === (int)$clubId)
                            <tr class="border-b hover:bg-gray-50 {{ $isClub ? 'bg-primary/5' : '' }}">
                                <td class="py-4 px-6 font-bold {{ $isClub ? 'text-primary' : '' }}">{{ $row->position }}</td>
                                <td class="py-4 px-6 {{ $isClub ? 'font-bold text-primary' : '' }}">
                                    <div class="flex items-center gap-3">
                                        @if($row->team_image_logo)
                                            <img src="{{ $row->team_image_logo }}" alt="{{ $row->international_team_name }}" class="w-8 h-8 object-contain">
                                        @endif
                                        <span>{{ $row->international_team_name }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center hidden md:table-cell">{{ $row->matches_played }}</td>
                                <td class="py-4 px-6 text-center hidden md:table-cell">{{ $row->wins }}</td>
                                <td class="py-4 px-6 text-center hidden md:table-cell">{{ $row->draws }}</td>
                                <td class="py-4 px-6 text-center hidden md:table-cell">{{ $row->losses }}</td>
                                <td class="py-4 px-6 text-center">{{ $row->goals_for }}:{{ $row->goals_against }}</td>
                                <td class="py-4 px-6 text-center font-bold">{{ $row->points }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="max-w-3xl mx-auto text-center text-gray-600">Keine Tabelle gefunden.</div>
        @endif
    </div>
</section>

<!-- Sponsors Section -->
<section class="py-16 bg-white border-t border-gray-200">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Unsere Partner</h2>
        <div class="flex flex-wrap justify-center items-center gap-12 opacity-60">
            <div class="text-4xl font-bold text-gray-400">SPONSOR 1</div>
            <div class="text-4xl font-bold text-gray-400">SPONSOR 2</div>
            <div class="text-4xl font-bold text-gray-400">SPONSOR 3</div>
            <div class="text-4xl font-bold text-gray-400">SPONSOR 4</div>
        </div>
    </div>
</section>
@endsection
