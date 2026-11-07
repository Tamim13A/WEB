<?php
/**
 * Property Controller
 * CIS324 Real Estate Portal
 */

class PropertyController extends Controller
{
    private Property $propertyModel;
    private PropertyImage $imageModel;
    private PropertyCategory $categoryModel;

    public function __construct()
    {
        $this->propertyModel = new Property();
        $this->imageModel = new PropertyImage();
        $this->categoryModel = new PropertyCategory();
    }

    /**
     * Public property listing
     */
    public function index(): void
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
        $result = $this->propertyModel->getApproved($page, 12, $filters);
        
        $categories = $this->categoryModel->getActive();
        $cities = $this->propertyModel->getCities();

        $this->view('property/index', [
            'properties' => $result['data'],
            'pagination' => $result['pagination'],
            'filters' => $filters,
            'categories' => $categories,
            'cities' => $cities,
            'csrf_token' => Security::generateCsrfToken(),
        ]);
    }

    /**
     * Property details
     */
    public function show(int $id): void
    {
        $property = $this->propertyModel->findWithRelations($id);
        
        if (!$property) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        // Check if user can view (approved or owner or admin)
        $canView = $property['status'] === 'approved' 
            || (Session::isLoggedIn() && (Auth::owns($property['user_id']) || Auth::isAdmin()));

        if (!$canView) {
            http_response_code(403);
            $this->view('errors/403');
            return;
        }

        // Increment views for approved properties
        if ($property['status'] === 'approved') {
            $this->propertyModel->incrementViews($id);
        }

        // Check if favourited
        $isFavourited = false;
        if (Session::isLoggedIn() && Session::userRole() === 'customer') {
            $favouriteModel = new Favourite();
            $isFavourited = $favouriteModel->isFavourited(Session::userId(), $id);
        }

        $this->view('property/show', [
            'property' => $property,
            'isFavourited' => $isFavourited,
            'csrf_token' => Security::generateCsrfToken(),
        ]);
    }

    /**
     * Show create form (agent only)
     */
    public function create(): void
    {
        Auth::requireAgent();
        
        $categories = $this->categoryModel->getActive();
        
        $this->view('property/create', [
            'categories' => $categories,
            'csrf_token' => Security::generateCsrfToken(),
        ]);
    }

    /**
     * Store new property (agent only)
     */
    public function store(): void
    {
        Auth::requireAgent();
        
        $data = [
            'user_id' => Session::userId(),
            'category_id' => (int)$this->input('category_id'),
            'title' => trim($this->input('title')),
            'description' => trim($this->input('description')),
            'price' => (float)$this->input('price'),
            'price_type' => $this->input('price_type'),
            'address' => trim($this->input('address')),
            'city' => trim($this->input('city')),
            'state' => trim($this->input('state')),
            'postal_code' => trim($this->input('postal_code')),
            'country' => trim($this->input('country')) ?? 'Bangladesh',
            'latitude' => $this->input('latitude') ? (float)$this->input('latitude') : null,
            'longitude' => $this->input('longitude') ? (float)$this->input('longitude') : null,
            'bedrooms' => (int)$this->input('bedrooms'),
            'bathrooms' => (int)$this->input('bathrooms'),
            'area_sqft' => $this->input('area_sqft') ? (int)$this->input('area_sqft') : null,
            'year_built' => $this->input('year_built') ? (int)$this->input('year_built') : null,
        ];

        $errors = $this->validate($data, [
            'category_id' => 'required|integer',
            'title' => 'required|min:5|max:200',
            'description' => 'required|min:20',
            'price' => 'required|price',
            'price_type' => 'required|in:sale,rent',
            'address' => 'required|min:5|max:300',
            'city' => 'required|min:2|max:100',
            'bedrooms' => 'required|integer',
            'bathrooms' => 'required|integer',
        ]);

        if (!empty($errors)) {
            $this->view('property/create', [
                'errors' => $errors,
                'old' => $data,
                'categories' => $this->categoryModel->getActive(),
                'csrf_token' => Security::generateCsrfToken(),
            ]);
            return;
        }

        // Handle image uploads
        $images = $this->handleImageUploads();
        if (!empty($images['errors'])) {
            $this->view('property/create', [
                'errors' => array_merge($errors, ['images' => $images['errors']]),
                'old' => $data,
                'categories' => $this->categoryModel->getActive(),
                'csrf_token' => Security::generateCsrfToken(),
            ]);
            return;
        }

        // Create property
        $propertyId = $this->propertyModel->create($data);

        // Save images
        foreach ($images['files'] as $index => $image) {
            $this->imageModel->add($propertyId, [
                'file_path' => $image['path'],
                'file_name' => $image['name'],
                'mime_type' => $image['mime'],
                'file_size' => $image['size'],
                'is_primary' => $index === 0 ? 1 : 0,
                'display_order' => $index,
            ]);
        }

        Session::flash('success', 'Property submitted for approval!');
        $this->redirect('/agent/dashboard');
    }

    /**
     * Show edit form (agent owner only)
     */
    public function edit(int $id): void
    {
        Auth::requireAgent();
        
        $property = $this->propertyModel->find($id);
        
        if (!$property) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        Auth::requireOwnership($property['user_id']);

        $categories = $this->categoryModel->getActive();
        $images = $this->imageModel->getByProperty($id);

        $this->view('property/edit', [
            'property' => $property,
            'categories' => $categories,
            'images' => $images,
            'csrf_token' => Security::generateCsrfToken(),
        ]);
    }

    /**
     * Update property (agent owner only)
     */
    public function update(int $id): void
    {
        Auth::requireAgent();
        
        $property = $this->propertyModel->find($id);
        
        if (!$property) {
            http_response_code(404);
            $this->view('errors/404');
            return;
        }

        Auth::requireOwnership($property['user_id']);

        $data = [
            'category_id' => (int)$this->input('category_id'),
            'title' => trim($this->input('title')),
            'description' => trim($this->input('description')),
            'price' => (float)$this->input('price'),
            'price_type' => $this->input('price_type'),
            'address' => trim($this->input('address')),
            'city' => trim($this->input('city')),
            'state' => trim($this->input('state')),
            'postal_code' => trim($this->input('postal_code')),
            'country' => trim($this->input('country')) ?? 'Bangladesh',
            'latitude' => $this->input('latitude') ? (float)$this->input('latitude') : null,
            'longitude' => $this->input('longitude') ? (float)$this->input('longitude') : null,
            'bedrooms' => (int)$this->input('bedrooms'),
            'bathrooms' => (int)$this->input('bathrooms'),
            'area_sqft' => $this->input('area_sqft') ? (int)$this->input('area_sqft') : null,
            'year_built' => $this->input('year_built') ? (int)$this->input('year_built') : null,
        ];

        $errors = $this->validate($data, [
            'category_id' => 'required|integer',
            'title' => 'required|min:5|max:200',
            'description' => 'required|min:20',
            'price' => 'required|price',
            'price_type' => 'required|in:sale,rent',
            'address' => 'required|min:5|max:300',
            'city' => 'required|min:2|max:100',
            'bedrooms' => 'required|integer',
            'bathrooms' => 'required|integer',
        ]);

        // Handle new image uploads
        $images = $this->handleImageUploads();
        if (!empty($images['errors'])) {
            $errors = array_merge($errors, ['images' => $images['errors']]);
        }

        if (!empty($errors)) {
            $this->view('property/edit', [
                'errors' => $errors,
                'old' => array_merge($property, $data),
                'property' => $property,
                'categories' => $this->categoryModel->getActive(),
                'images' => $this->imageModel->getByProperty($id),
                'csrf_token' => Security::generateCsrfToken(),
            ]);
            return;
        }

        // Update property
        $this->propertyModel->update($id, $data);

        // Save new images
        foreach ($images['files'] as $index => $image) {
            $this->imageModel->add($id, [
                'file_path' => $image['path'],
                'file_name' => $image['name'],
                'mime_type' => $image['mime'],
                'file_size' => $image['size'],
                'is_primary' => 0,
                'display_order' => $index + 100,
            ]);
        }

        Session::flash('success', 'Property updated successfully!');
        $this->redirect('/agent/dashboard');
    }

    /**
     * Delete property (agent owner only)
     */
    public function destroy(int $id): void
    {
        Auth::requireAgent();
        
        $property = $this->propertyModel->find($id);
        
        if (!$property) {
            http_response_code(404);
            $this->jsonError('Property not found');
        }

        Auth::requireOwnership($property['user_id']);

        // Delete images from filesystem
        $images = $this->imageModel->getByProperty($id);
        foreach ($images as $image) {
            $filePath = __DIR__ . '/../../public' . $image['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete from database (cascade handles images, inquiries, favourites)
        $this->propertyModel->delete($id);

        Session::flash('success', 'Property deleted successfully!');
        $this->redirect('/agent/dashboard');
    }

    /**
     * Handle image uploads
     */
    private function handleImageUploads(): array
    {
        $config = require __DIR__ . '/../Config/App.php';
        $uploadConfig = $config['upload'];
        
        $result = ['files' => [], 'errors' => []];
        
        if (empty($_FILES['images']['name'][0])) {
            return $result;
        }

        $totalSize = 0;
        $count = count($_FILES['images']['name']);
        
        for ($i = 0; $i < $count; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            
            $file = [
                'name' => $_FILES['images']['name'][$i],
                'type' => $_FILES['images']['type'][$i],
                'tmp_name' => $_FILES['images']['tmp_name'][$i],
                'error' => $_FILES['images']['error'][$i],
                'size' => $_FILES['images']['size'][$i],
            ];
            
            $errors = Security::validateImageUpload($file, $uploadConfig);
            
            if (!empty($errors)) {
                $result['errors'] = array_merge($result['errors'], $errors);
                continue;
            }
            
            $totalSize += $file['size'];
            if ($totalSize > $uploadConfig['max_size'] * 5) { // Max 5 images total size
                $result['errors'][] = 'Total upload size exceeds limit';
                break;
            }
            
            // Generate secure filename
            $filename = Security::generateSecureFilename($file['name']);
            $datePath = date('Y/m/d');
            $saveDir = $uploadConfig['path'] . '/' . $datePath;
            
            if (!is_dir($saveDir)) {
                mkdir($saveDir, 0755, true);
            }
            
            $savePath = $saveDir . '/' . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $savePath)) {
                $result['errors'][] = 'Failed to upload ' . $file['name'];
                continue;
            }
            
            $result['files'][] = [
                'path' => $uploadConfig['url_path'] . '/' . $datePath . '/' . $filename,
                'name' => $filename,
                'mime' => $file['type'],
                'size' => $file['size'],
            ];
        }
        
        return $result;
    }

    /**
     * AJAX: Delete image
     */
    public function deleteImage(): void
    {
        Auth::requireAgent();
        
        $imageId = (int)$this->input('image_id');
        $propertyId = (int)$this->input('property_id');
        
        $property = $this->propertyModel->find($propertyId);
        if (!$property || !Auth::owns($property['user_id'])) {
            $this->jsonError('Unauthorized');
        }
        
        $image = $this->imageModel->find($imageId);
        if (!$image || $image['property_id'] != $propertyId) {
            $this->jsonError('Image not found');
        }
        
        // Don't allow deleting primary if it's the only one
        if ($image['is_primary']) {
            $count = $this->imageModel->countByProperty($propertyId);
            if ($count <= 1) {
                $this->jsonError('Cannot delete the only image');
            }
        }
        
        // Delete file
        $filePath = __DIR__ . '/../../public' . $image['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $this->imageModel->delete($imageId);
        
        $this->jsonSuccess([], 'Image deleted');
    }

    /**
     * AJAX: Set primary image
     */
    public function setPrimaryImage(): void
    {
        Auth::requireAgent();
        
        $imageId = (int)$this->input('image_id');
        $propertyId = (int)$this->input('property_id');
        
        $property = $this->propertyModel->find($propertyId);
        if (!$property || !Auth::owns($property['user_id'])) {
            $this->jsonError('Unauthorized');
        }
        
        $this->imageModel->setPrimary($imageId, $propertyId);
        
        $this->jsonSuccess([], 'Primary image updated');
    }
}