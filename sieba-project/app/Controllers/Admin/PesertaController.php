<?php

namespace App\Controllers\Admin;

use App\Models\PendaftaranModel;
use App\Models\EventModel;
use App\Models\TiketModel;
use App\Models\SertifikatModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class PesertaController extends Controller
{
    protected $pendaftaranModel;
    protected $eventModel;
    protected $tiketModel;
    protected $sertifikatModel;
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->pendaftaranModel = new PendaftaranModel();
        $this->eventModel = new EventModel();
        $this->tiketModel = new TiketModel();
        $this->sertifikatModel = new SertifikatModel();
        $this->userModel = new UserModel();
        $this->session = session();
        helper(['form', 'url']);
    }

    /**
     * List all participants
     */
    public function index()
    {
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');
        $eventId = $this->request->getGet('event_id');

        $builder = $this->pendaftaranModel->select('pendaftaran.*, events.nama_event, users.nama as user_nama')
                                        ->join('events', 'events.id = pendaftaran.event_id')
                                        ->join('users', 'users.id = pendaftaran.user_id', 'left');

        if ($search) {
            $builder = $builder->groupStart()
                             ->like('pendaftaran.nama_peserta', $search)
                             ->orLike('pendaftaran.email_peserta', $search)
                             ->orLike('pendaftaran.institusi_peserta', $search)
                             ->orLike('events.nama_event', $search)
                             ->groupEnd();
        }

        if ($status) {
            $builder = $builder->where('pendaftaran.status', $status);
        }

        if ($eventId) {
            $builder = $builder->where('pendaftaran.event_id', $eventId);
        }

        $participants = $builder->orderBy('pendaftaran.created_at', 'DESC')->paginate(20);
        
        // Get events for filter
        $events = $this->eventModel->select('id, nama_event')->findAll();

        $data = [
            'title' => 'Data Peserta - SIEBA',
            'participants' => $participants,
            'events' => $events,
            'pager' => $this->pendaftaranModel->pager,
            'current_search' => $search,
            'current_status' => $status,
            'current_event_id' => $eventId
        ];

        return view('admin/data_peserta', $data);
    }

    /**
     * Show participants by event
     */
    public function byEvent($eventId)
    {
        $event = $this->eventModel->find($eventId);
        
        if (!$event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $participants = $this->pendaftaranModel->getEventParticipants($eventId);
        
        $data = [
            'title' => 'Peserta ' . $event['nama_event'] . ' - SIEBA',
            'event' => $event,
            'participants' => $participants,
            'stats' => [
                'total' => count($participants),
                'pending' => count(array_filter($participants, fn($p) => $p['status'] === 'pending')),
                'confirmed' => count(array_filter($participants, fn($p) => $p['status'] === 'confirmed')),
                'completed' => count(array_filter($participants, fn($p) => $p['status'] === 'completed')),
                'cancelled' => count(array_filter($participants, fn($p) => $p['status'] === 'cancelled'))
            ]
        ];

        return view('admin/peserta_event', $data);
    }

    /**
     * Show participant detail
     */
    public function detail($id)
    {
        $participant = $this->pendaftaranModel->select('pendaftaran.*, events.nama_event, events.tanggal_mulai, events.tanggal_selesai, users.nama as user_nama')
                                            ->join('events', 'events.id = pendaftaran.event_id')
                                            ->join('users', 'users.id = pendaftaran.user_id', 'left')
                                            ->find($id);

        if (!$participant) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Get ticket if exists
        $ticket = $this->tiketModel->where('pendaftaran_id', $id)->first();
        
        // Get certificate if exists
        $certificate = $this->sertifikatModel->where('pendaftaran_id', $id)->first();

        $data = [
            'title' => 'Detail Peserta - SIEBA',
            'participant' => $participant,
            'ticket' => $ticket,
            'certificate' => $certificate
        ];

        return view('admin/detail_peserta', $data);
    }

    /**
     * Confirm registration
     */
    public function confirm($id)
    {
        $participant = $this->pendaftaranModel->find($id);

        if (!$participant) {
            return $this->response->setJSON(['success' => false, 'message' => 'Peserta tidak ditemukan']);
        }

        if ($this->pendaftaranModel->confirmRegistration($id)) {
            // Generate ticket automatically
            $this->tiketModel->generateTiket($id);
            
            return $this->response->setJSON(['success' => true, 'message' => 'Pendaftaran berhasil dikonfirmasi']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal mengkonfirmasi pendaftaran']);
        }
    }

    /**
     * Cancel registration
     */
    public function cancel($id)
    {
        $participant = $this->pendaftaranModel->find($id);

        if (!$participant) {
            return $this->response->setJSON(['success' => false, 'message' => 'Peserta tidak ditemukan']);
        }

        if ($this->pendaftaranModel->cancelRegistration($id)) {
            // Cancel related ticket if exists
            $ticket = $this->tiketModel->where('pendaftaran_id', $id)->first();
            if ($ticket) {
                $this->tiketModel->cancelTiket($ticket['id']);
            }
            
            return $this->response->setJSON(['success' => true, 'message' => 'Pendaftaran berhasil dibatalkan']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal membatalkan pendaftaran']);
        }
    }

    /**
     * Mark as completed (attended)
     */
    public function markCompleted($id)
    {
        $participant = $this->pendaftaranModel->find($id);

        if (!$participant) {
            return $this->response->setJSON(['success' => false, 'message' => 'Peserta tidak ditemukan']);
        }

        if ($this->pendaftaranModel->markCompleted($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Peserta ditandai hadir']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menandai kehadiran']);
        }
    }

    /**
     * Generate certificate for participant
     */
    public function generateSertifikat($id)
    {
        $participant = $this->pendaftaranModel->find($id);

        if (!$participant) {
            return redirect()->back()->with('error', 'Peserta tidak ditemukan');
        }

        if ($participant['status'] !== 'completed') {
            return redirect()->back()->with('error', 'Peserta belum menyelesaikan event');
        }

        // Check if certificate already exists
        $existingCertificate = $this->sertifikatModel->where('pendaftaran_id', $id)->first();
        
        if ($existingCertificate) {
            return redirect()->back()->with('info', 'Sertifikat sudah tersedia');
        }

        // Generate certificate
        $certificate = $this->sertifikatModel->generateSertifikat($id);

        if ($certificate) {
            return redirect()->back()->with('success', 'Sertifikat berhasil digenerate');
        } else {
            return redirect()->back()->with('error', 'Gagal generate sertifikat');
        }
    }

    /**
     * Delete participant
     */
    public function delete($id)
    {
        $participant = $this->pendaftaranModel->find($id);

        if (!$participant) {
            return $this->response->setJSON(['success' => false, 'message' => 'Peserta tidak ditemukan']);
        }

        // Check if event has started
        $event = $this->eventModel->find($participant['event_id']);
        if (strtotime($event['tanggal_mulai']) <= time()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak dapat menghapus peserta event yang sudah berlangsung']);
        }

        // Delete related ticket and certificate
        $ticket = $this->tiketModel->where('pendaftaran_id', $id)->first();
        if ($ticket) {
            $this->tiketModel->delete($ticket['id']);
        }

        $certificate = $this->sertifikatModel->where('pendaftaran_id', $id)->first();
        if ($certificate) {
            $this->sertifikatModel->delete($certificate['id']);
        }

        if ($this->pendaftaranModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Peserta berhasil dihapus']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menghapus peserta']);
        }
    }

    /**
     * Export participants data
     */
    public function export($eventId = null)
    {
        if ($eventId) {
            $participants = $this->pendaftaranModel->getEventParticipants($eventId);
            $event = $this->eventModel->find($eventId);
            $filename = 'peserta_' . $event['nama_event'] . '_' . date('Y-m-d') . '.csv';
        } else {
            $participants = $this->pendaftaranModel->select('pendaftaran.*, events.nama_event')
                                                  ->join('events', 'events.id = pendaftaran.event_id')
                                                  ->findAll();
            $filename = 'semua_peserta_' . date('Y-m-d') . '.csv';
        }

        // Generate CSV
        $csv = "Nama,Email,No HP,Institusi,Event,Status,Tanggal Daftar\n";
        
        foreach ($participants as $participant) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $participant['nama_peserta'],
                $participant['email_peserta'],
                $participant['no_hp_peserta'],
                $participant['institusi_peserta'],
                $participant['nama_event'],
                ucfirst($participant['status']),
                date('d/m/Y H:i', strtotime($participant['created_at']))
            );
        }

        return $this->response->setHeader('Content-Type', 'text/csv')
                            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                            ->setBody($csv);
    }

    /**
     * Bulk actions for participants
     */
    public function bulkAction()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $action = $this->request->getPost('action');
        $participantIds = $this->request->getPost('participant_ids');

        if (!$participantIds || !is_array($participantIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pilih peserta terlebih dahulu']);
        }

        $success = 0;
        $failed = 0;

        foreach ($participantIds as $id) {
            switch ($action) {
                case 'confirm':
                    if ($this->pendaftaranModel->confirmRegistration($id)) {
                        $this->tiketModel->generateTiket($id);
                        $success++;
                    } else {
                        $failed++;
                    }
                    break;

                case 'cancel':
                    if ($this->pendaftaranModel->cancelRegistration($id)) {
                        $success++;
                    } else {
                        $failed++;
                    }
                    break;

                case 'complete':
                    if ($this->pendaftaranModel->markCompleted($id)) {
                        $success++;
                    } else {
                        $failed++;
                    }
                    break;

                case 'generate_certificates':
                    $participant = $this->pendaftaranModel->find($id);
                    if ($participant && $participant['status'] === 'completed') {
                        if ($this->sertifikatModel->generateSertifikat($id)) {
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

        $message = "Berhasil memproses {$success} peserta";
        if ($failed > 0) {
            $message .= ", {$failed} peserta gagal diproses";
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $message,
            'processed' => $success,
            'failed' => $failed
        ]);
    }

    /**
     * Check-in participant via QR scan
     */
    public function checkIn()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $ticketCode = $this->request->getPost('ticket_code');
        
        if (!$ticketCode) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kode tiket tidak valid'
            ]);
        }

        // Scan ticket and mark as completed
        $result = $this->tiketModel->scanTiket($ticketCode);
        
        return $this->response->setJSON($result);
    }

    /**
     * Get participant statistics
     */
    public function getStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $eventId = $this->request->getPost('event_id');
        
        if ($eventId) {
            $stats = $this->pendaftaranModel->getStatistik($eventId);
        } else {
            $stats = $this->pendaftaranModel->getStatistik();
        }

        return $this->response->setJSON($stats);
    }

    /**
     * Send notification to participants
     */
    public function sendNotification()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $participantIds = $this->request->getPost('participant_ids');
        $message = $this->request->getPost('message');
        $subject = $this->request->getPost('subject');

        if (!$participantIds || !$message) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak lengkap']);
        }

        // Implementation depends on email service
        // This is a placeholder for the actual email sending logic
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Notifikasi berhasil dikirim ke ' . count($participantIds) . ' peserta'
        ]);
    }
}