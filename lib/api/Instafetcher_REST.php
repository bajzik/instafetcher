<?php

/**
 * Class Instafetcher_REST
 *
 * @since 1.0.0
 */
class Instafetcher_REST {

	const BASE_PATH = 'jb-instafetcher/v1';

	const GET_IMAGES_ROUTE = 'get-images';
	const GET_USERDATA_ROUTE = 'get-userdata';

	/**
	 * Class initialization
	 */
	public static function init() {

		add_action('rest_api_init', array(__CLASS__, 'initRestApi'));

	}

	public static function initRestApi() {

		register_rest_route(self::BASE_PATH, '/' . self::GET_IMAGES_ROUTE, array(
			'methods' => 'POST',
			'callback' => array(__CLASS__, 'getImages')
		));

		register_rest_route(self::BASE_PATH, '/' . self::GET_USERDATA_ROUTE, array(
			'methods' => 'POST',
			'callback' => array(__CLASS__, 'getUserdata')
		));
	}

	public static function getImages(WP_REST_Request $request) {

		$params = $request->get_params();

		$currentPage = $params['current_page'];
		$perPage = $params['per_page'];
		$totalPages = $params['total_pages'];
		$userId = $params['user_id'];
		$tag = $params['tag'];

		$hideLoadMore = FALSE;

		if (intval($currentPage + 1) === intval($totalPages)) {
			$hideLoadMore = TRUE;
		}

		$queryArgs = array(
			'limit' => $perPage,
			'offset' => $currentPage * $perPage
		);

		if (intval($userId) > 0) {
			$queryArgs['user'] = intval($userId);
		}

		if (!empty($tag)) {
			$queryArgs['tag'] = $tag;
		}

		$images = Instafetcher_Model_Images::getImages($queryArgs);

		$html = '';

		foreach ($images as $image) {
			ob_start();
			include(JB_INSTAFETCHER_PATH . 'page_templates/image-grid.php');
			$html .= ob_get_clean();
		}

		return new WP_REST_Response(array(
			'success' => TRUE,
			'html' => $html,
			'current_page' => $currentPage + 1,
			'hide_load_more' => $hideLoadMore
		), 200);

	}

	public static function getUserdata(WP_REST_Request $request) {

		$params = $request->get_params();

		$userId = $params['user_id'];

		$userData = Instafetcher_Users::getUserDataFromIdFromExternalAPI($userId);

		if (!empty($userData)) {

			$userArgs = array(
				'user_name' => $userData['username'],
				'fullname' => $userData['full_name'],
				'bio' => $userData['bio'],
				'followers_count' => $userData['followers_count'],
				'following_count' => $userData['following_count'],
				'link' => $userData['external|-url']
			);

			Instafetcher_Model_Users::updateUser($userArgs, $userId);

			return new WP_REST_Response(array(
				'success' => TRUE,
				'message' => __('User data loaded, page will be reloaded after 3 seconds', 'jb-instafetcher')
			), 200);

		} else {
			return new WP_REST_Response(array(
				'success' => FALSE,
				'message' => __('Unable to fetch data at this moment', 'jb-instafetcher')
			), 200);
		}

	}

}