<?php

namespace App\Controllers\Admin;

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
     * List all events
     */
    public function index()
    {
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');
        $kategori = $this->request->getGet('kategori');

        $builder = $this->eventModel;

        if ($search) {
            $builder = $builder->groupStart()
                             ->like('nama_event', $search)
                             ->orLike('lokasi', $search)
                             ->orLike('deskripsi', $search)
                             ->groupEnd();
        }

        if ($status) {
            $builder = $builder->where('status', $status);
        }

        if ($kategori) {
            $builder = $builder->where('kategori', $kategori);
        }

        $events = $builder->orderBy('created_at', 'DESC')->paginate(20);

        $data = [
            'title' => 'Kelola Event - SIEBA',
            'events' => $events,
            'pager' => $this->eventModel->pager,
            'current_search' => $search,
            'current_status' => $status,
            'current_kategori' => $kategori
        ];

        return view('admin/data_event', $data);
    }

    /**
     * Show add event form
     */
    public function tambah()
    {
        $data = [
            'title' => 'Tambah Event - SIEBA',
            'validation' => \Config\Services::validation()
        ];

        return view('admin/tambah_event', $data);
    }

    /**
     * Process add event
     */
    public function simpan()
    {
        $rules = [
            'nama_event' => 'required|min_length[3]|max_length[255]',
            'deskripsi' => 'required|min_length[10]',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'lokasi' => 'required|max_length[255]',
            'alamat_lokasi' => 'required',
            'kategori' => 'required|in_list[seminar,workshop,webinar,training,conference]',
            'max_peserta' => 'required|integer|greater_than[0]',
            'biaya' => 'required|decimal|greater_than_equal_to[0]',
            'status' => 'in_list[draft,published]',
            'poster' => 'permit_empty|uploaded[poster]|max_size[poster,2048]|ext_in[poster,jpg,jpeg,png]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama_event' => $this->request->getPost('nama_event'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'waktu_mulai' => $this->request->getPost('waktu_mulai'),
            'waktu_selesai' => $this->request->getPost('waktu_selesai'),
            'lokasi' => $this->request->getPost('lokasi'),
            'alamat_lokasi' => $this->request->getPost('alamat_lokasi'),
            'kategori' => $this->request->getPost('kategori'),
            'max_peserta' => $this->request->getPost('max_peserta'),
            'biaya' => $this->request->getPost('biaya'),
            'status' => $this->request->getPost('status') ?: 'draft',
            'persyaratan' => $this->request->getPost('persyaratan'),
            'benefit' => $this->request->getPost('benefit'),
            'contact_person' => $this->request->getPost('contact_person'),
            'created_by' => $this->session->get('user_id')
        ];

        // Handle poster upload
        $poster = $this->request->getFile('poster');
        if ($poster && $poster->isValid() && !$poster->hasMoved()) {
            $newName = 'poster_' . time() . '.' . $poster->getExtension();
            $poster->move(WRITEPATH . 'uploads/posters/', $newName);
            $data['poster_url'] = 'uploads/posters/' . $newName;
        }

        if ($this->eventModel->insert($data)) {
            return redirect()->to('/admin/event')->with('success', 'Event berhasil ditambahkan');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan event');
        }
    }

    /**
     * Show edit event form
     */
    public function edit($id)
    {
        $event = $this->eventModel->find($id);

        if (!$event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit Event - SIEBA',
            'event' => $event,
            'validation' => \Config\Services::validation()
        ];

        return view('admin/edit_event', $data);
    }

    /**
     * Process edit event
     */
    public function update($id)
    {
        $event = $this->eventModel->find($id);

        if (!$event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'nama_event' => 'required|min_length[3]|max_length[255]',
            'deskripsi' => 'required|min_length[10]',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'lokasi' => 'required|max_length[255]',
            'alamat_lokasi' => 'required',
            'kategori' => 'required|in_list[seminar,workshop,webinar,training,conference]',
            'max_peserta' => 'required|integer|greater_than[0]',
            'biaya' => 'required|decimal|greater_than_equal_to[0]',
            'status' => 'in_list[draft,published,cancelled,completed]',
            'poster' => 'permit_empty|uploaded[poster]|max_size[poster,2048]|ext_in[poster,jpg,jpeg,png]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama_event' => $this->request->getPost('nama_event'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'waktu_mulai' => $this->request->getPost('waktu_mulai'),
            'waktu_selesai' => $this->request->getPost('waktu_selesai'),
            'lokasi' => $this->request->getPost('lokasi'),
            'alamat_lokasi' => $this->request->getPost('alamat_lokasi'),
            'kategori' => $this->request->getPost('kategori'),
            'max_peserta' => $this->request->getPost('max_peserta'),
            'biaya' => $this->request->getPost('biaya'),
            'status' => $this->request->getPost('status'),
            'persyaratan' => $this->request->getPost('persyaratan'),
            'benefit' => $this->request->getPost('benefit'),
            'contact_person' => $this->request->getPost('contact_person')
        ];

        // Handle poster upload
        $poster = $this->request->getFile('poster');
        if ($poster && $poster->isValid() && !$poster->hasMoved()) {
            // Delete old poster if exists
            if ($event['poster_url'] && file_exists(WRITEPATH . $event['poster_url'])) {
                unlink(WRITEPATH . $event['poster_url']);
            }

            $newName = 'poster_' . time() . '.' . $poster->getExtension();
            $poster->move(WRITEPATH . 'uploads/posters/', $newName);
            $data['poster_url'] = 'uploads/posters/' . $newName;
        }

        if ($this->eventModel->update($id, $data)) {
            return redirect()->to('/admin/event')->with('success', 'Event berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui event');
        }
    }

    /**
     * Delete event
     */
    public function hapus($id)
    {
        $event = $this->eventModel->find($id);

        if (!$event) {
            return $this->response->setJSON(['success' => false, 'message' => 'Event tidak ditemukan']);
        }

        // Check if there are registrations
        $registrationCount = $this->pendaftaranModel->where('event_id', $id)->countAllResults();
        
        if ($registrationCount > 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak dapat menghapus event yang sudah memiliki pendaftar']);
        }

        // Delete poster file if exists
        if ($event['poster_url'] && file_exists(WRITEPATH . $event['poster_url'])) {
            unlink(WRITEPATH . $event['poster_url']);
        }

        if ($this->eventModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Event berhasil dihapus']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menghapus event']);
        }
    }

    /**
     * View event detail with participants
     */
    public function detail($id)
    {
        $event = $this->eventModel->getEventDetail($id);

        if (!$event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $participants = $this->pendaftaranModel->getEventParticipants($id);
        $tickets = $this->tiketModel->getEventTiket($id);

        $data = [
            'title' => 'Detail Event - ' . $event['nama_event'],
            'event' => $event,
            'participants' => $participants,
            'tickets' => $tickets,
            'stats' => [
                'total_participants' => count($participants),
                'confirmed' => count(array_filter($participants, fn($p) => $p['status'] === 'confirmed')),
                'pending' => count(array_filter($participants, fn($p) => $p['status'] === 'pending')),
                'completed' => count(array_filter($participants, fn($p) => $p['status'] === 'completed')),
                'total_tickets' => count($tickets),
                'used_tickets' => count(array_filter($tickets, fn($t) => $t['status'] === 'used'))
            ]
        ];

        return view('admin/detail_event', $data);
    }

    /**
     * Change event status
     */
    public function changeStatus()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/event');
        }

        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');

        $event = $this->eventModel->find($id);
        if (!$event) {
            return $this->response->setJSON(['success' => false, 'message' => 'Event tidak ditemukan']);
        }

        if ($this->eventModel->update($id, ['status' => $status])) {
            return $this->response->setJSON(['success' => true, 'message' => 'Status event berhasil diubah']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal mengubah status event']);
        }
    }

    /**
     * Duplicate event
     */
    public function duplicate($id)
    {
        $event = $this->eventModel->find($id);

        if (!$event) {
            return redirect()->to('/admin/event')->with('error', 'Event tidak ditemukan');
        }

        // Remove ID and modify some fields
        unset($event['id']);
        $event['nama_event'] = $event['nama_event'] . ' (Copy)';
        $event['status'] = 'draft';
        $event['created_by'] = $this->session->get('user_id');
        
        // Clear timestamps
        unset($event['created_at'], $event['updated_at']);

        if ($this->eventModel->insert($event)) {
            return redirect()->to('/admin/event')->with('success', 'Event berhasil diduplikasi');
        } else {
            return redirect()->to('/admin/event')->with('error', 'Gagal menduplikasi event');
        }
    }

    /**
     * Export event data
     */
    public function export($id)
    {
        $event = $this->eventModel->find($id);
        $participants = $this->pendaftaranModel->getEventParticipants($id);

        if (!$event) {
            return redirect()->to('/admin/event')->with('error', 'Event tidak ditemukan');
        }

        $data = [
            'event' => $event,
            'participants' => $participants,
            'export_date' => date('Y-m-d H:i:s')
        ];

        $filename = 'event_' . $id . '_' . date('Y-m-d') . '.json';
        
        return $this->response->setHeader('Content-Type', 'application/json')
                            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                            ->setBody(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Quick stats for AJAX
     */
    public function getQuickStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $stats = [
            'total_events' => $this->eventModel->countAll(),
            'published_events' => $this->eventModel->where('status', 'published')->countAllResults(),
            'draft_events' => $this->eventModel->where('status', 'draft')->countAllResults(),
            'upcoming_events' => $this->eventModel->where('status', 'published')
                                                  ->where('tanggal_mulai >=', date('Y-m-d'))
                                                  ->countAllResults(),
            'ongoing_events' => $this->eventModel->where('status', 'published')
                                                 ->where('tanggal_mulai <=', date('Y-m-d'))
                                                 ->where('tanggal_selesai >=', date('Y-m-d'))
                                                 ->countAllResults()
        ];

        return $this->response->setJSON($stats);
    }

    /**
     * Bulk actions
     */
    public function bulkAction()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/event');
        }

        $action = $this->request->getPost('action');
        $eventIds = $this->request->getPost('event_ids');

        if (!$eventIds || !is_array($eventIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pilih event terlebih dahulu']);
        }

        $success = 0;
        $failed = 0;

        foreach ($eventIds as $id) {
            switch ($action) {
                case 'publish':
                    if ($this->eventModel->update($id, ['status' => 'published'])) {
                        $success++;
                    } else {
                        $failed++;
                    }
                    break;

                case 'draft':
                    if ($this->eventModel->update($id, ['status' => 'draft'])) {
                        $success++;
                    } else {
                        $failed++;
                    }
                    break;

                case 'cancel':
                    if ($this->eventModel->update($id, ['status' => 'cancelled'])) {
                        $success++;
                    } else {
                        $failed++;
                    }
                    break;

                case 'delete':
                    // Check registrations before deleting
                    $registrationCount = $this->pendaftaranModel->where('event_id', $id)->countAllResults();
                    if ($registrationCount == 0) {
                        if ($this->eventModel->delete($id)) {
                            $success++;
                        } else {
                            $failed++;
                        }
                    } else {
                        $failed++;
                    }
                    break;
            }
        }

        $message = "Berhasil memproses {$success} event";
        if ($failed > 0) {
            $message .= ", {$failed} event gagal diproses";
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $message,
            'processed' => $success,
            'failed' => $failed
        ]);
    }
}