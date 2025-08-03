<?php

namespace App\Controllers\Public;

use App\Models\EventModel;
use CodeIgniter\Controller;

class Home extends Controller
{
    protected $eventModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        helper(['form', 'url']);
    }

    /**
     * Halaman beranda
     */
    public function index()
    {
        // Get featured/upcoming events (max 6)
        $featuredEvents = $this->eventModel->getActiveEvents();
        $featuredEvents = array_slice($featuredEvents, 0, 6);

        // Get event statistics
        $stats = $this->eventModel->getStatistik();

        $data = [
            'title' => 'SIEBA - Sistem Event dan Berbagi',
            'featured_events' => $featuredEvents,
            'stats' => $stats
        ];

        return view('public/home', $data);
    }

    /**
     * Halaman telusuri event
     */
    public function telusuri()
    {
        $request = \Config\Services::request();
        
        // Get search parameters
        $search = $request->getGet('search') ?? $request->getPost('search');
        $kategori = $request->getGet('kategori') ?? $request->getPost('kategori');
        $perPage = 12;

        // Get events with pagination
        $events = $this->eventModel->getEventsWithPagination($perPage, $kategori, $search);
        
        // Get available categories
        $categories = [
            'seminar' => 'Seminar',
            'workshop' => 'Workshop', 
            'webinar' => 'Webinar',
            'training' => 'Training',
            'conference' => 'Conference'
        ];

        $data = [
            'title' => 'Telusuri Event - SIEBA',
            'events' => $events,
            'pager' => $this->eventModel->pager,
            'categories' => $categories,
            'current_search' => $search,
            'current_kategori' => $kategori,
            'total_events' => $this->eventModel->where('status', 'published')
                                              ->where('tanggal_selesai >=', date('Y-m-d'))
                                              ->countAllResults()
        ];

        return view('public/telusuri_event', $data);
    }

    /**
     * API endpoint for searching events (AJAX)
     */
    public function searchEventsAPI()
    {
        $request = \Config\Services::request();
        
        if (!$request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $search = $request->getPost('search');
        $kategori = $request->getPost('kategori');
        $page = $request->getPost('page') ?? 1;
        
        $events = $this->eventModel->getEventsWithPagination(12, $kategori, $search);

        $html = '';
        foreach ($events as $event) {
            $html .= view('components/card_event', ['event' => $event]);
        }

        return $this->response->setJSON([
            'success' => true,
            'html' => $html,
            'pagination' => $this->eventModel->pager->links()
        ]);
    }

    /**
     * Get event stats for homepage
     */
    public function getStats()
    {
        $stats = $this->eventModel->getStatistik();
        
        // Add participant count
        $pendaftaranModel = new \App\Models\PendaftaranModel();
        $stats['total_participants'] = $pendaftaranModel->where('status', 'confirmed')
                                                       ->orWhere('status', 'completed')
                                                       ->countAllResults();

        return $this->response->setJSON($stats);
    }

    /**
     * Get upcoming events for calendar widget
     */
    public function getUpcomingEvents($limit = 5)
    {
        $events = $this->eventModel->where('status', 'published')
                                  ->where('tanggal_mulai >=', date('Y-m-d'))
                                  ->orderBy('tanggal_mulai', 'ASC')
                                  ->limit($limit)
                                  ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'events' => $events
        ]);
    }

    /**
     * Get events by category
     */
    public function getEventsByCategory($kategori = null)
    {
        if (!$kategori) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Category required']);
        }

        $events = $this->eventModel->where('status', 'published')
                                  ->where('kategori', $kategori)
                                  ->where('tanggal_selesai >=', date('Y-m-d'))
                                  ->orderBy('tanggal_mulai', 'ASC')
                                  ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'category' => $kategori,
            'events' => $events
        ]);
    }

    /**
     * Search suggestions for autocomplete
     */
    public function getSearchSuggestions()
    {
        $request = \Config\Services::request();
        
        if (!$request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $query = $request->getPost('query');
        
        if (strlen($query) < 2) {
            return $this->response->setJSON(['suggestions' => []]);
        }

        // Search in event names and locations
        $suggestions = $this->eventModel->select('nama_event, lokasi')
                                       ->where('status', 'published')
                                       ->groupStart()
                                           ->like('nama_event', $query)
                                           ->orLike('lokasi', $query)
                                       ->groupEnd()
                                       ->limit(10)
                                       ->findAll();

        $result = [];
        foreach ($suggestions as $suggestion) {
            $result[] = $suggestion['nama_event'];
            if (!in_array($suggestion['lokasi'], $result)) {
                $result[] = $suggestion['lokasi'];
            }
        }

        return $this->response->setJSON([
            'suggestions' => array_unique(array_slice($result, 0, 10))
        ]);
    }

    /**
     * About page
     */
    public function about()
    {
        $data = [
            'title' => 'Tentang SIEBA'
        ];

        return view('public/about', $data);
    }

    /**
     * Contact page  
     */
    public function contact()
    {
        $data = [
            'title' => 'Kontak - SIEBA'
        ];

        return view('public/contact', $data);
    }

    /**
     * FAQ page
     */
    public function faq()
    {
        $data = [
            'title' => 'FAQ - SIEBA'
        ];

        return view('public/faq', $data);
    }
}