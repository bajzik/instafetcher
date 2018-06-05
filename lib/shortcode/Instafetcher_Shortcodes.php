<?php

/**
 * Class Instafetcher_Shortcodes
 *
 * @since 1.0.0
 */
class Instafetcher_Shortcodes {

	const SHOW_IMAGES = 'jbif_show_images';
	const SHOW_IMAGES_GRID = 'jbif_show_images_grid';
	const SHOW_IMAGE_DETAIL = 'jbif_show_image_detail';
	const SHOW_PROFILE = 'jbif_show_profile';
	const PER_PAGE_PHOTOS = 52;

	public static function init() {

		add_shortcode(self::SHOW_IMAGES, array(__CLASS__, 'showImages'));
		add_shortcode(self::SHOW_IMAGES_GRID, array(__CLASS__, 'showImagesGrid'));
		add_shortcode(self::SHOW_IMAGE_DETAIL, array(__CLASS__, 'showImageDetail'));
		add_shortcode(self::SHOW_PROFILE, array(__CLASS__, 'showProfile'));

		add_action('wp_footer', array(__CLASS__, 'wpFooter'));

	}

	public static function showImagesGrid($shortcodeAtts = array()) {

		$shortcodeAtts = shortcode_atts(array(
			'tag' => '',
			'user' => 0
		), $shortcodeAtts, self::SHOW_IMAGES);

		if ($shortcodeAtts['tag'] !== 'tag') {
			$queryArgs['tag'] = $shortcodeAtts['tag'];
		}

		if (intval($shortcodeAtts['user']) > 0) {
			$queryArgs['user'] = intval($shortcodeAtts['user']);
		}

		$queryArgs['limit'] = self::PER_PAGE_PHOTOS;

		$images = Instafetcher_Model_Images::getImages($queryArgs);

		$queryArgs['count_only'] = 'yes';

		$countImages = Instafetcher_Model_Images::getImages($queryArgs);

		$totalPages = ceil($countImages / self::PER_PAGE_PHOTOS);

		?>
		<div class="jbif__content">
			<div class="row jbif__container--grid">
				<form name="load-more-helper">
					<input type="hidden" name="per_page" id="per_page" value="<?php echo self::PER_PAGE_PHOTOS; ?>">
					<input type="hidden" name="current_page" id="current_page" value="1">
					<input type="hidden" name="total_pages" id="total_pages" value="<?php echo $totalPages; ?>">
					<input type="hidden" name="user_id" id="user_id" value="<?php echo $shortcodeAtts['user']; ?>">
					<input type="hidden" name="tag" id="tag" value="<?php echo $shortcodeAtts['tag']; ?>">
				</form>
				<?php foreach ($images as $image): ?>
					<?php include(JB_INSTAFETCHER_PATH . 'page_templates/image-grid.php'); ?>
				<?php endforeach; ?>
			</div>
			<?php if ($totalPages > 1): ?>
				<div class="row jbif__loadmore--results">

				</div>
				<div class="col-xs-12 jbif__loadmore--wrapper">
					<a class="jbif__flatbutton load-more-trigger">
						<?php _e('More photos', 'jb-instafetcher'); ?>
						<div class="spinner">
							<div class="sk-folding-cube">
								<div class="sk-cube1 sk-cube"></div>
								<div class="sk-cube2 sk-cube"></div>
								<div class="sk-cube4 sk-cube"></div>
								<div class="sk-cube3 sk-cube"></div>
							</div>
						</div>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<?php

	}

	public static function showImages($shortcodeAtts = array()) {

		$shortcodeAtts = shortcode_atts(array(
			'tag' => '',
			'limit' => 0
		), $shortcodeAtts, self::SHOW_IMAGES);

		$queryArgs = array(
			'limit' => intval($shortcodeAtts['limit'])
		);

		if ($shortcodeAtts['tag'] !== 'tag') {
			$queryArgs['tag'] = $shortcodeAtts['tag'];
		}

		$images = Instafetcher_Model_Images::getImages($queryArgs);
		$content = '<div class="jbif__container">';

		foreach ($images as $image) {

			$dir = Instafetcher_Parser::getSaveDirectoryUrl() . DIRECTORY_SEPARATOR . $image['owner_id'] . DIRECTORY_SEPARATOR . $image['image_name'];
			$tags = Instafetcher_Model_Images::getTagsByImageId($image['image_id']);
			$userName = Instafetcher_Model_Users::getUserNameByUserId($image['owner_id']);

			$content .= '<div class="jbif__container--image" id="' . $image['image_id'] . '">';
			$content .= '<div class="jbif__container--image-title">';
			$content .= $userName;
			$content .= '</div>';
			$content .= '<img src="' . $dir . '">';
			$content .= '<div class="jbif__container--image-text">';
			$content .= str_replace('\n', '<br>', Emoji::Decode($image['full_text']));
			$content .= '</div>';
			$content .= '<div class="jbif__container--image-tags">';

			if (!empty($tags)) {
				foreach ($tags as $tag) {
					$content .= '<div class="jbif__container--image-tags-tag">';
					$content .= '#' . $tag['tag'];
					$content .= '</div>';
				}
			}
			$content .= '</div>';
			$content .= '</div>';
		}

		$content .= '</div>';

		return $content;

	}

