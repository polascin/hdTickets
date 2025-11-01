#!/bin/bash

# HD Tickets - Deployment Setup Helper Script
# This script helps set up SSH keys and test deployment configuration

set -e

echo "🚀 HD Tickets - Deployment Setup Helper"
echo "========================================"
echo ""

# Colours for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Colour

# Function to print success message
success() {
    echo -e "${GREEN}✓ $1${NC}"
}

# Function to print warning message
warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# Function to print error message
error() {
    echo -e "${RED}✗ $1${NC}"
}

# Step 1: Generate SSH key
echo "Step 1: Generate SSH Deploy Key"
echo "--------------------------------"

KEY_PATH="$HOME/.ssh/github_deploy_hdtickets"

if [ -f "$KEY_PATH" ]; then
    warning "SSH key already exists at $KEY_PATH"
    read -p "Do you want to overwrite it? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Using existing key..."
    else
        ssh-keygen -t ed25519 -C "github-deploy-hdtickets" -f "$KEY_PATH" -N ""
        success "New SSH key generated"
    fi
else
    ssh-keygen -t ed25519 -C "github-deploy-hdtickets" -f "$KEY_PATH" -N ""
    success "SSH key generated at $KEY_PATH"
fi

echo ""

# Step 2: Display public key
echo "Step 2: Public Key"
echo "------------------"
echo "Add this public key to your Digital Ocean droplet's ~/.ssh/authorized_keys:"
echo ""
cat "${KEY_PATH}.pub"
echo ""
read -p "Press Enter when you've added the public key to the droplet..."

# Step 3: Get droplet information
echo ""
echo "Step 3: Droplet Information"
echo "---------------------------"

read -p "Enter droplet IP address or hostname: " DROPLET_HOST
read -p "Enter SSH username (default: lubomir): " DROPLET_USER
DROPLET_USER=${DROPLET_USER:-lubomir}
read -p "Enter deployment path (default: /var/www/hdtickets): " DEPLOY_PATH
DEPLOY_PATH=${DEPLOY_PATH:-/var/www/hdtickets}

echo ""

# Step 4: Test SSH connection
echo "Step 4: Test SSH Connection"
echo "---------------------------"

if ssh -i "$KEY_PATH" -o ConnectTimeout=5 -o StrictHostKeyChecking=no "$DROPLET_USER@$DROPLET_HOST" "echo 'Connection successful'" 2>/dev/null; then
    success "SSH connection successful"
else
    error "SSH connection failed"
    echo "Please ensure:"
    echo "  1. The public key is added to $DROPLET_USER@$DROPLET_HOST:~/.ssh/authorized_keys"
    echo "  2. SSH is enabled on the droplet"
    echo "  3. The firewall allows SSH connections"
    exit 1
fi

echo ""

# Step 5: Test deployment path
echo "Step 5: Verify Deployment Path"
echo "-------------------------------"

if ssh -i "$KEY_PATH" "$DROPLET_USER@$DROPLET_HOST" "test -d $DEPLOY_PATH" 2>/dev/null; then
    success "Deployment path exists: $DEPLOY_PATH"
    
    # Check if it's a git repository
    if ssh -i "$KEY_PATH" "$DROPLET_USER@$DROPLET_HOST" "cd $DEPLOY_PATH && git status" 2>/dev/null; then
        success "Git repository found"
    else
        warning "Not a git repository. You may need to clone it first."
    fi
else
    error "Deployment path does not exist: $DEPLOY_PATH"
    echo "Run this on the droplet to set up:"
    echo "  sudo mkdir -p $DEPLOY_PATH"
    echo "  sudo chown -R $DROPLET_USER:$DROPLET_USER $DEPLOY_PATH"
    echo "  cd $DEPLOY_PATH && git clone https://github.com/polascin/hdtickets.git ."
    exit 1
fi

echo ""

# Step 6: Generate maintenance secret
echo "Step 6: Generate Secrets"
echo "------------------------"

MAINTENANCE_SECRET=$(openssl rand -base64 32)
success "Maintenance secret generated"

echo ""

# Step 7: Display GitHub Secrets configuration
echo "Step 7: GitHub Secrets Configuration"
echo "====================================="
echo ""
echo "Add these secrets to GitHub:"
echo "https://github.com/polascin/hdtickets/settings/secrets/actions"
echo ""
echo "Secret Name: DEPLOY_SSH_KEY"
echo "Value:"
echo "------"
cat "$KEY_PATH"
echo ""
echo "------"
echo ""
echo "Secret Name: DEPLOY_USER"
echo "Value: $DROPLET_USER"
echo ""
echo "Secret Name: DEPLOY_HOST"
echo "Value: $DROPLET_HOST"
echo ""
echo "Secret Name: DEPLOY_PATH"
echo "Value: $DEPLOY_PATH"
echo ""
echo "Secret Name: MAINTENANCE_SECRET"
echo "Value: $MAINTENANCE_SECRET"
echo ""

# Step 8: Save configuration
echo "Step 8: Save Configuration"
echo "--------------------------"

CONFIG_FILE="$HOME/.hdtickets-deploy-config"
cat > "$CONFIG_FILE" << EOF
# HD Tickets Deployment Configuration
DEPLOY_USER=$DROPLET_USER
DEPLOY_HOST=$DROPLET_HOST
DEPLOY_PATH=$DEPLOY_PATH
DEPLOY_KEY_PATH=$KEY_PATH
MAINTENANCE_SECRET=$MAINTENANCE_SECRET
EOF

success "Configuration saved to $CONFIG_FILE"

echo ""
echo "✨ Setup Complete!"
echo "=================="
echo ""
echo "Next steps:"
echo "1. Add all the secrets shown above to GitHub"
echo "2. Push to the 'main' branch to trigger automatic deployment"
echo "3. Monitor deployment at: https://github.com/polascin/hdtickets/actions"
echo ""
echo "To manually test deployment, run:"
echo "  ssh -i $KEY_PATH $DROPLET_USER@$DROPLET_HOST \"cd $DEPLOY_PATH && git pull\""
echo ""
