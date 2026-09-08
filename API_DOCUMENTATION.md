# Dokumentasi API - QRIS & Device Bypass (QRIS Bos Pengering)

Dokumen ini menyediakan rincian teknis mengenai endpoint API untuk **Pengecekan Status Device**, **Request Pembayaran QRIS**, **Bypass Status**, serta **Pengecekan Status Pembayaran (Polling)**.

## 🌐 Base URL
Semua request API dikirimkan ke alamat dasar berikut:
```text
https://qris.laundrytapkartu.com
```

---

## 📌 Ringkasan Endpoint

| No | Endpoint | Method | Fungsi Utama | Controller Method |
|----|----------|--------|--------------|-------------------|
| 1  | `GET /api/check-device` | `GET` | IoT mengecek status & mengaktifkan bypass device | `DeviceController@checkDeviceStatus` |
| 2  | `POST /api/devices/{device}/update-status` | `POST` | Memperbarui status / mengaktifkan bypass device secara manual | `DeviceController@toggleStatus` |
| 3  | `POST /api/qr-request` | `POST` | Melakukan request pembuatan QRIS untuk pembayaran mesin | `QrisController@qr_request` |
| 4  | `GET /api/payment-check` | `GET` | Mengecek status pembayaran berdasarkan `order_id` (Polling manual) | `QrisController@checkPaymentStatus` |
| 5  | `GET /api/payment-check-2` | `GET` | Mengecek status pembayaran berdasarkan mesin/device (Polling IoT) | `QrisController@checkPaymentStatus2` |
| 6  | `POST /api/payment-status-update` | `POST` | Menerima callback/webhook status pembayaran dari payment gateway | `QrisWebhookController@auto` |

---

## 🔌 Detail Endpoint

### 1. Pengecekan Status Device (IoT Polling)

Digunakan oleh perangkat IoT untuk mengecek apakah ada perintah aktivasi bypass atau sesi aktif untuk mesin tersebut dalam 24 jam terakhir. Jika ada, IoT akan menerimanya dan sistem akan langsung mengonsumsi (menghapus/me-nonaktifkan) status bypass tersebut agar tidak digunakan berulang kali.

* **URL Lengkap:** `https://qris.laundrytapkartu.com/api/check-device`
* **Method:** `GET`
* **Parameter Query:**

| Nama Parameter | Tipe | Status | Deskripsi |
| :--- | :--- | :--- | :--- |
| `device_code` | `string` | **Wajib (Required)** | Kode unik dari mesin/device (contoh: `DEV-01`). |
| `service_type` | `string` | Opsional | Jenis layanan yang dicari (contoh: `washer`, `dryer`). Secara otomatis dikonversi ke format *lower_snake_case*. |

#### 📝 Catatan Alur & Logika (Bypass Logic):
1. **Validasi Awal:**
   - Jika parameter `device_code` tidak ada, mengembalikan status **400 Bad Request**.
   - Jika mesin tidak ditemukan di database, mengembalikan status **404 Not Found**.
2. **Pengecekan Perpanjangan Outlet:**
   - Jika perpanjangan QRIS outlet sudah jatuh tempo (`has_overdue_billing` bernilai true), respons langsung dibatasi dengan status `off`, source `'qris_billing'`, dan pesan `'Masa aktif QRIS habis. Silakan perpanjang.'` dengan HTTP Status **200**.
3. **Pencarian Sumber Aktivasi (Valid dalam 24 Jam Terakhir):**
   Sistem mencari data aktivasi bypass aktif dari 3 sumber berikut:
   * **Sumber `bypass` (Device Direct Bypass):** Status device di database tidak `'off'`, tipe layanan cocok, dan kolom `bypass_activation` diset kurang dari 24 jam yang lalu. Catatan berasal dari `bypass_note`.
   * **Sumber `session` (Drop-off Sesi Aktif):** Sesi drop-off aktif (`DeviceTransaction`) dengan `bypass_activation` tidak null, status `true`, dan dalam kurun waktu 24 jam terakhir. Catatannya adalah `'Pesanan Drop-off'`.
   * **Sumber `qris_bypass` (Bypass dari QRIS):** Data transaksi QRIS (`QrisTransactionDetail`) dengan `bypass_status` bernilai `'active'`, `bypass_activation` tidak null, dan dalam kurun waktu 24 jam terakhir. Catatannya adalah `'Bypass QRIS'`.
