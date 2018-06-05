<?php

class Instafetcher_Model_Users {

	public static function checkIfUserExistsInDatabase($userId = 0) {

		$userId = intval($userId);

		if ($userId === 0) {

			return FALSE;
		}

		global $wpdb;

		$usersTable = Instafetcher_Database::get_table_name(Instafetcher_Database::USERS_TABLE);

		$preparedQuery = "SELECT user_id FROM {$usersTable} "
			. " WHERE user_id = %d ";

		$query = $wpdb->prepare($preparedQuery, $userId);

		$result = $wpdb->get_var($query);

		return (intval($result) > 0) ? $result : FALSE;
	}

	public static function insertUser($userArgs = array()) {

		if (empty($userArgs)) {
			return FALSE;
		}

		global $wpdb;

		$usersTable = Instafetcher_Database::get_table_name(Instafetcher_Database::USERS_TABLE);

		$userArgs['added_time'] = current_time('mysql');

		$wpdb->insert(
			$usersTable,
			$userArgs
		);

		return $wpdb->insert_id;

	}

	public static function updateUser($userArgs = array(), $userId = 0) {

		$userId = intval($userId);

		if (empty($userArgs) || $userId === 0) {
			return FALSE;
		}

		global $wpdb;

		$usersTable = Instafetcher_Database::get_table_name(Instafetcher_Database::USERS_TABLE);

		foreach ($userArgs as $userArgKey => $userArgValue) {

			$userArgs[$userArgKey] = Emoji::Encode($userArgValue);

		}

		$wpdb->update(
			$usersTable,
			$userArgs,
			array(
				'user_id' => $userId
			)
		);

	}

	public static function getUserNameByUserId($userId = 0) {

		$userId = intval($userId);

		if ($userId === 0) {

			return FALSE;
		}

		global $wpdb;

		$usersTable = Instafetcher_Database::get_table_name(Instafetcher_Database::USERS_TABLE);

		$preparedQuery = "SELECT user_name FROM {$usersTable} "
			. " WHERE user_id = %d ";

		$query = $wpdb->prepare($preparedQuery, $userId);

		$result = $wpdb->get_var($query);

		return $result;
	}

	public static function getUserIdByUsername($userName = '') {


		if (empty($userName)) {

			return FALSE;
		}

		global $wpdb;

		$usersTable = Instafetcher_Database::get_table_name(Instafetcher_Database::USERS_TABLE);

		$preparedQuery = "SELECT user_id FROM {$usersTable} "
			. " WHERE user_name= %s ";

		$query = $wpdb->prepare($preparedQuery, $userName);

		$result = $wpdb->get_var($query);

		return intval($result);

	}

	public static function getUsersToFetch() {

		global $wpdb;

		$usersTable = Instafetcher_Database::get_table_name(Instafetcher_Database::USERS_TABLE);

		$query = "SELECT * FROM {$usersTable} "
			. " WHERE user_name IS NULL ORDER BY added_time DESC LIMIT 100";

		$result = $wpdb->get_results($query, ARRAY_A);

		return $result;

	}

	public static function getUserDataByUserId($userId = 0) {

		$userId = intval($userId);

		if ($userId === 0) {

			return FALSE;
		}

		global $wpdb;

		$usersTable = Instafetcher_Database::get_table_name(Instafetcher_Database::USERS_TABLE);

		$preparedQuery = "SELECT * FROM {$usersTable} "
			. " WHERE user_id = %d ";

		$query = $wpdb->prepare($preparedQuery, $userId);

		$result = $wpdb->get_row($query, ARRAY_A);

		return $result;
	}

}