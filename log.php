<?php
// Handle AJAX request for logs
if (isset($_GET['action']) && $_GET['action'] === 'get_logs') {
    // Set proper content type for JSON response
    header('Content-Type: application/json');

    // Path to log file - make sure this is the correct path
    $logFilePath = 'login_detailed_debug.log';
    
    // Check if log file exists and is readable
    if (!file_exists($logFilePath)) {
        echo json_encode([
            'error' => 'Log file not found',
            'message' => "The log file at '$logFilePath' does not exist.",
            'rawLogs' => '',
            'parsedLogs' => []
        ]);
        exit;
    }

    if (!is_readable($logFilePath)) {
        echo json_encode([
            'error' => 'Log file not readable',
            'message' => "The log file at '$logFilePath' exists but is not readable. Check file permissions.",
            'rawLogs' => '',
            'parsedLogs' => []
        ]);
        exit;
    }

    // Read the log file content
    $rawLogs = file_get_contents($logFilePath);
    
    if ($rawLogs === false) {
        echo json_encode([
            'error' => 'Failed to read log file',
            'message' => "Could not read the contents of '$logFilePath'.",
            'rawLogs' => '',
            'parsedLogs' => []
        ]);
        exit;
    }

    // If log file is empty
    if (empty(trim($rawLogs))) {
        echo json_encode([
            'error' => 'Log file is empty',
            'message' => "The log file at '$logFilePath' exists but is empty.",
            'rawLogs' => '',
            'parsedLogs' => []
        ]);
        exit;
    }

    // Parse log entries
    $parsedLogs = [];
    $logLines = explode("\n", $rawLogs);

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
            
            $parsedLogs[] = [
                'timestamp' => $timestamp,
                'status' => $status,
                'user' => $user,
                'message' => $message,
                'type' => $type
            ];
        } else {
            // If line doesn't match the expected format, add it as raw data
            $parsedLogs[] = [
                'timestamp' => 'Unknown',
                'status' => 'info',
                'user' => 'System',
                'message' => 'Unparsed log entry: ' . $line,
                'type' => 'other'
            ];
        }
    }

    // Return both raw and parsed logs
    echo json_encode([
        'success' => true,
        'message' => 'Logs retrieved successfully.',
        'rawLogs' => $rawLogs,
        'parsedLogs' => $parsedLogs
    ]);
    exit;
}