4. **Prioritas & Konsumsi Satu Kali Pakai (One-time Consumption):**
   - Jika terdapat lebih dari satu sumber aktivasi, sistem akan mengambil yang **terbaru** berdasarkan timestamp `bypass_activation`.
   - Data aktivasi tersebut akan dicatat ke dalam tabel `bypass_records`.
   - **Penting:** Status aktivasi langsung dikonsumsi (dimatikan) agar tidak bisa digunakan lagi pada request berikutnya:
     - Jika sumbernya `bypass`: Mengubah status device (`device_status`) kembali menjadi `'off'`.
     - Jika sumbernya `session`: Mengubah status transaksi (`status`) menjadi `false`.
     - Jika sumbernya `qris_bypass`: Mengubah status bypass QRIS (`bypass_status`) menjadi `'activated'`.

#### 📥 Contoh Response:

##### A. Respons Berhasil - Ada Aktivasi Bypass Terdeteksi (HTTP 200)
```json
{
  "status": "success",
  "status_device": "washer",
  "source": "qris_bypass",
  "activation_date": "2026-06-19 14:30:00",
  "message": "Status diterima"
}
```

##### B. Respons Berhasil - Tidak Ada Aktivasi (Mesin Tetap Off) (HTTP 200)
```json
{
  "status": "success",
  "status_device": "off",
  "source": null,
  "activation_date": null,
  "message": "Status diterima"
}
```

##### C. Respons Gagal - Parameter Tidak Lengkap (HTTP 400)
```json
{
  "status": "failure",
  "status_device": "off",
  "message": "Parameter device_code harus disertakan.",
  "activation_date": null,
  "source": null
}
```

---

### 2. Update Status & Bypass Device Manual

Digunakan untuk memaksa/mengubah status mesin serta mengisi data bypass manual langsung ke mesin tertentu dari panel admin atau sistem kasir.

* **URL Lengkap:** `https://qris.laundrytapkartu.com/api/devices/{device}/update-status`
* **Method:** `POST`
* **Parameter URL:**
  * `{device}`: ID primary key dari device di database (misal: `1`).
* **Parameter Body (JSON atau Form Data):**

| Nama Parameter | Tipe | Status | Deskripsi |
| :--- | :--- | :--- | :--- |
| `device_status` | `string` | **Wajib** | Status baru mesin (contoh: `'washer'`, `'dryer'`, `'off'`). |
| `bypass_note` | `string` | **Wajib** | Alasan/catatan dilakukannya bypass (contoh: `'Bypass manual uji coba mesin'`). |

#### 📝 Catatan Alur & Logika:
1. **Pengecekan Perpanjangan Outlet:** Jika perpanjangan QRIS outlet pemilik mesin sudah jatuh tempo, request akan ditolak dan mengembalikan `'status' => 'error'` dengan pesan `'Masa aktif QRIS habis. Silakan perpanjang.'`.
2. Jika lolos, status device akan diperbarui, kolom `bypass_activation` akan diisi waktu sekarang (`Carbon::now()`), dan `bypass_note` akan diisi sesuai inputan.

#### 📥 Contoh Response (HTTP 200):
```json
{
  "status": "success",
  "message": "Device DEV-01 berhasil diperbarui menjadi \"washer\""
}
```

---

### 3. Request Pembayaran QRIS

Endpoint ini digunakan untuk mengajukan transaksi baru melalui pembayaran digital QRIS. Sistem akan memvalidasi status outlet dan limitasi akun pembayaran sebelum memanggil API Payment Gateway (Midtrans, Midtrans Partner, Xendit, dll.) untuk mendapatkan string QR, lalu menyatukannya dengan template gambar QRIS dan mengembalikan URL gambarnya.

* **URL Lengkap:** `https://qris.laundrytapkartu.com/api/qr-request`
* **Method:** `POST`
* **Parameter Body (JSON atau Form Data):**

| Nama Parameter | Tipe | Status | Deskripsi |
| :--- | :--- | :--- | :--- |
| `amount` | `integer` | **Wajib** | Nominal pembayaran. Minimal harus senilai **10** rupiah (`MINIMUM_QRIS_AMOUNT`). |
| `type` | `string` | **Wajib** | Jenis layanan mesin (contoh: `'washer'`, `'dryer'`). |
| `device_code` | `string` | **Wajib** | Kode mesin yang akan dijalankan setelah pembayaran sukses. |
| `provider` | `string` | Opsional | Payment Gateway yang dituju. Jika tidak diisi, menggunakan default dari konfigurasi sistem. |

#### 📝 Catatan Alur & Logika:
1. **Validasi Parameter:** Memastikan `amount >= 10`, `type` tidak kosong, dan `device_code` tidak kosong.
2. **Validasi Outlet:**
   - Outlet harus terdaftar dan berstatus aktif (tidak tutup/status != 0).
   - Outlet tidak boleh memiliki perpanjangan QRIS yang sudah jatuh tempo (`has_overdue_billing`).
