# Domain Events Trigger Fix

## Problem Description

The HD Tickets application was experiencing a `UniqueConstraintViolationException` when users logged in multiple times:

```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'User-4-1' for key 'domain_events.uk_aggregate_version'
```

## Root Cause

The database trigger `log_user_changes` on the `users` table was hardcoded to always use `aggregate_version = 1` when inserting domain events:

```sql
INSERT INTO domain_events (..., aggregate_version, ...)
VALUES (..., 1, ...)  -- Always 1, causing constraint violations
```

This violated the unique constraint `uk_aggregate_version` which requires that each combination of `(aggregate_type, aggregate_id, aggregate_version)` be unique.

When a user logged in multiple times, the `login_count` field was updated each time, triggering the database trigger and attempting to insert multiple events with the same `aggregate_version = 1`.

## Solution

### Migration: `2025_09_16_102156_fix_user_trigger_with_function.php`

The fix involved:

1. **Creating a stored function** `GetNextAggregateVersion()` to properly calculate the next aggregate version:
   ```sql
   CREATE FUNCTION GetNextAggregateVersion(p_aggregate_type VARCHAR(100), p_aggregate_id VARCHAR(100))
   RETURNS BIGINT UNSIGNED
   BEGIN
       DECLARE next_version BIGINT UNSIGNED DEFAULT 1;
       
       SELECT COALESCE(MAX(aggregate_version), 0) + 1 INTO next_version
       FROM domain_events 
       WHERE aggregate_type = p_aggregate_type 
       AND aggregate_id = p_aggregate_id;
       
       RETURN next_version;
   END
   ```

2. **Updating the trigger** to use the function instead of hardcoding version 1:
   ```sql
   CREATE TRIGGER log_user_changes
   AFTER UPDATE ON users 
   FOR EACH ROW
   INSERT INTO domain_events (...)
   VALUES (..., GetNextAggregateVersion("User", NEW.id), ...)
   ```

3. **Enhanced event data** to include more context (login_count in old_values and new_values).

## Benefits

- ✅ **Fixes the constraint violation**: Each domain event now gets a unique aggregate version
- ✅ **Maintains Event Sourcing integrity**: Proper versioning for event streams
- ✅ **Better audit trail**: Login count changes are now properly tracked
- ✅ **Backwards compatible**: Existing events remain unchanged
- ✅ **Future-proof**: Works for any number of user updates

## Verification

After applying the fix, multiple user updates now work correctly:

```php
// This now works without constraint violations
$user = User::find(4);
$user->increment('login_count'); // Creates domain_events with version 2
$user->increment('login_count'); // Creates domain_events with version 3
// And so on...
```

Domain events are created with proper versioning:
```
aggregate_version: 3, event_type: "UserUpdated", occurred_at: "2025-09-16 12:22:49"
aggregate_version: 2, event_type: "UserUpdated", occurred_at: "2025-09-16 12:22:28"  
aggregate_version: 1, event_type: "UserUpdated", occurred_at: "2025-09-16 12:19:30"
```

## Files Modified

- `database/migrations/2025_09_16_102110_fix_user_trigger_aggregate_version.php` (initial attempt)
- `database/migrations/2025_09_16_102156_fix_user_trigger_with_function.php` (final solution)

## Related Components

- Event Sourcing architecture in `app/Infrastructure/EventStore/`
- Domain events table with `uk_aggregate_version` constraint
- User authentication system that updates login counts

## Date Fixed
2025-09-16

## Fixed By
AI Assistant via User Request