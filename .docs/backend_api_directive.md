📰 Direktif Penulisan Konten Berita (SEO & Production Ready)

M-One Solution – Content & Editorial Guideline

Dokumen ini menjadi acuan dalam menulis, mengelola, dan menyajikan konten berita/artikel pada sistem M-One Solution agar:

SEO optimal (Google-friendly)
Konsisten secara struktur
Ringan secara performa
Siap integrasi dengan frontend & API
1. Standar Struktur Konten Berita

Setiap artikel WAJIB memiliki struktur berikut:

🔹 Struktur Utama
{
  "title": "Judul artikel",
  "slug": "url-friendly-title",
  "excerpt": "Ringkasan singkat artikel",
  "content": "Isi lengkap artikel (HTML/Markdown)",
  "meta_title": "Judul SEO",
  "meta_description": "Deskripsi SEO",
  "category": {
    "name": "Kategori",
    "slug": "kategori-slug"
  },
  "author": "Nama penulis",
  "cover_url": "Gambar utama",
  "published_at": "Tanggal publish"
}
2. Aturan Penulisan Konten (Editorial Rules)
2.1 Judul (Title)
Maksimal 60 karakter
Mengandung keyword utama
Bersifat click-worthy tapi tetap informatif

Contoh:

❌ "Berita Sekolah"
✅ "SMK Muhammadiyah 1 Sukoharjo Gelar Workshop Web Development 2026"
2.2 Slug (URL SEO)
Gunakan huruf kecil
Pisahkan dengan tanda -
Hindari stop words berlebihan

Contoh:

workshop-web-development-smk-muhammadiyah
2.3 Excerpt (Ringkasan)
120–160 karakter
Harus menjawab:
Apa yang terjadi?
Siapa yang terlibat?
Kenapa penting?
2.4 Content (Isi Artikel)

Gunakan struktur piramida terbalik (inverted pyramid):

✅ Format Ideal:
H1: Judul

Paragraf pembuka (lead)
→ Ringkasan inti berita

H2: Latar Belakang
H2: Detail Kegiatan / Kejadian
H2: Kutipan / Insight (opsional)
H2: Dampak / Penutup
✅ Aturan Penulisan:
Paragraf pendek (3–4 baris)
Gunakan bahasa formal tapi ringan
Hindari pengulangan kata
Sisipkan keyword secara natural
2.5 Meta SEO
Meta Title
Maksimal 60 karakter
Lebih tajam dari title utama
Meta Description
Maksimal 155 karakter
Mengandung CTA atau nilai berita
2.6 Gambar (Cover & Thumbnail)
Gunakan URL absolut
Resolusi optimal (min 1200px width)
Wajib relevan dengan isi berita
3. Kategori Konten

Gunakan kategori yang jelas dan konsisten:

berita-sekolah
teknologi
workshop
pengumuman
event
prestasi
4. Optimasi SEO (WAJIB)

Checklist sebelum publish:

 Keyword utama ada di judul
 Keyword muncul di 100 kata pertama
 Ada minimal 1 subheading (H2)
 Ada internal link (jika memungkinkan)
 Meta title & description terisi
 URL slug clean
5. Performa & Best Practice
⚡ Payload Optimization
Endpoint /posts → TANPA content
Endpoint /posts/{slug} → FULL content

👉 Tujuan:

List artikel tetap ringan
Detail artikel tetap lengkap
⚡ Konsistensi Data
Gunakan format tanggal ISO:
2026-03-25T00:00:00.000Z
⚡ Handling Data Kosong
Gunakan:
"data": []
Jangan gunakan null
6. Standar Kualitas Konten

Konten dianggap layak publish jika:

Informatif (bukan filler)
Relevan dengan audience
Tidak duplikat (anti copy-paste)
Memiliki value (edukasi / insight / informasi baru)
7. Contoh Artikel Ideal (Singkat)
# SMK Muhammadiyah 1 Sukoharjo Gelar Workshop Web Development

SMK Muhammadiyah 1 Sukoharjo mengadakan workshop web development untuk meningkatkan kompetensi siswa di bidang teknologi.

## Latar Belakang
Kegiatan ini bertujuan...

## Jalannya Workshop
Workshop diikuti oleh...

## Dampak Kegiatan
Dengan adanya kegiatan ini...
8. Insight Penting (Level Strategis)

Ini yang sering dilewatkan:

👉 Backend hanya menyimpan data — SEO ditentukan oleh kualitas konten

Artinya:

API bagus ≠ ranking bagus
Konten berkualitas + struktur benar = SEO naik