3. **Logika Akun Privat (Private vs General Merchant Account):**
   - Khusus provider **Midtrans**: Jika Owner outlet mengatur tipe akun pembayaran mereka (`payment_account_type`) ke `'owner'` (Private Account), maka sistem otomatis mengalihkan provider ke `'midtrans_partner'`.
   - Transaksi akan diproses menggunakan data privat owner tersebut (`merchant_id` yang tersimpan di profil owner) serta credential partner dari konfigurasi aplikasi.
4. **Pembuatan Gambar QRIS Custom:**
   - Setelah API Gateway mengembalikan string QR (`qr_string`), sistem memproyeksikannya ke atas template gambar background (`assets/img/qristempl.jpg`).
   - Sistem juga mencetak nominal uang terformat (misal: `Rp 15.000`) di bagian atas QR dan nama outlet di bagian bawah gambar secara dinamis sebelum menyimpannya ke storage publik (`/storage/qrcodes/`).
5. **Pencatatan Database:** Membuat record baru pada tabel `transactions` (status: `pending`) dan tabel detail transaksi `qris_transaction_details`.

#### 📥 Contoh Response Berhasil (HTTP 200):
```json
{
  "status": "success",
  "message": {
    "order_id": "WASHER-OUT01-1689000000123ABCD",
    "transaction_id": "MID-REF-99887766",
    "payment_status": "pending",
    "provider": "midtrans_partner",
    "api_url": "https://api.sandbox.midtrans.com/v2/qris/...",
    "qr_image": "https://qris.laundrytapkartu.com/storage/qrcodes/WASHER-OUT01-1689000000123ABCD.jpg",
    "expiresAt": "2026-06-19T14:45:00+07:00"
  }
}
```

---

### 4. Cek Status Pembayaran (Berdasarkan Order ID)

Digunakan oleh frontend atau aplikasi klien untuk melakukan polling (pengecekan status berkala) guna mengetahui apakah pembayaran QRIS dengan kode order tertentu sudah berhasil terbayar atau sudah di-bypass oleh admin.

* **URL Lengkap:** `https://qris.laundrytapkartu.com/api/payment-check`
* **Method:** `GET`
* **Parameter Query:**

| Nama Parameter | Tipe | Status | Deskripsi |
| :--- | :--- | :--- | :--- |
| `order_id` | `string` | **Wajib** | Kode Order ID transaksi yang ingin dicek. |

#### 📝 Catatan Alur & Logika:
1. **Kriteria Pembayaran Berhasil:** Status transaksi dianggap berhasil jika:
   - Kolom status transaksi bernilai `'success'`.
   - ATAU kolom status transaksi bernilai `'pending'`, tetapi di-bypass secara manual oleh admin (`qrisTransaction->bypass_status === 'active'`).
2. **Tindakan Saat Berhasil (Post-Success Actions):**
   - File gambar QR Code di storage publik akan langsung **dihapus** (`deleteQRCode()`) demi efisiensi ruang penyimpanan disk.
   - Sesi aktivasi mesin (`DeviceTransaction` terkait) akan diset statusnya menjadi `false` (artinya telah dikonsumsi) dan `activated_at` diisi waktu sekarang.
   - Jika pembayaran sukses karena jalur bypass admin, status bypass tersebut diubah dari `'active'` menjadi `'activated'`.

#### 📥 Contoh Response:

##### A. Respons Pembayaran Berhasil (HTTP 200)
```json
{
  "status": "success",
  "message": {
    "order_id": "WASHER-OUT01-1689000000123ABCD",
    "payment_status": "success",
    "device_status": true,
    "qr_code_deleted": true,
    "description": "Pembayaran Berhasil."
  }
}
```

##### B. Respons Pembayaran Belum Berhasil/Pending (HTTP 200)
```json
{
  "status": "success",
  "message": {
    "order_id": "WASHER-OUT01-1689000000123ABCD",
    "payment_status": "pending",
    "qr_code_deleted": false,
    "description": "Pembayaran tidak berhasil."
  }
}
```

---

### 5. Cek Status Pembayaran 2 (Berdasarkan Kode Mesin/Device)

Endpoint polling alternatif yang berpusat pada mesin (device-centric). Sangat berguna bagi perangkat IoT yang tidak menyimpan data `order_id`, melainkan hanya memonitor apakah ada pembayaran sukses atau bypass aktif terbaru yang belum dikonsumsi untuk dirinya sendiri dalam 1 jam terakhir.

* **URL Lengkap:** `https://qris.laundrytapkartu.com/api/payment-check-2`
* **Method:** `GET`
* **Parameter Query:**

| Nama Parameter | Tipe | Status | Deskripsi |
| :--- | :--- | :--- | :--- |
| `service_type` | `string` | **Wajib** | Tipe layanan mesin yang dicek (contoh: `'washer'`, `'dryer'`). |
| `device_code` | `string` | **Wajib** | Kode unik mesin yang dicek. |

