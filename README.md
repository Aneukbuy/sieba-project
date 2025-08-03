# SIEBA - Sistem Informasi Event Terbuka Banda Aceh

## Deskripsi

SIEBA (Sistem Informasi Event Terbuka Banda Aceh) adalah platform web modern yang dirancang khusus untuk mempermudah masyarakat Banda Aceh dalam mencari, mengelola, dan berpartisipasi dalam berbagai event yang diselenggarakan di kota ini.

Platform ini menyediakan informasi lengkap tentang event-event terbuka di Banda Aceh, mulai dari seminar, workshop, konser, turnamen olahraga, festival budaya, hingga event teknologi.

## Fitur Utama

### 🎯 **Pencarian & Filter Event**
- Pencarian berdasarkan nama, lokasi, atau deskripsi event
- Filter berdasarkan kategori (Seminar, Workshop, Konser, Olahraga, Budaya, Teknologi)
- Filter berdasarkan waktu (Hari ini, Minggu ini, Bulan ini)
- Filter berdasarkan status event (Akan Datang, Sedang Berlangsung, Selesai)

### 📝 **Manajemen Event**
- Tambah event baru dengan mudah
- Informasi detail event (nama, kategori, tanggal, waktu, lokasi, deskripsi, harga)
- Kontak penyelenggara untuk setiap event
- Status event yang terupdate otomatis

### 🎨 **User Interface Modern**
- Design responsif untuk semua perangkat
- Animasi dan transisi yang smooth
- Interface yang user-friendly dan intuitif
- Dark mode support

### 🔄 **Fitur Interaktif**
- Modal untuk detail event
- Sistem notifikasi real-time
- Share event ke media sosial atau copy link
- Load more events dengan pagination

### 📱 **Mobile Responsive**
- Tampilan optimal di smartphone dan tablet
- Menu hamburger untuk navigasi mobile
- Touch-friendly interface

## Teknologi yang Digunakan

- **HTML5** - Struktur website modern dan semantik
- **CSS3** - Styling dengan Flexbox, Grid, dan CSS Custom Properties
- **JavaScript (Vanilla)** - Interaktivitas tanpa framework eksternal
- **Font Awesome** - Icon library untuk UI elements
- **Google Fonts (Poppins)** - Typography yang modern dan readable

## Struktur File

```
sieba-project/
├── index.html          # Halaman utama website
├── styles.css          # File CSS untuk styling
├── script.js           # File JavaScript untuk functionality
└── README.md           # Dokumentasi project
```

## Cara Menjalankan

1. **Clone repository**
   ```bash
   git clone <repository-url>
   cd sieba-project
   ```

2. **Buka di browser**
   - Buka file `index.html` langsung di browser
   - Atau gunakan live server untuk development

3. **Untuk development dengan Live Server (VSCode)**
   ```bash
   # Install Live Server extension di VSCode
   # Klik kanan pada index.html > "Open with Live Server"
   ```

## Cara Menggunakan

### 1. Mencari Event
- Gunakan search bar di bagian atas untuk mencari event berdasarkan keyword
- Pilih filter kategori, waktu, atau status sesuai kebutuhan
- Klik pada card event untuk melihat detail lengkap

### 2. Menambah Event Baru
- Klik tombol "Tambah Event" di hero section
- Isi form dengan informasi event yang lengkap
- Klik "Simpan Event" untuk menambahkan ke daftar

### 3. Melihat Detail Event
- Klik pada card event manapun
- Modal akan terbuka dengan informasi detail
- Gunakan tombol "Bagikan" untuk share event

### 4. Filter dan Kategori
- Gunakan dropdown filter untuk menyaring event
- Klik pada kategori di section "Kategori Event"
- Kombinasikan beberapa filter untuk hasil yang lebih spesifik

## Sample Data

Website sudah dilengkapi dengan 6 sample event untuk demonstrasi:

1. **Workshop Digital Marketing untuk UMKM** - Workshop gratis
2. **Seminar Kewirausahaan Muda** - Seminar berbayar
3. **Festival Budaya Aceh 2024** - Event budaya gratis
4. **Turnamen Futsal Ramadhan Cup** - Event olahraga
5. **Konser Musik Tradisional** - Event hiburan
6. **Hackathon Smart City Banda Aceh** - Event teknologi

## Customization

### Menambah Kategori Baru
Edit array `categories` di `script.js` dan tambahkan option baru di HTML:

```javascript
const categories = {
    'seminar': 'Seminar',
    'workshop': 'Workshop',
    'konser': 'Konser',
    'olahraga': 'Olahraga',
    'budaya': 'Budaya',
    'teknologi': 'Teknologi',
    'kategori-baru': 'Kategori Baru'  // Tambah di sini
};
```

### Mengubah Tema Warna
Edit CSS custom properties di `styles.css`:

```css
:root {
    --primary-color: #3b82f6;
    --secondary-color: #1d4ed8;
    --success-color: #16a34a;
    --background-color: #f8fafc;
}
```

### Mengubah Logo
Ganti URL placeholder di HTML dengan logo sebenarnya:

```html
<img src="path/to/your/logo.png" alt="SIEBA Logo">
```

## Browser Support

- Chrome (Modern versions)
- Firefox (Modern versions)
- Safari (Modern versions)
- Edge (Modern versions)

## Performance

- Vanilla JavaScript untuk performa optimal
- CSS optimized dengan minimal reflow
- Lazy loading untuk images
- Debounced search untuk efisiensi

## SEO Ready

- Semantic HTML structure
- Meta tags yang optimized
- Open Graph support
- Structured data ready

## Security

- Input validation untuk form data
- XSS protection dengan proper escaping
- No external scripts dari CDN yang tidak terpercaya

## Future Enhancements

Beberapa fitur yang bisa ditambahkan di masa depan:

- [ ] Backend integration dengan database
- [ ] User authentication dan authorization
- [ ] Email notifications untuk event reminder
- [ ] Payment gateway integration
- [ ] Admin dashboard untuk manajemen event
- [ ] Push notifications
- [ ] Export event ke calendar
- [ ] Review dan rating system
- [ ] Multi-language support
- [ ] API endpoints untuk mobile app

## Contributing

1. Fork project ini
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## License

Project ini menggunakan MIT License. Lihat file `LICENSE` untuk detail lebih lanjut.

## Contact

**SIEBA Development Team**
- Email: info@sieba-bandaaceh.id
- Website: [SIEBA Banda Aceh](https://sieba-bandaaceh.id)
- WhatsApp: +62 651 7551234

## Acknowledgments

- Font Awesome untuk icon library
- Google Fonts untuk typography
- Placeholder.com untuk sample images
- Komunitas developer Banda Aceh untuk feedback dan dukungan

---

**Dibuat dengan ❤️ untuk masyarakat Banda Aceh**