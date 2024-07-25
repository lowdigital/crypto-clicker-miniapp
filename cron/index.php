<?php
include("../_dbconnect.php");

$updateDailyIncomeStmt = $link->prepare("UPDATE users SET tmp_sum = ?, daily_income = ? WHERE id = ?");
$updateReferralIncomeStmt = $link->prepare("UPDATE users SET income = income + ?, balance = balance + ? WHERE id = ?");

$query = "SELECT id, tmp_sum, income FROM users";
$result = $link->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $tmpSum = $row['tmp_sum'];
        $income = $row['income'];
        $dailyIncome = $income - $tmpSum;
        $newTmpSum = $income;

        $updateDailyIncomeStmt->bind_param("iii", $newTmpSum, $dailyIncome, $id);
        $updateDailyIncomeStmt->execute();
    }
}

$query = "SELECT id FROM users";
$result = $link->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $referralIncome = 0;

        $queryReferrals = "SELECT daily_income FROM users WHERE referal = ?";
        $stmtReferrals = $link->prepare($queryReferrals);
        $stmtReferrals->bind_param("i", $id);
        $stmtReferrals->execute();
        $resultReferrals = $stmtReferrals->get_result();

        while ($refRow = $resultReferrals->fetch_assoc()) {
            $referralIncome += round($refRow['daily_income'] * 0.15);
        }

        $stmtReferrals->close();

        $updateReferralIncomeStmt->bind_param("iii", $referralIncome, $referralIncome, $id);
        $updateReferralIncomeStmt->execute();
    }
}

$updateDailyIncomeStmt->close();
$updateReferralIncomeStmt->close();
$link->close();
?>