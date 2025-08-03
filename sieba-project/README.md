# SIEBA - Sistem Event dan Berbagi

SIEBA adalah platform manajemen event yang komprehensif yang dibangun menggunakan CodeIgniter 4. Sistem ini memungkinkan pengguna untuk mendaftar dan mengelola event, mulai dari pendaftaran hingga penerbitan sertifikat digital.

## 🚀 Fitur Utama

### Untuk Peserta (User)
- **Pendaftaran Event**: Daftar event dengan mudah melalui interface yang intuitif
- **Dashboard Personal**: Lihat event yang sudah diikuti dan status pendaftaran
- **Tiket Digital**: Dapatkan tiket digital dengan QR code untuk check-in
- **Sertifikat Digital**: Download sertifikat setelah menyelesaikan event
- **Riwayat Event**: Tracking lengkap semua event yang pernah diikuti

### Untuk Tamu (Non-Login)
- **Browse Event**: Telusuri event yang tersedia tanpa perlu login
- **Detail Event**: Lihat informasi lengkap event
- **Pendaftaran Tamu**: Daftar event tanpa perlu membuat akun
- **Cek Tiket**: Verifikasi tiket menggunakan kode pendaftaran

### Untuk Admin
- **Manajemen Event**: CRUD lengkap untuk pengelolaan event
- **Manajemen Peserta**: Kelola data peserta dan konfirmasi pendaftaran
- **Dashboard Admin**: Statistik dan overview sistem
- **Laporan**: Generate laporan event dan peserta
- **Scanning Tiket**: Scan QR code tiket untuk check-in peserta

## 🏗️ Teknologi & Arsitektur

### Backend
- **Framework**: CodeIgniter 4
- **PHP Version**: 8.0+
- **Database**: MySQL/MariaDB
- **Authentication**: Session-based dengan role management
- **File Upload**: Support untuk poster event dan bukti pembayaran

### Frontend
- **CSS Framework**: Bootstrap 5
- **Icons**: Font Awesome 6
- **JavaScript**: jQuery + Vanilla JS
- **Responsive Design**: Mobile-first approach

### Fitur Teknis
- **QR Code Generation**: Untuk tiket digital
- **PDF Generation**: Untuk sertifikat dan tiket
- **Email System**: Notifikasi dan pengiriman dokumen
- **Image Processing**: Upload dan resize poster event
- **Security**: CSRF protection, input validation, XSS prevention

## 📁 Struktur Direktori

```
sieba-project/
│
├── app/
│   ├── Config/
│   │   └── Routes.php                 ← Konfigurasi routing
│   │
│   ├── Controllers/
│   │   ├── Public/
│   │   │   ├── Home.php              ← Halaman beranda & telusuri event
│   │   │   ├── EventController.php   ← Detail & pendaftaran event (tamu)
│   │   │   └── TiketController.php   ← Cek tiket tanpa login
│   │   │
│   │   ├── User/
│   │   │   ├── DashboardController.php ← Dashboard user login
│   │   │   ├── TiketController.php    ← Cetak tiket
│   │   │   └── EventController.php    ← Pendaftaran event untuk user
│   │   │
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── EventController.php    ← CRUD event
│   │   │   ├── PesertaController.php  ← Data peserta
│   │   │   └── LaporanController.php  ← Statistik & laporan
│   │   │
│   │   └── AuthController.php         ← Login, register, logout
│   │
│   ├── Models/
│   │   ├── EventModel.php
│   │   ├── UserModel.php
│   │   ├── PendaftaranModel.php
│   │   ├── TiketModel.php
│   │   └── SertifikatModel.php
│   │
│   ├── Views/
│   │   ├── layouts/                   ← Template layouts
│   │   ├── components/                ← Reusable components
│   │   ├── public/                    ← Public pages
│   │   ├── user/                      ← User dashboard pages
│   │   └── admin/                     ← Admin panel pages
│   │
│   ├── Filters/
│   │   ├── AuthFilter.php             ← Autentikasi middleware
│   │   └── AdminFilter.php            ← Admin authorization
│   │
│   └── Services/
│       ├── QRCodeService.php          ← QR code generation
│       ├── EmailService.php           ← Email functionality
│       └── SertifikatService.php      ← Certificate generation
│
├── public/
│   ├── assets/
│   │   ├── css/                       ← Stylesheets
│   │   ├── js/                        ← JavaScript files
│   │   └── images/                    ← Static images
│   └── index.php                      ← Entry point
│
└── writable/
    ├── uploads/                       ← File uploads
    ├── logs/                          ← Application logs
    └── cache/                         ← Cache files
```

## 🚀 Instalasi & Setup

### Persyaratan Sistem
- PHP 8.0 atau lebih tinggi
- MySQL 5.7+ atau MariaDB 10.3+
- Web server (Apache/Nginx)
- Composer
- Extension PHP: intl, mbstring, curl, gd

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd sieba-project
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Setup Environment**
   ```bash
   cp env .env
   ```
   Edit file `.env` sesuai konfigurasi server Anda:
   ```env
   # Database
   database.default.hostname = localhost
   database.default.database = sieba_db
   database.default.username = your_username
   database.default.password = your_password
   
   # App
   app.baseURL = 'http://localhost:8080/'
   app.sessionDriver = 'CodeIgniter\Session\Handlers\FileHandler'
   app.sessionSavePath = WRITEPATH . 'session'
   ```

