<?php
require 'common.php';

// Создание контейнера
$url = "https://dsig.eesti.ee/siga/hashcodecontainers";
$response = sendRequest($url);
if (!isset($response['containerId'])) {
    die("Error: containerId not found in response.");
}
$containerId = $response['containerId'];

// Добавление файла в контейнер
$url = "https://dsig.eesti.ee/siga/hashcodecontainers/$containerId/datafiles";
$fileContent = file_get_contents('document.pdf');
$fileName = 'document.pdf';

$data = [
    'datafileName' => $fileName,
    'datafileContent' => base64_encode($fileContent),
    'datafileMimeType' => 'application/pdf'
];

$response = sendRequest($url, $data);

// Инициация подписания через ИД-карту
$url = "https://dsig.eesti.ee/siga/hashcodecontainers/$containerId/idcardsigning";
$data = [
    'type' => 'idcard',
    'personalCode' => '12345678901',
    'country' => 'EE'
];

$response = sendRequest($url, $data);
if (!isset($response['signatureId'])) {
    die("Error: signatureId not found in response.");
}
$signatureId = $response['signatureId'];

// Проверка статуса подписи
$url = "https://dsig.eesti.ee/siga/hashcodecontainers/$containerId/idcardsigning/$signatureId/status";
$response = sendRequest($url, [], 'GET');
$status = $response['status'] ?? null;
if (!$status) {
    die("Error: Status not found in response.");
}

echo "Signing status: $status";

if ($status === 'COMPLETED') {
    echo "Signature process completed successfully.";
} elseif ($status === 'PENDING') {
    echo "Signature process is still pending. Retry later.";
} else {
    echo "Signature process failed.";
}

// Скачивание подписанного контейнера
$url = "https://dsig.eesti.ee/siga/hashcodecontainers/$containerId";
$response = sendRequest($url, [], 'GET');
if (!isset($response['containerContent'])) {
    die("Error: containerContent not found in response.");
}
file_put_contents('signed_container.asice', base64_decode($response['containerContent']));
echo "Signed container saved as signed_container.asice.";
?>
