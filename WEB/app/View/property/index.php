<?php
/**
 * Property Listing View
 * CIS324 Real Estate Portal
 */
?>
<div class="page-header">
    <div class="container">
        <h1>Property Listings</h1>
        <p class="page-subtitle">Find your dream property among thousands of listings</p>
    </div>
</div>

<div class="container">
    <div class="properties-layout">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar" aria-label="Property filters">
            <form method="GET" class="filters-form" id="filters-form">
                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" placeholder="Keywords, location..." value="<?= Security::escape($filters['search'] ?? '') ?>">
                </div>
                
                <div class="filter-group">
                    <label for="category_id">Property Type</label>
                    <select id="category_id" name="category_id">
                        <option value="">All Types</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= Security::escape($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="price_type">Sale / Rent</label>
                    <select id="price_type" name="price_type">
                        <option value="">All</option>
                        <option value="sale" <?= ($filters['price_type'] ?? '') === 'sale' ? 'selected' : '' ?>>For Sale</option>
                        <option value="rent" <?= ($filters['price_type'] ?? '') === 'rent' ? 'selected' : '' ?>>For Rent</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="city">City</label>
                    <select id="city" name="city">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= Security::escape($city) ?>" <?= ($filters['city'] ?? '') === $city ? 'selected' : '' ?>>
                                <?= Security::escape($city) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="min_price">Min Price</label>
                        <input type="number" id="min_price" name="min_price" min="0" step="1000" placeholder="Min" value="<?= Security::escape($filters['min_price'] ?? '') ?>">
                    </div>
                    <div class="filter-group">
                        <label for="max_price">Max Price</label>
                        <input type="number" id="max_price" name="max_price" min="0" step="1000" placeholder="Max" value="<?= Security::escape($filters['max_price'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="filter-group">
                    <label for="min_bedrooms">Min Bedrooms</label>
                    <select id="min_bedrooms" name="min_bedrooms">
                        <option value="">Any</option>
                        <option value="1" <?= ($filters['min_bedrooms'] ?? '') == 1 ? 'selected' : '' ?>>1+</option>
                        <option value="2" <?= ($filters['min_bedrooms'] ?? '') == 2 ? 'selected' : '' ?>>2+</option>
                        <option value="3" <?= ($filters['min_bedrooms'] ?? '') == 3 ? 'selected' : '' ?>>3+</option>
                        <option value="4" <?= ($filters['min_bedrooms'] ?? '') == 4 ? 'selected' : '' ?>>4+</option>
                        <option value="5" <?= ($filters['min_bedrooms'] ?? '') == 5 ? 'selected' : '' ?>>5+</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                <a href="/properties" class="btn btn-secondary btn-block">Clear Filters</a>
            </form>
        </aside>
        
        <!-- Properties Grid -->
        <div class="properties-main">
            <div class="results-header">
                <p class="results-count">
                    Showing <?= $pagination['from'] ?? 0 ?>–<?= $pagination['to'] ?? 0 ?> of <?= $pagination['total'] ?? 0 ?> properties
                </p>
                <div class="view-toggle" role="group" aria-label="View options">
                    <button type="button" class="view-btn active" data-view="grid" aria-label="Grid view" aria-pressed="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect></svg>
                    </button>
                    <button type="button" class="view-btn" data-view="list" aria-label="List view" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                </div>
            </div>
            
            <?php if (empty($properties)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <h3>No Properties Found</h3>
                    <p>Try adjusting your filters or search terms</p>
                    <a href="/properties" class="btn btn-primary">View All Properties</a>
                </div>
            <?php else: ?>
                <div class="properties-grid" id="properties-grid" role="list">
                    <?php foreach ($properties as $property): ?>
                        <?php include __DIR__ . '/../partials/property_card.php'; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if (($pagination['last_page'] ?? 1) > 1): ?>
                    <nav class="pagination" aria-label="Property pages">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>" class="page-link" rel="prev">Previous</a>
                        <?php endif; ?>
                        
                        <?php
                        $start = max(1, $pagination['current_page'] - 2);
                        $end = min($pagination['last_page'], $pagination['current_page'] + 2);
                        
                        if ($start > 1) echo '<span class="page-ellipsis">...</span>';
                        for ($i = $start; $i <= $end; $i++): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-link <?= $i == $pagination['current_page'] ? 'active' : '' ?>" <?= $i == $pagination['current_page'] ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                        <?php endfor;
                        if ($end < $pagination['last_page']) echo '<span class="page-ellipsis">...</span>';
                        ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>" class="page-link" rel="next">Next</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>