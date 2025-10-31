<?php declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryManagementController extends Controller
{
    /**
     * Display a listing of categories
     */
    /**
     * Index
     */
    public function index(Request $request): View
    {
        $query = Category::with('parent')
            ->withCount(['scrapedTickets', 'ticketSources', 'children']);

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by parent
        if ($request->filled('parent')) {
            if ($request->parent === 'root') {
                $query->root();
            } else {
                $query->byParent($request->parent);
            }
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', FALSE);
            }
        }

        $categories = $query->ordered()->paginate(15);

        // Get parent categories for filter dropdown
        $parentCategories = Category::root()->active()->ordered()->get();

        return view('admin.categories.index', ['categories' => $categories, 'parentCategories' => $parentCategories]);
    }

    /**
     * Show the form for creating a new category
     */
    /**
     * Create
     */
    public function create(): View
    {
        $parentCategories = Category::root()->active()->ordered()->get();

        return view('admin.categories.create', ['parentCategories' => $parentCategories]);
    }

    /**
     * Store a newly created category
     */
    /**
     * Store
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:categories'],
            'parent_id'   => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color'       => ['nullable', 'string', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'icon'        => ['nullable', 'string', 'max:50'],
            'is_active'   => ['boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $data = $request->all();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure unique slug
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Category::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Set default sort order
        if (!isset($data['sort_order'])) {
            $maxOrder = Category::where('parent_id', $data['parent_id'])->max('sort_order') ?? 0;
            $data['sort_order'] = $maxOrder + 1;
        }

        // Set default active status
        $data['is_active'] = $request->has('is_active');

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category
     */
    /**
     * Show
     */
    public function show(Category $category): View
    {
        $category->load(['parent', 'children', 'scrapedTickets' => function ($query): void {
            $query->latest()->limit(10);
        }, 'ticketSources' => function ($query): void {
            $query->latest()->limit(10);
        }]);

        return view('admin.categories.show', ['category' => $category]);
    }

    /**
     * Show the form for editing the specified category
     */
    /**
     * Edit
     */
    public function edit(Category $category): View
    {
        $parentCategories = Category::root()
            ->active()
            ->where('id', '!=', $category->id)
            ->ordered()
            ->get();

        return view('admin.categories.edit', ['category' => $category, 'parentCategories' => $parentCategories]);
    }

    /**
     * Update the specified category
     */
    /**
     * Update
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'parent_id'   => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color'       => ['nullable', 'string', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'icon'        => ['nullable', 'string', 'max:50'],
            'is_active'   => ['boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        // Prevent category from being its own parent or creating circular references
        if ($request->parent_id === $category->id) {
            return redirect()->back()
                ->withErrors(['parent_id' => 'Category cannot be its own parent.']);
        }

        // Check for circular references
        if ($request->parent_id && $this->wouldCreateCircularReference($category, $request->parent_id)) {
            return redirect()->back()
                ->withErrors(['parent_id' => 'This would create a circular reference.']);
        }

        $data = $request->all();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Set active status
        $data['is_active'] = $request->has('is_active');

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category
     */
    /**
     * Destroy
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Check if category has scraped tickets
        if ($category->scrapedTickets()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with existing scraped tickets. Please reassign tickets first.');
        }

        // Check if category has ticket sources
        if ($category->ticketSources()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with existing ticket sources. Please reassign sources first.');
        }

        // Check if category has child categories
        if ($category->children()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with child categories. Please delete or reassign child categories first.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Toggle category active status
     */
    /**
     * ToggleStatus
     */
    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.categories.index')
            ->with('success', "Category {$status} successfully.");
    }

    /**
     * Reorder categories
     */
    /**
     * Reorder
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'categories'              => ['required', 'array'],
            'categories.*.id'         => ['required', 'exists:categories,id'],
            'categories.*.sort_order' => ['required', 'integer', 'min:0'],
            'parent_id'               => ['nullable', 'exists:categories,id'],
        ]);

        foreach ($request->categories as $categoryData) {
            Category::where('id', $categoryData['id'])
                ->where('parent_id', $request->parent_id)
                ->update(['sort_order' => $categoryData['sort_order']]);
        }

        return response()->json(['success' => TRUE]);
    }

    /**
     * Get categories as JSON (for AJAX)
     */
    /**
     * ApiIndex
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = Category::active();

        if ($request->filled('parent_id')) {
            $query->byParent($request->parent_id);
        } else {
            $query->root();
        }

        $categories = $query->ordered()->get();

        return response()->json($categories);
    }

    /**
     * Check if assigning a parent would create a circular reference
     *
     * @param mixed $parentId
     */
    /**
     * WouldCreateCircularReference
     *
     * @param mixed $parentId
     */
    private function wouldCreateCircularReference(Category $category, $parentId): bool
    {
        $parent = Category::find($parentId);

        while ($parent) {
            if ($parent->id === $category->id) {
                return TRUE;
            }
            $parent = $parent->parent;
        }

        return FALSE;
    }
}
