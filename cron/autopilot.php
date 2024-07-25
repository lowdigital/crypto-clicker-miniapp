<?php
include("../_dbconnect.php");

$query = "SELECT id, boosters FROM users";
$result = $link->query($query);

if ($result) {
    $updateStmt = $link->prepare("UPDATE users SET balance = balance + ?, income = income + ? WHERE id = ?");
    
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $boosters = json_decode($row['boosters'], true);

        $autopilotIncome = 0;

        if (is_array($boosters)) {
            foreach ($boosters as $booster) {
                if ($booster['booster_code'] == 'autopilot') {
                    switch ($booster['booster_lvl']) {
                        case 1:
                            $autopilotIncome = 10000;
                            break;
                        case 2:
                            $autopilotIncome = 20000;
                            break;
                        case 3:
                            $autopilotIncome = 30000;
                            break;
                        case 4:
                            $autopilotIncome = 40000;
                            break;
                        case 5:
                            $autopilotIncome = 50000;
                            break;
                    }
                }
            }
        }

        if ($autopilotIncome > 0) {
            $updateStmt->bind_param("iii", $autopilotIncome, $autopilotIncome, $id);
            $updateStmt->execute();
        }
    }
    $updateStmt->close();
}

$link->close();
?>