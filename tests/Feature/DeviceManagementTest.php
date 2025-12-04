<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test device creation.
     */
    public function test_can_create_device(): void
    {
        $user = User::factory()->create();

        $device = Device::create([
            'user_id' => $user->id,
            'name' => 'Living Room TV',
            'mac_address' => '00:11:22:33:44:55',
            'ip_address' => '192.168.1.100',
            'device_type' => 'smart_tv',
        ]);

        $this->assertDatabaseHas('devices', [
            'user_id' => $user->id,
            'mac_address' => '00:11:22:33:44:55',
        ]);
    }

    /**
     * Test device blocking.
     */
    public function test_can_block_device(): void
    {
        $user = User::factory()->create();

        $device = Device::create([
            'user_id' => $user->id,
            'mac_address' => '00:11:22:33:44:55',
            'is_active' => true,
            'is_blocked' => false,
        ]);

        $this->assertTrue($device->canConnect());

        $device->block();

        $this->assertFalse($device->fresh()->canConnect());
    }

    /**
     * Test device unblocking.
     */
    public function test_can_unblock_device(): void
    {
        $user = User::factory()->create();

        $device = Device::create([
            'user_id' => $user->id,
            'mac_address' => '00:11:22:33:44:55',
            'is_blocked' => true,
        ]);

        $device->unblock();

        $this->assertTrue($device->fresh()->canConnect());
    }

    /**
     * Test find or create by MAC.
     */
    public function test_find_or_create_by_mac(): void
    {
        $user = User::factory()->create();

        $device1 = Device::findOrCreateByMac($user->id, '00:11:22:33:44:55', ['name' => 'Test Device']);
        $device2 = Device::findOrCreateByMac($user->id, '00:11:22:33:44:55', ['name' => 'Different Name']);

        $this->assertEquals($device1->id, $device2->id);
        $this->assertEquals('Test Device', $device2->name);
    }

    /**
     * Test device type detection.
     */
    public function test_device_type_detection(): void
    {
        $this->assertEquals('android', Device::detectDeviceType('Mozilla/5.0 (Linux; Android 10)'));
        $this->assertEquals('ios', Device::detectDeviceType('Mozilla/5.0 (iPhone; CPU iPhone OS)'));
        $this->assertEquals('smart_tv', Device::detectDeviceType('Mozilla/5.0 (SmartTV; SMART-TV)'));
        $this->assertEquals('media_player', Device::detectDeviceType('VLC/3.0'));
        $this->assertNull(Device::detectDeviceType(null));
    }

    /**
     * Test device scopes.
     */
    public function test_device_scopes_work(): void
    {
        $user = User::factory()->create();

        Device::create([
            'user_id' => $user->id,
            'mac_address' => '00:11:22:33:44:55',
            'is_active' => true,
            'is_blocked' => false,
        ]);

        Device::create([
            'user_id' => $user->id,
            'mac_address' => '00:11:22:33:44:66',
            'is_active' => true,
            'is_blocked' => true,
        ]);

        $this->assertEquals(1, Device::active()->count());
        $this->assertEquals(1, Device::blocked()->count());
    }
}
