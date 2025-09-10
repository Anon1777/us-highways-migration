<?php

$path = './pages';
$phpFiles = array();

function scanDirectoryRecursive($directoryPath, &$phpFiles) {
    if (!is_dir($directoryPath)) {
        echo "Error: '$directoryPath' is not a valid directory.\n";
        return;
    }
    $entries = scandir($directoryPath);
    $filteredEntries = array_diff($entries, array('.', '..'));
    foreach ($filteredEntries as $entry) {
        $fullPath = $directoryPath . '/' . $entry;
        if (is_dir($fullPath)) {
            scanDirectoryRecursive($fullPath, $phpFiles);
        } elseif (is_file($fullPath) && pathinfo($fullPath, PATHINFO_EXTENSION) === 'php') {
            $phpFiles[] = $fullPath;
        }
    }
}

scanDirectoryRecursive($path, $phpFiles);

foreach ($phpFiles as $phpFile) {
    $htmlFile = preg_replace('/\.php$/', '.html', $phpFile);
    if ($htmlFile !== $phpFile) {
        $content = file_get_contents($phpFile);
        $includePattern = '/<\?php\s+include\s+[\'\"]([^\'\"]+\.php)[\'\"];\s*\?>/i';
        $processedContent = preg_replace_callback($includePattern, function($matches) use ($phpFile) {
            $includePath = $matches[1];
            $baseDir = dirname($phpFile);
            $fullIncludePath = realpath($baseDir . DIRECTORY_SEPARATOR . $includePath);
            if ($fullIncludePath && file_exists($fullIncludePath)) {
                ob_start();
                include($fullIncludePath);
                $output = ob_get_clean();
                return $output;
            } else {
                return "<!-- Include file not found: $includePath -->";
            }
        }, $content);
        // Replace all <a href="...php"> with <a href="...html">
        $finalContent = preg_replace('/(<a\s+[^>]*href=["\"][^"\']+)\.php(["\'])/i', '$1.html$2', $processedContent);
        if (file_put_contents($htmlFile, $finalContent) === false) {
            echo "Failed to write $htmlFile\n";
        } else {
            echo "Rendered $phpFile to $htmlFile\n";
        }
    }
}


function deleteHtmlFiles($directoryPath) {
    if (!is_dir($directoryPath)) {
        echo "Error: '$directoryPath' is not a valid directory.\n";
        return;
    }
    $entries = scandir($directoryPath);
    $filteredEntries = array_diff($entries, array('.', '..'));
    foreach ($filteredEntries as $entry) {
        $fullPath = $directoryPath . '/' . $entry;
        if (is_dir($fullPath)) {
            deleteHtmlFiles($fullPath);
        } elseif (is_file($fullPath) && pathinfo($fullPath, PATHINFO_EXTENSION) === 'html') {
            if (unlink($fullPath)) {
                echo "Deleted $fullPath\n";
            } else {
                echo "Failed to delete $fullPath\n";
            }
        }
    }
}

// deleteHtmlFiles($path);

// Scan all PHP files for lines containing only PHP include statements
    // Execute the include statement for matching lines
    function executeIncludes($phpFiles) {
        $includePattern = '/<\?php\s+include\s+[\'\"]([^\'\"]+\.php)[\'\"];\s*\?>/i';
        foreach ($phpFiles as $phpFile) {
            $lines = file($phpFile);
            foreach ($lines as $num => $line) {
                if (preg_match($includePattern, $line, $matches)) {
                    $includePath = $matches[1];
                    // Resolve relative path based on the PHP file's location
                    $baseDir = dirname($phpFile);
                    $fullIncludePath = realpath($baseDir . DIRECTORY_SEPARATOR . $includePath);
                    if ($fullIncludePath && file_exists($fullIncludePath)) {
                        echo "Executing include from $phpFile (Line " . ($num+1) . "): $includePath\n";
                        include($fullIncludePath);
                    } else {
                        echo "Include file not found: $includePath in $phpFile (Line " . ($num+1) . ")\n";
                    }
                }
            }
        }
    }

    // executeIncludes($phpFiles);