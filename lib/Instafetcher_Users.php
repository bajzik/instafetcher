<?php

/**
 * Class Instafetcher_Users
 *
 * @since 1.0.0
 */
class Instafetcher_Users {

	public static function getUserDataFromIdFromExternalAPI($userId = 0) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, 'https://i.instagram.com/api/v1/users/' . $userId . '/info/');
		$result = curl_exec($ch);
		curl_close($ch);

		$userData = json_decode($result);
		$returnedData = array();

		$success = TRUE;

		if ($userData->status === 'fail') {
			$success = FALSE;
		}

		if ($success) {
			$returnedData = array(
				'username' => $userData->user->username,
				'full_name' => $userData->user->full_name,
				'followers_count' => $userData->user->follower_count,
				'following_count' => $userData->user->following_count,
				'bio' => $userData->user->biography,
				'external_url' => $userData->user->external_url
			);
		}

		return $returnedData;
	}

	public static function getUserIdFromUserName($userName = '') {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, 'https://codeofaninja.com/tools/find-instagram-id-answer.php?instagram_username=' . $userName);
		$result = curl_exec($ch);
		curl_close($ch);

		preg_match('/User ID: <b>(.+?)<\/b>/', $result, $id);

		return $id[1];

	}

	public static function getUserDetailUrl($userId = 0) {

		$urlToPage = Instafetcher_Core::getUserDetailUrl() . $userId . '/';

		return $urlToPage;

	}

}