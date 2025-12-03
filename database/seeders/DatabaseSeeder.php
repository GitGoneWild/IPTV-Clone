<?php

namespace Database\Seeders;

use App\Models\Bouquet;
use App\Models\Category;
use App\Models\Server;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@homelabtv.local',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
            'is_active' => true,
            'allowed_outputs' => ['m3u', 'xtream', 'enigma2'],
            'max_connections' => 10,
            'expires_at' => null,
        ]);

        // Create sample regular user
        $user1 = User::create([
            'name' => 'Demo User',
            'email' => 'demo@homelabtv.local',
            'username' => 'demo',
            'password' => Hash::make('demo123'),
            'is_admin' => false,
            'is_active' => true,
            'allowed_outputs' => ['m3u', 'xtream', 'enigma2'],
            'max_connections' => 2,
            'expires_at' => now()->addYear(),
        ]);

        // Create sample reseller
        $reseller = User::create([
            'name' => 'Reseller User',
            'email' => 'reseller@homelabtv.local',
            'username' => 'reseller',
            'password' => Hash::make('reseller123'),
            'is_admin' => false,
            'is_reseller' => true,
            'is_active' => true,
            'credits' => 100,
            'allowed_outputs' => ['m3u', 'xtream', 'enigma2'],
            'max_connections' => 5,
            'expires_at' => now()->addYear(),
        ]);

        // Create primary server
        $server = Server::create([
            'name' => 'Primary Server',
            'base_url' => 'http://localhost:8080',
            'http_port' => 8080,
            'is_active' => true,
            'is_primary' => true,
            'max_connections' => 1000,
        ]);

        // Create categories
        $categories = [
            ['name' => 'Live TV', 'sort_order' => 1],
            ['name' => 'CCTV Cameras', 'sort_order' => 2],
            ['name' => 'Homelab Streams', 'sort_order' => 3],
            ['name' => 'Radio', 'sort_order' => 4],
        ];

        $createdCategories = [];
        foreach ($categories as $categoryData) {
            $createdCategories[] = Category::create(array_merge($categoryData, ['is_active' => true]));
        }

        // Create 5 sample streams
        $streams = [
            [
                'name' => 'Big Buck Bunny',
                'stream_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'stream_type' => 'hls',
                'category_id' => $createdCategories[0]->id,
                'epg_channel_id' => 'bigbuckbunny.tv',
                'is_active' => true,
                'last_check_status' => 'online',
            ],
            [
                'name' => 'Sintel',
                'stream_url' => 'https://bitdash-a.akamaihd.net/content/sintel/hls/playlist.m3u8',
                'stream_type' => 'hls',
                'category_id' => $createdCategories[0]->id,
                'epg_channel_id' => 'sintel.tv',
                'is_active' => true,
                'last_check_status' => 'online',
            ],
            [
                'name' => 'Front Door Camera',
                'stream_url' => 'rtsp://localhost:8554/frontdoor',
                'stream_type' => 'rtmp',
                'category_id' => $createdCategories[1]->id,
                'is_active' => true,
                'notes' => 'Sample CCTV stream placeholder',
            ],
            [
                'name' => 'Homelab Dashboard',
                'stream_url' => 'http://localhost:3000/stream',
                'stream_type' => 'http',
                'category_id' => $createdCategories[2]->id,
                'is_active' => true,
                'notes' => 'Sample homelab stream',
            ],
            [
                'name' => 'Tears of Steel',
                'stream_url' => 'https://demo.unified-streaming.com/k8s/features/stable/video/tears-of-steel/tears-of-steel.ism/.m3u8',
                'stream_type' => 'hls',
                'category_id' => $createdCategories[0]->id,
                'epg_channel_id' => 'tearsofsteel.tv',
                'is_active' => true,
                'last_check_status' => 'online',
            ],
        ];

        $createdStreams = [];
        foreach ($streams as $streamData) {
            $createdStreams[] = Stream::create(array_merge($streamData, [
                'server_id' => $server->id,
                'sort_order' => count($createdStreams),
            ]));
        }

        // Create bouquets
        $basicBouquet = Bouquet::create([
            'name' => 'Basic Package',
            'description' => 'Basic streaming package with essential channels',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $premiumBouquet = Bouquet::create([
            'name' => 'Premium Package',
            'description' => 'Full access to all streams',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Attach streams to bouquets
        $basicBouquet->streams()->attach([
            $createdStreams[0]->id => ['sort_order' => 1],
            $createdStreams[1]->id => ['sort_order' => 2],
        ]);

        $premiumBouquet->streams()->attach([
            $createdStreams[0]->id => ['sort_order' => 1],
            $createdStreams[1]->id => ['sort_order' => 2],
            $createdStreams[2]->id => ['sort_order' => 3],
            $createdStreams[3]->id => ['sort_order' => 4],
            $createdStreams[4]->id => ['sort_order' => 5],
        ]);

        // Assign bouquets to users
        $user1->bouquets()->attach([$basicBouquet->id, $premiumBouquet->id]);
        $reseller->bouquets()->attach([$premiumBouquet->id]);
        $admin->bouquets()->attach([$basicBouquet->id, $premiumBouquet->id]);
    }
}
