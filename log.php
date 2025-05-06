<?php
// Server-side log processing - no AJAX needed
$logContent = [];
$rawLogContent = '';
$logError = null;
$activeLogType = isset($_GET['log_type']) ? $_GET['log_type'] : 'admin';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$filterType = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Security check - only allow specific log types
$allowedLogTypes = ['admin', 'farmer', 'client'];
if (!in_array($activeLogType, $allowedLogTypes)) {
    $activeLogType = 'admin'; // Default to admin if invalid type
}

// Path to log file based on selected type
$logFilePath = $activeLogType . '_login.log';

// Process log file if requested
if (isset($_GET['view_logs'])) {
    // Check if log file exists and is readable
    if (!file_exists($logFilePath)) {
        $logError = [
            'error' => 'Log file not found',
            'message' => "The log file at '$logFilePath' does not exist."
        ];
    } elseif (!is_readable($logFilePath)) {
        $logError = [
            'error' => 'Log file not readable',
            'message' => "The log file at '$logFilePath' exists but is not readable. Check file permissions."
        ];
    } else {
        // Read the log file content
        $rawLogContent = file_get_contents($logFilePath);
        
        if ($rawLogContent === false) {
            $logError = [
                'error' => 'Failed to read log file',
                'message' => "Could not read the contents of '$logFilePath'."
            ];
        } elseif (empty(trim($rawLogContent))) {
            $logError = [
                'error' => 'Log file is empty',
                'message' => "The log file at '$logFilePath' exists but is empty."
            ];
        } else {
            // Parse log entries
            $logLines = explode("\n", $rawLogContent);
            
            foreach ($logLines as $line) {
                // Skip empty lines
                if (empty(trim($line))) {
                    continue;
                }
                
                // Parse log format - this regex matches [TIMESTAMP] [STATUS] [USER] MESSAGE
                if (preg_match('/\[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)/', $line, $matches)) {
                    $timestamp = $matches[1];
                    $status = strtolower($matches[2]);
                    $user = $matches[3];
                    $message = $matches[4];
                    
                    // Determine log type based on message content
                    $type = 'other';
                    if (stripos($message, 'login') !== false) {
                        $type = 'login';
                    } elseif (stripos($message, 'registration') !== false) {
                        $type = 'registration';
                    } elseif (stripos($message, 'attempt') !== false) {
                        $type = 'attempt';
                    }
                    
                    // Apply filtering if needed
                    $includeEntry = true;
                    
                    // Filter by type if specified
                    if ($filterType !== 'all' && $type !== $filterType) {
                        $includeEntry = false;
                    }
                    
                    // Filter by search term if specified
                    if (!empty($searchTerm)) {
                        $searchableContent = $timestamp . ' ' . $status . ' ' . $user . ' ' . $message;
                        if (stripos($searchableContent, $searchTerm) === false) {
                            $includeEntry = false;
                        }
                    }
                    
                    if ($includeEntry) {
                        $logContent[] = [
                            'timestamp' => $timestamp,
                            'status' => $status,
                            'user' => $user,
                            'message' => $message,
                            'type' => $type
                        ];
                    }
                } else {
                    // If line doesn't match the expected format, add it as raw data
                    $logContent[] = [
                        'timestamp' => 'Unknown',
                        'status' => 'info',
                        'user' => 'System',
                        'message' => 'Unparsed log entry: ' . $line,
                        'type' => 'other'
                    ];
                }
            }
        }
    }
}

