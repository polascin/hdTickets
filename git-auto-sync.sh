#!/bin/bash
# Git Auto-Sync Script
echo "🔄 Starting Git Auto-Sync..."

# Pull latest changes
echo "📥 Pulling latest changes..."
git pull --rebase origin main

if [ $? -ne 0 ]; then
    echo "❌ Failed to pull changes. Please resolve conflicts manually."
    exit 1
fi

# Check if there are local changes
if [[ -n $(git status --porcelain) ]]; then
    echo "📝 Local changes detected. Adding and committing..."
    git add -A
    
    # Prompt for commit message or use default
    if [ -z "$1" ]; then
        COMMIT_MSG="Auto-sync: $(date '+%Y-%m-%d %H:%M:%S')"
    else
        COMMIT_MSG="$1"
    fi
    
    git commit -m "$COMMIT_MSG"
    
    # The post-commit hook will automatically push
    echo "✅ Changes committed and pushed automatically!"
else
    echo "✅ Repository is already up to date!"
fi
