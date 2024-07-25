<?php
include("../../_dbconnect.php");
include("../../_boosters.php");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
$user_name = isset($_POST['user_name']) ? $_POST['user_name'] : '';

$user_id = $link->real_escape_string($user_id);
$user_name = $link->real_escape_string($user_name);

$boosters = [];
$output = [];

$query = "SELECT boosters FROM users WHERE telegram_id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $boosters = json_decode($row['boosters'], true);
}
$stmt->close();

foreach ($allBoosters as $booster) {
    $boosterCode = $booster['booster_code'];
    $booster['booster_lvl'] = 0;

    if (is_array($boosters)) {
        foreach ($boosters as $userBooster) {
            if ($userBooster['booster_code'] == $boosterCode) {
                $booster['booster_lvl'] = $userBooster['booster_lvl'];
            }
        }
    }

    $output[] = $booster;
}

echo json_encode($output);
$link->close();
?>