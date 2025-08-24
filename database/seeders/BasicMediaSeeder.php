<?php

namespace Database\Seeders;

use App\Models\BasicMedia;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BasicMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $media = [
            ['id' => 1, 'key' => 'hero_image', 'comment' => 'Főképernyő nagy kép'],
            ['id' => 2, 'key' => 'default_logo', 'comment' => 'Fő logó (ez szerepel a felső és alsó sávban és még sok helyen)'],
            ['id' => 3, 'key' => 'favicon', 'comment' => 'Böngészőfül kis kép'],
            ['id' => 4, 'key' => 'default_breadcrumb', 'comment' => 'Oldalak tetején lévő kép (kivéve blog)'],
        ];

        foreach ($media as $media_item) {
            BasicMedia::updateOrCreate(
                ['id' => $media_item['id']],
                array_merge($media_item, [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ])
            );
        }
    }
}
