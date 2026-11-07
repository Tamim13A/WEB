<?php
/**
 * Admin Controller
 * CIS324 Real Estate Portal
 */

class AdminController extends Controller
{
    private User $userModel;
    private Property $propertyModel;
    private Inquiry $inquiryModel;
    private PropertyCategory $categoryModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->propertyModel = new Property();
        $this->inquiryModel = new Inquiry();
        $this->categoryModel = new PropertyCategory();
    }

    // ==================== USER MANAGEMENT ====================

    /**
     * List all users
     */
    public function users(int $page = 1): void
    {
        Auth::requireAdmin();
        
        $role = $this->input('role');
        $search = $this->input('search');
        
        $where = [];
        if ($role && in_array($role, ['admin', 'agent', 'customer'])) {
            $where['role'] = $role;
        }
        
        $users = $this->userModel->paginate($page, 20, $where);
        
        // Add search filter manually if needed
        if ($search) {
            // Would need custom query for search
        }
        
        $this->view('admin/users/index', [
            'users' => $users['data'],
            'pagination' => $users['pagination'],
            'filters' => ['role' => $role, 'search' => $search],
        ]);
    }

    /**
     * Show user details
     */
    public function showUser(int $id): void
    {
        Auth::requireAdmin();
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }
        
        // Get user's properties
        $properties = $this->propertyModel->getByAgent($id, 1, 10);
        
        // Get user's inquiries
        $inquiries = $this->inquiryModel->getByCustomer($id, 1, 10);
        
        $this->view('admin/users/show', [
            'user' => $user,
            'properties' => $properties['data'],
            'inquiries' => $inquiries['data'],
        ]);
    }

    /**
     * Toggle user active status
     */
    public function toggleUser(int $id): void
    {
        Auth::requireAdmin();
        
        if ($id == Session::userId()) {
            Session::flash('error', 'You cannot deactivate yourself');
            $this->redirect('/admin/users');
            return;
        }
        
        $this->userModel->toggleActive($id);
        
        Session::flash('success', 'User status updated');
        $this->redirect('/admin/users');
    }

    /**
     * Delete user (admin only, with confirmation)
     */
    public function deleteUser(int $id): void
    {
        Auth::requireAdmin();
        
        if ($id == Session::userId()) {
            $this->jsonError('You cannot delete yourself');
        }
        
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->jsonError('User not found');
        }
        
        // Check if user has properties
        if ($user['role'] === 'agent') {
            $propCount = $this->propertyModel->count(['user_id' => $id]);
            if ($propCount > 0) {
                $this->jsonError("Cannot delete agent with {$propCount} properties. Reassign or delete properties first.");
            }
        }
        
        $this->userModel->delete($id);
        
        $this->jsonSuccess([], 'User deleted');
    }

    // ==================== PROPERTY MANAGEMENT ====================

    /**
     * List all properties (admin)
     */
    public function properties(int $page = 1): void
    {
        Auth::requireAdmin();
        
        $status = $this->input('status');
        $search = $this->input('search');
        
        $where = [];
        if ($status && in_array($status, ['pending', 'approved', 'rejected', 'sold', 'rented'])) {
            $where['status'] = $status;
        }
        
        $result = $this->propertyModel->paginate($page, 20, $where, ['*'], 'created_at', 'DESC');
        
        $this->view('admin/properties/index', [
            'properties' => $result['data'],
            'pagination' => $result['pagination'],
            'filters' => ['status' => $status, 'search' => $search],
        ]);
    }

    /**
     * Approve property
     */
    public function approveProperty(int $id): void
    {
        Auth::requireAdmin();
        
        $property = $this->propertyModel->find($id);
        
        if (!$property) {
            $this->jsonError('Property not found');
        }
        
        if ($property['status'] !== 'pending') {
            $this->jsonError('Only pending properties can be approved');
        }
        
        $this->propertyModel->approve($id, Session::userId());
        
        $this->jsonSuccess([], 'Property approved');
    }

    /**
     * Reject property
     */
    public function rejectProperty(int $id): void
    {
        Auth::requireAdmin();
        
        $property = $this->propertyModel->find($id);
        
        if (!$property) {
            $this->jsonError('Property not found');
        }
        
        $reason = trim($this->input('reason'));
        
        if ($reason === '') {
            $this->jsonError('Rejection reason is required', 422);
        }
        
        $this->propertyModel->reject($id, Session::userId(), $reason);
        
        $this->jsonSuccess([], 'Property rejected');
    }

    /**
     * Delete property (admin)
     */
    public function deleteProperty(int $id): void
    {
        Auth::requireAdmin();
        
        $property = $this->propertyModel->find($id);
        
        if (!$property) {
            $this->jsonError('Property not found');
        }
        
        // Delete images
        $images = $this->propertyModel->getImages($id);
        foreach ($images as $image) {
            $filePath = __DIR__ . '/../../public' . $image['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        $this->propertyModel->delete($id);
        
        $this->jsonSuccess([], 'Property deleted');
    }

    // ==================== INQUIRY MANAGEMENT ====================

    /**
     * List all inquiries
     */
    public function inquiries(int $page = 1): void
    {
        Auth::requireAdmin();
        
        $status = $this->input('status');
        
        $where = [];
        if ($status && in_array($status, ['new', 'contacted', 'negotiating', 'closed', 'rejected'])) {
            $where['status'] = $status;
        }
        
        $result = $this->inquiryModel->paginate($page, 20, $where, ['*'], 'created_at', 'DESC');
        
        $this->view('admin/inquiries/index', [
            'inquiries' => $result['data'],
            'pagination' => $result['pagination'],
            'filters' => ['status' => $status],
        ]);
    }

    /**
     * Update inquiry status
     */
    public function updateInquiry(int $id): void
    {
        Auth::requireAdmin();
        
        $status = $this->input('status');
        
        $errors = $this->validate(['status' => $status], [
            'status' => 'required|in:new,contacted,negotiating,closed,rejected',
        ]);
        
        if (!empty($errors)) {
            $this->jsonError('Validation failed', 422, $errors);
        }
        
        $this->inquiryModel->updateStatus($id, $status);
        
        $this->jsonSuccess([], 'Inquiry status updated');
    }

    // ==================== CATEGORY MANAGEMENT ====================

    /**
     * List categories
     */
    public function categories(): void
    {
        Auth::requireAdmin();
        
        $categories = $this->categoryModel->getWithCount();
        
        $this->view('admin/categories/index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Create category
     */
    public function storeCategory(): void
    {
        Auth::requireAdmin();
        
        $data = [
            'name' => trim($this->input('name')),
            'slug' => trim($this->input('slug')),
            'description' => trim($this->input('description')),
            'is_active' => (bool)$this->input('is_active'),
        ];
        
        $errors = $this->validate($data, [
            'name' => 'required|min:2|max:50',
            'slug' => 'required|min:2|max:60',
        ]);
        
        if ($this->categoryModel->findBySlug($data['slug'])) {
            $errors['slug'] = 'Slug already exists';
        }
        
        if (!empty($errors)) {
            $this->jsonError('Validation failed', 422, $errors);
        }
        
        $id = $this->categoryModel->createCategory($data);
        
        $this->jsonSuccess(['id' => $id], 'Category created');
    }

    /**
     * Update category
     */
    public function updateCategory(int $id): void
    {
        Auth::requireAdmin();
        
        $data = [
            'name' => trim($this->input('name')),
            'slug' => trim($this->input('slug')),
            'description' => trim($this->input('description')),
            'is_active' => (bool)$this->input('is_active'),
        ];
        
        $errors = $this->validate($data, [
            'name' => 'required|min:2|max:50',
            'slug' => 'required|min:2|max:60',
        ]);
        
        $existing = $this->categoryModel->findBySlug($data['slug']);
        if ($existing && $existing['id'] != $id) {
            $errors['slug'] = 'Slug already exists';
        }
        
        if (!empty($errors)) {
            $this->jsonError('Validation failed', 422, $errors);
        }
        
        $this->categoryModel->updateCategory($id, $data);
        
        $this->jsonSuccess([], 'Category updated');
    }

    /**
     * Toggle category active
     */
    public function toggleCategory(int $id): void
    {
        Auth::requireAdmin();
        
        $this->categoryModel->toggleActive($id);
        
        $this->jsonSuccess([], 'Category status updated');
    }

    /**
     * Delete category
     */
    public function deleteCategory(int $id): void
    {
        Auth::requireAdmin();
        
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            $this->jsonError('Category not found');
        }
        
        // Check if has properties
        $propCount = $this->propertyModel->count(['category_id' => $id]);
        if ($propCount > 0) {
            $this->jsonError("Cannot delete category with {$propCount} properties");
        }
        
        $this->categoryModel->delete($id);
        
        $this->jsonSuccess([], 'Category deleted');
    }
}