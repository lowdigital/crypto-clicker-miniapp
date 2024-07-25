<?php
include("../../_dbconnect.php");
header("Access-Control-Allow-Origin: *");

$user_id = $_POST['user_id'];
$user_id = $link->real_escape_string($user_id);

$user_balance = 0.0;
$user_income = 0;
$boosters = [];
$click_revenue = 1000;

$current_time = new DateTime();
$current_time->setTimezone(new DateTimeZone('UTC'));

$query = "SELECT balance, income, boosters, last_game FROM users WHERE telegram_id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $user_balance = $row['balance'];
    $user_income = $row['income'];
    $boosters = json_decode($row['boosters'], true);
    $last_game = new DateTime($row['last_game']);
    $last_game->setTimezone(new DateTimeZone('UTC'));

    $interval = $current_time->diff($last_game);
    $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

    if ($minutes > 5) {
        $link->close();
        exit;
    }

    if (is_array($boosters)) {
        foreach ($boosters as $booster) {
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

    $user_balance += $click_revenue;
    $user_income += $click_revenue;
    $update_query = "UPDATE users SET balance = ?, income = ? WHERE telegram_id = ?";
    $update_stmt = $link->prepare($update_query);
    $update_stmt->bind_param("iis", $user_balance, $user_income, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
}

$output = ['balance' => $user_balance];
echo json_encode($output);

$stmt->close();
$link->close();
?>