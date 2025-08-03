<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content text-center">
            <h1 class="display-4 fw-bold mb-4">
                Selamat Datang di SIEBA
            </h1>
            <p class="lead mb-4">
                Sistem Event dan Berbagi - Platform terpercaya untuk mengelola dan mendaftar event
            </p>
            <div class="hero-buttons">
                <a href="<?= base_url('/telusuri') ?>" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-search me-2"></i>
                    Telusuri Event
                </a>
                <?php if (!session()->get('isLoggedIn')): ?>
                    <a href="<?= base_url('/auth/register') ?>" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>
                        Daftar Sekarang
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Quick Search -->
<section class="container">
    <div class="search-form">
        <form action="<?= base_url('/telusuri') ?>" method="GET" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Cari Event</label>
                <input type="text" name="search" class="form-control" placeholder="Nama event atau lokasi...">
            </div>
            <div class="col-md-4">
                <label class="form-label">Kategori</label>
                <select name="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="seminar">Seminar</option>
                    <option value="workshop">Workshop</option>
                    <option value="webinar">Webinar</option>
                    <option value="training">Training</option>
                    <option value="conference">Conference</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>
                    Cari
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Statistics -->
<section class="container my-5">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
                <div class="stat-label">Total Event</div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?= $stats['upcoming'] ?? 0 ?></div>
                <div class="stat-label">Event Mendatang</div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-play"></i>
                </div>
                <div class="stat-number"><?= $stats['ongoing'] ?? 0 ?></div>
                <div class="stat-label">Event Berlangsung</div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number" id="totalParticipants">0</div>
                <div class="stat-label">Total Peserta</div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Events -->
<section class="container my-5">
    <div class="row">
        <div class="col-12">
            <h2 class="text-center mb-5">
                <i class="fas fa-star text-warning me-2"></i>
                Event Terbaru
            </h2>
        </div>
    </div>
    
    <?php if (!empty($featured_events)): ?>
        <div class="row">
            <?php foreach ($featured_events as $event): ?>
                <div class="col-md-4 mb-4">
                    <?= view('components/card_event', ['event' => $event]) ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?= base_url('/telusuri') ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-th-large me-2"></i>
                Lihat Semua Event
            </a>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">Belum ada event tersedia</h4>
            <p class="text-muted">Event baru akan segera hadir. Stay tuned!</p>
        </div>
    <?php endif; ?>
</section>

<!-- Features Section -->
<section class="bg-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2 class="text-center mb-5">Mengapa Memilih SIEBA?</h2>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h5>Terpercaya & Aman</h5>
                    <p class="text-muted">
                        Platform yang aman dengan sistem keamanan berlapis untuk melindungi data Anda.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-mobile-alt fa-3x text-primary"></i>
                    </div>
                    <h5>Mudah Digunakan</h5>
                    <p class="text-muted">
                        Interface yang intuitif dan responsif, dapat diakses dari berbagai perangkat.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-qrcode fa-3x text-primary"></i>
                    </div>
                    <h5>Tiket Digital</h5>
                    <p class="text-muted">
                        Tiket digital dengan QR Code untuk kemudahan check-in dan verifikasi kehadiran.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Load participant statistics
    $.get('<?= base_url('/api/stats') ?>', function(data) {
        if (data.total_participants) {
            $('#totalParticipants').text(data.total_participants);
        }
    });
    
    // Add animation to stat cards
    $('.stat-card').each(function(index) {
        $(this).delay(100 * index).fadeIn();
    });
    
    // Search form enhancements
    $('input[name="search"]').on('input', function() {
        // Could add autocomplete here
    });
});
</script>
<?= $this->endSection() ?>