# API Quick Reference

Base URL: `https://hugow.wuaze.com`

## Authentication Required

For protected endpoints, include header:
```
Authorization: Bearer {your_token_here}
```

## Endpoints

### 🔐 Authentication

#### Register New User
```http
POST /auth/register
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123",
  "username": "John Doe"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registered successfully",
  "data": {
    "token": "eyJ0eXAi...",
    "user": {
      "id": 1,
      "email": "user@example.com",
      "username": "John Doe",
      "is_admin": true
    }
  }
}
```

#### Login
```http
POST /auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

#### Verify Token
```http
GET /auth/verify
Authorization: Bearer {token}
```

---

### 📝 Posts

#### Get All Posts
```http
GET /posts
GET /posts?tag=javascript
GET /posts?year=2024
GET /posts?month=1
GET /posts?search=react
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Welcome Post",
      "content": "Full content...",
      "author_id": 1,
      "author_name": "John Doe",
      "author_email": "user@example.com",
      "created_at": "2024-01-15 10:30:00",
      "updated_at": "2024-01-15 10:30:00",
      "like_count": 5,
      "comment_count": 3,
      "view_count": 42,
      "tags": [
        {"id": 1, "name": "javascript"},
        {"id": 2, "name": "tutorial"}
      ]
    }
  ]
}
```

#### Get Single Post
```http
GET /posts?id=1
```

#### Create Post (Admin Only)
```http
POST /posts
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "New Post Title",
  "content": "Post content here...",
  "tags": ["javascript", "react", "tutorial"],
  "status": "published"
}
```

#### Update Post (Admin Only)
```http
PUT /posts
Authorization: Bearer {token}
Content-Type: application/json

{
  "id": 1,
  "title": "Updated Title",
  "content": "Updated content...",
  "tags": ["javascript", "updated"]
}
```

#### Delete Post (Admin Only)
```http
DELETE /posts?id=1
Authorization: Bearer {token}
```

---

### 💬 Comments

#### Get Comments for Post
```http
GET /comments?post_id=1
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "post_id": 1,
      "user_id": 2,
      "username": "Jane Doe",
      "email": "jane@example.com",
      "content": "Great post!",
      "created_at": "2024-01-15 11:00:00"
    }
  ]
}
```

#### Add Comment (Requires Login)
```http
POST /comments
Authorization: Bearer {token}
Content-Type: application/json

{
  "post_id": 1,
  "content": "Great article!"
}
```

#### Delete Comment (Owner or Admin)
```http
DELETE /comments?id=1
Authorization: Bearer {token}
```

---

### ❤️ Likes

#### Get Like Status
```http
GET /likes?post_id=1
Authorization: Bearer {token}  # Optional
```

**Response:**
```json
{
  "success": true,
  "data": {
    "like_count": 5,
    "liked": true
  }
}
```

#### Toggle Like (Requires Login)
```http
POST /likes
Authorization: Bearer {token}
Content-Type: application/json

{
  "post_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Post liked",
  "data": {
    "liked": true
  }
}
```

---

### 🏷️ Tags

#### Get All Tags
```http
GET /tags
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "javascript",
      "post_count": 5,
      "created_at": "2024-01-15 10:00:00"
    },
    {
      "id": 2,
      "name": "react",
      "post_count": 3,
      "created_at": "2024-01-15 10:15:00"
    }
  ]
}
```

---

### 👨‍💼 Admin

#### Get All Users (Admin Only)
```http
GET /admin/users
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "email": "admin@example.com",
      "username": "Admin User",
      "is_admin": true,
      "created_at": "2024-01-15 09:00:00"
    }
  ]
}
```

#### Delete User (Admin Only)
```http
DELETE /admin/users?id=2
Authorization: Bearer {token}
```

#### Get View Logs (Admin Only)
```http
GET /admin/view-logs
GET /admin/view-logs?post_id=1
GET /admin/view-logs?user_id=2
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "post_id": 1,
      "post_title": "Welcome Post",
      "user_id": 2,
      "username": "Jane Doe",
      "email": "jane@example.com",
      "ip_address": "192.168.1.1",
      "viewed_at": "2024-01-15 12:00:00"
    }
  ]
}
```

#### Get Statistics (Admin Only)
```http
GET /admin/stats
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_users": 10,
    "total_posts": 25,
    "total_comments": 150,
    "total_likes": 200,
    "total_views": 5000,
    "most_viewed_posts": [...],
    "most_liked_posts": [...]
  }
}
```

---

## Error Responses

All errors follow this format:

```json
{
  "success": false,
  "error": "Error message here"
}
```

Common HTTP Status Codes:
- `200` - Success
- `400` - Bad Request
- `401` - Unauthorized (login required)
- `403` - Forbidden (admin access required)
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Server Error

---

## Testing with cURL

### Register
```bash
curl -X POST https://hugow.wuaze.com/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123","username":"Test User"}'
```

### Login
```bash
curl -X POST https://hugow.wuaze.com/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'
```

### Get Posts
```bash
curl https://hugow.wuaze.com/posts
```

### Create Post (with token)
```bash
curl -X POST https://hugow.wuaze.com/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{"title":"Test Post","content":"Test content","tags":["test"]}'
```

---

## Testing with JavaScript

```javascript
const API_URL = 'https://hugow.wuaze.com';

// Login
async function login(email, password) {
  const response = await fetch(`${API_URL}/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await response.json();
  return data.data.token;
}

// Get posts
async function getPosts() {
  const response = await fetch(`${API_URL}/posts`);
  const data = await response.json();
  return data.data;
}

// Create post (admin)
async function createPost(token, title, content, tags) {
  const response = await fetch(`${API_URL}/posts`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ title, content, tags })
  });
  return await response.json();
}

// Toggle like
async function toggleLike(token, postId) {
  const response = await fetch(`${API_URL}/likes`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ post_id: postId })
  });
  return await response.json();
}
```

---

## Rate Limiting

Currently no rate limiting implemented. Consider implementing in production:
- 100 requests per minute per IP
- 1000 requests per hour per user
- Stricter limits for write operations

---

## CORS

CORS is enabled for all origins (`*`). In production, consider restricting to:
```php
header('Access-Control-Allow-Origin: https://kychugo.github.io');
```
