<?php declare(strict_types=1);

namespace App\Exceptions;

use App\Support\UserAgentHelper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Override;
use Throwable;

use function get_class;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    /**
     * Register
     */
    #[Override]
    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
            // Enhanced iOS error reporting
            $request = request();
            if ($request && UserAgentHelper::isIOS($request)) {
                $this->reportIOSError($e, $request);
            }
        });
    }

    /**
     * Report iOS-specific error with enhanced context
     */
    protected function reportIOSError(Throwable $e, Request $request): void
    {
        try {
            $deviceInfo = UserAgentHelper::getDeviceInfo($request);

            Log::error('Exception on iOS device', [
                'exception'      => get_class($e),
                'message'        => $e->getMessage(),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
                'ios_version'    => $deviceInfo['ios_version'],
                'safari_version' => $deviceInfo['safari_version'],
                'device_type'    => $deviceInfo['device_type'],
                'user_agent'     => UserAgentHelper::sanitise($deviceInfo['user_agent'] ?? NULL),
                'url'            => $request->fullUrl(),
                'method'         => $request->method(),
                'ip'             => $request->ip(),
            ]);
        } catch (Throwable $loggingError) {
            // Don't let logging failures prevent error reporting
            Log::debug('Failed to log iOS error context', [
                'error' => $loggingError->getMessage(),
            ]);
        }
    }
}
