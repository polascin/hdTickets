<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ScrapedTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function in_array;

class CategoryController extends Controller
{
    /**
     * Get all categories with ticket counts
     */
    /**
     * Index
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount(['scrapedTickets']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('description', 'LIKE', '%' . $search . '%');
            });
        }

        if ($request->has('sport_type')) {
            $query->where('sport_type', $request->sport_type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Apply sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $allowedSorts = ['name', 'sport_type', 'created_at', 'scraped_tickets_count'];

        if (in_array($sortField, $allowedSorts, TRUE)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Pagination or all records
        if ($request->has('per_page')) {
            $perPage = min($request->get('per_page', 20), 100);
            $categories = $query->paginate($perPage);

            return response()->json([
                'success' => TRUE,
                'data'    => $categories->items(),
                'meta'    => [
                    'current_page' => $categories->currentPage(),
                    'from'         => $categories->firstItem(),
                    'last_page'    => $categories->lastPage(),
                    'per_page'     => $categories->perPage(),
                    'to'           => $categories->lastItem(),
                    'total'        => $categories->total(),
                ],
            ]);
        }
        $categories = $query->get();

        return response()->json([
            'success' => TRUE,
            'data'    => $categories,
        ]);
    }

    /**
     * Get category by ID with detailed statistics
     */
    /**
     * Show
     */
    public function show(int $id): JsonResponse
    {
        $category = Category::withCount(['scrapedTickets'])->find($id);

        if (! $category) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Category not found',
            ], 404);
        }

        // Get additional statistics
        $stats = [
            'total_tickets'     => $category->scraped_tickets_count,
            'available_tickets' => ScrapedTicket::where('category_id', $id)
                ->where('is_available', TRUE)->count(),
            'high_demand_tickets' => ScrapedTicket::where('category_id', $id)
                ->where('is_high_demand', TRUE)->count(),
            'average_price' => ScrapedTicket::where('category_id', $id)
                ->where('is_available', TRUE)
                ->avg('min_price') ?? 0,
            'platform_breakdown' => ScrapedTicket::where('category_id', $id)
                ->selectRaw('platform, COUNT(*) as count, AVG(min_price) as avg_price')
                ->groupBy('platform')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->platform => [
                        'count'     => $item->count,
                        'avg_price' => round($item->avg_price ?? 0, 2),
                    ]];
                }),
            'recent_tickets' => ScrapedTicket::where('category_id', $id)
                ->where('is_available', TRUE)
                ->orderBy('scraped_at', 'desc')
                ->limit(10)
                ->get(['uuid', 'title', 'platform', 'min_price', 'max_price', 'event_date']),
        ];

        return response()->json([
            'success' => TRUE,
            'data'    => [
                'category'   => $category,
                'statistics' => $stats,
            ],
        ]);
    }

    /**
     * Get tickets for a specific category
     */
    /**
     * Tickets
     */
    public function tickets(Request $request, int $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Category not found',
            ], 404);
        }

        $query = ScrapedTicket::where('category_id', $id);

        // Apply filters
        if ($request->has('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->has('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        if ($request->has('is_high_demand')) {
            $query->where('is_high_demand', $request->boolean('is_high_demand'));
        }

        if ($request->has('min_price')) {
            $query->where('min_price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('max_price', '<=', $request->max_price);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('venue', 'LIKE', '%' . $search . '%')
                    ->orWhere('team', 'LIKE', '%' . $search . '%');
            });
        }

        // Apply sorting
        $sortField = $request->get('sort', 'scraped_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['event_date', 'min_price', 'max_price', 'scraped_at', 'title'];

        if (in_array($sortField, $allowedSorts, TRUE)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('scraped_at', 'desc');
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $tickets = $query->paginate($perPage);

        return response()->json([
            'success' => TRUE,
            'data'    => $tickets->items(),
            'meta'    => [
                'current_page' => $tickets->currentPage(),
                'from'         => $tickets->firstItem(),
                'last_page'    => $tickets->lastPage(),
                'per_page'     => $tickets->perPage(),
                'to'           => $tickets->lastItem(),
                'total'        => $tickets->total(),
            ],
            'category' => [
                'id'         => $category->id,
                'name'       => $category->name,
                'sport_type' => $category->sport_type,
            ],
        ]);
    }

    /**
     * Get category statistics summary
     */
    /**
     * Statistics
     */
    public function statistics(): JsonResponse
    {
        $totalCategories = Category::count();
        $activeCategories = Category::where('is_active', TRUE)->count();

        $categoryStats = Category::withCount(['scrapedTickets'])
            ->orderBy('scraped_tickets_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($category) {
                return [
                    'id'                => $category->id,
                    'name'              => $category->name,
                    'sport_type'        => $category->sport_type,
                    'total_tickets'     => $category->scraped_tickets_count,
                    'available_tickets' => ScrapedTicket::where('category_id', $category->id)
                        ->where('is_available', TRUE)->count(),
                    'high_demand_tickets' => ScrapedTicket::where('category_id', $category->id)
                        ->where('is_high_demand', TRUE)->count(),
                ];
            });

        $sportTypeBreakdown = Category::selectRaw('sport_type, COUNT(*) as count')
            ->groupBy('sport_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->sport_type => $item->count];
            });

        return response()->json([
            'success' => TRUE,
            'data'    => [
                'overview' => [
                    'total_categories'    => $totalCategories,
                    'active_categories'   => $activeCategories,
                    'inactive_categories' => $totalCategories - $activeCategories,
                ],
                'top_categories'       => $categoryStats,
                'sport_type_breakdown' => $sportTypeBreakdown,
                'last_updated'         => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Create a new category (admin only)
     */
    /**
     * Store
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'sometimes|string|max:1000',
            'sport_type'  => 'required|string|max:100',
            'is_active'   => 'sometimes|boolean',
            'metadata'    => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['is_active'] ??= TRUE;

        $category = Category::create($data);

        return response()->json([
            'success' => TRUE,
            'message' => 'Category created successfully',
            'data'    => $category,
        ], 201);
    }

    /**
     * Update an existing category (admin only)
     */
    /**
     * Update
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Category not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|string|max:255|unique:categories,name,' . $id,
            'description' => 'sometimes|string|max:1000',
            'sport_type'  => 'sometimes|string|max:100',
            'is_active'   => 'sometimes|boolean',
            'metadata'    => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $category->update($validator->validated());

        return response()->json([
            'success' => TRUE,
            'message' => 'Category updated successfully',
            'data'    => $category,
        ]);
    }

    /**
     * Delete a category (admin only)
     */
    /**
     * Destroy
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Category not found',
            ], 404);
        }

        // Check if category has associated tickets
        $ticketCount = ScrapedTicket::where('category_id', $id)->count();

        if ($ticketCount > 0) {
            return response()->json([
                'success'      => FALSE,
                'message'      => 'Cannot delete category with associated tickets',
                'ticket_count' => $ticketCount,
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => TRUE,
            'message' => 'Category deleted successfully',
        ]);
    }

    /**
     * Get available sport types
     */
    /**
     * SportTypes
     */
    public function sportTypes(): JsonResponse
    {
        $sportTypes = Category::distinct()
            ->pluck('sport_type')
            ->filter()
            ->sort()
            ->values();

        return response()->json([
            'success' => TRUE,
            'data'    => $sportTypes,
        ]);
    }
}
