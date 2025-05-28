<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Database Importer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#1E40AF',
                        success: '#10B981',
                        error: '#EF4444',
                        warning: '#F59E0B'
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">

<?php
// Configuration and error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
ini_set('memory_limit', '2048M');
ini_set('post_max_size', '1024M');
ini_set('max_execution_time', 0);
ini_set('max_input_time', 0);
ini_set('upload_max_filesize', '1024M');

function printLine($text, $type = 'info') {
    $iconClass = '';
    $bgClass = '';
    $textClass = '';
    
    switch($type) {
        case 'success':
            $iconClass = 'fas fa-check-circle';
            $bgClass = 'bg-green-50 border-green-200';
            $textClass = 'text-green-800';
            break;
        case 'error':
            $iconClass = 'fas fa-exclamation-circle';
            $bgClass = 'bg-red-50 border-red-200';
            $textClass = 'text-red-800';
            break;
        case 'warning':
            $iconClass = 'fas fa-exclamation-triangle';
            $bgClass = 'bg-yellow-50 border-yellow-200';
            $textClass = 'text-yellow-800';
            break;
        default:
            $iconClass = 'fas fa-info-circle';
            $bgClass = 'bg-blue-50 border-blue-200';
            $textClass = 'text-blue-800';
    }
    
    echo "<div class='p-3 rounded-lg border {$bgClass} {$textClass} flex items-center space-x-3 animate-fade-in'>";
    echo "<i class='{$iconClass} flex-shrink-0'></i>";
    echo "<span class='text-sm'>{$text}</span>";
    echo "</div>";
    
    // Auto scroll to bottom of messages container and show progress
    echo "<script>
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
        const progressContainer = document.getElementById('progress-container');
        if (progressContainer) {
            progressContainer.style.display = 'block';
        }
    </script>";
    ob_flush();
    flush();
}

function printOperationDB($sql_query) {
    $operations = [
        'CREATE TABLE' => ['message' => 'Table created', 'type' => 'success'],
        'INSERT INTO' => ['message' => 'Data inserted into table', 'type' => 'info'],
        'ALTER TABLE' => ['message' => 'Table altered', 'type' => 'warning'],
        'UPDATE TABLE' => ['message' => 'Table updated', 'type' => 'warning'],
        'DELETE TABLE' => ['message' => 'Data deleted from table', 'type' => 'warning'],
        'DROP TABLE' => ['message' => 'Table dropped', 'type' => 'error'],
        'TRUNCATE TABLE' => ['message' => 'Table truncated', 'type' => 'warning'],
        'RENAME TABLE' => ['message' => 'Table renamed', 'type' => 'warning'],
        'CREATE INDEX' => ['message' => 'Index created', 'type' => 'success'],
        'DROP INDEX' => ['message' => 'Index dropped', 'type' => 'warning']
    ];
    
    foreach ($operations as $operation => $config) {
        if (substr(trim($sql_query), 0, strlen($operation)) == $operation) {
            // Extract table/index name
            preg_match("/".str_replace(' ', '\s+', $operation)."\s+`?([^`\s]+)`?/i", $sql_query, $matches);
            $name = isset($matches[1]) ? $matches[1] : 'unknown';
            
            printLine("[+] {$config['message']}: {$name}", $config['type']);
            break;
        }
    }
}

function restoreMysqlDB($filePath, $conn) {
    $sql = '';
    $error = '';
    $lineNumber = 0;
    $totalLines = 0;
    
    printLine("Reading file: " . $filePath, 'info');
    
    if (!file_exists($filePath)) {
        printLine("File not found: " . $filePath, 'error');
        return false;
    }
    
    // Count total lines for progress
    $totalLines = count(file($filePath));
    printLine("Total lines to process: " . number_format($totalLines), 'info');
    
    // Open file handle for memory efficiency
    $handle = fopen($filePath, 'r');
    if (!$handle) {
        printLine("Cannot open file", 'error');
        return false;
    }
    
    while (($line = fgets($handle)) !== false) {
        $lineNumber++;
        
        // Show progress every 1000 lines
        if ($lineNumber % 1000 == 0) {
            $progress = round(($lineNumber / $totalLines) * 100, 2);
            echo "<script>
                document.getElementById('progress-bar').style.width = '{$progress}%';
                document.getElementById('progress-text').textContent = 'Processing line {$lineNumber} of {$totalLines} ({$progress}%)';
            </script>";
            ob_flush();
            flush();
        }
        
        // Skip comments and empty lines
        if (substr(trim($line), 0, 2) == '--' || trim($line) == '') {
            continue;
        }
        
        $sql .= $line;
        
        // Execute when we find a complete statement
        if (substr(trim($line), -1, 1) == ';') {
            $result = mysqli_query($conn, $sql);
            if (!$result) {
                $error = mysqli_error($conn);
                printLine("SQL Error: " . $error, 'error');
                fclose($handle);
                return false;
            }
            printOperationDB($sql);
            $sql = '';
            
            // Memory cleanup
            if ($lineNumber % 100 == 0) {
                gc_collect_cycles();
            }
        }
    }
    
    fclose($handle);
    printLine("Database restoration completed successfully!", 'success');
    return true;
}

