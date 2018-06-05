<?php

/**
 * Class Instafetcher_Core
 *
 * @since 1.0.0
 */
class Instafetcher_Core {

	/**
	 * Class initialization
	 */
	public static function init() {

		Instafetcher_ACFD::init();
		Instafetcher_Cron::init();
		Instafetcher_Database::init();
		Instafetcher_Shortcodes::init();
		Instafetcher_REST::init();
		Instafetcher_Rewrites::init();

		add_action('after_setup_theme', array(__CLASS__, 'initTranslations'));

	}

	public static function getPageDetailUrl() {

		$pageDetailId = Instafetcher_ACFD::getAcfOption(Instafetcher_ACFD::PHOTO_DETAIL_PAGE_ID);

		return get_permalink($pageDetailId);

	}

	public static function getUserDetailUrl() {

		$pageDetailId = Instafetcher_ACFD::getAcfOption(Instafetcher_ACFD::USER_PROFILE_PAGE_ID);

		return get_permalink($pageDetailId);

	}

	/**
	 * Init i18n
	 */
	public static function initTranslations() {

		load_plugin_textdomain('jb-instafetcher', FALSE, dirname(plugin_basename(JB_INSTAFETCHER_INDEX)) . DIRECTORY_SEPARATOR . 'i18n');
	}

}