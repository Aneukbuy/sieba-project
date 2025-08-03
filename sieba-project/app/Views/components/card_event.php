<div class="card card-event h-100">
    <div class="position-relative">
        <?php if (!empty($event['poster_url'])): ?>
            <img src="<?= base_url($event['poster_url']) ?>" class="card-img-top" alt="<?= esc($event['nama_event']) ?>">
        <?php else: ?>
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                <i class="fas fa-calendar-alt fa-3x text-muted"></i>
            </div>
        <?php endif; ?>
        
        <!-- Category Badge -->
        <span class="badge bg-primary position-absolute">
            <?= ucfirst($event['kategori']) ?>
        </span>
        
        <!-- Price Badge -->
        <?php if ($event['biaya'] > 0): ?>
            <span class="badge bg-warning text-dark position-absolute" style="top: 10px; left: 10px;">
                Rp <?= number_format($event['biaya'], 0, ',', '.') ?>
            </span>
        <?php else: ?>
            <span class="badge bg-success position-absolute" style="top: 10px; left: 10px;">
                GRATIS
            </span>
        <?php endif; ?>
    </div>
    
    <div class="card-body d-flex flex-column">
        <h5 class="card-title mb-2">
            <a href="<?= base_url('/event/' . $event['id']) ?>" class="text-decoration-none text-dark">
                <?= esc($event['nama_event']) ?>
            </a>
        </h5>
        
        <p class="card-text text-muted mb-3 flex-grow-1">
            <?= esc(substr($event['deskripsi'], 0, 100)) ?>
            <?= strlen($event['deskripsi']) > 100 ? '...' : '' ?>
        </p>
        
        <div class="event-info mb-3">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-calendar text-primary me-2"></i>
                <small class="text-muted">
                    <?= date('d M Y', strtotime($event['tanggal_mulai'])) ?>
                    <?php if ($event['tanggal_mulai'] !== $event['tanggal_selesai']): ?>
                        - <?= date('d M Y', strtotime($event['tanggal_selesai'])) ?>
                    <?php endif; ?>
                </small>
            </div>
            
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-clock text-primary me-2"></i>
                <small class="text-muted">
                    <?= date('H:i', strtotime($event['waktu_mulai'])) ?> - 
                    <?= date('H:i', strtotime($event['waktu_selesai'])) ?>
                </small>
            </div>
            
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                <small class="text-muted">
                    <?= esc($event['lokasi']) ?>
                </small>
            </div>
            
            <div class="d-flex align-items-center">
                <i class="fas fa-users text-primary me-2"></i>
                <small class="text-muted">
                    <?php 
                    $jumlah_peserta = $event['jumlah_peserta'] ?? 0;
                    $max_peserta = $event['max_peserta'];
                    ?>
                    <?= $jumlah_peserta ?> / <?= $max_peserta ?> peserta
                </small>
                
                <!-- Progress bar for capacity -->
                <div class="ms-2 flex-grow-1">
                    <div class="progress" style="height: 4px;">
                        <?php $percentage = $max_peserta > 0 ? ($jumlah_peserta / $max_peserta) * 100 : 0; ?>
                        <div class="progress-bar 
                            <?= $percentage < 50 ? 'bg-success' : ($percentage < 80 ? 'bg-warning' : 'bg-danger') ?>" 
                            style="width: <?= $percentage ?>%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-auto">
            <?php 
            $canRegister = $event['dapat_daftar'] ?? true;
            $eventDate = strtotime($event['tanggal_mulai']);
            $today = time();
            ?>
            
            <?php if ($eventDate < $today): ?>
                <span class="btn btn-secondary w-100 disabled">
                    <i class="fas fa-check-circle me-2"></i>
                    Event Selesai
                </span>
            <?php elseif (!$canRegister): ?>
                <span class="btn btn-warning w-100 disabled">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Kuota Penuh
                </span>
            <?php else: ?>
                <a href="<?= base_url('/event/' . $event['id']) ?>" class="btn btn-primary w-100">
                    <i class="fas fa-eye me-2"></i>
                    Lihat Detail
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>