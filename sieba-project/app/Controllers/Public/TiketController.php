<?php

namespace App\Controllers\Public;

use App\Models\TiketModel;
use App\Models\PendaftaranModel;
use CodeIgniter\Controller;

class TiketController extends Controller
{
    protected $tiketModel;
    protected $pendaftaranModel;

    public function __construct()
    {
        $this->tiketModel = new TiketModel();
        $this->pendaftaranModel = new PendaftaranModel();
        helper(['form', 'url']);
    }

    /**
     * Show ticket verification form
     */
    public function index()
    {
        $data = [
            'title' => 'Cek Tiket - SIEBA',
            'validation' => \Config\Services::validation()
        ];

        return view('public/cek_tiket', $data);
    }

    /**
     * Process ticket verification
     */
    public function cek()
    {
        $rules = [
            'kode' => 'required|min_length[5]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $kode = $this->request->getPost('kode');
        
        // Try to find by ticket code first
        $tiket = $this->tiketModel->getTiketByKode($kode);
        
        if ($tiket) {
            return redirect()->to('/tiket/' . $kode);
        }

        // If not found, try registration code
        $pendaftaran = $this->pendaftaranModel->getByKode($kode);
        
        if ($pendaftaran) {
            return redirect()->to('/hasil-daftar/' . $kode);
        }

        return redirect()->back()->withInput()->with('error', 'Kode tiket atau pendaftaran tidak ditemukan');
    }

    /**
     * Show ticket details
     */
    public function lihat($kode)
    {
        $tiket = $this->tiketModel->getTiketByKode($kode);

        if (!$tiket) {
            return redirect()->to('/cek-tiket')->with('error', 'Tiket tidak ditemukan');
        }

        $data = [
            'title' => 'Detail Tiket - SIEBA',
            'tiket' => $tiket
        ];

        return view('public/hasil_cek_tiket', $data);
    }

    /**
     * Download ticket PDF
     */
    public function download($kode)
    {
        $tiket = $this->tiketModel->getTiketByKode($kode);

        if (!$tiket || !$tiket['file_tiket']) {
            return redirect()->to('/cek-tiket')->with('error', 'File tiket tidak ditemukan');
        }

        $filePath = WRITEPATH . $tiket['file_tiket'];
        
        if (!file_exists($filePath)) {
            return redirect()->to('/cek-tiket')->with('error', 'File tiket tidak ada');
        }

        return $this->response->download($filePath, null);
    }

    /**
     * Validate ticket via QR scan (AJAX)
     */
    public function validateQR()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $kode = $this->request->getPost('kode');
        
        if (!$kode) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kode tiket tidak valid'
            ]);
        }

        $result = $this->tiketModel->scanTiket($kode);
        
        return $this->response->setJSON($result);
    }

    /**
     * Show QR scanner page (for event organizers)
     */
    public function scanner()
    {
        $data = [
            'title' => 'QR Scanner - SIEBA'
        ];

        return view('public/qr_scanner', $data);
    }

    /**
     * Get ticket info for display (AJAX)
     */
    public function getTicketInfo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $kode = $this->request->getPost('kode');
        $tiket = $this->tiketModel->getTiketByKode($kode);

        if (!$tiket) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tiket tidak ditemukan'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $tiket
        ]);
    }

    /**
     * Print ticket view
     */
    public function print($kode)
    {
        $tiket = $this->tiketModel->getTiketByKode($kode);

        if (!$tiket) {
            return redirect()->to('/cek-tiket')->with('error', 'Tiket tidak ditemukan');
        }

        $data = [
            'title' => 'Print Tiket - ' . $tiket['nama_event'],
            'tiket' => $tiket
        ];

        return view('public/print_tiket', $data);
    }

    /**
     * Check registration status by email (for guests)
     */
    public function cekByEmail()
    {
        $rules = [
            'email' => 'required|valid_email',
            'event_id' => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $email = $this->request->getPost('email');
        $eventId = $this->request->getPost('event_id');

        $pendaftaran = $this->pendaftaranModel->where('event_id', $eventId)
                                            ->where('email_peserta', $email)
                                            ->first();

        if (!$pendaftaran) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pendaftaran tidak ditemukan'
            ]);
        }

        // Get ticket if exists
        $tiket = $this->tiketModel->where('pendaftaran_id', $pendaftaran['id'])->first();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'pendaftaran' => $pendaftaran,
                'tiket' => $tiket
            ]
        ]);
    }

    /**
     * Generate ticket for confirmed registration (for guests)
     */
    public function generateTiket($pendaftaranId)
    {
        $pendaftaran = $this->pendaftaranModel->find($pendaftaranId);

        if (!$pendaftaran || $pendaftaran['status'] !== 'confirmed') {
            return redirect()->to('/cek-tiket')->with('error', 'Pendaftaran belum dikonfirmasi');
        }

        // Check if ticket already exists
        $existingTiket = $this->tiketModel->where('pendaftaran_id', $pendaftaranId)->first();
        
        if ($existingTiket) {
            return redirect()->to('/tiket/' . $existingTiket['kode_tiket']);
        }

        // Generate new ticket
        $tiket = $this->tiketModel->generateTiket($pendaftaranId);

        if ($tiket) {
            return redirect()->to('/tiket/' . $tiket['kode_tiket'])
                           ->with('success', 'Tiket berhasil digenerate!');
        } else {
            return redirect()->to('/cek-tiket')->with('error', 'Gagal generate tiket');
        }
    }
}