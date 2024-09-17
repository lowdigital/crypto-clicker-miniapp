<?php
$log_path = "log/" . date("Y") . "/";
if (!file_exists($log_path)) mkdir($log_path, 0777, true);
$log_path .= date("Y-m-d") . ".txt";
$log = file_get_contents('php://input') . ",\n\n";
file_put_contents($log_path, $log, FILE_APPEND);

include("_dbconnect.php");

function send_message($user_id, $message_id, $tg_message) {
    global $tg_token;

    $tg_data = [
        'chat_id' => $user_id,
        'text' => $tg_message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ];

    if ($message_id != 0) {
        $tg_data['reply_to_message_id'] = $message_id;
    }

    $response = file_get_contents("https://api.telegram.org/bot$tg_token/sendMessage?" . http_build_query($tg_data));
    
    // Logging the response
    global $log_path;
    file_put_contents($log_path, "Telegram API Response: " . $response . "\n", FILE_APPEND);

    return $response !== false;
}

function generateRandomString($length = 16) {
    return bin2hex(random_bytes($length / 2));
}

$data = json_decode(file_get_contents('php://input'));
$message_id = $data->message->message_id ?? 0;
$user_id = $data->message->from->id ?? null;
$user_name = $data->message->from->username ?? "Аноним";
$chat_id = $data->message->chat->id ?? $user_id;

if ($chat_id) {
    $f_users_list = fopen("log/user_ids.txt", "a+");
    $users_array = [];
    while (!feof($f_users_list)) {
        $users_list_line = trim(fgets($f_users_list));
        if ($users_list_line !== '') {
            $users_array[] = $users_list_line;
        }
    }

    if (!in_array($chat_id, $users_array)) {
        fwrite($f_users_list, "\n" . $chat_id);
    }
    fclose($f_users_list);
} else {
    $link->close();
    exit;
}

if (isset($data->message->text) && strpos($data->message->text, "/start") !== false) {
    send_message($chat_id, $message_id, "Привет!\n\nДобро пожаловать в бот BOMJ COIN\n\nНажми \"Play\", чтобы начать игру");

    $ref_str = trim(str_replace(["/start ", "/start"], "", $data->message->text));
    $ref_str = $link->real_escape_string($ref_str);

    $query = "SELECT COUNT(*) as count FROM users WHERE telegram_id = ?";
    $stmt = $link->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        $link->close();
        exit;
    }

    if ($ref_str !== "") {
        $query = "SELECT id FROM users WHERE ref_str = ?";
        $stmt = $link->prepare($query);
        $stmt->bind_param("s", $ref_str);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $referal_id = $row['id'];
            $ref_new_str = generateRandomString();
            $query = "INSERT INTO users (telegram_id, user_name, referal, ref_str, balance) VALUES (?, ?, ?, ?, 1000000)";
            $stmt = $link->prepare($query);
            $stmt->bind_param("isss", $user_id, $user_name, $referal_id, $ref_new_str);
            $stmt->execute();

            $query = "UPDATE users SET balance = balance + 2000000, tickets = tickets + 5 WHERE id = ?";
            $stmt = $link->prepare($query);
            $stmt->bind_param("i", $referal_id);
            $stmt->execute();
        }
    }
}

$link->close();
?>
