// Sample event data
const eventsData = [
    {
        id: 1,
        title: "Festival Budaya Aceh 2024",
        description: "Festival budaya terbesar di Banda Aceh yang menampilkan berbagai kesenian, kuliner, dan tradisi Aceh.",
        category: "budaya",
        date: "15-20 Desember 2024",
        location: "Taman Sari, Banda Aceh",
        image: "fas fa-music"
    },
    {
        id: 2,
        title: "Seminar Kewirausahaan Digital",
        description: "Seminar tentang peluang bisnis digital dan strategi pengembangan UMKM di era teknologi.",
        category: "pendidikan",
        date: "25 November 2024",
        location: "Aula Universitas Syiah Kuala",
        image: "fas fa-laptop"
    },
    {
        id: 3,
        title: "Turnamen Futsal Antar Kecamatan",
        description: "Kompetisi futsal tingkat kecamatan se-Banda Aceh dengan hadiah total Rp 50 juta.",
        category: "olahraga",
        date: "10-15 Desember 2024",
        location: "GOR Tunas Bangsa",
        image: "fas fa-futbol"
    },
    {
        id: 4,
        title: "Pameran UMKM Banda Aceh",
        description: "Pameran produk UMKM lokal dengan berbagai kategori makanan, kerajinan, dan jasa.",
        category: "ekonomi",
        date: "5-10 Desember 2024",
        location: "Mall Citta",
        image: "fas fa-store"
    },
    {
        id: 5,
        title: "Workshop Fotografi Street Photography",
        description: "Workshop fotografi jalanan dengan pemandu profesional dan praktik langsung di lokasi menarik.",
        category: "pendidikan",
        date: "30 November 2024",
        location: "Kota Lama Banda Aceh",
        image: "fas fa-camera"
    },
    {
        id: 6,
        title: "Konser Musik Tradisional Aceh",
        description: "Konser musik tradisional Aceh dengan berbagai alat musik dan lagu daerah.",
        category: "budaya",
        date: "22 November 2024",
        location: "Taman Budaya Aceh",
        image: "fas fa-guitar"
    },
    {
        id: 7,
        title: "Marathon Banda Aceh 2024",
        description: "Lomba lari marathon dengan rute mengelilingi kota Banda Aceh dengan berbagai kategori.",
        category: "olahraga",
        date: "8 Desember 2024",
        location: "Start: Lapangan Blang Padang",
        image: "fas fa-running"
    },
    {
        id: 8,
        title: "Bazar Ramadhan 2024",
        description: "Bazar makanan dan kebutuhan Ramadhan dengan berbagai pedagang lokal.",
        category: "ekonomi",
        date: "1-30 Maret 2024",
        location: "Masjid Raya Baiturrahman",
        image: "fas fa-shopping-cart"
    }
];

// DOM Elements
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');
const eventsGrid = document.getElementById('eventsGrid');
const filterBtns = document.querySelectorAll('.filter-btn');
const loadMoreBtn = document.getElementById('loadMoreBtn');
const contactForm = document.getElementById('contactForm');

// Mobile Navigation
hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Close mobile menu when clicking on a link
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    });
});

// Smooth scrolling for navigation links
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

// Event filtering functionality
let currentFilter = 'semua';
let displayedEvents = 6;

function filterEvents(filter) {
    currentFilter = filter;
    displayedEvents = 6;
    
    // Update active filter button
    filterBtns.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.filter === filter) {
            btn.classList.add('active');
        }
    });
    
    // Filter events
    const filteredEvents = filter === 'semua' 
        ? eventsData 
        : eventsData.filter(event => event.category === filter);
    
    // Display filtered events
    displayEvents(filteredEvents.slice(0, displayedEvents));
    
    // Show/hide load more button
    loadMoreBtn.style.display = filteredEvents.length > displayedEvents ? 'block' : 'none';
}

function displayEvents(events) {
    eventsGrid.innerHTML = '';
    
    events.forEach(event => {
        const eventCard = createEventCard(event);
        eventsGrid.appendChild(eventCard);
    });
}

