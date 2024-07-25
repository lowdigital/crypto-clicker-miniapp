<?php
include("../_dbconnect.php");

function sendRequest($method, $data = []) {
    global $tg_token;
    $url = "https://api.telegram.org/bot$tg_token/$method";

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    return $result ? json_decode($result, true) : null;
}

$currentId = file_get_contents('avatar.counter');

$query = "SELECT MAX(id) AS max_id FROM users";
$result = $link->query($query);
$maxId = $result ? $result->fetch_assoc()['max_id'] : 0;

if ($currentId > $maxId) {
    $currentId = 0;
}

$query = "SELECT id, telegram_id FROM users WHERE id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $currentId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $telegramId = $row['telegram_id'];
    $defaultAvatar = "https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png?20150327203541";
    $avatar = $defaultAvatar;

    $response = sendRequest('getUserProfilePhotos', ['user_id' => $telegramId]);

    if ($response && $response['ok'] && $response['result']['total_count'] > 0) {
        $photos = $response['result']['photos'];
        $fileId = end($photos[0])['file_id'];

        $fileResponse = sendRequest('getFile', ['file_id' => $fileId]);

        if ($fileResponse && $fileResponse['ok']) {
            $filePath = $fileResponse['result']['file_path'];
            $avatar = "https://api.telegram.org/file/bot$token/$filePath";
        }
    }

    echo "$id: $avatar";
    $updateStmt = $link->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    $updateStmt->bind_param("si", $avatar, $id);
    $updateStmt->execute();
    $updateStmt->close();
}

$currentId++;
file_put_contents('avatar.counter', $currentId);
$stmt->close();
$link->close();
?>