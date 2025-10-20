<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Illuminate\Contracts\View\View;
use App\Models\User;
use App\Models\UserFavoriteTeam;
use App\Models\UserFavoriteVenue;
use App\Models\UserNotificationSettings;
use App\Models\UserPreference;
use App\Models\UserPricePreference;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use function count;
use function in_array;
use function is_array;
use function sprintf;
use function strlen;

class SettingsExportController extends Controller
{
    /**
     * Display the settings import/export page
     */
    /**
     * Index
     */
    public function index(): View
    {
        $user = auth()->user();

        return view('profile.settings-export', [
            'user'                  => $user,
            'last_export'           => $this->getLastExportInfo(),
            'supported_formats'     => $this->getSupportedFormats(),
            'exportable_categories' => $this->getExportableCategories(),
        ]);
    }

    /**
     * Export user preferences as JSON
     */
    /**
     * ExportSettings
     */
    public function exportSettings(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'categories'   => 'sometimes|array',
            'categories.*' => 'string|in:preferences,teams,venues,prices,notifications',
            'format'       => 'sometimes|string|in:json,csv',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $categories = $request->input('categories', ['preferences', 'teams', 'venues', 'prices', 'notifications']);
            $format = $request->input('format', 'json');

            $exportData = $this->buildExportData($user, $categories);

            // Log the export activity
            Log::info('User settings exported', [
                'user_id'    => $user->id,
                'categories' => $categories,
                'format'     => $format,
                'data_size'  => strlen(json_encode($exportData)),
            ]);

            if ($format === 'csv') {
                return $this->exportAsCSV($exportData, $user);
            }

            return $this->exportAsJSON($exportData, $user);
        } catch (Exception $e) {
            Log::error('Settings export failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Export failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview import data before applying
     */
    /**
     * PreviewImport
     */
    public function previewImport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'import_file'    => 'required|file|mimetypes:application/json,text/plain|max:2048',
            'merge_strategy' => 'sometimes|string|in:overwrite,merge,skip_existing',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('import_file');
            $content = file_get_contents($file->getRealPath());
            if ($content === FALSE) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Could not read the uploaded file.',
                ], 422);
            }
            $importData = json_decode($content, TRUE);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Invalid JSON format: ' . json_last_error_msg(),
                ], 422);
            }

            $validation = $this->validateImportData($importData);

            if (! $validation['valid']) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Invalid import data structure',
                    'errors'  => $validation['errors'],
                ], 422);
            }

            $user = Auth::user();
            $mergeStrategy = $request->input('merge_strategy', 'merge');
            $preview = $this->generateImportPreview($user, $importData, $mergeStrategy);

            return response()->json([
                'success'    => TRUE,
                'preview'    => $preview,
                'validation' => $validation,
            ]);
        } catch (Exception $e) {
            Log::error('Import preview failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Preview generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import user settings from uploaded file
     */
    /**
     * ImportSettings
     */
    public function importSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'import_file'       => 'required|file|mimetypes:application/json,text/plain|max:2048',
            'merge_strategy'    => 'required|string|in:overwrite,merge,skip_existing',
            'categories'        => 'sometimes|array',
            'categories.*'      => 'string|in:preferences,teams,venues,prices,notifications',
            'preview_confirmed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        if (! $request->input('preview_confirmed')) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Import must be previewed and confirmed before processing',
            ], 422);
        }

        try {
            $file = $request->file('import_file');
            $content = file_get_contents($file->getRealPath());
            if ($content === FALSE) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Could not read the uploaded file.',
                ], 422);
            }
            $importData = json_decode($content, TRUE);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Invalid JSON format: ' . json_last_error_msg(),
                ], 422);
            }

            $validation = $this->validateImportData($importData);

            if (! $validation['valid']) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Invalid import data structure',
                    'errors'  => $validation['errors'],
                ], 422);
            }

            $user = Auth::user();
            $mergeStrategy = $request->input('merge_strategy');
            $categories = $request->input('categories', array_keys($importData['data'] ?? []));

            $result = $this->processImport($user, $importData, $mergeStrategy, $categories);

            // Clear user preferences cache
            Cache::forget("user_preferences_{$user->id}");

            // Log successful import
            Log::info('User settings imported successfully', [
                'user_id'        => $user->id,
                'merge_strategy' => $mergeStrategy,
                'categories'     => $categories,
                'imported_items' => $result['imported_count'],
                'conflicts'      => count($result['conflicts']),
                'errors'         => count($result['errors']),
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Settings imported successfully',
                'result'  => $result,
            ]);
        } catch (Exception $e) {
            Log::error('Settings import failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle conflicts during import
     */
    /**
     * ResolveConflicts
     */
    public function resolveConflicts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conflicts'            => 'required|array',
            'conflicts.*.id'       => 'required|string',
            'conflicts.*.action'   => 'required|string|in:keep_existing,use_import,merge',
            'conflicts.*.category' => 'required|string|in:preferences,teams,venues,prices,notifications',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $conflicts = $request->input('conflicts');

            $resolved = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($conflicts as $conflict) {
                try {
                    $this->resolveConflict($conflict);
                    $resolved++;
                } catch (Exception $e) {
                    $errors[] = "Failed to resolve conflict {$conflict['id']}: " . $e->getMessage();
                }
            }

            DB::commit();

            // Clear cache after resolving conflicts
            Cache::forget("user_preferences_{$user->id}");

            return response()->json([
                'success'        => TRUE,
                'message'        => "Resolved {$resolved} conflicts",
                'resolved_count' => $resolved,
                'errors'         => $errors,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Conflict resolution failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Conflict resolution failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset settings to defaults with backup option
     */
    /**
     * ResetToDefaults
     */
    public function resetToDefaults(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'categories'    => 'sometimes|array',
            'categories.*'  => 'string|in:preferences,teams,venues,prices,notifications',
            'create_backup' => 'boolean',
            'confirm_reset' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        if (! $request->input('confirm_reset')) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Reset must be confirmed',
            ], 422);
        }

        try {
            $user = Auth::user();
            $categories = $request->input('categories', ['preferences', 'teams', 'venues', 'prices', 'notifications']);
            $createBackup = $request->input('create_backup', TRUE);

            $backupFile = NULL;

            if ($createBackup) {
                $exportData = $this->buildExportData($user, $categories);
                $backupFile = $this->createBackupFile($user, $exportData);
            }

            $resetResult = $this->performReset($user, $categories);

            // Clear cache
            Cache::forget("user_preferences_{$user->id}");

            Log::info('User settings reset to defaults', [
                'user_id'        => $user->id,
                'categories'     => $categories,
                'backup_created' => $createBackup,
                'backup_file'    => $backupFile,
                'reset_count'    => $resetResult['reset_count'],
            ]);

            return response()->json([
                'success'      => TRUE,
                'message'      => 'Settings reset to defaults successfully',
                'backup_file'  => $backupFile,
                'reset_result' => $resetResult,
            ]);
        } catch (Exception $e) {
            Log::error('Settings reset failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Reset failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build complete export data structure
     */
    /**
     * BuildExportData
     */
    private function buildExportData(User $user, array $categories): array
    {
        $exportData = [
            'meta' => [
                'version'     => '1.0',
                'exported_at' => now()->toISOString(),
                'exported_by' => $user->id,
                'application' => 'HD Tickets',
                'categories'  => $categories,
            ],
            'data' => [],
        ];

        foreach ($categories as $category) {
            switch ($category) {
                case 'preferences':
                    $exportData['data']['preferences'] = $this->exportPreferences($user);

                    break;
                case 'teams':
                    $exportData['data']['teams'] = $this->exportFavoriteTeams($user);

                    break;
                case 'venues':
                    $exportData['data']['venues'] = $this->exportFavoriteVenues($user);

                    break;
                case 'prices':
                    $exportData['data']['prices'] = $this->exportPricePreferences($user);

                    break;
                case 'notifications':
                    $exportData['data']['notifications'] = $this->exportNotificationSettings($user);

                    break;
            }
        }

        return $exportData;
    }

    /**
     * Export user preferences (excluding sensitive data)
     */
    /**
     * ExportPreferences
     */
    private function exportPreferences(User $user): array
    {
        $preferences = UserPreference::where('user_id', $user->id)
            ->select('category', 'key', 'value', 'data_type')
            ->get()
            ->groupBy('category');

        $exportPreferences = [];

        foreach ($preferences as $category => $categoryPrefs) {
            $exportPreferences[$category] = [];

            foreach ($categoryPrefs as $pref) {
                // Skip sensitive preferences
                if ($this->isSensitivePreference($pref->key)) {
                    continue;
                }

                $exportPreferences[$category][$pref->key] = [
                    'value'     => $this->castPreferenceValue($pref->value, $pref->data_type),
                    'data_type' => $pref->data_type,
                ];
            }
        }

        return $exportPreferences;
    }

    /**
     * Export favorite teams
     */
    /**
     * ExportFavoriteTeams
     */
    private function exportFavoriteTeams(User $user): array
    {
        return UserFavoriteTeam::where('user_id', $user->id)
            ->select('sport_type', 'team_name', 'team_city', 'league', 'priority', 'email_alerts', 'push_alerts', 'sms_alerts')
            ->get()
            ->toArray();
    }

    /**
     * Export favorite venues
     */
    /**
     * ExportFavoriteVenues
     */
    private function exportFavoriteVenues(User $user): array
    {
        return UserFavoriteVenue::where('user_id', $user->id)
            ->select('venue_name', 'city', 'state_province', 'country', 'venue_types', 'priority', 'email_alerts', 'push_alerts', 'sms_alerts')
            ->get()
            ->toArray();
    }

    /**
     * Export price preferences
     */
    /**
     * ExportPricePreferences
     */
    private function exportPricePreferences(User $user): array
    {
        return UserPricePreference::where('user_id', $user->id)
            ->where('is_active', TRUE)
            ->select('preference_name', 'sport_type', 'event_category', 'min_price', 'max_price', 'preferred_quantity', 'seat_preferences', 'price_drop_threshold', 'email_alerts', 'push_alerts', 'sms_alerts', 'alert_frequency')
            ->get()
            ->toArray();
    }

    /**
     * Export notification settings (excluding sensitive channels)
     */
    /**
     * ExportNotificationSettings
     */
    private function exportNotificationSettings(User $user): array
    {
        return UserNotificationSettings::where('user_id', $user->id)
            ->get()
            ->mapWithKeys(function ($setting): array {
                // Skip sensitive notification channels
                if (in_array($setting->channel, ['webhook', 'api'], TRUE)) {
                    return [];
                }

                return [$setting->channel => $setting->is_enabled];
            })
            ->toArray();
    }

    /**
     * Check if preference is sensitive and should be excluded from export
     */
    /**
     * Check if  sensitive preference
     */
    private function isSensitivePreference(string $key): bool
    {
        $sensitiveKeys = [
            'password', 'api_key', 'secret', 'token', 'webhook_url',
            'two_factor', '2fa', 'recovery_codes', 'trusted_devices',
            'payment_methods', 'billing', 'credit_card',
        ];

        foreach ($sensitiveKeys as $sensitiveKey) {
            if (str_contains(strtolower($key), $sensitiveKey)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Cast preference value from storage
     *
     * @param mixed $value
     */
    private function castPreferenceValue($value, string $dataType)
    {
        return match ($dataType) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'array', 'json' => json_decode((string) $value, TRUE),
            default => (string) $value,
        };
    }

    /**
     * Export data as JSON file
     */
    /**
     * ExportAsJSON
     */
    private function exportAsJSON(array $exportData, User $user): Response
    {
        $filename = "hdtickets-settings-{$user->id}-" . now()->format('Y-m-d-H-i-s') . '.json';

        return response()
            ->json($exportData, 200, [], JSON_PRETTY_PRINT)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Content-Type', 'application/json');
    }

    /**
     * Export data as CSV file
     */
    /**
     * ExportAsCSV
     */
    private function exportAsCSV(array $exportData, User $user): Response
    {
        $filename = "hdtickets-settings-{$user->id}-" . now()->format('Y-m-d-H-i-s') . '.csv';
        $csv = $this->convertToCSV($exportData);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Convert export data to CSV format
     */
    /**
     * ConvertToCSV
     */
    private function convertToCSV(array $exportData): string
    {
        $csv = "Category,Subcategory,Key,Value,Type\n";

        foreach ($exportData['data'] as $category => $categoryData) {
            if ($category === 'preferences') {
                foreach ($categoryData as $subCategory => $preferences) {
                    foreach ($preferences as $key => $data) {
                        $value = is_array($data['value']) ? json_encode($data['value']) : $data['value'];
                        $csv .= sprintf(
                            "%s,%s,%s,\"%s\",%s\n",
                            $category,
                            $subCategory,
                            $key,
                            str_replace('"', '""', $value),
                            $data['data_type'],
                        );
                    }
                }
            } else {
                foreach ($categoryData as $index => $item) {
                    foreach ($item as $key => $value) {
                        $valueStr = is_array($value) ? json_encode($value) : $value;
                        $csv .= sprintf(
                            "%s,%s,%s,\"%s\",string\n",
                            $category,
                            $index,
                            $key,
                            str_replace('"', '""', $valueStr),
                        );
                    }
                }
            }
        }

        return $csv;
    }

    /**
     * Validate import data structure
     */
    /**
     * ValidateImportData
     */
    private function validateImportData(array $data): array
    {
        $errors = [];
        $valid = TRUE;

        // Check required meta fields
        if (! isset($data['meta'])) {
            $errors[] = 'Missing meta information';
            $valid = FALSE;
        } elseif (! isset($data['meta']['version'])) {
            $errors[] = 'Missing version information';
            $valid = FALSE;
        }

        // Check data structure
        if (! isset($data['data']) || ! is_array($data['data'])) {
            $errors[] = 'Missing or invalid data section';
            $valid = FALSE;
        }

        // Validate each category
        $validCategories = ['preferences', 'teams', 'venues', 'prices', 'notifications'];

        foreach ($data['data'] as $category => $categoryData) {
            if (! in_array($category, $validCategories, TRUE)) {
                $errors[] = "Invalid category: {$category}";
                $valid = FALSE;

                continue;
            }

            $categoryValidation = $this->validateCategoryData($category, $categoryData);
            if (! $categoryValidation['valid']) {
                $errors = array_merge($errors, $categoryValidation['errors']);
                $valid = FALSE;
            }
        }

        return [
            'valid'  => $valid,
            'errors' => $errors,
        ];
    }

    /**
     * Validate specific category data
     *
     * @param mixed $data
     */
    /**
     * ValidateCategoryData
     *
     * @param mixed $data
     */
    private function validateCategoryData(string $category, $data): array
    {
        $errors = [];
        $valid = TRUE;

        switch ($category) {
            case 'preferences':
                if (! is_array($data)) {
                    $errors[] = 'Preferences must be an array';
                    $valid = FALSE;
                }

                break;
            case 'teams':
                if (! is_array($data)) {
                    $errors[] = 'Teams must be an array';
                    $valid = FALSE;
                } else {
                    foreach ($data as $index => $team) {
                        if (! isset($team['team_name']) || ! isset($team['sport_type'])) {
                            $errors[] = "Team at index {$index} missing required fields";
                            $valid = FALSE;
                        }
                    }
                }

                break;
            case 'venues':
                if (! is_array($data)) {
                    $errors[] = 'Venues must be an array';
                    $valid = FALSE;
                } else {
                    foreach ($data as $index => $venue) {
                        if (! isset($venue['venue_name']) || ! isset($venue['city'])) {
                            $errors[] = "Venue at index {$index} missing required fields";
                            $valid = FALSE;
                        }
                    }
                }

                break;
            case 'prices':
                if (! is_array($data)) {
                    $errors[] = 'Price preferences must be an array';
                    $valid = FALSE;
                } else {
                    foreach ($data as $index => $price) {
                        if (! isset($price['preference_name']) || ! isset($price['max_price'])) {
                            $errors[] = "Price preference at index {$index} missing required fields";
                            $valid = FALSE;
                        }
                    }
                }

                break;
            case 'notifications':
                if (! is_array($data)) {
                    $errors[] = 'Notification settings must be an array';
                    $valid = FALSE;
                }

                break;
        }

        return [
            'valid'  => $valid,
            'errors' => $errors,
        ];
    }

    /**
     * Generate import preview showing what will be changed
     */
    /**
     * GenerateImportPreview
     */
    private function generateImportPreview(User $user, array $importData, string $mergeStrategy): array
    {
        $preview = [
            'changes'       => [],
            'conflicts'     => [],
            'new_items'     => [],
            'total_changes' => 0,
        ];

        foreach ($importData['data'] as $category => $categoryData) {
            $categoryPreview = $this->generateCategoryPreview($user, $category, $categoryData, $mergeStrategy);

            $preview['changes'][$category] = $categoryPreview['changes'];
            $preview['conflicts'] = array_merge($preview['conflicts'], $categoryPreview['conflicts']);
            $preview['new_items'][$category] = $categoryPreview['new_items'];
            $preview['total_changes'] += $categoryPreview['change_count'];
        }

        return $preview;
    }

    /**
     * Generate preview for specific category
     *
     * @param mixed $data
     */
    /**
     * GenerateCategoryPreview
     *
     * @param mixed $data
     */
    private function generateCategoryPreview(User $user, string $category, array $data, string $mergeStrategy): array
    {
        $preview = [
            'changes'      => [],
            'conflicts'    => [],
            'new_items'    => [],
            'change_count' => 0,
        ];

        return match ($category) {
            'preferences'   => $this->previewPreferencesChanges($user, $data, $mergeStrategy),
            'teams'         => $this->previewTeamsChanges($user, $data, $mergeStrategy),
            'venues'        => $this->previewVenuesChanges($user, $data, $mergeStrategy),
            'prices'        => $this->previewPricesChanges($user, $data, $mergeStrategy),
            'notifications' => $this->previewNotificationsChanges($user, $data, $mergeStrategy),
            default         => $preview,
        };
    }

    /**
     * Preview preferences changes
     */
    /**
     * PreviewPreferencesChanges
     */
    private function previewPreferencesChanges(User $user, array $data, string $mergeStrategy): array
    {
        $changes = [];
        $conflicts = [];
        $newItems = [];
        $changeCount = 0;

        $existingPrefs = UserPreference::where('user_id', $user->id)
            ->get()
            ->mapWithKeys(fn ($pref): array => ["{$pref->category}.{$pref->key}" => $pref]);

        foreach ($data as $category => $preferences) {
            foreach ($preferences as $key => $prefData) {
                $fullKey = "{$category}.{$key}";

                if (isset($existingPrefs[$fullKey])) {
                    $existing = $existingPrefs[$fullKey];
                    $existingValue = $this->castPreferenceValue($existing->value, $existing->data_type);

                    if ($existingValue !== $prefData['value']) {
                        if ($mergeStrategy === 'skip_existing') {
                            $conflicts[] = [
                                'type'     => 'preference',
                                'id'       => $fullKey,
                                'category' => 'preferences',
                                'existing' => $existingValue,
                                'import'   => $prefData['value'],
                                'action'   => 'skipped',
                            ];
                        } else {
                            $changes[] = [
                                'type' => 'update',
                                'key'  => $fullKey,
                                'from' => $existingValue,
                                'to'   => $prefData['value'],
                            ];
                            $changeCount++;
                        }
                    }
                } else {
                    $newItems[] = [
                        'type'  => 'preference',
                        'key'   => $fullKey,
                        'value' => $prefData['value'],
                    ];
                    $changeCount++;
                }
            }
        }

        return [
            'changes'      => $changes,
            'conflicts'    => $conflicts,
            'new_items'    => $newItems,
            'change_count' => $changeCount,
        ];
    }

    /**
     * Preview teams changes
     */
    /**
     * PreviewTeamsChanges
     */
    private function previewTeamsChanges(User $user, array $data, string $mergeStrategy): array
    {
        $changes = [];
        $conflicts = [];
        $newItems = [];
        $changeCount = 0;

        $existingTeams = UserFavoriteTeam::where('user_id', $user->id)
            ->get()
            ->mapWithKeys(fn ($team): array => ["{$team->sport_type}.{$team->team_name}" => $team]);

        foreach ($data as $teamData) {
            $teamKey = "{$teamData['sport_type']}.{$teamData['team_name']}";

            if (isset($existingTeams[$teamKey])) {
                if ($mergeStrategy === 'skip_existing') {
                    $conflicts[] = [
                        'type'     => 'team',
                        'id'       => $teamKey,
                        'category' => 'teams',
                        'existing' => $existingTeams[$teamKey]->toArray(),
                        'import'   => $teamData,
                        'action'   => 'skipped',
                    ];
                } else {
                    $changes[] = [
                        'type'    => 'update',
                        'team'    => $teamKey,
                        'changes' => $this->compareArrays($existingTeams[$teamKey]->toArray(), $teamData),
                    ];
                    $changeCount++;
                }
            } else {
                $newItems[] = [
                    'type' => 'team',
                    'data' => $teamData,
                ];
                $changeCount++;
            }
        }

        return [
            'changes'      => $changes,
            'conflicts'    => $conflicts,
            'new_items'    => $newItems,
            'change_count' => $changeCount,
        ];
    }

    /**
     * Preview venues changes
     */
    /**
     * PreviewVenuesChanges
     */
    private function previewVenuesChanges(User $user, array $data, string $mergeStrategy): array
    {
        $changes = [];
        $conflicts = [];
        $newItems = [];
        $changeCount = 0;

        $existingVenues = UserFavoriteVenue::where('user_id', $user->id)
            ->get()
            ->mapWithKeys(fn ($venue): array => ["{$venue->venue_name}.{$venue->city}" => $venue]);

        foreach ($data as $venueData) {
            $venueKey = "{$venueData['venue_name']}.{$venueData['city']}";

            if (isset($existingVenues[$venueKey])) {
                if ($mergeStrategy === 'skip_existing') {
                    $conflicts[] = [
                        'type'     => 'venue',
                        'id'       => $venueKey,
                        'category' => 'venues',
                        'existing' => $existingVenues[$venueKey]->toArray(),
                        'import'   => $venueData,
                        'action'   => 'skipped',
                    ];
                } else {
                    $changes[] = [
                        'type'    => 'update',
                        'venue'   => $venueKey,
                        'changes' => $this->compareArrays($existingVenues[$venueKey]->toArray(), $venueData),
                    ];
                    $changeCount++;
                }
            } else {
                $newItems[] = [
                    'type' => 'venue',
                    'data' => $venueData,
                ];
                $changeCount++;
            }
        }

        return [
            'changes'      => $changes,
            'conflicts'    => $conflicts,
            'new_items'    => $newItems,
            'change_count' => $changeCount,
        ];
    }

    /**
     * Preview price preferences changes
     */
    /**
     * PreviewPricesChanges
     */
    private function previewPricesChanges(User $user, array $data, string $mergeStrategy): array
    {
        $changes = [];
        $conflicts = [];
        $newItems = [];
        $changeCount = 0;

        $existingPrices = UserPricePreference::where('user_id', $user->id)
            ->get()
            ->mapWithKeys(fn ($price): array => [$price->preference_name => $price]);

        foreach ($data as $priceData) {
            $priceName = $priceData['preference_name'];

            if (isset($existingPrices[$priceName])) {
                if ($mergeStrategy === 'skip_existing') {
                    $conflicts[] = [
                        'type'     => 'price',
                        'id'       => $priceName,
                        'category' => 'prices',
                        'existing' => $existingPrices[$priceName]->toArray(),
                        'import'   => $priceData,
                        'action'   => 'skipped',
                    ];
                } else {
                    $changes[] = [
                        'type'       => 'update',
                        'preference' => $priceName,
                        'changes'    => $this->compareArrays($existingPrices[$priceName]->toArray(), $priceData),
                    ];
                    $changeCount++;
                }
            } else {
                $newItems[] = [
                    'type' => 'price',
                    'data' => $priceData,
                ];
                $changeCount++;
            }
        }

        return [
            'changes'      => $changes,
            'conflicts'    => $conflicts,
            'new_items'    => $newItems,
            'change_count' => $changeCount,
        ];
    }

    /**
     * Preview notification settings changes
     */
    /**
     * PreviewNotificationsChanges
     */
    private function previewNotificationsChanges(User $user, array $data, string $mergeStrategy): array
    {
        $changes = [];
        $conflicts = [];
        $newItems = [];
        $changeCount = 0;

        $existingSettings = UserNotificationSettings::where('user_id', $user->id)
            ->get()
            ->mapWithKeys(fn ($setting): array => [$setting->channel => $setting]);

        foreach ($data as $channel => $enabled) {
            if (isset($existingSettings[$channel])) {
                if ($existingSettings[$channel]->is_enabled !== $enabled) {
                    if ($mergeStrategy === 'skip_existing') {
                        $conflicts[] = [
                            'type'     => 'notification',
                            'id'       => $channel,
                            'category' => 'notifications',
                            'existing' => $existingSettings[$channel]->is_enabled,
                            'import'   => $enabled,
                            'action'   => 'skipped',
                        ];
                    } else {
                        $changes[] = [
                            'type'    => 'update',
                            'channel' => $channel,
                            'from'    => $existingSettings[$channel]->is_enabled,
                            'to'      => $enabled,
                        ];
                        $changeCount++;
                    }
                }
            } else {
                $newItems[] = [
                    'type'    => 'notification',
                    'channel' => $channel,
                    'enabled' => $enabled,
                ];
                $changeCount++;
            }
        }

        return [
            'changes'      => $changes,
            'conflicts'    => $conflicts,
            'new_items'    => $newItems,
            'change_count' => $changeCount,
        ];
    }

    /**
     * Compare two arrays and return differences
     */
    /**
     * CompareArrays
     */
    private function compareArrays(array $existing, array $import): array
    {
        $changes = [];

        foreach ($import as $key => $value) {
            if (! isset($existing[$key]) || $existing[$key] !== $value) {
                $changes[$key] = [
                    'from' => $existing[$key] ?? NULL,
                    'to'   => $value,
                ];
            }
        }

        return $changes;
    }

    /**
     * Process the actual import
     */
    /**
     * ProcessImport
     */
    private function processImport(User $user, array $importData, string $mergeStrategy, array $categories): array
    {
        $imported = 0;
        $conflicts = [];
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($categories as $category) {
                if (! isset($importData['data'][$category])) {
                    continue;
                }

                $result = $this->importCategory($user, $category, $importData['data'][$category], $mergeStrategy);
                $imported += $result['imported'];
                $conflicts = array_merge($conflicts, $result['conflicts']);
                $errors = array_merge($errors, $result['errors']);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return [
            'imported_count' => $imported,
            'conflicts'      => $conflicts,
            'errors'         => $errors,
        ];
    }

    /**
     * Import specific category
     *
     * @param mixed $data
     */
    /**
     * ImportCategory
     *
     * @param mixed $data
     */
    private function importCategory(User $user, string $category, array $data, string $mergeStrategy): array
    {
        return match ($category) {
            'preferences'   => $this->importPreferences($user, $data, $mergeStrategy),
            'teams'         => $this->importFavoriteTeams($user, $data, $mergeStrategy),
            'venues'        => $this->importFavoriteVenues($user, $data, $mergeStrategy),
            'prices'        => $this->importPricePreferences($user, $data, $mergeStrategy),
            'notifications' => $this->importNotificationSettings($user, $data, $mergeStrategy),
            default         => ['imported' => 0, 'conflicts' => [], 'errors' => ["Unknown category: {$category}"]],
        };
    }

    /**
     * Import preferences
     */
    /**
     * ImportPreferences
     */
    private function importPreferences(User $user, array $data, string $mergeStrategy): array
    {
        $imported = 0;
        $conflicts = [];
        $errors = [];

        foreach ($data as $category => $preferences) {
            foreach ($preferences as $key => $prefData) {
                try {
                    $existing = UserPreference::where('user_id', $user->id)
                        ->where('category', $category)
                        ->where('key', $key)
                        ->first();

                    if ($existing && $mergeStrategy === 'skip_existing') {
                        $conflicts[] = [
                            'type'   => 'preference',
                            'key'    => "{$category}.{$key}",
                            'action' => 'skipped',
                        ];

                        continue;
                    }

                    $value = $prefData['value'] ?? $prefData;
                    $dataType = $prefData['data_type'] ?? 'string';

                    UserPreference::updateOrCreate(
                        [
                            'user_id'  => $user->id,
                            'category' => $category,
                            'key'      => $key,
                        ],
                        [
                            'value'     => is_array($value) ? json_encode($value) : $value,
                            'data_type' => $dataType,
                        ],
                    );

                    $imported++;
                } catch (Exception $e) {
                    $errors[] = "Failed to import preference {$category}.{$key}: " . $e->getMessage();
                }
            }
        }

        return ['imported' => $imported, 'conflicts' => $conflicts, 'errors' => $errors];
    }

    /**
     * Import favorite teams
     */
    /**
     * ImportFavoriteTeams
     */
    private function importFavoriteTeams(User $user, array $data, string $mergeStrategy): array
    {
        $imported = 0;
        $conflicts = [];
        $errors = [];

        foreach ($data as $teamData) {
            try {
                $existing = UserFavoriteTeam::where('user_id', $user->id)
                    ->where('sport_type', $teamData['sport_type'])
                    ->where('team_name', $teamData['team_name'])
                    ->first();

                if ($existing && $mergeStrategy === 'skip_existing') {
                    $conflicts[] = [
                        'type'   => 'team',
                        'key'    => "{$teamData['sport_type']}.{$teamData['team_name']}",
                        'action' => 'skipped',
                    ];

                    continue;
                }

                UserFavoriteTeam::updateOrCreate(
                    [
                        'user_id'    => $user->id,
                        'sport_type' => $teamData['sport_type'],
                        'team_name'  => $teamData['team_name'],
                    ],
                    array_merge($teamData, ['user_id' => $user->id]),
                );

                $imported++;
            } catch (Exception $e) {
                $errors[] = "Failed to import team {$teamData['team_name']}: " . $e->getMessage();
            }
        }

        return ['imported' => $imported, 'conflicts' => $conflicts, 'errors' => $errors];
    }

    /**
     * Import favorite venues
     */
    /**
     * ImportFavoriteVenues
     */
    private function importFavoriteVenues(User $user, array $data, string $mergeStrategy): array
    {
        $imported = 0;
        $conflicts = [];
        $errors = [];

        foreach ($data as $venueData) {
            try {
                $existing = UserFavoriteVenue::where('user_id', $user->id)
                    ->where('venue_name', $venueData['venue_name'])
                    ->where('city', $venueData['city'])
                    ->first();

                if ($existing && $mergeStrategy === 'skip_existing') {
                    $conflicts[] = [
                        'type'   => 'venue',
                        'key'    => "{$venueData['venue_name']}.{$venueData['city']}",
                        'action' => 'skipped',
                    ];

                    continue;
                }

                UserFavoriteVenue::updateOrCreate(
                    [
                        'user_id'    => $user->id,
                        'venue_name' => $venueData['venue_name'],
                        'city'       => $venueData['city'],
                    ],
                    array_merge($venueData, ['user_id' => $user->id]),
                );

                $imported++;
            } catch (Exception $e) {
                $errors[] = "Failed to import venue {$venueData['venue_name']}: " . $e->getMessage();
            }
        }

        return ['imported' => $imported, 'conflicts' => $conflicts, 'errors' => $errors];
    }

    /**
     * Import price preferences
     */
    /**
     * ImportPricePreferences
     */
    private function importPricePreferences(User $user, array $data, string $mergeStrategy): array
    {
        $imported = 0;
        $conflicts = [];
        $errors = [];

        foreach ($data as $priceData) {
            try {
                $existing = UserPricePreference::where('user_id', $user->id)
                    ->where('preference_name', $priceData['preference_name'])
                    ->first();

                if ($existing && $mergeStrategy === 'skip_existing') {
                    $conflicts[] = [
                        'type'   => 'price',
                        'key'    => $priceData['preference_name'],
                        'action' => 'skipped',
                    ];

                    continue;
                }

                UserPricePreference::updateOrCreate(
                    [
                        'user_id'         => $user->id,
                        'preference_name' => $priceData['preference_name'],
                    ],
                    array_merge($priceData, ['user_id' => $user->id, 'is_active' => TRUE]),
                );

                $imported++;
            } catch (Exception $e) {
                $errors[] = "Failed to import price preference {$priceData['preference_name']}: " . $e->getMessage();
            }
        }

        return ['imported' => $imported, 'conflicts' => $conflicts, 'errors' => $errors];
    }

    /**
     * Import notification settings
     */
    /**
     * ImportNotificationSettings
     */
    private function importNotificationSettings(User $user, array $data, string $mergeStrategy): array
    {
        $imported = 0;
        $conflicts = [];
        $errors = [];

        foreach ($data as $channel => $enabled) {
            try {
                $existing = UserNotificationSettings::where('user_id', $user->id)
                    ->where('channel', $channel)
                    ->first();

                if ($existing && $mergeStrategy === 'skip_existing') {
                    $conflicts[] = [
                        'type'   => 'notification',
                        'key'    => $channel,
                        'action' => 'skipped',
                    ];

                    continue;
                }

                UserNotificationSettings::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'channel' => $channel,
                    ],
                    [
                        'is_enabled' => $enabled,
                    ],
                );

                $imported++;
            } catch (Exception $e) {
                $errors[] = "Failed to import notification setting {$channel}: " . $e->getMessage();
            }
        }

        return ['imported' => $imported, 'conflicts' => $conflicts, 'errors' => $errors];
    }

    /**
     * Resolve a specific conflict
     */
    /**
     * ResolveConflict
     */
    private function resolveConflict(array $conflict): void
    {
        $action = $conflict['action'];

        if ($action === 'keep_existing') {
            // No action needed, keep existing
            return;
        }

        // Implementation for 'use_import' and 'merge' actions would go here
        // This would involve updating the specific record based on the conflict type
    }

    /**
     * Perform settings reset
     */
    /**
     * PerformReset
     */
    private function performReset(User $user, array $categories): array
    {
        $resetCount = 0;

        foreach ($categories as $category) {
            switch ($category) {
                case 'preferences':
                    $resetCount += UserPreference::where('user_id', $user->id)->delete();

                    break;
                case 'teams':
                    $resetCount += UserFavoriteTeam::where('user_id', $user->id)->delete();

                    break;
                case 'venues':
                    $resetCount += UserFavoriteVenue::where('user_id', $user->id)->delete();

                    break;
                case 'prices':
                    $resetCount += UserPricePreference::where('user_id', $user->id)->delete();

                    break;
                case 'notifications':
                    $resetCount += UserNotificationSettings::where('user_id', $user->id)->delete();

                    break;
            }
        }

        return ['reset_count' => $resetCount];
    }

    /**
     * Create backup file
     */
    /**
     * CreateBackupFile
     */
    private function createBackupFile(User $user, array $exportData): string
    {
        $filename = "backup-{$user->id}-" . now()->format('Y-m-d-H-i-s') . '.json';
        $path = "user-backups/{$user->id}/{$filename}";

        Storage::disk('local')->put($path, json_encode($exportData, JSON_PRETTY_PRINT));

        return $path;
    }

    /**
     * Get information about last export
     */
    /**
     * Get  last export info
     */
    private function getLastExportInfo(): ?array
    {
        // This would typically come from a user_exports table
        // For now, return null
        return NULL;
    }

    /**
     * Get supported export formats
     */
    /**
     * Get  supported formats
     */
    private function getSupportedFormats(): array
    {
        return [
            'json' => [
                'name'        => 'JSON',
                'description' => 'JavaScript Object Notation - recommended format',
                'extension'   => '.json',
                'mime_type'   => 'application/json',
            ],
            'csv' => [
                'name'        => 'CSV',
                'description' => 'Comma Separated Values - for spreadsheet applications',
                'extension'   => '.csv',
                'mime_type'   => 'text/csv',
            ],
        ];
    }

    /**
     * Get exportable categories
     */
    /**
     * Get  exportable categories
     */
    private function getExportableCategories(): array
    {
        return [
            'preferences' => [
                'name'        => 'General Preferences',
                'description' => 'Theme, display, alert settings (excluding sensitive data)',
                'icon'        => 'settings',
            ],
            'teams' => [
                'name'        => 'Favorite Teams',
                'description' => 'Your favorite sports teams and alert preferences',
                'icon'        => 'users',
            ],
            'venues' => [
                'name'        => 'Favorite Venues',
                'description' => 'Your favorite event venues and locations',
                'icon'        => 'map-pin',
            ],
            'prices' => [
                'name'        => 'Price Preferences',
                'description' => 'Price alerts, thresholds, and purchase preferences',
                'icon'        => 'dollar-sign',
            ],
            'notifications' => [
                'name'        => 'Notification Settings',
                'description' => 'Notification channel preferences (excluding sensitive channels)',
                'icon'        => 'bell',
            ],
        ];
    }
}