	public static function showImageDetail() {

		$photoId = get_query_var('photo');

		$photoDetail = Instafetcher_Model_Images::getImage($photoId);

		$tags = Instafetcher_Model_Images::getTagsByImageId($photoId);
		$mentions = Instafetcher_Model_Images::getMentionsByImageId($photoId);
		$imageName = $photoDetail['image_name'];
		$imageAuthorName = $photoDetail['user_name'];

		$image = $imageName;

		?>
		<div class="jbif__container--detail jbif__content row">
			<div class="jbif__container--detail-image col-xs-12">
				<img src="<?php echo $image; ?>">
			</div>
			<?php if (!empty($mentions)): ?>
				<div class="jbif__container--detail-mentions col-xs-12">
					<div><?php _e('Mentioned in this photo', 'jb-instafetcher'); ?> (<?php echo count($mentions); ?>)</div>
					<?php foreach ($mentions as $mention) : ?>
						<span class="jbif__container--detail-mention">
							<?php $userExists = Instafetcher_Model_Users::checkIfUserExistsInDatabase($mention['mention_user_id']); ?>
							<?php if (intval($userExists) > 0) : ?>
								<a href="<?php echo Instafetcher_Users::getUserDetailUrl($userExists); ?>">@<?php echo $mention['mention_user_name']; ?></a>
							<?php else : ?>
								@<?php echo $mention['mention_user_name']; ?>
							<?php endif; ?>
					</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="jbif__container--detail-text col-xs-6">
				<?php if (!empty($photoDetail['full_text'])): ?>
					<h2><?php echo Instafetcher_Images::formatFullText($photoDetail['full_text']); ?></h2>
				<?php else: ?>
					<?php _e('This photo doesn\'nt have any description', 'jb-instafetcher'); ?>
				<?php endif; ?>
			</div>
			<div class="jbif__container--detail-tags col-xs-6">
				<?php if (!empty($tags)): ?>
					<?php foreach ($tags as $tag): ?>
						<span class="jbif__container--detail-tag">
					#<?php echo $tag['tag']; ?>
				</span>
					<?php endforeach; ?>
				<?php else : ?>
					<?php _e('This photo doesn\'nt have any tags', 'jb-instafetcher'); ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
		$userId = $photoDetail['owner_id'];
		include(JB_INSTAFETCHER_PATH . 'page_templates/user-profile.php');

	}

	public static function showProfile() {

		$userId = get_query_var('user');
		include(JB_INSTAFETCHER_PATH . 'page_templates/user-profile.php');

	}

	public static function wpFooter() {

		wp_enqueue_script(
			'freewall',
			plugins_url('/js/freewall.js', JB_INSTAFETCHER_INDEX),
			array('jquery'),
			'1.0.10',
			TRUE
		);

		wp_enqueue_script(
			'app',
			plugins_url('/js/app.js', JB_INSTAFETCHER_INDEX),
			array('freewall'),
			'1.0.2',
			TRUE
		);

		wp_localize_script('app', 'JBIF', array(
			'rest_url' => rest_url(),
			'get_images_route' => Instafetcher_REST::BASE_PATH . '/' . Instafetcher_REST::GET_IMAGES_ROUTE,
			'get_userdata_route' => Instafetcher_REST::BASE_PATH . '/' . Instafetcher_REST::GET_USERDATA_ROUTE
		));

		?>
		<script>
					jQuery.noConflict();
					(function($) {
						$(function() {
							$(document).ready(function() {
								jQuery('head').append(' <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('/css/frontend.min.css?v=1.0.8', JB_INSTAFETCHER_INDEX); ?>" > ');
								jQuery('head').append(' <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('/css/flexboxgrid.min.css?v=1.0.2', JB_INSTAFETCHER_INDEX); ?>" > ');
								jQuery('head').append(' <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('/css/fontawesome-all.min.css?v=1.0.2', JB_INSTAFETCHER_INDEX); ?>" > ');
							});
						});
					})(jQuery);
		</script>
		<?php

	}

}