// Check if the log file exists before page load
$logExists = file_exists('login_details_debug.log');
$logStatus = $logExists ? 'exists' : 'not found';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Log Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Global variables
        let logLoadingAttempted = false;
        
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
            
            // If logs tab is selected, initialize logs
            if (tabName === 'logs' && !logLoadingAttempted) {
                initializeLogs();
                logLoadingAttempted = true;
            }
        }
        
        // Function to filter logs
        function filterLogs() {
            const filterType = document.getElementById('filter-type').value;
            const searchInput = document.getElementById('search-input').value.toLowerCase();
            const logItems = document.querySelectorAll('.log-item');
            
            logItems.forEach(item => {
                let content = item.textContent.toLowerCase();
                let shouldShow = true;
                
                // Filter by type
                if (filterType !== 'all' && !item.classList.contains(filterType)) {
                    shouldShow = false;
                }
                
                // Filter by search term
                if (searchInput && !content.includes(searchInput)) {
                    shouldShow = false;
                }
                
                item.style.display = shouldShow ? 'flex' : 'none';
            });
            
            // Update counter
            updateLogCounter();
        }
        
        // Update the log counter
        function updateLogCounter() {
            const visibleLogs = document.querySelectorAll('.log-item:not([style*="display: none"])').length;
            const totalLogs = document.querySelectorAll('.log-item').length;
            document.getElementById('log-counter').textContent = `Showing ${visibleLogs} of ${totalLogs} logs`;
        }
        
        // Initialize logs
        function initializeLogs() {
            // Show loading indicator
            const logContainer = document.getElementById('log-container');
            logContainer.innerHTML = '<div class="text-white text-center py-4">Loading logs...</div>';
            
            // Use AJAX to fetch logs
            fetch(window.location.pathname + '?action=get_logs')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Received log data:", data); // Debug
                    
                    // Check for errors
                    if (data.error) {
                        logContainer.innerHTML = `<div class="text-red-500 bg-red-100 border border-red-400 rounded p-4 mb-2">
                            <strong>Error:</strong> ${data.error}<br>
                            ${data.message || ''}
                        </div>`;
                        document.getElementById('raw-log-content').textContent = 'No logs available.';
                        return;
                    }
                    
                    // Clear loading indicator
                    logContainer.innerHTML = '';
                    
                    // Update raw log view
                    const rawLogContent = document.getElementById('raw-log-content');
                    rawLogContent.textContent = data.rawLogs;
                    
                    // Check if we have any parsed logs
                    if (data.parsedLogs.length === 0) {
                        logContainer.innerHTML = `<div class="text-yellow-500 bg-yellow-100 border border-yellow-400 rounded p-4 mb-2">
                            No log entries were found or could be parsed.
                        </div>`;
                        return;
                    }
                    
                    // Generate and add log items to container
                    data.parsedLogs.forEach(log => {
                        let statusClass;
                        if (log.status === 'success') {
                            statusClass = 'bg-green-100 border-green-400 text-green-700';
                        } else if (log.status === 'failed' || log.status === 'error') {
                            statusClass = 'bg-red-100 border-red-400 text-red-700';
                        } else if (log.status === 'info') {
                            statusClass = 'bg-blue-100 border-blue-400 text-blue-700';
                        } else if (log.status === 'warning') {
                            statusClass = 'bg-yellow-100 border-yellow-400 text-yellow-700';
                        } else {
                            statusClass = 'bg-gray-100 border-gray-400 text-gray-700';
                        }
                        
                        const logItem = document.createElement('div');
                        logItem.className = `log-item ${log.status} ${log.type} flex flex-col md:flex-row justify-between border px-4 py-3 mb-2 rounded ${statusClass}`;
                        logItem.innerHTML = `
                            <div class="flex items-center">
                                <span class="font-bold mr-2">${log.timestamp}</span>
                                <span class="mr-2">|</span>
                                <span class="mr-2">${log.user}</span>
                            </div>
                            <div>
                                <span>${log.message}</span>
                            </div>
                        `;
                        logContainer.appendChild(logItem);
                    });
                    
                    // Update counter
                    updateLogCounter();
                })
                .catch(error => {
                    console.error('Error loading logs:', error);
                    logContainer.innerHTML = `
                        <div class="text-red-500 bg-red-100 border border-red-400 rounded p-4 mb-2">
                            <strong>Error loading logs:</strong> ${error.message}
                            <div class="mt-2">
                                <p>Possible causes:</p>
                                <ul class="list-disc list-inside ml-4">
                                    <li>The log file does not exist</li>
                                    <li>The log file has incorrect permissions</li>
                                    <li>The server encountered an error processing the request</li>
                                </ul>
                            </div>
                        </div>`;
                });
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
            showTab('home');
        });
    </script>
