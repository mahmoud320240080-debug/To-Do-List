<?php
/**
 * ============================================
 * TASKMASTER - TASK MODEL
 * ============================================
 */

require_once __DIR__ . '/../Database.php';

class Task {
    private $db;
    private $table = 'tasks';
    
    // Task properties
    public $id;
    public $user_id;
    public $category_id;
    public $title;
    public $description;
    public $priority;
    public $status;
    public $due_date;
    public $completed_at;
    public $created_at;
    public $updated_at;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all tasks for a user with filtering and sorting
     * @param int $user_id
     * @param array $filters
     * @return array
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
        
        // Sorting
        $sortBy = $filters['sort_by'] ?? 'newest';
        switch ($sortBy) {
            case 'oldest':
                $sql .= " ORDER BY t.created_at ASC";
                break;
            case 'priority':
                $sql .= " ORDER BY FIELD(t.priority, 'high', 'medium', 'low'), t.created_at DESC";
                break;
            case 'due_date':
                $sql .= " ORDER BY t.due_date IS NULL, t.due_date ASC, t.created_at DESC";
                break;
            case 'alphabetical':
                $sql .= " ORDER BY t.title ASC";
                break;
            default: // newest
                $sql .= " ORDER BY t.created_at DESC";
        }
        
        // Limit and offset
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get a single task by ID
     * @param int $id
     * @param int $user_id
     * @return array|null
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
     * @param array $data
     * @return array
     */
    public function create($data) {
        // Get category ID
        $category_id = $this->getCategoryId($data['user_id'], $data['category'] ?? 'personal');
        
        $sql = "INSERT INTO {$this->table} 
                (user_id, category_id, title, description, priority, due_date, created_at)
                VALUES 
                (:user_id, :category_id, :title, :description, :priority, :due_date, NOW())";
        
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
     * @param int $id
     * @param int $user_id
     * @param array $data
     * @return array|null
     */
    public function update($id, $user_id, $data) {
        // Check if task exists
        $task = $this->getById($id, $user_id);
        if (!$task) {
            return null;
        }
        
        $fields = [];
        $params = ['id' => $id, 'user_id' => $user_id];
        
        // Build dynamic update query
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
                $fields[] = 'completed_at = NOW()';
            } else {
                $fields[] = 'completed_at = NULL';
            }
        }
        
        $fields[] = 'updated_at = NOW()';
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . 
               " WHERE id = :id AND user_id = :user_id";
        
        $this->db->query($sql, $params);
        
        return $this->getById($id, $user_id);
    }
    
    /**
     * Toggle task completion status
     * @param int $id
     * @param int $user_id
     * @return array|null
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
     * @param int $id
     * @param int $user_id
     * @return bool
     */
    public function delete($id, $user_id) {
        $sql = "UPDATE {$this->table} 
                SET is_deleted = 1, updated_at = NOW() 
                WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->db->query($sql, [
            'id' => $id,
            'user_id' => $user_id
        ]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete all completed tasks
     * @param int $user_id
     * @return int - Number of deleted tasks
     */
    public function deleteCompleted($user_id) {
        $sql = "UPDATE {$this->table} 
                SET is_deleted = 1, updated_at = NOW() 
                WHERE user_id = :user_id AND status = 'completed'";
        
        $stmt = $this->db->query($sql, ['user_id' => $user_id]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Get task statistics
     * @param int $user_id
     * @return array
     */
    public function getStats($user_id) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status != 'completed' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN priority = 'high' AND status != 'completed' THEN 1 ELSE 0 END) as high_priority,
                    SUM(CASE WHEN priority = 'medium' AND status != 'completed' THEN 1 ELSE 0 END) as medium_priority,
                    SUM(CASE WHEN priority = 'low' AND status != 'completed' THEN 1 ELSE 0 END) as low_priority,
                    SUM(CASE WHEN due_date < CURDATE() AND status != 'completed' THEN 1 ELSE 0 END) as overdue,
                    SUM(CASE WHEN DATE(completed_at) = CURDATE() THEN 1 ELSE 0 END) as completed_today
                FROM {$this->table}
                WHERE user_id = :user_id AND is_deleted = 0";
        
        $stmt = $this->db->query($sql, ['user_id' => $user_id]);
        return $stmt->fetch();
    }
    
    /**
     * Get category counts
     * @param int $user_id
     * @return array
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
     * @param int $user_id
     * @param int $limit
     * @return array
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
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get category ID by name
     * @param int $user_id
     * @param string $category_name
     * @return int|null
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