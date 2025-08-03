<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'nama_event',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'alamat_lokasi',
        'kategori',
        'max_peserta',
        'biaya',
        'poster_url',
        'status',
        'persyaratan',
        'benefit',
        'contact_person',
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'nama_event' => 'required|min_length[3]|max_length[255]',
        'deskripsi' => 'required|min_length[10]',
        'tanggal_mulai' => 'required|valid_date',
        'tanggal_selesai' => 'required|valid_date',
        'waktu_mulai' => 'required',
        'waktu_selesai' => 'required',
        'lokasi' => 'required|max_length[255]',
        'kategori' => 'required|in_list[seminar,workshop,webinar,training,conference]',
        'max_peserta' => 'required|integer|greater_than[0]',
        'biaya' => 'required|decimal|greater_than_equal_to[0]',
        'status' => 'in_list[draft,published,cancelled,completed]'
    ];

    protected $validationMessages = [
        'nama_event' => [
            'required' => 'Nama event harus diisi',
            'min_length' => 'Nama event minimal 3 karakter',
            'max_length' => 'Nama event maksimal 255 karakter'
        ],
        'tanggal_mulai' => [
            'required' => 'Tanggal mulai harus diisi',
            'valid_date' => 'Format tanggal mulai tidak valid'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Get events yang sudah published dan belum berakhir
     */
    public function getActiveEvents()
    {
        return $this->where('status', 'published')
                   ->where('tanggal_selesai >=', date('Y-m-d'))
                   ->orderBy('tanggal_mulai', 'ASC')
                   ->findAll();
    }

    /**
     * Get event dengan pagination untuk halaman telusuri
     */
    public function getEventsWithPagination($perPage = 12, $kategori = null, $search = null)
    {
        $builder = $this->where('status', 'published')
                       ->where('tanggal_selesai >=', date('Y-m-d'));

        if ($kategori) {
            $builder->where('kategori', $kategori);
        }

        if ($search) {
            $builder->groupStart()
                   ->like('nama_event', $search)
                   ->orLike('deskripsi', $search)
                   ->orLike('lokasi', $search)
                   ->groupEnd();
        }

        return $builder->orderBy('tanggal_mulai', 'ASC')
                      ->paginate($perPage);
    }

    /**
     * Get event detail dengan informasi pendaftaran
     */
    public function getEventDetail($id)
    {
        $event = $this->find($id);
        
        if ($event) {
            // Hitung jumlah peserta yang sudah terdaftar
            $pendaftaranModel = new PendaftaranModel();
            $event['jumlah_peserta'] = $pendaftaranModel->where('event_id', $id)
                                                       ->where('status', 'confirmed')
                                                       ->countAllResults();
            
            // Cek apakah masih bisa daftar
            $event['dapat_daftar'] = $event['jumlah_peserta'] < $event['max_peserta'] && 
                                    $event['status'] === 'published' &&
                                    strtotime($event['tanggal_mulai']) > time();
        }

        return $event;
    }

    /**
     * Get statistik event untuk dashboard admin
     */
    public function getStatistik()
    {
        $total = $this->countAll();
        $published = $this->where('status', 'published')->countAllResults();
        $upcoming = $this->where('status', 'published')
                        ->where('tanggal_mulai >', date('Y-m-d'))
                        ->countAllResults();
        $ongoing = $this->where('status', 'published')
                       ->where('tanggal_mulai <=', date('Y-m-d'))
                       ->where('tanggal_selesai >=', date('Y-m-d'))
                       ->countAllResults();

        return [
            'total' => $total,
            'published' => $published,
            'upcoming' => $upcoming,
            'ongoing' => $ongoing
        ];
    }

    /**
     * Get events berdasarkan creator (untuk admin yang buat event)
     */
    public function getEventsByCreator($userId)
    {
        return $this->where('created_by', $userId)
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Check apakah masih bisa mendaftar ke event
     */
    public function canRegister($eventId)
    {
        $event = $this->find($eventId);
        
        if (!$event || $event['status'] !== 'published') {
            return false;
        }

        // Cek tanggal
        if (strtotime($event['tanggal_mulai']) <= time()) {
            return false;
        }

        // Cek kuota
        $pendaftaranModel = new PendaftaranModel();
        $jumlahPeserta = $pendaftaranModel->where('event_id', $eventId)
                                         ->where('status', 'confirmed')
                                         ->countAllResults();

        return $jumlahPeserta < $event['max_peserta'];
    }
}