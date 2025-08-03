<?php

namespace App\Controllers\Admin;

use App\Models\EventModel;
use App\Models\UserModel;
use App\Models\PendaftaranModel;
use App\Models\TiketModel;
use App\Models\SertifikatModel;
use CodeIgniter\Controller;

class LaporanController extends Controller
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
     * Main reports dashboard
     */
    public function index()
    {
        $dateRange = $this->request->getGet('date_range') ?: 'last_30_days';
        $eventId = $this->request->getGet('event_id');

        // Calculate date range
        $dates = $this->getDateRange($dateRange);

        // Get general statistics
        $stats = [
            'events' => $this->getEventStatistics($dates, $eventId),
            'registrations' => $this->getRegistrationStatistics($dates, $eventId),
            'tickets' => $this->getTicketStatistics($dates, $eventId),
            'certificates' => $this->getCertificateStatistics($dates, $eventId),
            'users' => $this->getUserStatistics($dates)
        ];

        // Get chart data
        $chartData = [
            'registrations_over_time' => $this->getRegistrationsOverTime($dates, $eventId),
            'events_by_category' => $this->getEventsByCategory($dates),
            'registration_status_distribution' => $this->getRegistrationStatusDistribution($dates, $eventId),
            'popular_events' => $this->getPopularEvents($dates)
        ];

        // Get events for filter
        $events = $this->eventModel->select('id, nama_event')->findAll();

        $data = [
            'title' => 'Laporan & Statistik - SIEBA',
            'stats' => $stats,
            'chart_data' => $chartData,
            'events' => $events,
            'current_date_range' => $dateRange,
            'current_event_id' => $eventId,
            'date_range_text' => $this->getDateRangeText($dateRange)
        ];

        return view('admin/laporan', $data);
    }

    /**
     * Event report
     */
    public function event($id = null)
    {
        if ($id) {
            $event = $this->eventModel->find($id);
            if (!$event) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

            // Get event-specific statistics
            $participants = $this->pendaftaranModel->getEventParticipants($id);
            $tickets = $this->tiketModel->getEventTiket($id);
            $certificates = $this->sertifikatModel->getEventSertifikat($id);

            $stats = [
                'total_participants' => count($participants),
                'confirmed_participants' => count(array_filter($participants, fn($p) => $p['status'] === 'confirmed')),
                'completed_participants' => count(array_filter($participants, fn($p) => $p['status'] === 'completed')),
                'total_tickets' => count($tickets),
                'used_tickets' => count(array_filter($tickets, fn($t) => $t['status'] === 'used')),
                'total_certificates' => count($certificates),
                'downloaded_certificates' => count(array_filter($certificates, fn($c) => $c['status'] === 'downloaded'))
            ];

            // Calculate revenue
            $revenue = $event['biaya'] * $stats['confirmed_participants'];

            // Registration timeline
            $registrationTimeline = $this->getEventRegistrationTimeline($id);

            $data = [
                'title' => 'Laporan Event: ' . $event['nama_event'],
                'event' => $event,
                'stats' => $stats,
                'revenue' => $revenue,
                'participants' => $participants,
                'tickets' => $tickets,
                'certificates' => $certificates,
                'registration_timeline' => $registrationTimeline
            ];

            return view('admin/laporan_event', $data);
        } else {
            // Show all events report
            $events = $this->eventModel->select('events.*, COUNT(pendaftaran.id) as total_participants')
                                     ->join('pendaftaran', 'pendaftaran.event_id = events.id', 'left')
                                     ->groupBy('events.id')
                                     ->orderBy('events.created_at', 'DESC')
                                     ->findAll();

            $data = [
                'title' => 'Laporan Semua Event - SIEBA',
                'events' => $events
            ];

            return view('admin/laporan_semua_event', $data);
        }
    }

    /**
     * Financial report
     */
    public function financial()
    {
        $dateRange = $this->request->getGet('date_range') ?: 'last_30_days';
        $dates = $this->getDateRange($dateRange);

        // Get paid events
        $paidEvents = $this->eventModel->where('biaya >', 0)
                                      ->where('created_at >=', $dates['start'])
                                      ->where('created_at <=', $dates['end'])
                                      ->findAll();

        $financialData = [];
        $totalRevenue = 0;

        foreach ($paidEvents as $event) {
            $confirmedParticipants = $this->pendaftaranModel->where('event_id', $event['id'])
                                                           ->where('status', 'confirmed')
                                                           ->countAllResults();
            
            $revenue = $event['biaya'] * $confirmedParticipants;
            $totalRevenue += $revenue;

            $financialData[] = [
                'event' => $event,
                'participants' => $confirmedParticipants,
                'revenue' => $revenue
            ];
        }

        // Monthly revenue trend
        $monthlyRevenue = $this->getMonthlyRevenue($dates);

        $data = [
            'title' => 'Laporan Keuangan - SIEBA',
            'financial_data' => $financialData,
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'current_date_range' => $dateRange,
            'date_range_text' => $this->getDateRangeText($dateRange)
        ];

        return view('admin/laporan_keuangan', $data);
    }

    /**
     * Export report
     */
    public function export()
    {
        $type = $this->request->getGet('type') ?: 'general';
        $format = $this->request->getGet('format') ?: 'pdf';
        $dateRange = $this->request->getGet('date_range') ?: 'last_30_days';
        $eventId = $this->request->getGet('event_id');

        $dates = $this->getDateRange($dateRange);

        switch ($type) {
            case 'event':
                return $this->exportEventReport($eventId, $format);
            case 'financial':
                return $this->exportFinancialReport($dates, $format);
            case 'participants':
                return $this->exportParticipantsReport($dates, $eventId, $format);
            default:
                return $this->exportGeneralReport($dates, $format);
        }
    }

    /**
     * Get statistics data via AJAX
     */
    public function getStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $dateRange = $this->request->getPost('date_range') ?: 'last_30_days';
        $eventId = $this->request->getPost('event_id');

        $dates = $this->getDateRange($dateRange);

        $stats = [
            'events' => $this->getEventStatistics($dates, $eventId),
            'registrations' => $this->getRegistrationStatistics($dates, $eventId),
            'tickets' => $this->getTicketStatistics($dates, $eventId),
            'certificates' => $this->getCertificateStatistics($dates, $eventId)
        ];

        return $this->response->setJSON($stats);
    }

    /**
     * Private helper methods
     */
    private function getDateRange($range)
    {
        $end = date('Y-m-d H:i:s');
        
        switch ($range) {
            case 'today':
                $start = date('Y-m-d 00:00:00');
                break;
            case 'yesterday':
                $start = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $end = date('Y-m-d 23:59:59', strtotime('-1 day'));
                break;
            case 'last_7_days':
                $start = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case 'last_30_days':
                $start = date('Y-m-d 00:00:00', strtotime('-30 days'));
                break;
            case 'this_month':
                $start = date('Y-m-01 00:00:00');
                break;
            case 'last_month':
                $start = date('Y-m-01 00:00:00', strtotime('-1 month'));
                $end = date('Y-m-t 23:59:59', strtotime('-1 month'));
                break;
            case 'this_year':
                $start = date('Y-01-01 00:00:00');
                break;
            default:
                $start = date('Y-m-d 00:00:00', strtotime('-30 days'));
        }

        return ['start' => $start, 'end' => $end];
    }

    private function getDateRangeText($range)
    {
        $texts = [
            'today' => 'Hari Ini',
            'yesterday' => 'Kemarin',
            'last_7_days' => '7 Hari Terakhir',
            'last_30_days' => '30 Hari Terakhir',
            'this_month' => 'Bulan Ini',
            'last_month' => 'Bulan Lalu',
            'this_year' => 'Tahun Ini'
        ];

        return $texts[$range] ?? '30 Hari Terakhir';
    }

    private function getEventStatistics($dates, $eventId = null)
    {
        $builder = $this->eventModel->where('created_at >=', $dates['start'])
                                   ->where('created_at <=', $dates['end']);

        if ($eventId) {
            $builder = $builder->where('id', $eventId);
        }

        $total = $builder->countAllResults(false);
        $published = $builder->where('status', 'published')->countAllResults(false);
        $completed = $builder->where('status', 'completed')->countAllResults();

        return [
            'total' => $total,
            'published' => $published,
            'completed' => $completed,
            'draft' => $total - $published - $completed
        ];
    }

    private function getRegistrationStatistics($dates, $eventId = null)
    {
        $builder = $this->pendaftaranModel->where('created_at >=', $dates['start'])
                                        ->where('created_at <=', $dates['end']);

        if ($eventId) {
            $builder = $builder->where('event_id', $eventId);
        }

        $total = $builder->countAllResults(false);
        $confirmed = $builder->where('status', 'confirmed')->countAllResults(false);
        $completed = $builder->where('status', 'completed')->countAllResults(false);
        $pending = $builder->where('status', 'pending')->countAllResults();

        return [
            'total' => $total,
            'confirmed' => $confirmed,
            'completed' => $completed,
            'pending' => $pending
        ];
    }

    private function getTicketStatistics($dates, $eventId = null)
    {
        $builder = $this->tiketModel->where('created_at >=', $dates['start'])
                                   ->where('created_at <=', $dates['end']);

        if ($eventId) {
            $builder = $builder->join('pendaftaran', 'pendaftaran.id = tiket.pendaftaran_id')
                              ->where('pendaftaran.event_id', $eventId);
        }

        $total = $builder->countAllResults(false);
        $used = $builder->where('tiket.status', 'used')->countAllResults();

        return [
            'total' => $total,
            'used' => $used,
            'active' => $total - $used
        ];
    }

    private function getCertificateStatistics($dates, $eventId = null)
    {
        $builder = $this->sertifikatModel->where('created_at >=', $dates['start'])
                                        ->where('created_at <=', $dates['end']);

        if ($eventId) {
            $builder = $builder->join('pendaftaran', 'pendaftaran.id = sertifikat.pendaftaran_id')
                              ->where('pendaftaran.event_id', $eventId);
        }

        $total = $builder->countAllResults(false);
        $downloaded = $builder->where('sertifikat.status', 'downloaded')->countAllResults();

        return [
            'total' => $total,
            'downloaded' => $downloaded,
            'pending' => $total - $downloaded
        ];
    }

    private function getUserStatistics($dates)
    {
        $total = $this->userModel->where('created_at >=', $dates['start'])
                               ->where('created_at <=', $dates['end'])
                               ->countAllResults();

        $verified = $this->userModel->where('created_at >=', $dates['start'])
                                  ->where('created_at <=', $dates['end'])
                                  ->where('is_verified', true)
                                  ->countAllResults();

        return [
            'total' => $total,
            'verified' => $verified,
            'unverified' => $total - $verified
        ];
    }

    private function getRegistrationsOverTime($dates, $eventId = null)
    {
        // Implementation for getting registration data over time
        // This would return data for charts
        return [];
    }

    private function getEventsByCategory($dates)
    {
        return $this->eventModel->select('kategori, COUNT(*) as count')
                               ->where('created_at >=', $dates['start'])
                               ->where('created_at <=', $dates['end'])
                               ->groupBy('kategori')
                               ->findAll();
    }

    private function getRegistrationStatusDistribution($dates, $eventId = null)
    {
        $builder = $this->pendaftaranModel->select('status, COUNT(*) as count')
                                        ->where('created_at >=', $dates['start'])
                                        ->where('created_at <=', $dates['end']);

        if ($eventId) {
            $builder = $builder->where('event_id', $eventId);
        }

        return $builder->groupBy('status')->findAll();
    }

    private function getPopularEvents($dates)
    {
        return $this->eventModel->select('events.nama_event, COUNT(pendaftaran.id) as total_participants')
                               ->join('pendaftaran', 'pendaftaran.event_id = events.id', 'left')
                               ->where('events.created_at >=', $dates['start'])
                               ->where('events.created_at <=', $dates['end'])
                               ->groupBy('events.id')
                               ->orderBy('total_participants', 'DESC')
                               ->limit(10)
                               ->findAll();
    }

    private function getEventRegistrationTimeline($eventId)
    {
        // Get daily registration count for the event
        return $this->pendaftaranModel->select('DATE(created_at) as date, COUNT(*) as count')
                                     ->where('event_id', $eventId)
                                     ->groupBy('DATE(created_at)')
                                     ->orderBy('date', 'ASC')
                                     ->findAll();
    }

    private function getMonthlyRevenue($dates)
    {
        // Implementation for monthly revenue calculation
        return [];
    }

    private function exportGeneralReport($dates, $format)
    {
        // Implementation for exporting general report
        return $this->response->setJSON(['message' => 'Export functionality coming soon']);
    }

    private function exportEventReport($eventId, $format)
    {
        // Implementation for exporting event report
        return $this->response->setJSON(['message' => 'Export functionality coming soon']);
    }

    private function exportFinancialReport($dates, $format)
    {
        // Implementation for exporting financial report
        return $this->response->setJSON(['message' => 'Export functionality coming soon']);
    }

    private function exportParticipantsReport($dates, $eventId, $format)
    {
        // Implementation for exporting participants report
        return $this->response->setJSON(['message' => 'Export functionality coming soon']);
    }
}