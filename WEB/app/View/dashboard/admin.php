<?php
/**
 * Admin Dashboard View
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
?>
<div class="dashboard-page admin-dashboard">
    <div class="container">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <p>System overview and management</p>
        </div>
        
        <!-- User Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-users">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $userStats['total_users'] ?></span>
                    <span class="stat-label">Total Users</span>
                </div>
                <a href="/admin/users" class="stat-link">Manage</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-agents">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $userStats['total_agents'] ?></span>
                    <span class="stat-label">Agents</span>
                </div>
                <a href="/admin/users?role=agent" class="stat-link">Manage</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-customers">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $userStats['total_customers'] ?></span>
                    <span class="stat-label">Customers</span>
                </div>
                <a href="/admin/users?role=customer" class="stat-link">Manage</a>
            </div>
        </div>
        
        <!-- Property Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-properties">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $propertyStats['total'] ?? 0 ?></span>
                    <span class="stat-label">Total Properties</span>
                </div>
                <a href="/admin/properties" class="stat-link">Manage</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-approved">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $propertyStats['approved'] ?? 0 ?></span>
                    <span class="stat-label">Approved</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-pending">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $propertyStats['pending'] ?? 0 ?></span>
                    <span class="stat-label">Pending Review</span>
                </div>
                <a href="/admin/properties?status=pending" class="stat-link">Review</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-rejected">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $propertyStats['rejected'] ?? 0 ?></span>
                    <span class="stat-label">Rejected</span>
                </div>
            </div>
        </div>
        
        <!-- Inquiry Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-inquiries">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $inquiryStats['total'] ?? 0 ?></span>
                    <span class="stat-label">Total Inquiries</span>
                </div>
                <a href="/admin/inquiries" class="stat-link">Manage</a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-new">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $inquiryStats['new'] ?? 0 ?></span>
                    <span class="stat-label">New</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-contacted">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72"></path></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $inquiryStats['contacted'] ?? 0 ?></span>
                    <span class="stat-label">Contacted</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-closed">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?= $inquiryStats['closed'] ?? 0 ?></span>
                    <span class="stat-label">Closed</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="dashboard-section">
            <h2>Quick Actions</h2>
            <div class="quick-actions">
                <a href="/admin/properties?status=pending" class="action-card">
                    <div class="action-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <h3>Review Properties</h3>
                    <p><?= $propertyStats['pending'] ?? 0 ?> properties awaiting approval</p>
                </a>
                
                <a href="/admin/users" class="action-card">
                    <div class="action-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    </div>
                    <h3>Manage Users</h3>
                    <p><?= $userStats['total_users'] ?> registered users</p>
                </a>
                
                <a href="/admin/categories" class="action-card">
                    <div class="action-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line><circle cx="12" cy="12" r="3"></circle></svg>
                    </div>
                    <h3>Manage Categories</h3>
                    <p>Property categories & types</p>
                </a>
                
                <a href="/admin/inquiries" class="action-card">
                    <div class="action-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <h3>Manage Inquiries</h3>
                    <p><?= $inquiryStats['total'] ?? 0 ?> total inquiries</p>
                </a>
            </div>
        </div>
        
        <!-- Pending Properties -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Properties Pending Approval</h2>
                <a href="/admin/properties?status=pending" class="btn btn-secondary btn-sm">View All</a>
            </div>
            
            <?php if (empty($pendingProperties)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <h3>No Pending Properties</h3>
                    <p>All properties have been reviewed</p>
                </div>
            <?php else: ?>
                <div class="pending-properties-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Agent</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingProperties as $property): ?>
                                <tr>
                                    <td>
                                        <a href="/properties/<?= $property['id'] ?>">
                                            <img src="<?= Security::escape($property['primary_image'] ?? '/assets/images/placeholder.svg') ?>" alt="" class="property-thumb">
                                            <span><?= Security::escape($property['title']) ?></span>
                                        </a>
                                    </td>
                                    <td><?= Security::escape($property['agent_name']) ?></td>
                                    <td><?= Security::escape($property['category_name']) ?></td>
                                    <td>৳<?= number_format($property['price']) ?> (<?= ucfirst($property['price_type']) ?>)</td>
                                    <td><?= date('M d, Y', strtotime($property['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-success btn-sm approve-btn" data-id="<?= $property['id'] ?>">Approve</button>
                                        <button class="btn btn-danger btn-sm reject-btn" data-id="<?= $property['id'] ?>">Reject</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Users -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Recent Users</h2>
                <a href="/admin/users" class="btn btn-secondary btn-sm">View All</a>
            </div>
            
            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td><?= Security::escape($user['name']) ?></td>
                                <td><?= Security::escape($user['email']) ?></td>
                                <td><span class="badge badge-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span></td>
                                <td>
                                    <span class="badge <?= $user['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal" id="reject-modal" role="dialog" aria-modal="true" aria-labelledby="reject-title" hidden>
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="reject-title">Reject Property</h3>
            <button class="modal-close" aria-label="Close modal">&times;</button>
        </div>
        <form id="reject-form" class="modal-body">
            <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">
            <input type="hidden" name="property_id" id="reject-property-id">
            
            <div class="form-group">
                <label for="reject-reason">Reason for Rejection *</label>
                <textarea id="reject-reason" name="reason" rows="4" required placeholder="Explain why this property is being rejected..."></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject Property</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rejectModal = document.getElementById('reject-modal');
    const rejectForm = document.getElementById('reject-form');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Approve buttons
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            if (confirm('Approve this property?')) {
                try {
                    const res = await fetch('/admin/properties/' + id + '/approve', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': csrfToken
                        }
                    });
                    const data = await res.json();
                    if (data.success) location.reload();
                    else alert(data.message);
                } catch (e) { alert('Error'); }
            }
        });
    });
    
    // Reject buttons
    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('reject-property-id').value = this.dataset.id;
            document.getElementById('reject-reason').value = '';
            rejectModal.hidden = false;
            document.body.style.overflow = 'hidden';
            document.getElementById('reject-reason').focus();
        });
    });
    
    // Close modal
    rejectModal.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            rejectModal.hidden = true;
            document.body.style.overflow = '';
        });
    });
    rejectModal.querySelector('.modal-overlay').addEventListener('click', () => {
        rejectModal.hidden = true;
        document.body.style.overflow = '';
    });
    
    // Reject form
    rejectForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('reject-property-id').value;
        const reason = document.getElementById('reject-reason').value;
        
        try {
            const res = await fetch('/admin/properties/' + id + '/reject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ reason })
            });
            const data = await res.json();
            if (data.success) {
                rejectModal.hidden = true;
                document.body.style.overflow = '';
                location.reload();
            } else {
                alert(data.message || 'Failed to reject');
            }
        } catch (e) { alert('Error'); }
    });
});
</script>