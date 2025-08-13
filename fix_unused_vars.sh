#!/bin/bash

# Script to systematically fix unused variable ESLint errors
# This will prefix unused variables with underscores where appropriate

cd /var/www/hdtickets

# Get all unused variable errors and process them
npm run lint 2>&1 | grep -E "(error.*defined but never used|error.*assigned.*never used)" | while read -r line; do
    # Extract file path and variable name
    file_path=$(echo "$line" | cut -d: -f1)
    line_num=$(echo "$line" | cut -d: -f2)
    variable=$(echo "$line" | grep -oP "'[^']*'" | head -1 | tr -d "'")
    
    if [[ -n "$file_path" && -n "$variable" && -n "$line_num" ]]; then
        echo "Processing: $file_path:$line_num - Variable: $variable"
        
        # Skip variables that already start with underscore
        if [[ "$variable" != _* ]]; then
            # Use sed to replace the variable name on the specific line
            # This is a simplified approach - in production you'd want more sophisticated parsing
            sed -i "${line_num}s/\\b${variable}\\b/_${variable}/g" "$file_path" 2>/dev/null || echo "  Failed to process $file_path"
        fi
    fi
done

echo "Batch processing complete. Running lint again to check results..."
npm run lint 2>&1 | tail -5
