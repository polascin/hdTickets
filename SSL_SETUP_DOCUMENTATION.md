# HD Tickets SSL Configuration Documentation

## Overview

This document describes the complete SSL/TLS configuration for the HD Tickets Sports Events Entry Tickets Monitoring System. The setup provides secure HTTPS connections with modern security headers and proper HSTS implementation.

## Architecture

```
Client → Apache (Port 443) → Laravel Application
   ↑           ↑                      ↑
   │           │                      │
HTTPS      SSL/TLS               Secure Headers
```

## SSL Certificate Configuration

### Development Certificate (Self-Signed)
- **Location**: `/etc/ssl/hdtickets/hdtickets.local.crt`
- **Private Key**: `/etc/ssl/hdtickets/hdtickets.local.key`
- **Validity**: 365 days from creation
- **Subject**: `CN=hdtickets.local, O=HD Tickets, C=SK`

### Production Certificate Requirements
For production deployment, replace the self-signed certificate with:
- **Let's Encrypt** (Recommended for cost-effectiveness)
- **Commercial SSL Certificate** (For extended validation)

## Apache SSL Configuration

### Virtual Hosts
1. **HTTP Virtual Host (Port 80)**
   - Redirects all traffic to HTTPS (301 permanent redirect)
   - Basic security headers applied before redirect

2. **HTTPS Virtual Host (Port 443)**
   - Full SSL/TLS configuration
   - Modern security headers
   - Laravel application serving

### Security Features
- **TLS Protocol**: TLSv1.2 and TLSv1.3 only
- **Cipher Suites**: Modern ECDHE and GCM ciphers
- **HSTS**: 2-year max-age with includeSubDomains and preload
- **Server Information Hiding**: Server tokens and version hidden

## Security Headers Implemented

### Laravel SecurityHeadersMiddleware
```php
'headers' => [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Permissions-Policy' => 'geolocation=(), microphone=(), camera=(), payment=(), usb=()',
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
]
```

### Content Security Policy
```
default-src 'self';
script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com;
style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com https://fonts.googleapis.com;
[... additional directives ...]
upgrade-insecure-requests
```

## File Structure

```
/var/www/hdtickets/
├── ssl-status.sh                    # SSL monitoring script
├── hdtickets-ssl.conf              # Apache virtual host configuration
└── SSL_SETUP_DOCUMENTATION.md     # This documentation

/etc/ssl/hdtickets/
├── hdtickets.local.crt             # SSL certificate
└── hdtickets.local.key             # Private key (600 permissions)

/etc/apache2/
├── sites-available/
│   └── hdtickets-ssl.conf          # Virtual host configuration
└── sites-enabled/
    └── hdtickets-ssl.conf          # Symlink to available config
```

## Configuration Details

### Laravel Environment
```env
APP_URL=https://hdtickets.local
```

### Security Configuration
```php
'csp' => [
    'upgrade-insecure-requests' => true,  # Enabled for HTTPS
    // ... other CSP directives
],
```

## Monitoring and Maintenance

### Log Files
- **SSL Error Log**: `/var/log/apache2/hdtickets-ssl-error.log`
- **SSL Access Log**: `/var/log/apache2/hdtickets-ssl-access.log`
- **SSL Security Log**: `/var/log/apache2/hdtickets-ssl-security.log`
- **Laravel Log**: `/var/www/hdtickets/storage/logs/laravel.log`

### SSL Status Check Script
Run the monitoring script regularly:
```bash
/var/www/hdtickets/ssl-status.sh
```

### Certificate Monitoring
- **Expiry**: Certificate expires on August 12, 2026
- **Renewal**: Set up automated renewal 30 days before expiry
- **Validation**: Regular SSL Labs testing recommended

## Testing and Validation

### Manual Testing Commands
```bash
# Test HTTPS connection
curl -k -I https://hdtickets.local/

# Test HTTP redirect
curl -I http://hdtickets.local/

# Check certificate details
openssl s_client -connect hdtickets.local:443 -servername hdtickets.local

# Verify cipher suites
nmap --script ssl-enum-ciphers hdtickets.local
```

### Expected Headers
```
Strict-Transport-Security: max-age=63072000; includeSubDomains; preload
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' ...
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()
```

## Production Deployment Checklist

### Pre-Deployment
- [ ] Obtain valid SSL certificate from trusted CA
- [ ] Update certificate paths in Apache configuration
- [ ] Test certificate chain and validation
- [ ] Configure OCSP stapling (if supported)
- [ ] Update firewall rules for HTTPS traffic

### Security Hardening
- [ ] Disable weak SSL/TLS protocols (SSLv2, SSLv3, TLSv1.0, TLSv1.1)
- [ ] Configure strong cipher suites only
- [ ] Enable HTTP/2 for performance
- [ ] Implement certificate pinning
- [ ] Set up certificate transparency monitoring

### Monitoring
- [ ] Configure certificate expiry alerts
- [ ] Set up SSL Labs monitoring
- [ ] Implement security header validation
- [ ] Configure log analysis and alerting
- [ ] Set up performance monitoring

## Troubleshooting

### Common Issues

#### Certificate Trust Issues
- **Problem**: Browser shows certificate warnings
- **Solution**: For production, use a certificate from a trusted CA

#### Mixed Content Warnings
- **Problem**: HTTP resources loaded over HTTPS
- **Solution**: Update all resource URLs to HTTPS or relative paths

#### Performance Issues
- **Problem**: Slow SSL handshake
- **Solution**: Enable SSL session caching and HTTP/2

### Debug Commands
```bash
# Check Apache configuration
sudo apache2ctl configtest

# Verify SSL module
apache2ctl -M | grep ssl

# Test certificate validation
openssl verify /etc/ssl/hdtickets/hdtickets.local.crt

# Check cipher compatibility
openssl ciphers -v 'ECDHE-RSA-AES128-GCM-SHA256'
```

## Security Considerations

### Current Security Level: HIGH
- ✅ Strong encryption (AES-GCM)
- ✅ Perfect Forward Secrecy (ECDHE)
- ✅ HSTS with preload
- ✅ Comprehensive security headers
- ✅ Secure cookie settings
- ✅ Content Security Policy

### Future Enhancements
1. **Certificate Authority Authorization (CAA)** DNS records
2. **HTTP Public Key Pinning (HPKP)** - deprecated, use Certificate Transparency
3. **DNS-based Authentication of Named Entities (DANE)**
4. **Certificate Transparency** monitoring
5. **Automated certificate renewal** with Let's Encrypt

## Support and Maintenance

### Regular Tasks
- **Weekly**: Check SSL status script results
- **Monthly**: Review security logs for anomalies
- **Quarterly**: Update cipher suites and security headers
- **Bi-annually**: Security assessment and penetration testing

### Emergency Procedures
1. **Certificate Compromise**: Immediate revocation and replacement
2. **Security Vulnerability**: Apply patches and update configuration
3. **Performance Issues**: Review and optimize SSL settings

## References

- [Mozilla SSL Configuration Generator](https://ssl-config.mozilla.org/)
- [OWASP Transport Layer Protection Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Transport_Layer_Protection_Cheat_Sheet.html)
- [SSL Labs Server Test](https://www.ssllabs.com/ssltest/)
- [Apache SSL/TLS Strong Encryption](https://httpd.apache.org/docs/2.4/ssl/ssl_howto.html)

---

**Document Version**: 1.0  
**Last Updated**: August 12, 2025  
**Author**: Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle  
**Environment**: Ubuntu 24.04 LTS, Apache2, PHP8.4, Laravel 12
