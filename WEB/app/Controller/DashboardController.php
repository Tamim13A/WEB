<?php
/**
 * Dashboard Controller - Single entry point
 * CIS324 Real Estate Portal
 */

class DashboardController extends Controller
{
    private User $u;
    private Property $p;
    private Inquiry $i;
    private Favourite $f;

    public function __construct()
    {
        $this->u = new User();
        $this->p = new Property();
        $this->i = new Inquiry();
        $this->f = new Favourite();
    }

    public function index(): void
    {
        Auth::requireAuth();
        $role = Session::userRole();
        $uid = Session::userId();

        $data = match ($role) {
            'customer' => $this->customerData($uid),
            'agent' => $this->agentData($uid),
            'admin' => $this->adminData(),
            default => $this->redirect('/'),
        };

        $this->view("dashboard/{$role}", $data);
    }

    private function customerData(int $uid): array
    {
        $favs = $this->f->getByUser($uid, 1, 4);
        $inqs = $this->i->getByCustomer($uid, 1, 4);
        return [
            'stats' => [
                'favourites_count' => $this->f->count(['user_id' => $uid]),
                'inquiries_count' => $this->i->count(['user_id' => $uid]),
                'pending_inquiries' => $this->i->count(['user_id' => $uid, 'status' => 'new']),
            ],
            'favourites' => $favs['data'],
            'inquiries' => $inqs['data'],
        ];
    }

    private function agentData(int $aid): array
    {
        $props = $this->p->getByAgent($aid, 1, 5);
        $inqs = $this->i->getByAgent($aid, 1, 5);
        $istats = $this->i->getStats($aid);
        return [
            'stats' => [
                'total_properties' => $this->p->count(['user_id' => $aid]),
                'approved_properties' => $this->p->count(['user_id' => $aid, 'status' => 'approved']),
                'pending_properties' => $this->p->count(['user_id' => $aid, 'status' => 'pending']),
                'total_inquiries' => $istats['total'] ?? 0,
                'new_inquiries' => $istats['new'] ?? 0,
            ],
            'properties' => $props['data'],
            'inquiries' => $inqs['data'],
        ];
    }

    private function adminData(): array
    {
        return [
            'userStats' => [
                'total_users' => $this->u->count(),
                'total_agents' => $this->u->count(['role' => 'agent']),
                'total_customers' => $this->u->count(['role' => 'customer']),
                'active_users' => $this->u->count(['is_active' => 1]),
            ],
            'propertyStats' => $this->p->getStats(),
            'inquiryStats' => $this->i->getStats(),
            'pendingProperties' => $this->p->getPending(1, 5)['data'],
            'recentUsers' => array_slice($this->u->all(['*'], 'created_at', 'DESC'), 0, 5),
        ];
    }
}