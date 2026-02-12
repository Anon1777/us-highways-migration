<?php

$path = './us-highways-migration/pages';
$phpFiles = array();

if ($argc > 1) {
    if ($argv[1] === 'r') {
        echo "Rendering files.\n";
        scanDirectoryRecursive($path, $phpFiles);
        renderPhpToHtml($phpFiles);
        executeIncludes($phpFiles);
        echo "Program exited with code 0a - Successful render.\n";
    } elseif ($argv[1] === 'd') {
        echo "Deleting files.\n";
        deleteHtmlFiles($path);
        echo "Program exited with code 0b - Successful deletion.\n";
    } else {
        echo "Program exited with code 2 - Unknown argument: '$argv[1]'.\n";
    }
} else {
    echo "Program exited with code 1 - No arguments provided.\n";
}

function scanDirectoryRecursive($directoryPath, &$phpFiles)
{
    if (!is_dir($directoryPath)) {
        echo "Program exited with code 3a - '$directoryPath' is not a valid directory.\n";
        return;
    }
    $entries = scandir($directoryPath);
    $filteredEntries = array_diff($entries, array('.', '..'));
    foreach ($filteredEntries as $entry) {
        $fullPath = "$directoryPath/$entry";
        if (is_dir($fullPath)) {
            scanDirectoryRecursive($fullPath, $phpFiles);
        } elseif (is_file($fullPath) && pathinfo($fullPath, PATHINFO_EXTENSION) === 'php') {
            $phpFiles[] = $fullPath;
        }
    }
}

function renderPhpToHtml($phpFiles)
{
    foreach ($phpFiles as $phpFile) {
        $htmlFile = preg_replace('/\.php$/', '.html', $phpFile);
        if ($htmlFile !== $phpFile) {
            $content = file_get_contents($phpFile);
            $includePattern = '/<\?php\s+include\s+[\'\"]([^\'\"]+\.php)[\'\"];\s*\?>/i';
            $processedContent = preg_replace_callback($includePattern, function ($matches) use ($phpFile) {
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
                echo "Program exited with code 3b - Failed to write $htmlFile.\n";
                break;
            } else {
                echo "Rendered $phpFile to $htmlFile\n";
            }
        }
    }
}

function executeIncludes($phpFiles)
{
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
                    echo "Executing include from $phpFile (Line " . ($num + 1) . "): $includePath\n";
                    // include($fullIncludePath);
                } else {
                    echo "Program exited with code 3c - Include file not found: $includePath in $phpFile (Line " . ($num + 1) . ")\n";
                    break;
                }
            }
        }
    }
}

function deleteHtmlFiles($directoryPath)
{
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
                echo "Program exited with code 3d - Failed to delete $fullPath\n";
                break;
            }
        }
    }
}