#!/bin/bash

# VSCode Git Safety Pre-Commit Validation Script
# Prevents dangerous Git operations and VSCode warnings

echo "🔍 Running VSCode Git Safety Check..."
echo "========================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Flag to track if any issues are found
ISSUES_FOUND=0

echo ""
echo "📋 Checking VSCode Configuration..."
echo "-----------------------------------"

# Check VSCode settings for dangerous configurations
if [ -f ".vscode/settings.json" ]; then
    echo -e "${BLUE}Checking .vscode/settings.json...${NC}"
    
    # Check for force push allowance
    if grep -q '"git.allowForcePush": true' .vscode/settings.json; then
        echo -e "${RED}❌ DANGER: Force push is enabled in VSCode settings${NC}"
        echo "   This can be dangerous for protected branches!"
        ISSUES_FOUND=1
    else
        echo -e "${GREEN}✓ Force push protection: Enabled${NC}"
    fi
    
    # Check for no-verify commit allowance
    if grep -q '"git.allowNoVerifyCommit": true' .vscode/settings.json; then
        echo -e "${RED}❌ DANGER: No-verify commits are enabled${NC}"
        echo "   This bypasses important git hooks and validations!"
        ISSUES_FOUND=1
    else
        echo -e "${GREEN}✓ No-verify commit protection: Enabled${NC}"
    fi
    
    # Check file permissions
    PERMS=$(stat -c "%a" .vscode/settings.json)
    if [ "$PERMS" != "644" ]; then
        echo -e "${YELLOW}⚠ Warning: VSCode settings.json has unusual permissions ($PERMS)${NC}"
        echo "   Should be 644 for security"
        ISSUES_FOUND=1
    else
        echo -e "${GREEN}✓ File permissions: Correct (644)${NC}"
    fi
else
    echo -e "${YELLOW}⚠ No VSCode settings.json found${NC}"
fi

# Check workspace file for dangerous tasks
if [ -f ".vscode/hdtickets.code-workspace" ]; then
    echo -e "${BLUE}Checking workspace file...${NC}"
    
    if grep -q "Quick Commit & Push" .vscode/hdtickets.code-workspace; then
        echo -e "${RED}❌ DANGER: Quick commit & push task found${NC}"
        echo "   This bypasses proper git workflow!"
        ISSUES_FOUND=1
    else
        echo -e "${GREEN}✓ No dangerous quick commit tasks found${NC}"
    fi
fi

echo ""
echo "🔐 Checking Git Configuration..."
echo "--------------------------------"

# Check current branch
CURRENT_BRANCH=$(git branch --show-current)
echo -e "${BLUE}Current branch: ${NC}$CURRENT_BRANCH"

# Check if we're on a protected branch
PROTECTED_BRANCHES=("main" "master" "production" "staging")
IS_PROTECTED=0
for branch in "${PROTECTED_BRANCHES[@]}"; do
    if [ "$CURRENT_BRANCH" = "$branch" ]; then
        IS_PROTECTED=1
        echo -e "${YELLOW}⚠ Working on protected branch: $branch${NC}"
        break
    fi
done

if [ $IS_PROTECTED -eq 0 ]; then
    echo -e "${GREEN}✓ Working on non-protected branch${NC}"
fi

# Check for uncommitted changes
UNCOMMITTED=$(git status --porcelain | wc -l)
if [ $UNCOMMITTED -gt 0 ]; then
    echo -e "${BLUE}Uncommitted changes: $UNCOMMITTED files${NC}"
else
    echo -e "${GREEN}✓ No uncommitted changes${NC}"
fi

# Check remote connection
echo -e "${BLUE}Testing remote connection...${NC}"
if git ls-remote origin > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Remote connection: OK${NC}"
else
    echo -e "${RED}❌ Remote connection: Failed${NC}"
    echo "   Check your internet connection and GitHub permissions"
    ISSUES_FOUND=1
fi

echo ""
echo "📁 Checking File Structure..."
echo "-----------------------------"

