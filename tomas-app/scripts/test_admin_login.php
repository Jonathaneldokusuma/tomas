<?php
$base = 'https://tomas-production.up.railway.app';
$loginPath = '/admin/login';
$dashboardPath = '/admin/dashboard';
$loginUrl = $base . $loginPath;
$dashboardUrl = $base . $dashboardPath;
$username = 'admin';
$password = 'admin123';

$cookie = sys_get_temp_dir() . '/railway_test_cookies_' . bin2hex(random_bytes(6)) . '.txt';

function http_get($url, $cookieFile) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$res, $info];
}

function http_post($url, $postFields, $cookieFile) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$res, $info];
}

echo "GET $dashboardUrl\n";
list($html, $info) = http_get($dashboardUrl, $cookie);
printf("GET -> %d %s\n", $info['http_code'], $info['url'] ?? '') ;

// find CSRF token in HTML
$token = null;
if (preg_match('/name="_token"\s+value="([^"]+)"/i', $html, $m)) {
    $token = $m[1];
    echo "Found _token in form\n";
} else {
    // try find XSRF-TOKEN cookie
    $cookies = file_exists($cookie) ? file_get_contents($cookie) : '';
    if (preg_match('/XSRF-TOKEN\s+([a-zA-Z0-9-_%=]+)/', $cookies, $c)) {
        $token = urldecode($c[1]);
        echo "Found XSRF-TOKEN cookie\n";
    }
}

if (! $token) {
    echo "No CSRF token found; aborting login test.\n";
    exit(2);
}

// POST login
$post = ['_token' => $token, 'username' => $username, 'password' => $password];
echo "POST $loginUrl (username={$username})\n";
list($postHtml, $postInfo) = http_post($loginUrl, $post, $cookie);
printf("POST -> %d %s\n", $postInfo['http_code'], $postInfo['url'] ?? '');

// Check whether we're at dashboard
if (strpos($postInfo['url'], $dashboardPath) !== false || strpos($postHtml, 'Dashboard') !== false) {
    echo "Login appears successful; reached dashboard.\n";
    if (strpos($postHtml, 'Database belum siap') !== false) {
        echo "Note: Dashboard shows migration warning banner.\n";
    }
    exit(0);
}

// otherwise output a snippet of response to help debug
echo "Login may have failed. Response snippet:\n";
echo substr($postHtml, 0, 1000) . "\n";
exit(3);
