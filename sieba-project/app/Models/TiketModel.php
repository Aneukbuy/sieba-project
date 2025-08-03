<?php

namespace App\Models;

use CodeIgniter\Model;

class TiketModel extends Model
{
    protected $table = 'tiket';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'pendaftaran_id',
        'kode_tiket',
        'qr_code',
        'file_tiket',
        'status',
        'tanggal_generate',
        'tanggal_scan'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'pendaftaran_id' => 'required|integer',
        'kode_tiket' => 'required|is_unique[tiket.kode_tiket,id,{id}]',
        'status' => 'in_list[active,used,expired,cancelled]'
    ];

    protected $validationMessages = [
        'pendaftaran_id' => [
            'required' => 'ID Pendaftaran harus diisi',
            'integer' => 'ID Pendaftaran harus berupa angka'
        ],
        'kode_tiket' => [
            'required' => 'Kode tiket harus diisi',
            'is_unique' => 'Kode tiket sudah ada'
        ]
    ];

    protected $skipValidation = false;
    protected $beforeInsert = ['generateKodeTiket'];

    /**
     * Generate unique ticket code
     */
    protected function generateKodeTiket(array $data)
    {
        if (!isset($data['data']['kode_tiket'])) {
            $data['data']['kode_tiket'] = 'TKT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8));
        }

        return $data;
    }

    /**
     * Generate ticket for confirmed registration
     */
    public function generateTiket($pendaftaranId)
    {
        // Check if ticket already exists
        $existing = $this->where('pendaftaran_id', $pendaftaranId)->first();
        if ($existing) {
            return $existing;
        }

        // Get registration data
        $pendaftaranModel = new PendaftaranModel();
        $pendaftaran = $pendaftaranModel->select('pendaftaran.*, events.nama_event, events.tanggal_mulai, events.lokasi')
                                       ->join('events', 'events.id = pendaftaran.event_id')
                                       ->find($pendaftaranId);

        if (!$pendaftaran || $pendaftaran['status'] !== 'confirmed') {
            return false;
        }

        $tiketData = [
            'pendaftaran_id' => $pendaftaranId,
            'status' => 'active',
            'tanggal_generate' => date('Y-m-d H:i:s')
        ];

        $tiketId = $this->insert($tiketData);

        if ($tiketId) {
            // Generate QR code and PDF ticket
            $this->generateQRCodeAndPDF($tiketId);
            return $this->find($tiketId);
        }

        return false;
    }

    /**
     * Generate QR code and PDF ticket file
     */
    private function generateQRCodeAndPDF($tiketId)
    {
        $tiket = $this->getTiketDetail($tiketId);
        
        if (!$tiket) return false;

        // Generate QR Code using service
        $qrService = new \App\Services\QRCodeService();
        $qrCodePath = $qrService->generate($tiket['kode_tiket'], 'tiket');

        // Update ticket with QR code path
        $this->update($tiketId, ['qr_code' => $qrCodePath]);

        // Generate PDF ticket (implement later with TCPDF or similar)
        // $pdfPath = $this->generatePDFTiket($tiket);
        // $this->update($tiketId, ['file_tiket' => $pdfPath]);

        return true;
    }

    /**
     * Get ticket detail with all related information
     */
    public function getTiketDetail($tiketId)
    {
        return $this->select('tiket.*, pendaftaran.nama_peserta, pendaftaran.email_peserta, 
                             pendaftaran.kode_pendaftaran, events.nama_event, events.tanggal_mulai, 
                             events.tanggal_selesai, events.waktu_mulai, events.waktu_selesai, 
                             events.lokasi, events.alamat_lokasi')
                   ->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                   ->join('events', 'events.id = pendaftaran.event_id')
                   ->find($tiketId);
    }

    /**
     * Get ticket by code
     */
    public function getTiketByKode($kodeTiket)
    {
        return $this->select('tiket.*, pendaftaran.nama_peserta, pendaftaran.email_peserta, 
                             pendaftaran.status as status_pendaftaran, events.nama_event, 
                             events.tanggal_mulai, events.tanggal_selesai, events.waktu_mulai, 
                             events.waktu_selesai, events.lokasi, events.alamat_lokasi')
                   ->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                   ->join('events', 'events.id = pendaftaran.event_id')
                   ->where('tiket.kode_tiket', $kodeTiket)
                   ->first();
    }

    /**
     * Validate and scan ticket
     */
    public function scanTiket($kodeTiket)
    {
        $tiket = $this->getTiketByKode($kodeTiket);

        if (!$tiket) {
            return ['success' => false, 'message' => 'Tiket tidak ditemukan'];
        }

        if ($tiket['status'] === 'used') {
            return [
                'success' => false, 
                'message' => 'Tiket sudah digunakan pada ' . date('d/m/Y H:i', strtotime($tiket['tanggal_scan'])),
                'data' => $tiket
            ];
        }

        if ($tiket['status'] !== 'active') {
            return ['success' => false, 'message' => 'Tiket tidak aktif'];
        }

        // Check if event date is today
        $eventDate = date('Y-m-d', strtotime($tiket['tanggal_mulai']));
        $today = date('Y-m-d');

        if ($eventDate !== $today) {
            return ['success' => false, 'message' => 'Tiket tidak berlaku untuk hari ini'];
        }

        // Mark ticket as used and update registration to completed
        $this->update($tiket['id'], [
            'status' => 'used',
            'tanggal_scan' => date('Y-m-d H:i:s')
        ]);

        $pendaftaranModel = new PendaftaranModel();
        $pendaftaranModel->markCompleted($tiket['pendaftaran_id']);

        return [
            'success' => true, 
            'message' => 'Tiket berhasil discan', 
            'data' => $tiket
        ];
    }

    /**
     * Get user tickets
     */
    public function getUserTiket($userId)
    {
        return $this->select('tiket.*, pendaftaran.nama_peserta, events.nama_event, 
                             events.tanggal_mulai, events.tanggal_selesai, events.lokasi')
                   ->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                   ->join('events', 'events.id = pendaftaran.event_id')
                   ->where('pendaftaran.user_id', $userId)
                   ->orderBy('tiket.created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get event tickets for admin
     */
    public function getEventTiket($eventId, $status = null)
    {
        $builder = $this->select('tiket.*, pendaftaran.nama_peserta, pendaftaran.email_peserta')
                       ->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                       ->where('pendaftaran.event_id', $eventId);

        if ($status) {
            $builder->where('tiket.status', $status);
        }

        return $builder->orderBy('tiket.created_at', 'ASC')
                      ->findAll();
    }

    /**
     * Get ticket statistics
     */
    public function getStatistik($eventId = null)
    {
        $builder = $this;

        if ($eventId) {
            $builder = $builder->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                              ->where('pendaftaran.event_id', $eventId);
        }

        $total = $builder->countAllResults(false);
        $active = $builder->where('tiket.status', 'active')->countAllResults(false);
        $used = $builder->where('tiket.status', 'used')->countAllResults(false);
        $expired = $builder->where('tiket.status', 'expired')->countAllResults(false);

        return [
            'total' => $total,
            'active' => $active,
            'used' => $used,
            'expired' => $expired
        ];
    }

    /**
     * Expire old tickets
     */
    public function expireOldTiket()
    {
        // Mark tickets as expired for events that have ended
        $this->set('status', 'expired')
             ->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
             ->join('events', 'events.id = pendaftaran.event_id')
             ->where('tiket.status', 'active')
             ->where('events.tanggal_selesai <', date('Y-m-d'))
             ->update();
    }

    /**
     * Cancel ticket
     */
    public function cancelTiket($tiketId)
    {
        return $this->update($tiketId, ['status' => 'cancelled']);
    }

    /**
     * Reactivate cancelled ticket
     */
    public function reactivateTiket($tiketId)
    {
        $tiket = $this->getTiketDetail($tiketId);
        
        if ($tiket && $tiket['status'] === 'cancelled') {
            // Check if event hasn't ended
            if (strtotime($tiket['tanggal_selesai']) >= time()) {
                return $this->update($tiketId, ['status' => 'active']);
            }
        }

        return false;
    }
}