function createEventCard(event) {
    const card = document.createElement('div');
    card.className = 'event-card fade-in-up';
    
    card.innerHTML = `
        <div class="event-image">
            <i class="${event.image}"></i>
        </div>
        <div class="event-content">
            <span class="event-category">${getCategoryName(event.category)}</span>
            <h3 class="event-title">${event.title}</h3>
            <p class="event-description">${event.description}</p>
            <div class="event-meta">
                <div class="event-date">
                    <i class="fas fa-calendar"></i>
                    <span>${event.date}</span>
                </div>
                <div class="event-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>${event.location}</span>
                </div>
            </div>
        </div>
    `;
    
    return card;
}

function getCategoryName(category) {
    const categories = {
        'budaya': 'Budaya',
        'pendidikan': 'Pendidikan',
        'olahraga': 'Olahraga',
        'ekonomi': 'Ekonomi'
    };
    return categories[category] || category;
}

// Load more functionality
loadMoreBtn.addEventListener('click', () => {
    const filteredEvents = currentFilter === 'semua' 
        ? eventsData 
        : eventsData.filter(event => event.category === currentFilter);
    
    displayedEvents += 3;
    displayEvents(filteredEvents.slice(0, displayedEvents));
    
    if (displayedEvents >= filteredEvents.length) {
        loadMoreBtn.style.display = 'none';
    }
});

// Filter button event listeners
filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        filterEvents(btn.dataset.filter);
    });
});

// Contact form handling
contactForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const formData = new FormData(contactForm);
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value;
    
    // Simple validation
    if (!name || !email || !subject || !message) {
        showNotification('Mohon lengkapi semua field', 'error');
        return;
    }
    
    // Simulate form submission
    const submitBtn = contactForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<span class="loading"></span> Mengirim...';
    submitBtn.disabled = true;
    
    setTimeout(() => {
        showNotification('Pesan berhasil dikirim! Kami akan menghubungi Anda segera.', 'success');
        contactForm.reset();
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 2000);
});

// Newsletter form handling
const newsletterForm = document.querySelector('.newsletter-form');
newsletterForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const email = newsletterForm.querySelector('input[type="email"]').value;
    
    if (!email) {
        showNotification('Mohon masukkan email Anda', 'error');
        return;
    }
    
    showNotification('Berhasil berlangganan newsletter!', 'success');
    newsletterForm.reset();
});

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 1rem;
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
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
        
        .notification-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: background 0.3s ease;
        }
        
        .notification-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notification);
    
    // Close button functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.remove();
    });
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Navbar scroll effect
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 100) {
        navbar.style.background = 'rgba(255, 255, 255, 0.98)';
        navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
    } else {
        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        navbar.style.boxShadow = 'none';
    }
});

// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('fade-in-up');
        }
    });
}, observerOptions);

// Observe elements for animation
document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll('.event-card, .feature, .contact-item');
    animatedElements.forEach(el => observer.observe(el));
    
    // Initialize events display
    filterEvents('semua');
});

// Footer filter links
document.querySelectorAll('.footer-section a[data-filter]').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const filter = link.dataset.filter;
        filterEvents(filter);
        scrollToSection('event');
    });
});

// Add some interactive features
document.addEventListener('DOMContentLoaded', () => {
    // Add hover effects to event cards
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
        });
    });
    
    // Add click to copy functionality for contact info
    const contactItems = document.querySelectorAll('.contact-item p');
    contactItems.forEach(item => {
        item.style.cursor = 'pointer';
        item.addEventListener('click', () => {
            navigator.clipboard.writeText(item.textContent).then(() => {
                showNotification('Informasi berhasil disalin!', 'success');
            });
        });
    });
});

// Search functionality (if needed)
function searchEvents(query) {
    const filteredEvents = eventsData.filter(event => 
        event.title.toLowerCase().includes(query.toLowerCase()) ||
        event.description.toLowerCase().includes(query.toLowerCase()) ||
        event.location.toLowerCase().includes(query.toLowerCase())
    );
    
    displayEvents(filteredEvents);
}

// Export functions for global access
window.scrollToSection = scrollToSection;
window.filterEvents = filterEvents;
window.searchEvents = searchEvents;