4. **Setup Database**
   ```sql
   CREATE DATABASE sieba_db;
   ```
   Jalankan migration (akan dibuat terpisah):
   ```bash
   php spark migrate
   php spark db:seed
   ```

5. **Set Permissions**
   ```bash
   chmod -R 755 writable/
   ```

6. **Start Development Server**
   ```bash
   php spark serve
   ```
   Aplikasi akan tersedia di `http://localhost:8080`

## 📊 Database Schema

### Tabel Users
```sql
- id (int, primary key)
- nama (varchar)
- email (varchar, unique)
- password (varchar, hashed)
- no_hp (varchar)
- institusi (varchar)
- jabatan (varchar)
- alamat (text)
- tanggal_lahir (date)
- jenis_kelamin (enum: L, P)
- role (enum: user, admin)
- is_verified (boolean)
- verification_token (varchar)
- avatar (varchar)
- reset_token (varchar)
- reset_expires (datetime)
- created_at (timestamp)
- updated_at (timestamp)
```

### Tabel Events
```sql
- id (int, primary key)
- nama_event (varchar)
- deskripsi (text)
- tanggal_mulai (date)
- tanggal_selesai (date)
- waktu_mulai (time)
- waktu_selesai (time)
- lokasi (varchar)
- alamat_lokasi (text)
- kategori (enum: seminar, workshop, webinar, training, conference)
- max_peserta (int)
- biaya (decimal)
- poster_url (varchar)
- status (enum: draft, published, cancelled, completed)
- persyaratan (text)
- benefit (text)
- contact_person (varchar)
- created_by (int, foreign key)
- created_at (timestamp)
- updated_at (timestamp)
```

### Tabel Pendaftaran
```sql
- id (int, primary key)
- event_id (int, foreign key)
- user_id (int, foreign key, nullable for guests)
- nama_peserta (varchar)
- email_peserta (varchar)
- no_hp_peserta (varchar)
- institusi_peserta (varchar)
- status (enum: pending, confirmed, cancelled, completed)
- kode_pendaftaran (varchar, unique)
- bukti_pembayaran (varchar)
- catatan (text)
- tanggal_daftar (datetime)
- tanggal_hadir (datetime)
- created_at (timestamp)
- updated_at (timestamp)
```

### Tabel Tiket
```sql
- id (int, primary key)
- pendaftaran_id (int, foreign key)
- kode_tiket (varchar, unique)
- qr_code (varchar)
- file_tiket (varchar)
- status (enum: active, used, expired, cancelled)
- tanggal_generate (datetime)
- tanggal_scan (datetime)
- created_at (timestamp)
- updated_at (timestamp)
```

### Tabel Sertifikat
```sql
- id (int, primary key)
- pendaftaran_id (int, foreign key)
- nomor_sertifikat (varchar, unique)
- file_sertifikat (varchar)
- tanggal_generate (datetime)
- status (enum: generated, downloaded, sent)
- created_at (timestamp)
- updated_at (timestamp)
```

## 🔐 Sistem Autentikasi & Authorization

### Roles & Permissions

**Admin**
- Akses penuh ke admin panel
- CRUD events
- Kelola peserta
- Generate laporan
- Scan tiket
- Generate sertifikat

**User**
- Daftar event
- Lihat dashboard personal
- Download tiket
- Download sertifikat
- Update profil

**Guest**
- Browse events
- Daftar event tanpa akun
- Cek tiket dengan kode

### Security Features
- Password hashing dengan PHP `password_hash()`
- CSRF protection
- Input validation & sanitization
- XSS prevention
- SQL injection protection (melalui CodeIgniter Query Builder)
- Session management
- Rate limiting (dapat ditambahkan)

## 📱 Fitur Mobile

- Responsive design untuk semua ukuran layar
- Progressive Web App ready
- Touch-friendly interface
- Mobile-first CSS approach
- Optimized untuk scanning QR code di mobile

## 🔧 Kustomisasi & Pengembangan

### Menambah Event Category Baru
1. Update enum di database
2. Tambahkan di model `EventModel.php`
3. Update form input di views
4. Tambahkan styling jika diperlukan

### Menambah Field User Baru
1. Alter table users
2. Update `UserModel.php` di `$allowedFields`
3. Update form registrasi dan profil
4. Update validation rules

### Custom Email Templates
1. Buat template di `app/Views/emails/`
2. Update `EmailService.php`
3. Konfigurasi SMTP di `.env`

## 📈 Monitoring & Maintenance

### Log Files
- Application logs: `writable/logs/`
- Error tracking via CodeIgniter logging
- Custom event logging dapat ditambahkan

### Performance Optimization
- Database indexing pada foreign keys
- Image compression untuk poster
- CSS/JS minification (production)
- Database query optimization
- Caching untuk data statis

### Backup Strategy
- Database backup rutin
- File upload backup
- Configuration backup

## 🤝 Kontribusi

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

### Coding Standards
- PSR-4 autoloading
- PSR-12 coding style
- CodeIgniter 4 best practices
- Meaningful variable names
- Comprehensive comments

## 📄 License

Project ini menggunakan [MIT License](LICENSE).

## 📞 Support

Untuk bantuan dan pertanyaan:
- Email: support@sieba.com
- Documentation: [Wiki](wiki-url)
- Issues: [GitHub Issues](issues-url)

---

**SIEBA** - Memudahkan pengelolaan event untuk semua orang! 🎉