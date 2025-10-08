#!/bin/bash
# HD Tickets Deployment Setup Script
# Sports Events Entry Tickets Monitoring System
# Author: Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle
#
# This script coordinates the entire deployment setup process

set -euo pipefail

# Configuration
DOMAIN="hd-tickets.com"
WWW_DOMAIN="www.hd-tickets.com"
DEPLOY_USER="deploy"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Get droplet IP from DigitalOcean
get_droplet_ip() {
    if ! command -v doctl >/dev/null 2>&1; then
        log_error "doctl command not found. Please install DigitalOcean CLI."
        exit 1
    fi
    
    local ip=$(doctl compute droplet get hdtickets-production --format PublicIPv4 --no-header 2>/dev/null || echo "")
    if [[ -z "$ip" ]]; then
        log_error "Could not retrieve droplet IP. Check if hdtickets-production droplet exists."
        exit 1
    fi
    echo "$ip"
}

# Check DNS propagation
check_dns_propagation() {
    local domain=$1
    local expected_ip=$2
    
    log_step "Checking DNS propagation for $domain..."
    
    local resolved_ip=$(dig +short A "$domain" | tail -n1)
    if [[ "$resolved_ip" == "$expected_ip" ]]; then
        log_info "✅ DNS correctly points to $expected_ip"
        return 0
    else
        log_warn "⚠️  DNS for $domain resolves to '$resolved_ip', expected '$expected_ip'"
        log_warn "You may need to update DNS records in IONOS or wait for propagation"
        return 1
    fi
}

# Test SSH connection
test_ssh_connection() {
    local user=$1
    local host=$2
    
    log_step "Testing SSH connection to $user@$host..."
    
    if ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no -o BatchMode=yes "$user@$host" "echo 'SSH connection successful'" >/dev/null 2>&1; then
        log_info "✅ SSH connection to $user@$host successful"
        return 0
    else
        log_warn "⚠️  SSH connection to $user@$host failed"
        return 1
    fi
}

# Setup SSH key on droplet
setup_ssh_access() {
    local droplet_ip=$1
    
    log_step "Setting up SSH access to droplet..."
    
    # Check if we have a public key
    local pub_key_file="$HOME/.ssh/id_rsa.pub"
    if [[ ! -f "$pub_key_file" ]]; then
        pub_key_file="$HOME/.ssh/id_ed25519.pub"
    fi
    
    if [[ ! -f "$pub_key_file" ]]; then
        log_error "No SSH public key found. Please generate one with: ssh-keygen -t ed25519"
        exit 1
    fi
    
    local pub_key=$(cat "$pub_key_file")
    log_info "Using public key: $pub_key_file"
    
    # Try to add the key via DigitalOcean API
    log_step "Adding SSH key to DigitalOcean droplet via Console..."
    log_warn "Manual step required:"
    log_warn "1. Go to DigitalOcean Control Panel > Droplets > hdtickets-production"
    log_warn "2. Click 'Console' to access the web terminal"
    log_warn "3. Log in as root and run these commands:"
    echo ""
    echo "mkdir -p /root/.ssh"
    echo "echo '$pub_key' >> /root/.ssh/authorized_keys"
    echo "chmod 600 /root/.ssh/authorized_keys"
    echo "chmod 700 /root/.ssh"
    echo ""
    
    # Wait for user confirmation
    read -p "Press Enter after you've added the SSH key via the Console..."
    
    # Test the connection
    if test_ssh_connection "root" "$droplet_ip"; then
        log_info "✅ SSH access configured successfully"
        return 0
    else
        log_error "❌ SSH access still not working. Please check the key setup."
        exit 1
    fi
}

# Run server provisioning
provision_server() {
    local droplet_ip=$1
    
    log_step "Uploading and running server provisioning script..."
    
    # Upload the provisioning script
    scp "$SCRIPT_DIR/server-provision.sh" "root@$droplet_ip:/tmp/server-provision.sh"
    
    # Run the provisioning script
    log_info "Running server provisioning (this may take 10-15 minutes)..."
    ssh "root@$droplet_ip" "bash /tmp/server-provision.sh"
    
    log_info "✅ Server provisioning completed"
}

# Setup SSL certificates
setup_ssl_certificates() {
    local droplet_ip=$1
    
    log_step "Setting up Let's Encrypt SSL certificates..."
    
    # Run certbot on the server
    ssh "$DEPLOY_USER@$droplet_ip" "sudo certbot --apache -d $DOMAIN -d $WWW_DOMAIN --non-interactive --agree-tos --email lubomir@polascin.net --redirect"
    
    log_info "✅ SSL certificates configured"
}

# Create production environment file
setup_production_env() {
    local droplet_ip=$1
    
    log_step "Setting up production environment file..."
    
    # Copy template to .env if it doesn't exist
    ssh "$DEPLOY_USER@$droplet_ip" "
        if [[ ! -f /var/www/hdtickets/shared/.env ]]; then
            cp /var/www/hdtickets/shared/.env.template /var/www/hdtickets/shared/.env
            chmod 640 /var/www/hdtickets/shared/.env
            echo 'Environment file created from template'
        else
            echo 'Environment file already exists'
        fi
    "
    
    log_info "✅ Production environment configured"
    log_warn "⚠️  Please review and update /var/www/hdtickets/shared/.env on the server"
}

