// EduCourse Platform JavaScript Functions

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Auto-hide alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-important)');
        alerts.forEach(function(alert) {
            if (alert.classList.contains('alert-success') || alert.classList.contains('alert-info')) {
                fadeOut(alert, 500);
            }
        });
    }, 5000);

    // Search functionality
    setupSearch();
    
    // Form validations
    setupFormValidations();
    
    // Loading states
    setupLoadingStates();
    
    // Animation on scroll
    setupScrollAnimations();
});

// Utility Functions
function fadeOut(element, duration) {
    element.style.transition = `opacity ${duration}ms`;
    element.style.opacity = '0';
    setTimeout(() => {
        element.style.display = 'none';
    }, duration);
}

function fadeIn(element, duration) {
    element.style.display = 'block';
    element.style.opacity = '0';
    element.style.transition = `opacity ${duration}ms`;
    setTimeout(() => {
        element.style.opacity = '1';
    }, 10);
}

function showLoader() {
    const loader = document.createElement('div');
    loader.className = 'spinner';
    loader.id = 'globalLoader';
    document.body.appendChild(loader);
}

function hideLoader() {
    const loader = document.getElementById('globalLoader');
    if (loader) {
        loader.remove();
    }
}

// Search Setup
function setupSearch() {
    const searchInputs = document.querySelectorAll('input[type="search"], input[name="search"]');
    
    searchInputs.forEach(input => {
        let searchTimeout;
        
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    // Trigger search
                    const form = this.closest('form');
                    if (form && form.querySelector('button[type="submit"]')) {
                        // Auto-submit for live search (optional)
                        // form.submit();
                    }
                }
            }, 300);
        });
    });
}

// Form Validations
function setupFormValidations() {
    const forms = document.querySelectorAll('form[novalidate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Custom password validation
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            validatePassword(this);
        });
    });

    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateEmail(this);
        });
    });
}

function validatePassword(input) {
    const value = input.value;
    const minLength = 6;
    
    if (value.length < minLength) {
        input.setCustomValidity(`Password harus minimal ${minLength} karakter`);
    } else {
        input.setCustomValidity('');
    }
}

function validateEmail(input) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (input.value && !emailRegex.test(input.value)) {
        input.setCustomValidity('Format email tidak valid');
    } else {
        input.setCustomValidity('');
    }
}

// Loading States
function setupLoadingStates() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                
                // Re-enable after 5 seconds as fallback
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitButton.getAttribute('data-original-text') || 'Submit';
                }, 5000);
            }
        });
    });
}

// Scroll Animations
function setupScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe cards and sections
    const elementsToAnimate = document.querySelectorAll('.card, .feature-card, .stat-card');
    elementsToAnimate.forEach(el => observer.observe(el));
}

// Cart Functions (for marketplace)
let cart = JSON.parse(localStorage.getItem('eduCourseCart')) || [];

function addToCart(productId, productName, price) {
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: price,
            quantity: 1
        });
    }
    
    localStorage.setItem('eduCourseCart', JSON.stringify(cart));
    updateCartDisplay();
    showNotification('Produk berhasil ditambahkan ke keranjang!', 'success');
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    localStorage.setItem('eduCourseCart', JSON.stringify(cart));
    updateCartDisplay();
    showNotification('Produk dihapus dari keranjang', 'info');
}

function updateCartDisplay() {
    const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
    const cartBadge = document.querySelector('.cart-badge');
    
    if (cartBadge) {
        cartBadge.textContent = cartCount;
        cartBadge.style.display = cartCount > 0 ? 'inline' : 'none';
    }
}

// Notification System
function showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, duration);
}

// Financial Chart Functions
function createFinancialChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    
    return new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            }
        }
    });
}

// Course Progress Functions
function updateCourseProgress(courseId, lessonId) {
    // This would typically make an AJAX call to update progress
    console.log(`Updating progress for course ${courseId}, lesson ${lessonId}`);
    
    // Simulate progress update
    const progressBar = document.querySelector(`[data-course-id="${courseId}"] .progress-bar`);
    if (progressBar) {
        let currentProgress = parseInt(progressBar.style.width) || 0;
        const newProgress = Math.min(currentProgress + 10, 100);
        progressBar.style.width = newProgress + '%';
        progressBar.setAttribute('aria-valuenow', newProgress);
    }
}

// File Upload Functions
function setupFileUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                validateFile(file, this);
                previewFile(file, this);
            }
        });
    });
}

function validateFile(file, input) {
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    
    if (file.size > maxSize) {
        showNotification('File terlalu besar. Maksimal 5MB.', 'danger');
        input.value = '';
        return false;
    }
    
    if (!allowedTypes.includes(file.type)) {
        showNotification('Tipe file tidak didukung.', 'danger');
        input.value = '';
        return false;
    }
    
    return true;
}

function previewFile(file, input) {
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = input.parentNode.querySelector('.file-preview') || 
                          document.createElement('img');
            preview.src = e.target.result;
            preview.className = 'file-preview img-thumbnail mt-2';
            preview.style.maxWidth = '200px';
            
            if (!input.parentNode.querySelector('.file-preview')) {
                input.parentNode.appendChild(preview);
            }
        };
        reader.readAsDataURL(file);
    }
}

// Data Export Functions
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td, th');
        const rowData = Array.from(cells).map(cell => {
            return '"' + cell.textContent.replace(/"/g, '""') + '"';
        });
        csv.push(rowData.join(','));
    });
    
    downloadCSV(csv.join('\n'), filename);
}

function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    
    link.href = url;
    link.download = filename + '.csv';
    link.click();
    
    window.URL.revokeObjectURL(url);
}

// Theme Functions
function toggleTheme() {
    const body = document.body;
    const currentTheme = body.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    body.setAttribute('data-theme', newTheme);
    localStorage.setItem('eduCourseTheme', newTheme);
}

function loadTheme() {
    const savedTheme = localStorage.getItem('eduCourseTheme') || 'light';
    document.body.setAttribute('data-theme', savedTheme);
}

// Initialize theme on load
loadTheme();

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // ESC to close modals
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const modal = bootstrap.Modal.getInstance(openModal);
            if (modal) modal.hide();
        }
    }
});

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    setupFileUpload();
    updateCartDisplay();
    
    // Add floating back-to-top button
    const backToTop = document.createElement('button');
    backToTop.className = 'btn btn-floating d-none';
    backToTop.innerHTML = '<i class="fas fa-chevron-up"></i>';
    backToTop.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
    document.body.appendChild(backToTop);
    
    // Show/hide back-to-top button
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTop.classList.remove('d-none');
        } else {
            backToTop.classList.add('d-none');
        }
    });
});

// Export functions for global access
window.EduCourse = {
    addToCart,
    removeFromCart,
    updateCartDisplay,
    showNotification,
    createFinancialChart,
    updateCourseProgress,
    exportTableToCSV,
    toggleTheme
};