# Check for large files that might cause issues
echo -e "${BLUE}Checking for large files...${NC}"
LARGE_FILES=$(find . -type f -size +50M -not -path "./node_modules/*" -not -path "./.git/*" -not -path "./vendor/*" 2>/dev/null)
if [ -n "$LARGE_FILES" ]; then
    echo -e "${YELLOW}⚠ Large files found:${NC}"
    echo "$LARGE_FILES"
    echo "   Consider adding to .gitignore if not needed in repository"
else
    echo -e "${GREEN}✓ No large files found${NC}"
fi

# Check for truly sensitive files (not legitimate framework files)
echo -e "${BLUE}Checking for sensitive files...${NC}"
SENSITIVE_FOUND=0

# Check for actual secret files that should never be committed
SECRET_FILES=(".env" ".env.local" ".env.production" "*.key" "*.pem" "*secret.json" "*credentials.json")

for pattern in "${SECRET_FILES[@]}"; do
    FOUND_FILES=$(find . -name "$pattern" -not -path "./.git/*" -not -path "./node_modules/*" -not -path "./vendor/*" 2>/dev/null)
    
    for file in $FOUND_FILES; do
        # Check if file is tracked by git
        if git ls-files --error-unmatch "$file" > /dev/null 2>&1; then
            echo -e "${RED}❌ Tracked sensitive file: $file${NC}"
            SENSITIVE_FOUND=1
        fi
    done
done

# Check for files with actual secrets in them (API keys, tokens, etc)
if grep -r "api[_-]key\s*[:=]\s*['\"][a-zA-Z0-9]" --include="*.php" --include="*.js" --include="*.json" --exclude-dir=vendor --exclude-dir=node_modules . > /dev/null 2>&1; then
    echo -e "${YELLOW}⚠️  Potential API keys found in code - review carefully${NC}"
fi

if [ $SENSITIVE_FOUND -eq 1 ]; then
    echo -e "${RED}❌ Secret files are being tracked by git${NC}"
    echo "   These should be added to .gitignore immediately"
    ISSUES_FOUND=1
else
    echo -e "${GREEN}✓ No secret files are tracked by git${NC}"
fi

echo ""
echo "🛠️ VSCode Extension Recommendations..."
echo "-------------------------------------"

# List recommended extensions for safe git workflow
echo -e "${BLUE}Recommended VSCode Extensions for safe Git workflow:${NC}"
echo "  • GitLens (eamodio.gitlens) - Enhanced Git capabilities"
echo "  • Git Graph (mhutchie.git-graph) - Visual git history" 
echo "  • GitHub Pull Requests (github.vscode-pull-request-github) - PR management"
echo "  • GitIgnore (codezombiech.gitignore) - .gitignore management"

echo ""
echo "📊 Summary"
echo "----------"

if [ $ISSUES_FOUND -eq 0 ]; then
    echo -e "${GREEN}🎉 All checks passed! Safe to commit to protected branch.${NC}"
    echo ""
    echo -e "${BLUE}Next steps:${NC}"
    echo "  1. git add <files>     - Stage your changes"
    echo "  2. git commit -m \"message\" - Commit with descriptive message"
    echo "  3. git push origin $CURRENT_BRANCH - Push to remote"
    echo ""
    if [ $IS_PROTECTED -eq 1 ]; then
        echo -e "${YELLOW}Note: Since you're on protected branch '$CURRENT_BRANCH', consider:${NC}"
        echo "  • Creating a feature branch for complex changes"
        echo "  • Using pull requests for code review"
        echo "  • Following your team's branch protection rules"
    fi
    exit 0
else
    echo -e "${RED}❌ Issues found that need attention before committing!${NC}"
    echo ""
    echo -e "${YELLOW}Recommendations:${NC}"
    echo "  1. Fix VSCode configuration issues above"
    echo "  2. Review and resolve any warnings"
    echo "  3. Run this script again to verify fixes"
    echo ""
    echo -e "${RED}Please resolve these issues before committing to protected branch.${NC}"
    exit 1
fi
