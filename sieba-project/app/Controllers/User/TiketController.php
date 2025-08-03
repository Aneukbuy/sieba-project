<?php

namespace App\Controllers\User;

use App\Models\TiketModel;
use App\Models\PendaftaranModel;
use App\Models\SertifikatModel;
use CodeIgniter\Controller;

class TiketController extends Controller
{
    protected $tiketModel;
    protected $pendaftaranModel;
    protected $sertifikatModel;
    protected $session;

    public function __construct()
    {
        $this->tiketModel = new TiketModel();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->sertifikatModel = new SertifikatModel();
        $this->session = session();
        helper(['form', 'url']);
    }

    /**
     * Show user tickets
     */
    public function index()
    {
        $userId = $this->session->get('user_id');
        $tickets = $this->tiketModel->getUserTiket($userId);

        $data = [
            'title' => 'Tiket Saya - SIEBA',
            'tickets' => $tickets
        ];

        return view('user/tiket', $data);
    }

    /**
     * Show ticket detail
     */
    public function detail($id)
    {
        $userId = $this->session->get('user_id');
        
        $tiket = $this->tiketModel->select('tiket.*, pendaftaran.nama_peserta, pendaftaran.email_peserta, 
                                          pendaftaran.kode_pendaftaran, events.nama_event, events.tanggal_mulai, 
                                          events.tanggal_selesai, events.waktu_mulai, events.waktu_selesai, 
                                          events.lokasi, events.alamat_lokasi')
                                ->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                                ->join('events', 'events.id = pendaftaran.event_id')
                                ->where('tiket.id', $id)
                                ->where('pendaftaran.user_id', $userId)
                                ->first();

        if (!$tiket) {
            return redirect()->to('/user/tiket')->with('error', 'Tiket tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Tiket - SIEBA',
            'tiket' => $tiket,
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => base_url('/user/dashboard')],
                ['title' => 'Tiket Saya', 'url' => base_url('/user/tiket')],
                ['title' => 'Detail Tiket']
            ]
        ];

        return view('user/detail_tiket', $data);
    }

