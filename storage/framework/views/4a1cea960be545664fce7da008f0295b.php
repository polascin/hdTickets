<section>
    <header class="mb-6">
        <h2 class="text-lg font-medium text-gray-900">
            <?php echo e(__('Enhanced User Information')); ?>

        </h2>
        <p class="mt-1 text-sm text-gray-600">
            <?php echo e(__('Comprehensive user profile information, activity stats, and system permissions.')); ?>

        </p>
    </header>

    <?php
        $userInfo = $user->getEnhancedUserInfo();
        $profile = $userInfo['profile'];
        $lastLogin = $userInfo['last_login'];
        $activityStats = $userInfo['activity_stats'];
        $accountCreation = $userInfo['account_creation'];
        $permissions = $userInfo['permissions'];
        $notifications = $userInfo['notifications'];
    ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Picture and Basic Info -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h3 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Profile Information
            </h3>
            
            <div class="flex items-center space-x-4 mb-4">
                <div class="flex-shrink-0">
                    <?php if($profile['has_picture']): ?>
                        <img class="w-16 h-16 rounded-full object-cover" src="<?php echo e($profile['picture_url']); ?>" alt="<?php echo e($profile['display_name']); ?>">
                    <?php else: ?>
                        <div class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center">
                            <span class="text-lg font-medium text-gray-700"><?php echo e($profile['initials']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-gray-900"><?php echo e($profile['full_name'] ?: 'No name set'); ?></h4>
                    <p class="text-sm text-gray-600"><?php echo e($user->username); ?></p>
                    <p class="text-sm text-gray-500"><?php echo e($user->email); ?></p>
                </div>
            </div>

            <?php if($profile['bio']): ?>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                    <p class="text-sm text-gray-600"><?php echo e($profile['bio']); ?></p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <label class="block font-medium text-gray-700">Phone</label>
                    <span class="text-gray-600"><?php echo e($user->phone ?: 'Not provided'); ?></span>
                </div>
                <div>
                    <label class="block font-medium text-gray-700">Timezone</label>
                    <span class="text-gray-600"><?php echo e($profile['timezone']); ?></span>
                </div>
                <div>
                    <label class="block font-medium text-gray-700">Language</label>
                    <span class="text-gray-600"><?php echo e(strtoupper($profile['language'])); ?></span>
                </div>
                <div>
                    <label class="block font-medium text-gray-700">User ID</label>
                    <span class="text-gray-600 font-mono text-xs"><?php echo e($user->id); ?></span>
                </div>
            </div>
        </div>

        <!-- Role and Permissions -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h3 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-<?php echo e($permissions['role_display']['color']); ?>-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Role and Permissions
            </h3>

            <div class="mb-4">
                <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-<?php echo e($permissions['role_display']['color']); ?>-100 text-<?php echo e($permissions['role_display']['color']); ?>-800">
                    <?php echo e($permissions['role_display']['label']); ?>

                </span>
                <?php if($user->is_active): ?>
                    <span class="ml-2 inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                        Active
                    </span>
                <?php else: ?>
                    <span class="ml-2 inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                        Inactive
                    </span>
                <?php endif; ?>
            </div>

            <div class="space-y-2 text-sm">
                <?php if($permissions['permissions']['is_admin']): ?>
                    <div class="flex items-center text-red-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        System Administration
                    </div>
                <?php endif; ?>

                <?php if($permissions['permissions']['is_agent']): ?>
                    <div class="flex items-center text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Ticket Operations
                    </div>
                <?php endif; ?>

                <?php if($permissions['permissions']['manage_users']): ?>
                    <div class="flex items-center text-purple-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        User Management
                    </div>
                <?php endif; ?>

                <?php if($permissions['permissions']['view_scraping_metrics']): ?>
                    <div class="flex items-center text-indigo-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Scraping Metrics Access
                    </div>
                <?php endif; ?>

                <?php if(!$permissions['is_system_accessible']): ?>
                    <div class="flex items-center text-gray-500">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                        </svg>
                        No System Access (Scraper User)
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Last Login Information -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h3 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Last Login Information
            </h3>

            <div class="space-y-3 text-sm">
                <div>
                    <label class="block font-medium text-gray-700">Last Login</label>
                    <span class="text-gray-900"><?php echo e($lastLogin['formatted']); ?></span>
                    <?php if($lastLogin['relative'] !== 'Never'): ?>
                        <span class="block text-xs text-gray-500"><?php echo e($lastLogin['relative']); ?></span>
                    <?php endif; ?>
                </div>

                <?php if($lastLogin['ip']): ?>
                    <div>
                        <label class="block font-medium text-gray-700">IP Address</label>
                        <span class="text-gray-600 font-mono text-xs"><?php echo e($lastLogin['ip']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if($lastLogin['user_agent']): ?>
                    <div>
                        <label class="block font-medium text-gray-700">Browser/Device</label>
                        <span class="text-gray-600 text-xs"><?php echo e(Str::limit($lastLogin['user_agent'], 60)); ?></span>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block font-medium text-gray-700">Email Verified</label>
                    <?php if($activityStats['email_verified']): ?>
                        <span class="inline-flex items-center text-xs text-green-600">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Verified
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center text-xs text-red-600">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Not Verified
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Activity Statistics -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h3 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Activity Statistics
            </h3>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <label class="block font-medium text-gray-700">Total Logins</label>
                    <span class="text-2xl font-bold text-purple-600"><?php echo e(number_format($activityStats['login_count'])); ?></span>
                </div>
                <div>
                    <label class="block font-medium text-gray-700">Activity Score</label>
                    <span class="text-2xl font-bold text-indigo-600"><?php echo e(number_format($activityStats['activity_score'])); ?></span>
                </div>
                <div>
                    <label class="block font-medium text-gray-700">Account Age</label>
                    <span class="text-gray-900"><?php echo e($activityStats['account_age_days']); ?> days</span>
                </div>
                <div>
                    <label class="block font-medium text-gray-700">Last Activity</label>
                    <span class="text-gray-600 text-xs"><?php echo e($activityStats['last_activity']); ?></span>
                </div>
            </div>
        </div>

        <!-- Account Creation -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h3 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm6 0a2 2 0 100-4 2 2 0 000 4zm-6 4a2 2 0 100-4 2 2 0 000 4zm6 0a2 2 0 100-4 2 2 0 000 4z"></path>
                </svg>
                Account Creation Source
            </h3>

            <div class="space-y-3 text-sm">
                <div>
                    <label class="block font-medium text-gray-700">Registration Source</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                        <?php echo e($accountCreation['source_label']); ?>

                    </span>
                </div>

                <div>
                    <label class="block font-medium text-gray-700">Created</label>
                    <span class="text-gray-900"><?php echo e($accountCreation['created_at_formatted']); ?></span>
                    <span class="block text-xs text-gray-500"><?php echo e($accountCreation['created_at_relative']); ?></span>
                </div>

                <div>
                    <label class="block font-medium text-gray-700">Created By</label>
                    <span class="text-gray-600">
                        <?php if($accountCreation['created_by_type'] === 'self'): ?>
                            Self Registration
                        <?php elseif($accountCreation['created_by_type'] === 'admin'): ?>
                            Administrator
                        <?php elseif($accountCreation['created_by_type'] === 'system'): ?>
                            System Generated
                        <?php else: ?>
                            <?php echo e(ucfirst($accountCreation['created_by_type'])); ?>

                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Notification Preferences -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h3 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h10a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Notification Preferences
            </h3>

            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <label class="font-medium text-gray-700">Email Notifications</label>
                    <?php if($notifications['email_notifications']): ?>
                        <span class="inline-flex items-center text-xs text-green-600">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Enabled
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center text-xs text-red-600">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Disabled
                        </span>
                    <?php endif; ?>
                </div>

                <div class="flex items-center justify-between">
                    <label class="font-medium text-gray-700">Push Notifications</label>
                    <?php if($notifications['push_notifications']): ?>
                        <span class="inline-flex items-center text-xs text-green-600">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Enabled
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center text-xs text-red-600">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Disabled
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views/profile/partials/enhanced-user-info.blade.php ENDPATH**/ ?>