</head>
<body class="bg-[#144D42] flex">
    <!-- Sidebar -->
    <div class="w-1/4 bg-white p-6 h-screen flex flex-col justify-between">
        <div>
            <div class="flex flex-col items-center text-center">
                <img src="profile-pic.jpg" alt="Profile" class="w-20 h-20 rounded-full border mb-2">
                <h2 class="font-bold">Ricardo Dela Cruz</h2>
                <p class="text-sm text-gray-500">jpcn@gmail.com</p>
                <p class="text-sm italic">Farmer</p>
            </div>
            <div class="mt-6">
                <button id="home-tab" class="w-full text-left bg-[#115D5B] text-white py-2 px-4 rounded flex items-center gap-2 mb-2" onclick="showTab('home')">
                    &#127968; Home
                </button>
                <ul class="mt-4 space-y-2">
                    <li id="inventory-tab" class="flex items-center gap-2 cursor-pointer text-gray-700 hover:text-black py-2 px-4 rounded" onclick="showTab('inventory')">
                        &#128230; Inventory
                    </li>
                    <li id="notifications-tab" class="flex items-center gap-2 cursor-pointer text-gray-700 hover:text-black py-2 px-4 rounded" onclick="showTab('notifications')">
                        &#128276; Notifications
                    </li>
                    <li id="logs-tab" class="flex items-center gap-2 cursor-pointer text-gray-700 hover:text-black py-2 px-4 rounded" onclick="showTab('logs')">
                        &#128195; System Logs
                    </li>
                    <li id="profile-tab" class="flex items-center gap-2 cursor-pointer text-gray-700 hover:text-black py-2 px-4 rounded" onclick="showTab('profile')">
                        &#128100; Profile
                    </li>
                    <li class="flex items-center gap-2 cursor-pointer text-gray-700 hover:text-red-500 py-2 px-4 rounded" onclick="openLogoutModal()">
                        &#128682; Logout
                    </li>
                </ul>
            </div>
        </div>
        <footer class="text-center text-xs text-gray-500">
            &copy; 2025 Camarines Norte Lowland Rainfed Research Station. All Rights Reserved.
        </footer>
    </div>
    
    <!-- Main Content -->
    <div class="w-3/4 p-6">
        <!-- Tab Contents -->
        <div id="home-content" class="bg-[#0F3D3A] p-10 rounded-lg border border-green-700 w-full max-w-6xl mx-auto h-[615px] overflow-y-auto">
            <h2 class="text-2xl font-bold text-white">Welcome to the Admin Dashboard</h2>
            <p class="text-white mt-2">Select an option from the sidebar to get started.</p>
            <?php if (!$logExists): ?>
            <div class="mt-6 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded" role="alert">
                <p class="font-bold">Notice</p>
                <p>The log file (login_details_debug.log) was not found. System logs may not be available.</p>
            </div>
            <?php endif; ?>
        </div>

        <div id="inventory-content" class="bg-[#0F3D3A] p-10 rounded-lg border border-green-700 w-full max-w-6xl mx-auto h-[615px] overflow-y-auto hidden">
            <h2 class="text-2xl font-bold text-white">Inventory Management</h2>
            <p class="text-white mt-2">Inventory content will appear here.</p>
        </div>

        <div id="notifications-content" class="bg-[#0F3D3A] p-10 rounded-lg border border-green-700 w-full max-w-6xl mx-auto h-[615px] overflow-y-auto hidden">
            <h2 class="text-2xl font-bold text-white">Notifications</h2>
            <p class="text-white mt-2">Notifications will appear here.</p>
        </div>

        <div id="profile-content" class="bg-[#0F3D3A] p-10 rounded-lg border border-green-700 w-full max-w-6xl mx-auto h-[615px] overflow-y-auto hidden">
            <h2 class="text-2xl font-bold text-white">Profile Settings</h2>
            <p class="text-white mt-2">Profile settings will appear here.</p>
        </div>
        
        <!-- Log Interface -->
        <div id="logs-content" class="bg-[#0F3D3A] p-10 rounded-lg border border-green-700 w-full max-w-6xl mx-auto h-[615px] overflow-y-auto hidden">
            <div class="text-white">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">System Login Logs</h2>
                    <div class="text-sm" id="log-counter">Showing 0 of 0 logs</div>
                </div>
                
                <!-- Filter Controls -->
                <div class="bg-[#1A5E5C] p-4 rounded-lg mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <label for="filter-type" class="block text-sm font-medium mb-1">Filter by Status</label>
                            <select id="filter-type" class="w-full bg-[#0F3D3A] border border-green-700 rounded p-2 text-white" onchange="filterLogs()">
                                <option value="all">All Activities</option>
                                <option value="login">Logins</option>
                                <option value="attempt">Attempts</option>
                                <option value="registration">Registrations</option>
                                <option value="success">Successful Actions</option>
                                <option value="info">Informational</option>
                                <option value="error">Errors</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label for="search-input" class="block text-sm font-medium mb-1">Search</label>
                            <input type="text" id="search-input" placeholder="Search by user, IP, or message..." 
                                   class="w-full bg-[#0F3D3A] border border-green-700 rounded p-2 text-white" 
                                   oninput="filterLogs()">
                        </div>
                        <div class="flex items-end">
                            <button class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded">
                                Export Logs
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Log Display -->
                <div id="log-container" class="space-y-2">
                    <!-- Log items will be added here by JavaScript -->
                </div>
                
                <!-- Raw Log View -->
                <div id="raw-log-view" class="mt-6 bg-[#0A2E2C] p-4 rounded-lg hidden">
                    <pre id="raw-log-content" class="text-green-400 text-xs overflow-x-auto">
Loading logs...
                    </pre>
                </div>
                
                <!-- Raw Log View Toggle -->
                <div class="flex justify-between items-center mt-6">
                    <button id="toggle-raw-view" class="bg-[#115D5B] text-white py-2 px-4 rounded text-sm" onclick="toggleRawView()">
                        Show Raw Log Format
                    </button>
                    <button id="refresh-logs" class="bg-[#115D5B] text-white py-2 px-4 rounded text-sm" onclick="initializeLogs()">
                        Refresh Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Logout Modal -->
    <div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <h2 class="text-lg font-bold">Confirm Logout</h2>
            <p class="mt-2">Are you sure you want to logout?</p>
            <div class="mt-4 flex justify-center gap-4">
                <button onclick="confirmLogout()" class="bg-red-500 text-white px-4 py-2 rounded">Yes</button>
                <button onclick="closeLogoutModal()" class="bg-gray-300 px-4 py-2 rounded">No</button>
            </div>
        </div>
    </div>
</body>
</html>