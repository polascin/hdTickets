<?php declare(strict_types=1);

namespace App\Imports;

use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

use function in_array;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure, WithBatchInserts, WithChunkReading
{
    use SkipsFailures;

    private $rowCount = 0;

    private $successCount = 0;

    private $errorCount = 0;

    private $errors = [];

    private $importedUsers = [];

    /**
     * Collection
     */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            $this->rowCount++;

            try {
                // Validate row data
                $validated = $this->validateRow($row->toArray());

                if (!$validated['valid']) {
                    $this->errorCount++;
                    $this->errors[] = [
                        'row'    => $this->rowCount,
                        'errors' => $validated['errors'],
                    ];

                    continue;
                }

                // Create or update user
                $user = $this->createOrUpdateUser($validated['data']);

                if ($user) {
                    $this->successCount++;
                    $this->importedUsers[] = $user;

                    // Log user creation/update
                    activity('user_import')
                        ->performedOn($user)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'import_method' => 'bulk_import',
                            'row_number'    => $this->rowCount,
                            'original_data' => $row->toArray(),
                        ])
                        ->log('User imported from bulk import');
                }
            } catch (Exception $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'row'    => $this->rowCount,
                    'errors' => ['exception' => $e->getMessage()],
                ];
            }
        }
    }

    /**
     * Validation rules for the import
     */
    /**
     * Rules
     */
    public function rules(): array
    {
        return [
            '*.name'           => ['required', 'string', 'max:255'],
            '*.surname'        => ['required', 'string', 'max:255'],
            '*.email'          => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            '*.phone'          => ['nullable', 'string', 'max:20'],
            '*.role'           => ['required', 'string', Rule::in(User::getRoles())],
            '*.password'       => ['nullable', 'string', 'min:8'],
            '*.is_active'      => ['nullable', 'boolean'],
            '*.email_verified' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom error messages
     */
    /**
     * CustomValidationMessages
     */
    public function customValidationMessages(): array
    {
        return [
            '*.name.required'    => 'Name is required for each user.',
            '*.surname.required' => 'Surname is required for each user.',
            '*.email.required'   => 'Email is required for each user.',
            '*.email.unique'     => 'Email already exists in the database.',
            '*.role.required'    => 'Role is required for each user.',
            '*.role.in'          => 'Role must be one of: ' . implode(', ', User::getRoles()),
        ];
    }

    /**
     * Batch size for processing
     */
    /**
     * BatchSize
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Chunk size for reading
     */
    /**
     * ChunkSize
     */
    public function chunkSize(): int
    {
        return 200;
    }

    /**
     * Get import statistics
     */
    /**
     * Get  import stats
     */
    public function getImportStats(): array
    {
        return [
            'total_rows'     => $this->rowCount,
            'success_count'  => $this->successCount,
            'error_count'    => $this->errorCount,
            'errors'         => $this->errors,
            'imported_users' => $this->importedUsers,
        ];
    }

    /**
     * Get row count
     */
    /**
     * Get  row count
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Get success count
     */
    /**
     * Get  success count
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Get error count
     */
    /**
     * Get  error count
     */
    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    /**
     * Get detailed errors
     */
    /**
     * Get  errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Generate import report
     */
    /**
     * GenerateImportReport
     */
    public function generateImportReport(): array
    {
        $report = [
            'summary' => [
                'total_processed'    => $this->rowCount,
                'successful_imports' => $this->successCount,
                'failed_imports'     => $this->errorCount,
                'success_rate'       => $this->rowCount > 0 ? round(($this->successCount / $this->rowCount) * 100, 2) : 0,
            ],
            'role_breakdown'    => [],
            'common_errors'     => [],
            'imported_user_ids' => array_column($this->importedUsers, 'id'),
        ];

        // Role breakdown
        foreach ($this->importedUsers as $user) {
            $role = $user['role'];
            $report['role_breakdown'][$role] = ($report['role_breakdown'][$role] ?? 0) + 1;
        }

        // Common errors analysis
        $errorTypes = [];
        foreach ($this->errors as $error) {
            foreach ($error['errors'] as $field => $messages) {
                $errorTypes[$field] = ($errorTypes[$field] ?? 0) + 1;
            }
        }
        $report['common_errors'] = $errorTypes;

        return $report;
    }

    /**
     * Validate individual row data
     */
    /**
     * ValidateRow
     */
    private function validateRow(array $row): array
    {
        $validator = Validator::make($row, [
            'name'           => ['required', 'string', 'max:255'],
            'surname'        => ['required', 'string', 'max:255'],
            'email'          => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'role'           => ['required', 'string', Rule::in(User::getRoles())],
            'password'       => ['nullable', 'string', 'min:8'],
            'is_active'      => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
            'timezone'       => ['nullable', 'string', 'max:50'],
            'language'       => ['nullable', 'string', 'max:10'],
            'bio'            => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return [
                'valid'  => FALSE,
                'errors' => $validator->errors()->toArray(),
            ];
        }

        // Check for duplicate email in database
        $existingUser = User::where('email', $row['email'])->first();
        if ($existingUser) {
            return [
                'valid'  => FALSE,
                'errors' => ['email' => ['Email already exists in database']],
            ];
        }

        // Additional custom validations
        $customValidations = $this->performCustomValidations($row);
        if (!$customValidations['valid']) {
            return $customValidations;
        }

        return [
            'valid' => TRUE,
            'data'  => $validator->validated(),
        ];
    }

    /**
     * Perform custom business logic validations
     */
    /**
     * PerformCustomValidations
     */
    private function performCustomValidations(array $row): array
    {
        $errors = [];

        // Validate role-specific constraints
        if (isset($row['role'])) {
            switch ($row['role']) {
                case 'admin':
                    // Admins must have verified email
                    if (!isset($row['email_verified']) || !$row['email_verified']) {
                        $errors['email_verified'] = ['Admin users must have verified email'];
                    }

                    break;
                case 'scraper':
                    // Scrapers should not have personal details
                    if (!empty($row['phone']) || !empty($row['bio'])) {
                        $errors['role'] = ['Scraper users should not have personal details like phone or bio'];
                    }

                    break;
            }
        }

        // Validate phone format if provided
        if (!empty($row['phone'])) {
            if (!preg_match('/^[\+]?[1-9][\d]{0,15}$/', $row['phone'])) {
                $errors['phone'] = ['Phone number format is invalid'];
            }
        }

        // Validate timezone if provided
        if (!empty($row['timezone'])) {
            if (!in_array($row['timezone'], timezone_identifiers_list(), TRUE)) {
                $errors['timezone'] = ['Invalid timezone identifier'];
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Create or update user from validated data
     */
    /**
     * CreateOrUpdateUser
     */
    private function createOrUpdateUser(array $data): ?User
    {
        // Generate username from name and surname
        $username = $this->generateUniqueUsername($data['name'], $data['surname']);

        // Prepare user data
        $userData = [
            'name'                => $data['name'],
            'surname'             => $data['surname'],
            'username'            => $username,
            'email'               => $data['email'],
            'phone'               => $data['phone'] ?? NULL,
            'role'                => $data['role'],
            'password'            => !empty($data['password']) ? Hash::make($data['password']) : Hash::make('password123'),
            'is_active'           => $data['is_active'] ?? TRUE,
            'email_verified_at'   => isset($data['email_verified']) && $data['email_verified'] ? now() : NULL,
            'timezone'            => $data['timezone'] ?? 'UTC',
            'language'            => $data['language'] ?? 'en',
            'bio'                 => $data['bio'] ?? NULL,
            'registration_source' => 'import',
            'created_by_type'     => 'admin',
            'created_by_id'       => auth()->id(),
        ];

        return User::create($userData);
    }

    /**
     * Generate unique username from name and surname
     */
    /**
     * GenerateUniqueUsername
     */
    private function generateUniqueUsername(string $name, string $surname): string
    {
        $baseUsername = strtolower($name . '.' . $surname);
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . '.' . $counter;
            $counter++;
        }

        return $username;
    }
}
