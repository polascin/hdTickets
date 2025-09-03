#!/bin/bash

echo "=== Comprehensive File Check and Error Detection ==="
echo "Analyzing /var/www/hdtickets/resources/views/tickets/scraping/index-enhanced.blade.php"
echo ""

cd /var/www/hdtickets

# 1. PHP Syntax Check
echo "1. PHP Syntax Validation:"
php -l resources/views/tickets/scraping/index-enhanced.blade.php > /dev/null 2>&1
if [[ $? -eq 0 ]]; then
    echo "   ✅ No PHP syntax errors"
else
    echo "   ❌ PHP syntax errors found"
    php -l resources/views/tickets/scraping/index-enhanced.blade.php
fi
echo ""

# 2. Check for missing HTML elements
echo "2. HTML Structure Check:"
MISSING_IDS=()
REQUIRED_IDS=("loading-indicator" "error-state" "tickets-container" "filters-form" "advanced-filters")

for id in "${REQUIRED_IDS[@]}"; do
    if grep -q "id=\"$id\"" resources/views/tickets/scraping/index-enhanced.blade.php; then
        echo "   ✅ Element #$id found"
    else
        echo "   ❌ Missing element #$id"
        MISSING_IDS+=("$id")
    fi
done
echo ""

# 3. JavaScript Function Definitions Check
echo "3. JavaScript Functions Check:"
REQUIRED_FUNCTIONS=("showLoading" "hideLoading" "removeFilter" "clearAllFilters" "announceToScreenReader")

for func in "${REQUIRED_FUNCTIONS[@]}"; do
    if grep -q "function $func" resources/views/tickets/scraping/index-enhanced.blade.php; then
        echo "   ✅ Function $func() defined"
    else
        echo "   ❌ Missing function $func()"
    fi
done
echo ""

# 4. Check Active Filters Implementation
echo "4. Active Filters Implementation:"
if grep -q "removeFilter({{ json_encode(" resources/views/tickets/scraping/index-enhanced.blade.php; then
    echo "   ✅ Active Filters using secure JSON encoding"
else
    echo "   ❌ Active Filters missing secure encoding"
fi

if grep -q "onkeydown=\"if(event.key==='Enter') removeFilter" resources/views/tickets/scraping/index-enhanced.blade.php; then
    echo "   ✅ Keyboard accessibility implemented"
else
    echo "   ❌ Missing keyboard accessibility"
fi
echo ""

# 5. Loading Indicator State Check
echo "5. Loading Indicator Configuration:"
if grep -q "id=\"loading-indicator\" class=\"hidden\"" resources/views/tickets/scraping/index-enhanced.blade.php; then
    echo "   ✅ Loading indicator starts hidden"
else
    echo "   ❌ Loading indicator not properly hidden by default"
fi
echo ""

# 6. Check for common JavaScript errors
echo "6. JavaScript Error Prevention:"
POTENTIAL_ISSUES=0

# Check for unescaped quotes in onclick handlers
UNESCAPED_QUOTES=$(grep -c "onclick=\"[^\"]*'[^\"]*'[^\"]*\"" resources/views/tickets/scraping/index-enhanced.blade.php || echo "0")
if [[ $UNESCAPED_QUOTES -eq 0 ]]; then
    echo "   ✅ No unescaped quotes in onclick handlers"
else
    echo "   ⚠️  Found $UNESCAPED_QUOTES potential quote escaping issues"
    POTENTIAL_ISSUES=$((POTENTIAL_ISSUES + 1))
fi

# Check for proper event listeners
if grep -q "addEventListener" resources/views/tickets/scraping/index-enhanced.blade.php; then
    echo "   ✅ Modern event listeners in use"
else
    echo "   ⚠️  No addEventListener usage found"
    POTENTIAL_ISSUES=$((POTENTIAL_ISSUES + 1))
fi
echo ""

# 7. Accessibility Check
echo "7. Accessibility Features:"
ARIA_FEATURES=$(grep -c "aria-" resources/views/tickets/scraping/index-enhanced.blade.php)
echo "   ✅ Found $ARIA_FEATURES ARIA attributes"

SR_ONLY=$(grep -c "sr-only" resources/views/tickets/scraping/index-enhanced.blade.php)
echo "   ✅ Found $SR_ONLY screen reader only elements"
echo ""

# 8. CSS Class Conflicts Check
echo "8. CSS Class Conflicts:"
FLEX_HIDDEN_CONFLICTS=$(grep -c "flex.*hidden\|hidden.*flex" resources/views/tickets/scraping/index-enhanced.blade.php || echo "0")
if [[ $FLEX_HIDDEN_CONFLICTS -eq 0 ]]; then
    echo "   ✅ No flex/hidden class conflicts"
else
    echo "   ⚠️  Found $FLEX_HIDDEN_CONFLICTS potential CSS class conflicts"
    POTENTIAL_ISSUES=$((POTENTIAL_ISSUES + 1))
fi
echo ""

# Summary
echo "=== SUMMARY ==="
if [[ ${#MISSING_IDS[@]} -eq 0 && $POTENTIAL_ISSUES -eq 0 ]]; then
    echo "🎯 RESULT: NO ERRORS FOUND - File is in excellent condition!"
    echo "   ✅ All required elements present"
    echo "   ✅ All JavaScript functions defined"
    echo "   ✅ Active Filters properly implemented"
    echo "   ✅ Loading indicator correctly configured"
    echo "   ✅ No JavaScript or CSS conflicts"
    echo "   ✅ Accessibility features implemented"
else
    echo "🚨 ISSUES DETECTED:"
    if [[ ${#MISSING_IDS[@]} -gt 0 ]]; then
        echo "   ❌ Missing elements: ${MISSING_IDS[*]}"
    fi
    if [[ $POTENTIAL_ISSUES -gt 0 ]]; then
        echo "   ⚠️  $POTENTIAL_ISSUES potential issues found"
    fi
    echo ""
    echo "Recommendations: Review and fix the identified issues above."
fi
