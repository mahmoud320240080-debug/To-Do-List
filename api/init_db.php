<?php
/**
 * ============================================
 * TASKMASTER - DATABASE INITIALIZATION
 * Creates SQLite database and tables
 * ============================================
 * 
 * Run this file ONCE to create the database:
 * http://localhost/taskmaster/api/init_db.php
 */

// Database file path
$dbPath = __DIR__ . '/../database/taskmaster.db';
$dbDir = __DIR__ . '/../database';

// Create database directory if it doesn't exist
if (!file_exists($dbDir)) {
    mkdir($dbDir, 0755, true);
    echo "✅ Created database directory<br>";
}

try {
    // Create/connect to SQLite database
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to SQLite database<br>";
    
    // ============================================
    // CREATE TABLES
    // ============================================
    
    // Users table
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            first_name TEXT,
            last_name TEXT,
            theme_preference TEXT DEFAULT 'dark',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_active INTEGER DEFAULT 1
        )
    ");
    echo "✅ Created users table<br>";
    
    // Categories table
    $db->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            color TEXT DEFAULT '#7c3aed',
            icon TEXT DEFAULT '📁',
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE (user_id, name)
        )
    ");
    echo "✅ Created categories table<br>";
    
    // Tasks table
    $db->exec("
        CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            category_id INTEGER,
            title TEXT NOT NULL,
            description TEXT,
            priority TEXT DEFAULT 'medium',
            status TEXT DEFAULT 'pending',
            due_date DATE,
            completed_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_deleted INTEGER DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )
    ");
    echo "✅ Created tasks table<br>";
    
    // Activity log table
    $db->exec("
        CREATE TABLE IF NOT EXISTS activity_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            task_id INTEGER,
            action TEXT NOT NULL,
            details TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "✅ Created activity_log table<br>";
    
    // ============================================
    // CREATE INDEXES
    // ============================================
    $db->exec("CREATE INDEX IF NOT EXISTS idx_tasks_user_id ON tasks(user_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_tasks_status ON tasks(status)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_tasks_priority ON tasks(priority)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_tasks_due_date ON tasks(due_date)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_categories_user_id ON categories(user_id)");
    echo "✅ Created indexes<br>";
    
    // ============================================
    // INSERT DEFAULT DATA
    // ============================================
    
    // Check if default user exists
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE username = 'johndoe'");
    $userExists = $stmt->fetchColumn() > 0;
    
    if (!$userExists) {
        // Insert default user (password: password123)
        $db->exec("
            INSERT INTO users (username, email, password_hash, first_name, last_name)
            VALUES ('johndoe', 'john@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe')
        ");
        echo "✅ Created default user (johndoe)<br>";
        
        // Insert default categories
        $db->exec("
            INSERT INTO categories (user_id, name, color, icon, sort_order) VALUES
            (1, 'personal', '#7c3aed', '👤', 1),
            (1, 'work', '#ef4444', '💼', 2),
            (1, 'study', '#f59e0b', '📚', 3),
            (1, 'shopping', '#22c55e', '🛒', 4)
        ");
        echo "✅ Created default categories<br>";
        
        // Insert sample tasks
        $db->exec("
            INSERT INTO tasks (user_id, category_id, title, description, priority, due_date) VALUES
            (1, 2, 'Complete project documentation', 'Write comprehensive documentation for the TaskMaster project', 'high', date('now', '+7 days')),
            (1, 4, 'Buy groceries', 'Milk, bread, eggs, fruits, vegetables', 'medium', date('now', '+1 day')),
            (1, 3, 'Study for exam', 'Review chapters 5-10 for final exam', 'high', date('now', '+3 days')),
            (1, 1, 'Exercise routine', '30 minutes cardio and strength training', 'low', NULL),
            (1, 2, 'Team meeting preparation', 'Prepare slides and agenda for Monday meeting', 'medium', date('now', '+2 days'))
        ");
        echo "✅ Created sample tasks<br>";
    } else {
        echo "ℹ️ Default data already exists<br>";
    }
    
    echo "<br><h2>🎉 Database setup complete!</h2>";
    echo "<p>Database file location: <code>$dbPath</code></p>";
    echo "<p><a href='tasks.php'>Test the API →</a></p>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>