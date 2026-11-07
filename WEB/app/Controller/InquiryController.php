<?php
/**
 * Inquiry Controller
 * CIS324 Real Estate Portal
 */

class InquiryController extends Controller
{
    private Inquiry $inquiryModel;

    public function __construct()
    {
        $this->inquiryModel = new Inquiry();
    }

    /**
     * Submit inquiry (customer)
     */
    public function store(): void
    {
        Auth::requireAuth();
        Auth::requireRole('customer');
        
        $propertyId = (int)$this->input('property_id');
        $message = trim($this->input('message'));
        
        $errors = $this->validate([
            'property_id' => $propertyId,
            'message' => $message,
        ], [
            'property_id' => 'required|integer',
            'message' => 'required|min:10|max:2000',
        ]);

        // Check property exists and is approved
        $propertyModel = new Property();
        $property = $propertyModel->find($propertyId);
        
        if (!$property || $property['status'] !== 'approved') {
            $errors['property_id'] = 'Property not available for inquiries';
        }

        // Check if user already inquired
        if (empty($errors)) {
            $existing = $this->inquiryModel->findBy('property_id', $propertyId);
            // Need to check by user_id too - custom query
            $sql = "SELECT COUNT(*) as total FROM `inquiries` WHERE `property_id` = ? AND `user_id` = ?";
            $result = Database::fetchOne($sql, [$propertyId, Session::userId()]);
            if (($result['total'] ?? 0) > 0) {
                $errors['message'] = 'You have already inquired about this property';
            }
        }

        if (!empty($errors)) {
            $this->jsonError('Validation failed', 422, $errors);
        }

        $inquiryId = $this->inquiryModel->createInquiry($propertyId, Session::userId(), $message);
        
        $this->jsonSuccess(['inquiry_id' => $inquiryId], 'Inquiry submitted successfully');
    }

    /**
     * Get customer's inquiries
     */
    public function myInquiries(): void
    {
        Auth::requireAuth();
        Auth::requireRole('customer');
        
        $page = max(1, (int)($this->input('page') ?? 1));
        $result = $this->inquiryModel->getByCustomer(Session::userId(), $page);
        
        $this->view('dashboard/customer_inquiries', [
            'inquiries' => $result['data'],
            'pagination' => $result['pagination'],
        ]);
    }

    /**
     * Get agent's property inquiries
     */
    public function agentInquiries(): void
    {
        Auth::requireAgent();
        
        $page = max(1, (int)($this->input('page') ?? 1));
        $result = $this->inquiryModel->getByAgent(Session::userId(), $page);
        
        $this->view('dashboard/agent_inquiries', [
            'inquiries' => $result['data'],
            'pagination' => $result['pagination'],
        ]);
    }

    /**
     * Respond to inquiry (agent)
     */
    public function respond(int $id): void
    {
        Auth::requireAgent();
        
        $inquiry = $this->inquiryModel->find($id);
        
        if (!$inquiry) {
            http_response_code(404);
            $this->jsonError('Inquiry not found');
        }
        
        // Verify agent owns the property
        $propertyModel = new Property();
        $property = $propertyModel->find($inquiry['property_id']);
        
        if (!$property || !Auth::owns($property['user_id'])) {
            $this->jsonError('Unauthorized');
        }
        
        $response = trim($this->input('response'));
        
        $errors = $this->validate(['response' => $response], [
            'response' => 'required|min:5|max:2000',
        ]);
        
        if (!empty($errors)) {
            $this->jsonError('Validation failed', 422, $errors);
        }
        
        $this->inquiryModel->respond($id, Session::userId(), $response);
        
        $this->jsonSuccess([], 'Response sent successfully');
    }

    /**
     * Update inquiry status (agent/admin)
     */
    public function updateStatus(int $id): void
    {
        Auth::requireAgent();
        
        $inquiry = $this->inquiryModel->find($id);
        
        if (!$inquiry) {
            http_response_code(404);
            $this->jsonError('Inquiry not found');
        }
        
        // Verify ownership or admin
        $propertyModel = new Property();
        $property = $propertyModel->find($inquiry['property_id']);
        
        if (!$property || (!Auth::owns($property['user_id']) && !Auth::isAdmin())) {
            $this->jsonError('Unauthorized');
        }
        
        $status = $this->input('status');
        
        $errors = $this->validate(['status' => $status], [
            'status' => 'required|in:new,contacted,negotiating,closed,rejected',
        ]);
        
        if (!empty($errors)) {
            $this->jsonError('Validation failed', 422, $errors);
        }
        
        $this->inquiryModel->updateStatus($id, $status);
        
        $this->jsonSuccess([], 'Status updated');
    }

    /**
     * View single inquiry (customer/agent/admin)
     */
    public function show(int $id): void
    {
        Auth::requireAuth();
        
        $inquiry = $this->inquiryModel->findWithRelations($id);
        
        if (!$inquiry) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }
        
        // Check permissions
        $canView = false;
        if (Auth::isAdmin()) {
            $canView = true;
        } elseif (Session::userRole() === 'customer' && $inquiry['user_id'] == Session::userId()) {
            $canView = true;
        } elseif (Session::userRole() === 'agent') {
            $propertyModel = new Property();
            $property = $propertyModel->find($inquiry['property_id']);
            if ($property && Auth::owns($property['user_id'])) {
                $canView = true;
            }
        }
        
        if (!$canView) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }
        
        $this->view('inquiry/show', ['inquiry' => $inquiry]);
    }
}