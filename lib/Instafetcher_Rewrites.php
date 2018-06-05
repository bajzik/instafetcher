<?php

class Instafetcher_Rewrites {

	public static function init() {

		add_action('init', array(__CLASS__, 'mapCustomUrls'));

	}

	public static function mapCustomUrls() {

		$profilePageId = Instafetcher_ACFD::getAcfOption(Instafetcher_ACFD::USER_PROFILE_PAGE_ID);
		$profilePageSlug = get_post_field('post_name', get_post($profilePageId));

		$photoPageId = Instafetcher_ACFD::getAcfOption(Instafetcher_ACFD::PHOTO_DETAIL_PAGE_ID);
		$photoPageSlug = get_post_field('post_name', get_post($photoPageId));

		add_rewrite_tag('%user%', '([^/]+)');
		add_rewrite_tag('%photo%', '([^/]+)');

		add_rewrite_rule('^' . $profilePageSlug . '/([0-9]+)/?', 'index.php?page_id=' . $profilePageId . '&user=$matches[1]', 'top');
		add_rewrite_rule('^' . $photoPageSlug . '/([0-9]+)/?', 'index.php?page_id=' . $photoPageId . '&photo=$matches[1]', 'top');

	}

}