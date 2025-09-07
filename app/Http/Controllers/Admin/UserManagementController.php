<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecurityService;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use InvalidArgumentException;
use Log;
use Spatie\Activitylog\Models\Activity;

use function count;
use function in_array;

class UserManagementController extends Controller
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Display a listing of users
     */
    /**
     * Index
     */
    public function index(): Illuminate\Contracts\View\View
    {
        $query = User::query();

        // Multi-criteria search
        if (request('search')) {
            $searchTerm = request('search');
            $query->where(function ($q) use ($searchTerm): void {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('surname', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('username', 'like', '%' . $searchTerm . '%')
                    ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                    ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ['%' . $searchTerm . '%']);
            });
        }

        // Role filter
        if (request('has_roles')) {
            $query->whereHas('roles', function ($q): void {
                $q->whereIn('name', request('has_roles'));
            });
        }
        if (request('role') && request('role') !== 'all') {
            $query->where('role', request('role'));
        }

        // Status filter
        if (request('status') && request('status') !== 'all') {
            if (request('status') === 'active') {
                $query->where('is_active', TRUE);
            } elseif (request('status') === 'inactive') {
                $query->where('is_active', FALSE);
            } elseif (request('status') === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif (request('status') === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        // Date range filter
        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        // Sorting
        $sortBy = request('sort_by', 'created_at');
        $sortOrder = request('sort_order', 'desc');

        // Validate sort parameters
        $allowedSortFields = ['name', 'surname', 'email', 'role', 'is_active', 'created_at', 'email_verified_at'];
        $allowedSortOrders = ['asc', 'desc'];

        if (!in_array($sortBy, $allowedSortFields, TRUE)) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortOrder, $allowedSortOrders, TRUE)) {
            $sortOrder = 'desc';
        }

        // Handle full name sorting
        if ($sortBy === 'name') {
            $query->orderByRaw("CONCAT(name, ' ', surname) {$sortOrder}");
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination with query parameters
        $perPage = request('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], TRUE)) {
            $perPage = 10;
        }

        $users = $query->paginate($perPage)->appends(request()->query());

        // Get available roles for filter dropdown
        $availableRoles = User::getRoles();

        // Add new role management view
        if (request('manage_roles')) {
            // Return the roles management view
            return view('admin.users.roles');
        }

        // Debug logging
        Log::info('UserManagementController@index - Users count: ' . $users->count());
        Log::info('UserManagementController@index - Available roles: ' . json_encode($availableRoles));

        return view('admin.users.index', compact('users', 'availableRoles'));
    }

    /**
     * Show the form for creating a new user
     */
    /**
     * Create
     */
    public function create(): Illuminate\Contracts\View\View
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     */
    /**
     * Store
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'surname'  => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'string', 'in:admin,agent,customer'],
        ]);

        // Generate username automatically from name and surname
        $username = strtolower($request->name . '.' . $request->surname);
        $counter = 1;
        $originalUsername = $username;

        // Ensure username uniqueness
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . '.' . $counter;
            $counter++;
        }

        // Generate password if not provided
        $password = $request->password ?: 'password123';

        User::create([
            'name'              => $request->name,
            'surname'           => $request->surname,
            'username'          => $username,
            'email'             => $request->email,
            'password'          => Hash::make($password),
            'role'              => $request->role,
            'email_verified_at' => now(), // Auto-verify for admin-created users
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    /**
     * Show
     */
    public function show(User $user): \Illuminate\Contracts\View\View
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    /**
     * Edit
     */
    public function edit(User $user): \Illuminate\Contracts\View\View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    /**
     * Update
     */
    public function update(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'surname'   => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role'      => ['required', 'string', 'in:admin,agent,customer'],
            'is_active' => ['boolean'],
        ]);

        // Auto-update username if name or surname changed
        $username = $user->username; // Keep existing username by default
        if ($request->name !== $user->name || $request->surname !== $user->surname) {
            $newUsername = strtolower($request->name . '.' . $request->surname);
            $counter = 1;
            $originalUsername = $newUsername;

            // Ensure username uniqueness (excluding current user)
            while (User::where('username', $newUsername)->where('id', '!=', $user->id)->exists()) {
                $newUsername = $originalUsername . '.' . $counter;
                $counter++;
            }
            $username = $newUsername;
        }

        $user->update([
            'name'      => $request->name,
            'surname'   => $request->surname,
            'username'  => $username,
            'email'     => $request->email,
            'role'      => $request->role,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    /**
     * Destroy
     */
    public function destroy(User $user): \Illuminate\Http\RedirectResponse
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active status
     */
    /**
     * ToggleStatus
     */
    public function toggleStatus(User $user): \Illuminate\Http\JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.users.index')
            ->with('success', "User {$status} successfully.");
    }

    /**
     * Reset the user's password to a default value
     */
    /**
     * ResetPassword
     */
    public function resetPassword(User $user): \Illuminate\Http\JsonResponse
    {
        $defaultPassword = 'password';
        $user->update(['password' => Hash::make($defaultPassword)]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Password reset successfully to the default password.');
    }

    /**
     * Handle bulk actions on users with enhanced security
     */
    /**
     * BulkAction
     */
    public function bulkAction(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $startTime = microtime(TRUE);

        // Enhanced validation with CSRF token
        $request->validate([
            'action'           => 'required|string|in:activate,deactivate,delete,assign_role,export',
            'selected_users'   => 'required|array|min:1',
            'selected_users.*' => 'integer|exists:users,id',
            'role'             => 'nullable|string|in:admin,agent,customer,scraper',
        ]);

        $action = $request->input('action');
        $userIds = $request->input('selected_users');
        $bulkToken = $request->input('bulk_token');

        // Security checks
        if (!$this->securityService->checkPermission($user, 'bulk_operations', ['action' => $action])) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to perform bulk operations.');
        }

        // Validate bulk operation security
        $validation = $this->securityService->validateBulkOperation($userIds, $action, $user);
        if (!$validation['valid']) {
            $this->securityService->logSecurityActivity(
                'Bulk operation validation failed',
                ['action' => $action, 'errors' => $validation['errors']],
            );

            return redirect()->route('admin.users.index')
                ->with('error', implode(' ', $validation['errors']));
        }

        // Validate CSRF token for bulk operations
        if (!$this->securityService->validateBulkOperationToken($bulkToken, $action, $userIds)) {
            $this->securityService->logSecurityActivity(
                'Invalid bulk operation token',
                ['action' => $action, 'user_count' => count($userIds)],
            );

            return redirect()->route('admin.users.index')
                ->with('error', 'Invalid security token. Please refresh and try again.');
        }

        $users = User::whereIn('id', $userIds)->get();

        if ($users->isEmpty()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No users selected.');
        }

        $results = ['success' => 0, 'failure' => 0, 'errors' => []];

        try {
            switch ($action) {
                case 'activate':
                    $response = $this->bulkActivate($users);
                    $results['success'] = $users->count();

                    break;
                case 'deactivate':
                    $response = $this->bulkDeactivate($users);
                    $results['success'] = $users->count();

                    break;
                case 'delete':
                    $response = $this->bulkDelete($users);
                    $results['success'] = $users->count();

                    break;
                case 'assign_role':
                    $response = $this->bulkAssignRole($users, $request->input('role'));
                    $results['success'] = $users->count();

                    break;
                case 'export':
                    $results['success'] = $users->count();
                    $this->securityService->logBulkOperation($action, $userIds, $results);

                    return $this->bulkExport($users);
                default:
                    throw new InvalidArgumentException('Invalid bulk action');
            }
        } catch (Exception $e) {
            $results['failure'] = $users->count();
            $results['errors'][] = $e->getMessage();

            $this->securityService->logSecurityActivity(
                'Bulk operation failed',
                ['action' => $action, 'error' => $e->getMessage()],
            );

            return redirect()->route('admin.users.index')
                ->with('error', 'Bulk operation failed: ' . $e->getMessage());
        }

        // Log successful bulk operation
        $results['execution_time'] = round((microtime(TRUE) - $startTime) * 1000, 2) . 'ms';
        $this->securityService->logBulkOperation($action, $userIds, $results);

        return $response;
    }

    /**
     * Impersonate a user
     */
    /**
     * Impersonate
     */
    public function impersonate(User $user): \Illuminate\Http\RedirectResponse
    {
        // Prevent impersonating yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot impersonate yourself.');
        }

        // Store original admin user in session for later restoration
        session(['impersonating' => [
            'original_user'     => auth()->id(),
            'impersonated_user' => $user->id,
            'started_at'        => now(),
        ]]);

        // Log in as the target user
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', "You are now impersonating {$user->full_name}. Click 'Stop Impersonating' to return to your account.");
    }

    /**
     * Stop impersonating and return to original user
     */
    public function stopImpersonating()
    {
        if (!session('impersonating')) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not currently impersonating anyone.');
        }

        $originalUserId = session('impersonating.original_user');
        $originalUser = User::find($originalUserId);

        if (!$originalUser) {
            session()->forget('impersonating');

            return redirect()->route('login')
                ->with('error', 'Original user not found. Please log in again.');
        }

        // Clear impersonating session
        session()->forget('impersonating');

        // Log back in as original user
        Auth::login($originalUser);

        return redirect()->route('admin.users.index')
            ->with('success', 'You have stopped impersonating and returned to your account.');
    }

    /**
     * Send email verification to user
     */
    /**
     * SendVerification
     */
    public function sendVerification(User $user): \Illuminate\Http\JsonResponse
    {
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User email is already verified.');
        }

        // Fire the registered event to send verification email
        event(new Registered($user));

        return redirect()->route('admin.users.index')
            ->with('success', 'Verification email sent successfully.');
    }

    /**
     * Update user field inline via AJAX
     */
    /**
     * InlineUpdate
     */
    public function inlineUpdate(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $field = $request->input('field');
        $value = $request->input('value');

        // Define allowed fields for inline editing
        $allowedFields = ['name', 'surname', 'email', 'phone'];

        if (!in_array($field, $allowedFields, TRUE)) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Field not allowed for inline editing.',
            ], 400);
        }

        // Validate the input based on field type
        $rules = [];
        switch ($field) {
            case 'name':
            case 'surname':
                $rules[$field] = ['required', 'string', 'max:255'];

                break;
            case 'email':
                $rules[$field] = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id];

                break;
            case 'phone':
                $rules[$field] = ['nullable', 'string', 'max:20'];

                break;
        }

        $validator = validator([$field => $value], $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => $validator->errors()->first($field),
            ], 400);
        }

        // Update the field
        $user->update([$field => $value]);

        // If name or surname changed, update username as well
        if (in_array($field, ['name', 'surname'], TRUE)) {
            $username = strtolower($user->name . '.' . $user->surname);
            $counter = 1;
            $originalUsername = $username;

            // Ensure username uniqueness (excluding current user)
            while (User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $originalUsername . '.' . $counter;
                $counter++;
            }

            $user->update(['username' => $username]);
        }

        return response()->json([
            'success'   => TRUE,
            'message'   => 'Field updated successfully.',
            'new_value' => $value,
        ]);
    }

    /**
     * Show the user roles management interface
     */
    public function roles()
    {
        // Authorization check
        $this->authorize('manage_users');

        // Get role statistics
        $roleStats = [
            'admin'    => User::where('role', 'admin')->count(),
            'agent'    => User::where('role', 'agent')->count(),
            'customer' => User::where('role', 'customer')->count(),
            'scraper'  => User::where('role', 'scraper')->count(),
        ];

        // Extract individual counts for the view
        $adminCount = $roleStats['admin'];
        $agentCount = $roleStats['agent'];
        $customerCount = $roleStats['customer'];
        $scraperCount = $roleStats['scraper'];

        // Get users by role with pagination
        $usersQuery = User::query();

        if (request('role_filter') && request('role_filter') !== 'all') {
            $usersQuery->where('role', request('role_filter'));
        }

        if (request('search')) {
            $searchTerm = request('search');
            $usersQuery->where(function ($q) use ($searchTerm): void {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('surname', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ['%' . $searchTerm . '%']);
            });
        }

        $users = $usersQuery->orderBy('role')->orderBy('name')->paginate(20);

        // Get role permissions/capabilities for display
        $roleCapabilities = [
            'admin' => [
                'Manage Users',
                'Access Reports',
                'System Management',
                'Scraping Management',
                'Activity Logs',
                'Full System Access',
            ],
            'agent' => [
                'View Tickets',
                'Process Tickets',
                'Basic Reports',
                'Limited System Access',
            ],
            'customer' => [
                'View Own Tickets',
                'Submit Tickets',
                'Basic Profile Management',
                'Limited Access',
            ],
        ];

        // Recent role changes activity
        $recentRoleChanges = Activity::where('event', 'updated')
            ->where('description', 'like', '%role%')
            ->with('causer')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.users.roles', compact(
            'roleStats',
            'users',
            'roleCapabilities',
            'recentRoleChanges',
            'adminCount',
            'agentCount',
            'customerCount',
            'scraperCount',
        ));
    }

    /**
     * Update user role via AJAX
     */
    /**
     * UpdateRole
     */
    public function updateRole(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $this->authorize('manage_users');

        $request->validate([
            'role' => 'required|string|in:admin,agent,customer',
        ]);

        $oldRole = $user->role;
        $newRole = $request->role;

        if ($oldRole === $newRole) {
            return response()->json([
                'success' => FALSE,
                'message' => 'User already has this role.',
            ]);
        }

        // Prevent removing admin role from yourself
        if ($user->id === auth()->id() && $oldRole === 'admin' && $newRole !== 'admin') {
            return response()->json([
                'success' => FALSE,
                'message' => 'You cannot remove admin role from yourself.',
            ], 400);
        }

        $user->update(['role' => $newRole]);

        // Log the role change
        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_role' => $oldRole,
                'new_role' => $newRole,
            ])
            ->log("User role changed from {$oldRole} to {$newRole}");

        return response()->json([
            'success' => TRUE,
            'message' => "User role updated successfully from {$oldRole} to {$newRole}.",
        ]);
    }

    /**
     * Bulk role assignment
     */
    /**
     * BulkRoleAssignment
     */
    public function bulkRoleAssignment(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('manage_users');

        $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'new_role'   => 'required|string|in:admin,agent,customer',
        ]);

        $userIds = $request->user_ids;
        $newRole = $request->new_role;
        $currentUserId = auth()->id();

        $users = User::whereIn('id', $userIds)->get();
        $updatedCount = 0;
        $errors = [];

        foreach ($users as $user) {
            // Skip if user already has the role
            if ($user->role === $newRole) {
                continue;
            }

            // Prevent removing admin role from yourself
            if ($user->id === $currentUserId && $user->role === 'admin' && $newRole !== 'admin') {
                $errors[] = 'Cannot remove admin role from yourself';

                continue;
            }

            $oldRole = $user->role;
            $user->update(['role' => $newRole]);
            $updatedCount++;

            // Log the role change
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_role'       => $oldRole,
                    'new_role'       => $newRole,
                    'bulk_operation' => TRUE,
                ])
                ->log("User role changed from {$oldRole} to {$newRole} (bulk operation)");
        }

        $message = "Successfully updated {$updatedCount} user(s) to {$newRole} role.";
        if (!empty($errors)) {
            $message .= ' Errors: ' . implode(', ', $errors);
        }

        return response()->json([
            'success'       => TRUE,
            'message'       => $message,
            'updated_count' => $updatedCount,
            'errors'        => $errors,
        ]);
    }

    /**
     * Bulk activate users
     *
     * @param mixed $users
     */
    /**
     * BulkActivate
     *
     * @param mixed $users
     */
    private function bulkActivate($users): \Illuminate\Http\JsonResponse
    {
        $count = 0;
        foreach ($users as $user) {
            if (!$user->is_active) {
                $user->update(['is_active' => TRUE]);
                $count++;
            }
        }

        $message = $count > 0 ? "Successfully activated {$count} user(s)." : 'All selected users are already active.';

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    /**
     * Bulk deactivate users
     *
     * @param mixed $users
     */
    /**
     * BulkDeactivate
     *
     * @param mixed $users
     */
    private function bulkDeactivate($users): \Illuminate\Http\JsonResponse
    {
        $count = 0;
        $currentUserId = auth()->id();

        foreach ($users as $user) {
            // Prevent admin from deactivating themselves
            if ($user->id === $currentUserId) {
                continue;
            }

            if ($user->is_active) {
                $user->update(['is_active' => FALSE]);
                $count++;
            }
        }

        $message = $count > 0 ? "Successfully deactivated {$count} user(s)." : 'No users were deactivated.';

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    /**
     * Bulk delete users
     *
     * @param mixed $users
     */
    /**
     * BulkDelete
     *
     * @param mixed $users
     */
    private function bulkDelete($users): \Illuminate\Http\JsonResponse
    {
        $count = 0;
        $currentUserId = auth()->id();

        foreach ($users as $user) {
            // Prevent admin from deleting themselves
            if ($user->id === $currentUserId) {
                continue;
            }

            $user->delete();
            $count++;
        }

        $message = $count > 0 ? "Successfully deleted {$count} user(s)." : 'No users were deleted.';

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    /**
     * Bulk assign role to users
     *
     * @param mixed $users
     * @param mixed $role
     */
    /**
     * BulkAssignRole
     *
     * @param mixed $users
     * @param mixed $role
     */
    private function bulkAssignRole($users, $role): \Illuminate\Http\JsonResponse
    {
        if (!$role) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Please specify a role to assign.');
        }

        $count = 0;
        foreach ($users as $user) {
            if ($user->role !== $role) {
                $user->update(['role' => $role]);
                $count++;
            }
        }

        $message = $count > 0 ? "Successfully assigned {$role} role to {$count} user(s)." : "All selected users already have the {$role} role.";

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    /**
     * Bulk export users
     *
     * @param mixed $users
     */
    /**
     * BulkExport
     *
     * @param mixed $users
     */
    private function bulkExport($users): \Illuminate\Http\Response
    {
        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($users): void {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Name',
                'Surname',
                'Full Name',
                'Email',
                'Username',
                'Phone',
                'Role',
                'Status',
                'Email Verified',
                'Created At',
                'Updated At',
            ]);

            // Add user data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->surname,
                    $user->full_name,
                    $user->email,
                    $user->username,
                    $user->phone,
                    ucfirst($user->role),
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->email_verified_at ? 'Yes' : 'No',
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
