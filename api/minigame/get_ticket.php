<?php
include("../../_dbconnect.php");
header("Access-Control-Allow-Origin: *");

$user_id = $_POST['user_id'];
$user_id = $link->real_escape_string($user_id);

$gift = false;
$success = false;
$output = [];

$query = "SELECT boosters, last_ticket FROM users WHERE telegram_id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $boosters = json_decode($row['boosters'], true);
    $last_ticket = new DateTime($row['last_ticket']);
    $now = new DateTime();
    $interval = $now->diff($last_ticket);
    $hours = $interval->h + ($interval->days * 24);

    $gift = $hours >= 5;

    if (!$gift) {
        $gift_remaining = (5 * 3600) - $interval->s - ($interval->i * 60) - ($interval->h * 3600) - ($interval->days * 86400);
    }
}

$add_tickets = 3;
if (is_array($boosters)) {
    foreach ($boosters as $booster) {
        if ($booster['booster_code'] == 'treasure') {
            $levels_to_tickets = [1 => 5, 2 => 7, 3 => 9, 4 => 12, 5 => 15];
            if (isset($levels_to_tickets[$booster['booster_lvl']])) {
                $add_tickets = $levels_to_tickets[$booster['booster_lvl']];
                break;
            }
        }
    }
}

if ($gift) {
    $update_query = "UPDATE users SET tickets = tickets + ?, last_ticket = NOW() WHERE telegram_id = ?";
    $update_stmt = $link->prepare($update_query);
    $update_stmt->bind_param("ii", $add_tickets, $user_id);
    if ($update_stmt->execute()) {
        $success = true;
    }
    $update_stmt->close();
}

if ($success) {
    $output = [
        'status' => 'ok',
        'text' => "Вы получили $add_tickets билета" . ($add_tickets > 1 ? 'ов' : '')
    ];
} else {
    $output['status'] = 'error';
}

echo json_encode($output);
$stmt->close();
$link->close();
?>