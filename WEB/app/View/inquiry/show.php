<?php
/**
 * Inquiry Detail View
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
?>
<div class="page-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="/">Home</a></li>
                <li><a href="/customer/inquiries">My Inquiries</a></li>
                <li aria-current="page">Inquiry #<?= $inquiry['id'] ?></li>
            </ol>
        </nav>
        <h1>Inquiry Details</h1>
    </div>
</div>

<div class="container">
    <div class="inquiry-detail">
        <div class="inquiry-main">
            <div class="inquiry-header">
                <div class="inquiry-badges">
                    <span class="badge badge-<?= $inquiry['status'] ?>"><?= ucfirst($inquiry['status']) ?></span>
                    <span class="badge badge-<?= $inquiry['price_type'] ?>"><?= ucfirst($inquiry['price_type']) ?></span>
                </div>
                
                <h2><?= Security::escape($inquiry['property_title']) ?></h2>
                
                <div class="inquiry-meta">
                    <span>Submitted: <?= date('M d, Y H:i', strtotime($inquiry['created_at'])) ?></span>
                    <?php if ($inquiry['responded_at']): ?>
                        <span>Agent replied: <?= date('M d, Y H:i', strtotime($inquiry['responded_at'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="inquiry-content">
                <h3>Your Message</h3>
                <div class="message-box customer-message">
                    <p><?= nl2br(Security::escape($inquiry['message'])) ?></p>
                </div>
                
                <?php if ($inquiry['agent_response']): ?>
                    <h3>Agent Response</h3>
                    <div class="message-box agent-response">
                        <p><?= nl2br(Security::escape($inquiry['agent_response'])) ?></p>
                        <div class="response-meta">
                            <span>Replied by: <?= Security::escape($inquiry['agent_name'] ?? 'Agent') ?></span>
                            <span><?= date('M d, Y H:i', strtotime($inquiry['responded_at'])) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="inquiry-property">
                <h3>Property Details</h3>
                <div class="property-summary">
                    <img src="<?= Security::escape($inquiry['property_image'] ?? '/assets/images/placeholder.svg') ?>" alt="" class="property-thumb-large">
                    <div class="property-summary-info">
                        <h4><a href="/properties/<?= $inquiry['property_id'] ?>"><?= Security::escape($inquiry['property_title']) ?></a></h4>
                        <p class="property-price">৳<?= number_format($inquiry['price']) ?> /<?= $inquiry['price_type'] === 'rent' ? 'month' : 'total' ?></p>
                        <p class="property-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <?= Security::escape($inquiry['address']) ?>, <?= Security::escape($inquiry['city']) ?>
                        </p>
                        <a href="/properties/<?= $inquiry['property_id'] ?>" class="btn btn-secondary btn-sm">View Property</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>