<?php

/**
 * Class DS_GDPR_ACFD
 *
 * @since 1.0.0
 */
class Instafetcher_ACFD {

	public static $parserSettings;

	const TAGS_FIELD = 'jbif_tags';
	const TAG_FIELD = 'jbif_tag';
	const PHOTO_DETAIL_PAGE_ID = 'jbif_photo_detail_page_id';
	const USER_PROFILE_PAGE_ID = 'jbif_user_profile_page_id';

	/**
	 * Class initialization
	 */
	public static function init() {

		add_action('after_setup_theme', array(__CLASS__, 'afterPluginsLoaded'), 100);
		add_action('init', array(__CLASS__, 'createOptionsSubPage'));

	}

	/**
	 * Hook to launch after plugins are loaded
	 */
	public static function afterPluginsLoaded() {

		if (class_exists('ACFD') && ACFD::isActive()) {
			self::runAcfdScript();
		}
	}

	/**
	 * Run general ACFD Script
	 */
	public static function runAcfdScript() {

		$parserCoreSettings = self::$parserSettings = new CustomGroup(__('Parser settings', 'ds-gdpr-addon'), 'options_page == jb-instafetcher-settings');

		$tagsRepeater = $parserCoreSettings->addContainer(self::TAGS_FIELD, __('Tags to parse', 'jb-instafetcher'), 'repeater');
		$tagsRepeater->addField(self::TAG_FIELD, __('Tag', 'jb-instafetcher'), 'text');


		$parserCoreSettings->addField(self::PHOTO_DETAIL_PAGE_ID, __('Photo detail page', 'jb-instafetcher'), 'post_object')
			->set('post_type', array('page'))
			->set('taxonomy', array())
			->set('allow_null', 1)
			->set('multiple', 0)
			->set('return_format', 'id');

		$parserCoreSettings->addField(self::USER_PROFILE_PAGE_ID, __('User profile page', 'jb-instafetcher'), 'post_object')
			->set('post_type', array('page'))
			->set('taxonomy', array())
			->set('allow_null', 1)
			->set('multiple', 0)
			->set('return_format', 'id');
	}

	/**
	 * Create basic options page
	 */
	public static function createOptionsSubPage() {

		if (function_exists('acf_add_options_sub_page')) {

			acf_add_options_sub_page(array(
				'page_title' => __('Instafetcher Settings', 'jb-instafetcher'),
				'menu_title' => __('Instafetcher Settings', 'jb-instafetcher'),
				'parent_slug' => 'options-general.php',
				'menu_slug' => 'jb-instafetcher-settings'
			));

		}

	}

	/**
	 * Return ACF option value from database by field name
	 *
	 * @param string $optionName Option name
	 *
	 * @return string|array Option value from database
	 */
	public static function getAcfOption($optionName = '') {

		return get_option('options_' . $optionName);

	}

}
