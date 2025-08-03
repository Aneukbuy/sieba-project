<?php

namespace App\Controllers\Public;

use App\Models\EventModel;
use App\Models\PendaftaranModel;
use CodeIgniter\Controller;

class EventController extends Controller
{
    protected $eventModel;
    protected $pendaftaranModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->pendaftaranModel = new PendaftaranModel();
        helper(['form', 'url']);
    }

    /**
     * Show event detail
     */
    public function detail($id)
    {
        $event = $this->eventModel->getEventDetail($id);

        if (!$event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Check if event is published
        if ($event['status'] !== 'published') {
            return redirect()->to('/telusuri')->with('error', 'Event tidak tersedia');
        }

        $data = [
            'title' => $event['nama_event'] . ' - SIEBA',
            'event' => $event,
            'breadcrumb' => [
                ['title' => 'Telusuri Event', 'url' => base_url('/telusuri')],
                ['title' => $event['nama_event']]
            ]
        ];

        return view('public/detail_event', $data);
    }

    /**
     * Show registration form for guests
     */
    public function daftar($id)
    {
        $event = $this->eventModel->getEventDetail($id);

        if (!$event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Check if can register
        if (!$event['dapat_daftar']) {
            return redirect()->to('/event/' . $id)->with('error', 'Pendaftaran sudah ditutup atau kuota penuh');
        }

        $data = [
            'title' => 'Daftar ' . $event['nama_event'] . ' - SIEBA',
            'event' => $event,
            'validation' => \Config\Services::validation(),
            'breadcrumb' => [
                ['title' => 'Telusuri Event', 'url' => base_url('/telusuri')],
                ['title' => $event['nama_event'], 'url' => base_url('/event/' . $id)],
                ['title' => 'Pendaftaran']
            ]
        ];

        return view('public/daftar_event_tamu', $data);
    }

    /**
     * Process guest registration
     */
    public function prosesDaftar($id)
    {
        $event = $this->eventModel->getEventDetail($id);

        if (!$event || !$event['dapat_daftar']) {
            return redirect()->to('/event/' . $id)->with('error', 'Tidak dapat mendaftar event ini');
        }

        $rules = [
            'nama_peserta' => 'required|min_length[3]|max_length[100]',
            'email_peserta' => 'required|valid_email',
            'no_hp_peserta' => 'required|numeric|min_length[10]|max_length[15]',
            'institusi_peserta' => 'permit_empty|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Check if email already registered for this event
        if (!$this->pendaftaranModel->canUserRegister($id, null, $this->request->getPost('email_peserta'))) {
            return redirect()->back()->withInput()->with('error', 'Email sudah terdaftar untuk event ini');
        }

        $data = [
            'nama_peserta' => $this->request->getPost('nama_peserta'),
            'email_peserta' => $this->request->getPost('email_peserta'),
            'no_hp_peserta' => $this->request->getPost('no_hp_peserta'),
            'institusi_peserta' => $this->request->getPost('institusi_peserta')
        ];

        $pendaftaranId = $this->pendaftaranModel->daftarTamu($id, $data);

        if ($pendaftaranId) {
            // Get registration data
            $pendaftaran = $this->pendaftaranModel->find($pendaftaranId);
            
            // Send confirmation email (implement later)
            // $this->sendConfirmationEmail($data['email_peserta'], $pendaftaran);

            return redirect()->to('/hasil-daftar/' . $pendaftaran['kode_pendaftaran'])
                           ->with('success', 'Pendaftaran berhasil! Silakan cek email untuk konfirmasi.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.');
        }
    }

    /**
     * Show registration result
     */
    public function hasilDaftar($kode = null)
    {
        if (!$kode) {
            return redirect()->to('/')->with('error', 'Kode pendaftaran tidak valid');
        }

        $pendaftaran = $this->pendaftaranModel->getByKode($kode);

        if (!$pendaftaran) {
            return redirect()->to('/')->with('error', 'Data pendaftaran tidak ditemukan');
        }

        $data = [
            'title' => 'Hasil Pendaftaran - SIEBA',
            'pendaftaran' => $pendaftaran
        ];

        return view('public/hasil_daftar', $data);
    }

    /**
     * Show payment upload form
     */
    public function uploadBukti($kode = null)
    {
        if (!$kode) {
            return redirect()->to('/')->with('error', 'Kode pendaftaran tidak valid');
        }

        $pendaftaran = $this->pendaftaranModel->getByKode($kode);

        if (!$pendaftaran) {
            return redirect()->to('/')->with('error', 'Data pendaftaran tidak ditemukan');
        }

        // Check if event requires payment
        if ($pendaftaran['biaya'] == 0) {
            return redirect()->to('/hasil-daftar/' . $kode)->with('info', 'Event ini gratis, tidak perlu upload bukti pembayaran');
        }

        $data = [
            'title' => 'Upload Bukti Pembayaran - SIEBA',
            'pendaftaran' => $pendaftaran,
            'validation' => \Config\Services::validation()
        ];

        return view('public/upload_bukti', $data);
    }

    /**
     * Process payment proof upload
     */
    public function prosesUploadBukti()
    {
        $kode = $this->request->getPost('kode_pendaftaran');
        $pendaftaran = $this->pendaftaranModel->getByKode($kode);

        if (!$pendaftaran) {
            return redirect()->to('/')->with('error', 'Data pendaftaran tidak ditemukan');
        }

        $rules = [
            'bukti_pembayaran' => 'uploaded[bukti_pembayaran]|max_size[bukti_pembayaran,2048]|ext_in[bukti_pembayaran,jpg,jpeg,png,pdf]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('bukti_pembayaran');
        
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = 'bukti_' . $pendaftaran['id'] . '_' . time() . '.' . $file->getExtension();
            $file->move(WRITEPATH . 'uploads/bukti/', $newName);

            $filePath = 'uploads/bukti/' . $newName;
            
            if ($this->pendaftaranModel->uploadBuktiPembayaran($pendaftaran['id'], $filePath)) {
                return redirect()->to('/hasil-daftar/' . $kode)
                               ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu konfirmasi admin.');
            }
        }

        return redirect()->back()->with('error', 'Gagal mengupload bukti pembayaran');
    }

    /**
     * Send confirmation email (to be implemented)
     */
    private function sendConfirmationEmail($email, $pendaftaran)
    {
        // Implementation will depend on EmailService
        // $emailService = new \App\Services\EmailService();
        // $emailService->sendRegistrationConfirmation($email, $pendaftaran);
    }
}