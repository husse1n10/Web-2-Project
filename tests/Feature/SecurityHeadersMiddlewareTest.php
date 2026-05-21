<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SecurityHeadersMiddlewareTest extends TestCase
{
    public function test_security_headers_are_added_on_web_routes(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Content-Security-Policy');
        $this->assertStringContainsString(
            "frame-ancestors 'self'",
            (string) $response->headers->get('Content-Security-Policy')
        );
    }

    public function test_production_csp_allows_payment_redirect_hosts_for_form_submissions(): void
    {
        $originalEnv = $this->app['env'];
        $this->app['env'] = 'production';
        Config::set('app.url', 'https://cedargov.app');

        try {
            $response = $this->get('/');
        } finally {
            $this->app['env'] = $originalEnv;
        }

        $response->assertStatus(200);

        $csp = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("form-action 'self'", $csp);
        $this->assertStringContainsString('https://cedargov.app', $csp);
        $this->assertStringContainsString('https://checkout.stripe.com', $csp);
        $this->assertStringContainsString('https://*.stripe.com', $csp);
        $this->assertStringContainsString('https://nowpayments.io', $csp);
        $this->assertStringContainsString('https://*.nowpayments.io', $csp);
    }
}
