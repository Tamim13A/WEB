<?php
/**
 * Property Card Partial
 * CIS324 Real Estate Portal
 */
$image = $property['primary_image'] ?? null;
$priceType = $property['price_type'] === 'sale' ? 'Sale' : 'Rent';
$statusClass = 'status-' . $property['status'];
?>
<article class="property-card" role="listitem" data-property-id="<?= $property['id'] ?>">
    <div class="property-image">
        <?php if ($image): ?>
            <img src="<?= Security::escape($image) ?>" alt="<?= Security::escape($property['title']) ?>" loading="lazy">
        <?php else: ?>
            <div class="image-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
            </div>
        <?php endif; ?>
        <span class="property-badge <?= $statusClass ?>"><?= ucfirst($priceType) ?></span>
        <?php if ($property['is_featured']): ?>
            <span class="property-badge badge-featured">Featured</span>
        <?php endif; ?>
        <button class="btn-favourite" data-property-id="<?= $property['id'] ?>" aria-label="Add to favourites" aria-pressed="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
        </button>
    </div>
    
    <div class="property-content">
        <div class="property-meta">
            <span class="property-category"><?= Security::escape($property['category_name'] ?? 'Property') ?></span>
            <span class="property-location">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <?= Security::escape($property['city'] ?? 'Unknown') ?>
            </span>
        </div>
        
        <h3 class="property-title">
            <a href="/properties/<?= $property['id'] ?>"><?= Security::escape($property['title']) ?></a>
        </h3>
        
        <p class="property-description"><?= Security::escape(mb_substr($property['description'], 0, 120)) ?>...</p>
        
        <div class="property-features">
            <?php if ($property['bedrooms'] > 0): ?>
                <span class="feature">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <?= (int)$property['bedrooms'] ?> Bed
                </span>
            <?php endif; ?>
            <?php if ($property['bathrooms'] > 0): ?>
                <span class="feature">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"></path><line x1="2" y1="20" x2="22" y2="20"></line></svg>
                    <?= (int)$property['bathrooms'] ?> Bath
                </span>
            <?php endif; ?>
            <?php if ($property['area_sqft'] > 0): ?>
                <span class="feature">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <?= number_format($property['area_sqft']) ?> sqft
                </span>
            <?php endif; ?>
        </div>
        
        <div class="property-footer">
            <div class="property-price">
                <span class="price-amount">৳<?= number_format($property['price'], 0) ?></span>
                <span class="price-period">/<?= $priceType === 'Rent' ? 'month' : 'total' ?></span>
            </div>
            <a href="/properties/<?= $property['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
        </div>
    </div>
</article>