<?php

namespace App\Http\Controllers;

use App\Models\Central\Tenant;
use Illuminate\Http\Request;

class DomainVerificationController extends Controller
{
    /**
     * Verify custom domain via DNS check
     */
    public function verify(Request $request, string $token)
    {
        // Find tenant by verification token
        $tenant = Tenant::where('custom_domain_verification_token', $token)
            ->where('custom_domain_status', 'verifying')
            ->first();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Ungültiger Verification Token oder Domain bereits verifiziert.',
            ], 404);
        }

        // Check DNS record
        $domain = $tenant->custom_domain;
        $expectedCname = "{$tenant->id}.klubportal.com";

        try {
            // Get DNS records for the custom domain
            $records = dns_get_record($domain, DNS_CNAME);

            $verified = false;
            foreach ($records as $record) {
                if (isset($record['target']) &&
                    str_ends_with($record['target'], $expectedCname)) {
                    $verified = true;
                    break;
                }
            }

            if ($verified) {
                // Mark domain as verified
                $tenant->markDomainAsVerified();

                return response()->json([
                    'success' => true,
                    'message' => 'Domain erfolgreich verifiziert!',
                    'domain' => $domain,
                    'verified_at' => $tenant->custom_domain_verified_at,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'DNS CNAME Record nicht gefunden. Bitte warten Sie bis zu 48h für DNS-Propagation.',
                    'expected_cname' => $expectedCname,
                    'checked_domain' => $domain,
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'DNS Lookup fehlgeschlagen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Manual verification by super admin
     */
    public function manualVerify(Request $request, Tenant $tenant)
    {
        // Only super admins can manually verify
        if (!$request->user() || !$request->user()->hasRole('super_admin')) {
            abort(403, 'Keine Berechtigung');
        }

        $tenant->markDomainAsVerified();

        return response()->json([
            'success' => true,
            'message' => 'Domain manuell verifiziert',
        ]);
    }
}
