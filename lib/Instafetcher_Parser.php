<?php

/**
 * Class Instafetcher_Parser
 *
 * @since 1.0.0
 */
class Instafetcher_Parser {

	const INSTAGRAM_TAG_URL = 'https://www.instagram.com/explore/tags/';
	const SAVE_DIR = 'instafetcher';

	protected $tag;

	function __construct($tag) {

		$this->tag = $tag;

	}

	public function startParse() {

		$tag = $this->tag;
		$generalParseUrl = self::INSTAGRAM_TAG_URL;

		$parseUrl = $generalParseUrl . $tag . '/';

		$options = array(
			CURLOPT_RETURNTRANSFER => TRUE,   // return web page
			CURLOPT_HEADER => FALSE,  // don't return headers
			CURLOPT_FOLLOWLOCATION => TRUE,   // follow redirects
			CURLOPT_MAXREDIRS => 10,     // stop after 10 redirects
			CURLOPT_ENCODING => "",     // handle compressed
			CURLOPT_USERAGENT => "test", // name of client
			CURLOPT_AUTOREFERER => TRUE,   // set referrer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
			CURLOPT_TIMEOUT => 120,    // time-out on response
			CURLOPT_SSL_VERIFYPEER => FALSE
		);

		$ch = curl_init($parseUrl);
		curl_setopt_array($ch, $options);
		$content = curl_exec($ch);
		curl_close($ch);

		preg_match_all('/"node.+?(?=is_video")/', $content, $owners);

		$parsed = 0;

		foreach ($owners[0] as $owner) {

			preg_match('/"owner":{"id":"(.+?)"}/', $owner, $ownerId);
			preg_match('/150},{"src":"(.+?)","config_width":240/', $owner, $thumbnail);
			preg_match('/"text":"(.+?)"}/', $owner, $text);
			preg_match('/taken_at_timestamp":(.+?),/', $owner, $taken);
			preg_match('/"id":"(.+?)","edge_/', $owner, $imageId);
			preg_match_all('/https:\/\/.+?(?=")/', $owner, $images);

			$currentOwnerId = $ownerId[1];

			$imageSrc = $images[0][0];

			$imageExistsInDatabase = Instafetcher_Model_Images::checkIfImageExistsInDatabase($imageId[1], $currentOwnerId);

			if (!$imageExistsInDatabase) {

				$userData = Instafetcher_Users::getUserDataFromIdFromExternalAPI($currentOwnerId);
				$userName = $userData['username'];

				if (!empty($userData)) {

					$userExistsInDatabase = Instafetcher_Model_Users::checkIfUserExistsInDatabase($currentOwnerId);

					$userArgs = array(
						'user_id' => $currentOwnerId,
						'user_name' => $userName,
						'fullname' => $userData['full_name'],
						'bio' => $userData['bio'],
						'followers_count' => $userData['followers_count'],
						'following_count' => $userData['following_count'],
						'link' => $userData['external|-url']
					);

					if (!$userExistsInDatabase) {
						Instafetcher_Model_Users::insertUser($userArgs);
					} else {
						Instafetcher_Model_Users::updateUser($userArgs, $userExistsInDatabase);
					}

				} else {

					$userExistsInDatabase = Instafetcher_Model_Users::checkIfUserExistsInDatabase($currentOwnerId);

					$userArgs = array(
						'user_id' => $currentOwnerId
					);

					if (!$userExistsInDatabase) {
						Instafetcher_Model_Users::insertUser($userArgs);
					}

				}

				preg_match_all('/\#([a-zA-Z.|_-]+\b)(?!;)/', $text[1], $tags);
				preg_match_all('/\@([a-zA-Z.|_-]+\b)(?!;)/', $text[1], $mentions);

				$textToSave = preg_replace('/#\S+\s*/', '', $text[1]);

				$takenTime = date('Y-m-d H:i:s', intval($taken[1]));
				$forwardedTime = strtotime("+2 hours", strtotime($takenTime));
				$formattedTime = date("Y-m-d H:i:s", $forwardedTime);

				$imageTableArgs = array(
					'owner_id' => intval($currentOwnerId),
					'image_name' => $imageSrc,
					'in_image_id' => intval($imageId[1]),
					'full_text' => $textToSave,
					'parsed_time' => current_time('mysql'),
					'shot_time' => $formattedTime,
					'thumbnail' => $thumbnail[1]
				);

				$databaseImageId = Instafetcher_Model_Images::insertImage($imageTableArgs);

				if ($databaseImageId > 0) {

					foreach ($tags[1] as $currentTag) {

						$tagArgs = array(
							'image_id' => $databaseImageId,
							'tag' => $currentTag
						);

						Instafetcher_Model_Images::insertTag($tagArgs);
					}

					if (!empty($mentions[1])) {

						foreach ($mentions[1] as $mention) {

							$userId = Instafetcher_Users::getUserIdFromUserName($mention);

							$mentionsArgs = array(
								'image_id' => $databaseImageId,
								'mention_user_name' => $mention
							);

							if ($userId > 0) {
								$mentionsArgs['mention_user_id'] = $userId;
								$mentionsArgs['last_pairing_time'] = current_time('mysql');
							}

							Instafetcher_Model_Mentions::insertMention($mentionsArgs);
						}

					}

					$parsed++;

				}
			}

		}

		Instafetcher_Cron::$parsingStats[$tag] = $parsed;

	}

	public static function getSaveDirectoryUrl() {

		return wp_get_upload_dir()['baseurl'] . DIRECTORY_SEPARATOR . self::SAVE_DIR;

	}

	public static function getSaveDirectoryPath() {

		return wp_get_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . self::SAVE_DIR;

	}

	public function getTag() {

		return $this->tag;

	}

}