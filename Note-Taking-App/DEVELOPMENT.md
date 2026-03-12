# Development Guide - Extending Your Note App

## Project Architecture

```
┌─────────────────────────────────────────────────────┐
│                   Browser (Client)                   │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────┐ │
│  │  index.html  │  │  styles.css  │  │  app.js    │ │
│  └──────────────┘  └──────────────┘  └────────────┘ │
└─────────────────────────────────────────────────────┘
              │
              │ HTTP requests (JSON)
              ▼
┌─────────────────────────────────────────────────────┐
│              Server (Backend) - PHP                  │
│  ┌──────────────┐              ┌──────────────────┐ │
│  │  api.php     │◄────────────►│  config.php      │ │
│  │ (Routing &   │              │ (DB Connection)  │ │
│  │  Handlers)   │              └──────────────────┘ │
│  └──────────────┘              ┌──────────────────┐ │
│                                │  PDO (Database   │ │
│                                │  Abstraction)    │ │
│                                └──────────────────┘ │
└─────────────────────────────────────────────────────┘
              │
              │ SQL queries
              ▼
┌─────────────────────────────────────────────────────┐
│              Database - MySQL                        │
│         ┌───────────────────────────┐              │
│         │      notes Table           │              │
│         │  id | title | content ...  │              │
│         └───────────────────────────┘              │
└─────────────────────────────────────────────────────┘
```

## Understanding Each Component

### Frontend (`app.js`)

**Flow:**
1. User interacts with UI (clicks, types, searches)
2. JavaScript captures the event
3. Fetch API sends HTTP request to `api.php`
4. Response updates the DOM

**Key Functions:**
- `loadNotes()` - Fetch all notes from server
- `loadNote(id)` - Load specific note
- `createNewNote()` - Send POST request
- `saveCurrentNote()` - Send PUT request
- `deleteCurrentNote()` - Send DELETE request
- `handleSearch()` - Send search query
- `renderNotesList()` - Update the UI

### Backend (`api.php`)

**Flow:**
1. Receives HTTP `GET/POST/PUT/DELETE` request
2. Parses query parameter `action`
3. Routes to appropriate handler function
4. Queries database via PDO
5. Returns JSON response

**Key Functions:**
```php
createNote()      // INSERT
readNote()        // SELECT (single)
listNotes()       // SELECT (all with pagination)
updateNote()      // UPDATE
deleteNote()      // DELETE
searchNotes()     // FULLTEXT SEARCH
```

### Database (`database.sql`)

- **Storage:** Persistent data
- **Indexes:** Speed up queries
- **Validation:** CONSTRAINTS (NOT NULL, etc.)

## How to Add Features

### 1. Add a New API Endpoint

**Example: Add "favorite" feature**

**Step 1: Update Database**
```sql
ALTER TABLE notes ADD COLUMN is_favorite BOOLEAN DEFAULT FALSE;
```

**Step 2: Add PHP Handler in `api.php`**
```php
function toggleFavorite() {
    global $pdo;
    
    if (!isset($_GET['id'])) {
        throw new Exception('Note ID required', 400);
    }

    $id = (int)$_GET['id'];
    
    // Toggle favorite status
    $stmt = $pdo->prepare('
        UPDATE notes 
        SET is_favorite = NOT is_favorite 
        WHERE id = ?
    ');
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);
}
```

**Step 3: Add route in `api.php`**
```php
case 'toggle-favorite':
    if ($method !== 'POST') throw new Exception('Method not allowed', 405);
    toggleFavorite();
    break;
```

**Step 4: Call from JavaScript in `app.js`**
```javascript
async function toggleFavorite(id) {
    const response = await fetch(`${API_BASE}?action=toggle-favorite&id=${id}`, {
        method: 'POST'
    });
    const data = await response.json();
    if (data.success) {
        await loadNotes(); // Refresh list
    }
}
```

### 2. Add a New UI Element

**Example: Add a category dropdown**

**Step 1: Add HTML in `index.html`**
```html
<div class="editor-toolbar">
    <select id="categoriesSelect" class="category-select">
        <option value="">Uncategorized</option>
        <option value="Work">Work</option>
        <option value="Personal">Personal</option>
        <option value="Ideas">Ideas</option>
    </select>
</div>
```

**Step 2: Add CSS in `styles.css`**
```css
.category-select {
    padding: 8px 12px;
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    font-size: 14px;
}

.category-select:focus {
    outline: none;
    border-color: var(--primary-color);
}
```

**Step 3: Add JavaScript in `app.js`**
```javascript
const categorySelect = document.getElementById('categoriesSelect');

function loadNote(id) {
    // ... existing code ...
    
    // Load category for this note
    categorySelect.value = note.category || '';
}

categorySelect.addEventListener('change', () => {
    // Update note category
    updateNoteCategory(currentNoteId, categorySelect.value);
});
```

### 3. Modify the Styling

**Change Colors:**
```css
:root {
    --primary-color: #FF6B6B;      /* Change from blue to red */
    --success-color: #4ECDC4;      /* Change from green to teal */
    --danger-color: #95A5A6;       /* Change from red to gray */
}
```

**Change Layout:**
Edit the flexbox properties in `styles.css`:
```css
.container {
    flex-direction: row;  /* Change to column for mobile-first */
}
```

