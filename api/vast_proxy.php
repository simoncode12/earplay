<?php
// api/vast_proxy.php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Ambil Zone ID yang diminta oleh pemutar video
$zone_id = filter_input(INPUT_GET, 'zone_id', FILTER_SANITIZE_NUMBER_INT);
if (empty($zone_id)) {
    http_response_code(400);
    exit('Zone ID is missing.');
}

// Ambil URL Endpoint RTB dari pengaturan
$rtb_endpoint_url = get_setting('rtb_endpoint_url', $pdo);
if (empty($rtb_endpoint_url)) {
    http_response_code(500);
    exit('RTB Endpoint URL is not configured in admin settings.');
}

// --- MEMBANGUN PAYLOAD JSON YANG KOMPLEKS ---
$payload = [
    "id" => uniqid(), // ID unik untuk setiap permintaan
    "imp" => [
        [
            "id" => "1",
            "video" => [
                "mimes" => ["video/mp4", "video/webm"],
                "minduration" => 5,
                "maxduration" => 60,
                "protocols" => [2, 3, 5, 6],
                "w" => 640,
                "h" => 480,
                "startdelay" => 0,
                "playbackmethod" => [1, 2]
            ],
            "tagid" => (string)$zone_id // Zone ID yang kita terima
        ]
    ],
    "site" => [
        "domain" => $_SERVER['SERVER_NAME'],
        "page" => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'https://' . $_SERVER['SERVER_NAME'],
        "ref" => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'https://' . $_SERVER['SERVER_NAME']
    ],
    "device" => [
        "ua" => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        "ip" => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        "language" => substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2)
    ],
    "user" => [
        "id" => session_id() // Gunakan session ID sebagai ID pengguna anonim
    ],
    "tmax" => 500
];

// --- MENGHUBUNGI AD SERVER DENGAN METODE POST DAN PAYLOAD LENGKAP ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $rtb_endpoint_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 1); // Timeout agresif 1 detik
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

$ad_response_json = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// --- PROSES RESPON DAN SUNTIKKAN DATA HARGA ---
header('Content-Type: application/xml; charset=utf-8');

if ($http_code == 200 && !empty($ad_response_json)) {
    $ad_data = json_decode($ad_response_json, true);

    // Ambil VAST XML dan harga dari respons JSON
    $vast_xml = $ad_data['seatbid'][0]['bid'][0]['adm'] ?? null;
    $revenue = (float)($ad_data['seatbid'][0]['bid'][0]['price'] ?? 0);

    if ($vast_xml && $revenue > 0) {
        // --- Menyuntikkan blok <Extension> berisi harga ke dalam VAST XML ---
        $dom = new DOMDocument();
        // Gunakan @ untuk menekan warning jika ad server mengirim XML yang tidak sempurna
        @$dom->loadXML($vast_xml); 
        
        // Cari elemen InLine atau Wrapper untuk disisipi
        $inlineOrWrapper = $dom->getElementsByTagName('InLine')->item(0) ?? $dom->getElementsByTagName('Wrapper')->item(0);
        
        if ($inlineOrWrapper) {
            $extensions = $dom->createElement('Extensions');
            $extension = $dom->createElement('Extension');
            $extension->setAttribute('type', 'TubeX-Revenue'); // Tipe kustom kita
            $revenueNode = $dom->createElement('Revenue', $revenue); // Node berisi harga
            
            $extension->appendChild($revenueNode);
            $extensions->appendChild($extension);
            $inlineOrWrapper->appendChild($extensions);
            
            // Sajikan VAST XML yang sudah dimodifikasi ke pemutar video
            echo $dom->saveXML();
            exit();
        }
    }
}

// Jika gagal, tidak ada bid, atau format tidak sesuai, kirim VAST kosong
echo '<VAST version="3.0"></VAST>';