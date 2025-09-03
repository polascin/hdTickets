#!/bin/bash

# Comprehensive Dropdown Testing Script
# Tests all dropdown functionality and generates a detailed report

echo "🔧 HD Tickets - Comprehensive Dropdown Testing"
echo "=============================================="
echo

# Function to check if a file exists and contains specific content
check_file_content() {
    local file="$1"
    local pattern="$2"
    local description="$3"
    
    if [[ -f "$file" ]]; then
        if grep -q "$pattern" "$file"; then
            echo "✅ $description: FOUND"
            return 0
        else
            echo "❌ $description: MISSING"
            return 1
        fi
    else
        echo "❌ $description: FILE NOT FOUND"
        return 1
    fi
}

# Test navigation dropdown implementation
echo "📱 Testing Navigation Dropdown Implementation..."
check_file_content "resources/views/layouts/navigation.blade.php" "toggleAdminDropdown" "Admin dropdown toggle function"
check_file_content "resources/views/layouts/navigation.blade.php" "toggleProfileDropdown" "Profile dropdown toggle function"
check_file_content "resources/views/layouts/navigation.blade.php" "x-show.*adminDropdownOpen" "Admin dropdown visibility binding"
check_file_content "resources/views/layouts/navigation.blade.php" "x-show.*profileDropdownOpen" "Profile dropdown visibility binding"
check_file_content "resources/views/layouts/navigation.blade.php" "aria-expanded" "Accessibility attributes"
echo

# Test Alpine.js components
echo "🏔️ Testing Alpine.js Dropdown Components..."
check_file_content "resources/views/components/dropdown.blade.php" "x-data.*open.*false" "Basic dropdown component"
check_file_content "resources/views/components/ui/dropdown.blade.php" "hd-dropdown" "HD UI dropdown component"
check_file_content "resources/views/components/enhanced-dropdown.blade.php" "Enhanced Universal Dropdown" "Enhanced dropdown component"
check_file_content "resources/views/components/multi-select.blade.php" "Multi-Select Dropdown" "Multi-select component"
check_file_content "resources/views/components/dropdown-item.blade.php" "Enhanced Dropdown Menu Item" "Dropdown item component"
echo

# Test CSS implementations
echo "🎨 Testing CSS Implementations..."
check_file_content "public/css/navigation-enhanced.css" "nav-dropdown" "Navigation dropdown styles"
check_file_content "public/css/navigation-dashboard-fixes.css" "dropdown-menu" "Dashboard dropdown fixes"
check_file_content "public/css/dropdown-enhancements.css" "Universal Select/Dropdown" "Enhanced dropdown styles"
echo

# Test JavaScript functionality
echo "⚡ Testing JavaScript Functionality..."
check_file_content "resources/js/components/navigation.js" "navigationData" "Navigation JavaScript component"
check_file_content "resources/js/components/navigation.js" "handleArrowNavigation" "Keyboard navigation"
check_file_content "resources/js/components/navigation.js" "setupAccessibility" "Accessibility features"
echo

# Test Bootstrap integrations
echo "🅱️ Testing Bootstrap Integrations..."
check_file_content "resources/views/admin/reports/index.blade.php" "data-bs-toggle.*dropdown" "Admin reports dropdown"
check_file_content "resources/views/admin/user-profile.blade.php" "dropdown-toggle" "User profile dropdown"
echo

# Test responsive design
echo "📱 Testing Responsive Design..."
check_file_content "public/css/navigation-enhanced.css" "@media.*max-width" "Mobile responsive styles"
check_file_content "public/css/dropdown-enhancements.css" "pointer.*coarse" "Touch device optimizations"
echo

# Test accessibility features
echo "♿ Testing Accessibility Features..."
check_file_content "resources/views/layouts/navigation.blade.php" "role.*menu" "Menu roles"
check_file_content "resources/views/layouts/navigation.blade.php" "role.*menuitem" "Menu item roles"
check_file_content "public/css/dropdown-enhancements.css" "prefers-contrast" "High contrast mode"
check_file_content "public/css/dropdown-enhancements.css" "focus.*outline" "Focus indicators"
echo

# Check if files are properly linked in layouts
echo "🔗 Testing File Integration..."
check_file_content "resources/views/layouts/app.blade.php" "navigation-enhanced.css" "Navigation CSS linked"
check_file_content "resources/views/layouts/app.blade.php" "dropdown-enhancements.css" "Dropdown enhancements CSS linked"
check_file_content "resources/views/layouts/app.blade.php" "navigationData" "Navigation JS component registered"
echo

# Test form integration
echo "📝 Testing Form Integration..."
if [[ -f "resources/views/dropdown-demo.blade.php" ]]; then
    echo "✅ Dropdown demo page: EXISTS"
    check_file_content "resources/views/dropdown-demo.blade.php" "x-enhanced-dropdown" "Enhanced dropdown usage"
    check_file_content "resources/views/dropdown-demo.blade.php" "x-multi-select" "Multi-select usage"
else
    echo "❌ Dropdown demo page: MISSING"
fi
echo

# Generate summary
echo "📊 FINAL SUMMARY"
echo "================"

# Count files
total_files=0
existing_files=0

files_to_check=(
    "resources/views/layouts/navigation.blade.php"
    "resources/views/components/dropdown.blade.php" 
    "resources/views/components/ui/dropdown.blade.php"
    "resources/views/components/enhanced-dropdown.blade.php"
    "resources/views/components/multi-select.blade.php"
    "resources/views/components/dropdown-item.blade.php"
    "public/css/navigation-enhanced.css"
    "public/css/navigation-dashboard-fixes.css"
    "public/css/dropdown-enhancements.css"
    "resources/js/components/navigation.js"
    "resources/views/dropdown-demo.blade.php"
)

for file in "${files_to_check[@]}"; do
    total_files=$((total_files + 1))
    if [[ -f "$file" ]]; then
        existing_files=$((existing_files + 1))
    fi
done

echo "Core Dropdown Files: $existing_files/$total_files"

# Calculate success rate
success_rate=$(( (existing_files * 100) / total_files ))
echo "Implementation Status: $success_rate%"
echo

if [[ $success_rate -ge 95 ]]; then
    echo "🎉 EXCELLENT! Dropdown implementation is nearly complete."
elif [[ $success_rate -ge 80 ]]; then
    echo "✅ GOOD! Most dropdown functionality is implemented."
elif [[ $success_rate -ge 60 ]]; then
    echo "⚠️ FAIR! Basic dropdown functionality exists but needs work."
else
    echo "❌ POOR! Dropdown implementation needs significant work."
fi

echo
echo "🏁 Comprehensive dropdown testing completed!"
echo "📋 Check individual test results above for specific issues."
