================================================================================
                              TASKMASTER
                      TO-DO LIST WEB APPLICATION
                        PROJECT DOCUMENTATION
================================================================================

                            Prepared by:
                            Mahmoud Amgad Mostafa
                            320240080
                            Omar Mohamed Ziton
                            320240102
                            Yousef Hesham Mostafa
                            320240093
                            
                            Course:
                            Networks and Web Programming
                            
                            Instructor:
                            Dr.Ahmed Antar
                            
                            Date:
                            December 2024

================================================================================
                           TABLE OF CONTENTS
================================================================================

1. INTRODUCTION
   1.1 Project Overview
   1.2 Project Objectives
   1.3 Scope and Limitations

2. SYSTEM REQUIREMENTS
   2.1 Functional Requirements
   2.2 Non-Functional Requirements
   2.3 Hardware Requirements
   2.4 Software Requirements

3. SYSTEM ANALYSIS
   3.1 Current System Analysis
   3.2 Proposed System
   3.3 Feasibility Study

4. SYSTEM DESIGN
   4.1 System Architecture
   4.2 Database Design
   4.3 User Interface Design
   4.4 UML Diagrams

5. IMPLEMENTATION
   5.1 Technologies Used
   5.2 File Structure
   5.3 Key Features Implementation
   5.4 Code Samples

6. TESTING
   6.1 Testing Strategy
   6.2 Test Cases
   6.3 Test Results

7. USER MANUAL
   7.1 Installation Guide
   7.2 User Guide
   7.3 Troubleshooting

8. CONCLUSION
   8.1 Summary
   8.2 Future Enhancements
   8.3 Lessons Learned

9. REFERENCES

10. APPENDICES
    A. Complete Source Code
    B. Database Schema
    C. Screenshots


================================================================================
                            1. INTRODUCTION
================================================================================

1.1 PROJECT OVERVIEW
--------------------

TaskMaster is a modern, full-featured web application designed to help users 
manage their daily tasks efficiently. The application provides an intuitive 
dark-themed interface that allows users to create, organize, track, and 
complete tasks with ease.

The application is built using modern web technologies including HTML5, CSS3, 
JavaScript with jQuery, PHP, and SQLite database. It follows a client-server 
architecture with a RESTful API backend and a responsive single-page 
application frontend.

Key highlights of TaskMaster:
- Clean, modern dark theme user interface
- Full CRUD (Create, Read, Update, Delete) operations
- Real-time filtering and searching
- Data import/export via XML format
- Responsive design for all devices
- Progress tracking and statistics


1.2 PROJECT OBJECTIVES
----------------------

The main objectives of this project are:

Primary Objectives:
1. Design and implement a professional web application
2. Create responsive and accessible user interfaces using HTML and CSS
3. Implement client-side interactivity with JavaScript and jQuery
4. Develop server-side functionality using PHP
5. Work with databases for persistent data storage
6. Handle data interchange using JSON and XML formats
7. Apply AJAX for asynchronous server communication

Secondary Objectives:
1. Practice modern web development best practices
2. Implement form validation (client-side and server-side)
3. Create a RESTful API architecture
4. Design an intuitive user experience
5. Ensure cross-browser compatibility


1.3 SCOPE AND LIMITATIONS
-------------------------

Scope (What the application does):
- User can add new tasks with title, description, category, priority, and due date
- User can view all tasks in an organized list
- User can edit existing task details
- User can mark tasks as completed
- User can delete tasks
- User can filter tasks by status, category, and priority
- User can search tasks by keyword
- User can sort tasks by various criteria
- User can export tasks to XML format
- User can import tasks from XML files
- User can toggle between dark and light themes
- User can view task statistics and progress

Limitations:
- Single user system (no authentication/registration)
- No email notifications or reminders
- No task sharing or collaboration features
- No mobile native application
- No cloud synchronization
- No recurring task functionality


================================================================================
                         2. SYSTEM REQUIREMENTS
================================================================================

2.1 FUNCTIONAL REQUIREMENTS
---------------------------

The following table lists all functional requirements of the system:

+------+---------------------------+------------------------------------------+
| ID   | Requirement               | Description                              |
+------+---------------------------+------------------------------------------+
| FR01 | Add Task                  | User shall be able to create new tasks   |
|      |                           | with title, category, priority, and      |
|      |                           | optional due date                        |
+------+---------------------------+------------------------------------------+
| FR02 | View Tasks                | User shall be able to view all tasks     |
|      |                           | in a list format                         |
+------+---------------------------+------------------------------------------+
| FR03 | Edit Task                 | User shall be able to modify existing    |
|      |                           | task details                             |
+------+---------------------------+------------------------------------------+
| FR04 | Delete Task               | User shall be able to remove tasks       |
|      |                           | from the system                          |
+------+---------------------------+------------------------------------------+
| FR05 | Complete Task             | User shall be able to mark tasks as      |
|      |                           | completed                                |
+------+---------------------------+------------------------------------------+
| FR06 | Filter Tasks              | User shall be able to filter tasks by    |
|      |                           | status (all/active/completed)            |
+------+---------------------------+------------------------------------------+
| FR07 | Category Filter           | User shall be able to filter tasks by    |
|      |                           | category (personal/work/study/shopping)  |
+------+---------------------------+------------------------------------------+
| FR08 | Search Tasks              | User shall be able to search tasks by    |
|      |                           | keywords in title or description         |
+------+---------------------------+------------------------------------------+
| FR09 | Sort Tasks                | User shall be able to sort tasks by      |
|      |                           | date, priority, or alphabetically        |
+------+---------------------------+------------------------------------------+
| FR10 | Export to XML             | User shall be able to export all tasks   |
|      |                           | to an XML file                           |
+------+---------------------------+------------------------------------------+
| FR11 | Import from XML           | User shall be able to import tasks from  |
|      |                           | an XML file                              |
+------+---------------------------+------------------------------------------+
| FR12 | View Statistics           | User shall be able to view task          |
|      |                           | completion statistics                    |
+------+---------------------------+------------------------------------------+
| FR13 | Theme Toggle              | User shall be able to switch between     |
|      |                           | dark and light themes                    |
+------+---------------------------+------------------------------------------+
| FR14 | Form Validation           | System shall validate all user inputs    |
|      |                           | before processing                        |
+------+---------------------------+------------------------------------------+
| FR15 | Toast Notifications       | System shall display feedback messages   |
|      |                           | for user actions                         |
+------+---------------------------+------------------------------------------+


