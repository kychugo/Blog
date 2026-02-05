# Hugo 開發日誌 - Complete Deployment Guide

## Prerequisites

1. ✅ GitHub account
2. ✅ Access to PHP web server (hugow.wuaze.com)
3. ✅ MySQL database credentials (provided)

## Step-by-Step Deployment

### Part 1: Backend Deployment (PHP Server)

#### 1.1 Prepare Database

**Note**: The database credentials for this project are already configured in `config.php`. In production, you should secure these credentials using environment variables (see SECURITY.md).

1. Access your MySQL database using phpMyAdmin or command line:
   ```bash
   mysql -h sql201.infinityfree.com -u if0_39929369 -p if0_39929369_blog
   # Password: hfy23whc (from config.php)
   ```

2. Import the database schema:
   ```bash
   mysql -h sql201.infinityfree.com -u if0_39929369 -p if0_39929369_blog < backend/database.sql
   ```

3. Verify tables are created:
   ```sql
   SHOW TABLES;
   -- You should see: users, posts, tags, post_tags, comments, likes, view_logs
   ```

#### 1.2 Upload Backend Files

1. Connect to your PHP server (hugow.wuaze.com) via FTP/SFTP or file manager

2. Upload these files to the web root (e.g., `public_html/` or `www/`):
   ```
   backend/
   ├── .htaccess
   ├── index.php
   ├── config.php
   ├── auth.php
   ├── posts.php
   ├── comments.php
   ├── likes.php
   ├── tags.php
   └── admin.php
   ```

3. Ensure file permissions are correct:
   - PHP files: 644
   - .htaccess: 644

#### 1.3 Test Backend API

1. Open your browser and visit: `https://hugow.wuaze.com/`

2. You should see JSON output with API documentation

3. Test the API is working:
   ```bash
   curl https://hugow.wuaze.com/
   ```

### Part 2: Frontend Deployment (GitHub Pages)

#### 2.1 Enable GitHub Pages

1. Go to your repository on GitHub: `https://github.com/kychugo/Blog`

2. Click **Settings** → **Pages**

3. Under "Source", select:
   - Branch: `main` (or your default branch)
   - Folder: `/root` or `/` (root directory)

4. Click **Save**

5. Wait 1-2 minutes for GitHub Pages to build

#### 2.2 Verify Frontend URL

Your site will be available at:
```
https://kychugo.github.io/Blog/frontend/
```

Or if using custom domain:
```
https://yourdomain.com/frontend/
```

#### 2.3 Test Frontend

1. Open the URL in your browser

2. You should see the Hugo Development Log homepage with:
   - Navigation bar with logo
   - Inspirational quote
   - Search and filter controls
   - Empty posts grid (no posts yet)

### Part 3: Initial Setup

#### 3.1 Register First Admin User

1. Visit your frontend URL

2. Click **登入** (Login) button

3. Click **還沒有帳號？註冊** (Don't have account? Register)

4. Fill in:
   - Username: Your name
   - Email: Your email
   - Password: Strong password (min 6 characters)

5. Click **註冊** (Register)

6. **Important**: You are now the admin! (First registered user)

#### 3.2 Create Your First Post

1. After logging in, you'll see new buttons:
   - **管理** (Admin)
   - **發布文章** (Create Post)

2. Click **發布文章** (Create Post)

3. Fill in:
   - Title: "Welcome to Hugo Dev Log!"
   - Content: Your first post content
   - Tags: "welcome, first-post, announcement"

4. Click **發布** (Publish)

5. Your post will appear on the homepage!

#### 3.3 Test All Features

1. **View Post**: Click on the post card
2. **Like Post**: Click the ❤️ icon (must be logged in)
3. **Comment**: Write a comment at the bottom
4. **Search**: Try searching for keywords
5. **Filter by Tag**: Click on any tag
6. **Admin Panel**: Click **管理** to see statistics

### Part 4: Customization (Optional)

#### 4.1 Change API URL

If your PHP server URL changes, edit `frontend/index.html`:

```javascript
// Line ~450
const API_URL = 'https://hugow.wuaze.com'; // Change this
```

#### 4.2 Change JWT Secret

For better security, edit `backend/config.php`:

```php
define('JWT_SECRET', 'your-very-secret-key-here-change-this');
```

#### 4.3 Customize Colors

Edit CSS variables in `frontend/index.html`:

```css
:root {
    --primary: #00ff88;    /* Change primary color */
    --secondary: #0066ff;  /* Change secondary color */
    --dark: #0a0e27;       /* Change background */
}
```

## Troubleshooting

### Backend Issues

**Problem**: "Database connection failed"
- **Solution**: Check database credentials in `config.php`
- Verify MySQL server is accessible

**Problem**: "404 Not Found" on API endpoints
- **Solution**: Check `.htaccess` is uploaded and enabled
- Verify mod_rewrite is enabled on server

**Problem**: CORS errors
- **Solution**: Verify CORS headers in `config.php`
- Check server allows `.htaccess` overrides

### Frontend Issues

**Problem**: "Connection error" when loading posts
- **Solution**: Check `API_URL` in `index.html` matches your PHP server
- Open browser console (F12) to see detailed error

**Problem**: GitHub Pages not updating
- **Solution**: Wait 2-3 minutes after pushing changes
- Try clearing browser cache
- Check GitHub Actions tab for build status

**Problem**: Page looks broken
- **Solution**: Ensure `index.html` is in the correct path
- Check browser console for JavaScript errors

## Maintenance

### Regular Tasks

1. **Backup Database**:
   ```bash
   mysqldump -h sql201.infinityfree.com -u if0_39929369 -p if0_39929369_blog > backup.sql
   ```

2. **Monitor Logs**: Check view logs in admin panel regularly

3. **Manage Users**: Remove spam accounts via admin panel

4. **Update Content**: Keep posts fresh and engaging

### Security Best Practices

1. ✅ Use strong passwords
2. ✅ Keep admin accounts limited
3. ✅ Regularly review user accounts
4. ✅ Monitor view logs for suspicious activity
5. ✅ Keep database credentials secure

## Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review browser console for errors (F12)
3. Check server error logs
4. Review database connection settings

## Success Checklist

- [ ] Database tables created
- [ ] Backend API responding at hugow.wuaze.com
- [ ] Frontend accessible on GitHub Pages
- [ ] First admin account registered
- [ ] First post created successfully
- [ ] Can like and comment on posts
- [ ] Search and filters working
- [ ] Admin panel accessible
- [ ] Bilingual switching works

## Congratulations! 🎉

Your Hugo Development Log is now live and ready to use!

Start sharing your development journey with the world! 🚀
