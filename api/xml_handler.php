<?php
/**
 * ============================================
 * TASKMASTER - XML HANDLER API
 * Handles XML import/export operations
 * ============================================
 * 
 * Endpoints:
 * GET  /api/xml_handler.php?action=export     - Export tasks to XML
 * GET  /api/xml_handler.php?action=export&download=1  - Download XML file
 * POST /api/xml_handler.php?action=import     - Import tasks from XML
 * GET  /api/xml_handler.php?action=load       - Load from XML file
 */

// ============================================
// HEADERS
// ============================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ============================================
// CONFIGURATION
// ============================================
define('DB_PATH', __DIR__ . '/../database/taskmaster.db');
define('XML_PATH', __DIR__ . '/../data/tasks.xml');

// ============================================
// DATABASE CONNECTION
// ============================================
function getDatabase() {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $db;
    } catch (PDOException $e) {
        sendResponse(false, 'Database connection failed: ' . $e->getMessage(), null, 500);
    }
}

// ============================================
// RESPONSE HELPER
// ============================================
function sendResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function sendXMLResponse($xml) {
    header('Content-Type: application/xml; charset=utf-8');
    echo $xml;
    exit;
}

function sendXMLDownload($xml, $filename) {
    header('Content-Type: application/xml; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($xml));
    echo $xml;
    exit;
}

// ============================================
// EXPORT TASKS TO XML
// ============================================
function exportTasksToXML($user_id = 1) {
    $db = getDatabase();
    
    // Get tasks
    $stmt = $db->prepare("
        SELECT t.*, c.name as category_name, c.color as category_color
        FROM tasks t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = :user_id AND t.is_deleted = 0
        ORDER BY t.created_at DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $tasks = $stmt->fetchAll();
    
    // Get categories
    $stmt = $db->prepare("SELECT * FROM categories WHERE user_id = :user_id ORDER BY sort_order");
    $stmt->execute(['user_id' => $user_id]);
    $categories = $stmt->fetchAll();
    
    // Create XML document
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    
    // Root element
    $root = $xml->createElement('taskmaster');
    $xml->appendChild($root);
    
    // Metadata
    $metadata = $xml->createElement('metadata');
    $root->appendChild($metadata);
    
    $metadata->appendChild($xml->createElement('exported_at', date('c')));
    $metadata->appendChild($xml->createElement('version', '2.0'));
    $metadata->appendChild($xml->createElement('total_tasks', count($tasks)));
    $metadata->appendChild($xml->createElement('user_id', $user_id));
    
    // Tasks
    $tasksElement = $xml->createElement('tasks');
    $root->appendChild($tasksElement);
    
    foreach ($tasks as $task) {
        $taskElement = $xml->createElement('task');
        $taskElement->setAttribute('id', $task['id']);
        
        $taskElement->appendChild($xml->createElement('title', htmlspecialchars($task['title'])));
        $taskElement->appendChild($xml->createElement('description', htmlspecialchars($task['description'] ?? '')));
        $taskElement->appendChild($xml->createElement('category', $task['category_name'] ?? 'personal'));
        $taskElement->appendChild($xml->createElement('priority', $task['priority']));
        $taskElement->appendChild($xml->createElement('status', $task['status']));
        $taskElement->appendChild($xml->createElement('due_date', $task['due_date'] ?? ''));
        $taskElement->appendChild($xml->createElement('created_at', $task['created_at']));
        $taskElement->appendChild($xml->createElement('completed_at', $task['completed_at'] ?? ''));
        
        $tasksElement->appendChild($taskElement);
    }
    
    // Categories
    $categoriesElement = $xml->createElement('categories');
    $root->appendChild($categoriesElement);
    
    foreach ($categories as $category) {
        $catElement = $xml->createElement('category');
        $catElement->appendChild($xml->createElement('name', $category['name']));
        $catElement->appendChild($xml->createElement('color', $category['color']));
        $catElement->appendChild($xml->createElement('icon', $category['icon']));
        $categoriesElement->appendChild($catElement);
    }
    
    return $xml->saveXML();
}

// ============================================
// IMPORT TASKS FROM XML
// ============================================
function importTasksFromXML($xmlContent, $user_id = 1) {
    $db = getDatabase();
    
    // Parse XML
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xmlContent);
    
    if ($xml === false) {
        $errors = libxml_get_errors();
        $errorMsg = 'Invalid XML: ';
        foreach ($errors as $error) {
            $errorMsg .= trim($error->message) . '; ';
        }
        libxml_clear_errors();
        sendResponse(false, $errorMsg, null, 400);
    }
    
    $importedCount = 0;
    $skippedCount = 0;
    $errors = [];
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        foreach ($xml->tasks->task as $task) {
            $title = (string)$task->title;
            
            // Skip if empty title
            if (empty(trim($title))) {
                $skippedCount++;
                continue;
            }
            
            // Get category ID
            $categoryName = (string)$task->category ?: 'personal';
            $stmt = $db->prepare("SELECT id FROM categories WHERE user_id = :user_id AND name = :name");
            $stmt->execute(['user_id' => $user_id, 'name' => $categoryName]);
            $category = $stmt->fetch();
            $category_id = $category ? $category['id'] : null;
            
            // Insert task
            $stmt = $db->prepare("
                INSERT INTO tasks (user_id, category_id, title, description, priority, status, due_date, created_at, updated_at)
                VALUES (:user_id, :category_id, :title, :description, :priority, :status, :due_date, datetime('now'), datetime('now'))
            ");
            
            $stmt->execute([
                'user_id' => $user_id,
                'category_id' => $category_id,
                'title' => $title,
                'description' => (string)$task->description ?: null,
                'priority' => (string)$task->priority ?: 'medium',
                'status' => (string)$task->status ?: 'pending',
                'due_date' => !empty((string)$task->due_date) ? (string)$task->due_date : null
            ]);
            
            $importedCount++;
        }
        
        $db->commit();
        
        return [
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'total' => $importedCount + $skippedCount
        ];
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

// ============================================
// LOAD TASKS FROM XML FILE
// ============================================
function loadTasksFromXMLFile($user_id = 1) {
    if (!file_exists(XML_PATH)) {
        sendResponse(false, 'XML file not found', null, 404);
    }
    
    $xmlContent = file_get_contents(XML_PATH);
    return importTasksFromXML($xmlContent, $user_id);
}

// ============================================
// PARSE XML TO JSON (for AJAX)
// ============================================
function parseXMLToJSON() {
    if (!file_exists(XML_PATH)) {
        sendResponse(false, 'XML file not found', null, 404);
    }
    
    $xmlContent = file_get_contents(XML_PATH);
    $xml = simplexml_load_string($xmlContent);
    
    if ($xml === false) {
        sendResponse(false, 'Failed to parse XML', null, 400);
    }
    
    $tasks = [];
    foreach ($xml->tasks->task as $task) {
        $tasks[] = [
            'id' => (string)$task['id'],
            'title' => (string)$task->title,
            'description' => (string)$task->description,
            'category' => (string)$task->category,
            'priority' => (string)$task->priority,
            'status' => (string)$task->status,
            'due_date' => (string)$task->due_date,
            'created_at' => (string)$task->created_at,
            'completed_at' => (string)$task->completed_at
        ];
    }
    
    $categories = [];
    foreach ($xml->categories->category as $category) {
        $categories[] = [
            'name' => (string)$category->name,
            'color' => (string)$category->color,
            'icon' => (string)$category->icon
        ];
    }
    
    return [
        'metadata' => [
            'exported_at' => (string)$xml->metadata->exported_at,
            'version' => (string)$xml->metadata->version,
            'total_tasks' => (int)$xml->metadata->total_tasks
        ],
        'tasks' => $tasks,
        'categories' => $categories
    ];
}

// ============================================
// SAVE XML FILE
// ============================================
function saveXMLFile($user_id = 1) {
    $xmlContent = exportTasksToXML($user_id);
    
    $dataDir = __DIR__ . '/../data';
    if (!file_exists($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    $result = file_put_contents(XML_PATH, $xmlContent);
    
    if ($result === false) {
        sendResponse(false, 'Failed to save XML file', null, 500);
    }
    
    return true;
}

// ============================================
// MAIN EXECUTION
// ============================================
try {
    $action = $_GET['action'] ?? 'export';
    $user_id = 1; // Default user
    
    switch ($action) {
        case 'export':
            $xml = exportTasksToXML($user_id);
            
            if (isset($_GET['download']) && $_GET['download'] == '1') {
                $filename = 'taskmaster_export_' . date('Y-m-d_His') . '.xml';
                sendXMLDownload($xml, $filename);
            } elseif (isset($_GET['json']) && $_GET['json'] == '1') {
                // Save and return success
                saveXMLFile($user_id);
                sendResponse(true, 'Tasks exported to XML successfully', ['file' => 'data/tasks.xml']);
            } else {
                sendXMLResponse($xml);
            }
            break;
            
        case 'import':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                sendResponse(false, 'POST method required for import', null, 405);
            }
            
            // Check for file upload
            if (isset($_FILES['xml_file']) && $_FILES['xml_file']['error'] === UPLOAD_ERR_OK) {
                $xmlContent = file_get_contents($_FILES['xml_file']['tmp_name']);
            } else {
                // Check for raw XML in body
                $xmlContent = file_get_contents('php://input');
            }
            
            if (empty($xmlContent)) {
                sendResponse(false, 'No XML content provided', null, 400);
            }
            
            $result = importTasksFromXML($xmlContent, $user_id);
            sendResponse(true, "Import complete: {$result['imported']} tasks imported, {$result['skipped']} skipped", $result);
            break;
            
        case 'load':
            // Load from existing XML file into database
            $result = loadTasksFromXMLFile($user_id);
            sendResponse(true, "Loaded {$result['imported']} tasks from XML file", $result);
            break;
            
        case 'parse':
            // Just parse XML and return as JSON (for AJAX/JavaScript)
            $data = parseXMLToJSON();
            sendResponse(true, 'XML parsed successfully', $data);
            break;
            
        case 'save':
            // Export current database to XML file
            saveXMLFile($user_id);
            sendResponse(true, 'Tasks saved to XML file', ['file' => 'data/tasks.xml']);
            break;
            
        default:
            sendResponse(false, 'Invalid action. Use: export, import, load, parse, save', null, 400);
    }
    
} catch (Exception $e) {
    sendResponse(false, 'Error: ' . $e->getMessage(), null, 500);
}