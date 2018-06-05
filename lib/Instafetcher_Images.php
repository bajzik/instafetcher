<?php

/**
 * Class Instafetcher_Images
 *
 * @since 1.0.0
 */
class Instafetcher_Images {

	public static function getImageUrl($ownerId = 0, $imageName = '', $withoutFileName = FALSE) {

		$dir = Instafetcher_Parser::getSaveDirectoryUrl() . DIRECTORY_SEPARATOR . $ownerId . DIRECTORY_SEPARATOR;

		if (!$withoutFileName) {
			$dir .= $imageName;
		}

		return $dir;

	}

	public static function formatFullText($fullText = '') {

		return str_replace('\n', '<br>', Emoji::Decode($fullText));

	}

	public static function getImageDetailUrl($imageId = 0) {

		$urlToPage = Instafetcher_Core::getPageDetailUrl() . $imageId . '/';

		return $urlToPage;

	}

	public static function checkRemoteFile($url) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		// don't download content
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if (curl_exec($ch) !== FALSE) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}