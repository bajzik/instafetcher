<?php

/**
 * Class Instafetcher_Database
 *
 * @since 1.0.0
 */
class Instafetcher_Database {

	const DB_VERSION = 8;
	const DB_VERSION_OPTION_NAME = 'jbif_db_version';
	const DB_TABLE_PREFIX = 'jbif_';

	const IMAGES_TABLE = 'images';
	const TAGS_TABLE = 'tags';
	const USERS_TABLE = 'users';
	const MENTIONS_TABLE = 'mentions';

	public static function init() {

		add_action('plugins_loaded', array(__CLASS__, 'update_db_check'));
	}

	public static function update_db_check() {

		if (get_site_option(self::DB_VERSION_OPTION_NAME) != self::DB_VERSION) {
			self::install();
		}
	}

	public static function get_table_name($suffix) {

		global $wpdb;
		return $wpdb->prefix . self::DB_TABLE_PREFIX . $suffix;
	}

	public static function install() {

		global $wpdb;

		$installed_ver = get_option(self::DB_VERSION_OPTION_NAME, 0);
		if ($installed_ver != self::DB_VERSION) {

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			update_option(self::DB_VERSION_OPTION_NAME, self::DB_VERSION);

			$installed_ver = intval($installed_ver);

			if ($installed_ver == 0) {

				global $wpdb;

				$imagesTable = self::get_table_name(self::IMAGES_TABLE);

				$sql = "CREATE TABLE {$imagesTable} ("
					. " image_id BIGINT(20) NOT NULL AUTO_INCREMENT, "
					. " in_image_id BIGINT(20) NOT NULL, "
					. " owner_id BIGINT(20) NOT NULL, "
					. " image_name VARCHAR(255) NOT NULL, "
					. " full_text VARCHAR(255) NULL, "
					. " parsed_time TIMESTAMP NOT NULL, "
					. " shot_time TIMESTAMP NOT NULL, "
					. " PRIMARY KEY  (image_id) "
					. ") DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

				$wpdb->query($sql);

				$installed_ver = 1;

			}

			if ($installed_ver == 1) {

				global $wpdb;

				$tagsTable = self::get_table_name(self::TAGS_TABLE);

				$sql = "CREATE TABLE {$tagsTable} ("
					. " image_id BIGINT(20) NOT NULL, "
					. " tag VARCHAR(255) NOT NULL, "
					. " UNIQUE KEY  (image_id, tag) "
					. ") DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

				$wpdb->query($sql);

				$installed_ver = 2;

			}

			if ($installed_ver == 2) {

				global $wpdb;

				$usersTable = self::get_table_name(self::USERS_TABLE);

				$sql = "CREATE TABLE {$usersTable} ("
					. " user_id BIGINT(20) NOT NULL, "
					. " user_name VARCHAR(255) NULL, "
					. " added_time TIMESTAMP NOT NULL, "
					. " PRIMARY KEY (user_id) "
					. ") DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

				$wpdb->query($sql);

				$installed_ver = 3;

			}

			if ($installed_ver == 3) {

				global $wpdb;

				$mentionsTable = self::get_table_name(self::MENTIONS_TABLE);

				$sql = "CREATE TABLE {$mentionsTable} ("
					. " image_id BIGINT(20) NOT NULL, "
					. " mention_user_id BIGINT(20) NULL DEFAULT 0, "
					. " mention_user_name VARCHAR(255) NOT NULL, "
					. " added_time TIMESTAMP NOT NULL, "
					. " last_pairing_time TIMESTAMP NULL DEFAULT 0, "
					. " UNIQUE KEY (image_id, mention_user_name) "
					. ") DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

				$wpdb->query($sql);

				$installed_ver = 4;

			}

			if ($installed_ver == 4) {


				$tableName = self::get_table_name(self::USERS_TABLE);
				$wpdb->query("ALTER TABLE {$tableName} ADD COLUMN bio VARCHAR(1024) NULL DEFAULT NULL;");
				$wpdb->query("ALTER TABLE {$tableName} ADD COLUMN link VARCHAR(1024) NULL DEFAULT NULL;");

				$installed_ver = 5;

			}

			if ($installed_ver == 5) {


				$tableName = self::get_table_name(self::USERS_TABLE);
				$wpdb->query("ALTER TABLE {$tableName} ADD COLUMN fullname VARCHAR(1024) NULL DEFAULT NULL;");
				$wpdb->query("ALTER TABLE {$tableName} ADD COLUMN followers_count INT(255) NULL DEFAULT 0;");
				$wpdb->query("ALTER TABLE {$tableName} ADD COLUMN following_count INT(255) NULL DEFAULT 0;");


				$installed_ver = 6;

			}

			if ($installed_ver == 6) {


				$tableName = self::get_table_name(self::IMAGES_TABLE);
				$wpdb->query("ALTER TABLE {$tableName} ADD COLUMN thumbnail VARCHAR(1024) NULL DEFAULT NULL;");


				$installed_ver = 7;

			}

			if ($installed_ver == 7) {


				$tableName = self::get_table_name(self::IMAGES_TABLE);
				$wpdb->query("ALTER TABLE {$tableName} ADD COLUMN content_check_time TIMESTAMP NULL DEFAULT 0;");


				$installed_ver = 8;

			}
		}
	}

	public static function install_data() {

	}
}