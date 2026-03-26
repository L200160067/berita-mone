<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('slug', 'berita-sekolah')->first()
            ?? Category::first();

        $articles = [
            [
                'topic' => 'Workshop Web Development Laravel',
                'location' => 'SMK Muhammadiyah 1 Sukoharjo',
                'goal' => 'meningkatkan kompetensi siswa',
                'impact' => 'peningkatan skill digital siswa kelas XII',
            ],
            [
                'topic' => 'Pelatihan Desain Grafis Profesional',
                'location' => 'Lab Komputer SMA Negeri 2 Solo',
                'goal' => 'membekali siswa dengan skill industri kreatif',
                'impact' => 'siswa mampu membuat portfolio digital',
            ],
            [
                'topic' => 'Seminar Kewirausahaan Digital',
                'location' => 'Aula Universitas Batik Surakarta',
                'goal' => 'mendorong jiwa enterpreneur muda',
                'impact' => 'terbentuknya 10 startup siswa baru',
            ],
        ];

        $titleFormats = [
            fn ($t, $l) => "{$t} di {$l}",
            fn ($t, $l) => "{$l} Gelar {$t}",
            fn ($t, $l) => "Suksesnya {$t} di {$l}",
        ];

        foreach ($articles as $i => $data) {
            $titleFn = $titleFormats[$i % count($titleFormats)];
            $title = $titleFn($data['topic'], $data['location']);
            $slug = Str::slug($title).'-'.($i + 1);
            $hash = md5(json_encode($data));

            if (Post::where('payload_hash', $hash)->exists()) {
                continue;
            }

            $html = "<h1>{$data['topic']}</h1>";
            $html .= "<p>Kegiatan <strong>{$data['topic']}</strong> diselenggarakan di {$data['location']} bertujuan untuk {$data['goal']}.</p>";
            $html .= '<h2>Dampak Kegiatan</h2>';
            $html .= "<p>{$data['impact']}. Ini menjadi langkah konkret menuju pendidikan yang lebih adaptif dan berorientasi industri.</p>";
            $html .= '<h2>Penutup</h2>';
            $html .= '<p>Kegiatan ini diharapkan dapat menjadi inspirasi bagi lembaga pendidikan lainnya di wilayah sekitar.</p>';

            Post::create([
                'category_id' => $category?->id,
                'title' => $title,
                'slug' => $slug,
                'excerpt' => "{$data['topic']} di {$data['location']} bertujuan untuk {$data['goal']}.",
                'content' => $html,
                'meta_title' => Str::limit($title, 60),
                'meta_description' => Str::limit("{$data['topic']} di {$data['location']} memberikan dampak {$data['impact']}.", 155),
                'author' => 'Admin M-One',
                'status' => 'published',
                'image_source' => 'url',
                'cover_url' => config('app.url').'/uploads/default.jpg',
                'cover_thumb' => config('app.url').'/uploads/default.jpg',
                'published_at' => now()->subDays($i),
                'payload_hash' => $hash,
            ]);
        }

        $this->command->info('✅ Posts seeded: '.count($articles).' sample articles.');
    }
}
