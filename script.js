// Global Variables
let events = [];
let filteredEvents = [];
let eventsPerPage = 6;
let currentPage = 1;

// Sample Event Data
const sampleEvents = [
    {
        id: 1,
        name: "Workshop Digital Marketing untuk UMKM",
        category: "workshop",
        date: "2024-12-25",
        time: "09:00",
        location: "Gedung Serbaguna Banda Aceh",
        description: "Pelatihan digital marketing khusus untuk pelaku UMKM di Banda Aceh. Pelajari strategi promosi online yang efektif untuk meningkatkan penjualan.",
        price: 0,
        contact: "081234567890",
        status: "upcoming",
        image: "Workshop Digital Marketing"
    },
    {
        id: 2,
        name: "Seminar Kewirausahaan Muda",
        category: "seminar",
        date: "2024-12-28",
        time: "14:00",
        location: "Aula Universitas Syiah Kuala",
        description: "Seminar inspiratif tentang kewirausahaan untuk generasi muda. Dibawakan oleh pengusaha sukses dari Aceh.",
        price: 25000,
        contact: "info@seminar-aceh.com",
        status: "upcoming",
        image: "Seminar Kewirausahaan"
    },
    {
        id: 3,
        name: "Festival Budaya Aceh 2024",
        category: "budaya",
        date: "2024-12-30",
        time: "16:00",
        location: "Taman Budaya Banda Aceh",
        description: "Festival budaya tahunan yang menampilkan seni tradisional Aceh, tarian, musik, dan kuliner khas daerah.",
        price: 0,
        contact: "festival@budayaaceh.org",
        status: "upcoming",
        image: "Festival Budaya Aceh"
    },
    {
        id: 4,
        name: "Turnamen Futsal Ramadhan Cup",
        category: "olahraga",
        date: "2025-01-05",
        time: "08:00",
        location: "GOR Futsal Banda Aceh",
        description: "Turnamen futsal antar tim se-Banda Aceh. Terbuka untuk semua kalangan dengan hadiah total jutaan rupiah.",
        price: 150000,
        contact: "081298765432",
        status: "upcoming",
        image: "Turnamen Futsal"
    },
    {
        id: 5,
        name: "Konser Musik Tradisional",
        category: "konser",
        date: "2025-01-10",
        time: "19:30",
        location: "Pendopo Gubernur Aceh",
        description: "Konser musik tradisional Aceh yang menampilkan seniman lokal terbaik. Malam yang penuh dengan alunan musik khas Serambi Mekkah.",
        price: 50000,
        contact: "konser@acehmusic.id",
        status: "upcoming",
        image: "Konser Musik Tradisional"
    },
    {
        id: 6,
        name: "Hackathon Smart City Banda Aceh",
        category: "teknologi",
        date: "2025-01-15",
        time: "09:00",
        location: "Techno Park Banda Aceh",
        description: "Kompetisi 48 jam untuk mengembangkan solusi teknologi bagi smart city Banda Aceh. Terbuka untuk developer, designer, dan mahasiswa.",
        price: 0,
        contact: "hackathon@smartcity-aceh.id",
        status: "upcoming",
        image: "Hackathon Smart City"
    }
];

// Initialize App
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Load sample data
    events = [...sampleEvents];
    filteredEvents = [...events];
    
    // Render initial events
    renderEvents();
    
    // Initialize event listeners
    initializeEventListeners();
    
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Initialize smooth scrolling
    initializeSmoothScrolling();
    
    // Add animation classes to elements as they come into view
    observeElements();
}

// Event Listeners
function initializeEventListeners() {
    // Search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(searchEvents, 300));
    }
    
    // Filter selects
    const filters = ['categoryFilter', 'dateFilter', 'statusFilter'];
    filters.forEach(filterId => {
        const filter = document.getElementById(filterId);
        if (filter) {
            filter.addEventListener('change', filterEvents);
        }
    });
    
    // Modal close events
    window.addEventListener('click', function(event) {
        const eventModal = document.getElementById('eventModal');
        const eventDetailModal = document.getElementById('eventDetailModal');
        
        if (event.target === eventModal) {
            closeEventModal();
        }
        if (event.target === eventDetailModal) {
            closeEventDetailModal();
        }
    });
    
    // Escape key to close modals
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeEventModal();
            closeEventDetailModal();
        }
    });
}

