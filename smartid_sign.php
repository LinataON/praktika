<?php
require 'common.php';

// Создание контейнера
$url = "https://dsig.eesti.ee/siga/hashcodecontainers"; // Указываем конечную точку API для создания контейнера
$response = sendRequest($url);  // Вызываем функцию sendRequest для отправки запроса POST. Ответ от API сохраняется в переменной
if (!isset($response['containerId'])) { // Проверяем, содержится ли в ответе ключ containerId
    die("Error: containerId not found in response."); // Если ключ отсутствует, выводим ошибку и завершаем выполнение скрипта
}
$containerId = $response['containerId']; // Сохраняем идентификатор контейнера (containerId) для использования в дальнейших запросах

// Добавление файла в контейнер
$url = "https://dsig.eesti.ee/siga/hashcodecontainers/$containerId/datafiles"; // Формируем URL с использованием containerId
$fileContent = file_get_contents('document.pdf'); // Открываем файл document.pdf и считываем его содержимое
$fileName = 'document.pdf'; 
// Подготовка данных
$data = [
    'datafileName' => $fileName, 
    'datafileContent' => base64_encode($fileContent), // Содержимое файла, закодированное в Base64
    'datafileMimeType' => 'application/pdf' // MIME-тип файла
];

$response = sendRequest($url, $data); // Передаем данные в запросе к API для добавления файла в контейнер.


// Инициация подписания через Smart-ID
$url = "https://dsig.eesti.ee/siga/hashcodecontainers/$containerId/smartidsigning"; // Используем containerId для формирования URL.
// Подготовка данных
$data = [
    'type' => 'smartid', // тип подписания
    'personalCode' => '12345678901',
    'country' => 'EE'
];

$response = sendRequest($url, $data); //  Отправляем данные в запросе к API, чтобы инициировать процесс подписания
if (!isset($response['signatureId'])) { // Проверяем, содержится ли в ответе ключ signatureId
    die("Error: signatureId not found in response."); // Если ключ отсутствует, выводим ошибку и завершаем выполнение
}
$signatureId = $response['signatureId']; // Сохраняем идентификатор подписи (signatureId) для проверки статуса

// Проверка статуса подписи
$url = "https://dsig.eesti.ee/siga/hashcodecontainers/$containerId/smartidsigning/$signatureId/status"; // Формируем URL с использованием containerId и signatureId.
$response = sendRequest($url, [], 'GET'); // Используем метод GET для получения статуса подписи
$status = $response['status'] ?? null; // Извлекаем status из ответа
if (!$status) {
    die("Error: Status not found in response."); // Если статус отсутствует, выводим ошибку и завершаем выполнение
}

echo "Signing status: $status";
// Обработка статуса
if ($status === 'COMPLETED') {
    echo "Signature process completed successfully.";
} elseif ($status === 'PENDING') {
    echo "Signature process is still pending. Retry later.";
} else {
    echo "Signature process failed.";
}

// Скачивание подписанного контейнера
$url = "https://dsig.eesti.ee/siga/hashcodecontainers/$containerId"; // Формируем URL с использованием containerId
$response = sendRequest($url, [], 'GET'); // Используем метод GET для получения содержимого контейнера
if (!isset($response['containerContent'])) { // Проверяем наличие ключа containerContent в ответе
    die("Error: containerContent not found in response.");
}
file_put_contents('signed_container.asice', base64_decode($response['containerContent'])); // Раскодируем содержимое контейнера из Base64 и Сохраняем его в файл signed_container.asice
echo "Signed container saved as signed_container.asice."; // Уведомляем пользователя, что подписанный контейнер успешно сохранен.

?>
