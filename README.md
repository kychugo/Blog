# Hugo 開發日誌 | Hugo Development Log

A modern, feature-rich blog platform with **consolidated 2 PHP files + 1 HTML file** architecture.

> **"if you only know how to use a hammer, everything looks like a nail"**

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

## 🏗️ Simplified Architecture

**Consolidated Structure (2 PHP + 1 HTML):**

```
Blog/
├── api.php          # All-in-one API endpoint handler (29KB)
├── init.php         # Database initialization with embedded SQL (8KB)
├── index.html       # Complete frontend application (54KB)
└── .htaccess        # URL rewriting configuration
```

### File Breakdown

#### api.php (Backend)
- All authentication endpoints (register, login, verify)
- All post CRUD operations
- Comments management
- Likes system
- Tags system
- Admin panel endpoints
- Database connection & security helpers
- JWT-like token authentication

#### init.php (Database Setup)
- Embedded SQL schema for all 7 tables
- Automatic table creation
- Database verification
- Setup status reporting
- **Run once** to initialize the database

#### index.html (Frontend)
- Complete single-page application
- All UI components and styling
- Client-side routing
- Bilingual support (繁體中文/English)
- Responsive design
- No external dependencies

## 🚀 Quick Start

### Step 1: Database Setup

1. **Upload files to server**:
   - Upload `api.php`, `init.php`, `index.html`, and `.htaccess` to `hugow.wuaze.com`

2. **Initialize database**:
   - Visit: `https://hugow.wuaze.com/init.php`
   - This will create all required database tables
   - You should see success messages for all 7 tables

3. **Verify setup**:
   - Visit: `https://hugow.wuaze.com/api.php`
   - You should see the API documentation in JSON format

### Step 2: Start Using

1. **Open the blog**:
   - Visit: `https://hugow.wuaze.com/index.html`
   - Or directly: `https://hugow.wuaze.com/`

2. **Register first account**:
   - Click "登入 / Login" button
   - Switch to Register tab
   - Register with email and password
   - First user automatically becomes Admin!

3. **Create your first post**:
   - Click "新增文章 / Create Post"
   - Write title and content
   - Add tags (comma-separated)
   - Click "發佈 / Publish"

## 📖 Alternative Deployment

The `/backend/` and `/frontend/` folders contain the original modular structure for reference. The consolidated files in the root are production-ready.

## 🔐 Database Configuration

**IMPORTANT NOTE**: The database credentials in this repository are for a free InfinityFree hosting account provided specifically for this project. In a production environment with sensitive data, you should:

1. Use environment variables for credentials
2. Never commit credentials to version control
3. Use the provided `backend/config.template.php` as a guide

**Current Configuration** (for this demo project):
```
Host: sql201.infinityfree.com
Port: 3306
Database: if0_39929369_blog
Username: if0_39929369
Password: hfy23whc
```

For better security practices, see [SECURITY.md](SECURITY.md).

## 📋 API Endpoints

All endpoints are handled through `api.php`. The API automatically routes requests based on the URL path.

**Base URL**: `https://hugow.wuaze.com/api.php`

### Authentication
- `POST /auth/register` - Register new user (first becomes admin)
- `POST /auth/login` - Login with email/password
- `GET /auth/verify` - Verify authentication token

### Posts
- `GET /posts` - List all posts (supports filters: tag, year, month, search)
- `GET /posts?id={id}` - Get single post
- `POST /posts` - Create post (admin only)
- `PUT /posts` - Update post (admin only)
- `DELETE /posts?id={id}` - Delete post (admin only)

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

## 💾 Database Schema

The database schema is embedded in `init.php` and includes 7 tables:

1. **users** - User accounts with admin flag
2. **posts** - Blog posts with metadata
3. **tags** - Tag definitions
4. **post_tags** - Post-tag relationships (junction table)
5. **comments** - User comments on posts
6. **likes** - Post like tracking
7. **view_logs** - Post view analytics

All tables are automatically created when you run `init.php` for the first time.

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