/**
 * API Client for Real Estate Portal
 * CIS324 Fall 2026 - JavaScript Fetch API Integration
 */

(function() {
    'use strict';

    /**
     * API Client Class
     */
    class ApiClient {
        constructor(baseUrl = '/api') {
            this.baseUrl = baseUrl;
            this.csrfToken = this.getCsrfToken();
        }

        getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : '';
        }

        getHeaders() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': this.csrfToken
            };
        }

        async request(endpoint, options = {}) {
            const url = `${this.baseUrl}${endpoint}`;
            
            const config = {
                headers: this.getHeaders(),
                ...options
            };

            if (config.body && typeof config.body === 'object') {
                config.body = JSON.stringify(config.body);
            }

            try {
                const response = await fetch(url, config);
                const data = await response.json();

                if (!response.ok) {
                    throw new ApiError(response.status, data.message || 'Request failed', data.errors);
                }

                return data;
            } catch (error) {
                if (error instanceof ApiError) throw error;
                throw new ApiError(0, 'Network error: ' + error.message);
            }
        }

        // Properties
        async getProperties(filters = {}, page = 1, perPage = 12) {
            const params = new URLSearchParams({ page, per_page: perPage, ...filters });
            return this.request(`/properties?${params.toString()}`);
        }

        async getProperty(id) {
            return this.request(`/properties/${id}`);
        }

        async getFeatured(limit = 6) {
            return this.request(`/properties/featured?limit=${limit}`);
        }

        // Categories
        async getCategories() {
            return this.request('/categories');
        }

        async getCities() {
            return this.request('/cities');
        }

        // Favourites
        async toggleFavourite(propertyId) {
            return this.request('/favourites/toggle', {
                method: 'POST',
                body: { property_id: propertyId }
            });
        }

        async checkFavourite(propertyId) {
            return this.request(`/favourites/check/${propertyId}`);
        }

        // Inquiries
        async submitInquiry(propertyId, message) {
            return this.request('/inquiries', {
                method: 'POST',
                body: { property_id: propertyId, message }
            });
        }

        // Stats (admin)
        async getStats() {
            return this.request('/properties/stats');
        }
    }

    /**
     * Custom API Error
     */
    class ApiError extends Error {
        constructor(status, message, errors = []) {
            super(message);
            this.name = 'ApiError';
            this.status = status;
            this.errors = errors;
        }
    }

    // Create global instance
    window.api = new ApiClient();

    // Export for module use
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = { ApiClient, ApiError };
    }
})();