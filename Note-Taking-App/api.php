<?php
/**
 * API Endpoints for Note Taking App
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($action) {
        case 'create':
            if ($method !== 'POST') throw new Exception('Method not allowed', 405);
            createNote();
            break;

        case 'read':
            if ($method !== 'GET') throw new Exception('Method not allowed', 405);
            readNote();
            break;

        case 'list':
            if ($method !== 'GET') throw new Exception('Method not allowed', 405);
            listNotes();
            break;

        case 'update':
            if ($method !== 'PUT') throw new Exception('Method not allowed', 405);
            updateNote();
            break;

        case 'delete':
            if ($method !== 'DELETE') throw new Exception('Method not allowed', 405);
            deleteNote();
            break;

        case 'search':
            if ($method !== 'GET') throw new Exception('Method not allowed', 405);
            searchNotes();
            break;

        default:
            throw new Exception('Invalid action', 400);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 400);
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}

/**
 * Create a new note
 */
function createNote() {
    global $pdo;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['title']) || !isset($data['content'])) {
        throw new Exception('Title and content are required', 400);
    }

    $title = trim($data['title']);
    $content = trim($data['content']);

    if (empty($title) || empty($content)) {
        throw new Exception('Title and content cannot be empty', 400);
    }

    $stmt = $pdo->prepare('INSERT INTO notes (title, content) VALUES (?, ?)');
    $stmt->execute([$title, $content]);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId(),
        'message' => 'Note created successfully'
    ]);
}

/**
 * Read a single note
 */
function readNote() {
    global $pdo;
    
    if (!isset($_GET['id'])) {
        throw new Exception('Note ID is required', 400);
    }

    $id = (int)$_GET['id'];
    
    $stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?');
    $stmt->execute([$id]);
    $note = $stmt->fetch();

    if (!$note) {
        throw new Exception('Note not found', 404);
    }

    echo json_encode($note);
}

/**
 * List all notes with pagination
 */
function listNotes() {
    global $pdo;
    
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $perPage;

    // Get total count
    $countStmt = $pdo->query('SELECT COUNT(*) as count FROM notes');
    $totalNotes = $countStmt->fetch()['count'];

    // Get paginated notes
    $stmt = $pdo->prepare('
        SELECT id, title, content, created_at, updated_at 
        FROM notes 
        ORDER BY updated_at DESC 
        LIMIT ? OFFSET ?
    ');
    $stmt->execute([$perPage, $offset]);
    $notes = $stmt->fetchAll();

    echo json_encode([
        'notes' => $notes,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $totalNotes,
            'pages' => ceil($totalNotes / $perPage)
        ]
    ]);
}

/**
 * Update a note
 */
function updateNote() {
    global $pdo;
    
    if (!isset($_GET['id'])) {
        throw new Exception('Note ID is required', 400);
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)$_GET['id'];

    // Check if note exists
    $checkStmt = $pdo->prepare('SELECT id FROM notes WHERE id = ?');
    $checkStmt->execute([$id]);
    if (!$checkStmt->fetch()) {
        throw new Exception('Note not found', 404);
    }

    // Update only provided fields
    $updates = [];
    $params = [];

    if (isset($data['title'])) {
        $title = trim($data['title']);
        if (empty($title)) {
            throw new Exception('Title cannot be empty', 400);
        }
        $updates[] = 'title = ?';
        $params[] = $title;
    }

    if (isset($data['content'])) {
        $content = trim($data['content']);
        if (empty($content)) {
            throw new Exception('Content cannot be empty', 400);
        }
        $updates[] = 'content = ?';
        $params[] = $content;
    }

    if (empty($updates)) {
        throw new Exception('No fields to update', 400);
    }

    $params[] = $id;
    $sql = 'UPDATE notes SET ' . implode(', ', $updates) . ' WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => 'Note updated successfully'
    ]);
}

/**
 * Delete a note
 */
function deleteNote() {
    global $pdo;
    
    if (!isset($_GET['id'])) {
        throw new Exception('Note ID is required', 400);
    }

    $id = (int)$_GET['id'];

    $stmt = $pdo->prepare('DELETE FROM notes WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Note not found', 404);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Note deleted successfully'
    ]);
}

/**
 * Search notes by title and content
 */
function searchNotes() {
    global $pdo;
    
    if (!isset($_GET['q'])) {
        throw new Exception('Search query is required', 400);
    }

    $query = trim($_GET['q']);
    if (empty($query)) {
        throw new Exception('Search query cannot be empty', 400);
    }

    // Using FULLTEXT search for better results
    $stmt = $pdo->prepare('
        SELECT id, title, content, created_at, updated_at
        FROM notes
        WHERE MATCH(title, content) AGAINST(? IN BOOLEAN MODE)
        ORDER BY updated_at DESC
    ');
    $stmt->execute([$query]);
    $notes = $stmt->fetchAll();

    echo json_encode([
        'query' => $query,
        'results' => $notes,
        'count' => count($notes)
    ]);
}
