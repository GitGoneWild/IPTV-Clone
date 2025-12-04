<?php

namespace Tests\Feature;

use App\Jobs\ImportEpgJob;
use App\Models\EpgSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EpgImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test EPG source creation.
     */
    public function test_can_create_epg_source(): void
    {
        $source = EpgSource::create([
            'name' => 'Test EPG',
            'url' => 'https://example.com/epg.xml',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('epg_sources', [
            'name' => 'Test EPG',
            'url' => 'https://example.com/epg.xml',
        ]);
    }

    /**
     * Test EPG source active scope.
     */
    public function test_active_scope_filters_inactive_sources(): void
    {
        EpgSource::create(['name' => 'Active', 'is_active' => true]);
        EpgSource::create(['name' => 'Inactive', 'is_active' => false]);

        $this->assertEquals(1, EpgSource::active()->count());
        $this->assertEquals('Active', EpgSource::active()->first()->name);
    }

    /**
     * Test EPG import job instantiation.
     */
    public function test_import_job_can_be_instantiated(): void
    {
        $source = EpgSource::create([
            'name' => 'Test EPG',
            'url' => 'https://example.com/epg.xml',
            'is_active' => true,
        ]);

        $job = new ImportEpgJob($source);

        $this->assertInstanceOf(ImportEpgJob::class, $job);
        $this->assertEquals($source->id, $job->epgSource->id);
    }
}