2.2 NON-FUNCTIONAL REQUIREMENTS
-------------------------------

+------+---------------------------+------------------------------------------+
| ID   | Requirement               | Description                              |
+------+---------------------------+------------------------------------------+
| NF01 | Performance               | Page should load within 3 seconds        |
+------+---------------------------+------------------------------------------+
| NF02 | Responsiveness            | Application should work on all screen    |
|      |                           | sizes (desktop, tablet, mobile)          |
+------+---------------------------+------------------------------------------+
| NF03 | Browser Compatibility     | Should work on Chrome, Firefox, Safari,  |
|      |                           | and Edge browsers                        |
+------+---------------------------+------------------------------------------+
| NF04 | Usability                 | Interface should be intuitive and easy   |
|      |                           | to use without training                  |
+------+---------------------------+------------------------------------------+
| NF05 | Reliability               | System should handle errors gracefully   |
|      |                           | without crashing                         |
+------+---------------------------+------------------------------------------+
| NF06 | Data Integrity            | Data should be safely stored and not     |
|      |                           | lost during operations                   |
+------+---------------------------+------------------------------------------+
| NF07 | Security                  | User inputs should be sanitized to       |
|      |                           | prevent XSS attacks                      |
+------+---------------------------+------------------------------------------+
| NF08 | Maintainability           | Code should be well-organized and        |
|      |                           | documented for future updates            |
+------+---------------------------+------------------------------------------+


2.3 HARDWARE REQUIREMENTS
-------------------------

Minimum Requirements:
- Processor: 1 GHz or faster
- RAM: 2 GB minimum
- Storage: 100 MB free space
- Display: 1024 x 768 resolution
- Network: Internet connection (for CDN resources)

Recommended Requirements:
- Processor: 2 GHz dual-core or faster
- RAM: 4 GB or more
- Storage: 500 MB free space
- Display: 1920 x 1080 resolution


2.4 SOFTWARE REQUIREMENTS
-------------------------

Development Environment:
- Operating System: Windows 10/11, macOS 10.14+, or Linux
- Web Server: Apache 2.4+
- PHP: Version 8.0 or higher
- Database: SQLite 3.x
- Code Editor: VS Code, Sublime Text, or similar

Client Requirements:
- Web Browser: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- JavaScript: Enabled
- Cookies: Enabled

Development Stack:
- XAMPP (Apache + PHP + SQLite)
- jQuery 3.7.1
- Google Fonts (Poppins)


================================================================================
                          3. SYSTEM ANALYSIS
================================================================================

3.1 CURRENT SYSTEM ANALYSIS
---------------------------

Problem Statement:
Many individuals struggle to manage their daily tasks effectively. Common 
issues include:

1. Forgetting important tasks
2. Difficulty prioritizing work
3. No organized system to track progress
4. Using scattered notes or papers that get lost
5. No way to categorize different types of tasks
6. Inability to see overall progress

Traditional methods like paper to-do lists have limitations:
- Easy to lose or damage
- Cannot be searched
- No automatic organization
- No progress tracking
- Cannot be accessed from multiple locations


3.2 PROPOSED SYSTEM
-------------------

TaskMaster addresses these problems by providing:

1. Centralized Task Management
   - All tasks stored in one place
   - Accessible from any device with a browser

2. Organization Features
   - Categories to group related tasks
   - Priority levels to identify urgency
   - Due dates for deadline tracking

3. Progress Tracking
   - Visual progress indicators
   - Statistics dashboard
   - Completion history

4. Search and Filter
   - Quick search functionality
   - Multiple filter options
   - Sorting capabilities

5. Data Portability
   - Export to XML for backup
   - Import from XML for restoration

System Flow:
User → Browser → Frontend (HTML/CSS/JS) → AJAX → Backend (PHP) → Database (SQLite)


3.3 FEASIBILITY STUDY
---------------------

Technical Feasibility:
- Technologies required are well-established and documented
- Development tools are freely available
- Skills required are covered in the course curriculum
- All components can be developed within the given timeframe
Result: FEASIBLE

Economic Feasibility:
- All software tools used are free/open-source
- No hosting costs (local development)
- No licensing fees required
Result: FEASIBLE

Operational Feasibility:
- Simple, intuitive interface requires no training
- Can be easily maintained and updated
- Users familiar with web applications will adapt quickly
Result: FEASIBLE

Overall Conclusion: The project is FEASIBLE to implement.


================================================================================
                           4. SYSTEM DESIGN
================================================================================

4.1 SYSTEM ARCHITECTURE
-----------------------

TaskMaster follows a three-tier architecture:

+------------------------------------------------------------------+
|                      PRESENTATION LAYER                           |
|                     (Client-Side/Browser)                         |
|                                                                   |
|   +------------------+  +------------------+  +----------------+  |
|   |    HTML5         |  |     CSS3         |  |   JavaScript   |  |
|   |   (Structure)    |  |    (Styling)     |  |   + jQuery     |  |
|   +------------------+  +------------------+  +----------------+  |
|                                                                   |
+------------------------------------------------------------------+
                               |
                               | HTTP/AJAX (JSON/XML)
                               |