## Code Examples

### Example 1: Add Markdown Support

**Frontend (app.js):**
```javascript
// Convert markdown to HTML when displaying
function renderMarkdown(markdown) {
    // Use a library like marked.js
    return marked.parse(markdown);
}

// Display in note viewer
noteContent.innerHTML = renderMarkdown(note.content);
```

**Add to index.html:**
```html
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
```

### Example 2: Add Dark Mode

**CSS:**
```css
body.dark-mode {
    --gray-50: #111827;
    --gray-100: #1f2937;
    /* ... etc ... */
}

body.dark-mode .sidebar {
    background-color: #1f2937;
    color: white;
}
```

**JavaScript:**
```javascript
const darkModeToggle = document.getElementById('darkModeToggle');

darkModeToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', 
        document.body.classList.contains('dark-mode') ? 'dark' : 'light'
    );
});

// Load saved theme
if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-mode');
}
```

### Example 3: Add Note Export

**Backend (api.php):**
```php
case 'export':
    if ($method !== 'GET') throw new Exception('Method not allowed', 405);
    exportNotes();
    break;

function exportNotes() {
    global $pdo;
    
    $stmt = $pdo->query('SELECT * FROM notes ORDER BY created_at DESC');
    $notes = $stmt->fetchAll();
    
    $json = json_encode($notes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="notes_backup_' . date('Y-m-d') . '.json"');
    
    echo $json;
}
```

**Frontend (app.js):**
```javascript
const exportBtn = document.getElementById('exportBtn');

exportBtn.addEventListener('click', async () => {
    const response = await fetch(`${API_BASE}?action=export`);
    const blob = await response.blob();
    
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `notes_${new Date().toISOString().split('T')[0]}.json`;
    a.click();
    window.URL.revokeObjectURL(url);
});
```

## Best Practices

### For Backend (PHP)

1. **Always validate input:**
   ```php
   $title = trim($_POST['title'] ?? '');
   if (empty($title)) throw new Exception('Title required');
   ```

2. **Use prepared statements:**
   ```php
   $stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?');
   $stmt->execute([$id]);
   ```

3. **Return proper HTTP status codes:**
   ```php
   http_response_code(201);  // Created
   http_response_code(400);  // Bad Request
   http_response_code(404);  // Not Found
   http_response_code(500);  // Server Error
   ```

4. **Handle errors gracefully:**
   ```php
   try {
       // Your code
   } catch (Exception $e) {
       http_response_code(500);
       echo json_encode(['error' => $e->getMessage()]);
   }
   ```

### For Frontend (JavaScript)

1. **Always check response status:**
   ```javascript
   if (!response.ok) throw new Error(response.statusText);
   ```

2. **Use async/await** instead of .then() chains

3. **Debounce expensive operations** (like search):
   ```javascript
   searchInput.addEventListener('input', debounce(handleSearch, 300));
   ```

4. **Show user feedback:**
   ```javascript
   showNotification('Note saved!');
   saveBtn.disabled = true;
   saveBtn.textContent = 'Saving...';
   // ... after request ...
   saveBtn.disabled = false;
   saveBtn.textContent = 'Save';
   ```

### For Database

1. **Add indexes for frequently searched columns:**
   ```sql
   CREATE INDEX idx_category ON notes(category);
   ```

2. **Use appropriate data types:**
   - VARCHAR for short text
   - LONGTEXT for long content
   - INT for IDs
   - TIMESTAMP for dates

3. **Normalize data if needed:**
   - Separate tables for categories
   - Foreign keys for relationships

## Testing

### Manual Testing

1. **Create:** Add a new note, check it appears
2. **Read:** Click notes in list, verify content loads
3. **Update:** Edit and save, verify changes persist
4. **Delete:** Delete a note, verify it's gone
5. **Search:** Search for text, verify results
6. **Edge cases:** Empty fields, special characters, long text

### Browser DevTools

```javascript
// Test API directly in Console
fetch('api.php?action=list').then(r => r.json()).then(d => console.log(d))

// Test create
fetch('api.php?action=create', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({title: 'Test', content: 'Test'})
}).then(r => r.json()).then(d => console.log(d))
```

## Deployment Checklist

- [ ] Update database credentials in `config.php`
- [ ] Use HTTPS in production
- [ ] Add user authentication
- [ ] Set up CORS properly if accessed from different domain
- [ ] Enable error logging
- [ ] Set up database backups
- [ ] Test all features in production environment
- [ ] Add CSRF token protection
- [ ] Rate limit API endpoints
- [ ] Monitor performance

## Common Mistakes to Avoid

❌ Not validating input  
✅ Always validate and sanitize data

❌ Using string concatenation in SQL queries  
✅ Use prepared statements with placeholders

❌ Returning sensitive errors to users  
✅ Log detailed errors server-side, show generic messages to users

❌ Storing passwords in plain text  
✅ Use password_hash() and password_verify()

❌ No input length limits  
✅ Set reasonable VARCHAR length limits

❌ Forgetting pagination  
✅ Paginate results to prevent slowdowns

## Resources

- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
- JavaScript Fetch API: https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API
- CSS Guide: https://developer.mozilla.org/en-US/docs/Web/CSS
