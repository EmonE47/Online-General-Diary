<?php
/**
 * Custom SQL Panel - Admin Panel
 * Online General Diary System
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/security.php';

// Require admin access
requireAdmin();

$pageTitle = 'SQL Panel - Admin Panel';

$queryResult = null;
$queryError = '';
$querySuccess = '';

// Handle SQL query execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'execute_query') {
        $query = $_POST['query'] ?? '';
        
        if (!empty($query)) {
            $result = executeSafeQuery($query);
            
            if (isset($result['success'])) {
                $queryResult = $result['data'];
                $querySuccess = 'Query executed successfully. ' . $result['row_count'] . ' rows returned.';
            } else {
                $queryError = $result['error'];
            }
        } else {
            $queryError = 'Please enter a SQL query';
        }
    } elseif ($action === 'predefined_query') {
        $queryId = $_POST['predefined_query'] ?? '';
        
        if (!empty($queryId)) {
            $predefinedQueries = getPredefinedQueries();
            $selectedQuery = $predefinedQueries[$queryId] ?? null;
            
            if ($selectedQuery) {
                $result = executeSafeQuery($selectedQuery['query']);
                
                if (isset($result['success'])) {
                    $queryResult = $result['data'];
                    $querySuccess = 'Predefined query executed successfully. ' . $result['row_count'] . ' rows returned.';
                } else {
                    $queryError = $result['error'];
                }
            }
        }
    }
}

// Get predefined queries
$predefinedQueries = getPredefinedQueries();
?>

<?php include '../header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">SQL Panel</li>
    </ol>
</nav>

<!-- Warning Alert -->
<div class="alert alert-warning" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Warning:</strong> This panel allows execution of SELECT queries only. 
    All queries are logged and monitored for security purposes.
</div>

<div class="row">
    <!-- Query Input -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-database me-2"></i>Custom SQL Query</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="execute_query">
                    
                    <div class="mb-3">
                        <label for="query" class="form-label">SQL Query (SELECT only)</label>
                        <textarea class="form-control" id="query" name="query" rows="8" 
                                  placeholder="Enter your SELECT query here..." required><?php echo htmlspecialchars($_POST['query'] ?? ''); ?></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Only SELECT statements are allowed. Maximum 1000 characters.
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-play me-2"></i>Execute Query
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearQuery()">
                            <i class="fas fa-eraser me-2"></i>Clear
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="formatQuery()">
                            <i class="fas fa-code me-2"></i>Format
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Predefined Queries -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Predefined Queries</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="predefinedForm">
                    <input type="hidden" name="action" value="predefined_query">
                    
                    <div class="mb-3">
                        <label for="predefined_query" class="form-label">Select Predefined Query</label>
                        <select class="form-select" id="predefined_query" name="predefined_query" onchange="showQueryDescription()">
                            <option value="">Choose a predefined query...</option>
                            <?php foreach ($predefinedQueries as $index => $query): ?>
                                <option value="<?php echo $index; ?>" data-description="<?php echo htmlspecialchars($query['description']); ?>" data-query="<?php echo htmlspecialchars($query['query']); ?>">
                                    <?php echo htmlspecialchars($query['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="queryDescription" class="alert alert-info" style="display: none;">
                        <h6><i class="fas fa-info-circle me-2"></i>Description:</h6>
                        <p id="descriptionText" class="mb-0"></p>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-play me-2"></i>Execute Predefined Query
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="loadPredefinedQuery()">
                            <i class="fas fa-edit me-2"></i>Load into Editor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Query Results -->
    <div class="col-lg-4">
        <!-- Messages -->
        <?php if ($querySuccess): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $querySuccess; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($queryError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $queryError; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Query Results -->
        <?php if ($queryResult !== null): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Query Results</h5>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="exportResults()">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="copyResults()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($queryResult)): ?>
                        <div class="table-responsive" style="max-height: 500px;">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <?php foreach (array_keys($queryResult[0]) as $column): ?>
                                            <th><?php echo htmlspecialchars($column); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($queryResult as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $value): ?>
                                                <td><?php echo htmlspecialchars($value ?? ''); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-table fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No results returned</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Database Schema Info -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Database Schema</h5>
            </div>
            <div class="card-body">
                <h6>Available Tables:</h6>
                <ul class="list-unstyled">
                    <li><code>users</code> - User accounts and profiles</li>
                    <li><code>gds</code> - General Diary records</li>
                    <li><code>gd_statuses</code> - GD status definitions</li>
                    <li><code>files</code> - Uploaded files</li>
                    <li><code>admin_notes</code> - Admin notes on GDs</li>
                    <li><code>notifications</code> - System notifications</li>
                    <li><code>activity_log</code> - System activity log</li>
                </ul>
                
                <h6 class="mt-3">Sample Queries:</h6>
                <div class="small">
                    <code>SELECT * FROM users WHERE role = 'si'</code><br>
                    <code>SELECT COUNT(*) FROM gds WHERE DATE(created_at) = CURDATE()</code><br>
                    <code>SELECT g.gd_number, u.f_name, u.l_name FROM gds g JOIN users u ON g.user_id = u.user_id</code>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearQuery() {
    document.getElementById('query').value = '';
}

function formatQuery() {
    const query = document.getElementById('query').value;
    // Simple formatting - add line breaks after keywords
    const formatted = query
        .replace(/\bSELECT\b/gi, '\nSELECT')
        .replace(/\bFROM\b/gi, '\nFROM')
        .replace(/\bWHERE\b/gi, '\nWHERE')
        .replace(/\bJOIN\b/gi, '\nJOIN')
        .replace(/\bORDER BY\b/gi, '\nORDER BY')
        .replace(/\bGROUP BY\b/gi, '\nGROUP BY')
        .replace(/\bHAVING\b/gi, '\nHAVING')
        .trim();
    
    document.getElementById('query').value = formatted;
}

function showQueryDescription() {
    const select = document.getElementById('predefined_query');
    const selectedOption = select.options[select.selectedIndex];
    const description = selectedOption.getAttribute('data-description');
    const descriptionDiv = document.getElementById('queryDescription');
    const descriptionText = document.getElementById('descriptionText');
    
    if (description) {
        descriptionText.textContent = description;
        descriptionDiv.style.display = 'block';
    } else {
        descriptionDiv.style.display = 'none';
    }
}

function loadPredefinedQuery() {
    const select = document.getElementById('predefined_query');
    const selectedOption = select.options[select.selectedIndex];
    const query = selectedOption.getAttribute('data-query');
    
    if (query) {
        document.getElementById('query').value = query;
    }
}

function exportResults() {
    if (<?php echo json_encode($queryResult !== null && !empty($queryResult)); ?>) {
        const table = document.querySelector('.table');
        if (table) {
            exportToCSV('queryResults', 'sql_query_results');
        }
    }
}

function copyResults() {
    if (<?php echo json_encode($queryResult !== null && !empty($queryResult)); ?>) {
        const table = document.querySelector('.table');
        if (table) {
            const tableText = table.innerText;
            navigator.clipboard.writeText(tableText).then(function() {
                alert('Results copied to clipboard!');
            });
        }
    }
}

// Auto-resize textarea
document.getElementById('query').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') {
        document.querySelector('form').submit();
    }
});
</script>

<style>
.table-responsive {
    border-radius: 8px;
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

#query {
    font-family: 'Courier New', monospace;
    font-size: 14px;
}

code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.9em;
}
</style>

<?php include '../footer.php'; ?>
