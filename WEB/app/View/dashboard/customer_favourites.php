<?php
/**
 * Customer Favourites View
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
?>
<div class="dashboard-page">
    <div class="container">
        <div class="dashboard-header">
            <h1>My Favourites</h1>
            <p>Properties you've saved</p>
        </div>
        
        <?php if (empty($properties)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <h3>No Favourites Yet</h3>
                <p>Start saving properties you like by clicking the heart icon</p>
                <a href="/properties" class="btn btn-primary">Browse Properties</a>
            </div>
        <?php else: ?>
            <div class="properties-grid">
                <?php foreach ($properties as $property): ?>
                    <?php include __DIR__ . '/../partials/property_card.php'; ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if (($pagination['last_page'] ?? 1) > 1): ?>
                <nav class="pagination" aria-label="Favourite pages">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <a href="?page=<?= $pagination['current_page'] - 1 ?>" class="page-link" rel="prev">Previous</a>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $pagination['current_page'] - 2);
                    $end = min($pagination['last_page'], $pagination['current_page'] + 2);
                    
                    if ($start > 1) echo '<span class="page-ellipsis">...</span>';
                    for ($i = $start; $i <= $end; $i++): ?>
                        <a href="?page=<?= $i ?>" class="page-link <?= $i == $pagination['current_page'] ? 'active' : '' ?>" <?= $i == $pagination['current_page'] ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                    <?php endfor;
                    if ($end < $pagination['last_page']) echo '<span class="page-ellipsis">...</span>';
                    ?>
                    
                    <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                        <a href="?page=<?= $pagination['current_page'] + 1 ?>" class="page-link" rel="next">Next</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>