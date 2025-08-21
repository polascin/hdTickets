#!/bin/bash

# Script to identify and categorize markdown files for cleanup
# This helps identify which .md files are obsolete vs important

echo "🧹 HD Tickets Markdown File Cleanup Analysis"
echo "============================================="

# Files to KEEP (important documentation)
KEEP_FILES=(
    "README.md"
    "CHANGELOG.md" 
    "LICENSE.md"
    "CONTRIBUTING.md"
    "CODE_OF_CONDUCT.md"
)

# Patterns for files that are likely OBSOLETE (reports, summaries, etc.)
OBSOLETE_PATTERNS=(
    "*REPORT.md"
    "*SUMMARY.md" 
    "*SUCCESS*.md"
    "*FIX*.md"
    "*COMPLETION*.md"
    "*AUDIT*.md"
    "*ACHIEVEMENT*.md"
    "phpstan_*.md"
    "*_REPORT.md"
    "*_SUMMARY.md"
    "*_SUCCESS.md"
    "*_FIX.md"
    "*_COMPLETION.md"
    "*_AUDIT.md"
    "*OPTIMIZATION*.md"
    "*QUALITY*.md"
    "*TESTING*.md"
    "README-old.md"
    "README-*.md"
)

echo ""
echo "📋 IMPORTANT FILES TO KEEP:"
echo "-------------------------"
for file in "${KEEP_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file (EXISTS - will be kept)"
    else
        echo "❌ $file (not found)"
    fi
done

echo ""
echo "📊 POTENTIALLY OBSOLETE FILES:"
echo "-----------------------------"
obsolete_count=0

# Find potentially obsolete files
for pattern in "${OBSOLETE_PATTERNS[@]}"; do
    found_files=$(find . -maxdepth 1 -name "$pattern" -type f 2>/dev/null | sort)
    for file in $found_files; do
        if [[ ! " ${KEEP_FILES[@]} " =~ " $(basename $file) " ]]; then
            echo "🗑️  $file"
            ((obsolete_count++))
        fi
    done
done

echo ""
echo "📁 DOCUMENTATION DIRECTORIES:"
echo "----------------------------"
if [ -d "docs" ]; then
    echo "📂 docs/ directory found - contents:"
    find docs/ -name "*.md" -type f | head -10 | while read file; do
        echo "   📄 $file"
    done
    doc_count=$(find docs/ -name "*.md" -type f | wc -l)
    if [ $doc_count -gt 10 ]; then
        echo "   ... and $((doc_count - 10)) more files"
    fi
fi

echo ""
echo "📈 SUMMARY:"
echo "----------"
total_md=$(find . -name "*.md" -type f | wc -l)
echo "Total .md files found: $total_md"
echo "Potentially obsolete: $obsolete_count"
echo "Important files to keep: ${#KEEP_FILES[@]}"

echo ""
echo "⚠️  MANUAL REVIEW NEEDED FOR:"
echo "----------------------------"
# Find files that don't match common patterns
find . -maxdepth 1 -name "*.md" -type f ! -name "*REPORT*" ! -name "*SUMMARY*" ! -name "*SUCCESS*" ! -name "*FIX*" ! -name "*COMPLETION*" ! -name "*AUDIT*" ! -name "*ACHIEVEMENT*" ! -name "phpstan_*" ! -name "*OPTIMIZATION*" ! -name "*QUALITY*" ! -name "*TESTING*" ! -name "README-old*" ! -name "README.md" ! -name "CHANGELOG.md" | while read file; do
    echo "🤔 $file (needs manual review)"
done