# Setup Horizon service
setup_horizon_service() {
    local droplet_ip=$1
    
    log_step "Setting up Horizon queue worker service..."
    
    # Upload and install the systemd service
    scp "$SCRIPT_DIR/services/hdtickets-horizon.service" "$DEPLOY_USER@$droplet_ip:/tmp/hdtickets-horizon.service"
    
    ssh "$DEPLOY_USER@$droplet_ip" "
        sudo mv /tmp/hdtickets-horizon.service /etc/systemd/system/hdtickets-horizon.service
        sudo systemctl daemon-reload
        sudo systemctl enable hdtickets-horizon
        echo 'Horizon service installed and enabled'
    "
    
    log_info "✅ Horizon service configured"
}

# Setup Laravel scheduler
setup_scheduler() {
    local droplet_ip=$1
    
    log_step "Setting up Laravel scheduler..."
    
    ssh "$DEPLOY_USER@$droplet_ip" "
        # Add cron job for Laravel scheduler
        (crontab -l 2>/dev/null; echo '* * * * * cd /var/www/hdtickets/current && /usr/bin/php8.3 artisan schedule:run >> /dev/null 2>&1') | crontab -
        echo 'Laravel scheduler configured'
    " || true
    
    log_info "✅ Laravel scheduler configured"
}

# Perform first deployment
first_deployment() {
    local droplet_ip=$1
    
    log_step "Performing first deployment with Deployer..."
    
    cd "$PROJECT_ROOT"
    
    # Test deployer configuration
    log_info "Testing Deployer configuration..."
    ~/.config/composer/vendor/bin/dep config:hosts
    
    # Run the first deployment
    log_info "Running first deployment (this may take several minutes)..."
    ~/.config/composer/vendor/bin/dep deploy production
    
    log_info "✅ First deployment completed successfully"
}

# Verify deployment
verify_deployment() {
    log_step "Verifying deployment..."
    
    # Test HTTPS website
    local response=$(curl -s -o /dev/null -w "%{http_code}" --max-time 30 "https://$DOMAIN" || echo "000")
    
    if [[ "$response" == "200" ]]; then
        log_info "✅ Website is accessible at https://$DOMAIN"
    else
        log_warn "⚠️  Website returned HTTP $response. Check server logs."
    fi
    
    # Test redirect from HTTP to HTTPS
    local redirect=$(curl -s -o /dev/null -w "%{redirect_url}" --max-time 10 "http://$DOMAIN" || echo "")
    
    if [[ "$redirect" == "https://$DOMAIN"* ]]; then
        log_info "✅ HTTP to HTTPS redirect working"
    else
        log_warn "⚠️  HTTP to HTTPS redirect may not be working properly"
    fi
}

# Main execution flow
main() {
    log_info "🚀 Starting HD Tickets deployment setup..."
    log_info "Domain: $DOMAIN"
    log_info "Deploy user: $DEPLOY_USER"
    echo ""
    
    # Get droplet IP
    DROPLET_IP=$(get_droplet_ip)
    log_info "Droplet IP: $DROPLET_IP"
    echo ""
    
    # Check DNS propagation
    if ! check_dns_propagation "$DOMAIN" "$DROPLET_IP"; then
        log_warn "DNS propagation may take time. Continuing anyway..."
    fi
    if ! check_dns_propagation "$WWW_DOMAIN" "$DROPLET_IP"; then
        log_warn "www subdomain DNS propagation may take time. Continuing anyway..."
    fi
    echo ""
    
    # Test SSH connection to root
    if ! test_ssh_connection "root" "$DROPLET_IP"; then
        setup_ssh_access "$DROPLET_IP"
    fi
    echo ""
    
    # Provision the server
    provision_server "$DROPLET_IP"
    echo ""
    
    # Wait a moment for services to settle
    log_info "Waiting for services to settle..."
    sleep 10
    
    # Test SSH connection to deploy user
    if ! test_ssh_connection "$DEPLOY_USER" "$DROPLET_IP"; then
        log_error "Cannot connect to deploy user. Check server provisioning."
        exit 1
    fi
    echo ""
    
    # Setup production environment
    setup_production_env "$DROPLET_IP"
    echo ""
    
    # Setup SSL certificates
    setup_ssl_certificates "$DROPLET_IP"
    echo ""
    
    # Setup Horizon service
    setup_horizon_service "$DROPLET_IP"
    echo ""
    
    # Setup Laravel scheduler
    setup_scheduler "$DROPLET_IP"
    echo ""
    
    # Perform first deployment
    first_deployment "$DROPLET_IP"
    echo ""
    
    # Start Horizon service
    log_step "Starting Horizon service..."
    ssh "$DEPLOY_USER@$DROPLET_IP" "sudo systemctl start hdtickets-horizon"
    log_info "✅ Horizon service started"
    echo ""
    
    # Verify deployment
    verify_deployment
    echo ""
    
    # Success message
    log_info "🎉 HD Tickets deployment setup completed successfully!"
    log_info ""
    log_info "🌐 Website: https://$DOMAIN"
    log_info "📊 Sports Events Entry Tickets Monitoring System is now live!"
    log_info ""
    log_info "Next steps:"
    log_info "1. Review and update environment variables on the server"
    log_info "2. Configure mail, Pusher/Soketi, and third-party API keys"
    log_info "3. Test the ticket monitoring and purchase workflows"
    log_info "4. Set up monitoring and backups"
    log_info ""
    log_info "Deployment commands for future updates:"
    log_info "• Deploy: ~/.config/composer/vendor/bin/dep deploy production"
    log_info "• Rollback: ~/.config/composer/vendor/bin/dep rollback production"
    log_info "• SSH to server: ssh $DEPLOY_USER@$DROPLET_IP"
    log_info ""
}

# Run main function
main "$@"