# Implementation Plan: API for M-One Solution

## Goal
Tujuan utama adalah merancang dan mengimplementasikan Backend API yang _production-ready_ dan _SEO-ready_ untuk website M-One Solution (`https://mone.mutudev.com/`). API ini akan menggantikan penggunaan _mock data_ pada frontend saat ini. Implementasi mencakup endpoint standar untuk mengambil data (Posts, Services, Projects, Team, dsb) serta endpoint khusus untuk **Article Generator** berbasis SEO.

## User Review Required
Mohon periksa rencana arsitektur dan spesifikasi endpoint di bawah ini. Apakah ada tambahan untuk model data, atau ada penyesuaian khusus untuk fitur Article Generator sebelum pengembangan dimulai?

## Proposed Changes

### 1. Database & Models
Pembuatan atau penyesuaian _migration_ dan _model_ pada backend (Laravel/PHP):
- **Settings:** Mengembalikan struktur data objek tunggal (bukan array) berisi konfigurasi dan kontak.
- **Posts:** Menghasilkan data dengan field `id`, `title`, `slug`, `excerpt`, `content` (hanya di response `/posts/{slug}`), `meta_title`, `meta_description`, objek relasi **`category`** (`id`, `name`, `slug`), `author`, `cover_url`, `cover_thumb`, dan `published_at`.

### 2. Article Generator Feature (SEO-Ready)
Berdasarkan [seo_ready.md](file:///d:/GitHub/berita-mone/.docs/seo_ready.md), fitur ini akan membaca input (topic, location, goal, dll) dan menghasilkan artikel yang SEO friendly.
- **Service (`ArticleGeneratorService`):** Logic untuk _generate_ title, slug, excerpt, content, meta_title, dan meta_description. Service ini menggunakan `articles/template.blade.php`.
- **Endpoint API (`POST /api/generate-article`):** Menerima payload JSON untuk men-generate dan menyimpan artikel ke database, lalu mengembalikan data artikel yang dibuat.

### 3. Core Frontend API Endpoints (Read-Only)
Sesuai dengan [backend_api_directive.md](file:///d:/GitHub/berita-mone/.docs/backend_api_directive.md), base URL untuk API ini adalah `https://berita-mone.mutudev.com/`, dan semua respon API akan dibungkus dalam format standar:
```json
{
  "success": true,
  "data": { ... }
}
```
**Daftar Endpoint:**
- `GET /api/settings`
- `GET /api/posts` (Mendukung parameter `?limit=N`. `content` list tidak disertakan untuk menjaga payload tetap ringan)
- `GET /api/posts/{slug}`

*Catatan penting:*
- CORS harus dikonfigurasi agar frontend (`https://mone.mutudev.com/`) dapat mengakses endpoint API pada server `https://berita-mone.mutudev.com/`.
- Semua URL aset gambar (URL dan thumb) menggunakan _absolute URL_ (misal: `https://berita-mone.mutudev.com/storage/xyz.jpg`).

## Verification Plan
### Automated Tests
- Menulis Unit Test untuk `ArticleGeneratorService` untuk memastikan title, slug, excerpt, meta tags digenerate dengan benar, sesuai input parameter.
- Menulis Feature Test untuk setiap Endpoint API pada `https://berita-mone.mutudev.com/api/*` (atau `/posts`, `/services`, dll.) yang memverifikasi JSON structure response dan HTTP Status Codes (200 OK, 404 Not Found).

### Manual Verification
- Tim backend dan frontend (atau Agent) dapat menguji semua endpoint menggunakan Postman mencocokkan payload akhir dengan spesifikasi dalam dokumentasi ini.
- Mengonfigurasi _environment variable_ Frontend (`VITE_API_URL` atau setara) dengan nilai API Url `https://berita-mone.mutudev.com/` dan memastikan setiap halaman (Home, Blog, Services, Portfolio) berfungsi normal tanpa _mock data_.
