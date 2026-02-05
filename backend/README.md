# Hugo 開發日誌 Backend Setup

Backend API for Hugo Development Log blog platform.

## Features

- User authentication (email/password)
- First user becomes admin automatically
- Post management (CRUD)
- Comments and likes system
- Tags and filtering
- View logs tracking
- Admin panel with statistics

## Setup Instructions

**Security Note**: This README shows the database credentials that are configured in `config.php`. For production deployments with sensitive data, follow the security guidelines in [SECURITY.md](../SECURITY.md) to protect your credentials.

### 1. Database Setup

1. Import the database schema:
   ```bash
   # Credentials are in backend/config.php
   mysql -h sql201.infinityfree.com -u if0_39929369 -p if0_39929369_blog < database.sql
   ```

### 2. Upload to Server

Upload all PHP files to your web server (hugow.wuaze.com):

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
├── admin.php
└── database.sql
```

### 3. Configuration

Edit `config.php` if needed to update:
- Database credentials
- JWT secret key
- CORS settings

### 4. Test the API

Visit: https://hugow.wuaze.com/
You should see the API documentation.

## API Endpoints

### Authentication
- `POST /auth/register` - Register new user
- `POST /auth/login` - Login
- `GET /auth/verify` - Verify token

### Posts
- `GET /posts` - Get all posts (supports filters)
- `GET /posts?id={id}` - Get single post
- `POST /posts` - Create post (admin only)
- `PUT /posts` - Update post (admin only)
- `DELETE /posts?id={id}` - Delete post (admin only)

### Comments
- `GET /comments?post_id={id}` - Get comments
- `POST /comments` - Add comment
- `DELETE /comments?id={id}` - Delete comment

### Likes
- `GET /likes?post_id={id}` - Get like status
- `POST /likes` - Toggle like

### Tags
- `GET /tags` - Get all tags

### Admin
- `GET /admin/users` - Get all users
- `DELETE /admin/users?id={id}` - Delete user
- `GET /admin/view-logs` - Get view logs
- `GET /admin/stats` - Get statistics

## Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer {token}
```

## CORS

The API has CORS enabled to allow requests from GitHub Pages.

## Security Features

- Password hashing with bcrypt
- JWT-like token authentication
- SQL injection protection with prepared statements
- XSS protection with input sanitization
- Admin-only endpoints protection
