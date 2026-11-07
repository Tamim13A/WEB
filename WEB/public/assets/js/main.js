/**
 * Real Estate Portal - Main JavaScript
 * CIS324 Fall 2026 - Custom JS (No Framework)
 * Student: 241-16-034, Section: 19_A
 */

(function() {
    'use strict';

    // ==================== Utility Functions ====================
    
    /**
     * Get CSRF token from meta tag
     */
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    /**
     * Create headers for API requests
     */
    function apiHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrfToken()
        };
    }

    /**
     * Show flash message
     */
    function showFlash(message, type = 'info') {
        const container = document.querySelector('.flash-container');
        if (!container) return;

        const flash = document.createElement('div');
        flash.className = `flash flash-${type}`;
        flash.setAttribute('role', 'alert');
        flash.innerHTML = `
            <span>${escapeHtml(message)}</span>
            <button class="flash-close" aria-label="Dismiss">&times;</button>
        `;

        container.appendChild(flash);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            dismissFlash(flash);
        }, 5000);

        // Close button
        flash.querySelector('.flash-close').addEventListener('click', () => {
            dismissFlash(flash);
        });
    }

    function dismissFlash(flash) {
        flash.style.opacity = '0';
        flash.style.transform = 'translateX(100%)';
        setTimeout(() => flash.remove(), 300);
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Format currency
     */
    function formatPrice(amount) {
        return '৳' + Number(amount).toLocaleString('en-BD');
    }

    /**
     * Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // ==================== Navbar Toggle ====================
    
    function initNavbar() {
        const toggle = document.querySelector('.navbar-toggle');
        const menu = document.querySelector('.navbar-menu');
        
        if (toggle && menu) {
            toggle.addEventListener('click', () => {
                const isOpen = menu.classList.toggle('open');
                toggle.setAttribute('aria-expanded', isOpen);
                document.body.style.overflow = isOpen ? 'hidden' : '';
            });

            // Close on link click
            menu.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    menu.classList.remove('open');
                    toggle.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                });
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (!menu.contains(e.target) && !toggle.contains(e.target)) {
                    menu.classList.remove('open');
                    toggle.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                }
            });
        }
    }

    // ==================== Dropdown Menus ====================
    
    function initDropdowns() {
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');

            if (toggle && menu) {
                toggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    document.querySelectorAll('.dropdown-menu').forEach(m => {
                        if (m !== menu) m.style.opacity = '0';
                    });
                    const isOpen = menu.style.opacity === '1';
                    menu.style.opacity = isOpen ? '0' : '1';
                    menu.style.visibility = isOpen ? 'hidden' : 'visible';
                    menu.style.transform = isOpen ? 'translateY(-8px)' : 'translateY(0)';
                    toggle.setAttribute('aria-expanded', !isOpen);
                });
            }
        });

        // Close on outside click
        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.style.opacity = '0';
                menu.style.visibility = 'hidden';
                menu.style.transform = 'translateY(-8px)';
            });
            document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                toggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    // ==================== Flash Message Auto-dismiss ====================
    
    function initFlashMessages() {
        document.querySelectorAll('.flash').forEach(flash => {
            const closeBtn = flash.querySelector('.flash-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => dismissFlash(flash));
            }
            
            // Auto-dismiss
            setTimeout(() => dismissFlash(flash), 5000);
        });
    }

    // ==================== Property Search/Filters ====================
    
    function initPropertyFilters() {
        const form = document.getElementById('filters-form');
        if (!form) return;

        // Auto-submit on select change (debounced)
        const selects = form.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', debounce(() => {
                form.submit();
            }, 300));
        });

        // Price inputs - submit on Enter
        const priceInputs = form.querySelectorAll('input[name="min_price"], input[name="max_price"]');
        priceInputs.forEach(input => {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.submit();
                }
            });
        });
    }

    // ==================== View Toggle (Grid/List) ====================
    
    function initViewToggle() {
        const grid = document.getElementById('properties-grid');
        const viewButtons = document.querySelectorAll('.view-btn');
        
        if (!grid || viewButtons.length === 0) return;

        viewButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const view = btn.dataset.view;
                
                viewButtons.forEach(b => {
                    b.classList.remove('active');
                    b.setAttribute('aria-pressed', 'false');
                });
                
                btn.classList.add('active');
                btn.setAttribute('aria-pressed', 'true');
                
                grid.classList.toggle('list-view', view === 'list');
                
                // Save preference
                localStorage.setItem('propertyView', view);
            });
        });

        // Restore preference
        const savedView = localStorage.getItem('propertyView');
        if (savedView) {
            const btn = document.querySelector(`.view-btn[data-view="${savedView}"]`);
            if (btn) btn.click();
        }
    }

    // ==================== Favourite Toggle ====================
    
    function initFavouriteToggle() {
        document.querySelectorAll('.btn-favourite, .btn-favourite-toggle').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                const propertyId = this.dataset.propertyId;
                if (!propertyId) return;

                const isFav = this.getAttribute('aria-pressed') === 'true';
                
                try {
                    const response = await fetch('/api/favourites/toggle', {
                        method: 'POST',
                        headers: apiHeaders(),
                        body: JSON.stringify({ property_id: parseInt(propertyId) })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        const newFav = data.data.favourited;
                        this.setAttribute('aria-pressed', newFav.toString());
                        
                        const svg = this.querySelector('svg');
                        if (svg) {
                            svg.setAttribute('fill', newFav ? 'currentColor' : 'none');
                        }
                        
                        const span = this.querySelector('span');
                        if (span) {
                            span.textContent = newFav ? 'Saved to Favourites' : 'Save to Favourites';
                        }

                        showFlash(data.message, 'success');
                    } else {
                        showFlash(data.message || 'Failed to update favourite', 'error');
                    }
                } catch (error) {
                    console.error('Favourite toggle failed:', error);
                    showFlash('An error occurred', 'error');
                }
            });
        });
    }

    // ==================== Image Gallery ====================
    
    function initGallery() {
        const thumbs = document.querySelectorAll('.thumb-btn');
        const mainImage = document.getElementById('main-image');
        
        if (thumbs.length === 0 || !mainImage) return;

        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                const imgSrc = this.dataset.image;
                if (imgSrc) {
                    mainImage.src = imgSrc;
                }
                
                thumbs.forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-pressed', 'false');
                });
                
                this.classList.add('active');
                this.setAttribute('aria-pressed', 'true');
            });
        });

        // Keyboard navigation
        thumbs.forEach((thumb, index) => {
            thumb.addEventListener('keydown', (e) => {
                let newIndex = index;
                if (e.key === 'ArrowRight') newIndex = (index + 1) % thumbs.length;
                if (e.key === 'ArrowLeft') newIndex = (index - 1 + thumbs.length) % thumbs.length;
                if (newIndex !== index) {
                    e.preventDefault();
                    thumbs[newIndex].click();
                    thumbs[newIndex].focus();
                }
            });
        });
    }

    // ==================== Image Upload Preview ====================
    
    function initImageUpload() {
        const uploadArea = document.querySelector('.upload-area');
        const fileInput = document.querySelector('input[type="file"][name="images[]"]');
        const previewContainer = document.querySelector('.upload-preview');

        if (!uploadArea || !fileInput) return;

        // Click to upload
        uploadArea.addEventListener('click', (e) => {
            if (e.target === uploadArea || uploadArea.contains(e.target)) {
                fileInput.click();
            }
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('drag-over');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFiles(fileInput.files);
            }
        });

        // File input change
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                handleFiles(fileInput.files);
            }
        });

        function handleFiles(files) {
            if (!previewContainer) return;
            
            previewContainer.innerHTML = '';
            
            Array.from(files).forEach((file, index) => {
                if (!file.type.startsWith('image/')) return;
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    const item = document.createElement('div');
                    item.className = 'upload-preview-item';
                    item.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        <button type="button" class="upload-preview-remove" data-index="${index}" aria-label="Remove image">&times;</button>
                    `;
                    previewContainer.appendChild(item);
                };
                reader.readAsDataURL(file);
            });
        }

        // Remove preview
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('upload-preview-remove')) {
                const index = parseInt(e.target.dataset.index);
                const dt = new DataTransfer();
                Array.from(fileInput.files).forEach((file, i) => {
                    if (i !== index) dt.items.add(file);
                });
                fileInput.files = dt.files;
                handleFiles(fileInput.files);
            }
        });
    }

    // ==================== Property Delete Confirmation ====================
    
    function initPropertyDelete() {
        window.confirmDelete = function(propertyId) {
            if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
                fetch(`/agent/properties/${propertyId}/delete`, {
                    method: 'POST',
                    headers: apiHeaders()
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '/agent/dashboard';
                    } else {
                        alert(data.message || 'Failed to delete property');
                    }
                })
                .catch(() => alert('An error occurred'));
            }
        };
    }

    // ==================== Inquiry Form (Property Detail) ====================
    
    function initInquiryForm() {
        const form = document.getElementById('inquiry-form');
        if (!form) return;

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';
            
            try {
                const response = await fetch('/api/inquiries', {
                    method: 'POST',
                    headers: apiHeaders(),
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showFlash('Inquiry sent successfully!', 'success');
                    this.reset();
                } else {
                    showFlash(result.message || 'Failed to send inquiry', 'error');
                }
            } catch (error) {
                console.error('Inquiry failed:', error);
                showFlash('An error occurred', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    // ==================== Favourite Button on Property Cards ====================
    
    function initPropertyCardFavourite() {
        document.querySelectorAll('.property-card .btn-favourite').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const propertyId = this.dataset.propertyId;
                if (!propertyId) return;
                
                try {
                    const response = await fetch('/api/favourites/toggle', {
                        method: 'POST',
                        headers: apiHeaders(),
                        body: JSON.stringify({ property_id: parseInt(propertyId) })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        const newFav = data.data.favourited;
                        this.setAttribute('aria-pressed', newFav.toString());
                        const svg = this.querySelector('svg');
                        if (svg) {
                            svg.setAttribute('fill', newFav ? 'currentColor' : 'none');
                        }
                        showFlash(data.message, 'success');
                    } else {
                        // If not logged in, redirect to login
                        if (response.status === 401) {
                            window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                        } else {
                            showFlash(data.message || 'Failed to update favourite', 'error');
                        }
                    }
                } catch (error) {
                    console.error('Favourite toggle failed:', error);
                }
            });
        });
    }

    // ==================== Form Validation Enhancement ====================
    
    function initFormValidation() {
        document.querySelectorAll('form[novalidate]').forEach(form => {
            form.addEventListener('submit', function(e) {
                let hasErrors = false;
                
                this.querySelectorAll('[required]').forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('error');
                        hasErrors = true;
                    } else {
                        field.classList.remove('error');
                    }
                });
                
                this.querySelectorAll('input[type="email"]').forEach(field => {
                    if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                        field.classList.add('error');
                        hasErrors = true;
                    }
                });
                
                this.querySelectorAll('input[name="password_confirmation"]').forEach(field => {
                    const password = this.querySelector('input[name="password"]');
                    if (password && field.value !== password.value) {
                        field.classList.add('error');
                        hasErrors = true;
                    }
                });
                
                if (hasErrors) {
                    e.preventDefault();
                    const firstError = this.querySelector('.error');
                    if (firstError) firstError.focus();
                }
            });
            
            // Clear error on input
            form.querySelectorAll('input, select, textarea').forEach(field => {
                field.addEventListener('input', () => {
                    field.classList.remove('error');
                });
            });
        });
    }

    // ==================== Smooth Scroll for Anchor Links ====================
    
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    target.focus({ preventScroll: true });
                }
            });
        });
    }

    // ==================== Lazy Loading Images ====================
    
    function initLazyLoading() {
        if ('loading' in HTMLImageElement.prototype) {
            // Native lazy loading supported
            document.querySelectorAll('img[loading="lazy"]').forEach(img => {
                img.loading = 'lazy';
            });
        } else {
            // Fallback for older browsers
            const lazyImages = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                });
            });
            
            lazyImages.forEach(img => imageObserver.observe(img));
        }
    }

    // ==================== Back to Top Button ====================
    
    function initBackToTop() {
        const backToTop = document.createElement('button');
        backToTop.className = 'back-to-top';
        backToTop.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"></path></svg>';
        backToTop.setAttribute('aria-label', 'Back to top');
        backToTop.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--color-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all var(--transition-normal);
            z-index: 50;
            border: none;
            cursor: pointer;
        `;
        
        document.body.appendChild(backToTop);
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTop.style.opacity = '1';
                backToTop.style.visibility = 'visible';
                backToTop.style.transform = 'translateY(0)';
            } else {
                backToTop.style.opacity = '0';
                backToTop.style.visibility = 'hidden';
                backToTop.style.transform = 'translateY(20px)';
            }
        });
        
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ==================== Initialize All ====================
    
    function init() {
        initNavbar();
        initDropdowns();
        initFlashMessages();
        initPropertyFilters();
        initViewToggle();
        initFavouriteToggle();
        initGallery();
        initImageUpload();
        initPropertyDelete();
        initInquiryForm();
        initPropertyCardFavourite();
        initFormValidation();
        initSmoothScroll();
        initLazyLoading();
        initBackToTop();
        
        console.log('Real Estate Portal initialized');
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose utilities globally
    window.RealEstate = {
        showFlash,
        getCsrfToken,
        apiHeaders,
        formatPrice,
        escapeHtml,
        debounce
    };
})();