function validateConn($host, $db_name, $db_username, $db_password) {
    printLine("Attempting to connect to database...", 'info');
    
    try {
        $conn = mysqli_connect($host, $db_username, $db_password, $db_name);
        if (mysqli_connect_errno()) {
            printLine("Failed to connect to MySQL: " . mysqli_connect_error(), 'error');
            return false;
        }
        printLine("Successfully connected to database: " . $db_name, 'success');
        return $conn;
    } catch (Exception $e) {
        printLine("Connection error: " . $e->getMessage(), 'error');
        return false;
    }
}

// Process form submission
if (isset($_POST['restore']) && $_POST['restore']) {
    echo "<div class='container mx-auto px-4 py-8'>";
    echo "<div class='max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden'>";
    
    // Header section - fixed
    echo "<div class='bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6'>";
    echo "<h2 class='text-2xl font-bold flex items-center'>";
    echo "<i class='fas fa-cogs mr-3'></i>Processing Database Import";
    echo "</h2>";
    echo "</div>";
    
    // Progress container - fixed at bottom
    echo "<div id='progress-container' class='bg-gray-50 border-t border-gray-200 p-4 hidden'>";
    echo "<div class='bg-gray-200 rounded-full h-3 mb-2'>";
    echo "<div id='progress-bar' class='bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-300 shadow-sm' style='width: 0%'></div>";
    echo "</div>";
    echo "<p id='progress-text' class='text-sm text-gray-600 text-center font-medium'>Initializing...</p>";
    echo "</div>";
    
    // Messages container - scrollable with fixed height
    echo "<div class='flex flex-col h-[500px]'>";
    echo "<div id='messages-container' class='flex-1 overflow-y-auto p-6 space-y-3' style='max-height: 500px; min-height: 300px;'>";
    
    $host = $_POST['host'];
    $db_name = $_POST['db_name'];
    $db_username = $_POST['db_username'];
    $db_password = $_POST['db_password'];
    $path_file = $_POST['path_file'];
    
    $backup_file = '';
    $file_uploaded = false;
    
    // Handle file upload or path
    if (!empty($_FILES['backup_file']['name'])) {
        $backup_file = $_FILES['backup_file']['name'];
        $backup_file_type = $_FILES['backup_file']['type'];
        $backup_file_tmp = $_FILES['backup_file']['tmp_name'];
        $file_uploaded = true;
        
        // Validate file type
        $allowed_extensions = ['sql'];
        $file_extension = strtolower(pathinfo($backup_file, PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            printLine("Invalid file type. Only .sql files are allowed.", 'error');
            echo "</div>";
            echo "</div>";
            echo "<div class='bg-gray-50 border-t border-gray-200 p-4'>";
            echo "<button onclick='history.back()' class='bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center justify-center mx-auto'>";
            echo "<i class='fas fa-arrow-left mr-2'></i>Go Back";
            echo "</button>";
            echo "</div>";
            echo "</div></div>";
            exit();
        }
        
        // Move uploaded file
        if (is_uploaded_file($backup_file_tmp)) {
            if (!move_uploaded_file($backup_file_tmp, $backup_file)) {
                printLine("Error uploading file", 'error');
                echo "</div>";
                echo "</div>";
                echo "<div class='bg-gray-50 border-t border-gray-200 p-4'>";
                echo "<button onclick='history.back()' class='bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center justify-center mx-auto'>";
                echo "<i class='fas fa-arrow-left mr-2'></i>Go Back";
                echo "</button>";
                echo "</div>";
                echo "</div></div>";
                exit();
            }
        }
    } elseif (!empty($path_file)) {
        $backup_file = $path_file . '.sql';
    } else {
        printLine("No file selected. Please choose a file or specify a filename.", 'error');
        echo "</div>";
        echo "</div>";
        echo "<div class='bg-gray-50 border-t border-gray-200 p-4'>";
        echo "<button onclick='history.back()' class='bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center justify-center mx-auto'>";
        echo "<i class='fas fa-arrow-left mr-2'></i>Go Back";
        echo "</button>";
        echo "</div>";
        echo "</div></div>";
        exit();
    }
    
    // Validate connection and restore database
    $conn = validateConn($host, $db_name, $db_username, $db_password);
    if ($conn) {
        $success = restoreMysqlDB($backup_file, $conn);
        mysqli_close($conn);
        
        if ($success) {
            echo "<div class='p-4 bg-green-50 border border-green-200 rounded-lg'>";
            echo "<div class='flex items-center'>";
            echo "<i class='fas fa-check-circle text-green-600 text-xl mr-3'></i>";
            echo "<div>";
            echo "<h3 class='font-semibold text-green-800'>Import Completed Successfully!</h3>";
            echo "<p class='text-green-600 text-sm'>Your database has been restored successfully.</p>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
        
        // Clean up uploaded file
        if ($file_uploaded && file_exists($backup_file)) {
            unlink($backup_file);
        }
    }
    
    // Close messages container
    echo "</div>";
    echo "</div>";
    
    // Action buttons section - fixed at bottom
    echo "<div class='bg-gray-50 border-t border-gray-200 p-4'>";
    echo "<div class='flex flex-col sm:flex-row gap-3 justify-center'>";
    echo "<button onclick='history.back()' class='bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center justify-center'>";
    echo "<i class='fas fa-arrow-left mr-2'></i>Import Another File";
    echo "</button>";
    echo "<button onclick='location.reload()' class='bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center justify-center'>";
    echo "<i class='fas fa-redo mr-2'></i>Refresh Page";
    echo "</button>";
    echo "</div>";
    echo "</div>";
    
    echo "</div>";
    echo "</div>";
    
} else {
    // Display the form
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full mb-4">
                <i class="fas fa-database text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">SQL Database Importer</h1>
            <p class="text-gray-600">Import your SQL backup files to MySQL database with ease</p>
        </div>

        <!-- Main Form Card -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form method="post" action="" enctype="multipart/form-data" id="frm-restore" class="space-y-6">
                
                <!-- Database Connection Section -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-server mr-2 text-blue-600"></i>
                        Database Connection
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Host</label>
                            <input type="text" name="host" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="localhost" value="localhost">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Database Name</label>
                            <input type="text" name="db_name" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="Enter database name">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <input type="text" name="db_username" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="Database username">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" name="db_password" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="Database password">
                        </div>
                    </div>
                </div>

                <!-- File Upload Section -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-file-upload mr-2 text-blue-600"></i>
                        SQL File Selection
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- File Upload Option -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload SQL File</label>
                            <div class="relative">
                                <input type="file" name="backup_file" accept=".sql" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Maximum file size: 1GB</p>
                        </div>
                        
                        <!-- OR Divider -->
                        <div class="flex items-center">
                            <div class="flex-1 border-t border-gray-300"></div>
                            <span class="px-4 text-sm text-gray-500 bg-white">OR</span>
                            <div class="flex-1 border-t border-gray-300"></div>
                        </div>
                        
                        <!-- File Path Option -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Server File Path</label>
                            <input type="text" name="path_file" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="Enter filename (without .sql extension)">
                            <p class="text-sm text-gray-500 mt-1">Use this if the SQL file is already on the server</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6">
                    <button type="submit" name="restore" value="1" 
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-4 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-play mr-2"></i>
                        Start Database Import
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
            <div class="bg-white rounded-lg p-4 shadow-md">
                <div class="flex items-center mb-2">
                    <i class="fas fa-shield-alt text-green-600 mr-2"></i>
                    <h4 class="font-semibold text-gray-800">Secure</h4>
                </div>
                <p class="text-sm text-gray-600">Your database credentials are processed securely</p>
            </div>
            
            <div class="bg-white rounded-lg p-4 shadow-md">
                <div class="flex items-center mb-2">
                    <i class="fas fa-tachometer-alt text-blue-600 mr-2"></i>
                    <h4 class="font-semibold text-gray-800">Fast</h4>
                </div>
                <p class="text-sm text-gray-600">Optimized for large SQL files with progress tracking</p>
            </div>
            
            <div class="bg-white rounded-lg p-4 shadow-md">
                <div class="flex items-center mb-2">
                    <i class="fas fa-check-circle text-purple-600 mr-2"></i>
                    <h4 class="font-semibold text-gray-800">Reliable</h4>
                </div>
                <p class="text-sm text-gray-600">Comprehensive error handling and validation</p>
            </div>
        </div>
    </div>
</div>

<style>
    .animate-fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Custom scrollbar for messages container */
    #messages-container::-webkit-scrollbar {
        width: 8px;
    }
    
    #messages-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    
    #messages-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    
    #messages-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* General scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Ensure the messages container has proper styling */
    #messages-container {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f1f5f9;
    }
    
    /* Progress bar animation */
    #progress-bar {
        transition: width 0.3s ease-in-out;
    }
</style>

<?php
}
?>

</body>
</html> 