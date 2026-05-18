/**
 * Custom JavaScript
 * Sistem Manajemen Pendaftaran TOEFL
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // Sidebar Toggle
    // ========================================
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            const wrapper = document.getElementById('page-content-wrapper');
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
            }
        });
    }
    
    // ========================================
    // Auto-dismiss alerts after 5 seconds
    // ========================================
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // ========================================
    // Form validation
    // ========================================
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // ========================================
    // File upload preview
    // ========================================
    const fileInput = document.getElementById('bukti_file');
    const previewContainer = document.getElementById('filePreview');
    
    if (fileInput && previewContainer) {
        fileInput.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const maxSize = 2 * 1024 * 1024; // 2MB
                const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                
                // Validate size
                if (file.size > maxSize) {
                    previewContainer.innerHTML = '<div class="alert alert-danger py-2"><i class="bi bi-exclamation-circle me-1"></i> File terlalu besar (maks 2MB)</div>';
                    this.value = '';
                    return;
                }
                
                // Validate type
                if (!allowedTypes.includes(file.type)) {
                    previewContainer.innerHTML = '<div class="alert alert-danger py-2"><i class="bi bi-exclamation-circle me-1"></i> Tipe file tidak didukung. Gunakan JPG, PNG, atau PDF.</div>';
                    this.value = '';
                    return;
                }
                
                // Preview
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContainer.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail mt-2" style="max-height: 200px;">';
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.innerHTML = '<div class="alert alert-info py-2 mt-2"><i class="bi bi-file-pdf me-1"></i> ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)</div>';
                }
            }
        });
    }
    
    // ========================================
    // Confirm delete actions
    // ========================================
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
    
    // ========================================
    // Password toggle visibility
    // ========================================
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const input = document.querySelector(this.getAttribute('data-target'));
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    this.innerHTML = '<i class="bi bi-eye-slash"></i>';
                } else {
                    input.type = 'password';
                    this.innerHTML = '<i class="bi bi-eye"></i>';
                }
            }
        });
    });

    // ========================================
    // Navbar scroll effect (add shadow on scroll)
    // ========================================
    const publicNavbar = document.querySelector('.public-navbar');
    if (publicNavbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 20) {
                publicNavbar.classList.add('scrolled');
            } else {
                publicNavbar.classList.remove('scrolled');
            }
        });
    }

    // ========================================
    // Active nav link highlighting on scroll
    // ========================================
    const navLinks = document.querySelectorAll('.public-navbar .nav-link[href^="#"]');
    const sections = [];
    
    navLinks.forEach(function(link) {
        const sectionId = link.getAttribute('href').substring(1);
        const section = document.getElementById(sectionId);
        if (section) {
            sections.push({ link: link, section: section });
        }
    });

    if (sections.length > 0) {
        window.addEventListener('scroll', function() {
            const scrollPos = window.scrollY + 120;
            
            sections.forEach(function(item) {
                const sectionTop = item.section.offsetTop;
                const sectionBottom = sectionTop + item.section.offsetHeight;
                
                if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                    navLinks.forEach(function(l) { l.classList.remove('active'); });
                    item.link.classList.add('active');
                }
            });
        });
    }

    // ========================================
    // Scroll animations (Intersection Observer)
    // ========================================
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    
    if (animateElements.length > 0 && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -50px 0px'
        });

        animateElements.forEach(function(el) {
            observer.observe(el);
        });
    } else {
        // Fallback: show all elements immediately
        animateElements.forEach(function(el) {
            el.classList.add('animated');
        });
    }

    // ========================================
    // Smooth scroll for anchor links
    // ========================================
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetEl = document.querySelector(targetId);
            if (targetEl) {
                e.preventDefault();
                
                // Close mobile nav if open
                const navCollapse = document.getElementById('publicNav');
                if (navCollapse && navCollapse.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
                    if (bsCollapse) bsCollapse.hide();
                }

                targetEl.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

});

// ========================================
// FAQ Accordion Toggle (global function)
// ========================================
function toggleFaq(button) {
    const faqItem = button.closest('.faq-item');
    const wasActive = faqItem.classList.contains('active');
    
    // Close all FAQ items
    document.querySelectorAll('.faq-item').forEach(function(item) {
        item.classList.remove('active');
    });
    
    // Toggle clicked item
    if (!wasActive) {
        faqItem.classList.add('active');
    }
}
