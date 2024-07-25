<?php
include("../../_dbconnect.php");
header("Access-Control-Allow-Origin: *");

$user_id = $_POST['user_id'];
$user_id = $link->real_escape_string($user_id);

$query = "SELECT tickets FROM users WHERE telegram_id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $tickets = $row['tickets'];

    if ($tickets < 1) {
        echo "Недостаточно билетов";
    } else {
        $update_query = "UPDATE users SET tickets = tickets - 1, `last_game` = '" . date("Y-m-d H:i:s") . "' WHERE telegram_id = ?";
        $update_stmt = $link->prepare($update_query);
        $update_stmt->bind_param("i", $user_id);
        if ($update_stmt->execute()) {
            echo "ok";
        } else {
            echo "Ошибка обновления количества билетов";
        }
        $update_stmt->close();
    }
} else {
    echo "Не найден пользователь";
}

$stmt->close();
$link->close();
?>