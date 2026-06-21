<?php

namespace Tests\Feature\Plugin;

use App\Http\Middleware\EnsurePluginEnabled;
use App\Models\User;
use App\Modules\Tenancy\Models\Tenant;
use App\Plugins\Infrastructure\Models\{Plugin, TenantPlugin};
use App\Support\PluginRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EnsurePluginEnabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_bypasses_plugin_check(): void
    {
        $this->withoutExceptionHandling();
        $super = User::factory()->create(['tenant_id' => null, 'tipe' => 'super_admin']);

        // Since actingAs sets the user resolver, we can construct request and execute middleware
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn() => $super);

        $middleware = new EnsurePluginEnabled(app(PluginRegistry::class));
        $response = $middleware->handle($request, fn() => new Response('OK'), 'kurikulum');
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_tenant_user_blocked_when_plugin_inactive(): void
    {
        $tenant = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn() => $user);

        $middleware = new EnsurePluginEnabled(app(PluginRegistry::class));
        
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage("Plugin 'kurikulum' tidak aktif untuk tenant Anda.");
        
        $middleware->handle($request, fn() => new Response('OK'), 'kurikulum');
    }

    public function test_tenant_user_allowed_when_plugin_active(): void
    {
        $tenant = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $plugin = Plugin::updateOrCreate(
            ['kode' => 'kurikulum'],
            ['nama' => 'Kurikulum']
        );
        
        TenantPlugin::create([
            'tenant_id' => $tenant->id,
            'plugin_id' => $plugin->id,
            'aktif'     => true,
        ]);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn() => $user);

        $middleware = new EnsurePluginEnabled(app(PluginRegistry::class));
        $response = $middleware->handle($request, fn() => new Response('OK'), 'kurikulum');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
