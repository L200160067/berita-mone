🏗️ 1. Arsitektur Generator (Konsep)

Alur ideal:

Input (admin / AI prompt)
   ↓
Content Generator Service
   ↓
Formatter (SEO + struktur)
   ↓
Database (posts table)
   ↓
API /posts & /posts/{slug}
📦 2. Struktur Database (posts)

Pastikan tabel kamu minimal seperti ini:

posts
- id
- title
- slug
- excerpt
- content (longtext)
- meta_title
- meta_description
- category_id
- author
- cover_url
- cover_thumb
- published_at
- created_at
- updated_at
⚙️ 3. Service: ArticleGeneratorService (Core Logic)
<?php

namespace App\Services;

use Illuminate\Support\Str;

class ArticleGeneratorService
{
    public function generate(array $input): array
    {
        $title = $this->generateTitle($input);
        $slug = Str::slug($title);

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $this->generateExcerpt($input),
            'content' => $this->generateContent($input),
            'meta_title' => $this->generateMetaTitle($title),
            'meta_description' => $this->generateMetaDescription($input),
            'author' => $input['author'] ?? 'Admin',
            'published_at' => now(),
        ];
    }

    private function generateTitle($input)
    {
        return $input['title']
            ?? "{$input['topic']} di {$input['location']} {$input['year']}";
    }

    private function generateExcerpt($input)
    {
        return Str::limit(
            "{$input['topic']} yang diselenggarakan di {$input['location']} bertujuan untuk {$input['goal']}.",
            150
        );
    }

    private function generateMetaTitle($title)
    {
        return Str::limit($title, 60);
    }

    private function generateMetaDescription($input)
    {
        return Str::limit(
            "{$input['topic']} di {$input['location']} memberikan dampak {$input['impact']}.",
            155
        );
    }

    private function generateContent($input)
    {
        return view('articles.template', $input)->render();
    }
}
🧩 4. Blade Template (articles/template.blade.php)

Ini kunci utama agar konten selalu konsisten & SEO-ready.

<h1>{{ $title ?? $topic }}</h1>

<p>
{{ $topic }} yang diselenggarakan di {{ $location }} menjadi langkah penting dalam {{ $goal }}.
</p>

<h2>Latar Belakang</h2>
<p>
Kegiatan ini bertujuan untuk {{ $goal }} serta meningkatkan kualitas dalam bidang {{ $field ?? 'teknologi' }}.
</p>

<h2>Pelaksanaan Kegiatan</h2>
<p>
Acara berlangsung dengan melibatkan {{ $participants ?? 'peserta'}}, yang mengikuti berbagai sesi mulai dari {{ $sessions ?? 'materi utama' }}.
</p>

<h2>Dampak dan Hasil</h2>
<p>
Diharapkan kegiatan ini memberikan dampak positif berupa {{ $impact }}.
</p>

<h2>Penutup</h2>
<p>
Dengan adanya kegiatan ini, diharapkan akan tercipta peningkatan kualitas dan keberlanjutan dalam pengembangan {{ $field ?? 'bidang terkait' }}.
</p>
🤖 5. Integrasi AI (Opsional tapi Powerful)

Kalau mau upgrade ke level industrial:

Prompt AI (Reusable)
Buat artikel berita dengan gaya jurnalistik profesional.

Topik: {{topic}}
Lokasi: {{location}}
Tujuan: {{goal}}

Struktur:
- Judul
- Paragraf pembuka
- Latar belakang
- Jalannya kegiatan
- Dampak
- Penutup

Gunakan bahasa formal, SEO friendly, dan paragraf pendek.
🚀 6. Controller Endpoint (Generate Article)
public function generate(Request $request, ArticleGeneratorService $service)
{
    $data = $service->generate($request->all());

    $post = Post::create($data);

    return response()->json([
        'success' => true,
        'data' => $post
    ]);
}
📊 7. Contoh Input
{
  "topic": "Workshop Web Development",
  "location": "SMK Muhammadiyah 1 Sukoharjo",
  "goal": "meningkatkan kompetensi siswa",
  "impact": "peningkatan skill digital",
  "participants": "siswa kelas XII",
  "sessions": "HTML, CSS, dan Laravel",
  "year": "2026"
}
✅ 8. Output yang Dihasilkan
Judul otomatis SEO-friendly
Slug clean
Artikel sudah terstruktur (H1, H2)
Meta SEO siap pakai
Bisa langsung publish