    /**
     * Print/Download ticket
     */
    public function cetak($id)
    {
        $userId = $this->session->get('user_id');
        
        $tiket = $this->tiketModel->select('tiket.*, pendaftaran.nama_peserta, pendaftaran.email_peserta, 
                                          pendaftaran.kode_pendaftaran, events.nama_event, events.tanggal_mulai, 
                                          events.tanggal_selesai, events.waktu_mulai, events.waktu_selesai, 
                                          events.lokasi, events.alamat_lokasi, events.poster_url')
                                ->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                                ->join('events', 'events.id = pendaftaran.event_id')
                                ->where('tiket.id', $id)
                                ->where('pendaftaran.user_id', $userId)
                                ->first();

        if (!$tiket) {
            return redirect()->to('/user/tiket')->with('error', 'Tiket tidak ditemukan');
        }

        $data = [
            'title' => 'Cetak Tiket - ' . $tiket['nama_event'],
            'tiket' => $tiket
        ];

        return view('user/cetak_tiket', $data);
    }

    /**
     * Download ticket PDF
     */
    public function download($id)
    {
        $userId = $this->session->get('user_id');
        
        $tiket = $this->tiketModel->select('tiket.*, pendaftaran.user_id')
                                ->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                                ->where('tiket.id', $id)
                                ->where('pendaftaran.user_id', $userId)
                                ->first();

        if (!$tiket || !$tiket['file_tiket']) {
            return redirect()->to('/user/tiket')->with('error', 'File tiket tidak ditemukan');
        }

        $filePath = WRITEPATH . $tiket['file_tiket'];
        
        if (!file_exists($filePath)) {
            return redirect()->to('/user/tiket')->with('error', 'File tiket tidak ada');
        }

        return $this->response->download($filePath, null);
    }

    /**
     * Generate ticket if not exists
     */
    public function generate($pendaftaranId)
    {
        $userId = $this->session->get('user_id');
        
        $pendaftaran = $this->pendaftaranModel->where('id', $pendaftaranId)
                                            ->where('user_id', $userId)
                                            ->first();

        if (!$pendaftaran) {
            return redirect()->to('/user/tiket')->with('error', 'Pendaftaran tidak ditemukan');
        }

        if ($pendaftaran['status'] !== 'confirmed') {
            return redirect()->to('/user/tiket')->with('error', 'Pendaftaran belum dikonfirmasi');
        }

        // Check if ticket already exists
        $existingTiket = $this->tiketModel->where('pendaftaran_id', $pendaftaranId)->first();
        
        if ($existingTiket) {
            return redirect()->to('/user/tiket/' . $existingTiket['id'])
                           ->with('info', 'Tiket sudah tersedia');
        }

        // Generate new ticket
        $tiket = $this->tiketModel->generateTiket($pendaftaranId);

        if ($tiket) {
            return redirect()->to('/user/tiket/' . $tiket['id'])
                           ->with('success', 'Tiket berhasil digenerate!');
        } else {
            return redirect()->to('/user/tiket')->with('error', 'Gagal generate tiket');
        }
    }

    /**
     * Show QR code for ticket
     */
    public function qrcode($id)
    {
        $userId = $this->session->get('user_id');
        
        $tiket = $this->tiketModel->select('tiket.*, pendaftaran.nama_peserta, events.nama_event')
                                ->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                                ->join('events', 'events.id = pendaftaran.event_id')
                                ->where('tiket.id', $id)
                                ->where('pendaftaran.user_id', $userId)
                                ->first();

        if (!$tiket) {
            return redirect()->to('/user/tiket')->with('error', 'Tiket tidak ditemukan');
        }

        $data = [
            'title' => 'QR Code Tiket - SIEBA',
            'tiket' => $tiket
        ];

        return view('user/qr_tiket', $data);
    }

    /**
     * Validate ticket status
     */
    public function validate($kode)
    {
        $userId = $this->session->get('user_id');
        
        $tiket = $this->tiketModel->select('tiket.*, pendaftaran.user_id, pendaftaran.nama_peserta, events.nama_event')
                                ->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                                ->join('events', 'events.id = pendaftaran.event_id')
                                ->where('tiket.kode_tiket', $kode)
                                ->where('pendaftaran.user_id', $userId)
                                ->first();

        if (!$tiket) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $tiket,
            'status' => $tiket['status'],
            'can_use' => $tiket['status'] === 'active'
        ]);
    }

    /**
     * Show certificates page
     */
    public function sertifikat()
    {
        $userId = $this->session->get('user_id');
        $certificates = $this->sertifikatModel->getUserSertifikat($userId);

        $data = [
            'title' => 'Sertifikat Saya - SIEBA',
            'certificates' => $certificates
        ];

        return view('user/sertifikat', $data);
    }

    /**
     * Download certificate
     */
    public function downloadSertifikat($id)
    {
        $userId = $this->session->get('user_id');
        
        $sertifikat = $this->sertifikatModel->select('sertifikat.*, pendaftaran.user_id')
                                          ->join('pendaftaran', 'pendaftaran.id = sertifikat.pendaftaran_id')
                                          ->where('sertifikat.id', $id)
                                          ->where('pendaftaran.user_id', $userId)
                                          ->first();

        if (!$sertifikat || !$sertifikat['file_sertifikat']) {
            return redirect()->to('/user/sertifikat')->with('error', 'File sertifikat tidak ditemukan');
        }

        $filePath = WRITEPATH . $sertifikat['file_sertifikat'];
        
        if (!file_exists($filePath)) {
            return redirect()->to('/user/sertifikat')->with('error', 'File sertifikat tidak ada');
        }

        // Mark as downloaded
        $this->sertifikatModel->markDownloaded($id);

        return $this->response->download($filePath, null);
    }

    /**
     * Generate certificate for completed event
     */
    public function generateSertifikat($pendaftaranId)
    {
        $userId = $this->session->get('user_id');
        
        $pendaftaran = $this->pendaftaranModel->where('id', $pendaftaranId)
                                            ->where('user_id', $userId)
                                            ->first();

        if (!$pendaftaran) {
            return redirect()->to('/user/sertifikat')->with('error', 'Pendaftaran tidak ditemukan');
        }

        if ($pendaftaran['status'] !== 'completed') {
            return redirect()->to('/user/sertifikat')->with('error', 'Event belum selesai diikuti');
        }

        // Check if certificate already exists
        $existingSertifikat = $this->sertifikatModel->where('pendaftaran_id', $pendaftaranId)->first();
        
        if ($existingSertifikat) {
            return redirect()->to('/user/sertifikat')
                           ->with('info', 'Sertifikat sudah tersedia');
        }

        // Generate new certificate
        $sertifikat = $this->sertifikatModel->generateSertifikat($pendaftaranId);

        if ($sertifikat) {
            return redirect()->to('/user/sertifikat')
                           ->with('success', 'Sertifikat berhasil digenerate!');
        } else {
            return redirect()->to('/user/sertifikat')->with('error', 'Gagal generate sertifikat');
        }
    }

    /**
     * Get ticket statistics for user dashboard
     */
    public function getStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $userId = $this->session->get('user_id');
        $stats = $this->tiketModel->getStatistik();

        // Get user-specific stats
        $userTickets = $this->tiketModel->getUserTiket($userId);
        $userStats = [
            'total' => count($userTickets),
            'active' => 0,
            'used' => 0,
            'expired' => 0
        ];

        foreach ($userTickets as $ticket) {
            $userStats[$ticket['status']]++;
        }

        return $this->response->setJSON([
            'user_stats' => $userStats,
            'global_stats' => $stats
        ]);
    }

    /**
     * Share ticket (generate shareable link)
     */
    public function share($id)
    {
        $userId = $this->session->get('user_id');
        
        $tiket = $this->tiketModel->select('tiket.*, pendaftaran.user_id')
                                ->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                                ->where('tiket.id', $id)
                                ->where('pendaftaran.user_id', $userId)
                                ->first();

        if (!$tiket) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ]);
        }

        $shareUrl = base_url('/tiket/' . $tiket['kode_tiket']);

        return $this->response->setJSON([
            'success' => true,
            'share_url' => $shareUrl,
            'qr_url' => base_url('/user/tiket/' . $id . '/qr')
        ]);
    }
}