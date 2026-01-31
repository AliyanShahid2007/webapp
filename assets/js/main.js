/**
 * FreelanceHub - Enhanced JavaScript
 * Professional Interactive Features
 */

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 FreelanceHub Enhanced - Loading...');
    
    initTheme();
    initFormValidation();
    initImagePreview();
    initConfirmations();
    initAutoHideAlerts();
    initAnimations();
    initTooltips();
    initSearch();
    initLazyLoading();
    initProgressBars();
    initCharacterCounters();
    initRatingStars();


    initScrollEffects();
    initFileUpload();
    initCountUp(); // Initialize count-up animation immediately
    initMobileMenu();

    console.log('✅ FreelanceHub Enhanced - Ready!');
});

// ==================== THEME MANAGEMENT ====================
function initTheme() {
    const themeToggle = document.getElementById('theme-toggle');
    const html = document.documentElement;
    const body = document.body;
    
    // Load saved theme or default to light
    const savedTheme = localStorage.getItem('freelancehub_theme') || 'light';
    setTheme(savedTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            setTheme(newTheme);
            
            // Add ripple effect
            createRipple(this, event);
        });
    }
    
    function setTheme(theme) {
        html.setAttribute('data-theme', theme);
        body.setAttribute('data-theme', theme);
        localStorage.setItem('freelancehub_theme', theme);
        updateThemeIcon(theme);
        
        // Animate theme change
        document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
    }
    
    function updateThemeIcon(theme) {
        if (themeToggle) {
            const icon = themeToggle.querySelector('i');
            if (icon) {
                icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }
    }
}

// ==================== FORM VALIDATION ====================
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Show first error
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    showToast('Please fill in all required fields', 'danger');
                }
            }
            
            form.classList.add('was-validated');
        }, false);
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });
    });
}

// ==================== IMAGE PREVIEW ====================
function initImagePreview() {
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    
    imageInputs.forEach(input => {
        input.addEventListener('change', function(event) {
            const file = event.target.files[0];
            const previewId = input.getAttribute('data-preview');
            
            if (file && previewId) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        if (preview.tagName === 'IMG') {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                            
                            // Animate preview
                            preview.style.animation = 'scaleIn 0.3s ease';
                        } else {
                            preview.style.backgroundImage = `url(${e.target.result})`;
                            preview.style.backgroundSize = 'cover';
                            preview.style.backgroundPosition = 'center';
                        }
                    }
                };
                
                reader.readAsDataURL(file);
            }
        });
    });
}

// ==================== CONFIRMATION DIALOGS ====================
function initConfirmations() {
    document.addEventListener('click', function(event) {
        const confirmButton = event.target.closest('[data-confirm]');
        if (confirmButton) {
            const message = confirmButton.getAttribute('data-confirm');
            
            if (!confirm(message)) {
                event.preventDefault();
                event.stopPropagation();
            }
        }
    });
}

// ==================== AUTO-HIDE ALERTS ====================
function initAutoHideAlerts() {
    const alerts = document.querySelectorAll('.alert[data-auto-hide]');
    
    alerts.forEach(alert => {
        const delay = parseInt(alert.getAttribute('data-auto-hide')) || 5000;
        
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(100%)';
            setTimeout(() => alert.remove(), 300);
        }, delay);
    });
}

// ==================== ANIMATIONS ====================
function initAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Animate cards on scroll
    const animatedElements = document.querySelectorAll('.card, .stats-card');
    animatedElements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        el.style.transitionDelay = `${index * 0.1}s`;
        observer.observe(el);
    });

    // Count up animation for statistics
    initCountUp();
}

// ==================== COUNT UP ANIMATION ====================
function initCountUp() {
    const countUpElements = document.querySelectorAll('.count-up');

    // Trigger animation immediately for all elements
    countUpElements.forEach(el => {
        const target = parseInt(el.getAttribute('data-target')) || 0;
        animateCountUp(el, target);
    });

    // Update stats immediately and then every 30 seconds
    updateStats(); // Initial update
    setInterval(updateStats, 30000);
}

// ==================== DYNAMIC STATS UPDATE ====================
function updateStats() {
    fetch(window.basePath + '/api/stats.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateStatCard('total_freelancers', data.data.total_freelancers);
                updateStatCard('total_gigs', data.data.total_gigs);
                updateStatCard('completed_orders', data.data.completed_orders);
                updateStatCard('total_clients', data.data.total_clients);
            }
        })
        .catch(error => console.error('Error updating stats:', error));
}

function updateStatCard(statName, newValue) {
    const element = document.querySelector(`[data-stat="${statName}"]`);
    if (element) {
        const currentValue = parseInt(element.getAttribute('data-target')) || 0;
        if (currentValue !== newValue) {
            element.setAttribute('data-target', newValue);
            // Re-animate the count-up
            animateCountUp(element, newValue);
        }
    }
}

