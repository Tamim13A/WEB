<?php
/**
 * Agent Inquiries View
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
?>
<div class="dashboard-page">
    <div class="container">
        <div class="dashboard-header">
            <h1>Property Inquiries</h1>
            <p>Manage inquiries on your properties</p>
        </div>
        
        <?php if (empty($inquiries)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <h3>No Inquiries Yet</h3>
                <p>Inquiries from customers will appear here</p>
            </div>
        <?php else: ?>
            <div class="inquiries-table-wrapper">
                <table class="inquiries-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inquiries as $inquiry): ?>
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <span class="customer-name"><?= Security::escape($inquiry['customer_name']) ?></span>
                                        <span class="customer-contact"><?= Security::escape($inquiry['customer_email']) ?></span>
                                        <?php if ($inquiry['customer_phone']): ?>
                                            <span class="customer-contact"><?= Security::escape($inquiry['customer_phone']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="/properties/<?= $inquiry['property_id'] ?>">
                                        <?= Security::escape($inquiry['property_title']) ?>
                                    </a>
                                </td>
                                <td class="message-preview"><?= Security::escape(mb_substr($inquiry['message'], 0, 100)) ?>...</td>
                                <td>
                                    <span class="badge badge-<?= $inquiry['status'] ?>">
                                        <?= ucfirst($inquiry['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y H:i', strtotime($inquiry['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-secondary btn-sm respond-btn" 
                                            data-inquiry-id="<?= $inquiry['id'] ?>"
                                            data-customer="<?= Security::escape($inquiry['customer_name']) ?>"
                                            data-property="<?= Security::escape($inquiry['property_title']) ?>"
                                            data-message="<?= Security::escape($inquiry['message']) ?>">
                                        Respond
                                    </button>
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

<!-- Respond Modal -->
<div class="modal" id="respond-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" hidden>
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Respond to Inquiry</h3>
            <button class="modal-close" aria-label="Close modal">&times;</button>
        </div>
        <form id="respond-form" class="modal-body">
            <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">
            <input type="hidden" name="inquiry_id" id="respond-inquiry-id">
            
            <div class="form-group">
                <label>Customer</label>
                <p class="form-static" id="respond-customer"></p>
            </div>
            
            <div class="form-group">
                <label>Property</label>
                <p class="form-static" id="respond-property"></p>
            </div>
            
            <div class="form-group">
                <label>Customer's Message</label>
                <p class="form-static" id="respond-message"></p>
            </div>
            
            <div class="form-group">
                <label for="respond-message-input">Your Response</label>
                <textarea id="respond-message-input" name="response" rows="4" required placeholder="Type your response here..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="respond-status">Update Status</label>
                <select id="respond-status" name="status">
                    <option value="contacted">Contacted</option>
                    <option value="negotiating">Negotiating</option>
                    <option value="closed">Closed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                <button type="submit" class="btn btn-primary">Send Response</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('respond-modal');
    const form = document.getElementById('respond-form');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Open modal
    document.querySelectorAll('.respond-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('respond-inquiry-id').value = this.dataset.inquiryId;
            document.getElementById('respond-customer').textContent = this.dataset.customer;
            document.getElementById('respond-property').textContent = this.dataset.property;
            document.getElementById('respond-message').textContent = this.dataset.message;
            document.getElementById('respond-message-input').value = '';
            
            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.getElementById('respond-message-input').focus();
        });
    });
    
    // Close modal
    function closeModal() {
        modal.hidden = true;
        document.body.style.overflow = '';
    }
    
    modal.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', closeModal);
    });
    
    modal.querySelector('.modal-overlay').addEventListener('click', closeModal);
    
    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const inquiryId = document.getElementById('respond-inquiry-id').value;
        const response = document.getElementById('respond-message-input').value;
        const status = document.getElementById('respond-status').value;
        
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';
        
        try {
            const res = await fetch('/agent/inquiries/' + inquiryId + '/respond', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ response, status })
            });
            
            const data = await res.json();
            if (data.success) {
                closeModal();
                location.reload();
            } else {
                alert(data.message || 'Failed to send response');
            }
        } catch (e) {
            console.error('Respond failed:', e);
            alert('An error occurred');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Send Response';
        }
    });
});
</script>