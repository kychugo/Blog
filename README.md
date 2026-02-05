# Hugo 開發日誌 | Hugo Development Log

A modern, feature-rich blog platform with PHP backend and HTML frontend.

## 🌟 Features

### Core Functionality
- ✅ **User Authentication**: Email-based login system
- ✅ **Auto Admin**: First registered user becomes admin automatically
- ✅ **Post Management**: Create, edit, delete posts (admin only)
- ✅ **Comments System**: Users can comment on posts
- ✅ **Likes System**: Heart/like posts
- ✅ **Tags**: Organize posts with tags
- ✅ **Filtering**: Group by tags, year, and month
- ✅ **Search**: Full-text search across titles and content
- ✅ **View Tracking**: Admin can see post viewing logs
- ✅ **Bilingual**: Switch between 繁體中文 and English

### Admin Capabilities
- View all statistics (users, posts, views, likes, comments)
- Manage users (delete accounts)
- Create, edit, and delete posts
- View all post viewing logs
- See most viewed and most liked posts

### Design
- 🎨 Modern tech-inspired design with dark theme
- 💫 Animated background effects
- 📱 Fully responsive
- ⚡ Fast and lightweight
- 🎯 Smooth animations and transitions

## 🏗️ Architecture

This project consists of two separate parts:

### Backend (PHP)
- **Location**: `/backend/`
- **Hosting**: PHP web server at `hugow.wuaze.com`
- **Database**: MySQL on InfinityFree
- **Features**: RESTful API, authentication, CRUD operations

### Frontend (HTML)
- **Location**: `/frontend/`
- **Hosting**: GitHub Pages
- **Technology**: Single HTML file with embedded CSS/JS
- **Features**: SPA, responsive design, bilingual support

## 🚀 Quick Start

### Backend Setup

1. **Import database schema**:
   ```bash
   cd backend
   mysql -h sql201.infinityfree.com -u if0_39929369 -p if0_39929369_blog < database.sql
   # Password: hfy23whc
   ```

2. **Upload to PHP server**:
   - Upload all files in `/backend/` to `hugow.wuaze.com`
   - Ensure `.htaccess` is uploaded for URL rewriting

3. **Test the API**:
   - Visit: https://hugow.wuaze.com/
   - You should see API documentation

### Frontend Setup

1. **Deploy to GitHub Pages**:
   - Push the repository to GitHub
   - Enable GitHub Pages in repository settings
   - Select the appropriate branch

2. **Access your blog**:
   - Visit: `https://{username}.github.io/{repo-name}/frontend/`

3. **First User Registration**:
   - Register the first account - it will automatically become admin
   - Start creating posts!

## 📖 Documentation

- [Backend Documentation](backend/README.md)
- [Frontend Documentation](frontend/README.md)

## 🔐 Database Configuration

```
Host: sql201.infinityfree.com
Port: 3306
Database: if0_39929369_blog
Username: if0_39929369
Password: hfy23whc
```

## 📋 API Endpoints

### Authentication
- `POST /auth/register` - Register new user
- `POST /auth/login` - Login with email/password
- `GET /auth/verify` - Verify authentication token

### Posts
- `GET /posts` - List all posts (with filters)
- `GET /posts?id={id}` - Get single post
- `POST /posts` - Create post (admin)
- `PUT /posts` - Update post (admin)
- `DELETE /posts?id={id}` - Delete post (admin)

### Comments
- `GET /comments?post_id={id}` - Get post comments
- `POST /comments` - Add comment (authenticated)
- `DELETE /comments?id={id}` - Delete comment (owner/admin)

### Likes
- `GET /likes?post_id={id}` - Get like status
- `POST /likes` - Toggle like (authenticated)

### Tags
- `GET /tags` - Get all tags with post counts

### Admin
- `GET /admin/users` - List all users
- `DELETE /admin/users?id={id}` - Delete user
- `GET /admin/view-logs` - View access logs
- `GET /admin/stats` - Get platform statistics

## 🎯 Key Features Explained

### Auto-Admin System
The first person to register on the platform automatically becomes an admin with full privileges.

### View Tracking
Every time a post is viewed, it's logged with:
- Post ID
- User ID (if logged in)
- IP address
- Timestamp

Admins can view all these logs in the admin dashboard.

### Bilingual Support
The entire interface supports switching between:
- 繁體中文 (Traditional Chinese)
- English

Click the language toggle in the navigation bar to switch.

### Search & Filtering
- **Search**: Full-text search across post titles and content
- **Filter by Tag**: Click any tag to see posts with that tag
- **Filter by Date**: Filter by year and/or month
- **Combine Filters**: Use multiple filters simultaneously

## 💬 Inspirational Quote

The platform features the quote:

> **"if you only know how to use a hammer, everything looks like a nail"**

This reminds us to expand our knowledge and tools to approach problems with diverse perspectives.

## 🛠️ Technology Stack

### Backend
- PHP 7.4+
- MySQL 5.7+
- PDO for database access
- Custom JWT-like authentication
- RESTful API architecture

### Frontend
- Pure HTML5
- CSS3 with animations
- Vanilla JavaScript (ES6+)
- No frameworks or dependencies
- Responsive design

## 🔒 Security Features

- Password hashing with bcrypt
- Token-based authentication
- SQL injection protection (prepared statements)
- XSS protection (input sanitization)
- CORS configuration for secure cross-origin requests
- Admin-only endpoint protection

## 📱 Responsive Design

The platform works perfectly on:
- 💻 Desktop computers
- 📱 Mobile phones
- 📱 Tablets
- 🖥️ Large displays

## 🤝 Contributing

This is a complete, production-ready blog platform. Feel free to:
- Fork the repository
- Customize the design
- Add new features
- Report issues

## 📄 License

This project is open source and available for personal and commercial use.

## 👨‍💻 Author

Created for Hugo's Development Log

## 🙏 Acknowledgments

Built with modern web technologies and best practices, this platform demonstrates:
- Clean architecture
- Security-first approach
- User-friendly interface
- Bilingual support
- Admin capabilities
- Modern design trends