<?php
	include("../_dbconnect.php");

	function sendRequest($method, $data = []) {
		global $tg_token;
		$url = "https://api.telegram.org/bot$tg_token/$method";

		$options = [
			'http' => [
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data),
			],
		];

		$context  = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);

		return $result ? json_decode($result, true) : null;
	}

	function resizeAndSaveImage($sourceUrl, $destinationPath, $width, $height, $quality = 90) {
		$image = imagecreatefromjpeg($sourceUrl);
		if ($image === false) {
			return false;
		}

		$resizedImage = imagecreatetruecolor($width, $height);

		$origWidth = imagesx($image);
		$origHeight = imagesy($image);

		imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

		$saved = imagejpeg($resizedImage, $destinationPath, $quality);

		imagedestroy($image);
		imagedestroy($resizedImage);

		return $saved;
	}

	$current_id = file_get_contents('avatar.counter');
	
	$avatarDir = '../avatar';
	if (!is_dir($avatarDir)) {
		mkdir($avatarDir, 0755, true);
	}

	$query = "SELECT MAX(id) AS max_id FROM `users`";
	$result = $link->query($query);
	if ($result) {
		$row = $result->fetch_assoc();
		$maxId = $row['max_id'];
	} else {
		$link->close();
		exit;
	}

	if ($current_id > $maxId) {
		$current_id = 0;
	}

	$query = "SELECT id, telegram_id FROM users WHERE id = ?";
	$stmt = $link->prepare($query);
	$stmt->bind_param("i", $current_id);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($row = $result->fetch_assoc()) {
		$id = $row['id'];
		$telegram_id = $row['telegram_id'];
		$default_avatar = "https://upload.wikimedia.org/wikipedia/commons/7/7c/Profile_avatar_placeholder_large.png?20150327203541";
		$avatar = $default_avatar;

		$response = sendRequest('getUserProfilePhotos', ['user_id' => $telegram_id]);

		if ($response && $response['ok'] && $response['result']['total_count'] > 0) {
			$photos = $response['result']['photos'];
			$fileId = end($photos[0])['file_id'];

			$fileResponse = sendRequest('getFile', ['file_id' => $fileId]);

			if ($fileResponse && $fileResponse['ok']) {
				$filePath = $fileResponse['result']['file_path'];
				$fileUrl = "https://api.telegram.org/file/bot$tg_token/$filePath";

				$localAvatarPath = "../avatar/$telegram_id.jpg";

				if (resizeAndSaveImage($fileUrl, $localAvatarPath, 100, 100)) {
					$avatar = "/avatar/$telegram_id.jpg";
				}
			}
		}

		$updateStmt = $link->prepare("UPDATE users SET avatar = ? WHERE id = ?");
		$updateStmt->bind_param("si", $avatar, $id);
		$updateStmt->execute();
		$updateStmt->close();
		
		echo $avatar;
	}

	$current_id++;
	file_put_contents('avatar.counter', $current_id);
	$stmt->close();
	$link->close();
?>
<script>location.reload();</script>