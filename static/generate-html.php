<?php

$startTime = microtime(true);
$fromPath = './us-highways-migration/pages';
$toPath = './roads';
$phpFiles = array();

function printElapsedTime($startTime)
{
    $elapsedSeconds = microtime(true) - $startTime;
    echo "Elapsed time: " . number_format($elapsedSeconds, 3) . " seconds\n";
}

if ($argc > 1) {
    if ($argv[1] === 'r') {
        echo "Rendering files.\n";
        scanDirectoryRecursive($fromPath, $phpFiles);
        renderPhpToHtml($phpFiles, $fromPath, $toPath);
        echo "Program exited with code 0a - Successful render.\n";
        printElapsedTime($startTime);
    } elseif ($argv[1] === 'd') {
        echo "Deleting files.\n";
        deleteHtmlFiles($toPath);
        echo "Program exited with code 0b - Successful deletion.\n";
        printElapsedTime($startTime);
    } else {
        echo "Program exited with code 2 - Unknown argument: '$argv[1]'.\n";
        printElapsedTime($startTime);
    }
} else {
    echo "Program exited with code 1 - No arguments provided.\n";
    printElapsedTime($startTime);
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

function renderPhpToHtml($phpFiles, $fromPath, $toPath)
{
    $skippedCount = 0;
    foreach ($phpFiles as $phpFile) {
        $content = file_get_contents($phpFile);
        if ($content === false) {
            echo "Program exited with code 3b - Failed to read $phpFile.\n";
            break;
        }
        if (trim($content) === '') {
            $skippedCount++;
            continue;
        }
        $relativePath = ltrim(substr($phpFile, strlen($fromPath)), '/\\');
        $htmlFile = $toPath . DIRECTORY_SEPARATOR . preg_replace('/\.php$/', '.html', $relativePath);
        if ($htmlFile !== $phpFile) {
            $outputDirectory = dirname($htmlFile);
            if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0777, true)) {
                echo "Program exited with code 3b - Failed to create $outputDirectory.\n";
                break;
            }
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
    echo "Skipped $skippedCount empty PHP files.\n";
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
    $deletedCount = 0;
    $failedCount = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directoryPath, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'html') {
            if (unlink($file->getPathname())) {
                $deletedCount++;
            } else {
                $failedCount++;
                echo "Program exited with code 3d - Failed to delete " . $file->getPathname() . "\n";
            }
        }
    }

    echo "Deleted $deletedCount HTML files.\n";
    if ($failedCount > 0) {
        echo "Failed to delete $failedCount HTML files.\n";
    }
}