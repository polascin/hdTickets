# Git Auto-Sync Script (PowerShell)
Write-Host "🔄 Starting Git Auto-Sync..." -ForegroundColor Cyan

# Pull latest changes
Write-Host "📥 Pulling latest changes..." -ForegroundColor Yellow
git pull --rebase origin main

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Failed to pull changes. Please resolve conflicts manually." -ForegroundColor Red
    exit 1
}

# Check if there are local changes
$status = git status --porcelain
if ($status) {
    Write-Host "📝 Local changes detected. Adding and committing..." -ForegroundColor Yellow
    git add -A
    
    # Use provided commit message or default
    if ($args.Count -gt 0) {
        $commitMsg = $args -join " "
    } else {
        $commitMsg = "Auto-sync: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    }
    
    git commit -m $commitMsg
    
    # The post-commit hook will automatically push
    Write-Host "✅ Changes committed and pushed automatically!" -ForegroundColor Green
} else {
    Write-Host "✅ Repository is already up to date!" -ForegroundColor Green
}
