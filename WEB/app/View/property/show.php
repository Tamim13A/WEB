<?php
/**
 * Property Details View
 * CIS324 Real Estate Portal
 */
$images = $property['images'] ?? [];
$primaryImage = $property['primary_image'] ?? ($images[0]['file_path'] ?? null);
$priceType = $property['price_type'] === 'sale' ? 'For Sale' : 'For Rent';
$statusLabels = [
    'pending' => 'Pending Approval',
    'approved' => 'Available',
    'rejected' => 'Rejected',
    'sold' => 'Sold',
    'rented' => 'Rented',
];
?>
<div class="page-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="/">Home</a></li>
                <li><a href="/properties">Properties</a></li>
                <li aria-current="page"><?= Security::escape($property['title']) ?></li>
            </ol>
        </nav>
        <h1><?= Security::escape($property['title']) ?></h1>
    </div>
</div>

<div class="container">
    <div class="property-detail-layout">
        <!-- Main Content -->
        <div class="property-main">
            <!-- Image Gallery -->
            <div class="property-gallery">
                <div class="gallery-main" id="gallery-main">
                    <?php if ($primaryImage): ?>
                        <img src="<?= Security::escape($primaryImage) ?>" alt="<?= Security::escape($property['title']) ?>" id="main-image">
                    <?php else: ?>
                        <div class="image-placeholder large">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (count($images) > 1): ?>
                    <div class="gallery-thumbs" id="gallery-thumbs" role="group" aria-label="Property images">
                        <?php foreach ($images as $index => $img): ?>
                            <button class="thumb-btn <?= $index === 0 ? 'active' : '' ?>" 
                                    data-image="<?= Security::escape($img['file_path']) ?>" 
                                    aria-label="View image <?= $index + 1 ?>"
                                    aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>">
                                <img src="<?= Security::escape($img['file_path']) ?>" alt="" loading="lazy">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Property Info -->
            <div class="property-info">
                <div class="property-header">
                    <div class="property-badges">
                        <span class="badge badge-<?= $property['price_type'] ?>"><?= $priceType ?></span>
                        <span class="badge badge-status badge-<?= $property['status'] ?>"><?= $statusLabels[$property['status']] ?? ucfirst($property['status']) ?></span>
                        <?php if ($property['is_featured']): ?>
                            <span class="badge badge-featured">Featured</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="property-meta-detail">
                        <span class="category"><?= Security::escape($property['category_name'] ?? 'Property') ?></span>
                        <span class="location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <?= Security::escape($property['address']) ?>, <?= Security::escape($property['city']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="property-price-large">
                    <span class="price-amount">৳<?= number_format($property['price'], 0) ?></span>
                    <span class="price-period"><?= $priceType === 'For Rent' ? '/month' : '' ?></span>
                </div>
                
                <div class="property-description">
                    <h3>Description</h3>
                    <p><?= nl2br(Security::escape($property['description'])) ?></p>
                </div>
                
                <!-- Features Grid -->
                <div class="features-grid">
                    <?php if ($property['bedrooms'] > 0): ?>
                        <div class="feature-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            <span class="feature-label">Bedrooms</span>
                            <span class="feature-value"><?= (int)$property['bedrooms'] ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($property['bathrooms'] > 0): ?>
                        <div class="feature-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"></path><line x1="2" y1="20" x2="22" y2="20"></line></svg>
                            <span class="feature-label">Bathrooms</span>
                            <span class="feature-value"><?= (int)$property['bathrooms'] ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($property['area_sqft'] > 0): ?>
                        <div class="feature-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                            <span class="feature-label">Area</span>
                            <span class="feature-value"><?= number_format($property['area_sqft']) ?> sqft</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($property['year_built'] > 0): ?>
                        <div class="feature-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <span class="feature-label">Year Built</span>
                            <span class="feature-value"><?= (int)$property['year_built'] ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Agent Info -->
                <div class="agent-card">
                    <h3>Listed by</h3>
                    <div class="agent-info">
                        <div class="agent-avatar"><?= strtoupper($property['agent_name'][0] ?? 'A') ?></div>
                        <div class="agent-details">
                            <h4><?= Security::escape($property['agent_name']) ?></h4>
                            <p class="agent-contact">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                <?= Security::escape($property['agent_phone'] ?? 'Not provided') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar Actions -->
        <aside class="property-sidebar" aria-label="Property actions">
            <div class="sidebar-card">
                <h3>Take Action</h3>
                
                <?php if (Session::isLoggedIn() && Session::userRole() === 'customer'): ?>
                    <form action="/api/inquiries" method="POST" class="inquiry-form" id="inquiry-form">
                        <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                        <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">
                        
                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message" rows="4" placeholder="Ask about the property, request a viewing, or negotiate..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Send Inquiry</button>
                    </form>
                    
                    <div class="divider">OR</div>
                    
                    <button class="btn btn-secondary btn-block btn-favourite-toggle" data-property-id="<?= $property['id'] ?>" aria-pressed="<?= $isFavourited ? 'true' : 'false' ?>">
                        <svg viewBox="0 0 24 24" fill="<?= $isFavourited ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <span><?= $isFavourited ? 'Saved to Favourites' : 'Save to Favourites' ?></span>
                    </button>
                    
                <?php elseif (Session::isLoggedIn() && Session::userRole() === 'agent' && Auth::owns($property['user_id'])): ?>
                    <a href="/agent/properties/<?= $property['id'] ?>/edit" class="btn btn-primary btn-block">Edit Property</a>
                    <button class="btn btn-danger btn-block" onclick="confirmDelete(<?= $property['id'] ?>)">Delete Property</button>
                    
                <?php else: ?>
                    <div class="auth-prompt">
                        <p>Login to inquire or save this property</p>
                        <a href="/login" class="btn btn-primary btn-block">Login</a>
                        <a href="/register" class="btn btn-secondary btn-block">Register</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="sidebar-card">
                <h3>Property Details</h3>
                <dl class="details-list">
                    <dt>Property Type</dt>
                    <dd><?= Security::escape($property['category_name'] ?? 'N/A') ?></dd>
                    
                    <dt>Price Type</dt>
                    <dd><?= $priceType ?></dd>
                    
                    <dt>Status</dt>
                    <dd><span class="badge badge-<?= $property['status'] ?>"><?= $statusLabels[$property['status']] ?? ucfirst($property['status']) ?></span></dd>
                    
                    <dt>Address</dt>
                    <dd><?= Security::escape($property['address']) ?>, <?= Security::escape($property['city']) ?>, <?= Security::escape($property['state'] ?? '') ?> <?= Security::escape($property['postal_code'] ?? '') ?></dd>
                    
                    <?php if ($property['latitude'] && $property['longitude']): ?>
                        <dt>Coordinates</dt>
                        <dd><?= Security::escape($property['latitude']) ?>, <?= Security::escape($property['longitude']) ?></dd>
                    <?php endif; ?>
                    
                    <dt>Views</dt>
                    <dd><?= number_format($property['views_count'] ?? 0) ?></dd>
                    
                    <dt>Listed</dt>
                    <dd><?= date('M d, Y', strtotime($property['created_at'])) ?></dd>
                    
                    <?php if ($property['approved_at']): ?>
                        <dt>Approved</dt>
                        <dd><?= date('M d, Y', strtotime($property['approved_at'])) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </aside>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gallery thumbnail click
    const thumbs = document.querySelectorAll('.thumb-btn');
    const mainImage = document.getElementById('main-image');
    
    thumbs.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const imgSrc = this.dataset.image;
            if (mainImage && imgSrc) {
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
    
    // Favourite toggle
    const favBtn = document.querySelector('.btn-favourite-toggle');
    if (favBtn) {
        favBtn.addEventListener('click', async function() {
            const propertyId = this.dataset.propertyId;
            const isFav = this.getAttribute('aria-pressed') === 'true';
            
            try {
                const response = await fetch('/api/favourites/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ property_id: propertyId })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.setAttribute('aria-pressed', data.data.favourited ? 'true' : 'false');
                    this.querySelector('svg').setAttribute('fill', data.data.favourited ? 'currentColor' : 'none');
                    this.querySelector('span').textContent = data.data.favourited ? 'Saved to Favourites' : 'Save to Favourites';
                }
            } catch (e) {
                console.error('Favourite toggle failed:', e);
            }
        });
    }
    
    // Inquiry form
    const inquiryForm = document.getElementById('inquiry-form');
    if (inquiryForm) {
        inquiryForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('/api/inquiries', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('Inquiry sent successfully!');
                    this.reset();
                } else {
                    alert(result.message || 'Failed to send inquiry');
                }
            } catch (e) {
                console.error('Inquiry failed:', e);
                alert('An error occurred');
            }
        });
    }
    
    // Delete confirmation
    window.confirmDelete = function(id) {
        if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
            fetch('/agent/properties/' + id + '/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      window.location.href = '/agent/dashboard';
                  } else {
                      alert(data.message || 'Failed to delete');
                  }
              });
        }
    };
});
</script>