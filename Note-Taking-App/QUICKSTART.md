# Note Taking App - Quick Start Guide

## 🚀 Getting Started in 5 Minutes

### 1. Set up MySQL Database

```bash
# Connect to MySQL
mysql -u root -p

# Copy and paste the contents from database.sql
CREATE DATABASE IF NOT EXISTS notes_app;
USE notes_app;
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_created (created_at),
    INDEX idx_updated (updated_at),
    FULLTEXT idx_search (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Update Database Credentials

Edit `config.php` with your MySQL details:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Change this
define('DB_PASS', '');            // Change this if needed
define('DB_NAME', 'notes_app');
```

### 3. Run the App

```bash
# Navigate to your project folder
cd /path/to/note-taking-app

# Start PHP server
php -S localhost:8000

# Open in browser
# http://localhost:8000
```

## 📁 File Overview

| File | Purpose |
|------|---------|
| `index.html` | Main UI - sidebar with notes list and editor |
| `styles.css` | All styling - customize colors/fonts here |
| `app.js` | Frontend logic - handles user interactions |
| `api.php` | Backend API - processes all requests |
| `config.php` | Database connection settings |
| `database.sql` | Database schema - run this in MySQL first |

## 🔍 How It Works

1. **Frontend** (`app.js` + `index.html`): User creates/edits notes
2. **API** (`api.php`): Receives requests, validates data, queries database
3. **Database** (MySQL): Stores all notes permanently
4. **Styling** (`styles.css`): Makes everything look nice

## 🛠️ Key Features Implemented

✅ Create new notes  
✅ Edit existing notes  
✅ Delete notes  
✅ Full-text search  
✅ Timestamps (created/updated)  
✅ Responsive design  
✅ Input validation  
✅ Error handling  

## 🎨 Customizing the App

### Change Colors
Open `styles.css` and modify the `:root` variables:
```css
:root {
    --primary-color: #3b82f6;        /* Change this */
    --success-color: #10b981;        /* And this */
    --danger-color: #ef4444;         /* And this */
    /* ... */
}
```

### Change Fonts
```css
body {
    font-family: 'Your Font', Arial, sans-serif;
}
```

### Add New Features
- Edit `api.php` to add new endpoints
- Modify `app.js` to add new frontend functions
- Update `index.html` to add new UI elements

## ⚠️ Common Issues & Fixes

### "Database connection failed"
❌ Problem: MySQL credentials wrong or DB doesn't exist  
✅ Solution: Check `config.php`, verify `notes_app` database exists

### "Can't create notes"
❌ Problem: Table doesn't exist  
✅ Solution: Run the SQL from `database.sql` in MySQL

### "Search not working"
❌ Problem: FULLTEXT index not created  
✅ Solution: Verify the table was created with the correct SQL

### "Notes not showing"
❌ Problem: Database query failed  
✅ Solution: Check browser console (F12) for error messages

## 📚 API Reference

### Create
```javascript
fetch('api.php?action=create', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ title: 'Title', content: 'Content' })
})
```

### List
```javascript
fetch('api.php?action=list')
```

### Read
```javascript
fetch('api.php?action=read&id=1')
```

### Update
```javascript
fetch('api.php?action=update&id=1', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ title: 'New', content: 'New' })
})
```

### Delete
```javascript
fetch('api.php?action=delete&id=1', { method: 'DELETE' })
```

### Search
```javascript
fetch('api.php?action=search&q=your+query')
```

## 🔒 Before Going to Production

- [ ] Add user authentication (login/register)
- [ ] Add CSRF token protection
- [ ] Use HTTPS
- [ ] Sanitize all inputs
- [ ] Add rate limiting
- [ ] Back up your database regularly
- [ ] Test with different browsers
- [ ] Add error logging

## 📞 Need Help?

- Check the comments in each PHP/JS file
- Review the README.md for detailed info
- Test your MySQL connection with a simple script
- Use browser DevTools (F12) to debug JavaScript
