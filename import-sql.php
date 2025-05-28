<style>
    .form-row {
        margin: 10px;
    }

    .result-line {
        margin: 8px;
    }
</style>

<?php

// show all error
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// set max memory size
ini_set('memory_limit', '2048M');
// set max post size
ini_set('post_max_size', '1024M');
// set max execution time
ini_set('max_execution_time', 0);
// set max input time
ini_set('max_input_time', 0);
// set max allowed file size
ini_set('upload_max_filesize', '1024M');

function printLine($text)  {
    echo "<label class='result-line'>" . $text . "</label><br/>";
    // do scroll to end page
    echo "<script>window.scrollTo(0, document.body.scrollHeight);</script>";
}

function printOperationDB($sql_query) {
    // if create table, show message
    if (substr(trim($sql_query), 0, 12) == "CREATE TABLE") {
        // Get table name
        preg_match("/CREATE TABLE `(.*?)`/", $sql_query, $table_name);
        printLine("[+] Has been created the table: ". $table_name[1]);
    }
    // if insert into table, show message
    if (substr(trim($sql_query), 0, 11) == "INSERT INTO") {
        // Get table name
        preg_match("/INSERT INTO `(.*?)`/", $sql_query, $table_name);
        printLine("[+] Has been inserted data into the table: ". $table_name[1]);
    }
    // if alter table, show message
    if (substr(trim($sql_query), 0, 11) == "ALTER TABLE") {
        // Get table name
        preg_match("/ALTER TABLE `(.*?)`/", $sql_query, $table_name);
        printLine("[+] Has been altered the table: ". $table_name[1]);
    }

    // if update table
    if (substr(trim($sql_query), 0, 11) == "UPDATE TABLE") {
        // Get table name
        preg_match("/UPDATE TABLE `(.*?)`/", $sql_query, $table_name);
        printLine("[+] Has been updated the table: ". $table_name[1]);
    }

    // if delete table, show message
    if (substr(trim($sql_query), 0, 11) == "DELETE TABLE") {
        // Get table name
        preg_match("/DELETE TABLE `(.*?)`/", $sql_query, $table_name);
        printLine("[+] Has been deleted the table: ". $table_name[1]);
    }

    // if drop table, show message
    if (substr(trim($sql_query), 0, 11) == "DROP TABLE") {
        // Get table name
        preg_match("/DROP TABLE `(.*?)`/", $sql_query, $table_name);
        printLine("[+] Has been dropped the table: ". $table_name[1]);
    }

    // if truncate table, show message
    if (substr(trim($sql_query), 0, 11) == "TRUNCATE TABLE") {
        // Get table name
        preg_match("/TRUNCATE TABLE `(.*?)`/", $sql_query, $table_name);
        printLine("[+] Has been truncated the table: ". $table_name[1]);
    }

    // if rename table, show message
    if (substr(trim($sql_query), 0, 11) == "RENAME TABLE") {
        // Get table name
        preg_match("/RENAME TABLE `(.*?)`/", $sql_query, $table_name);
        printLine("[+] Has been renamed the table: ". $table_name[1]);
    }

    // if create a index
    if (substr(trim($sql_query), 0, 11) == "CREATE INDEX") {
        // Get table name
        preg_match("/CREATE INDEX `(.*?)`/", $sql_query, $table_name);
        printLine("[+] Has been created the index: ". $table_name[1]);
    }

    // if drop a index
    if (substr(trim($sql_query), 0, 11) == "DROP INDEX") {
        // Get table name
        preg_match("/DROP INDEX `(.*?)`/", $sql_query, $table_name);
        printLine("[+] Has been dropped the index: ". $table_name[1]);
    }
}

function restoreMysqlDB($filePath, $conn)
{
    $sql = '';
    $error = '';
    
    // Reading file
    printLine("[+] Reading file: " . $filePath);
    
    if (file_exists($filePath)) {
        $lines = file($filePath);
        
        foreach ($lines as $line) {
            
            // Ignoring comments from the SQL script
            if (substr($line, 0, 2) == '--' || $line == '') {
                continue;
            }
            
            $sql .= $line;
            
            if (substr(trim($line), - 1, 1) == ';') {
                $result = mysqli_query($conn, $sql);
                if (! $result) {
                    $error .= mysqli_error($conn) . "n";
                    printLine($error);
                    exit();
                }
                printOperationDB($sql);                
                $sql = '';
            }
        } // end foreach

        printLine("[+] Successfully restored");
        printLine("<a onclick='history.back()'><button>Volver</button></a>");
        exit();
    } else {
        printLine("[-] File not found");
        printLine("<a onclick='history.back()'><button>Volver</button></a>");
        exit();
    }
}