+------------------------------------------------------------------+
|                       APPLICATION LAYER                           |
|                        (Server-Side)                              |
|                                                                   |
|   +------------------+  +------------------+  +----------------+  |
|   |   tasks.php      |  | xml_handler.php  |  |  init_db.php   |  |
|   |   (REST API)     |  |  (XML Export/    |  |  (Database     |  |
|   |                  |  |   Import)        |  |   Setup)       |  |
|   +------------------+  +------------------+  +----------------+  |
|                                                                   |
+------------------------------------------------------------------+
                               |
                               | PDO (SQL)
                               |
+------------------------------------------------------------------+
|                         DATA LAYER                                |
|                        (Database)                                 |
|                                                                   |
|   +----------------------------------------------------------+   |
|   |                    SQLite Database                        |   |
|   |                    (taskmaster.db)                        |   |
|   |                                                           |   |
|   |    +--------+  +------------+  +-------+  +----------+   |   |
|   |    | users  |  | categories |  | tasks |  | activity |   |   |
|   |    +--------+  +------------+  +-------+  +----------+   |   |
|   +----------------------------------------------------------+   |
|                                                                   |
+------------------------------------------------------------------+


Data Flow:

1. User interacts with the web interface (HTML/CSS/JS)
2. JavaScript captures user actions and validates input
3. AJAX requests are sent to PHP backend
4. PHP processes requests and interacts with database
5. Database returns data to PHP
6. PHP formats response as JSON/XML
7. JavaScript receives response and updates UI
8. User sees the result


4.2 DATABASE DESIGN
-------------------

Entity-Relationship Description:

ENTITIES:

1. Users
   - Stores user account information
   - Primary Key: id
   - Contains: username, email, password, preferences

2. Categories
   - Stores task categories
   - Primary Key: id
   - Foreign Key: user_id (references Users)
   - Contains: name, color, icon

3. Tasks
   - Stores task information
   - Primary Key: id
   - Foreign Keys: user_id (references Users), category_id (references Categories)
   - Contains: title, description, priority, status, due_date, timestamps

4. Activity_Log
   - Stores action history
   - Primary Key: id
   - Foreign Keys: user_id (references Users), task_id (references Tasks)
   - Contains: action type, details, timestamp


RELATIONSHIPS:

+------------------+                  +------------------+
|      USERS       |                  |    CATEGORIES    |
+------------------+                  +------------------+
| PK: id           |<----+      +---->| PK: id           |
| username         |     |      |     | FK: user_id      |
| email            |     |      |     | name             |
| password_hash    |     |      |     | color            |
| theme_preference |     |      |     | icon             |
| created_at       |     |      |     +------------------+
+------------------+     |      |            |
        |                |      |            | 1
        | 1              |      |            |
        |                |      |            | *
        | *              |      |     +------------------+
+------------------+     |      +-----+      TASKS       |
|  ACTIVITY_LOG    |     +----------->+------------------+
+------------------+                  | PK: id           |
| PK: id           |                  | FK: user_id      |
| FK: user_id      |                  | FK: category_id  |
| FK: task_id      |<-----------------| title            |
| action           |                  | description      |
| details          |                  | priority         |
| created_at       |                  | status           |
+------------------+                  | due_date         |
                                      | completed_at     |
                                      | created_at       |
                                      | is_deleted       |
                                      +------------------+

Relationship Summary:
- One User has Many Categories (1:N)
- One User has Many Tasks (1:N)
- One Category has Many Tasks (1:N)
- One User has Many Activity Logs (1:N)
- One Task has Many Activity Logs (1:N)


DATABASE TABLES SPECIFICATION:

Table: users
+-------------------+----------+----------------------------------+
| Column            | Type     | Constraints                      |
+-------------------+----------+----------------------------------+
| id                | INTEGER  | PRIMARY KEY, AUTO_INCREMENT      |
| username          | TEXT     | NOT NULL, UNIQUE                 |
| email             | TEXT     | NOT NULL, UNIQUE                 |
| password_hash     | TEXT     | NOT NULL                         |
| first_name        | TEXT     | NULLABLE                         |
| last_name         | TEXT     | NULLABLE                         |
| theme_preference  | TEXT     | DEFAULT 'dark'                   |
| created_at        | DATETIME | DEFAULT CURRENT_TIMESTAMP        |
| updated_at        | DATETIME | DEFAULT CURRENT_TIMESTAMP        |
| is_active         | INTEGER  | DEFAULT 1                        |
+-------------------+----------+----------------------------------+

Table: categories
+-------------------+----------+----------------------------------+
| Column            | Type     | Constraints                      |
+-------------------+----------+----------------------------------+
| id                | INTEGER  | PRIMARY KEY, AUTO_INCREMENT      |
| user_id           | INTEGER  | FOREIGN KEY -> users(id)         |
| name              | TEXT     | NOT NULL                         |
| color             | TEXT     | DEFAULT '#7c3aed'                |
| icon              | TEXT     | DEFAULT '📁'                     |
| sort_order        | INTEGER  | DEFAULT 0                        |
| created_at        | DATETIME | DEFAULT CURRENT_TIMESTAMP        |
+-------------------+----------+----------------------------------+

Table: tasks
+-------------------+----------+----------------------------------+
| Column            | Type     | Constraints                      |
+-------------------+----------+----------------------------------+
| id                | INTEGER  | PRIMARY KEY, AUTO_INCREMENT      |
| user_id           | INTEGER  | FOREIGN KEY -> users(id)         |
| category_id       | INTEGER  | FOREIGN KEY -> categories(id)    |
| title             | TEXT     | NOT NULL                         |
| description       | TEXT     | NULLABLE                         |
| priority          | TEXT     | DEFAULT 'medium'                 |
| status            | TEXT     | DEFAULT 'pending'                |
| due_date          | DATE     | NULLABLE                         |
| completed_at      | DATETIME | NULLABLE                         |
| created_at        | DATETIME | DEFAULT CURRENT_TIMESTAMP        |
| updated_at        | DATETIME | DEFAULT CURRENT_TIMESTAMP        |
| is_deleted        | INTEGER  | DEFAULT 0                        |
+-------------------+----------+----------------------------------+