// Handle CSV export if requested
if (isset($_GET['export_csv']) && isset($_GET['log_type'])) {
    $exportLogType = $_GET['log_type'];
    
    // Security check again
    if (in_array($exportLogType, $allowedLogTypes)) {
        $exportFilePath = $exportLogType . '_login.log';
        
        if (file_exists($exportFilePath) && is_readable($exportFilePath)) {
            // Create CSV content
            $csvContent = "Timestamp,User,Status,Message\n";
            $rawExportContent = file_get_contents($exportFilePath);
            $exportLines = explode("\n", $rawExportContent);
            
            foreach ($exportLines as $line) {
                if (empty(trim($line))) continue;
                
                if (preg_match('/\[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)/', $line, $matches)) {
                    $timestamp = $matches[1];
                    $status = $matches[2];
                    $user = $matches[3];
                    $message = $matches[4];
                    
                    // Apply filtering if export with filters
                    $includeInExport = true;
                    if (isset($_GET['filter']) && $_GET['filter'] !== 'all') {
                        $type = 'other';
                        if (stripos($message, 'login') !== false) $type = 'login';
                        elseif (stripos($message, 'registration') !== false) $type = 'registration';
                        elseif (stripos($message, 'attempt') !== false) $type = 'attempt';
                        
                        if ($type !== $_GET['filter']) {
                            $includeInExport = false;
                        }
                    }
                    
                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                        $searchableContent = $timestamp . ' ' . $status . ' ' . $user . ' ' . $message;
                        if (stripos($searchableContent, $_GET['search']) === false) {
                            $includeInExport = false;
                        }
                    }
                    
                    if ($includeInExport) {
                        $csvContent .= '"' . $timestamp . '","' . $user . '","' . $status . '","' . str_replace('"', '""', $message) . "\"\n";
                    }
                }
            }
            
            // Output CSV headers
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $exportLogType . '_logs_' . date('Y-m-d') . '.csv"');
            echo $csvContent;
            exit;
        }
    }
}

// Check if the log files exist before page load
$adminLogExists = file_exists('admin_login.log');
$farmerLogExists = file_exists('farmer_login.log');
$clientLogExists = file_exists('client_login.log');

