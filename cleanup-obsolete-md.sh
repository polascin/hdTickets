#!/bin/bash

# HD Tickets Markdown Cleanup Script
# Removes obsolete report and summary files while preserving important documentation

set -e  # Exit on error

echo "🧹 HD Tickets - Removing Obsolete Markdown Files"
echo "==============================================="

# Important files to KEEP (never delete these)
KEEP_FILES=(
    "README.md"
    "CHANGELOG.md"
    "API_ROUTE_DOCUMENTATION.md"
    "DDD_IMPLEMENTATION.md"
    "SECURITY.md"
    "SSL_SETUP_GUIDE.md"
    "SSL_SETUP_DOCUMENTATION.md"
    "DASHBOARD_ROUTING_DOCUMENTATION.md"
    "DASHBOARD_ROUTE_ANALYSIS.md"
    "PRODUCTION_READY.md"
    "PRODUCTION_MONITORING.md"
    "ENHANCED_DASHBOARD_IMPLEMENTATION.md"
    "DEPENDENCY_UPDATE_GUIDELINES.md"
    "conflict-resolution-guide.md"
    "CUSTOMER_DASHBOARD_REFACTOR.md"
    "SERVICE_CONSOLIDATION_PLAN.md"
    "SECURITY_HARDENING_IMPLEMENTATION.md"
    "ROLE_CHECKING_UPDATES.md"
)

# Files to DEFINITELY REMOVE (obsolete reports and summaries)
REMOVE_FILES=(
    "*_REPORT.md"
    "*_SUMMARY.md"
    "*REPORT.md"
    "*SUMMARY.md"
    "*SUCCESS*.md"
    "*FIX*.md"
    "*COMPLETION*.md"
    "*AUDIT*.md"
    "*ACHIEVEMENT*.md"
    "phpstan_*.md"
    "*OPTIMIZATION*.md"
    "*QUALITY*.md"
    "*TESTING*.md"
    "README-old.md"
    "README-*.md"
    "README_PHPSTAN_COMPLETE.md"
)

# Create backup directory
BACKUP_DIR="./md_cleanup_backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

echo "📂 Created backup directory: $BACKUP_DIR"

removed_count=0
backed_up_count=0

# Function to check if file should be kept
should_keep_file() {
    local file="$1"
    local basename=$(basename "$file")
    
    # Check if it's in the keep list
    for keep_file in "${KEEP_FILES[@]}"; do
        if [[ "$basename" == "$keep_file" ]]; then
            return 0  # Keep it
        fi
    done
    
    return 1  # Don't keep it
}

# Process files to remove
echo ""
echo "🗑️  REMOVING OBSOLETE FILES:"
echo "--------------------------"

for pattern in "${REMOVE_FILES[@]}"; do
    # Find files matching the pattern
    found_files=$(find . -maxdepth 1 -name "$pattern" -type f 2>/dev/null || true)
    
    for file in $found_files; do
        if [ -f "$file" ]; then
            # Check if we should keep this file
            if should_keep_file "$file"; then
                echo "🛡️  PROTECTED: $file (in keep list)"
                continue
            fi
            
            # Backup the file
            cp "$file" "$BACKUP_DIR/"
            echo "💾 Backed up: $file"
            ((backed_up_count++))
            
            # Remove the file
            rm "$file"
            echo "❌ Removed: $file"
            ((removed_count++))
        fi
    done
done

# Also check for specific files by exact name (handle edge cases)
SPECIFIC_REMOVES=(
    "OPTIMIZATION_BACKUP_\$(date +%Y%m%d).md"
    "OPTIMIZATION_BACKUP_*.md"
)

for file_pattern in "${SPECIFIC_REMOVES[@]}"; do
    found_files=$(find . -maxdepth 1 -name "$file_pattern" -type f 2>/dev/null || true)
    for file in $found_files; do
        if [ -f "$file" ] && ! should_keep_file "$file"; then
            cp "$file" "$BACKUP_DIR/"
            rm "$file"
            echo "❌ Removed special case: $file"
            ((removed_count++))
        fi
    done
done

echo ""
echo "✅ PROTECTED FILES (kept):"
echo "-------------------------"
for keep_file in "${KEEP_FILES[@]}"; do
    if [ -f "$keep_file" ]; then
        echo "🛡️  $keep_file"
    fi
done

echo ""
echo "📊 CLEANUP SUMMARY:"
echo "------------------"
echo "Files backed up: $backed_up_count"
echo "Files removed: $removed_count"
echo "Backup location: $BACKUP_DIR"

# Show remaining markdown files
echo ""
echo "📁 REMAINING MARKDOWN FILES:"
echo "----------------------------"
remaining_md=$(find . -maxdepth 1 -name "*.md" -type f | wc -l)
echo "Root directory .md files: $remaining_md"

if [ -d "docs" ]; then
    docs_md=$(find docs/ -name "*.md" -type f | wc -l)
    echo "Documentation (docs/) files: $docs_md"
fi

echo ""
echo "🎉 Cleanup completed! All obsolete files have been removed."
echo "   Backup available at: $BACKUP_DIR"
echo ""
echo "📝 Next steps:"
echo "   1. Review the remaining files to ensure they're all needed"
echo "   2. If satisfied, you can remove the backup directory"
echo "   3. Commit the cleanup: git add . && git commit -m 'Remove obsolete markdown files'"
