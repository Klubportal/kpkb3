<?php

namespace App\Http\Controllers\Central;

use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class TenantRegistrationController extends Controller
{
    public function show()
    {
        return view('tenant-registration');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'club_name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'subdomain' => 'required|alpha_dash|unique:domains,domain',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'plan' => 'required|in:trial,basic,premium',
        ]);

        // 1. Tenant erstellen mit Admin-Daten im data Feld
        $tenant = Tenant::create([
            'id' => Str::slug($validated['club_name']) . '-' . Str::random(8),
            'name' => $validated['club_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'plan' => $validated['plan'],
            'trial_ends_at' => now()->addDays(14),  // 14 Tage Trial
            'data' => [
                'admin_name' => $validated['admin_name'],
                'admin_email' => $validated['admin_email'],
                'admin_password' => $validated['password'], // Wird im Job gehasht
            ],
        ]);

        // 2. Domain zuweisen
        $domain = $validated['subdomain'] . '.' . config('app.domain');
        $tenant->domains()->create([
            'domain' => $domain,
        ]);

        // 3. Admin User erstellen (Job wird automatisch über JobPipeline ausgeführt)
        // Kein manueller dispatch nötig - läuft über TenantCreated Event

        // 4. Optional: Stripe Subscription erstellen
        // $this->createStripeSubscription($tenant, $validated['plan']);

        return redirect()
            ->away('http://' . $domain . '/club/login')
            ->with('success', 'Verein erfolgreich registriert!');
    }

    /**
     * Create Stripe subscription for tenant
     *
     * @param Tenant $tenant
     * @param string $plan
     * @return void
     */
    protected function createStripeSubscription(Tenant $tenant, string $plan)
    {
        $stripePriceIds = [
            'basic' => 'price_xxxxx',    // Deine Stripe Price IDs
            'premium' => 'price_yyyyy',
        ];

        if ($plan !== 'trial') {
            // Stripe Customer erstellen (im Central Context!)
            $customer = $tenant->createAsStripeCustomer([
                'name' => $tenant->name,
                'email' => $tenant->email,
            ]);

            // Subscription erstellen
            $tenant->newSubscription('default', $stripePriceIds[$plan])
                   ->create();
        }
    }
}
