# Images Directory

Folder ini digunakan untuk menyimpan semua aset gambar website.

## Struktur Rekomendasi

```
images/
├── logo/               # Logo dalam berbagai format dan ukuran
│   ├── logo.svg
│   ├── logo.png
│   └── logo-white.png
├── hero/               # Gambar untuk hero section
├── services/           # Gambar untuk section services
├── team/               # Foto team members
├── gallery/            # Galeri foto
└── icons/              # Custom icons (jika tidak menggunakan Bootstrap Icons)
```

## Guidelines

### Format Gambar
- **Logo**: SVG (scalable) atau PNG dengan background transparan
- **Hero Images**: JPG atau WebP untuk foto, PNG untuk illustrations
- **Service Icons**: SVG atau PNG 64x64px minimum
- **Team Photos**: JPG atau WebP, minimal 300x300px

### Optimisasi
1. **Compress images** sebelum upload
2. **Use WebP format** untuk browser modern (dengan fallback JPG)
3. **Responsive images** - sediakan multiple sizes
4. **Lazy loading** - implement di JavaScript

### Naming Convention
```
hero-image.jpg
hero-image@2x.jpg      # For retina displays
service-web-dev.png
team-john-doe.jpg
```

### Tools Rekomendasi
- **TinyPNG**: Untuk kompres PNG/JPG
- **SVGOMG**: Untuk optimasi SVG
- **ImageOptim**: Tool desktop untuk Mac
- **Squoosh**: Web-based image optimizer

## Placeholder Images

Untuk development, bisa gunakan:
- [Unsplash](https://unsplash.com/) - Free high-quality photos
- [Pexels](https://pexels.com/) - Free stock photos
- [Lorem Picsum](https://picsum.photos/) - Placeholder service
- [UI Faces](https://uifaces.co/) - Profile pictures