// ==================== COUNT UP ANIMATION HELPER ====================
function animateCountUp(element, target) {
    let current = 0;
    const duration = 2000; // 2 seconds
    const increment = target / (duration / 50);

    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current).toLocaleString();
    }, 50);
}

// ==================== TOOLTIPS ====================
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        const tooltipText = element.getAttribute('data-tooltip');
        
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip-custom';
            tooltip.textContent = tooltipText;
            tooltip.style.cssText = `
                position: absolute;
                background: var(--text-primary);
                color: var(--bg-primary);
                padding: 0.5rem 1rem;
                border-radius: 0.5rem;
                font-size: 0.875rem;
                white-space: nowrap;
                z-index: 9999;
                pointer-events: none;
                animation: fadeIn 0.2s ease;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = element.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
            
            element._tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (element._tooltip) {
                element._tooltip.remove();
                element._tooltip = null;
            }
        });
    });
}

// ==================== SEARCH FUNCTIONALITY ====================
function initSearch() {
    const searchInput = document.getElementById('search-input');
    if (!searchInput) return;
    
    let searchTimeout;
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            const query = e.target.value.toLowerCase();
            // Add your search logic here
            console.log('Searching for:', query);
        }, 300);
    });
}

// ==================== LAZY LOADING ====================
function initLazyLoading() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
}

// ==================== PROGRESS BARS ====================
function initProgressBars() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        
        setTimeout(() => {
            bar.style.transition = 'width 1s ease';
            bar.style.width = width;
        }, 100);
    });
}

// ==================== CHARACTER COUNTERS ====================
function initCharacterCounters() {
    const textareas = document.querySelectorAll('textarea[data-max-length]');
    
    textareas.forEach(textarea => {
        const maxLength = parseInt(textarea.getAttribute('data-max-length'));
        const counterId = textarea.getAttribute('data-counter');
        const counter = document.getElementById(counterId);
        
        if (counter) {
            function updateCounter() {
                const remaining = maxLength - textarea.value.length;
                counter.textContent = `${remaining} characters remaining`;
                
                if (remaining < 0) {
                    counter.style.color = 'var(--danger-color)';
                } else if (remaining < 50) {
                    counter.style.color = 'var(--warning-color)';
                } else {
                    counter.style.color = 'var(--text-muted)';
                }
            }
            
            textarea.addEventListener('input', updateCounter);
            updateCounter();
        }
    });
}

// ==================== RATING STARS ====================
function initRatingStars() {
    const ratingContainers = document.querySelectorAll('.rating-input');
    
    ratingContainers.forEach(container => {
        const stars = container.querySelectorAll('.star');
        const input = container.querySelector('input[type="hidden"]');
        
        stars.forEach((star, index) => {
            star.addEventListener('click', function() {
                const rating = index + 1;
                input.value = rating;
                
                stars.forEach((s, i) => {
                    if (i < rating) {
                        s.classList.add('active');
                        s.style.color = '#fbbf24';
                    } else {
                        s.classList.remove('active');
                        s.style.color = 'var(--text-muted)';
                    }
                });
                
                // Animate selected star
                star.style.transform = 'scale(1.3)';
                setTimeout(() => {
                    star.style.transform = 'scale(1)';
                }, 200);
            });
            
            star.addEventListener('mouseenter', function() {
                stars.forEach((s, i) => {
                    if (i <= index) {
                        s.style.color = '#fbbf24';
                    }
                });
            });
            
            container.addEventListener('mouseleave', function() {
                const currentRating = parseInt(input.value) || 0;
                stars.forEach((s, i) => {
                    if (i < currentRating) {
                        s.style.color = '#fbbf24';
                    } else {
                        s.style.color = 'var(--text-muted)';
                    }
                });
            });
        });
    });
}

// ==================== DROPDOWNS ====================
function initDropdowns() {
    const dropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.nextElementSibling;
            
            if (dropdown && dropdown.classList.contains('dropdown-menu')) {
                const isOpen = dropdown.classList.contains('show');
                
                // Close all dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
                
                if (!isOpen) {
                    dropdown.classList.add('show');
                    dropdown.style.animation = 'scaleIn 0.2s ease';
                }
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });
}



// ==================== SCROLL EFFECTS ====================
function initScrollEffects() {
    let lastScroll = 0;
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        // Hide/show navbar on scroll
        if (currentScroll > lastScroll && currentScroll > 100) {
            navbar.style.transform = 'translateY(-100%)';
        } else {
            navbar.style.transform = 'translateY(0)';
        }
        
        // Add shadow on scroll
        if (currentScroll > 10) {
            navbar.style.boxShadow = 'var(--shadow-lg)';
        } else {
            navbar.style.boxShadow = 'var(--shadow-sm)';
        }
        
        lastScroll = currentScroll;
    });
    
    // Smooth scroll to top button
    const scrollTopBtn = document.getElementById('scroll-top');
    if (scrollTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollTopBtn.style.display = 'flex';
                scrollTopBtn.style.animation = 'scaleIn 0.3s ease';
            } else {
                scrollTopBtn.style.display = 'none';
            }
        });
        
        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

// ==================== FILE UPLOAD ENHANCEMENT ====================
function initFileUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const files = e.target.files;
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-info';
            fileInfo.style.cssText = `
                margin-top: 0.5rem;
                padding: 0.5rem;
                background: var(--bg-tertiary);
                border-radius: var(--radius-md);
                font-size: 0.875rem;
                color: var(--text-secondary);
            `;
            
            let infoText = '';
            if (files.length === 1) {
                infoText = `Selected: ${files[0].name} (${formatFileSize(files[0].size)})`;
            } else if (files.length > 1) {
                infoText = `Selected ${files.length} files`;
            }
            
            fileInfo.textContent = infoText;
            
            // Remove old file info
            const oldInfo = input.parentElement.querySelector('.file-info');
            if (oldInfo) oldInfo.remove();
            
            input.parentElement.appendChild(fileInfo);
        });
    });
}

// ==================== HELPER FUNCTIONS ====================

// Show toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 500px;
        padding: 1rem 1.5rem;
        background: var(--card-bg);
        border-left: 4px solid var(--${type}-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xl);
        animation: slideInRight 0.3s ease;
        display: flex;
        align-items: center;
        gap: 1rem;
    `;
    
    const icon = document.createElement('i');
    icon.className = `fas fa-${getIconForType(type)}`;
    icon.style.fontSize = '1.5rem';
    icon.style.color = `var(--${type}-color)`;
    
    const text = document.createElement('span');
    text.textContent = message;
    text.style.color = 'var(--text-primary)';
    
    toast.appendChild(icon);
    toast.appendChild(text);
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

