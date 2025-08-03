<?php

namespace App\Controllers\User;

use App\Models\EventModel;
use App\Models\PendaftaranModel;
use App\Models\TiketModel;
use App\Models\SertifikatModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class DashboardController extends Controller
{
    protected $eventModel;
    protected $pendaftaranModel;
    protected $tiketModel;
    protected $sertifikatModel;
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->tiketModel = new TiketModel();
        $this->sertifikatModel = new SertifikatModel();
        $this->userModel = new UserModel();
        $this->session = session();
        helper(['form', 'url']);
    }

    /**
     * User dashboard index
     */
    public function index()
    {
        $userId = $this->session->get('user_id');
        
        // Get user profile with statistics
        $user = $this->userModel->getUserProfile($userId);
        
        // Get user registrations
        $registrations = $this->pendaftaranModel->getUserRegistrations($userId);
        
        // Get user tickets
        $tickets = $this->tiketModel->getUserTiket($userId);
        
        // Get user certificates
        $certificates = $this->sertifikatModel->getUserSertifikat($userId);
        
        // Get upcoming events (for quick registration)
        $upcomingEvents = $this->eventModel->getActiveEvents();
        $upcomingEvents = array_slice($upcomingEvents, 0, 3);
        
        // Calculate statistics
        $stats = [
            'total_registrations' => count($registrations),
            'total_tickets' => count($tickets),
            'total_certificates' => count($certificates),
            'upcoming_events' => count($upcomingEvents)
        ];
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($userId);

        $data = [
            'title' => 'Dashboard - SIEBA',
            'user' => $user,
            'stats' => $stats,
            'registrations' => array_slice($registrations, 0, 5), // Latest 5
            'tickets' => array_slice($tickets, 0, 5), // Latest 5
            'certificates' => array_slice($certificates, 0, 5), // Latest 5
            'upcoming_events' => $upcomingEvents,
            'recent_activities' => $recentActivities
        ];

        return view('user/dashboard', $data);
    }

    /**
     * User profile page
     */
    public function profile()
    {
        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);

        $data = [
            'title' => 'Profil Saya - SIEBA',
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];

        return view('user/profile', $data);
    }

    /**
     * Update user profile
     */
    public function updateProfile()
    {
        $userId = $this->session->get('user_id');
        
        $rules = [
            'nama' => 'required|min_length[3]|max_length[100]',
            'no_hp' => 'required|numeric|min_length[10]|max_length[15]',
            'institusi' => 'permit_empty|max_length[100]',
            'jabatan' => 'permit_empty|max_length[100]',
            'alamat' => 'permit_empty|max_length[255]',
            'tanggal_lahir' => 'permit_empty|valid_date',
            'jenis_kelamin' => 'permit_empty|in_list[L,P]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'no_hp' => $this->request->getPost('no_hp'),
            'institusi' => $this->request->getPost('institusi'),
            'jabatan' => $this->request->getPost('jabatan'),
            'alamat' => $this->request->getPost('alamat'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin')
        ];

        // Handle avatar upload if present
        $avatar = $this->request->getFile('avatar');
        if ($avatar && $avatar->isValid() && !$avatar->hasMoved()) {
            $newName = 'avatar_' . $userId . '_' . time() . '.' . $avatar->getExtension();
            $avatar->move(WRITEPATH . 'uploads/avatars/', $newName);
            $data['avatar'] = 'uploads/avatars/' . $newName;
        }

        if ($this->userModel->updateProfile($userId, $data)) {
            // Update session data
            $this->session->set('nama', $data['nama']);
            
            return redirect()->to('/user/profile')->with('success', 'Profil berhasil diperbarui');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui profil');
        }
    }

    /**
     * Change password form
     */
    public function changePassword()
    {
        $data = [
            'title' => 'Ubah Password - SIEBA',
            'validation' => \Config\Services::validation()
        ];

        return view('user/change_password', $data);
    }

    /**
     * Process change password
     */
    public function processChangePassword()
    {
        $userId = $this->session->get('user_id');
        
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');

        if ($this->userModel->changePassword($userId, $currentPassword, $newPassword)) {
            return redirect()->to('/user/profile')->with('success', 'Password berhasil diubah');
        } else {
            return redirect()->back()->with('error', 'Password lama tidak sesuai');
        }
    }

    /**
     * My events - list of registered events
     */
    public function myEvents()
    {
        $userId = $this->session->get('user_id');
        $registrations = $this->pendaftaranModel->getUserRegistrations($userId);

        $data = [
            'title' => 'Event Saya - SIEBA',
            'registrations' => $registrations
        ];

        return view('user/my_events', $data);
    }

    /**
     * My tickets
     */
    public function myTickets()
    {
        $userId = $this->session->get('user_id');
        $tickets = $this->tiketModel->getUserTiket($userId);

        $data = [
            'title' => 'Tiket Saya - SIEBA',
            'tickets' => $tickets
        ];

        return view('user/my_tickets', $data);
    }

    /**
     * My certificates
     */
    public function myCertificates()
    {
        $userId = $this->session->get('user_id');
        $certificates = $this->sertifikatModel->getUserSertifikat($userId);

        $data = [
            'title' => 'Sertifikat Saya - SIEBA',
            'certificates' => $certificates
        ];

        return view('user/my_certificates', $data);
    }

    /**
     * Get user statistics for dashboard widgets
     */
    public function getStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $userId = $this->session->get('user_id');
        
        $registrations = $this->pendaftaranModel->getUserRegistrations($userId);
        $tickets = $this->tiketModel->getUserTiket($userId);
        $certificates = $this->sertifikatModel->getUserSertifikat($userId);

        // Count by status
        $registrationsByStatus = [];
        foreach ($registrations as $reg) {
            $status = $reg['status'];
            $registrationsByStatus[$status] = ($registrationsByStatus[$status] ?? 0) + 1;
        }

        $ticketsByStatus = [];
        foreach ($tickets as $ticket) {
            $status = $ticket['status'];
            $ticketsByStatus[$status] = ($ticketsByStatus[$status] ?? 0) + 1;
        }

        return $this->response->setJSON([
            'registrations' => $registrationsByStatus,
            'tickets' => $ticketsByStatus,
            'certificates' => count($certificates),
            'total_events' => count($registrations)
        ]);
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($userId)
    {
        $activities = [];

        // Get recent registrations
        $recentRegistrations = $this->pendaftaranModel->where('user_id', $userId)
                                                    ->orderBy('created_at', 'DESC')
                                                    ->limit(5)
                                                    ->findAll();

        foreach ($recentRegistrations as $reg) {
            $activities[] = [
                'type' => 'registration',
                'message' => 'Mendaftar event: ' . $reg['nama_event'] ?? 'Event',
                'date' => $reg['created_at'],
                'icon' => 'fas fa-calendar-plus',
                'color' => 'primary'
            ];
        }

        // Get recent tickets
        $recentTickets = $this->tiketModel->select('tiket.*, pendaftaran.nama_peserta, events.nama_event')
                                        ->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                                        ->join('events', 'events.id = pendaftaran.event_id')
                                        ->where('pendaftaran.user_id', $userId)
                                        ->orderBy('tiket.created_at', 'DESC')
                                        ->limit(3)
                                        ->findAll();

        foreach ($recentTickets as $ticket) {
            $activities[] = [
                'type' => 'ticket',
                'message' => 'Tiket digenerate untuk: ' . $ticket['nama_event'],
                'date' => $ticket['created_at'],
                'icon' => 'fas fa-ticket-alt',
                'color' => 'success'
            ];
        }

        // Sort by date
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Export user data
     */
    public function exportData()
    {
        $userId = $this->session->get('user_id');
        
        $userData = [
            'profile' => $this->userModel->getUserProfile($userId),
            'registrations' => $this->pendaftaranModel->getUserRegistrations($userId),
            'tickets' => $this->tiketModel->getUserTiket($userId),
            'certificates' => $this->sertifikatModel->getUserSertifikat($userId)
        ];

        $filename = 'sieba_data_' . $userId . '_' . date('Y-m-d') . '.json';
        
        return $this->response->setHeader('Content-Type', 'application/json')
                            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                            ->setBody(json_encode($userData, JSON_PRETTY_PRINT));
    }

    /**
     * Settings page
     */
    public function settings()
    {
        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);

        $data = [
            'title' => 'Pengaturan - SIEBA',
            'user' => $user
        ];

        return view('user/settings', $data);
    }
}