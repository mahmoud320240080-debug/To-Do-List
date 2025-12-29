<?php
/**
 * ============================================
 * TASKMASTER - TASKS API (SQLite Version)
 * ============================================
 * 
 * Endpoints:
 * GET    /api/tasks.php              - Get all tasks
 * GET    /api/tasks.php?id=1         - Get single task
 * POST   /api/tasks.php              - Create task
 * PUT    /api/tasks.php?id=1         - Update task
 * DELETE /api/tasks.php?id=1         - Delete task
 * PATCH  /api/tasks.php?id=1         - Toggle complete
 * 
 * GET    /api/tasks.php?action=stats     - Get statistics
 * GET    /api/tasks.php?action=deadlines - Get upcoming deadlines
 * DELETE /api/tasks.php?action=clear     - Clear completed tasks
 */

// ============================================
// HEADERS & CORS
// ============================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ============================================
// CONFIGURATION
// ============================================
define('ENVIRONMENT', 'development');
define('DB_PATH', __DIR__ . '/../database/taskmaster.db');

// Error reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

// ============================================
// DATABASE CLASS (SQLite)
// ============================================
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            // Check if database file exists
            if (!file_exists(DB_PATH)) {
                throw new Exception('Database not found. Please run init_db.php first.');
            }
            
            $this->connection = new PDO('sqlite:' . DB_PATH);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Enable foreign keys
            $this->connection->exec('PRAGMA foreign_keys = ON');
            
        } catch (PDOException $e) {
            Response::serverError('Database connection failed: ' . $e->getMessage());
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}