$logStatus = [
    'admin' => $adminLogExists ? 'exists' : 'not found',
    'farmer' => $farmerLogExists ? 'exists' : 'not found',
    'client' => $clientLogExists ? 'exists' : 'not found'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Log Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function openLogoutModal() {
            document.getElementById('logout-modal').classList.remove('hidden');
        }
        function closeLogoutModal() {
            document.getElementById('logout-modal').classList.add('hidden');
        }
        function confirmLogout() {
            window.location.href = 'account.php'; // Change this to your logout URL
        }
        
        // Tab navigation
        function showTab(tabName) {
            // Hide all content
            document.querySelectorAll('[id$="-content"]').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('[id$="-tab"]').forEach(tab => {
                tab.classList.remove('bg-[#115D5B]', 'text-white');
                tab.classList.add('text-gray-700');
            });
            
            // Show selected content
            document.getElementById(tabName + '-content').classList.remove('hidden');
            
            // Highlight selected tab
            const activeTab = document.getElementById(tabName + '-tab');
            if (activeTab) {
                activeTab.classList.remove('text-gray-700');
                activeTab.classList.add('bg-[#115D5B]', 'text-white');
            } else if (tabName === 'home') {
                // For home button which has a different structure
                document.getElementById('home-tab').classList.add('bg-[#115D5B]', 'text-white');
            }
        }
        
        function toggleRawView() {
            const rawView = document.getElementById('raw-log-view');
            const toggleButton = document.getElementById('toggle-raw-view');
            
            if (rawView.classList.contains('hidden')) {
                rawView.classList.remove('hidden');
                toggleButton.textContent = 'Hide Raw Log Format';
            } else {
                rawView.classList.add('hidden');
                toggleButton.textContent = 'Show Raw Log Format';
            }
        }
        
        // Initialize the page with home tab selected
        document.addEventListener('DOMContentLoaded', function() {
            showTab('<?php echo isset($_GET['view_logs']) ? "logs" : "home"; ?>');
        });
    </script>
</head>
<body class="bg-gray-100 font-sans">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-[#115D5B]">Admin Dashboard</h1>
            <button onclick="openLogoutModal()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                Logout
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Navigation Tabs -->
        <div class="flex flex-wrap mb-6 bg-white rounded-lg shadow-md overflow-hidden">
            <button id="home-tab" onclick="showTab('home')" class="px-6 py-3 text-gray-700 hover:bg-gray-100 transition duration-150">
                Home
            </button>
            <button id="logs-tab" onclick="showTab('logs')" class="px-6 py-3 text-gray-700 hover:bg-gray-100 transition duration-150">
                Logs
            </button>
            <!-- Additional tabs would go here -->
        </div>

        <!-- Home Content -->
        <div id="home-content" class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Dashboard Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 border rounded-lg bg-gray-50">
                    <h3 class="font-medium text-lg mb-2">Log Files Status</h3>
                    <ul class="space-y-2">
                        <li class="flex justify-between">
                            <span>Admin Logs:</span>
                            <span class="<?php echo $adminLogExists ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $logStatus['admin']; ?>
                            </span>
                        </li>
                        <li class="flex justify-between">
                            <span>Farmer Logs:</span>
                            <span class="<?php echo $farmerLogExists ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $logStatus['farmer']; ?>
                            </span>
                        </li>
                        <li class="flex justify-between">
                            <span>Client Logs:</span>
                            <span class="<?php echo $clientLogExists ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $logStatus['client']; ?>
                            </span>
                        </li>
                    </ul>
                </div>
                
                <!-- Quick Actions -->
                <div class="p-4 border rounded-lg bg-gray-50">
                    <h3 class="font-medium text-lg mb-2">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="?view_logs=1&log_type=admin" class="block w-full bg-[#115D5B] hover:bg-[#0F3D3A] text-white text-center py-2 rounded">
                            View Admin Logs
                        </a>
                        <a href="?view_logs=1&log_type=farmer" class="block w-full bg-[#115D5B] hover:bg-[#0F3D3A] text-white text-center py-2 rounded">
                            View Farmer Logs
                        </a>
                        <a href="?view_logs=1&log_type=client" class="block w-full bg-[#115D5B] hover:bg-[#0F3D3A] text-white text-center py-2 rounded">
                            View Client Logs
                        </a>
                    </div>
                </div>
                
                <!-- System Info -->
                <div class="p-4 border rounded-lg bg-gray-50">
                    <h3 class="font-medium text-lg mb-2">System Information</h3>
                    <ul class="space-y-2">
                        <li class="flex justify-between">
                            <span>PHP Version:</span>
                            <span><?php echo phpversion(); ?></span>
                        </li>
                        <li class="flex justify-between">
                            <span>Server:</span>
                            <span><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                        </li>
                        <li class="flex justify-between">
                            <span>Time:</span>
                            <span><?php echo date('Y-m-d H:i:s'); ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Logs Content -->
        <div id="logs-content" class="bg-white rounded-lg shadow-md p-6 hidden">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Log Management</h2>
                <div class="space-x-2">
                    <a href="?view_logs=1&log_type=admin" class="px-4 py-2 rounded <?php echo $activeLogType == 'admin' ? 'bg-[#1A5E5C] text-white' : 'bg-[#0F3D3A] text-white hover:bg-[#1A5E5C]'; ?>">
                        Admin Logs
                    </a>
                    <a href="?view_logs=1&log_type=farmer" class="px-4 py-2 rounded <?php echo $activeLogType == 'farmer' ? 'bg-[#1A5E5C] text-white' : 'bg-[#0F3D3A] text-white hover:bg-[#1A5E5C]'; ?>">
                        Farmer Logs
                    </a>
                    <a href="?view_logs=1&log_type=client" class="px-4 py-2 rounded <?php echo $activeLogType == 'client' ? 'bg-[#1A5E5C] text-white' : 'bg-[#0F3D3A] text-white hover:bg-[#1A5E5C]'; ?>">
                        Client Logs
                    </a>
                </div>
            </div>
            
            <?php if (isset($_GET['view_logs'])): ?>
                <div class="bg-[#0F3D3A] text-white p-4 rounded-t">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium"><?php echo ucfirst($activeLogType); ?> Logs</h3>
                        <span id="log-counter">
                            <?php echo count($logContent) . ' log entries found'; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Filters and Controls -->
                <div class="bg-[#F3F4F6] p-4 border-x border-t">
                    <form action="" method="GET" class="flex flex-wrap gap-2 items-center">
                        <input type="hidden" name="view_logs" value="1">
                        <input type="hidden" name="log_type" value="<?php echo $activeLogType; ?>">
                        
                        <div class="flex-1 min-w-[200px]">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" 
                                   class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#115D5B]">
                        </div>
                        
                        <div class="w-40">
                            <label for="filter" class="block text-sm font-medium text-gray-700 mb-1">Filter By Type</label>
                            <select id="filter" name="filter" 
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-[#115D5B]">
                                <option value="all" <?php echo $filterType == 'all' ? 'selected' : ''; ?>>All Types</option>
                                <option value="login" <?php echo $filterType == 'login' ? 'selected' : ''; ?>>Login</option>
                                <option value="registration" <?php echo $filterType == 'registration' ? 'selected' : ''; ?>>Registration</option>
                                <option value="attempt" <?php echo $filterType == 'attempt' ? 'selected' : ''; ?>>Attempts</option>
                                <option value="other" <?php echo $filterType == 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="self-end">
                            <button type="submit" class="bg-[#115D5B] hover:bg-[#0F3D3A] text-white px-4 py-2 rounded">
                                Apply Filters
                            </button>
                        </div>
                        
                        <div class="self-end ml-auto">
                            <a href="?export_csv=1&log_type=<?php echo $activeLogType; ?><?php 
                                echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; 
                                echo $filterType != 'all' ? '&filter=' . urlencode($filterType) : ''; 
                            ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                Export to CSV
                            </a>
                            <button type="button" id="toggle-raw-view" onclick="toggleRawView()" 
                                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                                Show Raw Log Format
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Raw Log View (Hidden by default) -->
                <div id="raw-log-view" class="hidden border p-4 mt-2 bg-gray-900 text-green-400 rounded overflow-x-auto">
                    <pre><?php echo htmlspecialchars($rawLogContent); ?></pre>
                </div>
                
                <!-- Log Display -->
                <div id="log-container" class="border rounded-b overflow-hidden">
                    <?php if ($logError): ?>
                        <div class="text-red-500 bg-red-100 border border-red-400 rounded p-4 mb-2">
                            <strong>Error: </strong><?php echo $logError['error']; ?><br>
                            <?php echo $logError['message']; ?>
                        </div>
                    <?php elseif (empty($logContent)): ?>
                        <div class="text-yellow-500 bg-yellow-100 border border-yellow-400 rounded p-4 mb-2">
                            No log entries were found or could be parsed.
                        </div>
                    <?php else: ?>
                        <?php foreach ($logContent as $log): ?>
                            <?php
                                $statusClass = '';
                                if ($log['status'] === 'success') {
                                    $statusClass = 'bg-green-100 border-green-400 text-green-700';
                                } elseif ($log['status'] === 'failed' || $log['status'] === 'error') {
                                    $statusClass = 'bg-red-100 border-red-400 text-red-700';
                                } elseif ($log['status'] === 'info') {
                                    $statusClass = 'bg-blue-100 border-blue-400 text-blue-700';
                                } elseif ($log['status'] === 'warning') {
                                    $statusClass = 'bg-yellow-100 border-yellow-400 text-yellow-700';
                                } else {
                                    $statusClass = 'bg-gray-100 border-gray-400 text-gray-700';
                                }
                            ?>
                            <div class="flex flex-col md:flex-row justify-between border px-4 py-3 mb-2 <?php echo $statusClass; ?>">
                                <div class="flex items-center">
                                    <span class="font-bold mr-2"><?php echo htmlspecialchars($log['timestamp']); ?></span>
                                    <span class="mr-2">|</span>
                                    <span class="mr-2"><?php echo htmlspecialchars($log['user']); ?></span>
                                </div>
                                <div>
                                    <span><?php echo htmlspecialchars($log['message']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="text-center p-8 bg-gray-50 rounded">
                    <p class="text-lg text-gray-600">Please select a log type from the options above to view logs.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 max-w-sm mx-4">
            <h3 class="text-lg font-medium mb-4">Confirm Logout</h3>
            <p class="mb-6">Are you sure you want to log out?</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeLogoutModal()" class="px-4 py-2 border rounded hover:bg-gray-100">
                    Cancel
                </button>
                <button onclick="confirmLogout()" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Yes, Log Out
                </button>
            </div>
        </div>
    </div>
</body>
</html>