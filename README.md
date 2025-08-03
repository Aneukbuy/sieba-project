# Sieba Project

## 🚀 Modern Web Application with Bootstrap 5

Sebuah website modern dan responsif yang dibangun menggunakan Bootstrap 5, HTML5, CSS3, dan JavaScript vanilla. Project ini menampilkan desain yang bersih, animasi yang halus, dan user experience yang optimal.

## ✨ Features

### 🎨 Design & UI/UX
- **Responsive Design** - Tampil sempurna di semua device (desktop, tablet, mobile)
- **Modern UI** - Interface yang bersih dan modern menggunakan Bootstrap 5
- **Smooth Animations** - Animasi yang halus dan engaging
- **Interactive Elements** - Hover effects, card animations, dan parallax scrolling
- **Dark Mode Support** - Otomatis menyesuaikan dengan preferensi sistem

### 🛠️ Technical Features
- **Bootstrap 5.3.2** - Framework CSS terbaru
- **Bootstrap Icons** - Icon set yang lengkap dan modern
- **Vanilla JavaScript** - Performa optimal tanpa dependency yang berlebihan
- **CSS Custom Properties** - Variabel CSS untuk konsistensi theming
- **Intersection Observer API** - Scroll animations yang performant
- **Form Validation** - Real-time validation dengan feedback visual
- **PWA Ready** - Struktur dasar untuk Progressive Web App

### 📱 Sections
1. **Hero Section** - Landing page dengan call-to-action yang menarik
2. **About Section** - Penjelasan tentang visi, misi, dan nilai-nilai
3. **Services Section** - Layanan yang ditawarkan dengan detail
4. **Contact Section** - Form kontak dengan validasi dan notifikasi

## 🏗️ Project Structure

```
sieba-project/
├── index.html              # Main HTML file
├── README.md               # Project documentation
├── assets/
│   ├── css/
│   │   └── style.css       # Custom CSS styles
│   ├── js/
│   │   └── script.js       # JavaScript functionality
│   ├── images/             # Image assets
│   └── fonts/              # Custom fonts (if any)
└── .git/                   # Git repository
```

## 🚀 Quick Start

### Prerequisites
- Web browser modern (Chrome, Firefox, Safari, Edge)
- Text editor (VS Code, Sublime Text, dll.)
- Git (untuk version control)

### Installation

1. **Clone repository**
   ```bash
   git clone https://github.com/username/sieba-project.git
   cd sieba-project
   ```

2. **Buka project**
   - Buka `index.html` di browser, atau
   - Gunakan live server untuk development

3. **Development Server (Recommended)**
   ```bash
   # Jika menggunakan VS Code dengan Live Server extension
   # Klik kanan pada index.html -> "Open with Live Server"
   
   # Atau menggunakan Python (jika terinstall)
   python -m http.server 8000
   
   # Atau menggunakan Node.js (jika terinstall)
   npx serve .
   ```

4. **Akses website**
   - Buka browser dan kunjungi: `http://localhost:8000`

## 🛠️ Development

### Struktur CSS
File CSS menggunakan metodologi yang terorganisir:
- **CSS Variables** untuk konsistensi warna dan spacing
- **Mobile-first approach** untuk responsive design
- **BEM-like naming** untuk class yang semantik
- **Performance optimizations** untuk loading yang cepat

### JavaScript Features
- **ES6+ Modern JavaScript** dengan arrow functions, const/let, template literals
- **Modular approach** dengan functions yang terpisah untuk setiap feature
- **Event delegation** untuk performa yang optimal
- **Intersection Observer** untuk scroll animations
- **Form validation** dengan regex dan real-time feedback

### Customization

#### Mengubah Warna Theme
Edit variabel CSS di `assets/css/style.css`:
```css
:root {
    --primary-color: #0d6efd;    /* Warna utama */
    --secondary-color: #6c757d;  /* Warna sekunder */
    --success-color: #198754;    /* Warna sukses */
    /* dst... */
}
```

#### Menambah Section Baru
1. Tambahkan HTML structure di `index.html`
2. Tambahkan styling di `assets/css/style.css`
3. Tambahkan JavaScript interactions di `assets/js/script.js`

#### Mengubah Content
- **Hero Section**: Edit text di `.hero-section`
- **About Section**: Modifikasi cards di `#about`
- **Services**: Update layanan di `#services`
- **Contact Info**: Ubah informasi kontak di `#contact`

## 📦 Dependencies

### External CDN
- **Bootstrap 5.3.2**: CSS Framework
- **Bootstrap Icons 1.11.1**: Icon library

### Browser Support
- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+

## 🎯 Performance Features

- **Optimized Images**: Gunakan format WebP dan lazy loading
- **Minified CSS/JS**: Untuk production, gunakan versi minified
- **CDN Usage**: Bootstrap dan icons dari CDN untuk caching optimal
- **Debounced/Throttled Events**: Scroll events dioptimasi untuk performa
- **Intersection Observer**: Efficient scroll-based animations

## 🔧 Configuration

### Form Setup
Untuk menghubungkan form dengan backend:

1. **PHP Backend**:
   ```php
   // contact.php
   if ($_POST) {
       $name = $_POST['firstName'] . ' ' . $_POST['lastName'];
       $email = $_POST['email'];
       $message = $_POST['message'];
       // Process form data
   }
   ```

2. **Node.js Backend**:
   ```javascript
   app.post('/contact', (req, res) => {
       const { firstName, lastName, email, message } = req.body;
       // Process form data
   });
   ```

3. **Update JavaScript**: Ubah form submission di `assets/js/script.js`

## 🚀 Deployment

### Hosting Statis
- **Netlify**: Drag & drop folder project
- **Vercel**: Connect dengan Git repository
- **GitHub Pages**: Aktifkan di repository settings

### Traditional Hosting
1. Upload semua files ke public_html folder
2. Pastikan `index.html` ada di root directory
3. Test semua links dan functionalities

## 🤝 Contributing

1. Fork repository
2. Create feature branch: `git checkout -b feature/AmazingFeature`
3. Commit changes: `git commit -m 'Add some AmazingFeature'`
4. Push to branch: `git push origin feature/AmazingFeature`
5. Open Pull Request

## 📝 License

Distributed under the MIT License. See `LICENSE` file for more information.

## 👨‍💻 Author

**Sieba Project Team**
- Website: [siebaproject.com](https://siebaproject.com)
- Email: info@siebaproject.com
- Instagram: [@siebaproject](https://instagram.com/siebaproject)

## 🙏 Acknowledgments

- [Bootstrap Team](https://getbootstrap.com/) untuk framework yang amazing
- [Bootstrap Icons](https://icons.getbootstrap.com/) untuk icon library
- [MDN Web Docs](https://developer.mozilla.org/) untuk dokumentasi web
- [Can I Use](https://caniuse.com/) untuk browser compatibility reference

---

**⭐ Jika project ini membantu, jangan lupa kasih star ya! ⭐**