#### 📝 Catatan Alur & Logika:
1. **Filter Pencarian Transaksi:**
   - Transaksi harus bertipe `'qris'`.
   - Status transaksi bernilai `'success'` ATAU (`'pending'` dengan status bypass QRIS aktif/`'active'`).
   - Memiliki `DeviceTransaction` yang sesuai dengan `service_type` dan `device_code` yang dicari, serta statusnya masih bernilai `true` (belum dikonsumsi).
   - Transaksi tersebut dibuat maksimal 1 jam yang lalu (`created_at >= Carbon::now()->subHour()`) ATAU memiliki bypass QRIS yang aktif.
   - Transaksi diurutkan dari yang paling lama/dulu dibuat (`created_at` ASC) untuk memastikan antrean diproses berurutan.
2. **Tindakan Saat Berhasil (Post-Success Actions):**
   - Menghapus gambar QR Code terkait dari storage.
   - Mengubah status `DeviceTransaction` menjadi `false` dan mengisi kolom `activated_at`.
   - Mengubah status bypass QRIS dari `'active'` menjadi `'activated'`.

#### 📥 Contoh Response:

##### A. Respons Pembayaran Berhasil & Siap Dijalankan (HTTP 200)
```json
{
  "status": "success",
  "message": {
    "order_id": "WASHER-OUT01-1689000000123ABCD",
    "payment_status": "success",
    "device_status": true,
    "amount": 15000,
    "description": "Pembayaran Berhasil."
  }
}
```

##### B. Respons Transaksi Tidak Ditemukan/Belum Lunas (HTTP 200)
```json
{
  "status": "error",
  "message": {
    "order_id": null,
    "description": "Order not found."
  }
}
```

---

### 6. Callback Status Pembayaran (Webhook Payment Gateway)

Endpoint ini digunakan oleh payment gateway untuk mengirimkan perubahan status pembayaran QRIS ke server. Sistem akan mendeteksi provider secara otomatis dari transaksi tersimpan atau dari bentuk payload webhook.

* **URL Lengkap:** `https://qris.laundrytapkartu.com/api/payment-status-update`
* **Method:** `POST`
* **Content-Type:** `application/json`
* **CSRF:** Dikecualikan dari validasi CSRF agar bisa dipanggil langsung oleh payment gateway.

#### Provider yang Didukung

| Provider | Cara Deteksi |
| :--- | :--- |
| `midtrans` | Payload memiliki `transaction_status` atau `signature_key`, atau transaksi tersimpan memiliki provider `midtrans`. |
| `midtrans_partner` | Transaksi tersimpan memiliki provider `midtrans_partner`. Bentuk payload mirip Midtrans. |
| `xendit` | Payload memiliki `qr_code`, `reference_id`, atau `data.reference_id`, atau transaksi tersimpan memiliki provider `xendit`. |

#### Field Penting Payload

| Nama Field | Tipe | Deskripsi |
| :--- | :--- | :--- |
| `order_id` | `string` | ID pesanan transaksi. Untuk beberapa gateway, sistem juga membaca dari `qr_code.external_id`, `qr_code.reference_id`, `reference_id`, atau `data.reference_id`. |
| `transaction_status` | `string` | Status transaksi dari Midtrans atau Midtrans Partner. |
| `signature_key` | `string` | Signature Midtrans untuk validasi keamanan webhook. |
| `qr_code` / `data` | `object` | Bentuk payload Xendit QRIS yang berisi reference transaksi dan status pembayaran. |

#### Alur Proses

1. Sistem membaca payload JSON dan mencoba mengambil `order_id`.
2. Jika transaksi ditemukan, provider dipakai dari data `qrisTransaction->payment_provider`.
3. Jika transaksi belum ditemukan atau provider kosong, sistem menebak provider dari bentuk payload.
4. Payload diparse melalui service gateway sesuai provider.
5. Transaksi difinalisasi berdasarkan `order_id`, status pembayaran, nominal, gateway reference, dan metadata.

#### Contoh Response

##### A. Callback Berhasil Diproses (HTTP 200)
```json
{
  "status": "success",
  "message": "Transaction updated successfully"
}
```

##### B. Transaksi Tidak Ditemukan, Callback Diabaikan (HTTP 200)
```json
{
  "status": "success",
  "message": "Transaction not found, callback ignored"
}
```

##### C. Provider Tidak Bisa Dideteksi (HTTP 400)
```json
{
  "status": "error",
  "message": "Payment provider could not be resolved."
}
```

##### D. Signature/Payload Tidak Valid (HTTP 403 atau 400)
```json
{
  "status": "error",
  "message": "Invalid signature"
}
```
