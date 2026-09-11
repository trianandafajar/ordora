Database (migration, model, enum, seeder) untuk aplikasi **Coffee Shop QR Ordering** berbasis Laravel 13 sudah dibuat, dengan tabel: `users` (role: admin/kasir, `is_active`), `categories`, `products`, `tables` (status: available/occupied, `qr_token`), `orders` (status: pending/preparing/ready/served/paid), `order_items`, `order_status_histories`.

Sekarang tolong buatkan **routing, controller, middleware, dan logic** Laravel 13 untuk 3 alur berikut, sesuai flowchart yang sudah diupload:

### Alur 1 — Autentikasi & Percabangan Role
1. Halaman awal (`/`) menentukan: **Admin/Kasir** → redirect ke halaman login; **Customer** → tidak perlu login sama sekali.
2. `GET /login` → tampilkan form. `POST /login` → validasi email & password.
   - Jika **tidak valid** → kembali ke form dengan pesan error.
   - Jika **valid** → cek `role`:
     - `admin` → redirect ke Dashboard Admin
     - `kasir` → redirect ke Dashboard Kasir
   - Saat login, cek juga `is_active`. Jika kasir `is_active = false`, tolak login dengan pesan "Akun tidak aktif, hubungi Admin".
3. Buat middleware `role:admin` dan `role:kasir` untuk membatasi akses masing-masing dashboard.

### Alur 2 — Customer (tanpa login)
1. `GET /meja/{qr_token}` → validasi `qr_token` ada di tabel `tables`, simpan `table_id` ke session. Tampilkan halaman menu (list produk per kategori, hanya yang `is_available = true`).
2. Customer tambah produk ke keranjang (gunakan **session**, bukan tabel database, agar tidak perlu login).
3. `GET /checkout` → tampilkan ringkasan keranjang + form isi nama (`customer_name`).
4. `POST /checkout`:
   - Buat record `orders` baru: `status = pending`, `order_token` = random unik, `total_price` = hasil hitung dari keranjang.
   - Buat `order_items` dari isi keranjang (snapshot `price` produk saat itu).
   - Insert `order_status_histories` (`status = pending`, `changed_by = null`).
   - Update `tables.status = occupied`.
   - Kosongkan session keranjang.
   - Redirect ke `GET /tracking/{order_token}`.
5. `GET /tracking/{order_token}` → tampilkan status order terkini secara realtime (polling atau broadcasting) sampai status `served`/`paid`.

### Alur 3 — Kasir
1. Dashboard Kasir (`GET /kasir/dashboard`) → tampilkan **daftar order masuk secara realtime** (gunakan Laravel Reverb/Pusher broadcasting, atau minimal polling AJAX tiap beberapa detik) diurutkan dari order terbaru, filter status selain `paid`.
2. Kasir pilih salah satu order (`GET /kasir/order/{id}`) → tampilkan detail order + tombol update status.
3. `PATCH /kasir/order/{id}/status`:
   - Terima status baru: `preparing` → `ready` → `served`.
   - Setiap perubahan: update `orders.status`, insert baris baru ke `order_status_histories` (`changed_by = auth()->id()`).
   - Broadcast event perubahan status agar halaman tracking customer ikut update realtime.
4. Proses pembayaran (`POST /kasir/order/{id}/pay`):
   - Terima `payment_method` (`cash`/`qris`).
   - Update `orders.status = paid`, `orders.payment_method`, `orders.user_id` = kasir yang memproses.
   - Insert `order_status_histories` (`status = paid`).
   - Update `tables.status = available` (meja kembali kosong).
   - Return data untuk cetak/tampilkan struk (bisa view print-friendly atau PDF).

### Alur 4 — Admin
1. Dashboard Admin (`GET /admin/dashboard`) dengan menu ke:
   - **Kelola Kategori** — CRUD `categories` (Tambah/Edit/Hapus)
   - **Kelola Produk** — CRUD `products` (Tambah/Edit/Hapus + upload `image` via `Storage::disk('public')`)
   - **Kelola Meja** — CRUD `tables` (Tambah meja + generate `qr_token` otomatis dengan `Str::random(32)`, tampilkan QR code — gunakan package `simplesoftwareio/simple-qrcode` atau sejenis)
   - **Kelola Akun Kasir** — CRUD `users` role kasir (Tambah/Edit/**Nonaktifkan** — toggle `is_active`, bukan hard delete)
   - **Laporan Penjualan** — laporan order dengan status `paid`, filter per tanggal, total omzet, produk terlaris

### Ketentuan tambahan
1. Gunakan **Form Request** class untuk validasi tiap form (mis. `StoreProductRequest`, `UpdateTableRequest`, dll).
2. Gunakan **Resource Controller** (`Route::resource`) untuk CRUD Admin, dan route custom untuk alur Customer/Kasir yang bukan CRUD murni.
3. Struktur route dikelompokkan dengan prefix & middleware:
   ```php
   Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(...);
   Route::middleware(['auth', 'role:kasir'])->prefix('kasir')->group(...);
   Route::prefix('meja')->group(...); // customer, tanpa auth
   ```
4. Gunakan **Service class** atau **Action class** terpisah untuk logic yang kompleks (mis. `CreateOrderAction`, `UpdateOrderStatusAction`) agar controller tetap tipis.
5. Untuk realtime, jika tidak sempat setup broadcasting, buatkan fallback polling AJAX (`setInterval` fetch tiap 5 detik) sebagai alternatif sederhana — beri catatan mana yang dipakai.
6. Tampilkan hasil dalam bentuk kode lengkap per file, urutan: routes/web.php → middleware → Form Request → Action/Service class → Controller → catatan singkat integrasi view (Blade atau frontend terpisah, sebutkan asumsi jika pakai API-only).

---

## Catatan
- Flow ini diturunkan dari `flow-coffe-shop.pdf` yang sudah diupload, dan disinkronkan dengan perbaikan ERD (`is_active` pada `users`) dari `prompt-setup-database-coffeeshop.md`.
- Bagian realtime (order masuk & tracking status) di flowchart digambar sebagai "secara Realtime" — prompt ini memberi 2 opsi implementasi (broadcasting atau polling) karena flowchart tidak menentukan teknologi spesifik.