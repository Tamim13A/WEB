<?php
/**
 * Agent Dashboard View
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
?>
<div class="dashboard-page">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>Agent Dashboard</h1>
                <p>Manage your properties and inquiries</p>
            </div>
            <a href="/agent/properties/create" class="btn btn-primary">Add New Property</a>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-properties">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['total_properties'] ?></span>
                    <span class="stat-label">Total Properties</span>
                </div>
                <a href="/agent/dashboard" class="stat-link">View All</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-approved">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['approved_properties'] ?></span>
                    <span class="stat-label">Approved</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-pending">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['pending_properties'] ?></span>
                    <span class="stat-label">Pending Approval</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-inquiries">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['total_inquiries'] ?></span>
                    <span class="stat-label">Total Inquiries</span>
                </div>
                <a href="/agent/inquiries" class="stat-link">View All</a>
            </div>
        </div>
        
        <!-- Recent Properties -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>My Properties</h2>
                <a href="/agent/dashboard" class="btn btn-secondary btn-sm">Manage All</a>
            </div>
            
            <?php if (empty($properties)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <h3>No Properties Yet</h3>
                    <p>Add your first property listing</p>
                    <a href="/agent/properties/create" class="btn btn-primary">Add Property</a>
                </div>
            <?php else: ?>
                <div class="properties-grid">
                    <?php foreach ($properties as $property): ?>
                        <?php include __DIR__ . '/../partials/property_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Inquiries -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Recent Inquiries</h2>
                <a href="/agent/inquiries" class="btn btn-secondary btn-sm">View All</a>
            </div>
            
            <?php if (empty($inquiries)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    <h3>No Inquiries Yet</h3>
                    <p>Inquiries will appear here when customers contact you</p>
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
                                        </div>
                                    </td>
                                    <td>
                                        <a href="/properties/<?= $inquiry['property_id'] ?>">
                                            <?= Security::escape($inquiry['property_title']) ?>
                                        </a>
                                    </td>
                                    <td class="message-preview"><?= Security::escape(mb_substr($inquiry['message'], 0, 80)) ?>...</td>
                                    <td>
                                        <span class="badge badge-<?= $inquiry['status'] ?>">
                                            <?= ucfirst($inquiry['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($inquiry['created_at'])) ?></td>
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
            <?php endif; ?>
        </div>
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
    // Respond modal
    const modal = document.getElementById('respond-modal');
    const form = document.getElementById('respond-form');
    const closeButtons = modal.querySelectorAll('.modal-close');
    
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
    
    closeButtons.forEach(btn => {
        btn.addEventListener('click', closeModal);
    });
    
    modal.querySelector('.modal-overlay').addEventListener('click', closeModal);
    
    function closeModal() {
        modal.hidden = true;
        document.body.style.overflow = '';
    }
    
    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const inquiryId = document.getElementById('respond-inquiry-id').value;
        const response = document.getElementById('respond-message-input').value;
        const status = document.getElementById('respond-status').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
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
        }
    });
});
</script>