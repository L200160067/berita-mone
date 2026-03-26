<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleGeneratorService
{
    /**
     * Main entry point. Attempts Groq AI generation first,
     * falls back to static template if AI fails or key is missing.
     */
    public function generate(array $input): array
    {
        $aiContent = $this->callGroqApi($input);

        if ($aiContent) {
            return $this->buildFromAi($input, $aiContent);
        }

        // Fallback to static template
        Log::warning('ArticleGeneratorService: Falling back to static template.');
        return $this->fallbackTemplate($input);
    }

    // ---------------------------------------------------------------------------
    //  GROQ AI PATH
    // ---------------------------------------------------------------------------

    /**
     * Calls Groq API. Returns raw text content or null on failure.
     */
    private function callGroqApi(array $input): ?string
    {
        $apiKey = config('services.groq.api_key');

        if (empty($apiKey)) {
            return null;
        }

        $prompt = $this->buildPrompt($input);

        try {
            $response = Http::timeout(50)
                ->withToken($apiKey)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 1024,
                    'response_format' => ['type' => 'json_object'],
                ])->throw(); // forces concrete Response type; throws on error

            $text = $response->json('choices.0.message.content');

            return $text ?: null;
        } catch (\Throwable $e) {
            Log::error('ArticleGeneratorService: Groq API call failed.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Constructs a carefully engineered Indonesian-language prompt.
     */
    private function buildPrompt(array $input): string
    {
        return <<<PROMPT
Kamu adalah jurnalis profesional Indonesia. Tulis sebuah artikel berita berbahasa Indonesia yang lengkap dan natural.

Data artikel:
- Topik: {$input['topic']}
- Lokasi: {$input['location']}
- Tujuan: {$input['goal']}
- Dampak: {$input['impact']}

Format output HARUS selalu dalam bentuk pure JSON yang valid (tanpa markdown format/petik tiga tambahan) menggunakan struktur berikut:
{
  "title": "...",
  "excerpt": "...",
  "content": "..."
}

Aturan:
- title: judul berita yang menarik, maksimal 70 karakter
- excerpt: ringkasan 1-2 kalimat, maksimal 160 karakter
- content: isi artikel dalam format HTML (gunakan <h2>, <p>, <strong>, <em>), minimal 300 kata, ditulis seperti artikel berita profesional
PROMPT;
    }

    /**
     * Parses AI output and maps it to the Post schema.
     */
    private function buildFromAi(array $input, string $aiText): array
    {
        // Strip possible markdown code fences from AI response
        $clean = trim(preg_replace('/^```(json)?\n?|```$/m', '', trim($aiText)));

        $parsed = json_decode($clean, true);

        if (! $parsed || ! isset($parsed['title'], $parsed['excerpt'], $parsed['content'])) {
            Log::warning('ArticleGeneratorService: Failed to parse Groq JSON, falling back.', ['raw' => $aiText]);

            return $this->fallbackTemplate($input);
        }

        $title = $parsed['title'];

        return [
            'title'            => $title,
            'slug'             => Str::slug($title).'-'.rand(100, 999),
            'excerpt'          => $parsed['excerpt'],
            'content'          => $parsed['content'],
            'meta_title'       => Str::limit($title, 60),
            'meta_description' => Str::limit($parsed['excerpt'], 155),
            'author'           => auth()->user()?->name ?? 'System',
            'is_ai_generated'  => true,
            'source'           => 'groq',
        ];
    }

    // ---------------------------------------------------------------------------
    //  FALLBACK STATIC TEMPLATE PATH
    // ---------------------------------------------------------------------------

    private function fallbackTemplate(array $input): array
    {
        $title = $this->generateTitle($input);

        return [
            'title'            => $title,
            'slug'             => Str::slug($title).'-'.rand(100, 999),
            'excerpt'          => $this->excerpt($input),
            'content'          => $this->content($input),
            'meta_title'       => Str::limit($title, 60),
            'meta_description' => Str::limit($this->excerpt($input), 155),
            'author'           => auth()->user()?->name ?? 'System',
            'is_ai_generated'  => false,
            'source'           => 'template',
        ];
    }

    private function generateTitle(array $input): string
    {
        $formats = [
            "{$input['topic']} di {$input['location']}",
            "{$input['location']} Gelar {$input['topic']}",
            "Suksesnya {$input['topic']} di {$input['location']}",
            "Langkah Maju: {$input['topic']} untuk {$input['goal']}",
            "Mengenal {$input['topic']} yang diadakan di {$input['location']}",
        ];

        return $formats[array_rand($formats)];
    }

    private function excerpt(array $input): string
    {
        $formats = [
            "{$input['topic']} yang diselenggarakan di {$input['location']} bertujuan untuk {$input['goal']}. Sebuah langkah yang sangat dinantikan.",
            "Dalam rangka {$input['goal']}, acara {$input['topic']} kembali digelar secara masif di {$input['location']}.",
            "Berita baik dari {$input['location']}, di mana {$input['topic']} baru saja dilaksanakan demi tercapainya {$input['goal']}.",
        ];

        return $formats[array_rand($formats)];
    }

    private function content(array $input): string
    {
        $openings = [
            "Kegiatan {$input['topic']} menjadi langkah krusial dalam dunia modern saat ini.",
            "Di era disrupsi, inisiatif {$input['topic']} menjadi prioritas bagi banyak pihak.",
            "Kita patut mengapresiasi keberhasilan penyelenggaraan {$input['topic']}.",
        ];

        $opening = $openings[array_rand($openings)];

        $html = "<h1>{$input['topic']}</h1>";
        $html .= "<p>{$opening} Acara yang dilangsungkan di <strong>{$input['location']}</strong> ini secara spesifik bertujuan untuk {$input['goal']}.</p>";
        $html .= '<h2>Dampak &amp; Manfaat Terkini</h2>';
        $html .= "<p>Menurut data di lapangan, hadirnya kegiatan ini membawa dampak signifikan, terutama: <em>{$input['impact']}</em>. Para peserta menunjukkan antusiasme tinggi.</p>";
        $html .= '<h2>Langkah Selanjutnya</h2>';
        $html .= "<p>Diharapkan bahwa melalui tercapainya {$input['goal']}, {$input['location']} dapat menjadi contoh nyata bagi kawasan atau wilayah lainnya.</p>";

        return $html;
    }
}
