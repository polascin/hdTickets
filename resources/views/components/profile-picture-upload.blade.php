@props(['user'])

<div class="profile-picture-section">
    <div class="profile-picture-header">
        <h3>Profile Picture</h3>
    </div>

    <div class="profile-picture-container">
        <!-- Profile Picture Preview -->
        <div class="profile-picture-preview">
            <div class="profile-picture" id="profile-picture-preview" data-profile-picture>
                @if($user->profile_picture)
                    <img src="{{ $user->getProfileDisplay()['picture_url'] }}" alt="{{ $user->getFullNameAttribute() }}" />
                @else
                    <span class="user-initials">{{ $user->getProfileDisplay()['initials'] }}</span>
                @endif
            </div>
            <div class="profile-picture-loading">
                <div class="spinner"></div>
            </div>
        </div>

        <!-- Upload Controls -->
        <div class="profile-picture-controls">
            <div class="upload-section">
                <h4>Upload New Picture</h4>
                <p>Upload a new profile picture. For best results, use a square image that's at least 200x200 pixels.</p>
                
                <!-- Drop Zone -->
                <div class="profile-picture-drop-zone" id="profile-picture-drop-zone">
                    <div class="drop-zone-content">
                        <div class="drop-zone-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M14.25 9.75L16.5 12L14.25 14.25M9.75 14.25L7.5 12L9.75 9.75M6 20.25H18C19.24 20.25 20.25 19.24 20.25 18V6C20.25 4.76 19.24 3.75 18 3.75H6C4.76 3.75 3.75 4.76 3.75 6V18C3.75 19.24 4.76 20.25 6 20.25Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M3.75 15.75L7.5 12L10.5 15L13.5 12L20.25 18.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="drop-zone-text">Drop your image here or click to browse</div>
                        <div class="drop-zone-subtext">JPG, PNG, WEBP up to 5MB</div>
                    </div>
                </div>

                <!-- Hidden File Input -->
                <input type="file" id="profile-picture-input" accept="image/jpeg,image/jpg,image/png,image/webp" style="display: none;">

                <!-- Action Buttons -->
                <div class="profile-picture-buttons">
                    <button type="button" id="profile-picture-upload-btn" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 15v4c0 .55-.45 1-1 1H4c-.55 0-1-.45-1-1v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Choose Photo
                    </button>
                    
                    @if($user->profile_picture)
                    <button type="button" id="profile-picture-delete-btn" class="btn btn-outline-danger">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 6h18M8 6V4c0-.55.45-1 1-1h6c.55 0 1 .45 1 1v2M19 6v14c0 .55-.45 1-1 1H6c-.55 0-1-.45-1-1V6h14zM10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Remove
                    </button>
                    @endif
                </div>

                <!-- Upload Requirements -->
                <div class="upload-requirements">
                    <h5>Image Requirements</h5>
                    <ul>
                        <li>Supported formats: JPG, JPEG, PNG, WEBP</li>
                        <li>Maximum file size: 5MB</li>
                        <li>Minimum dimensions: 100x100 pixels</li>
                        <li>Recommended: Square images work best</li>
                        <li>Images will be automatically optimized and resized</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Crop Modal -->
<div id="crop-modal" class="crop-modal" style="display: none;">
    <div class="crop-modal-content">
        <div class="crop-modal-header">
            <h3>Crop Your Picture</h3>
        </div>
        
        <div class="crop-preview-container">
            <img id="crop-preview" class="crop-preview" alt="Image to crop" />
        </div>
        
        <div class="crop-modal-buttons">
            <button type="button" id="cancel-crop-btn" class="btn btn-outline-secondary">Cancel</button>
            <button type="button" id="apply-crop-btn" class="btn btn-primary">Save Photo</button>
        </div>
    </div>
</div>

@push('styles')
<link href="{{ css_timestamp('css/profile-picture.css') }}" rel="stylesheet">
<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" integrity="sha512-cyzxRvewl+7jiNzQQw2sFOOlUY28D5dSlqpKBOvt7sLw0xFfkBnz4RUIDlgd8WvUSKyf4UKvJJJ8V8oQr5WFkQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@push('scripts')
<!-- Cropper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js" integrity="sha512-6lplKUSl86rUVprDIjiW8DuOniNX8UDoRATqZSds/7t6zCQZfaCe3e5zcGaQwxa8Kpn5RTM9Fvl3X2lLV4grPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- Profile Picture Manager -->
<script src="{{ css_timestamp('js/profile-picture-manager.js') }}"></script>
@endpush
