#!/bin/bash

# HD Tickets Let's Encrypt Setup Script
# Sports Events Entry Tickets Monitoring System - Production SSL Setup
# Author: Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle

set -e

# Configuration variables
DOMAIN="hdtickets.local"  # Change this to your production domain
EMAIL="admin@hdtickets.com"  # Change this to your admin email
WEBROOT="/var/www/hdtickets/public"
CERT_NAME="hdtickets"

echo "=================================="
echo "HD Tickets Let's Encrypt Setup"
echo "=================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root or with sudo
if [[ $EUID -ne 0 ]]; then
   print_error "This script must be run as root or with sudo"
   exit 1
fi

# Production domain validation
if [[ "$DOMAIN" == "hdtickets.local" ]]; then
    print_warning "This appears to be a local development setup."
    print_warning "For production, update DOMAIN variable to your actual domain name."
    echo ""
    read -p "Do you want to continue with the local setup simulation? (y/N): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_status "Exiting. Update the DOMAIN variable for production use."
        exit 0
    fi
    
    print_warning "Continuing with simulation mode for local development..."
    SIMULATION_MODE=true
else
    SIMULATION_MODE=false
fi

# Step 1: Backup current SSL configuration
print_status "Backing up current SSL configuration..."
BACKUP_DIR="/var/backups/ssl-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"

if [ -f "/etc/apache2/sites-available/hdtickets-ssl.conf" ]; then
    cp /etc/apache2/sites-available/hdtickets-ssl.conf "$BACKUP_DIR/"
    print_status "Apache configuration backed up to $BACKUP_DIR"
fi

if [ -d "/etc/ssl/hdtickets" ]; then
    cp -r /etc/ssl/hdtickets "$BACKUP_DIR/"
    print_status "Current certificates backed up to $BACKUP_DIR"
fi

# Step 2: Test Apache configuration
print_status "Testing Apache configuration..."
if ! apache2ctl configtest; then
    print_error "Apache configuration test failed. Please fix and retry."
    exit 1
fi

# Step 3: Stop Apache temporarily for standalone mode (if needed)
print_status "Preparing for certificate generation..."

if [ "$SIMULATION_MODE" = true ]; then
    print_warning "SIMULATION MODE: Skipping actual Let's Encrypt certificate generation"
    print_status "In production, this would run:"
    echo "  certbot --apache -d $DOMAIN --email $EMAIL --agree-tos --non-interactive"
    
    # Create simulated Let's Encrypt directory structure
    mkdir -p "/etc/letsencrypt/live/$DOMAIN"
    mkdir -p "/etc/letsencrypt/archive/$DOMAIN"
    
    # Copy current certificates as simulation
    cp "/etc/ssl/hdtickets/hdtickets.local.crt" "/etc/letsencrypt/live/$DOMAIN/fullchain.pem"
    cp "/etc/ssl/hdtickets/hdtickets.local.key" "/etc/letsencrypt/live/$DOMAIN/privkey.pem"
    
    print_status "Simulated Let's Encrypt directory structure created"
    
else
    # Step 4: Obtain Let's Encrypt certificate (PRODUCTION)
    print_status "Obtaining Let's Encrypt certificate for $DOMAIN..."
    
    # Use Apache plugin for automatic configuration
    certbot --apache \
        -d "$DOMAIN" \
        -d "www.$DOMAIN" \
        --email "$EMAIL" \
        --agree-tos \
        --non-interactive \
        --redirect \
        --cert-name "$CERT_NAME"
        
    if [ $? -eq 0 ]; then
        print_status "Let's Encrypt certificate obtained successfully!"
    else
        print_error "Failed to obtain Let's Encrypt certificate"
        exit 1
    fi
fi

print_status "Let's Encrypt setup completed!"
echo ""
echo "Next steps:"
echo "1. Update Apache configuration for OCSP stapling"
echo "2. Set up automated renewal"
echo "3. Configure certificate monitoring"
echo ""
