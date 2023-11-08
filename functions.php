<?php

function handleFileRequest()
{
    if (empty($_GET['file'])) {
        // Modify HTML content by adding 'file' and 'version' parameters to URLs
        ob_start('addAssetsVersionToUrls', 4096);
    } elseif (preg_match('#^(default|adminer|static(/\w[\w.-]*)+)\.(\w+)\z#', $_GET['file'], $m)) {
        // Serve static files with appropriate headers and content type
        serveStaticFile($_GET['file']);
    }
}
// Redirect to HTTPS if httpsRedirect is defined in environment variables
function redirectToHttps()
{
    if (getenv('ADMINER_HTTPS_REDIRECT') === 'true' && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Function to add 'file' and 'version' parameters to 'link' and 'script' tags
function addAssetsVersionToUrls($s)
{
    return preg_replace_callback(
        '#(<(link|script)\s[^>]*(href|src)=")(adminer\.css|static/.+)(\?v=\d+)?"#U',
        function ($m) {
            return $m[1] . '?file=' . urlencode($m[4]) . '&amp;version=' . ASSETS_VERSION . '"';
        },
        $s
    );
}

// Function to serve static files with appropriate caching headers and content type
function serveStaticFile($file)
{
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        header('HTTP/1.1 304 Not Modified');
        exit;
    }

    header('Expires: ' . gmdate('D, d M Y H:i:s', strtotime('1 month')) . ' GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    $types = ['css' => 'text/css', 'js' => 'text/javascript', 'gif' => 'image/gif', 'png' => 'image/png'];
    $file_extension = pathinfo($file, PATHINFO_EXTENSION);

    if (isset($types[$file_extension])) {
        header('Content-Type: ' . $types[$file_extension]);
    }

    readfile(__DIR__ . '/' . $file);
    exit;
}
