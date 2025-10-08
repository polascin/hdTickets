#!/bin/bash
# Minimal SSH Setup for HD Tickets Deployment
# Run this in DigitalOcean Console as root to enable SSH access

echo "🔧 Setting up SSH access for HD Tickets deployment..."

# Update and install SSH
apt-get update -qq
apt-get install -y openssh-server net-tools

# Enable and start SSH service
systemctl enable ssh
systemctl start ssh

# Configure SSH keys for root
mkdir -p /root/.ssh
chmod 700 /root/.ssh
echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKPInr2Qy1Z+3JAF+Irn2KNHccQCpi015Juqf34EL8Qq lubomir@polascin.net' > /root/.ssh/authorized_keys
chmod 600 /root/.ssh/authorized_keys

# Create deploy user
useradd -m -s /bin/bash deploy
usermod -aG sudo deploy

# Configure SSH keys for deploy user
mkdir -p /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKPInr2Qy1Z+3JAF+Irn2KNHccQCpi015Juqf34EL8Qq lubomir@polascin.net' > /home/deploy/.ssh/authorized_keys
chmod 600 /home/deploy/.ssh/authorized_keys
chown -R deploy:deploy /home/deploy/.ssh

# Configure basic firewall
apt-get install -y ufw
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow http
ufw allow https
ufw --force enable

# Test SSH service
echo "SSH service status:"
systemctl status ssh --no-pager --lines=3

echo "SSH listening on:"
netstat -tlnp | grep :22

echo "🔑 SSH setup completed!"
echo "Test connection from local machine with:"
echo "ssh deploy@YOUR_DROPLET_IP"
echo "ssh root@YOUR_DROPLET_IP"