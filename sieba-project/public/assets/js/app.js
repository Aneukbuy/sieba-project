/**
 * SIEBA - Main JavaScript File
 * Handles common functionality across the application
 */

// Global SIEBA object
window.SIEBA = {
    baseUrl: window.location.origin,
    
    // Initialize application
    init: function() {
        this.setupCSRFProtection();
        this.setupAjaxDefaults();
        this.setupFormValidation();
        this.setupUIEnhancements();
        this.setupNotifications();
    },
    
    // Setup CSRF protection for AJAX requests
    setupCSRFProtection: function() {
        // Get CSRF token from meta tag (if available)
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        if (csrfToken) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
        }
    },
    
    // Setup default AJAX settings
    setupAjaxDefaults: function() {
        $.ajaxSetup({
            timeout: 30000, // 30 seconds
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                
                if (xhr.status === 419) {
                    SIEBA.showNotification('Sesi telah berakhir. Silakan refresh halaman.', 'error');
                } else if (xhr.status === 500) {
                    SIEBA.showNotification('Terjadi kesalahan server. Silakan coba lagi.', 'error');
                } else if (status === 'timeout') {
                    SIEBA.showNotification('Koneksi timeout. Silakan coba lagi.', 'error');
                }
            }
        });
    },
    
    // Setup form validation
    setupFormValidation: function() {
        // Auto-remove invalid class on input
        $(document).on('input change', '.form-control.is-invalid', function() {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').hide();
        });
        
        // Form submission with loading state
        $(document).on('submit', 'form[data-loading]', function() {
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.html();
            
            $submitBtn.prop('disabled', true)
                     .html('<i class="fas fa-spinner fa-spin me-2"></i>Loading...');
            
            // Re-enable after 10 seconds as failsafe
            setTimeout(function() {
                $submitBtn.prop('disabled', false).html(originalText);
            }, 10000);
        });
    },
    
    // Setup UI enhancements
    setupUIEnhancements: function() {
        // Auto-hide alerts after 5 seconds
        $('.alert').each(function() {
            const $alert = $(this);
            if (!$alert.hasClass('alert-permanent')) {
                setTimeout(function() {
                    $alert.fadeOut();
                }, 5000);
            }
        });
        
        // Tooltip initialization
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        
        // Popover initialization
        if (typeof bootstrap !== 'undefined') {
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        }
        
        // Smooth scrolling for anchor links
        $(document).on('click', 'a[href^="#"]', function(e) {
            const target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 800);
            }
        });
    },
    
    // Setup notification system
    setupNotifications: function() {
        // Create notification container if it doesn't exist
        if (!$('#notification-container').length) {
            $('body').append('<div id="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>');
        }
    },
    
    // Show notification
    showNotification: function(message, type = 'info', duration = 5000) {
        const types = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        };
        
        const icons = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        };
        
        const alertClass = types[type] || 'alert-info';
        const icon = icons[type] || 'fas fa-info-circle';
        
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="min-width: 300px; margin-bottom: 10px;">
                <i class="${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('#notification-container').append(notification);
        
        // Auto-remove after duration
        if (duration > 0) {
            setTimeout(function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            }, duration);
        }
    },
    
    // Format currency
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    },
    
    // Format date
    formatDate: function(date, format = 'dd/mm/yyyy') {
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        
        switch (format) {
            case 'dd/mm/yyyy':
                return `${day}/${month}/${year}`;
            case 'dd-mm-yyyy':
                return `${day}-${month}-${year}`;
            case 'yyyy-mm-dd':
                return `${year}-${month}-${day}`;
            default:
                return `${day}/${month}/${year}`;
        }
    },
    
    // Confirm dialog
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },
    
    // Loading overlay
    showLoading: function(target = 'body') {
        const $target = $(target);
        const overlay = $(`
            <div class="loading-overlay" style="
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
            ">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Loading...</div>
                </div>
            </div>
        `);
        
        if ($target.css('position') === 'static') {
            $target.css('position', 'relative');
        }
        
        $target.append(overlay);
    },
    
    // Hide loading overlay
    hideLoading: function(target = 'body') {
        $(target).find('.loading-overlay').remove();
    },
    
    // Copy to clipboard
    copyToClipboard: function(text, successMessage = 'Teks berhasil disalin') {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                SIEBA.showNotification(successMessage, 'success');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            SIEBA.showNotification(successMessage, 'success');
        }
    },
    
    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Event card interactions
    setupEventCards: function() {
        // Add click handler for event cards
        $(document).on('click', '.card-event', function(e) {
            if (!$(e.target).is('a, button, .btn')) {
                const link = $(this).find('a[href*="/event/"]').first();
                if (link.length) {
                    window.location.href = link.attr('href');
                }
            }
        });
        
        // Add hover effects
        $(document).on('mouseenter', '.card-event', function() {
            $(this).addClass('shadow-lg');
        }).on('mouseleave', '.card-event', function() {
            $(this).removeClass('shadow-lg');
        });
    },
    
    // Search functionality
    setupSearch: function() {
        // Auto-search with debouncing
        const searchInput = $('input[name="search"]');
        if (searchInput.length) {
            const debouncedSearch = this.debounce(function() {
                // Implement auto-search functionality
                console.log('Searching:', searchInput.val());
            }, 500);
            
            searchInput.on('input', debouncedSearch);
        }
    }
};

// Initialize when document is ready
$(document).ready(function() {
    SIEBA.init();
    SIEBA.setupEventCards();
    SIEBA.setupSearch();
});

// Utility functions for global use
window.formatCurrency = SIEBA.formatCurrency;
window.formatDate = SIEBA.formatDate;
window.showNotification = SIEBA.showNotification;