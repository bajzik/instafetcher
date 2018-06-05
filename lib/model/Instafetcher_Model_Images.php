<?php

class Instafetcher_Model_Images {

	public static function insertImage($imageArgs = array()) {

		if (empty($imageArgs)) {
			return FALSE;
		}

		global $wpdb;

		$imagesTable = Instafetcher_Database::get_table_name(Instafetcher_Database::IMAGES_TABLE);

		$wpdb->insert(
			$imagesTable,
			$imageArgs
		);

		return $wpdb->insert_id;

	}

	public static function insertTag($tagArgs = array()) {

		if (empty($tagArgs)) {
			return FALSE;
		}

		global $wpdb;

		$tagsTable = Instafetcher_Database::get_table_name(Instafetcher_Database::TAGS_TABLE);

		$wpdb->insert(
			$tagsTable,
			$tagArgs
		);

		return $wpdb->insert_id;

	}

	public static function checkIfImageExistsInDatabase($inImageId = 0, $ownerId = 0) {

		$inImageId = intval($inImageId);
		$ownerId = intval($ownerId);

		if ($inImageId === 0 || $ownerId === 0) {

			return FALSE;
		}

		global $wpdb;

		$imagesTable = Instafetcher_Database::get_table_name(Instafetcher_Database::IMAGES_TABLE);

		$preparedQuery = "SELECT image_id FROM {$imagesTable} "
			. " WHERE in_image_id = %d AND owner_id = %d ";

		$query = $wpdb->prepare($preparedQuery, $inImageId, $ownerId);

		$result = $wpdb->get_var($query);

		return (intval($result) > 0) ? $result : FALSE;
	}

	public static function getImages($queryArgs = array()) {

		global $wpdb;

		$imagesTable = Instafetcher_Database::get_table_name(Instafetcher_Database::IMAGES_TABLE);
		$tagsTable = Instafetcher_Database::get_table_name(Instafetcher_Database::TAGS_TABLE);
		$usersTable = Instafetcher_Database::get_table_name(Instafetcher_Database::USERS_TABLE);
		$mensionsTable = Instafetcher_Database::get_table_name(Instafetcher_Database::MENTIONS_TABLE);

		$countOnly = FALSE;

		if (array_key_exists('count_only', $queryArgs) && $queryArgs['count_only'] === 'yes') {
			$countOnly = TRUE;
		}

		$orderBy = 'im.shot_time DESC';

		$offset = 0;

		if (array_key_exists('offset', $queryArgs) && $queryArgs['offset'] > 0) {
			$offset = $queryArgs['offset'];
		}

		$prepareArgs = array();

		$selectString = 'im.*, COUNT(DISTINCT t.tag) as tags, COUNT(DISTINCT m.mention_user_id) as mentions, u.*';

		if ($countOnly) {
			$selectString = 'COUNT(*)';
		}

		$userSet = FALSE;
		$tagSet = FALSE;

		if (array_key_exists('user', $queryArgs) && intval($queryArgs['user'] > 0)) {
			$userSet = TRUE;
		}

		if (array_key_exists('tag', $queryArgs) && $queryArgs['tag'] !== '') {
			$tagSet = TRUE;
		}

		$query = "SELECT " . $selectString . " FROM {$imagesTable} im "
			. " LEFT JOIN {$mensionsTable} m ON (im.image_id = m.image_id) ";

		if (!$countOnly || ($countOnly && $tagSet)) {
			$query .= " LEFT JOIN {$tagsTable} t ON (im.image_id = t.image_id) ";
		}

		if (!$countOnly || ($countOnly && $userSet)) {
			$query .= " LEFT JOIN {$usersTable} u ON (im.owner_id = u.user_id) ";
		}

		$query .= " WHERE 1=1 ";

		if (array_key_exists('tag', $queryArgs) && $queryArgs['tag'] !== '') {
			$query .= " AND t.tag = %s ";
			array_push($prepareArgs, $queryArgs['tag']);
		}

		if (array_key_exists('user', $queryArgs) && intval($queryArgs['user'] > 0)) {
			$query .= " AND im.owner_id = %d";
			array_push($prepareArgs, intval($queryArgs['user']));
		}

		if (!$countOnly) {
			$query .= " GROUP BY im.image_id ";
		}
		$query .= " ORDER BY " . $orderBy . " ";

		if ($countOnly) {
			$limit = 0;
		} else {
			$limit = Instafetcher_Shortcodes::PER_PAGE_PHOTOS;
		}

		if ($limit > 0) {
			$query .= " LIMIT %d ";
			array_push($prepareArgs, $limit);
		}

		if ($offset > 0) {
			$query .= " OFFSET %d ";
			array_push($prepareArgs, $offset);
		}

		$preparedQuery = $wpdb->prepare($query, $prepareArgs);

		if (!$countOnly) {
			$results = $wpdb->get_results($preparedQuery, ARRAY_A);
		} else {
			$results = intval($wpdb->get_var($preparedQuery));
		}

		return $results;
	}

