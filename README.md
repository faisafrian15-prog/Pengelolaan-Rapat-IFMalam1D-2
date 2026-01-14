# OTP System

## 🚀 Quick Setup

### 1. Clone & Install
```bash
git clone <your-repo>
cd <project-folder>
```

### 2. Set Environment Variables

**Untuk Development (Local):**

Buat file `.htaccess` di root folder:
```apache
SetEnv MAIL_USER "your-email@gmail.com"
SetEnv MAIL_PASS "your-app-password"
```

**ATAU** di `php.ini`:
```ini
[PHP]
variables_order = "EGPCS"
```

Lalu di terminal:
```bash
export MAIL_USER="your-email@gmail.com"
export MAIL_PASS="your-app-password"
php -S localhost:8000
```

**Untuk Production (cPanel/Hosting):**

1. Masuk cPanel
2. Pilih **PHP Variables** atau **Environment Variables**
3. Tambahkan:
   - `MAIL_USER` = your-email@gmail.com
   - `MAIL_PASS` = your-app-password

### 3. Setup Database

Copy dan edit `Koneksi.php`:
```php
<?php
$koneksi = new mysqli("localhost", "user", "pass", "database");
?>
```

### 4. Get Gmail App Password

1. Google Account → Security
2. Enable 2-Factor Authentication
3. Generate **App Password** (pilih Mail)
4. Copy password 16 karakter

### 5. Done! 🎉

## 📋 Cara Kerja

Kode ini menggunakan `getenv()` untuk membaca environment variables:
```php
$mailUser = getenv('MAIL_USER');
$mailPass = getenv('MAIL_PASS');
```

**Keuntungan:**
- ✅ Tidak ada file config tambahan
- ✅ Kredensial tidak pernah ter-commit
- ✅ Mudah deploy ke berbagai environment
- ✅ Standard practice di industri

## ⚠️ PENTING

**JANGAN commit:**
- `Koneksi.php` (berisi kredensial database)
- `.env` (jika pakai file .env nanti)
- `*.log` files

File ini sudah di-protect oleh `.gitignore`

## 🔧 Troubleshooting

**"Konfigurasi email belum lengkap"**
- Environment variables belum di-set
- Cek: `echo getenv('MAIL_USER');`

**Email tidak terkirim**
- Pastikan pakai App Password, bukan password biasa
- Cek Gmail "Less secure app access" di-disable
- Allow access dari IP server Anda