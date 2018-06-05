<?php

/**
 * Class Instafetcher_Cron
 *
 * @since 1.0.0
 */
class Instafetcher_Cron {

	const RUN_PARSER_JOB = 'jbif_run_parser';
	const RUN_NAME_JOB = 'jbif_fetch_names';
	const RUN_MENTIONS_JOB = 'jbif_pair_mentions';
	const RUN_CONTENT_CHECKER_JOB = 'jbif_content_check';

	public static $parsingStats = array();

	/**
	 * Class initialization
	 */
	public static function init() {

		add_action(self::RUN_PARSER_JOB, array(__CLASS__, 'runParser'));
		add_action(self::RUN_NAME_JOB, array(__CLASS__, 'runNamesJob'));
		add_action(self::RUN_MENTIONS_JOB, array(__CLASS__, 'runMentionsJob'));
		add_action(self::RUN_CONTENT_CHECKER_JOB, array(__CLASS__, 'runContentCheckJob'));

		add_filter('cron_schedules', array(__CLASS__, 'addCustomCronSchedulesTime'));

	}

	public static function addCustomCronSchedulesTime($schedules) {

		$schedules['jbif-30mins'] = array(
			'interval' => 1800,
			'display' => __('Every 30 mins', 'jb-instafetcher')
		);

		$schedules['jbif-15mins'] = array(
			'interval' => 900,
			'display' => __('Every 15 mins', 'jb-instafetcher')
		);

		return $schedules;

	}

	public static function registerCronJobs() {

		if (!wp_next_scheduled(self::RUN_PARSER_JOB)) {
			wp_schedule_event(time(), 'jbif-30mins', self::RUN_PARSER_JOB);
		}

		if (!wp_next_scheduled(self::RUN_NAME_JOB)) {
			wp_schedule_event(time(), 'jbif-15mins', self::RUN_NAME_JOB);
		}

		if (!wp_next_scheduled(self::RUN_MENTIONS_JOB)) {
			wp_schedule_event(time(), 'jbif-15mins', self::RUN_MENTIONS_JOB);
		}

		if (!wp_next_scheduled(self::RUN_CONTENT_CHECKER_JOB)) {
			wp_schedule_event(time(), 'jbif-30mins', self::RUN_CONTENT_CHECKER_JOB);
		}

	}

	public static function removeCronJobs() {

		wp_clear_scheduled_hook(self::RUN_PARSER_JOB);
		wp_clear_scheduled_hook(self::RUN_NAME_JOB);
		wp_clear_scheduled_hook(self::RUN_MENTIONS_JOB);
		wp_clear_scheduled_hook(self::RUN_CONTENT_CHECKER_JOB);

	}

	public static function runParser() {

		self::$parsingStats = array();

		if (have_rows(Instafetcher_ACFD::TAGS_FIELD, 'option')) {
			while (have_rows(Instafetcher_ACFD::TAGS_FIELD, 'option')) {
				the_row();

				$parser = new Instafetcher_Parser(get_sub_field(Instafetcher_ACFD::TAG_FIELD));
				$parser->startParse();
			}
		}

		$headers = array('Content-Type: text/html; charset=UTF-8');

		ob_start();

		$stats = self::$parsingStats;

		$totalParsed = 0;

		if (!empty($stats)) {

			foreach ($stats as $tag => $count) {

				$totalParsed += $count;
				echo $tag . ' [' . $count . ']<br>';

			}

			echo 'TOTAL ' . $totalParsed;

		} else {

			echo 'No parsed data !';
		}

		wp_mail(get_option('admin_email'), 'Parsing complete', ob_get_clean(), $headers);

	}

	public static function runNamesJob() {

		$names = Instafetcher_Model_Users::getUsersToFetch();

		$fetchedNames = 0;

		if (!empty($names)) {

			foreach ($names as $name) {

				$nameId = $name['user_id'];

				$userData = Instafetcher_Users::getUserDataFromIdFromExternalAPI($nameId);
				$userName = $userData['username'];

				if (!empty($userData)) {

					$userArgs = array(
						'user_name' => $userName
					);

					Instafetcher_Model_Users::updateUser($userArgs, $nameId);

					$fetchedNames++;

				}

			}

		}

		$headers = array('Content-Type: text/html; charset=UTF-8');

		ob_start();
		echo 'Number of fetched names : ' . $fetchedNames;
		wp_mail(get_option('admin_email'), 'Name Fetching Complete', ob_get_clean(), $headers);

	}

	public static function runMentionsJob() {

		$usersToPair = Instafetcher_Model_Mentions::getUsersToPair();

		$usersPaired = 0;

		foreach ($usersToPair as $userToPair) {

			$userNameToSearch = $userToPair['mention_user_name'];
			// $userId = Instafetcher_Model_Users::getUserIdByUsername($userNameToSearch);

			$userId = Instafetcher_Users::getUserIdFromUserName($userNameToSearch);

			if ($userId > 0) {

				$mentionArgs = array(
					'mention_user_id' => $userId,
					'last_pairing_time' => current_time('mysql')
				);

				Instafetcher_Model_Mentions::updateMention($mentionArgs, $userNameToSearch);

				$usersPaired++;

			} else {
				$mentionArgs = array(
					'last_pairing_time' => current_time('mysql')
				);

				Instafetcher_Model_Mentions::updateMention($mentionArgs, $userNameToSearch);
			}

		}

		$headers = array('Content-Type: text/html; charset=UTF-8');

		ob_start();
		echo 'Number of paired users : ' . $usersPaired;
		wp_mail(get_option('admin_email'), 'Users pairing complete', ob_get_clean(), $headers);
	}

	public static function runContentCheckJob() {

		$imagesToCheck = Instafetcher_Model_Images::getImagesToContentCheck();

		$deletedImages = 0;

		foreach ($imagesToCheck as $image) {

			$fileExists = Instafetcher_Images::checkRemoteFile($image['image_name']);

			if (!$fileExists) {
				Instafetcher_Model_Images::removeImage($image['image_id']);

				$deletedImages++;
			} else {

				$imageArgs = array(
					'content_check_time' => current_time('mysql')
				);

				Instafetcher_Model_Images::updateImage($imageArgs, $image['image_id']);

			}

		}

		$headers = array('Content-Type: text/html; charset=UTF-8');

		ob_start();
		echo 'Number of deleted images : ' . $deletedImages;
		wp_mail(get_option('admin_email'), 'Content check update', ob_get_clean(), $headers);

	}

}