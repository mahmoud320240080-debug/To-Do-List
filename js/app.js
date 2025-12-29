/* ============================================
   TASKMASTER - MAIN APPLICATION
   Author: Your Name
   Version: 2.0
   Description: Main application with API integration
   ============================================ */

const TaskMaster = (function($) {
    'use strict';

    // ==========================================
    // CONFIGURATION
    // ==========================================
    const CONFIG = {
        API_URL: 'api/tasks.php',
        TOAST_DURATION: 3000,
        ANIMATION_DURATION: 300,
        USE_API: true  // Set to false to use localStorage only
    };

    // ==========================================
    // STATE MANAGEMENT
    // ==========================================
    let state = {
        tasks: [],
        currentFilter: 'all',
        currentCategory: 'all',
        currentSort: 'newest',
        searchQuery: '',
        taskToDelete: null,
        isLoading: false,
        settings: {
            theme: 'dark',
            notifications: true
        }
    };

    // ==========================================
    // DOM CACHE
    // ==========================================
    const DOM = {};

    function cacheDOM() {
        // Forms
        DOM.$taskForm = $('#task-form');
        DOM.$editTaskForm = $('#edit-task-form');
        
        // Inputs
        DOM.$taskTitle = $('#task-title');
        DOM.$taskCategory = $('#task-category');
        DOM.$taskPriority = $('#task-priority');
        DOM.$taskDueDate = $('#task-due-date');
        DOM.$searchInput = $('#search-tasks');
        DOM.$sortSelect = $('#sort-by');
        
        // Edit Modal Inputs
        DOM.$editTaskId = $('#edit-task-id');
        DOM.$editTaskTitle = $('#edit-task-title');
        DOM.$editTaskDescription = $('#edit-task-description');
        DOM.$editTaskCategory = $('#edit-task-category');
        DOM.$editTaskPriority = $('#edit-task-priority');
        DOM.$editTaskDueDate = $('#edit-task-due-date');
        
        // Lists
        DOM.$taskList = $('#task-list');
        DOM.$completedList = $('#completed-list');
        
        // Sections
        DOM.$emptyState = $('#empty-state');
        DOM.$completedSection = $('#completed-section');
        
        // Modals
        DOM.$editModal = $('#edit-modal');
        DOM.$deleteModal = $('#delete-modal');
        
        // Buttons
        DOM.$addTaskBtn = $('#add-task-btn');
        DOM.$modalClose = $('#modal-close');
        DOM.$cancelEdit = $('#cancel-edit');
        DOM.$cancelDelete = $('#cancel-delete');
        DOM.$confirmDelete = $('#confirm-delete');
        DOM.$clearCompleted = $('#clear-completed-btn');
        DOM.$themeToggle = $('#theme-toggle');
        
        // Filter Tabs
        DOM.$filterTabs = $('.filter-tab');
        
        // Sidebar
        DOM.$sidebar = $('.sidebar');
        DOM.$categoryLinks = $('.collections-list .nav-link');
        
        // Stats & Progress
        DOM.$currentDate = $('#current-date');
        DOM.$progressRingFill = $('#progress-ring-fill');
        DOM.$progressPercent = $('#progress-percent');
        DOM.$progressNumbers = $('#progress-numbers');
        DOM.$progressBar = $('#progress-bar');
        DOM.$priorityIndicator = $('#priority-indicator');
        
        // Badges & Counts
        DOM.$allCount = $('#all-count');
        DOM.$activeCount = $('#active-count');
        DOM.$completedCount = $('#completed-count');
        DOM.$activeBadge = $('#active-badge');
        DOM.$completedBadge = $('#completed-badge');
        DOM.$personalCount = $('#personal-count');
        DOM.$workCount = $('#work-count');
        DOM.$studyCount = $('#study-count');
        DOM.$shoppingCount = $('#shopping-count');
        DOM.$highPriorityCount = $('#high-priority-count');
        DOM.$mediumPriorityCount = $('#medium-priority-count');
        DOM.$lowPriorityCount = $('#low-priority-count');
        DOM.$todayCompleted = $('#today-completed');
        DOM.$todayPending = $('#today-pending');
        
        // Other
        DOM.$deadlinesList = $('#deadlines-list');
        DOM.$toastContainer = $('#toast-container');
        DOM.$formError = $('#form-error');
    }

    // ==========================================
    // INITIALIZATION
    // ==========================================
    function init() {
        cacheDOM();
        loadSettings();
        bindEvents();
        updateDate();
        updatePriorityIndicator();
        setMinDate();
        
        // Load tasks from API
        if (CONFIG.USE_API) {
            fetchTasks();
        } else {
            loadFromStorage();
            renderTasks();
            updateStats();
        }
        
        console.log('✅ TaskMaster initialized!');
    }

    function setMinDate() {
        const today = new Date().toISOString().split('T')[0];
        DOM.$taskDueDate.attr('min', today);
        DOM.$editTaskDueDate.attr('min', today);
    }

    function updateDate() {
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        const today = new Date().toLocaleDateString('en-US', options);
        DOM.$currentDate.text(today);
    }

    // ==========================================
    // EVENT BINDING
    // ==========================================
    function bindEvents() {
        // Form submission
        DOM.$taskForm.on('submit', handleAddTask);
        DOM.$editTaskForm.on('submit', handleEditTask);
        
        // Task actions (event delegation)
        DOM.$taskList.on('click', '.action-btn.edit', handleEditClick);
        DOM.$taskList.on('click', '.action-btn.delete', handleDeleteClick);
        DOM.$taskList.on('change', '.task-checkbox input', handleToggleComplete);
        
        DOM.$completedList.on('click', '.action-btn.delete', handleDeleteClick);
        DOM.$completedList.on('change', '.task-checkbox input', handleToggleComplete);
        
        // Modal controls
        DOM.$modalClose.on('click', closeEditModal);
        DOM.$cancelEdit.on('click', closeEditModal);
        DOM.$cancelDelete.on('click', closeDeleteModal);
        DOM.$confirmDelete.on('click', confirmDelete);
        
        // Close modals on overlay click
        DOM.$editModal.on('click', function(e) {
            if ($(e.target).is('.modal-overlay')) {
                closeEditModal();
            }
        });
        
        DOM.$deleteModal.on('click', function(e) {
            if ($(e.target).is('.modal-overlay')) {
                closeDeleteModal();
            }
        });
        
        // Filter tabs
        DOM.$filterTabs.on('click', handleFilterChange);
        
        // Category filter
        DOM.$categoryLinks.on('click', handleCategoryFilter);
        
        // Search
        DOM.$searchInput.on('input', debounce(handleSearch, 300));
        
        // Sort
        DOM.$sortSelect.on('change', handleSort);
        
        // Clear completed
        DOM.$clearCompleted.on('click', handleClearCompleted);
        
        // Priority indicator
        DOM.$taskPriority.on('change', updatePriorityIndicator);
        
        // Theme toggle
        DOM.$themeToggle.on('click', toggleTheme);
        
        // Keyboard shortcuts
        $(document).on('keydown', handleKeyboardShortcuts);
        
        // Escape key for modals
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
                closeDeleteModal();
            }
        });
        
        // XML Import/Export buttons
        $('#export-xml-btn').on('click', function(e) {
            e.preventDefault();
            exportToXML();
        });
        
        $('#import-xml-btn').on('click', function(e) {
            e.preventDefault();
            $('#xml-file-input').click();
        });
        
        $('#xml-file-input').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                importFromXML(file);
                $(this).val(''); // Reset input
            }
        });
        
        $('#load-xml-btn').on('click', function(e) {
            e.preventDefault();
            loadXMLDirect();
        });
        // Real-time validation
        setupValidation();
    }

    function setupValidation() {
        if (typeof Validator !== 'undefined') {
            Validator.setupRealTimeValidation(DOM.$taskTitle, Validator.validateTaskTitle);
            Validator.setupRealTimeValidation(DOM.$editTaskTitle, Validator.validateTaskTitle);
        }
    }

    // ==========================================
    // API FUNCTIONS (AJAX)
    // ==========================================

    /**
     * Fetch all tasks from API
     */
    function fetchTasks() {
        setLoading(true);
        
        const params = {
            status: state.currentFilter,
            category: state.currentCategory,
            sort_by: state.currentSort,
            search: state.searchQuery
        };
        
        $.ajax({
            url: CONFIG.API_URL,
            method: 'GET',
            data: params,
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                state.tasks = response.data.tasks || [];
                renderTasks();
                updateStatsFromAPI(response.data.stats);
                fetchDeadlines();
                fetchCategoryCounts();
            } else {
                showToast('error', 'Error', response.message);
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.error('API Error:', textStatus, errorThrown);
            showToast('error', 'Connection Error', 'Failed to load tasks. Using local data.');
            loadFromStorage();
            renderTasks();
            updateStats();
        })
        .always(function() {
            setLoading(false);
        });
    }

    /**
     * Fetch statistics from API
     */
    function fetchStats() {
        $.ajax({
            url: CONFIG.API_URL,
            method: 'GET',
            data: { action: 'stats' },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                updateStatsFromAPI(response.data.stats);
                updateCategoryCountsFromAPI(response.data.categories);
            }
        });
    }

    /**
     * Fetch upcoming deadlines
     */
    function fetchDeadlines() {
        $.ajax({
            url: CONFIG.API_URL,
            method: 'GET',
            data: { action: 'deadlines', limit: 5 },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                updateDeadlinesFromAPI(response.data);
            }
        });
    }

    /**
     * Fetch category counts
     */
    function fetchCategoryCounts() {
        $.ajax({
            url: CONFIG.API_URL,
            method: 'GET',
            data: { action: 'stats' },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success && response.data.categories) {
                updateCategoryCountsFromAPI(response.data.categories);
            }
        });
    }

    /**
     * Create task via API
     */
    function createTaskAPI(taskData, callback) {
        $.ajax({
            url: CONFIG.API_URL,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(taskData),
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                callback(null, response.data);
            } else {
                callback(response.message, null);
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            callback('Failed to create task', null);
        });
    }

    /**
     * Update task via API
     */
    function updateTaskAPI(taskId, taskData, callback) {
        $.ajax({
            url: CONFIG.API_URL + '?id=' + taskId,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(taskData),
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                callback(null, response.data);
            } else {
                callback(response.message, null);
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            callback('Failed to update task', null);
        });
    }

    /**
     * Toggle task completion via API
     */
    function toggleTaskAPI(taskId, callback) {
        $.ajax({
            url: CONFIG.API_URL + '?id=' + taskId,
            method: 'PATCH',
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                callback(null, response.data);
            } else {
                callback(response.message, null);
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            callback('Failed to toggle task', null);
        });
    }

    /**
     * Delete task via API
     */
    function deleteTaskAPI(taskId, callback) {
        $.ajax({
            url: CONFIG.API_URL + '?id=' + taskId,
            method: 'DELETE',
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                callback(null, response);
            } else {
                callback(response.message, null);
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            callback('Failed to delete task', null);
        });
    }

    /**
     * Clear completed tasks via API
     */
    function clearCompletedAPI(callback) {
        $.ajax({
            url: CONFIG.API_URL + '?action=clear',
            method: 'DELETE',
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                callback(null, response.data);
            } else {
                callback(response.message, null);
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            callback('Failed to clear tasks', null);
        });
    }

    // ==========================================
    // TASK HANDLERS
    // ==========================================

    /**
     * Handle add task
     */
    function handleAddTask(e) {
        e.preventDefault();
        
        const taskData = {
            title: DOM.$taskTitle.val().trim(),
            category: DOM.$taskCategory.val(),
            priority: DOM.$taskPriority.val(),
            dueDate: DOM.$taskDueDate.val() || null
        };
        
        // Validate
        if (typeof Validator !== 'undefined') {
            const validation = Validator.validateTaskForm(taskData);
            if (!validation.isValid) {
                const firstError = Object.values(validation.errors)[0][0];
                showFormError(firstError);
                return;
            }
        } else if (!taskData.title || taskData.title.length < 2) {
            showFormError('Title must be at least 2 characters');
            return;
        }
        
        // Disable button
        DOM.$addTaskBtn.prop('disabled', true).addClass('loading');
        
        if (CONFIG.USE_API) {
            createTaskAPI(taskData, function(error, newTask) {
                DOM.$addTaskBtn.prop('disabled', false).removeClass('loading');
                
                if (error) {
                    showToast('error', 'Error', error);
                    return;
                }
                
                // Add to state and render
                state.tasks.unshift(newTask);
                renderTasks();
                fetchStats();
                
                // Reset form
                DOM.$taskForm[0].reset();
                DOM.$formError.text('');
                updatePriorityIndicator();
                
                showToast('success', 'Task Added', `"${newTask.title}" has been added!`);
            });
        } else {
            // Local storage fallback
            const newTask = {
                id: generateId(),
                ...taskData,
                status: 'pending',
                completed: false,
                createdAt: new Date().toISOString()
            };
            
            state.tasks.unshift(newTask);
            saveToStorage();
            renderTasks();
            updateStats();
            
            DOM.$taskForm[0].reset();
            updatePriorityIndicator();
            DOM.$addTaskBtn.prop('disabled', false).removeClass('loading');
            
            showToast('success', 'Task Added', `"${newTask.title}" has been added!`);
        }
    }

    /**
     * Handle edit click
     */
    function handleEditClick(e) {
        e.stopPropagation();
        
        const taskId = $(this).closest('.task-item').data('id');
        const task = state.tasks.find(t => t.id == taskId);
        
        if (task) {
            DOM.$editTaskId.val(task.id);
            DOM.$editTaskTitle.val(task.title);
            DOM.$editTaskDescription.val(task.description || '');
            DOM.$editTaskCategory.val(task.category || 'personal');
            DOM.$editTaskPriority.val(task.priority || 'medium');
            DOM.$editTaskDueDate.val(task.due_date || task.dueDate || '');
            
            openEditModal();
        }
    }

    /**
     * Handle edit task submit
     */
    function handleEditTask(e) {
        e.preventDefault();
        
        const taskId = DOM.$editTaskId.val();
        
        const taskData = {
            title: DOM.$editTaskTitle.val().trim(),
            description: DOM.$editTaskDescription.val().trim(),
            category: DOM.$editTaskCategory.val(),
            priority: DOM.$editTaskPriority.val(),
            dueDate: DOM.$editTaskDueDate.val() || null
        };
        
        // Validate
        if (!taskData.title || taskData.title.length < 2) {
            showToast('error', 'Validation Error', 'Title must be at least 2 characters');
            return;
        }
        
        if (CONFIG.USE_API) {
            updateTaskAPI(taskId, taskData, function(error, updatedTask) {
                if (error) {
                    showToast('error', 'Error', error);
                    return;
                }
                
                // Update in state
                const index = state.tasks.findIndex(t => t.id == taskId);
                if (index !== -1) {
                    state.tasks[index] = updatedTask;
                }
                
                renderTasks();
                fetchStats();
                closeEditModal();
                
                showToast('success', 'Task Updated', 'Your task has been updated!');
            });
        } else {
            const index = state.tasks.findIndex(t => t.id == taskId);
            if (index !== -1) {
                state.tasks[index] = {
                    ...state.tasks[index],
                    ...taskData,
                    due_date: taskData.dueDate,
                    updatedAt: new Date().toISOString()
                };
                
                saveToStorage();
                renderTasks();
                updateStats();
                closeEditModal();
                
                showToast('success', 'Task Updated', 'Your task has been updated!');
            }
        }
    }

    /**
     * Handle delete click
     */
    function handleDeleteClick(e) {
        e.stopPropagation();
        
        const taskId = $(this).closest('.task-item').data('id');
        state.taskToDelete = taskId;
        
        openDeleteModal();
    }

    /**
     * Confirm delete
     */
    function confirmDelete() {
        if (!state.taskToDelete) return;
        
        const taskId = state.taskToDelete;
        const task = state.tasks.find(t => t.id == taskId);
        const taskTitle = task ? task.title : 'Task';
        
        if (CONFIG.USE_API) {
            deleteTaskAPI(taskId, function(error, response) {
                if (error) {
                    showToast('error', 'Error', error);
                    return;
                }
                
                // Remove from state
                state.tasks = state.tasks.filter(t => t.id != taskId);
                
                renderTasks();
                fetchStats();
                closeDeleteModal();
                state.taskToDelete = null;
                
                showToast('success', 'Task Deleted', `"${taskTitle}" has been removed.`);
            });
        } else {
            state.tasks = state.tasks.filter(t => t.id != taskId);
            saveToStorage();
            renderTasks();
            updateStats();
            closeDeleteModal();
            state.taskToDelete = null;
            
            showToast('success', 'Task Deleted', `"${taskTitle}" has been removed.`);
        }
    }

    /**
     * Handle toggle complete
     */
    function handleToggleComplete(e) {
        const taskId = $(this).closest('.task-item').data('id');
        
        if (CONFIG.USE_API) {
            toggleTaskAPI(taskId, function(error, updatedTask) {
                if (error) {
                    showToast('error', 'Error', error);
                    // Revert checkbox
                    fetchTasks();
                    return;
                }
                
                // Update in state
                const index = state.tasks.findIndex(t => t.id == taskId);
                if (index !== -1) {
                    state.tasks[index] = updatedTask;
                }
                
                renderTasks();
                fetchStats();
                
                if (updatedTask.status === 'completed') {
                    showToast('success', 'Task Completed', `Great job! "${updatedTask.title}" is done!`);
                } else {
                    showToast('info', 'Task Restored', `"${updatedTask.title}" moved to active.`);
                }
            });
        } else {
            const task = state.tasks.find(t => t.id == taskId);
            if (task) {
                task.completed = !task.completed;
                task.status = task.completed ? 'completed' : 'pending';
                task.completedAt = task.completed ? new Date().toISOString() : null;
                
                saveToStorage();
                renderTasks();
                updateStats();
                
                if (task.completed) {
                    showToast('success', 'Task Completed', `Great job! "${task.title}" is done!`);
                }
            }
        }
    }

    /**
     * Handle clear completed
     */
    function handleClearCompleted() {
        const completedCount = state.tasks.filter(t => 
            t.status === 'completed' || t.completed
        ).length;
        
        if (completedCount === 0) {
            showToast('info', 'Nothing to Clear', 'No completed tasks to remove.');
            return;
        }
        
        if (CONFIG.USE_API) {
            clearCompletedAPI(function(error, response) {
                if (error) {
                    showToast('error', 'Error', error);
                    return;
                }
                
                fetchTasks();
                showToast('success', 'Cleared', `${response.deleted_count} completed task(s) removed.`);
            });
        } else {
            state.tasks = state.tasks.filter(t => !t.completed && t.status !== 'completed');
            saveToStorage();
            renderTasks();
            updateStats();
            
            showToast('success', 'Cleared', `${completedCount} completed task(s) removed.`);
        }
    }

    // ==========================================
    // FILTERING & SORTING
    // ==========================================

    function handleFilterChange(e) {
        const filter = $(this).data('filter');
        
        DOM.$filterTabs.removeClass('active');
        $(this).addClass('active');
        
        state.currentFilter = filter;
        
        if (CONFIG.USE_API) {
            fetchTasks();
        } else {
            renderTasks();
        }
    }

    function handleCategoryFilter(e) {
        e.preventDefault();
        
        const category = $(this).data('category');
        
        DOM.$categoryLinks.parent().removeClass('active');
        $(this).parent().addClass('active');
        
        state.currentCategory = category;
        
        if (CONFIG.USE_API) {
            fetchTasks();
        } else {
            renderTasks();
        }
    }

    function handleSort() {
        state.currentSort = DOM.$sortSelect.val();
        
        if (CONFIG.USE_API) {
            fetchTasks();
        } else {
            renderTasks();
        }
    }

    function handleSearch() {
        state.searchQuery = DOM.$searchInput.val().toLowerCase().trim();
        
        if (CONFIG.USE_API) {
            fetchTasks();
        } else {
            renderTasks();
        }
    }

    function getFilteredTasks() {
        let filtered = [...state.tasks];
        
        // For local storage mode, apply filters manually
        if (!CONFIG.USE_API) {
            // Status filter
            if (state.currentFilter === 'active') {
                filtered = filtered.filter(t => !t.completed && t.status !== 'completed');
            } else if (state.currentFilter === 'completed') {
                filtered = filtered.filter(t => t.completed || t.status === 'completed');
            }
            
            // Category filter
            if (state.currentCategory !== 'all') {
                filtered = filtered.filter(t => t.category === state.currentCategory);
            }
            
            // Search
            if (state.searchQuery) {
                filtered = filtered.filter(t => 
                    t.title.toLowerCase().includes(state.searchQuery) ||
                    (t.description && t.description.toLowerCase().includes(state.searchQuery))
                );
            }
            
            // Sort
            filtered = sortTasks(filtered, state.currentSort);
        }
        
        return filtered;
    }

    function sortTasks(tasks, sortBy) {
        const sorted = [...tasks];
        
        switch (sortBy) {
            case 'newest':
                sorted.sort((a, b) => new Date(b.created_at || b.createdAt) - new Date(a.created_at || a.createdAt));
                break;
            case 'oldest':
                sorted.sort((a, b) => new Date(a.created_at || a.createdAt) - new Date(b.created_at || b.createdAt));
                break;
            case 'priority':
                const order = { high: 1, medium: 2, low: 3 };
                sorted.sort((a, b) => order[a.priority] - order[b.priority]);
                break;
            case 'due_date':
            case 'due-date':
                sorted.sort((a, b) => {
                    const dateA = a.due_date || a.dueDate;
                    const dateB = b.due_date || b.dueDate;
                    if (!dateA && !dateB) return 0;
                    if (!dateA) return 1;
                    if (!dateB) return -1;
                    return new Date(dateA) - new Date(dateB);
                });
                break;
            case 'alphabetical':
                sorted.sort((a, b) => a.title.localeCompare(b.title));
                break;
        }
        
        return sorted;
    }

    // ==========================================
    // RENDERING
    // ==========================================

    function renderTasks() {
        const filteredTasks = getFilteredTasks();
        const activeTasks = filteredTasks.filter(t => t.status !== 'completed' && !t.completed);
        const completedTasks = filteredTasks.filter(t => t.status === 'completed' || t.completed);
        
        // Clear lists
        DOM.$taskList.empty();
        DOM.$completedList.empty();
        
        // Render active tasks
        if (activeTasks.length === 0) {
            DOM.$emptyState.removeClass('hidden');
        } else {
            DOM.$emptyState.addClass('hidden');
            activeTasks.forEach((task, index) => {
                const $task = createTaskElement(task);
                $task.css('animation-delay', `${index * 50}ms`);
                DOM.$taskList.append($task);
            });
        }
        
        // Render completed tasks
        if (completedTasks.length === 0) {
            DOM.$completedSection.addClass('hidden');
        } else {
            DOM.$completedSection.removeClass('hidden');
            completedTasks.forEach(task => {
                DOM.$completedList.append(createTaskElement(task));
            });
        }
        
        // Update badges
        DOM.$activeBadge.text(activeTasks.length);
        DOM.$completedBadge.text(completedTasks.length);
    }

    function createTaskElement(task) {
        const isCompleted = task.status === 'completed' || task.completed;
        const dueDate = task.due_date || task.dueDate;
        const dueDateInfo = getDueDateInfo(dueDate);
        const category = task.category || 'personal';
        const priority = task.priority || 'medium';
        
        const taskHtml = `
            <li class="task-item priority-${priority} ${isCompleted ? 'completed' : ''}" data-id="${task.id}">
                <div class="task-checkbox">
                    <label class="custom-checkbox">
                        <input type="checkbox" ${isCompleted ? 'checked' : ''}>
                        <span class="checkmark"></span>
                    </label>
                </div>
                <div class="task-content">
                    <h3 class="task-title">${escapeHtml(task.title)}</h3>
                    <div class="task-meta">
                        <span class="task-tag task-category ${category}">${category.toUpperCase()}</span>
                        <span class="task-tag task-priority ${priority}">${priority.toUpperCase()}</span>
                        ${dueDate ? `<span class="task-due-date ${dueDateInfo.class}">📅 ${dueDateInfo.text}</span>` : ''}
                    </div>
                </div>
                <div class="task-actions">
                    <button class="action-btn edit" title="Edit Task">✏️</button>
                    <button class="action-btn delete" title="Delete Task">🗑️</button>
                </div>
            </li>
        `;
        
        return $(taskHtml);
    }

    function getDueDateInfo(dueDate) {
        if (!dueDate) return { text: '', class: '' };
        
        const due = new Date(dueDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const diffDays = Math.ceil((due - today) / (1000 * 60 * 60 * 24));
        
        let text = '';
        let className = '';
        
        if (diffDays < 0) {
            text = 'Overdue';
            className = 'overdue';
        } else if (diffDays === 0) {
            text = 'Today';
            className = 'soon';
        } else if (diffDays === 1) {
            text = 'Tomorrow';
            className = 'soon';
        } else if (diffDays <= 7) {
            text = `${diffDays} days left`;
            className = 'soon';
        } else {
            text = due.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }
        
        return { text, class: className };
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ==========================================
    // STATISTICS
    // ==========================================

    function updateStats() {
        const total = state.tasks.length;
        const completed = state.tasks.filter(t => t.status === 'completed' || t.completed).length;
        const active = total - completed;
        const progress = total > 0 ? Math.round((completed / total) * 100) : 0;
        
        // Filter counts
        DOM.$allCount.text(total);
        DOM.$activeCount.text(active);
        DOM.$completedCount.text(completed);
        
        // Category counts
        const activeTasks = state.tasks.filter(t => t.status !== 'completed' && !t.completed);
        DOM.$personalCount.text(activeTasks.filter(t => t.category === 'personal').length);
        DOM.$workCount.text(activeTasks.filter(t => t.category === 'work').length);
        DOM.$studyCount.text(activeTasks.filter(t => t.category === 'study').length);
        DOM.$shoppingCount.text(activeTasks.filter(t => t.category === 'shopping').length);
        
        // Priority counts
        DOM.$highPriorityCount.text(activeTasks.filter(t => t.priority === 'high').length);
        DOM.$mediumPriorityCount.text(activeTasks.filter(t => t.priority === 'medium').length);
        DOM.$lowPriorityCount.text(activeTasks.filter(t => t.priority === 'low').length);
        
        // Today's stats
        DOM.$todayCompleted.text(completed);
        DOM.$todayPending.text(active);
        
        // Progress
        updateProgressRing(progress);
        DOM.$progressNumbers.text(`${completed}/${total}`);
        DOM.$progressBar.css('width', `${progress}%`);
        DOM.$progressPercent.text(`${progress}%`);
        
        // Deadlines
        updateDeadlines();
    }

    function updateStatsFromAPI(stats) {
        if (!stats) return;
        
        const total = stats.total || 0;
        const completed = stats.completed || 0;
        const active = stats.active || 0;
        const progress = total > 0 ? Math.round((completed / total) * 100) : 0;
        
        DOM.$allCount.text(total);
        DOM.$activeCount.text(active);
        DOM.$completedCount.text(completed);
        
        DOM.$highPriorityCount.text(stats.high_priority || 0);
        DOM.$mediumPriorityCount.text(stats.medium_priority || 0);
        DOM.$lowPriorityCount.text(stats.low_priority || 0);
        
        DOM.$todayCompleted.text(stats.completed_today || 0);
        DOM.$todayPending.text(active);
        
        updateProgressRing(progress);
        DOM.$progressNumbers.text(`${completed}/${total}`);
        DOM.$progressBar.css('width', `${progress}%`);
        DOM.$progressPercent.text(`${progress}%`);
    }

    function updateCategoryCountsFromAPI(categories) {
        if (!categories) return;
        
        categories.forEach(cat => {
            const count = cat.count || 0;
            switch (cat.name) {
                case 'personal':
                    DOM.$personalCount.text(count);
                    break;
                case 'work':
                    DOM.$workCount.text(count);
                    break;
                case 'study':
                    DOM.$studyCount.text(count);
                    break;
                case 'shopping':
                    DOM.$shoppingCount.text(count);
                    break;
            }
        });
    }

    function updateProgressRing(percent) {
        const circumference = 2 * Math.PI * 52;
        const offset = circumference - (percent / 100) * circumference;
        DOM.$progressRingFill.css('stroke-dashoffset', offset);
    }

    function updateDeadlines() {
        const upcomingTasks = state.tasks
            .filter(t => {
                const dueDate = t.due_date || t.dueDate;
                return (t.status !== 'completed' && !t.completed) && dueDate;
            })
            .sort((a, b) => {
                const dateA = a.due_date || a.dueDate;
                const dateB = b.due_date || b.dueDate;
                return new Date(dateA) - new Date(dateB);
            })
            .slice(0, 3);
        
        if (upcomingTasks.length === 0) {
            DOM.$deadlinesList.html(`
                <div class="no-deadlines">
                    <span>📅</span>
                    <p>No upcoming deadlines</p>
                </div>
            `);
            return;
        }
        
        const deadlinesHtml = upcomingTasks.map(task => {
            const dueDate = task.due_date || task.dueDate;
            const dueDateInfo = getDueDateInfo(dueDate);
            return `
                <div class="deadline-item">
                    <div class="deadline-icon">📌</div>
                    <div class="deadline-info">
                        <span class="deadline-title">${escapeHtml(task.title)}</span>
                        <span class="deadline-date">${dueDateInfo.text}</span>
                    </div>
                </div>
            `;
        }).join('');
        
        DOM.$deadlinesList.html(deadlinesHtml);
    }

    function updateDeadlinesFromAPI(deadlines) {
        if (!deadlines || deadlines.length === 0) {
            DOM.$deadlinesList.html(`
                <div class="no-deadlines">
                    <span>📅</span>
                    <p>No upcoming deadlines</p>
                </div>
            `);
            return;
        }
        
        const deadlinesHtml = deadlines.map(task => {
            const dueDateInfo = getDueDateInfo(task.due_date);
            return `
                <div class="deadline-item">
                    <div class="deadline-icon">📌</div>
                    <div class="deadline-info">
                        <span class="deadline-title">${escapeHtml(task.title)}</span>
                        <span class="deadline-date">${dueDateInfo.text}</span>
                    </div>
                </div>
            `;
        }).join('');
        
        DOM.$deadlinesList.html(deadlinesHtml);
    }

    function updatePriorityIndicator() {
        const priority = DOM.$taskPriority.val();
        DOM.$priorityIndicator.removeClass('low medium high').addClass(priority);
    }

    // ==========================================
    // MODALS
    // ==========================================

    function openEditModal() {
        DOM.$editModal.addClass('active');
        DOM.$editTaskTitle.focus();
        $('body').css('overflow', 'hidden');
    }

    function closeEditModal() {
        DOM.$editModal.removeClass('active');
        DOM.$editTaskForm[0].reset();
        $('body').css('overflow', '');
    }

    function openDeleteModal() {
        DOM.$deleteModal.addClass('active');
        $('body').css('overflow', 'hidden');
    }

    function closeDeleteModal() {
        DOM.$deleteModal.removeClass('active');
        state.taskToDelete = null;
        $('body').css('overflow', '');
    }

    // ==========================================
    // TOAST NOTIFICATIONS
    // ==========================================

    function showToast(type, title, message) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        
        const $toast = $(`
            <div class="toast ${type}">
                <span class="toast-icon">${icons[type]}</span>
                <div class="toast-content">
                    <span class="toast-title">${title}</span>
                    <span class="toast-message">${message}</span>
                </div>
                <button class="toast-close">×</button>
            </div>
        `);
        
        DOM.$toastContainer.append($toast);
        
        $toast.find('.toast-close').on('click', function() {
            $toast.remove();
        });
        
        setTimeout(() => {
            $toast.remove();
        }, CONFIG.TOAST_DURATION);
    }

    function showFormError(message) {
        DOM.$formError.text(message);
        DOM.$taskTitle.addClass('error');
        
        setTimeout(() => {
            DOM.$formError.text('');
            DOM.$taskTitle.removeClass('error');
        }, 3000);
    }

    // ==========================================
    // LOADING STATE
    // ==========================================

    function setLoading(isLoading) {
        state.isLoading = isLoading;
        
        if (isLoading) {
            DOM.$taskList.addClass('loading');
        } else {
            DOM.$taskList.removeClass('loading');
        }
    }

    // ==========================================
    // LOCAL STORAGE
    // ==========================================

    function saveToStorage() {
        try {
            localStorage.setItem('taskmaster_tasks', JSON.stringify(state.tasks));
        } catch (e) {
            console.error('Error saving to localStorage:', e);
        }
    }

    function loadFromStorage() {
        try {
            const saved = localStorage.getItem('taskmaster_tasks');
            if (saved) {
                state.tasks = JSON.parse(saved);
            }
        } catch (e) {
            console.error('Error loading from localStorage:', e);
            state.tasks = [];
        }
    }

    function loadSettings() {
        try {
            const saved = localStorage.getItem('taskmaster_settings');
            if (saved) {
                state.settings = { ...state.settings, ...JSON.parse(saved) };
            }
            
            if (state.settings.theme === 'light') {
                $('html').attr('data-theme', 'light');
                DOM.$themeToggle.text('☀️');
            }
        } catch (e) {
            console.error('Error loading settings:', e);
        }
    }

    function saveSettings() {
        try {
            localStorage.setItem('taskmaster_settings', JSON.stringify(state.settings));
        } catch (e) {
            console.error('Error saving settings:', e);
        }
    }

    // ==========================================
    // THEME
    // ==========================================

    function toggleTheme() {
        const currentTheme = $('html').attr('data-theme');
        
        if (currentTheme === 'light') {
            $('html').removeAttr('data-theme');
            DOM.$themeToggle.text('🌙');
            state.settings.theme = 'dark';
        } else {
            $('html').attr('data-theme', 'light');
            DOM.$themeToggle.text('☀️');
            state.settings.theme = 'light';
        }
        
        saveSettings();
    }

    // ==========================================
    // KEYBOARD SHORTCUTS
    // ==========================================

    function handleKeyboardShortcuts(e) {
        if ($(e.target).is('input, textarea, select')) return;
        
        switch (e.key) {
            case 'n':
                e.preventDefault();
                DOM.$taskTitle.focus();
                break;
            case '/':
                e.preventDefault();
                DOM.$searchInput.focus();
                break;
            case '1':
                DOM.$filterTabs.filter('[data-filter="all"]').click();
                break;
            case '2':
                DOM.$filterTabs.filter('[data-filter="active"]').click();
                break;
            case '3':
                DOM.$filterTabs.filter('[data-filter="completed"]').click();
                break;
        }
    }

    // ==========================================
    // UTILITY FUNCTIONS
    // ==========================================

    function generateId() {
        return 'task_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

// ==========================================
    // PUBLIC API
    // ==========================================
    return {
        init,
        getState: () => ({ ...state }),
        getTasks: () => [...state.tasks],
        fetchTasks,
        showToast,
        // XML Functions
        exportToXML,
        importFromXML,
        loadFromXML,
        loadXMLDirect
    };
// ==========================================
    // XML FUNCTIONS
    // ==========================================

    /**
     * Export tasks to XML file
     */
    function exportToXML() {
        showToast('info', 'Exporting', 'Preparing XML export...');
        
        $.ajax({
            url: 'api/xml_handler.php',
            method: 'GET',
            data: { action: 'export', json: '1' },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                showToast('success', 'Exported', 'Tasks exported to XML successfully!');
                // Trigger download
                window.open('api/xml_handler.php?action=export&download=1', '_blank');
            } else {
                showToast('error', 'Export Failed', response.message);
            }
        })
        .fail(function() {
            showToast('error', 'Export Failed', 'Could not export tasks to XML.');
        });
    }

    /**
     * Import tasks from XML file
     */
    function importFromXML(file) {
        const formData = new FormData();
        formData.append('xml_file', file);
        
        showToast('info', 'Importing', 'Processing XML file...');
        
        $.ajax({
            url: 'api/xml_handler.php?action=import',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                showToast('success', 'Imported', `${response.data.imported} tasks imported!`);
                fetchTasks(); // Refresh task list
            } else {
                showToast('error', 'Import Failed', response.message);
            }
        })
        .fail(function() {
            showToast('error', 'Import Failed', 'Could not import XML file.');
        });
    }

    /**
     * Load tasks from XML file using AJAX
     */
    function loadFromXML() {
        showToast('info', 'Loading', 'Loading tasks from XML...');
        
        $.ajax({
            url: 'api/xml_handler.php',
            method: 'GET',
            data: { action: 'parse' },
            dataType: 'json'
        })
        .done(function(response) {
            if (response.success) {
                console.log('XML Data:', response.data);
                showToast('success', 'Loaded', `Found ${response.data.tasks.length} tasks in XML`);
                
                // You can use this data as needed
                // For example, display in a modal or merge with existing tasks
            } else {
                showToast('error', 'Load Failed', response.message);
            }
        })
        .fail(function() {
            showToast('error', 'Load Failed', 'Could not load XML file.');
        });
    }

    /**
     * Load XML directly using jQuery AJAX (demonstrates XML parsing)
     */
    function loadXMLDirect() {
        $.ajax({
            url: 'data/tasks.xml',
            method: 'GET',
            dataType: 'xml'
        })
        .done(function(xml) {
            const tasks = [];
            
            $(xml).find('task').each(function() {
                tasks.push({
                    id: $(this).attr('id'),
                    title: $(this).find('title').text(),
                    description: $(this).find('description').text(),
                    category: $(this).find('category').text(),
                    priority: $(this).find('priority').text(),
                    status: $(this).find('status').text(),
                    due_date: $(this).find('due_date').text(),
                    created_at: $(this).find('created_at').text()
                });
            });
            
            console.log('Tasks from XML:', tasks);
            showToast('success', 'XML Loaded', `Parsed ${tasks.length} tasks from XML`);
            
            return tasks;
        })
        .fail(function() {
            showToast('error', 'Error', 'Could not load XML file');
        });
    }
})(jQuery);

// ==========================================
// INITIALIZE APP
// ==========================================
$(document).ready(function() {
    TaskMaster.init();
});

// Expose to global scope for debugging
window.TaskMaster = TaskMaster;