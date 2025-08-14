# HD Tickets - SSL Setup Quick Guide

## New Developer Setup (5 minutes)

### 1. Install mkcert
```bash
curl -JLO "https://dl.filippo.io/mkcert/latest?for=linux/amd64"
chmod +x mkcert-v*-linux-amd64
sudo mv mkcert-v*-linux-amd64 /usr/local/bin/mkcert
```

### 2. Setup certificates
```bash
mkcert -install
cd /etc/ssl/hdtickets/mkcert
sudo mkcert localhost 127.0.0.1 hdtickets.local *.hdtickets.local
sudo chown www-data:www-data localhost+*.pem localhost+*-key.pem
sudo chmod 644 localhost+*.pem
sudo chmod 600 localhost+*-key.pem
sudo systemctl restart apache2
```

### 3. Access HD Tickets
- **HTTP**: http://hdtickets.local
- **HTTPS**: https://hdtickets.local (now trusted!)

## Troubleshooting
- **Certificate not trusted**: Run `mkcert -install`
- **Permission errors**: Check www-data ownership with `ls -la /etc/ssl/hdtickets/mkcert/`
- **Apache errors**: Check logs with `sudo tail -f /var/log/apache2/error.log`

## Team CA Sharing
```bash
# Share CA certificate with team
mkcert -CAROOT  # Shows CA location
cp "$(mkcert -CAROOT)/rootCA.pem" ~/hdtickets-ca.pem
# Send hdtickets-ca.pem to team members

# Team members install shared CA
mkcert -install ~/hdtickets-ca.pem
```

## Complete Documentation
- **Full Guide**: `/etc/ssl/hdtickets/mkcert/README.md`
- **Project README**: `/var/www/hdtickets/README.md` (SSL section)

---
*HD Tickets SSL Setup - Sports Events Entry Tickets System*
