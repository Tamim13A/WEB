<?php
/**
 * Customer Inquiries View
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
?>
<div class="dashboard-page">
    <div class="container">
        <div class="dashboard-header">
            <h1>My Inquiries</h1>
            <p>Track your property inquiries</p>
        </div>
        
        <?php if (empty($inquiries)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <h3>No Inquiries Yet</h3>
                <p>Inquiries you send will appear here</p>
                <a href="/properties" class="btn btn-primary">Browse Properties</a>
            </div>
        <?php else: ?>
            <div class="inquiries-table-wrapper">
                <table class="inquiries-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Agent Response</th>
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
                                <td><?= date('M d, Y H:i', strtotime($inquiry['created_at'])) ?></td>
                                <td>
                                    <?php if ($inquiry['agent_response']): ?>
                                        <span class="response-preview"><?= Security::escape(mb_substr($inquiry['agent_response'], 0, 80)) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">No response yet</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/customer/inquiries/<?= $inquiry['id'] ?>" class="btn btn-secondary btn-sm">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if (($pagination['last_page'] ?? 1) > 1): ?>
                <nav class="pagination" aria-label="Inquiry pages">
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