function getIconForType(type) {
    const icons = {
        success: 'check-circle',
        danger: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Create ripple effect
function createRipple(element, event) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple 0.6s ease-out;
        left: ${x}px;
        top: ${y}px;
        pointer-events: none;
    `;
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);
    
    setTimeout(() => ripple.remove(), 600);
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Debounce function
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

// Copy to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Copied to clipboard!', 'success');
        });
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast('Copied to clipboard!', 'success');
    }
}

// ==================== KEYBOARD SHORTCUTS ====================
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[type="search"], input[name="search"]');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Escape to close modals/dropdowns
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal.show, .dropdown-menu.show').forEach(el => {
            el.classList.remove('show');
        });
    }
});

// ==================== PERFORMANCE MONITORING ====================
window.addEventListener('load', function() {
    // Performance metrics
    if (window.performance && window.performance.timing) {
        const perfData = window.performance.timing;
        const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
        if (pageLoadTime > 0) {
            console.log(`📊 Page Load Time: ${pageLoadTime}ms`);
        }
    }
});

// ==================== ERROR HANDLING ====================
window.addEventListener('error', function(e) {
    console.error('❌ Error:', e.message);
});

// ==================== MOBILE MENU ====================
function initMobileMenu() {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const navbarMenu = document.getElementById('navbar-menu');

    if (mobileMenuToggle && navbarMenu) {
        mobileMenuToggle.addEventListener('click', function(event) {
            navbarMenu.classList.toggle('show');

            // Animate hamburger icon
            const icon = this.querySelector('i');
            if (icon) {
                if (navbarMenu.classList.contains('show')) {
                    icon.className = 'fas fa-times';
                } else {
                    icon.className = 'fas fa-bars';
                }
            }

            // Prevent event bubbling
            event.stopPropagation();
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileMenuToggle.contains(event.target) && !navbarMenu.contains(event.target)) {
                navbarMenu.classList.remove('show');
                const icon = mobileMenuToggle.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-bars';
                }
            }
        });

        // Add close button functionality
        const closeBtn = document.getElementById('menu-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                navbarMenu.classList.remove('show');
                const icon = mobileMenuToggle.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-bars';
                }
            });
        }
    }
}

// ==================== EXPORT FUNCTIONS ====================
window.FreelanceHub = {
    showToast,
    copyToClipboard,
    formatCurrency,
    formatFileSize,
    debounce
};

console.log('🎉 FreelanceHub Enhanced JavaScript Loaded Successfully!');