// ============================================
// RESPONSE CLASS
// ============================================
class Response {
    public static function json($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    public static function success($data = null, $message = 'Success', $statusCode = 200) {
        self::json($statusCode, [
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    public static function created($data = null, $message = 'Created successfully') {
        self::success($data, $message, 201);
    }
    
    public static function error($message = 'An error occurred', $statusCode = 400, $errors = []) {
        self::json($statusCode, [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ]);
    }
    
    public static function notFound($message = 'Resource not found') {
        self::error($message, 404);
    }
    
    public static function validationError($errors) {
        self::error('Validation failed', 422, $errors);
    }
    
    public static function serverError($message = 'Internal server error') {
        self::error($message, 500);
    }
}

// ============================================
// TASK CLASS
// ============================================
class Task {
    private $db;
    private $table = 'tasks';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all tasks for a user with filtering and sorting
     */
    public function getAll($user_id, $filters = []) {
        $sql = "SELECT t.*, c.name as category, c.color as category_color
                FROM {$this->table} t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = :user_id AND t.is_deleted = 0";
        
        $params = ['user_id' => $user_id];
        
        // Status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'completed') {
                $sql .= " AND t.status = 'completed'";
            } else {
                $sql .= " AND t.status != 'completed'";
            }
        }
        
        // Category filter
        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $sql .= " AND c.name = :category";
            $params['category'] = $filters['category'];
        }
        
        // Priority filter
        if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
            $sql .= " AND t.priority = :priority";
            $params['priority'] = $filters['priority'];
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $sql .= " AND (t.title LIKE :search OR t.description LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Sorting (SQLite compatible)
        $sortBy = $filters['sort_by'] ?? 'newest';
        switch ($sortBy) {
            case 'oldest':
                $sql .= " ORDER BY t.created_at ASC";
                break;
            case 'priority':
                $sql .= " ORDER BY CASE t.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END, t.created_at DESC";
                break;
            case 'due_date':
                $sql .= " ORDER BY t.due_date IS NULL, t.due_date ASC, t.created_at DESC";
                break;
            case 'alphabetical':
                $sql .= " ORDER BY t.title ASC";
                break;
            default:
                $sql .= " ORDER BY t.created_at DESC";
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get a single task by ID
     */
    public function getById($id, $user_id) {
        $sql = "SELECT t.*, c.name as category, c.color as category_color
                FROM {$this->table} t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.id = :id AND t.user_id = :user_id AND t.is_deleted = 0";
        
        $stmt = $this->db->query($sql, [
            'id' => $id,
            'user_id' => $user_id
        ]);
        
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Create a new task
     */
    public function create($data) {
        $category_id = $this->getCategoryId($data['user_id'], $data['category'] ?? 'personal');
        
        $sql = "INSERT INTO {$this->table} 
                (user_id, category_id, title, description, priority, due_date, created_at, updated_at)
                VALUES 
                (:user_id, :category_id, :title, :description, :priority, :due_date, datetime('now'), datetime('now'))";
        
        $this->db->query($sql, [
            'user_id' => $data['user_id'],
            'category_id' => $category_id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => !empty($data['due_date']) ? $data['due_date'] : null
        ]);
        
        $id = $this->db->lastInsertId();
        return $this->getById($id, $data['user_id']);
    }
    
    /**
     * Update a task
     */
    public function update($id, $user_id, $data) {
        $task = $this->getById($id, $user_id);
        if (!$task) {
            return null;
        }
        
        $fields = [];
        $params = ['id' => $id, 'user_id' => $user_id];
        
        if (isset($data['title'])) {
            $fields[] = 'title = :title';
            $params['title'] = $data['title'];
        }
        
        if (isset($data['description'])) {
            $fields[] = 'description = :description';
            $params['description'] = $data['description'];
        }
        
        if (isset($data['category'])) {
            $category_id = $this->getCategoryId($user_id, $data['category']);
            $fields[] = 'category_id = :category_id';
            $params['category_id'] = $category_id;
        }
        
        if (isset($data['priority'])) {
            $fields[] = 'priority = :priority';
            $params['priority'] = $data['priority'];
        }
        
        if (array_key_exists('due_date', $data)) {
            $fields[] = 'due_date = :due_date';
            $params['due_date'] = !empty($data['due_date']) ? $data['due_date'] : null;
        }
        
        if (isset($data['status'])) {
            $fields[] = 'status = :status';
            $params['status'] = $data['status'];
            
            if ($data['status'] === 'completed') {
                $fields[] = "completed_at = datetime('now')";
            } else {
                $fields[] = 'completed_at = NULL';
            }
        }
        
        $fields[] = "updated_at = datetime('now')";
        
        if (count($fields) === 1) {
            return $task;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . 
               " WHERE id = :id AND user_id = :user_id";
        
        $this->db->query($sql, $params);
        
        return $this->getById($id, $user_id);
    }
    
    /**
     * Toggle task completion status
     */
    public function toggleComplete($id, $user_id) {
        $task = $this->getById($id, $user_id);
        if (!$task) {
            return null;
        }
        
        $newStatus = $task['status'] === 'completed' ? 'pending' : 'completed';
        
        return $this->update($id, $user_id, ['status' => $newStatus]);
    }
    
    /**
     * Delete a task (soft delete)
     */
    public function delete($id, $user_id) {
        $sql = "UPDATE {$this->table} 
                SET is_deleted = 1, updated_at = datetime('now') 
                WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->db->query($sql, [
            'id' => $id,
            'user_id' => $user_id
        ]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete all completed tasks
     */
    public function deleteCompleted($user_id) {
        $sql = "UPDATE {$this->table} 
                SET is_deleted = 1, updated_at = datetime('now') 
                WHERE user_id = :user_id AND status = 'completed'";
        
        $stmt = $this->db->query($sql, ['user_id' => $user_id]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Get task statistics
     */
    public function getStats($user_id) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status != 'completed' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN priority = 'high' AND status != 'completed' THEN 1 ELSE 0 END) as high_priority,
                    SUM(CASE WHEN priority = 'medium' AND status != 'completed' THEN 1 ELSE 0 END) as medium_priority,
                    SUM(CASE WHEN priority = 'low' AND status != 'completed' THEN 1 ELSE 0 END) as low_priority,
                    SUM(CASE WHEN due_date < date('now') AND status != 'completed' THEN 1 ELSE 0 END) as overdue,
                    SUM(CASE WHEN date(completed_at) = date('now') THEN 1 ELSE 0 END) as completed_today
                FROM {$this->table}
                WHERE user_id = :user_id AND is_deleted = 0";
        
        $stmt = $this->db->query($sql, ['user_id' => $user_id]);
        $result = $stmt->fetch();
        
        // Convert nulls to zeros
        foreach ($result as $key => $value) {
            $result[$key] = (int)$value;
        }
        
        return $result;
    }
    
    /**
     * Get category counts
     */
    public function getCategoryCounts($user_id) {
        $sql = "SELECT c.name, c.color, COUNT(t.id) as count
                FROM categories c
                LEFT JOIN {$this->table} t ON c.id = t.category_id 
                    AND t.status != 'completed' 
                    AND t.is_deleted = 0
                WHERE c.user_id = :user_id
                GROUP BY c.id, c.name, c.color
                ORDER BY c.sort_order";
        
        $stmt = $this->db->query($sql, ['user_id' => $user_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get upcoming deadlines
     */
    public function getUpcomingDeadlines($user_id, $limit = 5) {
        $sql = "SELECT t.id, t.title, t.due_date, t.priority, c.name as category
                FROM {$this->table} t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = :user_id 
                    AND t.is_deleted = 0 
                    AND t.status != 'completed'
                    AND t.due_date IS NOT NULL
                ORDER BY t.due_date ASC
                LIMIT :limit";
        
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get category ID by name
     */
    private function getCategoryId($user_id, $category_name) {
        $sql = "SELECT id FROM categories WHERE user_id = :user_id AND name = :name";
        $stmt = $this->db->query($sql, [
            'user_id' => $user_id,
            'name' => $category_name
        ]);
        
        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Sanitize input string
 */
function sanitize($input) {
    if (is_string($input)) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

/**
 * Validate task input
 */
function validateTaskInput($input, $requireTitle = true) {
    $errors = [];
    
    // Title validation
    if ($requireTitle) {
        if (empty($input['title'])) {
            $errors['title'] = 'Title is required';
        } elseif (strlen($input['title']) < 2) {
            $errors['title'] = 'Title must be at least 2 characters';
        } elseif (strlen($input['title']) > 100) {
            $errors['title'] = 'Title must not exceed 100 characters';
        }
    } elseif (isset($input['title'])) {
        if (strlen($input['title']) < 2) {
            $errors['title'] = 'Title must be at least 2 characters';
        } elseif (strlen($input['title']) > 100) {
            $errors['title'] = 'Title must not exceed 100 characters';
        }
    }
    
    // Description validation
    if (isset($input['description']) && strlen($input['description']) > 500) {
        $errors['description'] = 'Description must not exceed 500 characters';
    }
    
    // Priority validation
    if (isset($input['priority'])) {
        $validPriorities = ['low', 'medium', 'high'];
        if (!in_array($input['priority'], $validPriorities)) {
            $errors['priority'] = 'Invalid priority value';
        }
    }
    
    // Category validation
    if (isset($input['category'])) {
        $validCategories = ['personal', 'work', 'study', 'shopping'];
        if (!in_array($input['category'], $validCategories)) {
            $errors['category'] = 'Invalid category value';
        }
    }
    
    // Due date validation
    $dueDate = $input['dueDate'] ?? $input['due_date'] ?? null;
    if (!empty($dueDate)) {
        $date = DateTime::createFromFormat('Y-m-d', $dueDate);
        if (!$date || $date->format('Y-m-d') !== $dueDate) {
            $errors['dueDate'] = 'Invalid date format (use YYYY-MM-DD)';
        }
    }
    
    return $errors;
}

// ============================================
// REQUEST HANDLERS
// ============================================

/**
 * Handle special actions
 */
function handleAction($action, $task, $user_id) {
    switch ($action) {
        case 'stats':
            $stats = $task->getStats($user_id);
            $categories = $task->getCategoryCounts($user_id);
            Response::success([
                'stats' => $stats,
                'categories' => $categories
            ]);
            break;
            
        case 'deadlines':
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $deadlines = $task->getUpcomingDeadlines($user_id, $limit);
            Response::success($deadlines);
            break;
            
        case 'clear':
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                Response::error('Method not allowed', 405);
            }
            $count = $task->deleteCompleted($user_id);
            Response::success(['deleted_count' => $count], "$count completed tasks cleared");
            break;
            
        default:
            Response::error('Invalid action', 400);
    }
}

/**
 * Handle GET requests
 */
function handleGet($task, $user_id, $id) {
    if ($id) {
        $result = $task->getById($id, $user_id);
        
        if (!$result) {
            Response::notFound('Task not found');
        }
        
        Response::success($result);
    } else {
        $filters = [
            'status' => $_GET['status'] ?? 'all',
            'category' => $_GET['category'] ?? 'all',
            'priority' => $_GET['priority'] ?? 'all',
            'search' => $_GET['search'] ?? '',
            'sort_by' => $_GET['sort_by'] ?? 'newest',
        ];
        
        $tasks = $task->getAll($user_id, $filters);
        $stats = $task->getStats($user_id);
        
        Response::success([
            'tasks' => $tasks,
            'stats' => $stats,
            'filters' => $filters
        ]);
    }
}

/**
 * Handle POST requests (Create)
 */
function handlePost($task, $user_id, $input) {
    $errors = validateTaskInput($input);
    
    if (!empty($errors)) {
        Response::validationError($errors);
    }
    
    $data = [
        'user_id' => $user_id,
        'title' => sanitize($input['title']),
        'description' => isset($input['description']) ? sanitize($input['description']) : null,
        'category' => $input['category'] ?? 'personal',
        'priority' => $input['priority'] ?? 'medium',
        'due_date' => $input['dueDate'] ?? $input['due_date'] ?? null
    ];
    
    $newTask = $task->create($data);
    
    Response::created($newTask, 'Task created successfully');
}

/**
 * Handle PUT requests (Update)
 */
function handlePut($task, $user_id, $id, $input) {
    if (!$id) {
        Response::error('Task ID is required', 400);
    }
    
    $existingTask = $task->getById($id, $user_id);
    if (!$existingTask) {
        Response::notFound('Task not found');
    }
    
    $errors = validateTaskInput($input, false);
    
    if (!empty($errors)) {
        Response::validationError($errors);
    }
    
    $data = [];
    
    if (isset($input['title'])) {
        $data['title'] = sanitize($input['title']);
    }
    
    if (isset($input['description'])) {
        $data['description'] = sanitize($input['description']);
    }
    
    if (isset($input['category'])) {
        $data['category'] = $input['category'];
    }
    
    if (isset($input['priority'])) {
        $data['priority'] = $input['priority'];
    }
    
    if (array_key_exists('dueDate', $input) || array_key_exists('due_date', $input)) {
        $data['due_date'] = $input['dueDate'] ?? $input['due_date'] ?? null;
    }
    
    if (isset($input['status'])) {
        $data['status'] = $input['status'];
    }
    
    $updatedTask = $task->update($id, $user_id, $data);
    
    Response::success($updatedTask, 'Task updated successfully');
}

/**
 * Handle PATCH requests (Toggle complete)
 */
function handlePatch($task, $user_id, $id) {
    if (!$id) {
        Response::error('Task ID is required', 400);
    }
    
    $updatedTask = $task->toggleComplete($id, $user_id);
    
    if (!$updatedTask) {
        Response::notFound('Task not found');
    }
    
    $status = $updatedTask['status'] === 'completed' ? 'completed' : 'restored';
    Response::success($updatedTask, "Task $status successfully");
}

/**
 * Handle DELETE requests
 */
function handleDelete($task, $user_id, $id) {
    if (!$id) {
        Response::error('Task ID is required', 400);
    }
    
    $deleted = $task->delete($id, $user_id);
    
    if (!$deleted) {
        Response::notFound('Task not found');
    }
    
    Response::success(null, 'Task deleted successfully');
}

// ============================================
// MAIN EXECUTION
// ============================================
try {
    $task = new Task();
    
    // Default user (in production, get from authentication)
    $user_id = 1;
    
    $method = $_SERVER['REQUEST_METHOD'];
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true) ?? [];
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $action = $_GET['action'] ?? null;
    
    // Handle special actions first
    if ($action) {
        handleAction($action, $task, $user_id);
    }
    
    // Handle CRUD operations
    switch ($method) {
        case 'GET':
            handleGet($task, $user_id, $id);
            break;
            
        case 'POST':
            handlePost($task, $user_id, $input);
            break;
            
        case 'PUT':
            handlePut($task, $user_id, $id, $input);
            break;
            
        case 'PATCH':
            handlePatch($task, $user_id, $id);
            break;
            
        case 'DELETE':
            handleDelete($task, $user_id, $id);
            break;
            
        default:
            Response::error('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    if (ENVIRONMENT === 'development') {
        Response::serverError('Error: ' . $e->getMessage());
    } else {
        Response::serverError('An unexpected error occurred');
    }
}