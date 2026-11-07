/**
 * Property Search JavaScript
 * CIS324 Real Estate Portal - AJAX Search & Filter
 */

(function() {
    'use strict';

    const searchForm = document.getElementById('filters-form');
    const propertiesGrid = document.getElementById('properties-grid');
    const paginationContainer = document.querySelector('.pagination');
    const resultsCount = document.querySelector('.results-count');

    if (!searchForm || !propertiesGrid) return;

    let searchTimeout = null;
    const DEBOUNCE_MS = 500;

    // Serialize form to URLSearchParams
    function serializeForm(form) {
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        for (const [key, value] of formData.entries()) {
            if (value !== '') {
                params.append(key, value);
            }
        }
        return params;
    }

    // Show loading state
    function setLoading(isLoading) {
        propertiesGrid.style.opacity = isLoading ? '0.5' : '1';
        propertiesGrid.style.pointerEvents = isLoading ? 'none' : 'auto';
    }

    // Render properties
    function renderProperties(properties) {
        if (properties.length === 0) {
            propertiesGrid.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <h3>No Properties Found</h3>
                    <p>Try adjusting your filters or search terms</p>
                    <a href="/properties" class="btn btn-primary">View All Properties</a>
                </div>
            `;
            return;
        }

        propertiesGrid.innerHTML = properties.map(property => `
            <article class="property-card" role="listitem" data-property-id="${property.id}">
                <div class="property-image">
                    ${property.primary_image 
                        ? `<img src="${escapeHtml(property.primary_image)}" alt="${escapeHtml(property.title)}" loading="lazy">`
                        : `<div class="image-placeholder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg></div>`
                    }
                    <span class="property-badge badge-${property.price_type}">${property.price_type === 'sale' ? 'Sale' : 'Rent'}</span>
                    ${property.is_featured ? '<span class="property-badge badge-featured">Featured</span>' : ''}
                    <button class="btn-favourite" data-property-id="${property.id}" aria-label="Add to favourites" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    </button>
                </div>
                <div class="property-content">
                    <div class="property-meta">
                        <span class="property-category">${escapeHtml(property.category_name)}</span>
                        <span class="property-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            ${escapeHtml(property.city)}
                        </span>
                    </div>
                    <h3 class="property-title"><a href="/properties/${property.id}">${escapeHtml(property.title)}</a></h3>
                    <p class="property-description">${escapeHtml(property.description.substring(0, 120))}...</p>
                    <div class="property-features">
                        ${property.bedrooms > 0 ? `<span class="feature"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg> ${property.bedrooms} Bed</span>` : ''}
                        ${property.bathrooms > 0 ? `<span class="feature"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"></path><line x1="2" y1="20" x2="22" y2="20"></line></svg> ${property.bathrooms} Bath</span>` : ''}
                        ${property.area_sqft > 0 ? `<span class="feature"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg> ${property.area_sqft.toLocaleString()} sqft</span>` : ''}
                    </div>
                    <div class="property-footer">
                        <div class="property-price">
                            <span class="price-amount">৳${property.price.toLocaleString()}</span>
                            <span class="price-period">/${property.price_type === 'rent' ? 'month' : 'total'}</span>
                        </div>
                        <a href="/properties/${property.id}" class="btn btn-primary btn-sm">View Details</a>
                    </div>
                </div>
            </article>
        `).join('');

        // Re-initialize favourite buttons
        initFavouriteButtons();
    }

    // Render pagination
    function renderPagination(pagination) {
        if (!paginationContainer) return;

        if (pagination.last_page <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '';
        
        if (pagination.current_page > 1) {
            html += `<a href="#" class="page-link" data-page="${pagination.current_page - 1}" rel="prev">Previous</a>`;
        }

        const start = Math.max(1, pagination.current_page - 2);
        const end = Math.min(pagination.last_page, pagination.current_page + 2);

        if (start > 1) html += '<span class="page-ellipsis">...</span>';
        
        for (let i = start; i <= end; i++) {
            html += `<a href="#" class="page-link ${i === pagination.current_page ? 'active' : ''}" data-page="${i}" ${i === pagination.current_page ? 'aria-current="page"' : ''}>${i}</a>`;
        }
        
        if (end < pagination.last_page) html += '<span class="page-ellipsis">...</span>';
        
        if (pagination.current_page < pagination.last_page) {
            html += `<a href="#" class="page-link" data-page="${pagination.current_page + 1}" rel="next">Next</a>`;
        }

        paginationContainer.innerHTML = html;

        // Add click handlers
        paginationContainer.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(link.dataset.page);
                if (!isNaN(page)) {
                    loadPage(page);
                }
            });
        });
    }

    // Update URL without reload
    function updateUrl(params) {
        const url = new URL(window.location);
        url.search = params.toString();
        window.history.pushState({}, '', url);
    }

    // Load page via AJAX
    async function loadPage(page = 1) {
        const params = serializeForm(searchForm);
        params.set('page', page);
        
        setLoading(true);
        
        try {
            const response = await fetch(`/api/properties?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            
            const data = await response.json();
            
            if (data.success) {
                renderProperties(data.data.properties);
                renderPagination(data.data.pagination);
                updateResultsCount(data.data.pagination);
                updateUrl(params);
            } else {
                console.error('Search failed:', data.message);
            }
        } catch (error) {
            console.error('Search error:', error);
        } finally {
            setLoading(false);
        }
    }

    function updateResultsCount(pagination) {
        if (resultsCount) {
            resultsCount.textContent = `Showing ${pagination.from}–${pagination.to} of ${pagination.total} properties`;
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize favourite buttons on dynamically loaded content
    function initFavouriteButtons() {
        document.querySelectorAll('.btn-favourite').forEach(btn => {
            // Remove existing listeners by cloning
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const propertyId = this.dataset.propertyId;
                if (!propertyId) return;
                
                try {
                    const response = await fetch('/api/favourites/toggle', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ property_id: parseInt(propertyId) })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        const newFav = data.data.favourited;
                        this.setAttribute('aria-pressed', newFav.toString());
                        const svg = this.querySelector('svg');
                        if (svg) svg.setAttribute('fill', newFav ? 'currentColor' : 'none');
                        
                        if (window.RealEstate?.showFlash) {
                            window.RealEstate.showFlash(data.message, 'success');
                        }
                    } else if (response.status === 401) {
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                    }
                } catch (error) {
                    console.error('Favourite toggle failed:', error);
                }
            });
        });
    }

    // Event listeners
    searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        loadPage(1);
    });

    // Debounced auto-submit on input change
    const searchInput = searchForm.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadPage(1), 500);
        });
    }

    // Select changes - immediate submit
    searchForm.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', () => loadPage(1));
    });

    // Initialize on load
    document.addEventListener('DOMContentLoaded', () => {
        initFavouriteButtons();
    });

})();