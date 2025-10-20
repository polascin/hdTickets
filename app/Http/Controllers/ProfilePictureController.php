<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Log;

use function count;
use function in_array;

class ProfilePictureController extends Controller
{
    /** Maximum file size in bytes (5MB) */
    private const MAX_FILE_SIZE = 5242880; // 5MB

    /** Allowed image formats */
    private const ALLOWED_FORMATS = ['jpg', 'jpeg', 'png', 'webp'];

    /** Profile picture dimensions */
    private const PROFILE_DIMENSIONS = [
        'thumbnail' => 150,   // 150x150 for thumbnails
        'medium'    => 300,      // 300x300 for profile views
        'large'     => 500,        // 500x500 for full size
    ];

    private ImageManager $imageManager;

    public function __construct(private ActivityLogger $activityLogger)
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Upload and process profile picture
     */
    /**
     * Upload
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_picture' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:' . (self::MAX_FILE_SIZE / 1024), // Convert to KB for Laravel validation
                ],
                'crop_data' => 'nullable|json',
            ], [
                'profile_picture.required' => 'Please select an image file to upload.',
                'profile_picture.image'    => 'The uploaded file must be a valid image.',
                'profile_picture.mimes'    => 'Only JPG, JPEG, PNG, and WEBP formats are allowed.',
                'profile_picture.max'      => 'The image file size cannot exceed 5MB.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $file = $request->file('profile_picture');
            $cropData = $request->input('crop_data') ? json_decode((string) $request->input('crop_data'), TRUE) : NULL;

            // Generate unique filename
            $timestamp = now()->format('YmdHis');
            $randomString = Str::random(8);
            $userId = Auth::id();
            $extension = $file->getClientOriginalExtension();
            $baseFilename = "profile_{$userId}_{$timestamp}_{$randomString}";

            // Delete old profile pictures for this user
            $this->deleteOldProfilePictures($userId);

            // Process and save different sizes
            $savedPictures = $this->processAndSaveImage($file, $baseFilename, $cropData);

            // Update user profile picture in database
            $user = Auth::user();
            $user->profile_picture = $savedPictures['medium']; // Use medium size as default
            $user->save();

            // Log activity
            $this->activityLogger->log(
                'profile_picture_updated',
                'Profile picture updated',
                [
                    'user_id'           => $user->id,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size'         => $file->getSize(),
                    'saved_sizes'       => array_keys($savedPictures),
                    'cropped'           => ! empty($cropData),
                ],
                $user,
            );

            return response()->json([
                'success' => TRUE,
                'message' => 'Profile picture updated successfully!',
                'data'    => [
                    'pictures' => $savedPictures,
                    'user'     => $user->getProfileDisplay(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'An error occurred while uploading the profile picture.',
                'error'   => config('app.debug') ? $e->getMessage() : NULL,
            ], 500);
        }
    }

    /**
     * Crop existing profile picture
     */
    /**
     * Crop
     */
    public function crop(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'crop_data' => 'required|json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();
            if (! $user->profile_picture) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'No profile picture found to crop.',
                ], 400);
            }

            $cropData = json_decode((string) $request->input('crop_data'), TRUE);

            // Validate crop data structure
            if (! $this->isValidCropData($cropData)) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Invalid crop data format.',
                ], 400);
            }

            // Get the current profile picture path
            $currentPicturePath = str_replace('storage/', '', $user->profile_picture);
            $fullPath = storage_path('app/public/' . $currentPicturePath);

            if (! file_exists($fullPath)) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Profile picture file not found.',
                ], 404);
            }

            // Create new cropped versions
            $baseFilename = pathinfo($currentPicturePath, PATHINFO_FILENAME);
            $extension = pathinfo($currentPicturePath, PATHINFO_EXTENSION);

            // Add cropped suffix to filename
            $baseFilename .= '_cropped_' . time();

            // Delete old versions
            $this->deleteOldProfilePictures($user->id);

            // Process cropped image
            $savedPictures = $this->processAndSaveImage($fullPath, $baseFilename, $cropData, TRUE);

            // Update user profile picture
            $user->profile_picture = $savedPictures['medium'];
            $user->save();

            // Log activity
            $this->activityLogger->log(
                'profile_picture_cropped',
                'Profile picture cropped',
                [
                    'user_id'     => $user->id,
                    'crop_data'   => $cropData,
                    'saved_sizes' => array_keys($savedPictures),
                ],
                $user,
            );

            return response()->json([
                'success' => TRUE,
                'message' => 'Profile picture cropped successfully!',
                'data'    => [
                    'pictures' => $savedPictures,
                    'user'     => $user->getProfileDisplay(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'An error occurred while cropping the profile picture.',
                'error'   => config('app.debug') ? $e->getMessage() : NULL,
            ], 500);
        }
    }

    /**
     * Delete profile picture
     */
    /**
     * Delete
     */
    public function delete(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (! $user->profile_picture) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'No profile picture found to delete.',
                ], 400);
            }

            // Delete all profile picture files
            $this->deleteOldProfilePictures($user->id);

            // Update user record
            $user->profile_picture = NULL;
            $user->save();

            // Log activity
            $this->activityLogger->log(
                'profile_picture_deleted',
                'Profile picture deleted',
                ['user_id' => $user->id],
                $user,
            );

            return response()->json([
                'success' => TRUE,
                'message' => 'Profile picture deleted successfully!',
                'data'    => [
                    'user' => $user->getProfileDisplay(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'An error occurred while deleting the profile picture.',
                'error'   => config('app.debug') ? $e->getMessage() : NULL,
            ], 500);
        }
    }

    /**
     * Remove profile picture (alias for delete)
     */
    public function remove(): JsonResponse
    {
        return $this->delete();
    }

    /**
     * Get profile picture info
     */
    /**
     * Info
     */
    public function info(): JsonResponse
    {
        try {
            $user = Auth::user();
            $profileDisplay = $user->getProfileDisplay();

            $info = [
                'has_picture'  => $profileDisplay['has_picture'],
                'picture_url'  => $profileDisplay['picture_url'],
                'initials'     => $profileDisplay['initials'],
                'full_name'    => $profileDisplay['full_name'],
                'display_name' => $profileDisplay['display_name'],
            ];

            // If user has a profile picture, get additional info
            if ($user->profile_picture) {
                $currentPicturePath = str_replace('storage/', '', $user->profile_picture);
                $fullPath = storage_path('app/public/' . $currentPicturePath);

                if (file_exists($fullPath)) {
                    $info['file_size'] = filesize($fullPath);
                    $info['file_size_human'] = $this->formatBytes($info['file_size']);

                    // Get image dimensions
                    $imageInfo = getimagesize($fullPath);
                    if ($imageInfo) {
                        $info['dimensions'] = [
                            'width'  => $imageInfo[0],
                            'height' => $imageInfo[1],
                        ];
                    }

                    // Check for different sizes
                    $info['available_sizes'] = $this->getAvailableSizes($user->id);
                }
            }

            return response()->json([
                'success' => TRUE,
                'data'    => $info,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'An error occurred while retrieving profile picture info.',
                'error'   => config('app.debug') ? $e->getMessage() : NULL,
            ], 500);
        }
    }

    /**
     * Get maximum allowed file size
     */
    /**
     * Get  upload limits
     */
    public function getUploadLimits(): JsonResponse
    {
        return response()->json([
            'success' => TRUE,
            'data'    => [
                'max_file_size'       => self::MAX_FILE_SIZE,
                'max_file_size_human' => $this->formatBytes(self::MAX_FILE_SIZE),
                'allowed_formats'     => self::ALLOWED_FORMATS,
                'dimensions'          => self::PROFILE_DIMENSIONS,
            ],
        ]);
    }

    /**
     * Process and save image in multiple sizes
     *
     * @param mixed $source
     */
    /**
     * ProcessAndSaveImage
     *
     * @param mixed $source
     */
    private function processAndSaveImage($source, string $baseFilename, ?array $cropData = NULL, bool $isFilePath = FALSE): array
    {
        $savedPictures = [];

        // Load image
        $image = $isFilePath ? $this->imageManager->read($source) : $this->imageManager->read($source->getPathname());

        // Apply cropping if specified
        if ($cropData && $this->isValidCropData($cropData)) {
            $image = $image->crop(
                (int) $cropData['width'],
                (int) $cropData['height'],
                (int) $cropData['x'],
                (int) $cropData['y'],
            );
        }

        // Convert to WebP for better compression while maintaining quality
        $targetFormat = 'webp';
        $targetExtension = 'webp';

        // Create and save different sizes
        foreach (self::PROFILE_DIMENSIONS as $sizeName => $dimension) {
            // Clone the image for each size
            $sizedImage = clone $image;

            // Resize maintaining aspect ratio
            $sizedImage = $sizedImage->scaleDown($dimension, $dimension);

            // Apply sharpening for better quality after resize
            if ($dimension < 300) {
                $sizedImage = $sizedImage->sharpen(10);
            }

            // Create filename
            $filename = "{$baseFilename}_{$sizeName}.{$targetExtension}";
            $filePath = "profile-pictures/{$filename}";

            // Save with optimized quality
            $quality = $sizeName === 'thumbnail' ? 80 : 90;
            $encodedImage = $sizedImage->encode($targetFormat, $quality);

            Storage::disk('public')->put($filePath, $encodedImage);

            $savedPictures[$sizeName] = asset('storage/' . $filePath);
        }

        return $savedPictures;
    }

    /**
     * Delete old profile pictures for a user
     */
    /**
     * DeleteOldProfilePictures
     */
    private function deleteOldProfilePictures(int $userId): void
    {
        try {
            // Get all files in the profile-pictures directory that belong to this user
            $files = Storage::disk('public')->files('profile-pictures');

            foreach ($files as $file) {
                $filename = basename((string) $file);
                // Match pattern: profile_{userId}_*
                if (preg_match("/^profile_{$userId}_/", $filename)) {
                    Storage::disk('public')->delete($file);
                }
            }
        } catch (Exception $e) {
            // Log error but don't throw - we don't want to stop the upload process
            Log::warning('Failed to delete old profile pictures for user ' . $userId . ': ' . $e->getMessage());
        }
    }

    /**
     * Validate crop data structure
     */
    /**
     * Check if  valid crop data
     */
    private function isValidCropData(array $cropData): bool
    {
        $requiredFields = ['x', 'y', 'width', 'height'];

        foreach ($requiredFields as $field) {
            if (! isset($cropData[$field]) || ! is_numeric($cropData[$field])) {
                return FALSE;
            }
        }

        // Ensure positive dimensions
        return $cropData['width'] > 0 && $cropData['height'] > 0;
    }

    /**
     * Format bytes to human readable format
     */
    /**
     * FormatBytes
     */
    private function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Get available picture sizes for a user
     */
    /**
     * Get  available sizes
     */
    private function getAvailableSizes(int $userId): array
    {
        $availableSizes = [];
        $files = Storage::disk('public')->files('profile-pictures');

        foreach ($files as $file) {
            $filename = basename((string) $file);
            if (preg_match("/^profile_{$userId}_.*_(\w+)\.webp$/", $filename, $matches)) {
                $sizeName = $matches[1];
                if (in_array($sizeName, array_keys(self::PROFILE_DIMENSIONS), TRUE)) {
                    $availableSizes[$sizeName] = asset('storage/' . $file);
                }
            }
        }

        return $availableSizes;
    }
}
