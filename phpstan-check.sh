#!/bin/bash

# PHPStan Local Development Workflow Script
# Usage: ./phpstan-check.sh [quick|full|baseline]

echo "🔍 PHPStan Analysis for HD Tickets Laravel Application"
echo "======================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default mode
MODE=${1:-quick}

case $MODE in
    "quick")
        echo -e "${BLUE}🚀 Running Quick Analysis (Level 1)${NC}"
        vendor/bin/phpstan analyse --level=1 --memory-limit=1G
        ;;
    "full")
        echo -e "${BLUE}🔬 Running Full Analysis (All levels)${NC}"
        vendor/bin/phpstan analyse --level=max --memory-limit=1G
        ;;
    "baseline")
        echo -e "${YELLOW}📋 Generating Baseline (Current errors as acceptable)${NC}"
        vendor/bin/phpstan analyse --level=1 --generate-baseline --memory-limit=1G
        echo -e "${GREEN}✅ Baseline created: phpstan-baseline.neon${NC}"
        ;;
    "count")
        echo -e "${BLUE}📊 Getting Error Count${NC}"
        ERRORS=$(vendor/bin/phpstan analyse --level=1 --error-format=json --memory-limit=1G | jq -r '.totals.file_errors // 0')
        echo -e "${GREEN}Current error count: $ERRORS${NC}"
        ;;
    "categories")
        echo -e "${BLUE}📊 Error Category Breakdown${NC}"
        vendor/bin/phpstan analyse --level=1 --error-format=json --memory-limit=1G | \
        jq -r '.files | to_entries | map(.value.messages[]) | group_by(.identifier) | map({identifier: .[0].identifier, count: length}) | sort_by(-.count) | .[] | "\(.count) \(.identifier)"'
        ;;
    "help"|"-h"|"--help")
        echo "Available modes:"
        echo "  quick      - Quick analysis (Level 1) [default]"
        echo "  full       - Full analysis (Max level)"
        echo "  baseline   - Generate baseline file"
        echo "  count      - Show total error count"
        echo "  categories - Show error breakdown by category"
        echo "  help       - Show this help message"
        ;;
    *)
        echo -e "${RED}❌ Invalid mode: $MODE${NC}"
        echo "Use './phpstan-check.sh help' for available options"
        exit 1
        ;;
esac

# Show completion message
if [ $? -eq 0 ] && [ "$MODE" != "help" ]; then
    echo -e "\n${GREEN}✅ PHPStan analysis completed successfully!${NC}"
else
    if [ "$MODE" != "help" ]; then
        echo -e "\n${RED}❌ PHPStan analysis found issues to address.${NC}"
    fi
fi
