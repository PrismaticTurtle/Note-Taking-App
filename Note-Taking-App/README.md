# Note Taking App

A modern, lightweight note-taking application built with HTML, PHP, and MySQL.

## Features

- ✨ **Create, Read, Update, Delete (CRUD)** - Full note management
- 🔍 **Full-text Search** - Search notes by title and content
- 📅 **Timestamps** - Track when notes were created and updated
- 💾 **Persistent Storage** - All notes saved to MySQL database
- 📱 **Responsive Design** - Works on desktop and mobile devices
- ⚡ **Lightweight** - Plain CSS, no heavy frameworks

## Project Structure

```
├── index.html           # Main HTML page
├── styles.css           # CSS styling
├── app.js               # Frontend JavaScript logic
├── api.php              # Backend API endpoints
├── config.php           # Database configuration
├── database.sql         # Database schema
└── README.md            # This file
```

## Installation & Setup

### Prerequisites

- PHP 7.4+ (with PDO MySQL extension)
- MySQL 5.7+
- Web server (Apache, Nginx, etc.) or PHP built-in server

### Step 1: Create the Database

1. Open your MySQL client (phpMyAdmin, MySQL Workbench, or CLI)
2. Run the SQL commands from `database.sql`:
   ```sql
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

### Step 2: Configure Database Connection

Edit `config.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password
define('DB_NAME', 'notes_app');
```

### Step 3: Run the Application

#### Option A: Using PHP Built-in Server
```bash
php -S localhost:8000
```
Then open `http://localhost:8000` in your browser.

#### Option B: Using Apache/Nginx
Place the entire folder in your web root and access via your configured domain.

## API Endpoints

All endpoints return JSON responses.

### Create Note
```
POST /api.php?action=create
Content-Type: application/json

{
    "title": "My Note",
    "content": "Note content here"
}
```

### List Notes
```
GET /api.php?action=list[&page=1&per_page=10]
```

### Read Note
```
GET /api.php?action=read&id=1
```

### Update Note
```
PUT /api.php?action=update&id=1
Content-Type: application/json

{
    "title": "Updated Title",
    "content": "Updated content"
}
```

### Delete Note
```
DELETE /api.php?action=delete&id=1
```

### Search Notes
```
GET /api.php?action=search&q=search+query
```

## Browser Support

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers

## Customization

### Styling
Edit `styles.css` to customize colors, fonts, and layout. CSS variables are defined at the top for easy theming.

### Features
You can extend the app by:
- Adding note categories/tags
- Implementing note sharing
- Adding export functionality
- Creating backups
- Adding markdown support

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check credentials in `config.php`
- Ensure `notes_app` database exists

### Cannot create/save notes
- Check file permissions
- Verify PHP error logs
- Ensure `api.php` is accessible

### Search not working
- Confirm FULLTEXT index was created in the database
- Check if MySQL FULLTEXT search is supported (requires MyISAM or InnoDB 5.6+)

## Performance Tips

- Add pagination for large note collections (already implemented)
- Use indexes for faster searches (already included in schema)
- Consider adding caching for frequently accessed notes
- Optimize images if you add attachments

## Security Notes

- Always validate input on the backend (already done in `api.php`)
- Use prepared statements (PDO with ? placeholders - already implemented)
- Add user authentication before deploying to production
- Implement CSRF tokens for production use
- Use HTTPS in production

## Future Enhancements

- [ ] User authentication & accounts
- [ ] Note sharing & collaboration
- [ ] Rich text editor (with markdown)
- [ ] Tags/Categories system
- [ ] Note templates
- [ ] Export to PDF/HTML
- [ ] Backup/Restore functionality
- [ ] Dark mode theme
- [ ] Offline support with Service Workers

## License

Free to use and modify for personal and commercial projects.

## Support

For issues or questions, check the code comments or review the API documentation above.