	public static function getImage($imageId = 0) {

		global $wpdb;

		$imagesTable = Instafetcher_Database::get_table_name(Instafetcher_Database::IMAGES_TABLE);
		$usersTable = Instafetcher_Database::get_table_name(Instafetcher_Database::USERS_TABLE);

		$query = "SELECT * FROM {$imagesTable} im "
			. " LEFT JOIN {$usersTable} u ON (im.owner_id = u.user_id) "
			. " WHERE im.image_id = %d ";

		$preparedQuery = $wpdb->prepare($query, $imageId);

		$results = $wpdb->get_row($preparedQuery, ARRAY_A);

		return $results;
	}

	public static function getTagsByImageId($imageId = 0) {

		$imageId = intval($imageId);

		if ($imageId === 0) {
			return FALSE;
		}

		global $wpdb;

		$tagsTable = Instafetcher_Database::get_table_name(Instafetcher_Database::TAGS_TABLE);

		$preparedQuery = "SELECT * FROM {$tagsTable} "
			. " WHERE image_id = %d";

		$query = $wpdb->prepare($preparedQuery, $imageId);

		$result = $wpdb->get_results($query, ARRAY_A);

		return $result;

	}

	public static function getMentionsByImageId($imageId = 0) {

		$imageId = intval($imageId);

		if ($imageId === 0) {
			return FALSE;
		}

		global $wpdb;

		$mentionsTable = Instafetcher_Database::get_table_name(Instafetcher_Database::MENTIONS_TABLE);

		$preparedQuery = "SELECT * FROM {$mentionsTable} "
			. " WHERE image_id = %d";

		$query = $wpdb->prepare($preparedQuery, $imageId);

		$result = $wpdb->get_results($query, ARRAY_A);

		return $result;

	}

	public static function getMostUsedTagsForUser($userId = 0) {

		$userId = intval($userId);

		if ($userId === 0) {
			return FALSE;
		}

		global $wpdb;

		$imagesTable = Instafetcher_Database::get_table_name(Instafetcher_Database::IMAGES_TABLE);
		$tagsTable = Instafetcher_Database::get_table_name(Instafetcher_Database::TAGS_TABLE);

		$preparedQuery = "SELECT COUNT(t.tag) as count, t.tag FROM {$imagesTable} i"
			. " LEFT JOIN {$tagsTable} t ON (i.image_id = t.image_id) "
			. " WHERE i.owner_id = %d "
			. " GROUP BY t.tag "
			. " ORDER BY count DESC "
			. "LIMIT 10 ";

		$query = $wpdb->prepare($preparedQuery, $userId);

		$result = $wpdb->get_results($query, ARRAY_A);

		return $result;

	}

	public static function updateImage($imageArgs = array(), $imageId = 0) {

		$imageId = intval($imageId);

		if (empty($imageArgs) || $imageId === 0) {
			return FALSE;
		}

		global $wpdb;

		$imagesTable = Instafetcher_Database::get_table_name(Instafetcher_Database::IMAGES_TABLE);

		$wpdb->update(
			$imagesTable,
			$imageArgs,
			array(
				'image_id' => $imageId
			)
		);

	}

	public static function getImagesToContentCheck() {


		global $wpdb;

		$imagesTable = Instafetcher_Database::get_table_name(Instafetcher_Database::IMAGES_TABLE);

		$query = "SELECT * FROM {$imagesTable} "
			. " ORDER BY content_check_time ASC LIMIT 500";

		$result = $wpdb->get_results($query, ARRAY_A);

		return $result;

	}

	public static function removeImage($imageId = 0) {

		$imageId = intval($imageId);

		if ($imageId === 0) {
			return FALSE;
		}

		global $wpdb;

		$imagesTable = Instafetcher_Database::get_table_name(Instafetcher_Database::IMAGES_TABLE);
		$tagsTable = Instafetcher_Database::get_table_name(Instafetcher_Database::TAGS_TABLE);
		$mentionsTable = Instafetcher_Database::get_table_name(Instafetcher_Database::MENTIONS_TABLE);

		$wpdb->delete(
			$imagesTable,
			array(
				'image_id' => $imageId
			)
		);

		$wpdb->delete(
			$tagsTable,
			array(
				'image_id' => $imageId
			)
		);

		$wpdb->delete(
			$mentionsTable,
			array(
				'image_id' => $imageId
			)
		);

	}

}