// Mobile Menu
function initializeMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenu && navMenu) {
        mobileMenu.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            
            // Animate hamburger menu
            const bars = mobileMenu.querySelectorAll('.bar');
            bars.forEach((bar, index) => {
                bar.style.transform = navMenu.classList.contains('active') 
                    ? `rotate(${index === 0 ? '45deg' : index === 1 ? '0deg' : '-45deg'})` 
                    : 'rotate(0deg)';
                if (index === 1) {
                    bar.style.opacity = navMenu.classList.contains('active') ? '0' : '1';
                }
            });
        });
    }
}

// Smooth Scrolling
function initializeSmoothScrolling() {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            scrollToSection(targetId);
        });
    });
}

function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        const headerHeight = document.querySelector('.header').offsetHeight;
        const targetPosition = section.offsetTop - headerHeight - 20;
        
        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
        
        // Close mobile menu if open
        const navMenu = document.querySelector('.nav-menu');
        if (navMenu && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenu) {
                const bars = mobileMenu.querySelectorAll('.bar');
                bars.forEach((bar, index) => {
                    bar.style.transform = 'rotate(0deg)';
                    if (index === 1) {
                        bar.style.opacity = '1';
                    }
                });
            }
        }
    }
}

// Event Rendering
function renderEvents() {
    const eventsGrid = document.getElementById('eventsGrid');
    if (!eventsGrid) return;
    
    const startIndex = (currentPage - 1) * eventsPerPage;
    const endIndex = startIndex + eventsPerPage;
    const eventsToShow = filteredEvents.slice(0, endIndex);
    
    if (eventsToShow.length === 0) {
        eventsGrid.innerHTML = `
            <div class="no-events">
                <i class="fas fa-calendar-times" style="font-size: 3rem; color: #64748b; margin-bottom: 1rem;"></i>
                <h3>Tidak ada event ditemukan</h3>
                <p>Coba ubah filter pencarian atau tambahkan event baru.</p>
            </div>
        `;
        return;
    }
    
    eventsGrid.innerHTML = eventsToShow.map(event => createEventCard(event)).join('');
    
    // Update load more button visibility
    const loadMoreBtn = document.querySelector('.load-more');
    if (loadMoreBtn) {
        loadMoreBtn.style.display = endIndex >= filteredEvents.length ? 'none' : 'block';
    }
}

function createEventCard(event) {
    const eventDate = new Date(event.date + 'T' + event.time);
    const formattedDate = formatDate(eventDate);
    const priceText = event.price === 0 ? 'Gratis' : `Rp ${formatNumber(event.price)}`;
    const statusClass = `status-${event.status}`;
    const statusText = getStatusText(event.status);
    
    return `
        <div class="event-card" onclick="showEventDetail(${event.id})">
            <div class="event-image">${event.image}</div>
            <div class="event-content">
                <span class="event-category">${getCategoryName(event.category)}</span>
                <h3 class="event-title">${event.name}</h3>
                <div class="event-meta">
                    <span><i class="fas fa-calendar"></i> ${formattedDate}</span>
                    <span><i class="fas fa-map-marker-alt"></i> ${event.location}</span>
                </div>
                <p class="event-description">${event.description}</p>
                <div class="event-footer">
                    <span class="event-price">${priceText}</span>
                    <span class="event-status ${statusClass}">${statusText}</span>
                </div>
            </div>
        </div>
    `;
}

// Search Functionality
function searchEvents() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    filteredEvents = events.filter(event => {
        return event.name.toLowerCase().includes(searchTerm) ||
               event.description.toLowerCase().includes(searchTerm) ||
               event.location.toLowerCase().includes(searchTerm) ||
               getCategoryName(event.category).toLowerCase().includes(searchTerm);
    });
    
    // Apply other filters
    applyFilters();
    
    currentPage = 1;
    renderEvents();
}