Table: activity_log
+-------------------+----------+----------------------------------+
| Column            | Type     | Constraints                      |
+-------------------+----------+----------------------------------+
| id                | INTEGER  | PRIMARY KEY, AUTO_INCREMENT      |
| user_id           | INTEGER  | FOREIGN KEY -> users(id)         |
| task_id           | INTEGER  | NULLABLE                         |
| action            | TEXT     | NOT NULL                         |
| details           | TEXT     | NULLABLE                         |
| created_at        | DATETIME | DEFAULT CURRENT_TIMESTAMP        |
+-------------------+----------+----------------------------------+


4.3 USER INTERFACE DESIGN
-------------------------

The application interface is divided into three main sections:

+------------------------------------------------------------------------+
|                           HEADER BAR                                    |
|  [Logo]              [Search Box]              [Notifications] [Theme]  |
+------------------------------------------------------------------------+
|          |                                    |                         |
|          |                                    |                         |
|  LEFT    |          MAIN CONTENT              |    RIGHT                |
| SIDEBAR  |            AREA                    |   SIDEBAR               |
|          |                                    |                         |
|  - Menu  |    - Add Task Form                 |  - Progress Ring        |
|  - Cats  |    - Filter Tabs                   |  - Statistics           |
|  - XML   |    - Task List                     |  - Deadlines            |
|          |    - Completed Section             |  - Summary              |
|          |                                    |                         |
|          |                                    |                         |
+------------------------------------------------------------------------+

Color Scheme (Dark Theme):
- Background Primary: #12141c
- Background Secondary: #1a1d2e
- Background Card: #252a3d
- Accent Primary: #7c3aed (Purple)
- Accent Secondary: #00d4ff (Cyan)
- Text Primary: #ffffff
- Text Secondary: #94a3b8
- Priority High: #ef4444 (Red)
- Priority Medium: #f59e0b (Orange)
- Priority Low: #22c55e (Green)

Typography:
- Font Family: Poppins (Google Fonts)
- Headings: 600-700 weight
- Body: 400-500 weight
- Sizes: 0.75rem to 2rem


4.4 UML DIAGRAMS
----------------

USE CASE DIAGRAM:

                    +------------------------------------------+
                    |          TaskMaster System               |
                    |                                          |
     +------+       |   +-------------+                        |
     |      |-------+-->| Add Task    |                        |
     |      |       |   +-------------+                        |
     |      |       |                                          |
     |      |-------+-->+-------------+                        |
     |      |       |   | Edit Task   |                        |
     |      |       |   +-------------+                        |
     |      |       |                                          |
     | USER |-------+-->+-------------+                        |
     |      |       |   | Delete Task |                        |
     |      |       |   +-------------+                        |
     |      |       |                                          |
     |      |-------+-->+---------------+                      |
     |      |       |   | Complete Task |                      |
     |      |       |   +---------------+                      |
     |      |       |                                          |
     |      |-------+-->+--------------+                       |
     |      |       |   | Filter Tasks |                       |
     |      |       |   +--------------+                       |
     |      |       |                                          |
     |      |-------+-->+--------------+                       |
     |      |       |   | Search Tasks |                       |
     |      |       |   +--------------+                       |
     |      |       |                                          |
     |      |-------+-->+----------------+                     |
     |      |       |   | Export to XML  |                     |
     |      |       |   +----------------+                     |
     |      |       |                                          |
     |      |-------+-->+------------------+                   |
     |      |       |   | Import from XML  |                   |
     +------+       |   +------------------+                   |
                    |                                          |
                    +------------------------------------------+


CLASS DIAGRAM (Simplified):

+---------------------------+
|        TaskMaster         |
+---------------------------+
| - CONFIG: Object          |
| - state: Object           |
| - DOM: Object             |
+---------------------------+
| + init()                  |
| + fetchTasks()            |
| + createTaskAPI()         |
| + updateTaskAPI()         |
| + deleteTaskAPI()         |
| + renderTasks()           |
| + showToast()             |
+---------------------------+
            |
            | uses
            v
+---------------------------+        +---------------------------+
|        Validator          |        |         Database          |
+---------------------------+        +---------------------------+
| - rules: Object           |        | - connection: PDO         |
+---------------------------+        +---------------------------+
| + validateField()         |        | + getInstance()           |
| + validateTaskForm()      |        | + query()                 |
| + sanitizeString()        |        | + lastInsertId()          |
+---------------------------+        +---------------------------+
                                                |
                                                | uses
                                                v
                                     +---------------------------+
                                     |           Task            |
                                     +---------------------------+
                                     | - db: Database            |
                                     | - table: string           |
                                     +---------------------------+
                                     | + getAll()                |
                                     | + getById()               |
                                     | + create()                |
                                     | + update()                |
                                     | + delete()                |
                                     | + getStats()              |
                                     +---------------------------+


SEQUENCE DIAGRAM - Add Task:

  User          Frontend        API           Database
    |               |            |                |
    | 1. Fill form  |            |                |
    |-------------->|            |                |
    |               |            |                |
    | 2. Click Add  |            |                |
    |-------------->|            |                |
    |               |            |                |
    |               | 3. Validate|                |
    |               |-----+      |                |
    |               |     |      |                |
    |               |<----+      |                |
    |               |            |                |
    |               | 4. POST    |                |
    |               |----------->|                |
    |               |            |                |
    |               |            | 5. INSERT      |
    |               |            |--------------->|
    |               |            |                |
    |               |            | 6. Success     |
    |               |            |<---------------|
    |               |            |                |
    |               | 7. Response|                |
    |               |<-----------|                |
    |               |            |                |
    | 8. Update UI  |            |                |
    |<--------------|            |                |
    |               |            |                |


