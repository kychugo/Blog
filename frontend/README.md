# Hugo 開發日誌 Frontend

Single-page application for Hugo Development Log blog platform.

## Features

- Modern, tech-inspired design
- Bilingual support (繁體中文 / English)
- Responsive layout
- Post listing with filters (tags, year, month)
- Search functionality
- Post detail view with comments and likes
- Admin dashboard
- User authentication

## Setup Instructions

### 1. Deploy to GitHub Pages

1. Copy `index.html` to your GitHub repository
2. Enable GitHub Pages in repository settings
3. Select the branch and folder containing `index.html`

### 2. Configuration

Edit the `API_URL` constant in `index.html`:

```javascript
const API_URL = 'https://hugow.wuaze.com';
```

Make sure this matches your backend PHP server URL.

### 3. Access the Site

Once deployed, access your blog at:
```
https://{username}.github.io/{repository-name}/frontend/
```

## Features Overview

### For Visitors
- Browse all published posts
- Filter posts by tags, year, or month
- Search posts by title or content
- View post details
- See the inspirational quote

### For Registered Users
- All visitor features
- Like posts
- Comment on posts
- Delete own comments

### For Admins (First Registered User)
- All user features
- Create, edit, and delete posts
- Add tags to posts
- View statistics dashboard
- Manage users (delete accounts)
- View all viewing logs
- Delete any comments

## Design Philosophy

The design features:
- **Tech-inspired aesthetic**: Dark theme with neon accents
- **Animated background**: Subtle rotating gradient
- **Modern UI**: Clean cards and smooth transitions
- **Responsive**: Works on all device sizes
- **Accessibility**: High contrast and clear typography

## Inspirational Quote

The platform features the quote:
> "if you only know how to use a hammer, everything looks like a nail"

This reminds us to expand our toolset and approach problems with diverse perspectives.

## Language Toggle

Click "English / 繁體中文" in the navigation to switch between languages.

## Browser Support

Works on all modern browsers:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers

## No Build Required

This is a single HTML file with embedded CSS and JavaScript. No build process or dependencies required!
