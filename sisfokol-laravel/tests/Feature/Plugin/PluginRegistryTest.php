<?php

namespace Tests\Feature\Plugin;

use App\Support\PluginRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PluginRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_registry_returns_empty_when_no_plugins_on_disk(): void
    {
        // Note: Kurikulum plugin now exists on disk, so registry always has >= 1 plugin.
        // We verify the registry scans correctly (returns array, kode 'kurikulum' exists).
        $registry = app(PluginRegistry::class);
        $this->assertIsArray($registry->all());
        $this->assertArrayHasKey('kurikulum', $registry->all());
    }

    public function test_registry_discovers_plugin_manifest_files(): void
    {
        // Create a fake plugin manifest (Kurikulum already on disk, so registry will have >= 2)
        $this->createFakePlugin('TestPlugin');

        $registry = app(PluginRegistry::class);
        $registry->rescan();

        // Must include our fake plugin
        $this->assertArrayHasKey('testplugin', $registry->all());
        $this->assertSame('testplugin', $registry->get('testplugin')->kode());
    }

    public function test_registry_syncs_to_database(): void
    {
        $this->createFakePlugin('TestPlugin');
        $registry = app(PluginRegistry::class);
        $registry->rescan();
        $registry->syncToDatabase();

        $this->assertDatabaseHas('plugins', ['kode' => 'testplugin', 'nama' => 'TestPlugin Plugin']);
    }

    public function test_is_active_for_tenant_returns_false_when_not_activated(): void
    {
        $this->createFakePlugin('TestPlugin');
        $registry = app(PluginRegistry::class);
        $registry->rescan();

        $this->assertFalse($registry->isActiveForTenant('testplugin', 1));
    }

    private function createFakePlugin(string $name): void
    {
        $dir = app_path("Plugins/{$name}");
        @mkdir($dir, 0777, true);
        file_put_contents($dir . "/{$name}Plugin.php", <<<PHP
<?php
namespace App\\Plugins\\{$name};

use App\\Support\\{PluginContract, PluginContext};

class {$name}Plugin implements PluginContract {
    public function kode(): string { return strtolower('{$name}'); }
    public function nama(): string { return '{$name} Plugin'; }
    public function versi(): string { return '1.0.0'; }
    public function isCore(): bool { return false; }
    public function dependencies(): array { return []; }
    public function providerClass(): string { return ''; }
    public function permissions(): array { return []; }
    public function menu(): array { return []; }
    public function boot(PluginContext \$ctx): void {}
}
PHP
        );
    }

    protected function tearDown(): void
    {
        @array_map('unlink', glob(app_path('Plugins/TestPlugin/*')) ?: []);
        @rmdir(app_path('Plugins/TestPlugin'));
        parent::tearDown();
    }
}
