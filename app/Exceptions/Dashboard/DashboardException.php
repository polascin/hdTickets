<?php declare(strict_types=1);

namespace App\Exceptions\Dashboard;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardException extends Exception
{
    protected array $context;
    protected string $userMessage;
    protected int $statusCode;

    public function __construct(
        string $message = 'Dashboard error occurred',
        string $userMessage = 'Unable to load dashboard data',
        int $statusCode = 500,
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        
        $this->userMessage = $userMessage;
        $this->statusCode = $statusCode;
        $this->context = $context;
    }

    /**
     * Create exception for data retrieval errors
     */
    public static function dataRetrievalFailed(string $dataType, ?\Throwable $previous = null, array $context = []): self
    {
        return new self(
            message: "Failed to retrieve {$dataType} data",
            userMessage: "Unable to load {$dataType}. Please try again later.",
            statusCode: 503,
            context: array_merge(['data_type' => $dataType], $context),
            previous: $previous
        );
    }

    /**
     * Create exception for cache errors
     */
    public static function cacheError(string $operation, ?\Throwable $previous = null, array $context = []): self
    {
        return new self(
            message: "Cache {$operation} operation failed",
            userMessage: "Service temporarily unavailable. Please refresh the page.",
            statusCode: 503,
            context: array_merge(['cache_operation' => $operation], $context),
            previous: $previous
        );
    }

    /**
     * Create exception for authentication errors
     */
    public static function authenticationRequired(): self
    {
        return new self(
            message: "Dashboard access requires authentication",
            userMessage: "Please log in to access the dashboard.",
            statusCode: 401,
            context: ['authentication' => 'required']
        );
    }

    /**
     * Create exception for authorization errors
     */
    public static function insufficientPermissions(string $requiredRole, array $context = []): self
    {
        return new self(
            message: "Insufficient permissions for dashboard access",
            userMessage: "You don't have permission to access this dashboard.",
            statusCode: 403,
            context: array_merge(['required_role' => $requiredRole], $context)
        );
    }

    /**
     * Create exception for API errors
     */
    public static function apiError(string $endpoint, ?\Throwable $previous = null, array $context = []): self
    {
        return new self(
            message: "API request to {$endpoint} failed",
            userMessage: "Unable to load real-time data. Please try again.",
            statusCode: 502,
            context: array_merge(['api_endpoint' => $endpoint], $context),
            previous: $previous
        );
    }

    /**
     * Create exception for database errors
     */
    public static function databaseError(string $operation, ?\Throwable $previous = null, array $context = []): self
    {
        return new self(
            message: "Database {$operation} operation failed",
            userMessage: "Data temporarily unavailable. Please try again later.",
            statusCode: 503,
            context: array_merge(['database_operation' => $operation], $context),
            previous: $previous
        );
    }

    /**
     * Get user-friendly message
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * Get HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get additional context
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Report the exception
     */
    public function report(): void
    {
        Log::error($this->getMessage(), [
            'exception' => get_class($this),
            'status_code' => $this->statusCode,
            'user_message' => $this->userMessage,
            'context' => $this->context,
            'trace' => $this->getTraceAsString(),
        ]);
    }

    /**
     * Render the exception as HTTP response
     */
    public function render(Request $request): JsonResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'error' => $this->userMessage,
                'message' => $this->userMessage,
                'code' => $this->statusCode,
                'timestamp' => now()->toISOString(),
            ], $this->statusCode);
        }

        // For web requests, you could redirect with error message
        return response()->json([
            'error' => $this->userMessage,
            'status' => $this->statusCode,
        ], $this->statusCode);
    }

    /**
     * Convert to array for logging
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'user_message' => $this->userMessage,
            'status_code' => $this->statusCode,
            'context' => $this->context,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'previous' => $this->getPrevious() ? [
                'message' => $this->getPrevious()->getMessage(),
                'file' => $this->getPrevious()->getFile(),
                'line' => $this->getPrevious()->getLine(),
            ] : null,
        ];
    }
}