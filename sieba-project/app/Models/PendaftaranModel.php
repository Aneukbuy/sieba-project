<?php

namespace App\Models;

use CodeIgniter\Model;

class PendaftaranModel extends Model
{
    protected $table = 'pendaftaran';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'event_id',
        'user_id',
        'nama_peserta',
        'email_peserta',
        'no_hp_peserta',
        'institusi_peserta',
        'status',
        'kode_pendaftaran',
        'bukti_pembayaran',
        'catatan',
        'tanggal_daftar',
        'tanggal_hadir'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'event_id' => 'required|integer',
        'nama_peserta' => 'required|min_length[3]|max_length[100]',
        'email_peserta' => 'required|valid_email',
        'no_hp_peserta' => 'required|numeric|min_length[10]|max_length[15]',
        'status' => 'in_list[pending,confirmed,cancelled,completed]'
    ];

    protected $validationMessages = [
        'event_id' => [
            'required' => 'Event ID harus diisi',
            'integer' => 'Event ID harus berupa angka'
        ],
        'nama_peserta' => [
            'required' => 'Nama peserta harus diisi',
            'min_length' => 'Nama peserta minimal 3 karakter',
            'max_length' => 'Nama peserta maksimal 100 karakter'
        ],
        'email_peserta' => [
            'required' => 'Email peserta harus diisi',
            'valid_email' => 'Format email tidak valid'
        ]
    ];

    protected $skipValidation = false;
    protected $beforeInsert = ['generateKodePendaftaran'];

    /**
     * Generate unique registration code
     */
    protected function generateKodePendaftaran(array $data)
    {
        if (!isset($data['data']['kode_pendaftaran'])) {
            $data['data']['kode_pendaftaran'] = 'REG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        }

        return $data;
    }

    /**
     * Register for event (for guests)
     */
    public function daftarTamu($eventId, $data)
    {
        // Check if event exists and still accepting registration
        $eventModel = new EventModel();
        if (!$eventModel->canRegister($eventId)) {
            return false;
        }

        // Check if email already registered for this event
        $existing = $this->where('event_id', $eventId)
                        ->where('email_peserta', $data['email_peserta'])
                        ->first();

        if ($existing) {
            return false; // Already registered
        }

        $registrationData = [
            'event_id' => $eventId,
            'user_id' => null, // For guests
            'nama_peserta' => $data['nama_peserta'],
            'email_peserta' => $data['email_peserta'],
            'no_hp_peserta' => $data['no_hp_peserta'],
            'institusi_peserta' => $data['institusi_peserta'] ?? '',
            'status' => 'pending',
            'tanggal_daftar' => date('Y-m-d H:i:s')
        ];

        return $this->insert($registrationData);
    }

    /**
     * Register for event (for logged in users)
     */
    public function daftarUser($eventId, $userId, $data = [])
    {
        // Check if event exists and still accepting registration
        $eventModel = new EventModel();
        if (!$eventModel->canRegister($eventId)) {
            return false;
        }

        // Check if user already registered for this event
        $existing = $this->where('event_id', $eventId)
                        ->where('user_id', $userId)
                        ->first();

        if ($existing) {
            return false; // Already registered
        }

        // Get user data
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $registrationData = [
            'event_id' => $eventId,
            'user_id' => $userId,
            'nama_peserta' => $data['nama_peserta'] ?? $user['nama'],
            'email_peserta' => $data['email_peserta'] ?? $user['email'],
            'no_hp_peserta' => $data['no_hp_peserta'] ?? $user['no_hp'],
            'institusi_peserta' => $data['institusi_peserta'] ?? $user['institusi'],
            'status' => 'confirmed', // Auto confirm for registered users
            'tanggal_daftar' => date('Y-m-d H:i:s')
        ];

        return $this->insert($registrationData);
    }

    /**
     * Get user's registrations
     */
    public function getUserRegistrations($userId)
    {
        return $this->select('pendaftaran.*, events.nama_event, events.tanggal_mulai, events.tanggal_selesai, events.lokasi')
                   ->join('events', 'events.id = pendaftaran.event_id')
                   ->where('pendaftaran.user_id', $userId)
                   ->orderBy('pendaftaran.created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get event participants
     */
    public function getEventParticipants($eventId, $status = null)
    {
        $builder = $this->select('pendaftaran.*, users.nama as user_nama, users.email as user_email')
                       ->join('users', 'users.id = pendaftaran.user_id', 'left')
                       ->where('pendaftaran.event_id', $eventId);

        if ($status) {
            $builder->where('pendaftaran.status', $status);
        }

        return $builder->orderBy('pendaftaran.created_at', 'ASC')
                      ->findAll();
    }

    /**
     * Get registration by code
     */
    public function getByKode($kode)
    {
        return $this->select('pendaftaran.*, events.nama_event, events.tanggal_mulai, events.tanggal_selesai, events.lokasi, events.alamat_lokasi')
                   ->join('events', 'events.id = pendaftaran.event_id')
                   ->where('pendaftaran.kode_pendaftaran', $kode)
                   ->first();
    }

    /**
     * Confirm registration (for admin)
     */
    public function confirmRegistration($id)
    {
        return $this->update($id, ['status' => 'confirmed']);
    }

    /**
     * Cancel registration
     */
    public function cancelRegistration($id)
    {
        return $this->update($id, ['status' => 'cancelled']);
    }

    /**
     * Mark as completed (attended)
     */
    public function markCompleted($id)
    {
        return $this->update($id, [
            'status' => 'completed',
            'tanggal_hadir' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get registration statistics
     */
    public function getStatistik($eventId = null)
    {
        $builder = $this;

        if ($eventId) {
            $builder = $builder->where('event_id', $eventId);
        }

        $total = $builder->countAllResults(false);
        $confirmed = $builder->where('status', 'confirmed')->countAllResults(false);
        $pending = $builder->where('status', 'pending')->countAllResults(false);
        $completed = $builder->where('status', 'completed')->countAllResults(false);

        return [
            'total' => $total,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'completed' => $completed,
            'cancelled' => $total - $confirmed - $pending - $completed
        ];
    }

    /**
     * Check if user can register for event
     */
    public function canUserRegister($eventId, $userId = null, $email = null)
    {
        $builder = $this->where('event_id', $eventId);

        if ($userId) {
            $builder->where('user_id', $userId);
        } elseif ($email) {
            $builder->where('email_peserta', $email);
        }

        return $builder->first() === null;
    }

    /**
     * Get completed registrations for certificate generation
     */
    public function getCompletedRegistrations($eventId)
    {
        return $this->select('pendaftaran.*, events.nama_event')
                   ->join('events', 'events.id = pendaftaran.event_id')
                   ->where('pendaftaran.event_id', $eventId)
                   ->where('pendaftaran.status', 'completed')
                   ->findAll();
    }

    /**
     * Upload payment proof
     */
    public function uploadBuktiPembayaran($id, $filePath)
    {
        return $this->update($id, ['bukti_pembayaran' => $filePath]);
    }
}