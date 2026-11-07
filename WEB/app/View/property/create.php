<?php
/**
 * Property Create View
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
?>
<div class="page-header">
    <div class="container">
        <h1>Add New Property</h1>
        <p class="page-subtitle">List your property for sale or rent</p>
    </div>
</div>

<div class="container">
    <form method="POST" enctype="multipart/form-data" class="property-form" novalidate>
        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= Security::escape($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="form-section">
            <h3>Basic Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="category_id">Property Type *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select Type</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($old['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= Security::escape($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price_type">Sale or Rent *</label>
                    <select id="price_type" name="price_type" required>
                        <option value="">Select</option>
                        <option value="sale" <?= ($old['price_type'] ?? '') === 'sale' ? 'selected' : '' ?>>For Sale</option>
                        <option value="rent" <?= ($old['price_type'] ?? '') === 'rent' ? 'selected' : '' ?>>For Rent</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="title">Property Title *</label>
                <input type="text" id="title" name="title" required minlength="5" maxlength="200"
                       value="<?= Security::escape($old['title'] ?? '') ?>" placeholder="e.g., Modern 3BR Apartment in Gulshan">
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" required minlength="20" rows="5"
                          placeholder="Describe the property features, amenities, nearby facilities..."><?= Security::escape($old['description'] ?? '') ?></textarea>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Pricing</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="price">Price (৳) *</label>
                    <input type="number" id="price" name="price" required min="1" step="1000"
                           value="<?= Security::escape($old['price'] ?? '') ?>" placeholder="e.g., 12500000">
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Location</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="address">Street Address *</label>
                    <input type="text" id="address" name="address" required minlength="5" maxlength="300"
                           value="<?= Security::escape($old['address'] ?? '') ?>" placeholder="House 45, Road 12">
                </div>
                
                <div class="form-group">
                    <label for="city">City *</label>
                    <input type="text" id="city" name="city" required minlength="2" maxlength="100"
                           value="<?= Security::escape($old['city'] ?? '') ?>" placeholder="Dhaka">
                </div>
                
                <div class="form-group">
                    <label for="state">State/Region</label>
                    <input type="text" id="state" name="state" maxlength="100"
                           value="<?= Security::escape($old['state'] ?? '') ?>" placeholder="Dhaka Division">
                </div>
                
                <div class="form-group">
                    <label for="postal_code">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" maxlength="20"
                           value="<?= Security::escape($old['postal_code'] ?? '') ?>" placeholder="1212">
                </div>
                
                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" maxlength="100"
                           value="<?= Security::escape($old['country'] ?? 'Bangladesh') ?>" placeholder="Bangladesh">
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="latitude">Latitude</label>
                    <input type="number" id="latitude" name="latitude" step="any" min="-90" max="90"
                           value="<?= Security::escape($old['latitude'] ?? '') ?>" placeholder="23.7806">
                </div>
                
                <div class="form-group">
                    <label for="longitude">Longitude</label>
                    <input type="number" id="longitude" name="longitude" step="any" min="-180" max="180"
                           value="<?= Security::escape($old['longitude'] ?? '') ?>" placeholder="90.4185">
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Property Features</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="bedrooms">Bedrooms *</label>
                    <input type="number" id="bedrooms" name="bedrooms" required min="0" max="20"
                           value="<?= Security::escape($old['bedrooms'] ?? '0') ?>">
                </div>
                
                <div class="form-group">
                    <label for="bathrooms">Bathrooms *</label>
                    <input type="number" id="bathrooms" name="bathrooms" required min="0" max="20"
                           value="<?= Security::escape($old['bathrooms'] ?? '0') ?>">
                </div>
                
                <div class="form-group">
                    <label for="area_sqft">Area (sq ft)</label>
                    <input type="number" id="area_sqft" name="area_sqft" min="0"
                           value="<?= Security::escape($old['area_sqft'] ?? '') ?>" placeholder="e.g., 1800">
                </div>
                
                <div class="form-group">
                    <label for="year_built">Year Built</label>
                    <input type="number" id="year_built" name="year_built" min="1900" max="<?= date('Y') ?>"
                           value="<?= Security::escape($old['year_built'] ?? '') ?>" placeholder="2020">
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Property Images</h3>
            <p class="form-hint">Upload up to 10 images (JPG, PNG, WebP). Max 5MB each. First image becomes primary.</p>
            
            <div class="image-upload">
                <div class="upload-area" id="upload-area">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <p>Drag & drop images here, or click to browse</p>
                    <small>JPG, PNG, WebP • Max 5MB each • Up to 10 images</small>
                </div>
                <input type="file" name="images[]" id="images" multiple accept="image/jpeg,image/png,image/webp" style="display: none;">
                <div class="upload-preview" id="upload-preview"></div>
            </div>
        </div>
        
        <div class="form-actions">
            <a href="/agent/dashboard" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg">Submit for Approval</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('upload-area');
    const fileInput = document.getElementById('images');
    const previewContainer = document.getElementById('upload-preview');
    
    // Click to upload
    uploadArea.addEventListener('click', (e) => {
        if (e.target === uploadArea || uploadArea.contains(e.target)) {
            fileInput.click();
        }
    });
    
    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('drag-over');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('drag-over');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFiles(fileInput.files);
        }
    });
    
    // File input change
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            handleFiles(fileInput.files);
        }
    });
    
    function handleFiles(files) {
        previewContainer.innerHTML = '';
        
        Array.from(files).forEach((file, index) => {
            if (!file.type.startsWith('image/')) return;
            if (file.size > 5 * 1024 * 1024) {
                alert('File ' + file.name + ' exceeds 5MB limit');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = (e) => {
                const item = document.createElement('div');
                item.className = 'upload-preview-item';
                item.innerHTML = `
                    <img src="${e.target.result}" alt="Preview ${index + 1}">
                    <button type="button" class="upload-preview-remove" data-index="${index}" aria-label="Remove image">&times;</button>
                `;
                previewContainer.appendChild(item);
            };
            reader.readAsDataURL(file);
        });
    }
    
    // Remove preview
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('upload-preview-remove')) {
            const index = parseInt(e.target.dataset.index);
            const dt = new DataTransfer();
            Array.from(fileInput.files).forEach((file, i) => {
                if (i !== index) dt.items.add(file);
            });
            fileInput.files = dt.files;
            handleFiles(fileInput.files);
        }
    });
});
</script>