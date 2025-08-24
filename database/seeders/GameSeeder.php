<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            [
                'name' => 'Mobile Legends',
                'slug' => 'mobile-legends',
                'publisher' => 'Moonton',
                'category' => 'games',
                'is_hot' => true,
                'requires_server' => true,
                'id_label' => 'User ID',
                'server_label' => 'Server ID',
                'description' => 'Top up Mobile Legends: Bang Bang diamonds untuk unlock hero dan skin favoritmu!'
            ],
            [
                'name' => 'Free Fire',
                'slug' => 'free-fire',
                'publisher' => 'Garena',
                'category' => 'games',
                'is_hot' => true,
                'requires_server' => false,
                'id_label' => 'Player ID',
                'description' => 'Top up Free Fire diamonds instant dan murah!'
            ],
            [
                'name' => 'PUBG Mobile',
                'slug' => 'pubg-mobile',
                'publisher' => 'Tencent Games',
                'category' => 'games',
                'is_hot' => true,
                'requires_server' => false,
                'id_label' => 'User ID',
                'description' => 'Beli UC PUBG Mobile untuk upgrade senjata dan skin.'
            ],
            [
                'name' => 'Genshin Impact',
                'slug' => 'genshin-impact',
                'publisher' => 'HoYoverse',
                'category' => 'games',
                'is_hot' => true,
                'requires_server' => true,
                'id_label' => 'UID',
                'server_label' => 'Server',
                'description' => 'Top up Genesis Crystal dan Primogem Genshin Impact.'
            ],
            [
                'name' => 'Valorant',
                'slug' => 'valorant',
                'publisher' => 'Riot Games',
                'category' => 'games',
                'requires_server' => false,
                'id_label' => 'Riot ID',
                'description' => 'Beli Valorant Points untuk unlock agent dan skin senjata.'
            ],
            [
                'name' => 'Call of Duty Mobile',
                'slug' => 'cod-mobile',
                'publisher' => 'Activision',
                'category' => 'games',
                'requires_server' => false,
                'id_label' => 'Player ID',
                'description' => 'Top up CP Call of Duty Mobile dengan harga termurah.'
            ],
            [
                'name' => 'Honor of Kings',
                'slug' => 'honor-of-kings',
                'publisher' => 'Tencent Games',
                'category' => 'games',
                'requires_server' => false,
                'id_label' => 'User ID',
                'description' => 'Top up Token Honor of Kings instant 24 jam.'
            ],
            [
                'name' => 'Honkai Star Rail',
                'slug' => 'honkai-star-rail',
                'publisher' => 'HoYoverse',
                'category' => 'games',
                'requires_server' => true,
                'id_label' => 'UID',
                'server_label' => 'Server',
                'description' => 'Top up Oneiric Shard dan Stellar Jade HSR.'
            ],
            [
                'name' => 'Roblox',
                'slug' => 'roblox',
                'publisher' => 'Roblox Corporation',
                'category' => 'voucher',
                'is_hot' => true,
                'requires_server' => false,
                'id_label' => 'Username',
                'description' => 'Beli Robux untuk Roblox dengan berbagai nominal.'
            ],
            [
                'name' => 'Arena Breakout',
                'slug' => 'arena-breakout',
                'publisher' => 'Tencent Games',
                'category' => 'games',
                'requires_server' => false,
                'id_label' => 'User ID',
                'description' => 'Top up Bonds Arena Breakout Infinite.'
            ],
            [
                'name' => 'Netflix',
                'slug' => 'netflix',
                'publisher' => 'Netflix',
                'category' => 'entertainment',
                'requires_server' => false,
                'id_label' => 'Email',
                'description' => 'Langganan Netflix Premium dengan harga terjangkau.'
            ],
            [
                'name' => 'Spotify Premium',
                'slug' => 'spotify',
                'publisher' => 'Spotify',
                'category' => 'entertainment',
                'requires_server' => false,
                'id_label' => 'Email',
                'description' => 'Nikmati musik tanpa iklan dengan Spotify Premium.'
            ],
        ];

        foreach ($games as $index => $game) {
            $payload = array_merge($game, [
                'sort_order' => $index,
                'cover_path' => '/images/games/' . $game['slug'] . '.jpg',
            ]);

            // kunci unik: slug
            Game::updateOrCreate(
                ['slug' => $game['slug']],
                Arr::except($payload, ['slug'])
            );
        }
    }
}
