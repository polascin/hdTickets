<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ActivityLogger
{
    /**
     * Log admin activity
     *
     * @param string $action
     * @param string $description
     * @param array $context
     * @return void
     */
    public function logAdminActivity(string $action, string $description, array $context = []): void
    {
        $user = Auth::user();
        
        $logData = [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : 'system',
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => $context,
        ];

        Log::channel('single')->info('Admin Activity: ' . $action, $logData);
    }

    /**
     * Log system activity
     *
     * @param string $action
     * @param string $description
     * @param array $context
     * @return void
     */
    public function logSystemActivity(string $action, string $description, array $context = []): void
    {
        $logData = [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'action' => $action,
            'description' => $description,
            'context' => $context,
        ];

        Log::channel('single')->info('System Activity: ' . $action, $logData);
    }

    /**
     * Log user activity
     *
     * @param string $action
     * @param string $description
     * @param array $context
     * @return void
     */
    public function logUserActivity(string $action, string $description, array $context = []): void
    {
        $user = Auth::user();
        
        $logData = [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : 'guest',
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'context' => $context,
        ];

        Log::channel('single')->info('User Activity: ' . $action, $logData);
    }

    /**
     * Log ticket monitoring activity
     *
     * @param string $action
     * @param string $description
     * @param array $context
     * @return void
     */
    public function logTicketActivity(string $action, string $description, array $context = []): void
    {
        $logData = [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'action' => $action,
            'description' => $description,
            'context' => $context,
        ];

        Log::channel('single')->info('Ticket Activity: ' . $action, $logData);
    }

    /**
     * Log security-related activity
     *
     * @param string $action
     * @param string $description
     * @param array $context
     * @return void
     */
    public function logSecurityActivity(string $action, string $description, array $context = []): void
    {
        $user = Auth::user();
        
        $logData = [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : 'unknown',
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => $context,
        ];

        Log::channel('single')->warning('Security Activity: ' . $action, $logData);
    }

    /**
     * Log error activity
     *
     * @param string $action
     * @param string $description
     * @param array $context
     * @return void
     */
    public function logError(string $action, string $description, array $context = []): void
    {
        $logData = [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'action' => $action,
            'description' => $description,
            'context' => $context,
        ];

        Log::channel('single')->error('Error Activity: ' . $action, $logData);
    }

    /**
     * Get formatted log entry
     *
     * @param string $level
     * @param string $action
     * @param string $description
     * @param array $context
     * @return array
     */
    private function formatLogEntry(string $level, string $action, string $description, array $context = []): array
    {
        $user = Auth::user();
        
        return [
            'level' => $level,
            'timestamp' => Carbon::now()->toDateTimeString(),
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : 'system',
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => $context,
        ];
    }
}