================================================================================
                          5. IMPLEMENTATION
================================================================================

5.1 TECHNOLOGIES USED
---------------------

Frontend Technologies:

+----------------+----------+------------------------------------------+
| Technology     | Version  | Purpose                                  |
+----------------+----------+------------------------------------------+
| HTML5          | -        | Page structure and semantic markup       |
+----------------+----------+------------------------------------------+
| CSS3           | -        | Styling, animations, responsive design   |
+----------------+----------+------------------------------------------+
| JavaScript     | ES6+     | Client-side logic and DOM manipulation   |
+----------------+----------+------------------------------------------+
| jQuery         | 3.7.1    | Simplified DOM manipulation and AJAX     |
+----------------+----------+------------------------------------------+
| Google Fonts   | -        | Typography (Poppins font family)         |
+----------------+----------+------------------------------------------+

Backend Technologies:

+----------------+----------+------------------------------------------+
| Technology     | Version  | Purpose                                  |
+----------------+----------+------------------------------------------+
| PHP            | 8.0+     | Server-side processing and API           |
+----------------+----------+------------------------------------------+
| SQLite         | 3.x      | Database storage                         |
+----------------+----------+------------------------------------------+
| PDO            | -        | Database abstraction layer               |
+----------------+----------+------------------------------------------+

Data Formats:

+----------------+------------------------------------------+
| Format         | Purpose                                  |
+----------------+------------------------------------------+
| JSON           | API data interchange                     |
+----------------+------------------------------------------+
| XML            | Data import/export                       |
+----------------+------------------------------------------+


5.2 FILE STRUCTURE
------------------

taskmaster/
│
├── index.html                 Main HTML file
│
├── css/
│   └── style.css             Main stylesheet (1800+ lines)
│
├── js/
│   ├── app.js                Main application logic (900+ lines)
│   └── validation.js         Form validation module (200+ lines)
│
├── api/
│   ├── tasks.php             RESTful API endpoint (500+ lines)
│   ├── xml_handler.php       XML import/export handler
│   └── init_db.php           Database initialization script
│
├── database/
│   └── taskmaster.db         SQLite database file
│
├── data/
│   └── tasks.xml             Sample XML data file
│
└── docs/
    └── PROJECT_DOCUMENTATION  This document


5.3 KEY FEATURES IMPLEMENTATION
-------------------------------

A. RESPONSIVE DESIGN

The application uses CSS media queries to adapt to different screen sizes:

- Desktop (>1200px): Three-column layout with both sidebars visible
- Tablet (768px-1200px): Two-column layout, right sidebar hidden
- Mobile (<768px): Single-column layout, hamburger menu for navigation

CSS Breakpoints:
@media screen and (max-width: 1200px) { ... }
@media screen and (max-width: 992px) { ... }
@media screen and (max-width: 768px) { ... }
@media screen and (max-width: 480px) { ... }


B. AJAX IMPLEMENTATION

All API calls use jQuery AJAX for asynchronous communication:

Example - Fetching Tasks:
$.ajax({
    url: 'api/tasks.php',
    method: 'GET',
    dataType: 'json'
})
.done(function(response) {
    // Handle success
})
.fail(function(error) {
    // Handle error
});


C. FORM VALIDATION

Both client-side and server-side validation are implemented:

Client-side (JavaScript):
- Title: Required, 2-100 characters
- Description: Optional, max 500 characters
- Priority: Must be 'low', 'medium', or 'high'
- Category: Must be valid category name
- Due Date: Must be valid date format

Server-side (PHP):
- All inputs sanitized to prevent XSS
- Validation repeated before database operations
- Error messages returned in JSON format


D. DATABASE OPERATIONS

CRUD operations implemented using PDO prepared statements:

Create:
INSERT INTO tasks (user_id, title, priority, ...) VALUES (?, ?, ?, ...)

Read:
SELECT * FROM tasks WHERE user_id = ? AND is_deleted = 0

Update:
UPDATE tasks SET title = ?, priority = ? WHERE id = ? AND user_id = ?

Delete (Soft):
UPDATE tasks SET is_deleted = 1 WHERE id = ? AND user_id = ?


E. XML HANDLING

Export to XML:
- PHP DOMDocument used to create XML structure
- Tasks and categories exported with metadata
- File can be downloaded or saved to server

Import from XML:
- SimpleXML used to parse uploaded files
- Validation of XML structure
- Tasks inserted into database with transaction


5.4 CODE SAMPLES
----------------

Sample 1: Task Creation (JavaScript)

function handleAddTask(e) {
    e.preventDefault();
    
    const taskData = {
        title: $('#task-title').val().trim(),
        category: $('#task-category').val(),
        priority: $('#task-priority').val(),
        dueDate: $('#task-due-date').val() || null
    };
    
    // Validate
    if (!taskData.title || taskData.title.length < 2) {
        showFormError('Title must be at least 2 characters');
        return;
    }
    
    // Send to API
    $.ajax({
        url: 'api/tasks.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(taskData),
        dataType: 'json'
    })
    .done(function(response) {
        if (response.success) {
            showToast('success', 'Task Added', 'Task created successfully');
            fetchTasks(); // Refresh list
        }
    });
}


Sample 2: API Endpoint (PHP)

// Handle POST request - Create Task
function handlePost($task, $user_id, $input) {
    // Validation
    $errors = validateTaskInput($input);
    if (!empty($errors)) {
        Response::validationError($errors);
    }
    
    // Sanitize
    $data = [
        'user_id' => $user_id,
        'title' => sanitize($input['title']),
        'category' => $input['category'] ?? 'personal',
        'priority' => $input['priority'] ?? 'medium',
        'due_date' => $input['dueDate'] ?? null
    ];
    
    // Create task
    $newTask = $task->create($data);
    
    Response::created($newTask, 'Task created successfully');
}


