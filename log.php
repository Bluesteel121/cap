<?php
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
if (isset($_GET['view_logs']) || !isset($_GET['view_logs'])) { // Default to viewing logs
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
            
            // Reverse the array to show latest logs first
            $logContent = array_reverse($logContent);
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

// *** FIX: Only include one database connection file ***
require_once 'db_connect.php';

// *** FIX: Check if session is already started before starting a new one ***
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user information directly from accounts table
$user_id = $_SESSION['user_id'] ?? 1; // Default to user ID 1 if not set

// *** FIX: Use the existing database connection from db_connect.php ***
// Assuming $conn is created in db_connect.php, if not uncomment the line below
// $conn = new mysqli("localhost", "root", "", "capstone");

$user_name = "Unknown";
$user_position = "Unknown";

// Only proceed if connection exists and is valid
if (isset($conn) && !$conn->connect_error) {
    $user_query = "SELECT name, position FROM accounts WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_result = $stmt->get_result();
        if ($user_info = $user_result->fetch_assoc()) {
            $user_name = $user_info['name'] ?? 'Unknown';
            $user_position = $user_info['position'] ?? 'Unknown';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
                toggleButton.classList.remove('bg-[#115D5B]');
                toggleButton.classList.add('bg-[#0F3D3A]');
            } else {
                rawView.classList.add('hidden');
                toggleButton.textContent = 'Show Raw Log Format';
                toggleButton.classList.remove('bg-[#0F3D3A]');
                toggleButton.classList.add('bg-[#115D5B]');
            }
        }
        
        // Initialize the page - always show logs by default
        document.addEventListener('DOMContentLoaded', function() {
            // No need to call showTab as we're always showing logs now
        });
    </script>
    <style>
        /* Custom scrollbar for WebKit browsers */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0F3D3A;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb {
            background: #CAEED5;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #90C29E;
        }
        
        /* Animation for status indicators */
        @keyframes pulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }
        .status-active {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body class="bg-[#F0F7F4]">
    <!-- Sidebar -->
    <aside class="w-1/4 bg-gradient-to-b from-[#115D5B] to-[#0F3D3A] p-6 h-screen fixed top-0 left-0 flex flex-col justify-between text-white shadow-xl">
        <div>
            <div class="flex flex-col items-center text-center mb-8">
                <div class="relative">
                    <img src="<?= isset($profile_pic) ? htmlspecialchars($profile_pic) : 'default_avatar.png' ?>" alt="Profile" class="w-24 h-24 rounded-full border-4 border-[#CAEED5] mb-3 object-cover shadow-md">
                    <?php if(isset($status) && $status == 'Active'): ?>
                        <span class="absolute bottom-3 right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-[#115D5B] status-active"></span>
                    <?php endif; ?>
                </div>
                <h2 class="font-bold text-lg"><?= htmlspecialchars($user_name) ?></h2>
                <p class="text-sm italic text-[#CAEED5]"><?= htmlspecialchars($user_position) ?></p>
                <?php if(isset($contact_num)): ?>
                    <p class="text-sm mt-1"><i class="fas fa-phone-alt text-xs mr-1"></i><?= htmlspecialchars($contact_num) ?></p>
                <?php endif; ?>
            </div>

            <nav class="mt-8">
                <ul class="space-y-3">
                    <li><a href="adminpage.php" class="flex items-center p-3 hover:bg-[#CAEED5] hover:text-[#115D5B] rounded-lg transition-all duration-200">
                        <i class="fas fa-home w-5 h-5 mr-3"></i>
                        Home</a></li>
                    
                    <li><a href="#" class="flex items-center p-3 hover:bg-[#CAEED5] hover:text-[#115D5B] rounded-lg transition-all duration-200">
                        <i class="fas fa-user w-5 h-5 mr-3"></i>
                        Profile</a></li>

                    <li><a href="#" class="flex items-center p-3 hover:bg-[#CAEED5] hover:text-[#115D5B] rounded-lg transition-all duration-200">
                        <i class="fas fa-bell w-5 h-5 mr-3"></i>
                        Notifications</a></li>
                        
                    <li><a href="log.php" class="flex items-center p-3 bg-[#CAEED5] text-[#115D5B] rounded-lg font-medium shadow-md">
                        <i class="fas fa-clipboard-list w-5 h-5 mr-3"></i>
                        System Logs</a></li>
                        
                    <li><a href="#" class="flex items-center p-3 text-red-300 hover:bg-red-500 hover:text-white rounded-lg transition-all duration-200" onclick="openLogoutModal()">
                        <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
                        Logout</a></li>
                </ul>
            </nav>
        </div>
        <footer class="text-center text-xs text-[#CAEED5]">&copy; 2025 Camarines Norte Lowland Rainfed Research Station</footer>
    </aside>
    
    <!-- Main Content -->
    <main class="w-3/4 ml-[25%] p-6">
        <div class="bg-gradient-to-br from-[#0F3D3A] to-[#0A2E2C] p-6 rounded-xl border border-[#115D5B] shadow-lg w-full max-w-7xl mx-auto h-[calc(100vh-3rem)] overflow-hidden flex flex-col">
            <!-- Header with Breadcrumb -->
            <div class="flex justify-between items-center mb-4 text-white">
                <div>
                    <h1 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-clipboard-list mr-3 text-[#CAEED5]"></i>
                        System Logs
                    </h1>
                    <div class="text-sm text-[#CAEED5] mt-1">
                        Dashboard / <span class="text-white">System Logs</span>
                    </div>
                </div>
                <div class="text-sm bg-[#115D5B] px-3 py-1 rounded-lg">
                    <i class="far fa-clock mr-1"></i> <?php echo date('F d, Y h:i A'); ?>
                </div>
            </div>
            
            <!-- Log Navigation Tabs -->
            <div class="flex items-center mb-4 bg-[#0A2E2C] rounded-lg p-1">
                <a href="?log_type=admin" class="flex-1 px-4 py-2 text-center font-medium rounded-lg transition-all duration-200 <?php echo $activeLogType == 'admin' ? 'bg-[#CAEED5] text-[#115D5B]' : 'text-white hover:bg-[#115D5B]'; ?>">
                    <i class="fas fa-user-shield mr-2"></i>Admin Logs
                </a>
                <a href="?log_type=farmer" class="flex-1 px-4 py-2 text-center font-medium rounded-lg transition-all duration-200 <?php echo $activeLogType == 'farmer' ? 'bg-[#CAEED5] text-[#115D5B]' : 'text-white hover:bg-[#115D5B]'; ?>">
                    <i class="fas fa-tractor mr-2"></i>Farmer Logs
                </a>
                <a href="?log_type=client" class="flex-1 px-4 py-2 text-center font-medium rounded-lg transition-all duration-200 <?php echo $activeLogType == 'client' ? 'bg-[#CAEED5] text-[#115D5B]' : 'text-white hover:bg-[#115D5B]'; ?>">
                    <i class="fas fa-users mr-2"></i>Client Logs
                </a>
            </div>
            
            <!-- Filters and Controls -->
            <div class="bg-[#115D5B] p-4 rounded-t-lg border-b border-[#CAEED5]/20">
                <form action="" method="GET" class="flex flex-wrap gap-3 items-end">
                    <input type="hidden" name="log_type" value="<?php echo $activeLogType; ?>">
                    
                    <div class="flex-1 min-w-[200px]">
                        <label for="search" class="block text-sm font-medium text-white mb-1">
                            <i class="fas fa-search mr-1"></i> Search Logs
                        </label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" 
                               placeholder="Search by keyword, user, or status..."
                               class="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-[#CAEED5] bg-[#0F3D3A] text-white border border-[#CAEED5]/30 placeholder-gray-400">
                    </div>
                    
                    <div class="w-48">
                        <label for="filter" class="block text-sm font-medium text-white mb-1">
                            <i class="fas fa-filter mr-1"></i> Filter By Type
                        </label>
                        <select id="filter" name="filter" 
                                class="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-[#CAEED5] bg-[#0F3D3A] text-white border border-[#CAEED5]/30">
                            <option value="all" <?php echo $filterType == 'all' ? 'selected' : ''; ?>>All Types</option>
                            <option value="login" <?php echo $filterType == 'login' ? 'selected' : ''; ?>>Login Activities</option>
                            <option value="registration" <?php echo $filterType == 'registration' ? 'selected' : ''; ?>>Registrations</option>
                            <option value="attempt" <?php echo $filterType == 'attempt' ? 'selected' : ''; ?>>Login Attempts</option>
                            <option value="other" <?php echo $filterType == 'other' ? 'selected' : ''; ?>>Other Actions</option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="bg-[#CAEED5] hover:bg-green-200 text-[#115D5B] px-4 py-2 rounded-md font-medium transition-all duration-200 flex items-center">
                            <i class="fas fa-search mr-2"></i> Apply Filters
                        </button>
                    </div>
                    
                    <div class="ml-auto flex gap-2">
                        <a href="?export_csv=1&log_type=<?php echo $activeLogType; ?><?php 
                            echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; 
                            echo $filterType != 'all' ? '&filter=' . urlencode($filterType) : ''; 
                        ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-all duration-200 flex items-center">
                            <i class="fas fa-file-csv mr-2"></i> Export CSV
                        </a>
                        <button type="button" id="toggle-raw-view" onclick="toggleRawView()" 
                                class="bg-[#115D5B] hover:bg-[#0F3D3A] text-white px-4 py-2 rounded-md transition-all duration-200 flex items-center">
                            <i class="fas fa-code mr-2"></i> Show Raw Log
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Raw Log View (Hidden by default) -->
            <div id="raw-log-view" class="hidden p-4 bg-[#0A2E2C] text-green-400 border border-green-800 rounded-md mb-3 overflow-auto max-h-[200px] font-mono text-sm">
                <pre><?php echo htmlspecialchars($rawLogContent); ?></pre>
            </div>
            
            <!-- Log Display -->
            <div class="flex-1 overflow-auto bg-[#0A2E2C] rounded-b-lg">
                <div class="sticky top-0 z-10 flex justify-between items-center p-3 bg-[#0A2E2C] border-b border-[#CAEED5]/20 text-white">
                    <div class="font-semibold text-[#CAEED5]">
                        <i class="fas fa-list-alt mr-2"></i> <?php echo ucfirst($activeLogType); ?> Activity Log
                    </div>
                    <div class="text-sm flex items-center">
                        <span class="bg-[#115D5B] px-2 py-1 rounded-lg">
                            <i class="fas fa-clipboard-check mr-1"></i>
                            <?php echo count($logContent); ?> <?php echo count($logContent) === 1 ? 'entry' : 'entries'; ?> found
                        </span>
                    </div>
                </div>
                
                <?php if ($logError): ?>
                    <div class="flex items-center p-4 bg-red-900/40 border-l-4 border-red-500 text-red-300 m-4 rounded-r">
                        <div class="mr-3 text-xl text-red-500">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div>
                            <div class="font-bold"><?php echo $logError['error']; ?></div>
                            <div class="text-sm"><?php echo $logError['message']; ?></div>
                        </div>
                    </div>
                <?php elseif (empty($logContent)): ?>
                    <div class="flex items-center p-4 bg-yellow-900/40 border-l-4 border-yellow-500 text-yellow-300 m-4 rounded-r">
                        <div class="mr-3 text-xl text-yellow-500">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <div class="font-bold">No Log Entries Found</div>
                            <div class="text-sm">No matching log entries were found with the current filters.</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-[#CAEED5]/10">
                        <?php foreach ($logContent as $index => $log): ?>
                            <?php
                                // Determine status icon and colors
                                $statusIcon = 'fas fa-info-circle';
                                $statusBgClass = 'bg-blue-900/30';
                                $statusBorderClass = 'border-blue-600';
                                $statusTextClass = 'text-blue-300';
                                
                                if ($log['status'] === 'success') {
                                    $statusIcon = 'fas fa-check-circle';
                                    $statusBgClass = 'bg-green-900/30';
                                    $statusBorderClass = 'border-green-600';
                                    $statusTextClass = 'text-green-300';
                                } elseif ($log['status'] === 'failed' || $log['status'] === 'error') {
                                    $statusIcon = 'fas fa-times-circle';
                                    $statusBgClass = 'bg-red-900/30';
                                    $statusBorderClass = 'border-red-600';
                                    $statusTextClass = 'text-red-300';
                                } elseif ($log['status'] === 'warning') {
                                    $statusIcon = 'fas fa-exclamation-triangle';
                                    $statusBgClass = 'bg-yellow-900/30';
                                    $statusBorderClass = 'border-yellow-600';
                                    $statusTextClass = 'text-yellow-300';
                                }
                                
                                // Highlight for latest entry
                                $isLatestClass = ($index === 0) ? 'border-l-4 '.$statusBorderClass : '';
                                
                                // Different background for alternating rows
                                $rowBgClass = ($index % 2 === 0) ? 'bg-[#0F3D3A]/50' : '';
                            ?>
                            <div class="flex items-center p-3 <?php echo $rowBgClass; ?> <?php echo $isLatestClass; ?> hover:bg-[#115D5B]/20 transition-colors duration-200">
                                <div class="mr-3 <?php echo $statusTextClass; ?>">
                                    <i class="<?php echo $statusIcon; ?> text-lg"></i>
                                </div>
                                <div class="flex-grow">
                                    <div class="flex flex-col md:flex-row justify-between">
                                        <div class="font-medium text-white">
                                            <?php echo htmlspecialchars($log['message']); ?>
                                            <?php if ($index === 0): ?>
                                                <span class="bg-[#CAEED5] text-[#115D5B] text-xs px-2 py-0.5 rounded-full ml-2">Latest</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-[#CAEED5]">
                                            <?php echo htmlspecialchars($log['user']); ?> • 
                                            <span class="<?php echo $statusTextClass; ?>">
                                                <?php echo ucfirst(htmlspecialchars($log['status'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1 flex items-center">
                                        <i class="far fa-clock mr-1"></i>
                                        <?php echo htmlspecialchars($log['timestamp']); ?>
                                        <span class="mx-2">•</span>
                                        <span class="rounded-full px-2 py-0.5 text-xs <?php echo $statusBgClass; ?> <?php echo $statusTextClass; ?>">
                                            <?php echo ucfirst(htmlspecialchars($log['type'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
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