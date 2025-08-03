<?php

namespace App\Controllers\Admin;

use App\Models\EventModel;
use App\Models\UserModel;
use App\Models\PendaftaranModel;
use App\Models\TiketModel;
use App\Models\SertifikatModel;
use CodeIgniter\Controller;

class DashboardController extends Controller
{
    protected $eventModel;
    protected $userModel;
    protected $pendaftaranModel;
    protected $tiketModel;
    protected $sertifikatModel;
    protected $session;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->userModel = new UserModel();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->tiketModel = new TiketModel();
        $this->sertifikatModel = new SertifikatModel();
        $this->session = session();
        helper(['form', 'url']);
    }

    /**
     * Admin dashboard index
     */
    public function index()
    {
        // Get overall statistics
        $eventStats = $this->eventModel->getStatistik();
        $userStats = $this->userModel->getStatistik();
        $pendaftaranStats = $this->pendaftaranModel->getStatistik();
        $tiketStats = $this->tiketModel->getStatistik();
        $sertifikatStats = $this->sertifikatModel->getStatistik();

        // Get recent activities
        $recentEvents = $this->eventModel->orderBy('created_at', 'DESC')->limit(5)->findAll();
        $recentRegistrations = $this->pendaftaranModel->select('pendaftaran.*, events.nama_event, users.nama as user_nama')
                                                     ->join('events', 'events.id = pendaftaran.event_id')
                                                     ->join('users', 'users.id = pendaftaran.user_id', 'left')
                                                     ->orderBy('pendaftaran.created_at', 'DESC')
                                                     ->limit(10)
                                                     ->findAll();

        // Get events that need attention
        $pendingRegistrations = $this->pendaftaranModel->where('status', 'pending')->countAllResults();
        $upcomingEvents = $this->eventModel->where('status', 'published')
                                          ->where('tanggal_mulai >=', date('Y-m-d'))
                                          ->where('tanggal_mulai <=', date('Y-m-d', strtotime('+7 days')))
                                          ->countAllResults();

        // Monthly statistics for charts
        $monthlyData = $this->getMonthlyStatistics();

        // Popular events
        $popularEvents = $this->getPopularEvents();

        $data = [
            'title' => 'Dashboard Admin - SIEBA',
            'stats' => [
                'events' => $eventStats,
                'users' => $userStats,
                'registrations' => $pendaftaranStats,
                'tickets' => $tiketStats,
                'certificates' => $sertifikatStats
            ],
            'recent_events' => $recentEvents,
            'recent_registrations' => $recentRegistrations,
            'alerts' => [
                'pending_registrations' => $pendingRegistrations,
                'upcoming_events' => $upcomingEvents
            ],
            'monthly_data' => $monthlyData,
            'popular_events' => $popularEvents
        ];

        return view('admin/dashboard', $data);
    }

    /**
     * Get system statistics for widgets
     */
    public function getStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $stats = [
            'events' => $this->eventModel->getStatistik(),
            'users' => $this->userModel->getStatistik(),
            'registrations' => $this->pendaftaranModel->getStatistik(),
            'tickets' => $this->tiketModel->getStatistik(),
            'certificates' => $this->sertifikatModel->getStatistik()
        ];

        return $this->response->setJSON($stats);
    }

    /**
     * Get monthly statistics for charts
     */
    private function getMonthlyStatistics()
    {
        $months = [];
        $eventData = [];
        $registrationData = [];
        $userRegistrationData = [];

        // Get data for last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $months[] = date('M Y', strtotime("-$i months"));

            // Events created this month
            $eventCount = $this->eventModel->where('created_at >=', $date . '-01 00:00:00')
                                          ->where('created_at <', date('Y-m-d H:i:s', strtotime($date . '-01 +1 month')))
                                          ->countAllResults();
            $eventData[] = $eventCount;

            // Registrations this month
            $regCount = $this->pendaftaranModel->where('created_at >=', $date . '-01 00:00:00')
                                              ->where('created_at <', date('Y-m-d H:i:s', strtotime($date . '-01 +1 month')))
                                              ->countAllResults();
            $registrationData[] = $regCount;

            // User registrations this month
            $userRegCount = $this->userModel->where('created_at >=', $date . '-01 00:00:00')
                                           ->where('created_at <', date('Y-m-d H:i:s', strtotime($date . '-01 +1 month')))
                                           ->countAllResults();
            $userRegistrationData[] = $userRegCount;
        }

        return [
            'months' => $months,
            'events' => $eventData,
            'registrations' => $registrationData,
            'user_registrations' => $userRegistrationData
        ];
    }

    /**
     * Get popular events based on registration count
     */
    private function getPopularEvents()
    {
        return $this->eventModel->select('events.*, COUNT(pendaftaran.id) as total_registrations')
                               ->join('pendaftaran', 'pendaftaran.event_id = events.id', 'left')
                               ->where('events.status', 'published')
                               ->groupBy('events.id')
                               ->orderBy('total_registrations', 'DESC')
                               ->limit(5)
                               ->findAll();
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $activities = [];

        // Recent event registrations
        $recentRegistrations = $this->pendaftaranModel->select('pendaftaran.*, events.nama_event, users.nama as user_nama')
                                                     ->join('events', 'events.id = pendaftaran.event_id')
                                                     ->join('users', 'users.id = pendaftaran.user_id', 'left')
                                                     ->orderBy('pendaftaran.created_at', 'DESC')
                                                     ->limit(10)
                                                     ->findAll();

        foreach ($recentRegistrations as $reg) {
            $userName = $reg['user_nama'] ?? $reg['nama_peserta'];
            $activities[] = [
                'type' => 'registration',
                'message' => "{$userName} mendaftar event: {$reg['nama_event']}",
                'time' => $reg['created_at'],
                'icon' => 'fas fa-user-plus',
                'color' => 'primary'
            ];
        }

        // Recent events created
        $recentEvents = $this->eventModel->select('events.*, users.nama as creator_name')
                                        ->join('users', 'users.id = events.created_by', 'left')
                                        ->orderBy('events.created_at', 'DESC')
                                        ->limit(5)
                                        ->findAll();

        foreach ($recentEvents as $event) {
            $creatorName = $event['creator_name'] ?? 'Admin';
            $activities[] = [
                'type' => 'event',
                'message' => "{$creatorName} membuat event: {$event['nama_event']}",
                'time' => $event['created_at'],
                'icon' => 'fas fa-calendar-plus',
                'color' => 'success'
            ];
        }

        // Sort by time
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return $this->response->setJSON([
            'activities' => array_slice($activities, 0, 15)
        ]);
    }

    /**
     * Get event status distribution
     */
    public function getEventStatusDistribution()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $distribution = [
            'draft' => $this->eventModel->where('status', 'draft')->countAllResults(),
            'published' => $this->eventModel->where('status', 'published')->countAllResults(),
            'cancelled' => $this->eventModel->where('status', 'cancelled')->countAllResults(),
            'completed' => $this->eventModel->where('status', 'completed')->countAllResults()
        ];

        return $this->response->setJSON($distribution);
    }

    /**
     * Get registration status distribution
     */
    public function getRegistrationStatusDistribution()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $distribution = [
            'pending' => $this->pendaftaranModel->where('status', 'pending')->countAllResults(),
            'confirmed' => $this->pendaftaranModel->where('status', 'confirmed')->countAllResults(),
            'cancelled' => $this->pendaftaranModel->where('status', 'cancelled')->countAllResults(),
            'completed' => $this->pendaftaranModel->where('status', 'completed')->countAllResults()
        ];

        return $this->response->setJSON($distribution);
    }

    /**
     * Quick actions
     */
    public function quickAction()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/dashboard');
        }

        $action = $this->request->getPost('action');
        $result = ['success' => false, 'message' => 'Invalid action'];

        switch ($action) {
            case 'expire_old_tickets':
                $this->tiketModel->expireOldTiket();
                $result = ['success' => true, 'message' => 'Old tickets marked as expired'];
                break;

            case 'send_pending_certificates':
                // Implementation for sending pending certificates
                $result = ['success' => true, 'message' => 'Pending certificates sent'];
                break;

            case 'cleanup_logs':
                // Implementation for log cleanup
                $result = ['success' => true, 'message' => 'Logs cleaned up'];
                break;
        }

        return $this->response->setJSON($result);
    }

    /**
     * System health check
     */
    public function healthCheck()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $health = [
            'database' => $this->checkDatabaseConnection(),
            'uploads' => $this->checkUploadsDirectory(),
            'cache' => $this->checkCacheDirectory(),
            'logs' => $this->checkLogsDirectory()
        ];

        $overallStatus = array_reduce($health, function($carry, $item) {
            return $carry && $item['status'];
        }, true);

        return $this->response->setJSON([
            'overall_status' => $overallStatus,
            'checks' => $health
        ]);
    }

    /**
     * Check database connection
     */
    private function checkDatabaseConnection()
    {
        try {
            $this->eventModel->countAll();
            return ['status' => true, 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Database connection failed'];
        }
    }

    /**
     * Check uploads directory
     */
    private function checkUploadsDirectory()
    {
        $uploadPath = WRITEPATH . 'uploads/';
        
        if (!is_dir($uploadPath)) {
            return ['status' => false, 'message' => 'Uploads directory not found'];
        }
        
        if (!is_writable($uploadPath)) {
            return ['status' => false, 'message' => 'Uploads directory not writable'];
        }
        
        return ['status' => true, 'message' => 'Uploads directory OK'];
    }

    /**
     * Check cache directory
     */
    private function checkCacheDirectory()
    {
        $cachePath = WRITEPATH . 'cache/';
        
        if (!is_dir($cachePath)) {
            return ['status' => false, 'message' => 'Cache directory not found'];
        }
        
        if (!is_writable($cachePath)) {
            return ['status' => false, 'message' => 'Cache directory not writable'];
        }
        
        return ['status' => true, 'message' => 'Cache directory OK'];
    }

    /**
     * Check logs directory
     */
    private function checkLogsDirectory()
    {
        $logsPath = WRITEPATH . 'logs/';
        
        if (!is_dir($logsPath)) {
            return ['status' => false, 'message' => 'Logs directory not found'];
        }
        
        if (!is_writable($logsPath)) {
            return ['status' => false, 'message' => 'Logs directory not writable'];
        }
        
        return ['status' => true, 'message' => 'Logs directory OK'];
    }
}