// Filter Functionality
function filterEvents() {
    applyFilters();
    currentPage = 1;
    renderEvents();
}

function applyFilters() {
    const categoryFilter = document.getElementById('categoryFilter').value;
    const dateFilter = document.getElementById('dateFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    filteredEvents = events.filter(event => {
        // Search filter
        const matchesSearch = !searchTerm || 
            event.name.toLowerCase().includes(searchTerm) ||
            event.description.toLowerCase().includes(searchTerm) ||
            event.location.toLowerCase().includes(searchTerm) ||
            getCategoryName(event.category).toLowerCase().includes(searchTerm);
        
        // Category filter
        const matchesCategory = !categoryFilter || event.category === categoryFilter;
        
        // Date filter
        const matchesDate = !dateFilter || checkDateFilter(event, dateFilter);
        
        // Status filter
        const matchesStatus = !statusFilter || event.status === statusFilter;
        
        return matchesSearch && matchesCategory && matchesDate && matchesStatus;
    });
}

function checkDateFilter(event, filter) {
    const eventDate = new Date(event.date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    switch (filter) {
        case 'today':
            return eventDate.toDateString() === today.toDateString();
        case 'week':
            const weekFromNow = new Date(today);
            weekFromNow.setDate(today.getDate() + 7);
            return eventDate >= today && eventDate <= weekFromNow;
        case 'month':
            const monthFromNow = new Date(today);
            monthFromNow.setMonth(today.getMonth() + 1);
            return eventDate >= today && eventDate <= monthFromNow;
        default:
            return true;
    }
}

function filterByCategory(category) {
    document.getElementById('categoryFilter').value = category;
    filterEvents();
    scrollToSection('events');
}

// Load More Events
function loadMoreEvents() {
    currentPage++;
    renderEvents();
}

// Modal Functions
function openEventModal() {
    const modal = document.getElementById('eventModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Reset form
        document.getElementById('eventForm').reset();
        document.getElementById('modalTitle').textContent = 'Tambah Event Baru';
    }
}

function closeEventModal() {
    const modal = document.getElementById('eventModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function showEventDetail(eventId) {
    const event = events.find(e => e.id === eventId);
    if (!event) return;
    
    const modal = document.getElementById('eventDetailModal');
    const content = document.getElementById('eventDetailContent');
    
    if (modal && content) {
        const eventDate = new Date(event.date + 'T' + event.time);
        const formattedDate = formatDate(eventDate);
        const priceText = event.price === 0 ? 'Gratis' : `Rp ${formatNumber(event.price)}`;
        
        content.innerHTML = `
            <div class="event-detail">
                <div class="event-detail-image">${event.image}</div>
                <h3>${event.name}</h3>
                <div class="event-detail-meta">
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span>${getCategoryName(event.category)}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>${formattedDate}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>${event.location}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-money-bill"></i>
                        <span>${priceText}</span>
                    </div>
                    ${event.contact ? `
                        <div class="meta-item">
                            <i class="fas fa-phone"></i>
                            <span>${event.contact}</span>
                        </div>
                    ` : ''}
                </div>
                <p>${event.description}</p>
                <div class="modal-buttons">
                    <button class="btn btn-secondary" onclick="closeEventDetailModal()">Tutup</button>
                    <button class="btn btn-primary" onclick="shareEvent(${event.id})">
                        <i class="fas fa-share"></i> Bagikan
                    </button>
                </div>
            </div>
        `;
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeEventDetailModal() {
    const modal = document.getElementById('eventDetailModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Event Form Submission
function submitEvent(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const newEvent = {
        id: Date.now(),
        name: formData.get('name'),
        category: formData.get('category'),
        date: formData.get('date'),
        time: formData.get('time'),
        location: formData.get('location'),
        description: formData.get('description'),
        price: parseInt(formData.get('price')) || 0,
        contact: formData.get('contact'),
        status: 'upcoming',
        image: `Event ${formData.get('name')}`
    };
    
    events.unshift(newEvent);
    applyFilters();
    renderEvents();
    closeEventModal();
    
    // Show success message
    showNotification('Event berhasil ditambahkan!', 'success');
}

// Contact Form Submission
function submitContactForm(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const contactData = {
        name: formData.get('name'),
        email: formData.get('email'),
        subject: formData.get('subject'),
        message: formData.get('message')
    };
    
    // Simulate form submission
    setTimeout(() => {
        showNotification('Pesan Anda berhasil dikirim! Kami akan merespons dalam 1x24 jam.', 'success');
        event.target.reset();
    }, 1000);
    
    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
    submitBtn.disabled = true;
    
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 1000);
}

// Share Event
function shareEvent(eventId) {
    const event = events.find(e => e.id === eventId);
    if (!event) return;
    
    const shareText = `Jangan lewatkan: ${event.name} di ${event.location} pada ${formatDate(new Date(event.date + 'T' + event.time))}. Info lengkap di SIEBA - Sistem Informasi Event Terbuka Banda Aceh`;
    
    if (navigator.share) {
        navigator.share({
            title: event.name,
            text: shareText,
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(shareText).then(() => {
            showNotification('Link event berhasil disalin!', 'success');
        });
    }
}

// Utility Functions
function formatDate(date) {
    const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('id-ID', options);
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function getCategoryName(category) {
    const categories = {
        'seminar': 'Seminar',
        'workshop': 'Workshop',
        'konser': 'Konser',
        'olahraga': 'Olahraga',
        'budaya': 'Budaya',
        'teknologi': 'Teknologi'
    };
    return categories[category] || category;
}

function getStatusText(status) {
    const statuses = {
        'upcoming': 'Akan Datang',
        'ongoing': 'Sedang Berlangsung',
        'completed': 'Selesai'
    };
    return statuses[status] || status;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add notification styles if not exists
    if (!document.querySelector('#notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .notification {
                position: fixed;
                top: 100px;
                right: 20px;
                background: white;
                padding: 1rem 1.5rem;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                display: flex;
                align-items: center;
                gap: 1rem;
                z-index: 3000;
                min-width: 300px;
                animation: slideInRight 0.3s ease;
                border-left: 4px solid #3b82f6;
            }
            .notification-success {
                border-left-color: #16a34a;
                color: #16a34a;
            }
            .notification button {
                background: none;
                border: none;
                color: #64748b;
                cursor: pointer;
                padding: 4px;
                border-radius: 4px;
            }
            .notification button:hover {
                background: #f1f5f9;
            }
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Intersection Observer for Animations
function observeElements() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, {
        threshold: 0.1
    });
    
    // Observe elements that should animate
    document.querySelectorAll('.category-card, .event-card, .feature').forEach(el => {
        observer.observe(el);
    });
}

// Header Scroll Effect
window.addEventListener('scroll', function() {
    const header = document.querySelector('.header');
    if (header) {
        if (window.scrollY > 100) {
            header.style.background = 'rgba(255, 255, 255, 0.98)';
            header.style.backdropFilter = 'blur(15px)';
        } else {
            header.style.background = 'rgba(255, 255, 255, 0.95)';
            header.style.backdropFilter = 'blur(10px)';
        }
    }
});

// Update Event Status Based on Date
function updateEventStatuses() {
    const now = new Date();
    
    events.forEach(event => {
        const eventDate = new Date(event.date + 'T' + event.time);
        const eventEndDate = new Date(eventDate.getTime() + (3 * 60 * 60 * 1000)); // Assume 3 hours duration
        
        if (now < eventDate) {
            event.status = 'upcoming';
        } else if (now >= eventDate && now <= eventEndDate) {
            event.status = 'ongoing';
        } else {
            event.status = 'completed';
        }
    });
    
    renderEvents();
}

// Update statuses every minute
setInterval(updateEventStatuses, 60000);

// Initialize status update on load
setTimeout(updateEventStatuses, 1000);