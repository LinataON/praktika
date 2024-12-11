<?php
function sendRequest($url, $data = [], $method = 'POST') {
    $ch = curl_init($url); // Инициализируется cURL-сессия для указанного URL, что позволяет выполнять HTTP-запросы.

    // Общие настройки
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // результат запроса должен быть возвращен как строка
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); //Устанавливает заголовок HTTP-запроса, указывая, что отправляемые данные имеют формат JSON

    // Настройки в зависимости от метода
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true); // Включает использование метода POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Добавляет данные в тело запроса, предварительно закодированные в формате JSON
    } elseif ($method === 'GET') {
        curl_setopt($ch, CURLOPT_HTTPGET, true); //  Явно указывает использовать метод GET
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // Задает метод PUT
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Добавляет данные в тело запроса,
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); // Задает метод DELETE
    }

    // Выполнение запроса
    $response = curl_exec($ch); // Выполняет запрос и сохраняет ответ сервера 
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch); // Если возникает ошибка в cURL, выводится сообщение об ошибке 
    }

    curl_close($ch); // Закрывает cURL-сессию
    return json_decode($response, true); // Возвращаем ответ как массив
}
?>
