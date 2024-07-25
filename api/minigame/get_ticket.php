<?php
include("../../_dbconnect.php");
header("Access-Control-Allow-Origin: *");

$user_id = $_POST['user_id'];
$user_id = $link->real_escape_string($user_id);

$gift = false;
$success = false;

$query = "SELECT boosters, last_ticket FROM users WHERE telegram_id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $boosters = json_decode($row['boosters'], true);
    $last_ticket = new DateTime($row['last_ticket']);
    $now = new DateTime();
    $interval = $now->diff($last_ticket);
    $hours = $interval->h + ($interval->days * 24);

    if ($hours >= 5) {
        $gift = true;
    } else {
        $gift = false;
        $gift_remaining = (5 * 3600) - ($interval->s + ($interval->i * 60) + ($interval->h * 3600) + ($interval->days * 86400));
    }
}

$add_tickets = 3;
if (is_array($boosters)) {
    foreach ($boosters as $booster) {
        if ($booster['booster_code'] == 'treasure') {
            switch ($booster['booster_lvl']) {
                case 1:
                    $add_tickets = 5;
                    break;
                case 2:
                    $add_tickets = 7;
                    break;
                case 3:
                    $add_tickets = 9;
                    break;
                case 4:
                    $add_tickets = 12;
                    break;
                case 5:
                    $add_tickets = 15;
                    break;
            }
        }
    }
}

if ($gift) {
    $update_query = "UPDATE users SET tickets = tickets + ?, last_ticket = NOW() WHERE telegram_id = ?";
    $update_stmt = $link->prepare($update_query);
    $update_stmt->bind_param("is", $add_tickets, $user_id);
    if ($update_stmt->execute()) {
        $success = true;
    }
    $update_stmt->close();
}

if ($success) {
    echo "ok";
} else {
    echo "error";
}

$stmt->close();
$link->close();
?>