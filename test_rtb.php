<?php
// Menonaktifkan error reporting agar tidak mengganggu output XML
error_reporting(0);

// Sertakan file koneksi database dan fungsi
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

echo "<h1>RTB Endpoint Test Tool (Advanced)</h1>";

// --- KONFIGURASI PENGUJIAN ---
// Ambil Zone ID dari URL, atau gunakan Default Zone ID dari pengaturan
$zone_id_to_test = $_GET['zone_id'] ?? get_setting('default_ad_zone_id', $pdo);
$rtb_endpoint_url = get_setting('rtb_endpoint_url', $pdo);

if (empty($rtb_endpoint_url)) {
    die("<p style='color:red;'><b>Error:</b> 'Endpoint URL Ad Server RTB' belum diatur di Admin Panel.</p>");
}
if (empty($zone_id_to_test)) {
    die("<p style='color:red;'><b>Error:</b> Tidak ada Zone ID untuk diuji. Atur 'Default Ad Zone ID' atau berikan via URL.</p>");
}

// --- MEMBANGUN PAYLOAD JSON YANG KOMPLEKS ---
$payload = [
    "id" => uniqid() . "-" . $zone_id_to_test,
    "imp" => [
        [
            "id" => "1",
            "video" => [ "mimes" => ["video/mp4", "video/webm"], "minduration" => 2, "maxduration" => 60, "protocols" => [2, 3, 5, 6], "w" => 640, "h" => 480, "startdelay" => 0, "playbackmethod" => [1, 2, 3] ],
            "tagid" => (string)$zone_id_to_test
        ]
    ],
    "site" => [ "domain" => $_SERVER['SERVER_NAME'], "page" => 'https://' . $_SERVER['SERVER_NAME'] ],
    "device" => [ "ua" => $_SERVER['HTTP_USER_AGENT'] ?? 'Server Test', "ip" => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', "language" => "en" ],
    "user" => [ "id" => session_id() ],
    "at" => 1,
    "tmax" => 500,
    "cur" => ["USD"]
];

echo "<p><b>Menguji koneksi ke:</b><br>" . htmlspecialchars($rtb_endpoint_url) . "</p>";
echo "<p><b>Dengan metode:</b> POST</p>";
echo "<h3>Request Payload (Data yang Dikirim):</h3>";
echo "<textarea style='width: 100%; height: 150px; font-family: monospace;'>" . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) . "</textarea>";
echo "<hr>";

// --- PROSES PENGUJIAN MENGGUNAKAN cURL DENGAN PAYLOAD LENGKAP ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $rtb_endpoint_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Timeout 2 detik
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

$ad_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// --- TAMPILKAN HASIL ---
echo "<h2>Hasil Pengujian:</h2>";
echo "<p><b>HTTP Status Code:</b> " . $http_code . "</p>";

if ($curl_error) {
    echo "<p style='color:red;'><b>cURL Error:</b> " . $curl_error . "</p>";
}

echo "<h3>Respons Mentah dari Ad Server:</h3>";
echo "<textarea style='width: 100%; height: 300px; font-family: monospace;'>" . htmlspecialchars($ad_response) . "</textarea>";

?>