Sample 3: Database Query (PHP)

public function create($data) {
    $category_id = $this->getCategoryId($data['user_id'], $data['category']);
    
    $sql = "INSERT INTO tasks 
            (user_id, category_id, title, description, priority, due_date)
            VALUES 
            (:user_id, :category_id, :title, :description, :priority, :due_date)";
    
    $this->db->query($sql, [
        'user_id' => $data['user_id'],
        'category_id' => $category_id,
        'title' => $data['title'],
        'description' => $data['description'] ?? null,
        'priority' => $data['priority'] ?? 'medium',
        'due_date' => $data['due_date']
    ]);
    
    return $this->getById($this->db->lastInsertId(), $data['user_id']);
}


================================================================================
                              6. TESTING
================================================================================

6.1 TESTING STRATEGY
--------------------

Testing Approach:
- Manual testing of all features
- Browser compatibility testing
- Responsive design testing
- API endpoint testing
- Form validation testing

Testing Environment:
- Browser: Google Chrome 120+
- Server: XAMPP with Apache and PHP 8.0
- Database: SQLite 3
- Operating System: Windows 11


6.2 TEST CASES
--------------

+------+---------------------+--------------------------------+----------------+
| ID   | Test Case           | Steps                          | Expected Result|
+------+---------------------+--------------------------------+----------------+
| TC01 | Add Task            | 1. Enter title                 | Task appears   |
|      |                     | 2. Select category             | in list with   |
|      |                     | 3. Click Add button            | success toast  |
+------+---------------------+--------------------------------+----------------+
| TC02 | Add Task -          | 1. Leave title empty           | Error message  |
|      | Empty Title         | 2. Click Add button            | displayed      |
+------+---------------------+--------------------------------+----------------+
| TC03 | Add Task -          | 1. Enter 1 character           | Error message  |
|      | Short Title         | 2. Click Add button            | displayed      |
+------+---------------------+--------------------------------+----------------+
| TC04 | Edit Task           | 1. Click edit button           | Modal opens,   |
|      |                     | 2. Change title                | task updates   |
|      |                     | 3. Click Save                  | successfully   |
+------+---------------------+--------------------------------+----------------+
| TC05 | Delete Task         | 1. Click delete button         | Confirmation   |
|      |                     | 2. Click Confirm               | modal, task    |
|      |                     |                                | removed        |
+------+---------------------+--------------------------------+----------------+
| TC06 | Complete Task       | 1. Click checkbox              | Task moves to  |
|      |                     |                                | completed      |
+------+---------------------+--------------------------------+----------------+
| TC07 | Filter - Active     | 1. Click Active tab            | Only pending   |
|      |                     |                                | tasks shown    |
+------+---------------------+--------------------------------+----------------+
| TC08 | Filter - Completed  | 1. Click Completed tab         | Only completed |
|      |                     |                                | tasks shown    |
+------+---------------------+--------------------------------+----------------+
| TC09 | Filter - Category   | 1. Click category in sidebar   | Tasks filtered |
|      |                     |                                | by category    |
+------+---------------------+--------------------------------+----------------+
| TC10 | Search              | 1. Type in search box          | Tasks filtered |
|      |                     |                                | by keyword     |
+------+---------------------+--------------------------------+----------------+
| TC11 | Sort                | 1. Select sort option          | Tasks reordered|
+------+---------------------+--------------------------------+----------------+
| TC12 | Export XML          | 1. Click Export to XML         | XML file       |
|      |                     |                                | downloads      |
+------+---------------------+--------------------------------+----------------+
| TC13 | Import XML          | 1. Click Import from XML       | Tasks imported |
|      |                     | 2. Select valid XML file       | from file      |
+------+---------------------+--------------------------------+----------------+
| TC14 | Theme Toggle        | 1. Click theme button          | Theme switches |
+------+---------------------+--------------------------------+----------------+
| TC15 | Responsive -        | 1. Resize browser to mobile    | Layout adapts  |
|      | Mobile              |                                | correctly      |
+------+---------------------+--------------------------------+----------------+


6.3 TEST RESULTS
----------------

+------+---------------------+--------+--------------------------------+
| ID   | Test Case           | Status | Notes                          |
+------+---------------------+--------+--------------------------------+
| TC01 | Add Task            | PASS   | Task created successfully      |
+------+---------------------+--------+--------------------------------+
| TC02 | Add Task - Empty    | PASS   | Validation error shown         |
+------+---------------------+--------+--------------------------------+
| TC03 | Add Task - Short    | PASS   | Validation error shown         |
+------+---------------------+--------+--------------------------------+
| TC04 | Edit Task           | PASS   | Task updated correctly         |
+------+---------------------+--------+--------------------------------+
| TC05 | Delete Task         | PASS   | Task removed from list         |
+------+---------------------+--------+--------------------------------+
| TC06 | Complete Task       | PASS   | Status changed successfully    |
+------+---------------------+--------+--------------------------------+
| TC07 | Filter - Active     | PASS   | Correct tasks displayed        |
+------+---------------------+--------+--------------------------------+
| TC08 | Filter - Completed  | PASS   | Correct tasks displayed        |
+------+---------------------+--------+--------------------------------+
| TC09 | Filter - Category   | PASS   | Correct tasks displayed        |
+------+---------------------+--------+--------------------------------+
| TC10 | Search              | PASS   | Search works correctly         |
+------+---------------------+--------+--------------------------------+
| TC11 | Sort                | PASS   | All sort options work          |
+------+---------------------+--------+--------------------------------+
| TC12 | Export XML          | PASS   | Valid XML file generated       |
+------+---------------------+--------+--------------------------------+
| TC13 | Import XML          | PASS   | Tasks imported correctly       |
+------+---------------------+--------+--------------------------------+
| TC14 | Theme Toggle        | PASS   | Theme switches correctly       |
+------+---------------------+--------+--------------------------------+
| TC15 | Responsive          | PASS   | Layout adapts on all sizes     |
+------+---------------------+--------+--------------------------------+