function validateConn($host, $db_name, $db_username, $db_password) {
    printLine("[+] Try conect to data base");
    $conn = mysqli_connect($host, $db_username, $db_password, $db_name);
    if (mysqli_connect_errno()) {
        printLine("[-] Failed to connect to MySQL: " . mysqli_connect_error());
        printLine("<a onclick='history.back()'><button>Volver</button></a>");
        exit();
    }
    printLine("[+] Conected to data base");
    return $conn;
}

?>

<?php

if (isset($_POST) && isset($_POST['restore']) && $_POST['restore']) {
    printLine("[+] Processing...");
    $host = $_POST['host'];
    $db_name = $_POST['db_name'];
    $db_username = $_POST['db_username'];
    $db_password = $_POST['db_password'];
    $backup_file = $_FILES['backup_file']['name'];
    $backup_file_type = $_FILES['backup_file']['type'];
    $backup_file_size = $_FILES['backup_file']['size'];
    $backup_file_tmp = $_FILES['backup_file']['tmp_name'];
    $path_file = $_POST['path_file'];
    $file_uploaded = true;
    if ($backup_file == "") {
        if ($path_file == "") {
            printLine("[-] No file selected");
            printLine("<a onclick='history.back()'><button>Volver</button></a>");
            exit();
        } else {
            $backup_file = $path_file . '.sql';
            $file_uploaded = false;
        }        
    } else {
        if ($backup_file_type != "application/sql") {
            printLine("[-] Invalid file type");
            printLine("<a onclick='history.back()'><button>Volver</button></a>");
            exit();
        }
        if (! in_array(strtolower(pathinfo($_FILES["backup_file"]["name"], PATHINFO_EXTENSION)), array(
            "sql"
        ))) {
            printLine("[-] Invalid file type");
            printLine("<a onclick='history.back()'><button>Volver</button></a>");
            exit();
        }
    }

    $error = false;
    if ($file_uploaded && is_uploaded_file($_FILES["backup_file"]["tmp_name"])) {
        move_uploaded_file($_FILES["backup_file"]["tmp_name"], $_FILES["backup_file"]["name"]);
    } else {
        
    }
    

    if (!$error) {
        $conn = validateConn($host, $db_name, $db_username, $db_password);
        restoreMysqlDB($backup_file, $conn);
    } else {
        printLine("[-] Error uploading file");
        printLine("<a onclick='history.back()'><button>Volver</button></a>");
        exit();
    }
} else {
?>



    <form method="post" action="" enctype="multipart/form-data" id="frm-restore">
        <div class="form-row">
            <div>HOST:</div>
            <div>
                <input type="text" name="host" required="required" placeholder="localhost" value="localhost" />
            </div>
        </div>
        <div class="form-row">
            <div>DB Name:</div>
            <div>
                <input type="text" name="db_name" required="required" placeholder="Insert your data base name" />
            </div>
        </div>
        <div class="form-row">
            <div>DB Username: </div>
            <div>
                <input type="text" name="db_username" required="required" placeholder="Insert your date base username"  />
            </div>
        </div>
        <div class="form-row">
            <div>DB Password: </div>
            <div>
                <input type="password" name="db_password" required="required" placeholder="Insert your password" />
            </div>
        </div>
        <div class="form-row">
            <div>Choose Backup File</div>
            <div>
                <input type="file" name="backup_file" class="input-file" />
            </div>
        </div>
        <div class="form-row">
            <div>Filename</div>
            <div>
                <input type="text" name="path_file" class="input-file"/>
            </div>
        </div>
        <div class="form-row">
            <input type="submit" name="restore" value="Restore" class="btn-action" style="margin-top: 10px;" />
        </div>
    </form>

<?php
}

?>