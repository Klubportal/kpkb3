<?php

namespace App\Jobs;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use App\Settings\Tenant\ClubSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job: Create Default Club Settings
 *
 * This job creates default settings for a newly created tenant.
 * Runs automatically after tenant database is created and migrated.
 *
 * Two approaches available:
 * 1. Using Spatie Settings classes (recommended, type-safe)
 * 2. Using direct DB inserts (flexible, more control)
 */
class CreateDefaultClubSettings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public TenantWithDatabase $tenant
    ) {}

    /**
     * Execute the job - Creates ClubSettings for new tenant
     */
    public function handle(): void
    {
        // Tenant-Context initialisieren
        tenancy()->initialize($this->tenant);

        try {
            // ClubSettings direkt in DB einfügen (vermeidet "MissingSettings" beim ersten app())
            $settings = [
                'club_name' => $this->tenant->name ?? 'Mein Verein',
                'club_full_name' => $this->tenant->name ?? 'Mein Verein',
                'club_slogan' => null,
                'founded_year' => null,
                'logo' => null,
                'logo_dark' => null,
                'favicon' => null,
                'header_image' => null,
                'primary_color' => '#3b82f6',
                'secondary_color' => '#64748b',
                'accent_color' => '#f59e0b',
                'font_family' => 'Inter',
                'font_size' => 16,
                'contact_email' => $this->tenant->email ?? '',
                'phone' => '',
                'address' => '',
                'city' => '',
                'postal_code' => '',
                'country' => 'Croatia',
                'facebook_url' => null,
                'instagram_url' => null,
                'twitter_url' => null,
                'youtube_url' => null,
                'tiktok_url' => null,
                'show_sponsors' => true,
                'show_news' => true,
                'show_calendar' => true,
                'timezone' => 'Europe/Zagreb',
                'locale' => 'hr',
            ];

            foreach ($settings as $name => $value) {
                DB::table('settings')->insert([
                    'group' => 'club',
                    'name' => $name,
                    'payload' => json_encode($value),
                    'locked' => false,
                ]);
            }

            Log::info("ClubSettings created for tenant: {$this->tenant->id}");

        } catch (\Exception $e) {
            Log::error("Failed to create ClubSettings for tenant {$this->tenant->id}: " . $e->getMessage());
            throw $e;
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Alternative: Direct DB Insert Approach (commented out, kept for reference)
     *
     * Uncomment this method and comment out the handle() method above
     * if you prefer more control over the settings creation.
     */
    /*
    public function handleWithDirectInsert(): void
    {
        // Initialize tenant context
        tenancy()->initialize($this->tenant);

        try {
            // Default theme settings
            $this->createThemeSettings();

            // Default club settings
            $this->createClubSettings();

            // Default notification settings
            $this->createNotificationSettings();

            // Default email settings
            $this->createEmailSettings();

            Log::info("Default settings created for tenant: {$this->tenant->id}");

        } catch (\Exception $e) {
            Log::error("Failed to create default settings for tenant {$this->tenant->id}: " . $e->getMessage());
            throw $e;
        } finally {
            // End tenant context
            tenancy()->end();
        }
    }

    protected function createThemeSettings(): void
    {
        $settings = [
            ['group' => 'theme', 'name' => 'active_theme', 'payload' => json_encode('default'), 'locked' => false],
            ['group' => 'theme', 'name' => 'primary_color', 'payload' => json_encode('#dc2626'), 'locked' => false],
            ['group' => 'theme', 'name' => 'secondary_color', 'payload' => json_encode('#1f2937'), 'locked' => false],
            ['group' => 'theme', 'name' => 'header_bg_color', 'payload' => json_encode('#dc2626'), 'locked' => false],
            ['group' => 'theme', 'name' => 'footer_bg_color', 'payload' => json_encode('#1f2937'), 'locked' => false],
        ];
        foreach ($settings as $setting) DB::table('settings')->insert($setting);
    }

    protected function createClubSettings(): void
    {
        $settings = [
            ['group' => 'club', 'name' => 'club_name', 'payload' => json_encode($this->tenant->name), 'locked' => false],
            ['group' => 'club', 'name' => 'club_email', 'payload' => json_encode($this->tenant->email ?? ''), 'locked' => false],
            ['group' => 'club', 'name' => 'club_phone', 'payload' => json_encode(''), 'locked' => false],
            ['group' => 'club', 'name' => 'club_address', 'payload' => json_encode(''), 'locked' => false],
            ['group' => 'club', 'name' => 'club_logo', 'payload' => json_encode('/images/logo.svg'), 'locked' => false],
            ['group' => 'club', 'name' => 'founded_year', 'payload' => json_encode(date('Y')), 'locked' => false],
        ];
        foreach ($settings as $setting) DB::table('settings')->insert($setting);
    }

    protected function createNotificationSettings(): void
    {
        $settings = [
            ['group' => 'notifications', 'name' => 'email_notifications', 'payload' => json_encode(true), 'locked' => false],
            ['group' => 'notifications', 'name' => 'push_notifications', 'payload' => json_encode(false), 'locked' => false],
            ['group' => 'notifications', 'name' => 'sms_notifications', 'payload' => json_encode(false), 'locked' => false],
        ];
        foreach ($settings as $setting) DB::table('settings')->insert($setting);
    }

    protected function createEmailSettings(): void
    {
        $settings = [
            ['group' => 'email', 'name' => 'from_name', 'payload' => json_encode($this->tenant->name), 'locked' => false],
            ['group' => 'email', 'name' => 'from_address', 'payload' => json_encode($this->tenant->email ?? 'noreply@example.com'), 'locked' => false],
            ['group' => 'email', 'name' => 'reply_to', 'payload' => json_encode($this->tenant->email ?? ''), 'locked' => false],
        ];
        foreach ($settings as $setting) DB::table('settings')->insert($setting);
    }
    */

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['tenant:' . $this->tenant->id, 'tenant-setup', 'default-settings'];
    }
}