Overall Test Results: 15/15 PASSED (100%)


================================================================================
                            7. USER MANUAL
================================================================================

7.1 INSTALLATION GUIDE
----------------------

Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Run the installer and follow the prompts
3. Install to default location (C:\xampp on Windows)

Step 2: Copy Project Files
1. Download or extract the TaskMaster project folder
2. Copy the entire "taskmaster" folder to:
   - Windows: C:\xampp\htdocs\
   - Mac: /Applications/XAMPP/htdocs/
   - Linux: /opt/lampp/htdocs/

Step 3: Start Apache
1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Wait until Apache status shows "Running" (green)
4. Note: MySQL is NOT required (using SQLite)

Step 4: Initialize Database
1. Open web browser
2. Navigate to: http://localhost/taskmaster/api/init_db.php
3. Wait for success messages
4. You should see:
   - "Created database directory"
   - "Created users table"
   - "Created categories table"
   - "Created tasks table"
   - "Database setup complete!"

Step 5: Access Application
1. Open web browser
2. Navigate to: http://localhost/taskmaster/
3. The application should load with sample tasks


7.2 USER GUIDE
--------------

ADDING A NEW TASK:

1. Locate the "What's on your mind today?" input field
2. Type your task title (minimum 2 characters)
3. Click the category dropdown and select:
   - Personal (for personal errands)
   - Work (for job-related tasks)
   - Study (for educational tasks)
   - Shopping (for shopping lists)
4. Click the priority dropdown and select:
   - Low (green dot) - not urgent
   - Medium (yellow dot) - normal priority
   - High (red dot) - urgent tasks
5. Optionally, click the calendar icon to set a due date
6. Click the "Add +" button
7. Your task will appear in the list below


EDITING A TASK:

1. Hover your mouse over the task you want to edit
2. Click the pencil (✏️) icon that appears on the right
3. A modal dialog will open with the task details
4. Modify any fields you wish to change:
   - Title
   - Description
   - Category
   - Priority
   - Due Date
5. Click "Save Changes" to update the task
6. Click "Cancel" or the X button to discard changes


COMPLETING A TASK:

1. Find the task in your list
2. Click the circular checkbox on the left side of the task
3. The task will be marked with a strikethrough
4. The task moves to the "Completed" section at the bottom
5. To undo, click the checkbox again


DELETING A TASK:

1. Hover your mouse over the task
2. Click the trash can (🗑️) icon
3. A confirmation dialog will appear
4. Click "Delete" to confirm removal
5. Click "Cancel" to keep the task


FILTERING TASKS:

By Status:
1. Look at the tabs above the task list
2. Click "All Tasks" to see everything
3. Click "Active" to see only pending tasks
4. Click "Completed" to see only finished tasks

By Category:
1. Look at the left sidebar under "COLLECTIONS"
2. Click on a category name (Personal, Work, Study, Shopping)
3. Only tasks from that category will be displayed


SEARCHING TASKS:

1. Find the search box in the header (magnifying glass icon)
2. Type your search term
3. Tasks will filter in real-time as you type
4. Search looks in both title and description
5. Clear the search box to show all tasks again


SORTING TASKS:

1. Find the "Sort by" dropdown in the task list section
2. Select your preferred sort order:
   - Newest First: Most recently created at top
   - Oldest First: Oldest tasks at top
   - Priority: High priority first
   - Due Date: Earliest deadline first
   - A-Z: Alphabetical order


EXPORTING TO XML:

1. Find "DATA MANAGEMENT" section in left sidebar
2. Click "Export to XML"
3. An XML file will download automatically
4. File contains all your tasks with metadata
5. You can use this file for backup


IMPORTING FROM XML:

1. Find "DATA MANAGEMENT" section in left sidebar
2. Click "Import from XML"
3. A file selection dialog will open
4. Select a valid TaskMaster XML file
5. Tasks from the file will be added to your list


CHANGING THEME:

1. Find the moon (🌙) icon in the header
2. Click it to switch to light theme
3. The icon changes to sun (☀️)
4. Click again to switch back to dark theme
5. Your preference is saved automatically


KEYBOARD SHORTCUTS:

+------------------+----------------------------------------+
| Key              | Action                                 |
+------------------+----------------------------------------+
| N                | Focus on new task input field          |
| /                | Focus on search box                    |
| 1                | Show all tasks                         |
| 2                | Show active tasks only                 |
| 3                | Show completed tasks only              |
| Esc              | Close any open modal/dialog            |
+------------------+----------------------------------------+


7.3 TROUBLESHOOTING
-------------------

PROBLEM: Page shows blank or error
SOLUTION:
1. Ensure Apache is running in XAMPP
2. Check the URL is correct: http://localhost/taskmaster/
3. Clear browser cache (Ctrl+Shift+R)
4. Check browser console for errors (F12)

PROBLEM: Tasks not saving
SOLUTION:
1. Verify database was initialized (run init_db.php)
2. Check if database folder exists with taskmaster.db file
3. Ensure the database folder has write permissions

PROBLEM: API errors appearing
SOLUTION:
1. Check that api/tasks.php exists
2. Verify PHP is running (Apache started)
3. Check PHP error logs in XAMPP

PROBLEM: XML import fails
SOLUTION:
1. Ensure XML file follows correct format
2. Check file encoding is UTF-8
3. Verify XML structure matches expected schema

PROBLEM: Styles not loading correctly
SOLUTION:
1. Check css/style.css exists
2. Clear browser cache
3. Check for 404 errors in browser console

PROBLEM: JavaScript errors
SOLUTION:
1. Ensure js/app.js and js/validation.js exist
2. Verify jQuery is loading from CDN
3. Check browser console for specific errors


