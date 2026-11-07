<?php
/**
 * Favourite Controller
 * CIS324 Real Estate Portal
 */

class FavouriteController extends Controller
{
    private Favourite $favouriteModel;

    public function __construct()
    {
        $this->favouriteModel = new Favourite();
    }

    /**
     * Toggle favourite (AJAX)
     */
    public function toggle(): void
    {
        Auth::requireAuth();
        Auth::requireRole('customer');
        
        $propertyId = (int)$this->input('property_id');
        
        if ($propertyId <= 0) {
            $this->jsonError('Invalid property');
        }
        
        // Check property exists and is approved
        $propertyModel = new Property();
        $property = $propertyModel->find($propertyId);
        
        if (!$property || $property['status'] !== 'approved') {
            $this->jsonError('Property not available');
        }
        
        $result = $this->favouriteModel->toggle(Session::userId(), $propertyId);
        
        $this->jsonSuccess($result, $result['favourited'] ? 'Added to favourites' : 'Removed from favourites');
    }

    /**
     * Get user's favourites
     */
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requireRole('customer');
        
        $page = max(1, (int)($this->input('page') ?? 1));
        $result = $this->favouriteModel->getByUser(Session::userId(), $page);
        
        $this->view('dashboard/customer_favourites', [
            'properties' => $result['data'],
            'pagination' => $result['pagination'],
        ]);
    }

    /**
     * Check if property is favourited (for AJAX)
     */
    public function check(int $propertyId): void
    {
        Auth::requireAuth();
        
        $isFavourited = $this->favouriteModel->isFavourited(Session::userId(), $propertyId);
        $count = $this->favouriteModel->getPropertyFavouriteCount($propertyId);
        
        $this->jsonSuccess([
            'favourited' => $isFavourited,
            'count' => $count,
        ]);
    }
}