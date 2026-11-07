<?php
/**
 * Customer Dashboard View
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
?>
<div class="dashboard-page">
    <div class="container">
        <div class="dashboard-header">
            <h1>My Dashboard</h1>
            <p>Welcome back, <?= Security::escape(Session::user()['name'] ?? 'Customer') ?>!</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-favourites">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['favourites_count'] ?></span>
                    <span class="stat-label">Saved Properties</span>
                </div>
                <a href="/customer/favourites" class="stat-link">View All</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-inquiries">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['inquiries_count'] ?></span>
                    <span class="stat-label">Total Inquiries</span>
                </div>
                <a href="/customer/inquiries" class="stat-link">View All</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-pending">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['pending_inquiries'] ?></span>
                    <span class="stat-label">Pending Replies</span>
                </div>
                <a href="/customer/inquiries" class="stat-link">View All</a>
            </div>
        </div>
        
        <!-- Recent Favourites -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Recent Favourites</h2>
                <a href="/customer/favourites" class="btn btn-secondary btn-sm">View All</a>
            </div>
            
            <?php if (empty($favourites)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    <h3>No Favourites Yet</h3>
                    <p>Start saving properties you like</p>
                    <a href="/properties" class="btn btn-primary">Browse Properties</a>
                </div>
            <?php else: ?>
                <div class="properties-grid">
                    <?php foreach ($favourites as $property): ?>
                        <?php include __DIR__ . '/../partials/property_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Inquiries -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Recent Inquiries</h2>
                <a href="/customer/inquiries" class="btn btn-secondary btn-sm">View All</a>
            </div>
            
            <?php if (empty($inquiries)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    <h3>No Inquiries Yet</h3>
                    <p>Start inquiring about properties you're interested in</p>
                </div>
            <?php else: ?>
                <div class="inquiries-table-wrapper">
                    <table class="inquiries-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $inquiry): ?>
                                <tr>
                                    <td>
                                        <a href="/properties/<?= $inquiry['property_id'] ?>">
                                            <img src="<?= Security::escape($inquiry['property_image'] ?? '/assets/images/placeholder.svg') ?>" alt="" class="property-thumb">
                                            <span><?= Security::escape($inquiry['property_title']) ?></span>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $inquiry['status'] ?>">
                                            <?= ucfirst($inquiry['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($inquiry['created_at'])) ?></td>
                                    <td>
                                        <a href="/customer/inquiries/<?= $inquiry['id'] ?>" class="btn btn-secondary btn-sm">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>