================================================================================
                            8. CONCLUSION
================================================================================

8.1 SUMMARY
-----------

TaskMaster is a fully functional web-based task management application that 
successfully meets all the project requirements. The application demonstrates 
proficiency in:

Technical Achievements:
- HTML5 semantic structure with accessible markup
- CSS3 styling with responsive design and animations
- JavaScript/jQuery for client-side interactivity
- PHP backend with RESTful API design
- SQLite database for persistent storage
- JSON data interchange for API communication
- XML import/export functionality
- Client-side and server-side validation

Features Implemented:
- Full CRUD operations for tasks
- Multiple filtering and sorting options
- Real-time search functionality
- Category-based organization
- Priority levels with visual indicators
- Due date tracking
- Progress statistics and visualization
- Dark/Light theme toggle
- Toast notification system
- Keyboard shortcuts
- Responsive design for all devices

The application provides a professional, user-friendly interface that helps 
users effectively manage their daily tasks.


8.2 FUTURE ENHANCEMENTS
-----------------------

The following features could be added in future versions:

Short-term Improvements:
1. User authentication and registration
2. Multiple user support
3. Task due date reminders
4. Subtasks within tasks
5. Custom categories

Medium-term Improvements:
6. Email notifications
7. Task sharing/collaboration
8. Task templates
9. Recurring tasks
10. Data synchronization across devices

Long-term Improvements:
11. Mobile applications (iOS/Android)
12. Cloud hosting and backup
13. Team/workspace features
14. Calendar integration
15. Productivity reports and analytics


8.3 LESSONS LEARNED
-------------------

Technical Skills Gained:
1. Building responsive layouts with CSS Flexbox and media queries
2. Creating RESTful APIs with PHP
3. Working with SQLite databases
4. Implementing AJAX communication with jQuery
5. Handling XML data with PHP DOMDocument and SimpleXML
6. Form validation techniques (client and server-side)
7. State management in JavaScript applications

Best Practices Learned:
1. Separating concerns (HTML/CSS/JS/PHP)
2. Using prepared statements to prevent SQL injection
3. Sanitizing user input to prevent XSS
4. Writing modular, reusable code
5. Documenting code with comments
6. Testing thoroughly before deployment

Challenges Overcome:
1. Managing asynchronous AJAX calls
2. Handling database initialization without MySQL
3. Creating a cohesive dark theme design
4. Implementing responsive sidebar navigation
5. Synchronizing UI state with server data


================================================================================
                            9. REFERENCES
================================================================================

Web Technologies:
1. MDN Web Docs - HTML, CSS, JavaScript Reference
   https://developer.mozilla.org/

2. W3Schools - Web Development Tutorials
   https://www.w3schools.com/

3. jQuery Documentation
   https://api.jquery.com/

4. PHP Official Documentation
   https://www.php.net/docs.php

5. SQLite Documentation
   https://www.sqlite.org/docs.html

Design Resources:
6. Google Fonts - Poppins
   https://fonts.google.com/specimen/Poppins

7. CSS-Tricks - CSS Reference
   https://css-tricks.com/

Development Tools:
8. XAMPP - Apache Friends
   https://www.apachefriends.org/

9. Visual Studio Code
   https://code.visualstudio.com/

Books and Courses:
10. Course Materials - Web Programming
    [Your University/Institution]


================================================================================
                           10. APPENDICES
================================================================================

APPENDIX A: API ENDPOINTS REFERENCE
-----------------------------------

+--------+---------------------------+--------------------------------+
| Method | Endpoint                  | Description                    |
+--------+---------------------------+--------------------------------+
| GET    | /api/tasks.php            | Get all tasks                  |
| GET    | /api/tasks.php?id={id}    | Get single task                |
| POST   | /api/tasks.php            | Create new task                |
| PUT    | /api/tasks.php?id={id}    | Update task                    |
| PATCH  | /api/tasks.php?id={id}    | Toggle task completion         |
| DELETE | /api/tasks.php?id={id}    | Delete task                    |
| GET    | /api/tasks.php?action=stats| Get statistics                |
| GET    | /api/xml_handler.php?action=export | Export to XML       |
| POST   | /api/xml_handler.php?action=import | Import from XML     |
+--------+---------------------------+--------------------------------+


APPENDIX B: XML DATA FORMAT
---------------------------

<?xml version="1.0" encoding="UTF-8"?>
<taskmaster>
    <metadata>
        <exported_at>2024-12-28T12:00:00Z</exported_at>
        <version>2.0</version>
        <total_tasks>5</total_tasks>
    </metadata>
    <tasks>
        <task id="1">
            <title>Task Title</title>
            <description>Task Description</description>
            <category>work</category>
            <priority>high</priority>
            <status>pending</status>
            <due_date>2024-12-31</due_date>
            <created_at>2024-12-28T10:00:00Z</created_at>
        </task>
    </tasks>
    <categories>
        <category>
            <name>personal</name>
            <color>#7c3aed</color>
            <icon>👤</icon>
        </category>
    </categories>
</taskmaster>


APPENDIX C: CSS COLOR VARIABLES
-------------------------------

:root {
    /* Background Colors */
    --bg-primary: #12141c;
    --bg-secondary: #1a1d2e;
    --bg-card: #252a3d;
    
    /* Accent Colors */
    --accent-primary: #7c3aed;
    --accent-secondary: #00d4ff;
    
    /* Priority Colors */
    --priority-high: #ef4444;
    --priority-medium: #f59e0b;
    --priority-low: #22c55e;
    
    /* Text Colors */
    --text-primary: #ffffff;
    --text-secondary: #94a3b8;
}


================================================================================
                          END OF DOCUMENTATION
================================================================================

Document Information:
- Title: TaskMaster Project Documentation
- Author: [Your Name]
- Version: 1.0
- Date: December 2024
- Pages: [Page Count]
- Course: Web Programming

================================================================================