<?php
include("../../_dbconnect.php");
header("Access-Control-Allow-Origin: *");

$user_id = $_POST['user_id'];
$user_id = $link->real_escape_string($user_id);

$user_info_query = "SELECT id, ref_str FROM users WHERE telegram_id = ?";
$stmt = $link->prepare($user_info_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_info_result = $stmt->get_result();

if ($user_info_row = $user_info_result->fetch_assoc()) {
    $user_id = $user_info_row['id'];
    $ref_str = $user_info_row['ref_str'];
    
    $referral_income = 0.0;
    $referrals = [];

    $referral_query = "SELECT balance, daily_income, user_name, avatar, date FROM users WHERE referal = ?";
    $referral_stmt = $link->prepare($referral_query);
    $referral_stmt->bind_param("i", $user_id);
    $referral_stmt->execute();
    $referral_result = $referral_stmt->get_result();

    while ($referral_row = $referral_result->fetch_assoc()) {
        $referral_income += $referral_row['daily_income'] * 0.15;
        $referral = [
            'balance' => $referral_row['balance'],
            'daily_income_f' => $referral_row['daily_income'],
            'daily_income' => $referral_row['daily_income'] * 0.15,
            'name' => $referral_row['user_name'] ?: 'Аноним',
            'avatar' => $referral_row['avatar'],
            'date' => date('d.m.Y', strtotime($referral_row['date']))
        ];
        $referrals[] = $referral;
    }

    $output = [
        'ref_str' => $ref_str,
        'referal_income' => $referral_income,
        'referals' => $referrals
    ];
    
    echo json_encode($output);
} else {
    echo json_encode([]);
}

$stmt->close();
$link->close();
?>