<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= base_url('/') ?>">
            <i class="fas fa-calendar-alt me-2"></i>
            SIEBA
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/') ?>">
                        <i class="fas fa-home me-1"></i>Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/telusuri') ?>">
                        <i class="fas fa-search me-1"></i>Telusuri Event
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/cek-tiket') ?>">
                        <i class="fas fa-ticket-alt me-1"></i>Cek Tiket
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <?php if (session()->get('isLoggedIn')): ?>
                    <!-- User is logged in -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?= session()->get('nama') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (session()->get('role') === 'admin'): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('/admin/dashboard') ?>">
                                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php else: ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('/user/dashboard') ?>">
                                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('/user/tiket') ?>">
                                        <i class="fas fa-ticket-alt me-2"></i>Tiket Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('/user/sertifikat') ?>">
                                        <i class="fas fa-certificate me-2"></i>Sertifikat
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" href="<?= base_url('/user/profile') ?>">
                                    <i class="fas fa-user-edit me-2"></i>Edit Profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= base_url('/auth/logout') ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- User is not logged in -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/auth/login') ?>">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light btn-sm ms-2" href="<?= base_url('/auth/register') ?>">
                            <i class="fas fa-user-plus me-1"></i>Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Breadcrumb (Optional) -->
<?php if (isset($breadcrumb) && !empty($breadcrumb)): ?>
<nav class="bg-light py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="<?= base_url('/') ?>">
                    <i class="fas fa-home"></i> Beranda
                </a>
            </li>
            <?php foreach ($breadcrumb as $item): ?>
                <?php if (isset($item['url'])): ?>
                    <li class="breadcrumb-item">
                        <a href="<?= $item['url'] ?>"><?= $item['title'] ?></a>
                    </li>
                <?php else: ?>
                    <li class="breadcrumb-item active"><?= $item['title'] ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>
<?php endif; ?>