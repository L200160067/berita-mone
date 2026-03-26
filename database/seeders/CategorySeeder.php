<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Berita Sekolah',
            'Teknologi Pendidikan',
            'Kegiatan Siswa',
            'Pengumuman',
            'Prestasi',
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'slug' => Str::slug($name)]
            );
        }

        $this->command->info('✅ Categories seeded: '.count($categories).' items.');
    }
}
