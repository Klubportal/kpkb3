<?php

namespace App\Livewire\Central;

use Livewire\Component;
use App\Models\Central\News;
use App\Settings\GeneralSettings;
use App\Settings\ThemeSettings;
use App\Settings\SocialMediaSettings;
use App\Settings\ContactSettings;
use Illuminate\Support\Facades\Mail;

class LandingPage extends Component
{
    public $contact_name = '';
    public $contact_email = '';
    public $contact_phone = '';
    public $contact_message = '';
    public $contact_success = false;

    public $register_club_name = '';
    public $register_email = '';
    public $register_phone = '';
    public $register_message = '';
    public $register_success = false;

    public function mount()
    {
        // Set locale from session
        if (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }
    }

    protected $rules = [
        'contact_name' => 'required|min:3',
        'contact_email' => 'required|email',
        'contact_message' => 'required|min:10',
    ];

    protected $registerRules = [
        'register_club_name' => 'required|min:3',
        'register_email' => 'required|email',
        'register_phone' => 'required',
    ];

    public function submitContact()
    {
        $this->validate();

        // Hier könnten Sie eine E-Mail senden
        // Mail::to('info@klubportal.com')->send(new ContactMail($this->contact_name, $this->contact_email, $this->contact_message));

        $this->contact_success = true;
        $this->reset(['contact_name', 'contact_email', 'contact_phone', 'contact_message']);
    }

    public function submitRegistration()
    {
        $this->validate($this->registerRules);

        // Hier könnten Sie einen Lead in der Datenbank speichern
        // Lead::create([...]);

        $this->register_success = true;
        $this->reset(['register_club_name', 'register_email', 'register_phone', 'register_message']);
    }

    public function render()
    {
        // Stelle sicher, dass wir Central DB verwenden
        $originalConnection = config('database.default');
        config(['database.default' => 'central']);

        try {
            // Lade Settings aus Central DB mit Fallback
            $generalSettings = $this->loadGeneralSettings();
            $themeSettings = $this->loadThemeSettings();
            $socialSettings = $this->loadSocialMediaSettings();
            $contactSettings = $this->loadContactSettings();

            $latestNews = News::where('status', 'published')
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->take(3)
                ->get();

            return view('livewire.central.landing-page-modern', [
                'latestNews' => $latestNews,
                'generalSettings' => $generalSettings,
                'themeSettings' => $themeSettings,
                'socialSettings' => $socialSettings,
                'contactSettings' => $contactSettings,
            ]);
        } finally {
            // Stelle ursprüngliche Connection wieder her
            config(['database.default' => $originalConnection]);
        }
    }

    private function loadGeneralSettings()
    {
        try {
            return app(GeneralSettings::class);
        } catch (\Exception $e) {
            // Fallback mit Default-Werten
            return (object) [
                'site_name' => 'Klubportal',
                'site_description' => 'Die moderne Vereinsverwaltung',
                'logo' => null,
                'favicon' => null,
                'logo_height' => '3rem',
                'primary_color' => '#dc2626',
                'secondary_color' => '#991b1b',
                'contact_email' => 'info@klubportal.com',
                'phone' => null,
            ];
        }
    }

    private function loadThemeSettings()
    {
        try {
            return app(ThemeSettings::class);
        } catch (\Exception $e) {
            return (object) [
                'header_bg_color' => '#dc2626',
                'footer_bg_color' => '#1f2937',
            ];
        }
    }

    private function loadSocialMediaSettings()
    {
        try {
            return app(SocialMediaSettings::class);
        } catch (\Exception $e) {
            return (object) [
                'facebook_url' => null,
                'instagram_url' => null,
                'twitter_url' => null,
                'youtube_url' => null,
                'linkedin_url' => null,
                'tiktok_url' => null,
            ];
        }
    }

    private function loadContactSettings()
    {
        try {
            return app(ContactSettings::class);
        } catch (\Exception $e) {
            return (object) [
                'company_name' => null,
                'street' => null,
                'postal_code' => null,
                'city' => null,
                'phone' => null,
                'mobile' => null,
                'email' => null,
            ];
        }
    }
}
