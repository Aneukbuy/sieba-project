<?php

namespace App\Models;

use CodeIgniter\Model;

class SertifikatModel extends Model
{
    protected $table = 'sertifikat';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'pendaftaran_id',
        'nomor_sertifikat',
        'file_sertifikat',
        'tanggal_generate',
        'status'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'pendaftaran_id' => 'required|integer',
        'nomor_sertifikat' => 'required|is_unique[sertifikat.nomor_sertifikat,id,{id}]',
        'status' => 'in_list[generated,downloaded,sent]'
    ];

    protected $validationMessages = [
        'pendaftaran_id' => [
            'required' => 'ID Pendaftaran harus diisi',
            'integer' => 'ID Pendaftaran harus berupa angka'
        ],
        'nomor_sertifikat' => [
            'required' => 'Nomor sertifikat harus diisi',
            'is_unique' => 'Nomor sertifikat sudah ada'
        ]
    ];

    protected $skipValidation = false;
    protected $beforeInsert = ['generateNomorSertifikat'];

    /**
     * Generate unique certificate number
     */
    protected function generateNomorSertifikat(array $data)
    {
        if (!isset($data['data']['nomor_sertifikat'])) {
            $year = date('Y');
            $month = date('m');
            
            // Get last number for this month
            $lastCert = $this->where('nomor_sertifikat LIKE', "CERT-{$year}{$month}-%")
                            ->orderBy('id', 'DESC')
                            ->first();
            
            $number = 1;
            if ($lastCert) {
                $lastNumber = (int)substr($lastCert['nomor_sertifikat'], -4);
                $number = $lastNumber + 1;
            }
            
            $data['data']['nomor_sertifikat'] = sprintf('CERT-%s%s-%04d', $year, $month, $number);
        }

        return $data;
    }

    /**
     * Generate certificate for completed event participation
     */
    public function generateSertifikat($pendaftaranId)
    {
        // Check if certificate already exists
        $existing = $this->where('pendaftaran_id', $pendaftaranId)->first();
        if ($existing) {
            return $existing;
        }

        // Get registration data
        $pendaftaranModel = new PendaftaranModel();
        $pendaftaran = $pendaftaranModel->select('pendaftaran.*, events.nama_event, events.tanggal_mulai, events.tanggal_selesai')
                                       ->join('events', 'events.id = pendaftaran.event_id')
                                       ->find($pendaftaranId);

        if (!$pendaftaran || $pendaftaran['status'] !== 'completed') {
            return false;
        }

        $sertifikatData = [
            'pendaftaran_id' => $pendaftaranId,
            'tanggal_generate' => date('Y-m-d H:i:s'),
            'status' => 'generated'
        ];

        $sertifikatId = $this->insert($sertifikatData);

        if ($sertifikatId) {
            // Generate PDF certificate
            $this->generatePDFSertifikat($sertifikatId);
            return $this->find($sertifikatId);
        }

        return false;
    }

    /**
     * Generate PDF certificate file
     */
    private function generatePDFSertifikat($sertifikatId)
    {
        $sertifikat = $this->getSertifikatDetail($sertifikatId);
        
        if (!$sertifikat) return false;

        // Generate PDF certificate using service
        $sertifikatService = new \App\Services\SertifikatService();
        $pdfPath = $sertifikatService->generatePDF($sertifikat);

        // Update certificate with file path
        $this->update($sertifikatId, ['file_sertifikat' => $pdfPath]);

        return true;
    }

    /**
     * Get certificate detail with all related information
     */
    public function getSertifikatDetail($sertifikatId)
    {
        return $this->select('sertifikat.*, pendaftaran.nama_peserta, pendaftaran.email_peserta, 
                             events.nama_event, events.tanggal_mulai, events.tanggal_selesai, 
                             events.lokasi, events.deskripsi')
                   ->join('pendaftaran', 'pendaftaran.id = sertifikat.pendaftaran_id')
                   ->join('events', 'events.id = pendaftaran.event_id')
                   ->find($sertifikatId);
    }

    /**
     * Get certificate by number
     */
    public function getSertifikatByNomor($nomorSertifikat)
    {
        return $this->select('sertifikat.*, pendaftaran.nama_peserta, pendaftaran.email_peserta, 
                             events.nama_event, events.tanggal_mulai, events.tanggal_selesai, events.lokasi')
                   ->join('pendaftaran', 'pendaftaran.id = sertifikat.pendaftaran_id')
                   ->join('events', 'events.id = pendaftaran.event_id')
                   ->where('sertifikat.nomor_sertifikat', $nomorSertifikat)
                   ->first();
    }

    /**
     * Get user certificates
     */
    public function getUserSertifikat($userId)
    {
        return $this->select('sertifikat.*, pendaftaran.nama_peserta, events.nama_event, 
                             events.tanggal_mulai, events.tanggal_selesai')
                   ->join('pendaftaran', 'pendaftaran.id = sertifikat.pendaftaran_id')
                   ->join('events', 'events.id = pendaftaran.event_id')
                   ->where('pendaftaran.user_id', $userId)
                   ->orderBy('sertifikat.created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get event certificates for admin
     */
    public function getEventSertifikat($eventId)
    {
        return $this->select('sertifikat.*, pendaftaran.nama_peserta, pendaftaran.email_peserta')
                   ->join('pendaftaran', 'pendaftaran.id = sertifikat.pendaftaran_id')
                   ->where('pendaftaran.event_id', $eventId)
                   ->orderBy('sertifikat.created_at', 'ASC')
                   ->findAll();
    }

    /**
     * Bulk generate certificates for event
     */
    public function bulkGenerateForEvent($eventId)
    {
        $pendaftaranModel = new PendaftaranModel();
        $completedRegistrations = $pendaftaranModel->getCompletedRegistrations($eventId);

        $generated = [];
        foreach ($completedRegistrations as $pendaftaran) {
            $sertifikat = $this->generateSertifikat($pendaftaran['id']);
            if ($sertifikat) {
                $generated[] = $sertifikat;
            }
        }

        return $generated;
    }

    /**
     * Get certificate statistics
     */
    public function getStatistik($eventId = null)
    {
        $builder = $this;

        if ($eventId) {
            $builder = $builder->join('pendaftaran', 'pendaftaran.id = sertifikat.pendaftaran_id')
                              ->where('pendaftaran.event_id', $eventId);
        }

        $total = $builder->countAllResults(false);
        $generated = $builder->where('sertifikat.status', 'generated')->countAllResults(false);
        $downloaded = $builder->where('sertifikat.status', 'downloaded')->countAllResults(false);
        $sent = $builder->where('sertifikat.status', 'sent')->countAllResults(false);

        return [
            'total' => $total,
            'generated' => $generated,
            'downloaded' => $downloaded,
            'sent' => $sent
        ];
    }

    /**
     * Mark certificate as downloaded
     */
    public function markDownloaded($sertifikatId)
    {
        return $this->update($sertifikatId, ['status' => 'downloaded']);
    }

    /**
     * Mark certificate as sent via email
     */
    public function markSent($sertifikatId)
    {
        return $this->update($sertifikatId, ['status' => 'sent']);
    }

    /**
     * Verify certificate authenticity
     */
    public function verifySertifikat($nomorSertifikat)
    {
        $sertifikat = $this->getSertifikatByNomor($nomorSertifikat);

        if (!$sertifikat) {
            return [
                'valid' => false,
                'message' => 'Sertifikat tidak ditemukan'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Sertifikat valid',
            'data' => $sertifikat
        ];
    }

    /**
     * Send certificate via email
     */
    public function sendViaEmail($sertifikatId)
    {
        $sertifikat = $this->getSertifikatDetail($sertifikatId);
        
        if (!$sertifikat || !$sertifikat['file_sertifikat']) {
            return false;
        }

        // Send email using service
        $emailService = new \App\Services\EmailService();
        $sent = $emailService->sendSertifikat($sertifikat);

        if ($sent) {
            $this->markSent($sertifikatId);
        }

        return $sent;
    }

    /**
     * Get certificates that need to be sent
     */
    public function getPendingEmailSertifikat()
    {
        return $this->select('sertifikat.*, pendaftaran.nama_peserta, pendaftaran.email_peserta, events.nama_event')
                   ->join('pendaftaran', 'pendaftaran.id = sertifikat.pendaftaran_id')
                   ->join('events', 'events.id = pendaftaran.event_id')
                   ->where('sertifikat.status', 'generated')
                   ->whereNotNull('sertifikat.file_sertifikat')
                   ->findAll();
    }

    /**
     * Get certificate download statistics
     */
    public function getDownloadStats($eventId = null)
    {
        $builder = $this;

        if ($eventId) {
            $builder = $builder->join('pendaftaran', 'pendaftaran.id = sertifikat.pendaftaran_id')
                              ->where('pendaftaran.event_id', $eventId);
        }

        $totalGenerated = $builder->countAllResults(false);
        $totalDownloaded = $builder->whereIn('sertifikat.status', ['downloaded', 'sent'])
                                 ->countAllResults(false);

        $percentage = $totalGenerated > 0 ? round(($totalDownloaded / $totalGenerated) * 100, 2) : 0;

        return [
            'total_generated' => $totalGenerated,
            'total_downloaded' => $totalDownloaded,
            'download_percentage' => $percentage
        ];
    }
}