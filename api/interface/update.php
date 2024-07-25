<?php
include("../../_dbconnect.php");
header("Access-Control-Allow-Origin: *");

function generateRandomString($length = 16) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
$user_name = isset($_POST['user_name']) ? $_POST['user_name'] : '';

$user_id = $link->real_escape_string($user_id);
$user_name = $link->real_escape_string($user_name);

$counter = 0;
$tickets = 1;
$user_balance = 0;
$user_income = 0;
$gift_remaining = 0;
$gift = false;
$boosters = [];

$query = "SELECT * FROM users WHERE telegram_id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $counter++;
    $user_balance = $row['balance'];
    $user_income = $row['income'];
    $tickets = $row['tickets'];

    $last_ticket = $row['last_ticket'];
    $date = new DateTime($last_ticket);
    $now = new DateTime();
    $interval = $now->diff($date);
    $hours = $interval->h + ($interval->days * 24);
    $seconds = $interval->s + ($interval->i * 60) + ($interval->h * 3600) + ($interval->days * 86400);
    $boosters = json_decode($row['boosters'], true);

    if ($hours >= 5) {
        $gift = true;
    } else {
        $gift = false;
        $gift_remaining = (5 * 3600) - $seconds;
    }
}

$tap_income = 10;
$click_revenue = 1000;
if (is_array($boosters)) {
    foreach ($boosters as $booster) {
        if ($booster['booster_code'] == 'booster') {
            switch ($booster['booster_lvl']) {
                case 1:
                    $tap_income = 20;
                    break;
                case 2:
                    $tap_income = 30;
                    break;
                case 3:
                    $tap_income = 40;
                    break;
                case 4:
                    $tap_income = 50;
                    break;
                case 5:
                    $tap_income = 100;
                    break;
            }
        }
        if ($booster['booster_code'] == 'bow') {
            switch ($booster['booster_lvl']) {
                case 1:
                    $click_revenue = 2000;
                    break;
                case 2:
                    $click_revenue = 3000;
                    break;
                case 3:
                    $click_revenue = 4000;
                    break;
                case 4:
                    $click_revenue = 5000;
                    break;
                case 5:
                    $click_revenue = 6000;
                    break;
            }
        }
    }
}

if ($counter == 0) {
    $ref_str = generateRandomString();
    $insert_query = "INSERT INTO users (telegram_id, user_name, ref_str, balance, tickets, last_ticket) VALUES (?, ?, ?, ?, ?, NOW())";
    $insert_stmt = $link->prepare($insert_query);
    $insert_stmt->bind_param("issii", $user_id, $user_name, $ref_str, $user_balance, $tickets); // telegram_id - bigint(20), balance - bigint(20), tickets - int(11)
    $insert_stmt->execute();
    $insert_stmt->close();
} else {
    $user_balance += $tap_income;
    $user_income += $tap_income;
    $update_query = "UPDATE users SET balance = ?, income = ? WHERE telegram_id = ?";
    $update_stmt = $link->prepare($update_query);
    $update_stmt->bind_param("iii", $user_balance, $user_income, $user_id); // balance - bigint(20), income - bigint(20), telegram_id - bigint(20)
    $update_stmt->execute();
    $update_stmt->close();
}

$leaders = [];
$leader_query = "SELECT user_name, balance, avatar FROM users ORDER BY balance DESC LIMIT 5";
$leader_result = $link->query($leader_query);

while ($row = $leader_result->fetch_assoc()) {
    $leader_name = $row['user_name'];
    if (empty($leader_name)) {
        $leader_name = "Аноним";
    }
    $leaders[] = [
        'name' => $leader_name,
        'balance' => $row['balance'],
        'avatar' => $row['avatar']
    ];
}

$output = [
    'balance' => $user_balance,
    'tickets' => $tickets,
    'leaders' => $leaders,
    'gift_remaining' => $gift_remaining,
    'gift' => $gift,
    'tap_income' => $tap_income,
    'game_revenue' => $click_revenue
];

echo json_encode($output);

$stmt->close();
$link->close();
?>