# Security Best Practices & Configuration

## 🔒 Important Security Notes

This blog platform includes several security features, but requires proper configuration for production use.

## Critical Security Steps Before Deployment

### 1. Database Credentials

**Current Status**: Database credentials are in `config.php` for easy setup.

**For Production**:

Option A: Use Environment Variables (Recommended)
```php
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_NAME', getenv('DB_NAME'));
```

Option B: Use Separate Config File
1. Copy `config.template.php` to `config.php`
2. Update credentials in `config.php`
3. Add `config.php` to `.gitignore`
4. Never commit actual credentials

### 2. JWT Secret Key

**Action Required**: Change the JWT secret in `config.php`

Generate a secure random key:
```bash
openssl rand -base64 32
```

Update in `config.php`:
```php
define('JWT_SECRET', 'your_generated_random_key_here');
```

### 3. CORS Configuration

**Current Status**: Allows all origins (*) for development

**For Production**: Restrict to your GitHub Pages URL

Edit `config.php`:
```php
$allowedOrigins = [
    'https://kychugo.github.io',  // Your GitHub Pages URL
];

// Remove the else block that allows all origins
```

### 4. Password Requirements

**Current Setting**: Minimum 8 characters

**Recommendations**:
- Consider 12+ characters for better security
- Add complexity requirements (uppercase, lowercase, numbers, symbols)
- Implement password strength meter on frontend

To increase minimum length, edit `backend/auth.php`:
```php
if (strlen($password) < 12) {
    sendError('Password must be at least 12 characters');
    exit();
}
```

### 5. HTML Content Sanitization

**Current Status**: Allows safe HTML tags in posts

**Allowed Tags**: `<p><br><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><code><pre><a>`

**To Further Restrict**: Edit `backend/posts.php` and reduce allowed tags

**To Allow More**: Add tags to the `strip_tags()` second parameter

### 6. Database Security

**Recommendations**:

1. **Create Read-Only User for Analytics**:
```sql
CREATE USER 'blog_readonly'@'%' IDENTIFIED BY 'secure_password';
GRANT SELECT ON if0_39929369_blog.view_logs TO 'blog_readonly'@'%';
GRANT SELECT ON if0_39929369_blog.posts TO 'blog_readonly'@'%';
```

2. **Regular Backups**:
```bash
# Daily backup script
mysqldump -h sql201.infinityfree.com -u if0_39929369 -p if0_39929369_blog > backup_$(date +%Y%m%d).sql
```

3. **Monitor Failed Login Attempts**:
Consider implementing rate limiting for login attempts.

### 7. API Rate Limiting

**Not Currently Implemented**

**Recommended Implementation**:
```php
// Add to config.php
function checkRateLimit($identifier, $maxRequests = 100, $timeWindow = 60) {
    // Implement rate limiting logic
    // Store in database or Redis
    // Return true if allowed, false if exceeded
}
```

### 8. HTTPS Only

**Critical**: Ensure your server enforces HTTPS

Add to `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 9. Security Headers

Add to `.htaccess`:
```apache
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"
Header set Content-Security-Policy "default-src 'self'"
```

### 10. File Upload (Future Feature)

If you add file upload functionality:
- Validate file types (whitelist approach)
- Check file size limits
- Store files outside web root
- Generate random filenames
- Scan for malware
- Validate image dimensions

## Security Features Already Implemented ✅

### Authentication
- ✅ Password hashing with bcrypt (cost factor 10)
- ✅ Token-based authentication (30-day expiry)
- ✅ Token signature verification
- ✅ Email validation

### Input Protection
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention (input sanitization)
- ✅ HTML content sanitization (allowed tags only)
- ✅ Output encoding

### Access Control
- ✅ Role-based access control (admin/user)
- ✅ Authorization checks on protected endpoints
- ✅ User can only delete own comments
- ✅ Admin-only post management

### Session Security
- ✅ Secure token generation
- ✅ Token expiration
- ✅ Logout functionality

## Security Checklist

Before going live, verify:

- [ ] Database credentials are secure and not in version control
- [ ] JWT secret key is changed and secure
- [ ] CORS is restricted to your domain only
- [ ] HTTPS is enforced
- [ ] Security headers are configured
- [ ] Password requirements are strong enough
- [ ] Admin account has strong password
- [ ] Database is backed up regularly
- [ ] File permissions are correct (644 for PHP files)
- [ ] `.htaccess` is uploaded and working
- [ ] Error reporting is disabled in production
- [ ] Database error messages don't leak information
- [ ] Rate limiting is considered (future)
- [ ] Monitoring is in place (future)

## Monitoring & Maintenance

### Regular Tasks

1. **Review View Logs**: Check for suspicious patterns
2. **Monitor Users**: Remove spam accounts
3. **Check Comments**: Moderate inappropriate content
4. **Update Dependencies**: Keep PHP and MySQL updated
5. **Review Access Logs**: Look for attack patterns
6. **Test Backups**: Ensure backups are working

### Incident Response

If you suspect a security breach:

1. **Immediate Actions**:
   - Change JWT secret key
   - Force logout all users (change secret)
   - Review recent posts and comments
   - Check view logs for suspicious activity
   - Review user accounts for unauthorized access

2. **Investigation**:
   - Check server access logs
   - Review database for unauthorized changes
   - Check for SQL injection attempts
   - Look for XSS payloads in content

3. **Recovery**:
   - Restore from backup if needed
   - Patch vulnerability
   - Update passwords
   - Notify users if data was compromised

## Additional Recommendations

### For High-Traffic Sites

1. **Implement Caching**:
   - Cache GET requests for posts
   - Use Redis or Memcached
   - Set appropriate cache headers

2. **Database Optimization**:
   - Add more indexes
   - Implement query caching
   - Consider read replicas

3. **CDN**:
   - Use CloudFlare or similar
   - Protect against DDoS
   - Improve load times

4. **WAF (Web Application Firewall)**:
   - ModSecurity
   - CloudFlare WAF
   - AWS WAF

### For Sensitive Content

1. **Two-Factor Authentication**:
   - Add TOTP support
   - SMS verification
   - Backup codes

2. **Audit Logging**:
   - Log all admin actions
   - Track content changes
   - Monitor login attempts

3. **Content Moderation**:
   - Implement approval workflow
   - Flag system for users
   - Automated spam detection

## Vulnerability Reporting

If you discover a security vulnerability:

1. **Do Not** post it publicly
2. **Do** report it privately to the admin
3. **Include** steps to reproduce
4. **Wait** for confirmation before disclosure

## Stay Updated

Security is an ongoing process. Stay informed about:

- PHP security updates
- MySQL security patches
- Common web vulnerabilities (OWASP Top 10)
- Best practices in web security

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [MySQL Security Best Practices](https://dev.mysql.com/doc/refman/8.0/en/security-guidelines.html)
- [Web Security Academy](https://portswigger.net/web-security)

---

**Remember**: Security is not a one-time task but an ongoing commitment. Review and update your security measures regularly.
