<?php

namespace App\Controllers\User;

use App\Models\EventModel;
use App\Models\PendaftaranModel;
use App\Models\TiketModel;
use CodeIgniter\Controller;

class EventController extends Controller
{
    protected $eventModel;
    protected $pendaftaranModel;
    protected $tiketModel;
    protected $session;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->tiketModel = new TiketModel();
        $this->session = session();
        helper(['form', 'url']);
    }

    /**
     * Show event registration form for logged in users
     */
    public function daftar($id)
    {
        $userId = $this->session->get('user_id');
        $event = $this->eventModel->getEventDetail($id);

        if (!$event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Check if can register
        if (!$event['dapat_daftar']) {
            return redirect()->to('/event/' . $id)->with('error', 'Pendaftaran sudah ditutup atau kuota penuh');
        }

        // Check if user already registered
        if (!$this->pendaftaranModel->canUserRegister($id, $userId)) {
            return redirect()->to('/event/' . $id)->with('warning', 'Anda sudah terdaftar untuk event ini');
        }

        $data = [
            'title' => 'Daftar ' . $event['nama_event'] . ' - SIEBA',
            'event' => $event,
            'validation' => \Config\Services::validation(),
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => base_url('/user/dashboard')],
                ['title' => $event['nama_event'], 'url' => base_url('/event/' . $id)],
                ['title' => 'Pendaftaran']
            ]
        ];

        return view('user/daftar_event_user', $data);
    }

    /**
     * Process user registration
     */
    public function prosesDaftar($id)
    {
        $userId = $this->session->get('user_id');
        $event = $this->eventModel->getEventDetail($id);

        if (!$event || !$event['dapat_daftar']) {
            return redirect()->to('/event/' . $id)->with('error', 'Tidak dapat mendaftar event ini');
        }

        // Check if user already registered
        if (!$this->pendaftaranModel->canUserRegister($id, $userId)) {
            return redirect()->to('/event/' . $id)->with('warning', 'Anda sudah terdaftar untuk event ini');
        }

        $rules = [
            'nama_peserta' => 'permit_empty|min_length[3]|max_length[100]',
            'email_peserta' => 'permit_empty|valid_email',
            'no_hp_peserta' => 'permit_empty|numeric|min_length[10]|max_length[15]',
            'institusi_peserta' => 'permit_empty|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Prepare data (use user data if not provided)
        $data = [
            'nama_peserta' => $this->request->getPost('nama_peserta'),
            'email_peserta' => $this->request->getPost('email_peserta'),
            'no_hp_peserta' => $this->request->getPost('no_hp_peserta'),
            'institusi_peserta' => $this->request->getPost('institusi_peserta')
        ];

        $pendaftaranId = $this->pendaftaranModel->daftarUser($id, $userId, $data);

        if ($pendaftaranId) {
            // Generate ticket automatically for confirmed registration
            $tiket = $this->tiketModel->generateTiket($pendaftaranId);
            
            $message = 'Pendaftaran berhasil!';
            if ($tiket) {
                $message .= ' Tiket Anda telah digenerate.';
            }

            return redirect()->to('/user/dashboard')
                           ->with('success', $message);
        } else {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.');
        }
    }

    /**
     * Cancel registration
     */
    public function batalDaftar($id)
    {
        $userId = $this->session->get('user_id');
        
        $pendaftaran = $this->pendaftaranModel->where('event_id', $id)
                                            ->where('user_id', $userId)
                                            ->first();

        if (!$pendaftaran) {
            return redirect()->to('/user/dashboard')->with('error', 'Pendaftaran tidak ditemukan');
        }

        // Check if can cancel (only pending and confirmed status can be cancelled)
        if (!in_array($pendaftaran['status'], ['pending', 'confirmed'])) {
            return redirect()->to('/user/dashboard')->with('error', 'Pendaftaran tidak dapat dibatalkan');
        }

        // Check event date (can't cancel if event is today or past)
        $event = $this->eventModel->find($id);
        if (strtotime($event['tanggal_mulai']) <= strtotime(date('Y-m-d'))) {
            return redirect()->to('/user/dashboard')->with('error', 'Tidak dapat membatalkan pendaftaran event yang sudah berlangsung');
        }

        if ($this->pendaftaranModel->cancelRegistration($pendaftaran['id'])) {
            // Cancel related ticket if exists
            $tiket = $this->tiketModel->where('pendaftaran_id', $pendaftaran['id'])->first();
            if ($tiket) {
                $this->tiketModel->cancelTiket($tiket['id']);
            }

            return redirect()->to('/user/dashboard')->with('success', 'Pendaftaran berhasil dibatalkan');
        } else {
            return redirect()->to('/user/dashboard')->with('error', 'Gagal membatalkan pendaftaran');
        }
    }

    /**
     * Show event detail for registered users
     */
    public function detail($id)
    {
        $userId = $this->session->get('user_id');
        $event = $this->eventModel->getEventDetail($id);

        if (!$event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Get user's registration for this event
        $pendaftaran = $this->pendaftaranModel->where('event_id', $id)
                                            ->where('user_id', $userId)
                                            ->first();

        // Get ticket if exists
        $tiket = null;
        if ($pendaftaran) {
            $tiket = $this->tiketModel->where('pendaftaran_id', $pendaftaran['id'])->first();
        }

        $data = [
            'title' => $event['nama_event'] . ' - SIEBA',
            'event' => $event,
            'pendaftaran' => $pendaftaran,
            'tiket' => $tiket,
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => base_url('/user/dashboard')],
                ['title' => $event['nama_event']]
            ]
        ];

        return view('user/detail_event_user', $data);
    }

    /**
     * Generate ticket for confirmed registration
     */
    public function generateTiket($pendaftaranId)
    {
        $userId = $this->session->get('user_id');
        
        $pendaftaran = $this->pendaftaranModel->where('id', $pendaftaranId)
                                            ->where('user_id', $userId)
                                            ->first();

        if (!$pendaftaran) {
            return redirect()->to('/user/dashboard')->with('error', 'Pendaftaran tidak ditemukan');
        }

        if ($pendaftaran['status'] !== 'confirmed') {
            return redirect()->to('/user/dashboard')->with('error', 'Pendaftaran belum dikonfirmasi');
        }

        // Check if ticket already exists
        $existingTiket = $this->tiketModel->where('pendaftaran_id', $pendaftaranId)->first();
        
        if ($existingTiket) {
            return redirect()->to('/user/tiket')->with('info', 'Tiket sudah tersedia');
        }

        // Generate new ticket
        $tiket = $this->tiketModel->generateTiket($pendaftaranId);

        if ($tiket) {
            return redirect()->to('/user/tiket')->with('success', 'Tiket berhasil digenerate!');
        } else {
            return redirect()->to('/user/dashboard')->with('error', 'Gagal generate tiket');
        }
    }

    /**
     * Upload payment proof for paid events
     */
    public function uploadBukti($pendaftaranId)
    {
        $userId = $this->session->get('user_id');
        
        $pendaftaran = $this->pendaftaranModel->where('id', $pendaftaranId)
                                            ->where('user_id', $userId)
                                            ->first();

        if (!$pendaftaran) {
            return redirect()->to('/user/dashboard')->with('error', 'Pendaftaran tidak ditemukan');
        }

        $data = [
            'title' => 'Upload Bukti Pembayaran - SIEBA',
            'pendaftaran' => $pendaftaran,
            'validation' => \Config\Services::validation()
        ];

        return view('user/upload_bukti_user', $data);
    }

    /**
     * Process payment proof upload
     */
    public function prosesUploadBukti()
    {
        $userId = $this->session->get('user_id');
        $pendaftaranId = $this->request->getPost('pendaftaran_id');
        
        $pendaftaran = $this->pendaftaranModel->where('id', $pendaftaranId)
                                            ->where('user_id', $userId)
                                            ->first();

        if (!$pendaftaran) {
            return redirect()->to('/user/dashboard')->with('error', 'Pendaftaran tidak ditemukan');
        }

        $rules = [
            'bukti_pembayaran' => 'uploaded[bukti_pembayaran]|max_size[bukti_pembayaran,2048]|ext_in[bukti_pembayaran,jpg,jpeg,png,pdf]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('bukti_pembayaran');
        
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = 'bukti_' . $pendaftaranId . '_' . time() . '.' . $file->getExtension();
            $file->move(WRITEPATH . 'uploads/bukti/', $newName);

            $filePath = 'uploads/bukti/' . $newName;
            
            if ($this->pendaftaranModel->uploadBuktiPembayaran($pendaftaranId, $filePath)) {
                return redirect()->to('/user/dashboard')
                               ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu konfirmasi admin.');
            }
        }

        return redirect()->back()->with('error', 'Gagal mengupload bukti pembayaran');
    }

    /**
     * Get registration status via AJAX
     */
    public function getRegistrationStatus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $userId = $this->session->get('user_id');
        $eventId = $this->request->getPost('event_id');

        $pendaftaran = $this->pendaftaranModel->where('event_id', $eventId)
                                            ->where('user_id', $userId)
                                            ->first();

        if (!$pendaftaran) {
            return $this->response->setJSON([
                'registered' => false,
                'can_register' => $this->eventModel->canRegister($eventId)
            ]);
        }

        // Get ticket if exists
        $tiket = $this->tiketModel->where('pendaftaran_id', $pendaftaran['id'])->first();

        return $this->response->setJSON([
            'registered' => true,
            'status' => $pendaftaran['status'],
            'registration_code' => $pendaftaran['kode_pendaftaran'],
            'has_ticket' => $tiket !== null,
            'ticket_code' => $tiket ? $tiket['kode_tiket'] : null
        ]);
    }
}