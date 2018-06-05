<?php

class Instafetcher_Model_Mentions {

	public static function insertMention($mentionsArray = array()) {

		if (empty($mentionsArray)) {
			return FALSE;
		}

		global $wpdb;

		$mentionsTable = Instafetcher_Database::get_table_name(Instafetcher_Database::MENTIONS_TABLE);

		$mentionsArray['added_time'] = current_time('mysql');

		$wpdb->insert(
			$mentionsTable,
			$mentionsArray
		);

		return $wpdb->insert_id;

	}

	public static function updateMention($mentionArray = array(), $userName = 0) {

		if (empty($mentionArray) || $userName === 0) {
			return FALSE;
		}

		global $wpdb;

		$mentionsTable = Instafetcher_Database::get_table_name(Instafetcher_Database::MENTIONS_TABLE);

		$wpdb->update(
			$mentionsTable,
			$mentionArray,
			array(
				'mention_user_name' => $userName
			)
		);

	}

	public static function getUsersToPair() {

		global $wpdb;

		$mentionsTable = Instafetcher_Database::get_table_name(Instafetcher_Database::MENTIONS_TABLE);

		$query = "SELECT * FROM {$mentionsTable} "
			. " WHERE mention_user_id = 0 ORDER BY last_pairing_time DESC LIMIT 50";

		$result = $wpdb->get_results($query, ARRAY_A);

		return $result;

	}

}