<?php
/**
 * API Controller
 * CIS324 Real Estate Portal
 * Provides JSON endpoints for frontend integration
 */

class ApiController extends Controller
{
    private Property $propertyModel;
    private PropertyCategory $categoryModel;

    public function __construct()
    {
        $this->propertyModel = new Property();
        $this->categoryModel = new PropertyCategory();
    }

    /**
     * GET /api/properties
     * List properties with filtering and pagination
     */
    public function properties(): void
    {
        $filters = [
            'category_id' => $this->input('category_id') ? (int)$this->input('category_id') : null,
            'price_type' => $this->input('price_type') ?: null,
            'city' => $this->input('city') ?: null,
            'min_price' => $this->input('min_price') ? (float)$this->input('min_price') : null,
            'max_price' => $this->input('max_price') ? (float)$this->input('max_price') : null,
            'min_bedrooms' => $this->input('min_bedrooms') ? (int)$this->input('min_bedrooms') : null,
            'search' => $this->input('search') ?: null,
        ];

        $page = max(1, (int)($this->input('page') ?? 1));
        $perPage = min(50, max(1, (int)($this->input('per_page') ?? 12)));
        
        $result = $this->propertyModel->search($filters, $page, $perPage);
        
        $this->jsonSuccess([
            'properties' => $result['data'],
            'pagination' => $result['pagination'],
        ]);
    }

    /**
     * GET /api/properties/{id}
     * Get single property details
     */
    public function property(int $id): void
    {
        $property = $this->propertyModel->findWithRelations($id);
        
        if (!$property || $property['status'] !== 'approved') {
            $this->notFound('Property not found');
        }
        
        $this->jsonSuccess(['property' => $property]);
    }

    /**
     * GET /api/properties/featured
     * Get featured properties
     */
    public function featured(): void
    {
        $limit = min(20, max(1, (int)($this->input('limit') ?? 6)));
        $properties = $this->propertyModel->getFeatured($limit);
        
        $this->jsonSuccess(['properties' => $properties]);
    }

    /**
     * GET /api/categories
     * Get all categories with property counts
     */
    public function categories(): void
    {
        $categories = $this->categoryModel->getWithCount();
        $this->jsonSuccess(['categories' => $categories]);
    }

    /**
     * GET /api/cities
     * Get distinct cities
     */
    public function cities(): void
    {
        $cities = $this->propertyModel->getCities();
        $this->jsonSuccess(['cities' => $cities]);
    }

    /**
     * GET /api/properties/stats
     * Get property statistics (admin only)
     */
    public function stats(): void
    {
        // Simple auth check for admin
        if (!Session::isLoggedIn() || !Auth::isAdmin()) {
            $this->unauthorized('Admin access required');
        }
        
        $stats = $this->propertyModel->getStats();
        $this->jsonSuccess(['stats' => $stats]);
    }

    /**
     * POST /api/favourites/toggle
     * Toggle favourite (requires auth)
     */
    public function toggleFavourite(): void
    {
        if (!Session::isLoggedIn() || Session::userRole() !== 'customer') {
            $this->unauthorized('Customer login required');
        }
        
        $propertyId = (int)$this->input('property_id');
        
        if ($propertyId <= 0) {
            $this->jsonError('Invalid property');
        }
        
        $property = $this->propertyModel->find($propertyId);
        if (!$property || $property['status'] !== 'approved') {
            $this->jsonError('Property not available');
        }
        
        $favouriteModel = new Favourite();
        $result = $favouriteModel->toggle(Session::userId(), $propertyId);
        
        $this->jsonSuccess($result);
    }

    /**
     * POST /api/inquiries
     * Submit inquiry (requires auth)
     */
    public function submitInquiry(): void
    {
        if (!Session::isLoggedIn() || Session::userRole() !== 'customer') {
            $this->unauthorized('Customer login required');
        }
        
        $propertyId = (int)$this->input('property_id');
        $message = trim($this->input('message'));
        
        $errors = $this->validate([
            'property_id' => $propertyId,
            'message' => $message,
        ], [
            'property_id' => 'required|integer',
            'message' => 'required|min:10|max:2000',
        ]);
        
        if (!empty($errors)) {
            $this->validationError($errors);
        }
        
        $property = $this->propertyModel->find($propertyId);
        if (!$property || $property['status'] !== 'approved') {
            $this->jsonError('Property not available for inquiries');
        }
        
        // Check duplicate
        $sql = "SELECT COUNT(*) as total FROM `inquiries` WHERE `property_id` = ? AND `user_id` = ?";
        $result = Database::fetchOne($sql, [$propertyId, Session::userId()]);
        if (($result['total'] ?? 0) > 0) {
            $this->jsonError('You have already inquired about this property');
        }
        
        $inquiryModel = new Inquiry();
        $inquiryId = $inquiryModel->createInquiry($propertyId, Session::userId(), $message);
        
        $this->jsonSuccess(['inquiry_id' => $inquiryId], 'Inquiry submitted');
    }
}