<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5>
                    <i class="fas fa-calendar-alt me-2"></i>
                    SIEBA
                </h5>
                <p class="text-muted">
                    Sistem Event dan Berbagi - Platform terpercaya untuk mengelola dan mendaftar event.
                </p>
                <div class="social-links">
                    <a href="#" class="text-light me-3">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" class="text-light me-3">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-light me-3">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-light">
                        <i class="fab fa-linkedin"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-md-2 mb-3">
                <h6>Menu</h6>
                <ul class="list-unstyled">
                    <li><a href="<?= base_url('/') ?>" class="text-muted">Beranda</a></li>
                    <li><a href="<?= base_url('/telusuri') ?>" class="text-muted">Telusuri Event</a></li>
                    <li><a href="<?= base_url('/cek-tiket') ?>" class="text-muted">Cek Tiket</a></li>
                    <li><a href="<?= base_url('/about') ?>" class="text-muted">Tentang</a></li>
                </ul>
            </div>
            
            <div class="col-md-2 mb-3">
                <h6>Bantuan</h6>
                <ul class="list-unstyled">
                    <li><a href="<?= base_url('/faq') ?>" class="text-muted">FAQ</a></li>
                    <li><a href="<?= base_url('/contact') ?>" class="text-muted">Kontak</a></li>
                    <li><a href="#" class="text-muted">Panduan</a></li>
                    <li><a href="#" class="text-muted">Kebijakan Privasi</a></li>
                </ul>
            </div>
            
            <div class="col-md-4 mb-3">
                <h6>Kontak Kami</h6>
                <div class="contact-info">
                    <p class="text-muted mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        info@sieba.com
                    </p>
                    <p class="text-muted mb-2">
                        <i class="fas fa-phone me-2"></i>
                        +62 123 456 7890
                    </p>
                    <p class="text-muted mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Jakarta, Indonesia
                    </p>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted mb-0">
                    &copy; <?= date('Y') ?> SIEBA. Semua hak cipta dilindungi.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-muted mb-0">
                    <i class="fas fa-code me-1"></i>
                    Dibuat dengan <i class="fas fa-heart text-danger"></i> menggunakan CodeIgniter 4
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" class="btn btn-primary position-fixed" style="bottom: 20px; right: 20px; display: none; z-index: 1000;">
    <i class="fas fa-arrow-up"></i>
</button>

<style>
.social-links a {
    transition: color 0.3s ease;
}

.social-links a:hover {
    color: #007bff !important;
}

footer a {
    text-decoration: none;
    transition: color 0.3s ease;
}

footer a:hover {
    color: #007bff !important;
}

#backToTop {
    border-radius: 50%;
    width: 50px;
    height: 50px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

#backToTop:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
</style>

<script>
// Back to top functionality
$(document).ready(function() {
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('#backToTop').fadeIn();
        } else {
            $('#backToTop').fadeOut();
        }
    });
    
    $('#backToTop').click(function() {
        $('html, body').animate({scrollTop: 0}, 800);
        return false;
    });
});
</script>