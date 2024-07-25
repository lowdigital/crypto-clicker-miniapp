<?php
include("../../_dbconnect.php");
include("../../_boosters.php");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
$booster_code = isset($_POST['booster_code']) ? $_POST['booster_code'] : '';
$new_level = isset($_POST['booster_level']) ? intval($_POST['booster_level']) : 0;
$user_id = $link->real_escape_string($user_id);
$booster_code = $link->real_escape_string($booster_code);
$response = [];

$query = "SELECT balance, boosters FROM users WHERE telegram_id = ?";
$stmt = $link->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $balance = intval($row['balance']);
    $boosters = json_decode($row['boosters'], true);

    if (is_null($boosters)) {
        $boosters = [];
    }

    $price = 0;
    $booster_found = false;
    foreach ($allBoosters as $booster) {
        if ($booster['booster_code'] === $booster_code) {
            if ($new_level > $booster['booster_max_lvl']) {
                $response = ["status" => "error", "message" => "Вы достигли максимального уровня улучшения."];
                echo json_encode($response);
                $stmt->close();
                $link->close();
                exit;
            }
            $price = $booster['booster_prices'][$new_level];
            $booster_found = true;
            break;
        }
    }

    if (!$booster_found) {
        $response = ["status" => "error", "message" => "Некорректный код улучшения."];
        echo json_encode($response);
        $stmt->close();
        $link->close();
        exit;
    }

    if ($balance < $price) {
        $response = ["status" => "error", "message" => "У вас недостаточно средств."];
        echo json_encode($response);
        $stmt->close();
        $link->close();
        exit;
    }

    $balance -= $price;

    $boosterFound = false;
    foreach ($boosters as &$userBooster) {
        if ($userBooster['booster_code'] === $booster_code) {
            $userBooster['booster_lvl'] = $new_level;
            $boosterFound = true;
            break;
        }
    }

    if (!$boosterFound) {
        $boosters[] = ["booster_code" => $booster_code, "booster_lvl" => $new_level];
    }

    $boosters_json = json_encode($boosters);
    $updateQuery = "UPDATE users SET balance = ?, boosters = ? WHERE telegram_id = ?";
    $updateStmt = $link->prepare($updateQuery);
    $updateStmt->bind_param("isi", $balance, $boosters_json, $user_id);
    $updateStmt->execute();
    $updateStmt->close();

    $response = ["status" => "success"];
} else {
    $response = ["status" => "error", "message" => "Пользователь не найден."];
}

echo json_encode($response);

$stmt->close();
$link->close();
?>