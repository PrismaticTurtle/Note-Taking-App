# Database Schema Documentation

## Table: `notes`

### Overview
The `notes` table stores all user notes with metadata including creation and update timestamps.

### Columns

| Column | Type | Constraints | Description |
|--------|------|-----------|-------------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier for each note |
| `title` | VARCHAR(255) | NOT NULL | Note title/heading |
| `content` | LONGTEXT | NOT NULL | Note body content (up to 4GB) |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | When the note was created |
| `updated_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP, ON UPDATE CURRENT_TIMESTAMP | When the note was last modified |

### Indexes

```sql
-- Speed up date-based queries
INDEX `idx_created` (created_at)
INDEX `idx_updated` (updated_at)

-- Enable full-text search on title and content
FULLTEXT `idx_search` (title, content)
```

### Data Types Explained

- **INT**: Integer (32-bit)
- **VARCHAR(255)**: Variable-length string, max 255 bytes
- **LONGTEXT**: Can store up to 4GB of text (plenty for notes)
- **TIMESTAMP**: Date and time, automatically set by MySQL

## Example Queries

### Create a Note
```sql
INSERT INTO notes (title, content) 
VALUES ('My First Note', 'This is the content of my note');
```

### Read All Notes
```sql
SELECT * FROM notes ORDER BY updated_at DESC;
```

### Search Notes
```sql
SELECT * FROM notes 
WHERE MATCH(title, content) AGAINST('search term' IN BOOLEAN MODE)
ORDER BY updated_at DESC;
```

### Update a Note
```sql
UPDATE notes 
SET title = 'Updated Title', content = 'Updated content'
WHERE id = 1;
```

### Delete a Note
```sql
DELETE FROM notes WHERE id = 1;
```

### Get Recent Notes
```sql
SELECT * FROM notes 
ORDER BY updated_at DESC 
LIMIT 10;
```

### Get Notes from Last Week
```sql
SELECT * FROM notes 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY created_at DESC;
```

## Storage Considerations

- **Average Note Size**: ~2-5 KB per note
- **1000 Notes**: ~2-5 MB
- **Typical Database**: Can easily handle 100,000+ notes

## Performance Tips

1. **Indexes**: Already created for `created_at`, `updated_at`, and full-text search
2. **Query Optimization**: Pagination implemented in `api.php` (10 notes per page)
3. **Search**: FULLTEXT index makes searches fast (even with 100,000+ notes)
4. **Backup**: Regularly backup your database using:
   ```bash
   mysqldump -u root -p notes_app > backup.sql
   ```

## Future Schema Enhancements

If you want to extend the app, here are some useful additions:

### Add Tags/Categories
```sql
ALTER TABLE notes ADD COLUMN category VARCHAR(100);
CREATE INDEX idx_category ON notes(category);
```

### Add User Support
```sql
ALTER TABLE notes ADD COLUMN user_id INT;
ALTER TABLE notes ADD FOREIGN KEY (user_id) REFERENCES users(id);
```

### Add Pinning
```sql
ALTER TABLE notes ADD COLUMN is_pinned BOOLEAN DEFAULT FALSE;
```

### Archive Notes
```sql
ALTER TABLE notes ADD COLUMN is_archived BOOLEAN DEFAULT FALSE;
```

## Troubleshooting

### Table Not Found Error
- Ensure the `notes_app` database exists: `SHOW DATABASES;`
- Ensure the `notes` table exists: `SHOW TABLES;`
- Run the SQL from `database.sql` to create it

### Special Characters Issue
- The table uses `utf8mb4_unicode_ci` collation for full Unicode support
- Safe to store emojis, special characters, and non-Latin scripts

### Search Not Working
- Verify FULLTEXT index exists: `SHOW INDEX FROM notes;`
- Ensure you're using MySQL 5.6+ (InnoDB FULLTEXT support)

## Recovery

### Export All Notes
```bash
mysqldump -u root -p notes_app notes > notes_backup.sql
```

### Import from Backup
```bash
mysql -u root -p notes_app < notes_backup.sql
```
