<?php
$userData = Instafetcher_Model_Users::getUserDataByUserId($userId);

$userName = $userData['user_name'];

$userNameKnown = TRUE;

if (empty($userName)) {
	$userName = __('We\'re trying to fetch username', 'jb-instafetcher');
	$userNameKnown = FALSE;
}

$imagesArgs = array(
	'user' => $userId
);

$userImages = Instafetcher_Model_Images::getImages($imagesArgs);
$randomImage = array_rand($userImages);
$selectedUserImage = $userImages[$randomImage];

$imagesArgs['count_only'] = 'yes';

$userImagesCount = Instafetcher_Model_Images::getImages($imagesArgs);

$mostUsedTags = Instafetcher_Model_Images::getMostUsedTagsForUser($userId);

?>
<div class="jbif__content jbif__container--user-detail <?php echo (wp_is_mobile()) ? 'mobile' : ''; ?> row">
	<div class="jbif__container--user-detail-image col-xs-12 col-md-4" style="background-image:url('<?php echo $selectedUserImage['image_name']; ?>');"></div>
	<div class="jbif__container--user-detail-info col-xs-12 col-md-8">
		<div class="row">
			<div class="col-xs-12">
				<h1>
					<?php echo $userName; ?>
					<?php if ($userNameKnown) : ?>
						<a href="https://instagram.com/<?php echo $userName; ?>/" style="vertical-align:middle;" class="jbif__button jbif__button-white"><?php _e('IG Profile', 'jb-instafetcher'); ?></a>
					<?php else: ?>
						<form name="username-fetch-helper">
							<input type="hidden" name="user_id" id="user_id" value="<?php echo $userId; ?>">
						</form>
						<a style="vertical-align:middle;" class="jbif__button jbif__button-white username-fetch-trigger"><?php _e('Try to fetch username', 'jb-instafetcher'); ?></a>
						<div class="spinner">
							<div class="sk-folding-cube">
								<div class="sk-cube1 sk-cube"></div>
								<div class="sk-cube2 sk-cube"></div>
								<div class="sk-cube4 sk-cube"></div>
								<div class="sk-cube3 sk-cube"></div>
							</div>
						</div>
					<?php endif; ?>
				</h1>
			</div>
			<div class="col-xs-4 jbif__container--user-detail-info-key"><?php _e('Full name', 'jb-instafetcher'); ?></div>
			<div class="col-xs-8 jbif__container--user-detail-info-value"><?php echo $userData['fullname']; ?></div>
			<div class="col-xs-4 jbif__container--user-detail-info-key"><?php _e('Bio', 'jb-instafetcher'); ?></div>
			<div class="col-xs-8 jbif__container--user-detail-info-value"><?php echo Emoji::Decode($userData['bio']); ?></div>
			<div class="col-xs-4 jbif__container--user-detail-info-key"><?php _e('Link', 'jb-instafetcher'); ?></div>
			<div class="col-xs-8 jbif__container--user-detail-info-value">
				<a href="<?php echo $userData['link']; ?>"><?php echo $userData['link']; ?></a>
			</div>
			<div class="spacer"></div>
			<div class="col-xs-4 jbif__container--user-detail-info-key"><?php _e('Followers count', 'jb-instafetcher'); ?></div>
			<div class="col-xs-8 jbif__container--user-detail-info-value"><?php echo $userData['followers_count']; ?></div>
			<div class="col-xs-4 jbif__container--user-detail-info-key"><?php _e('Following count', 'jb-instafetcher'); ?></div>
			<div class="col-xs-8 jbif__container--user-detail-info-value"><?php echo $userData['following_count']; ?></div>
			<div class="spacer"></div>
			<div class="col-xs-4 jbif__container--user-detail-info-key"><?php _e('Total photos', 'jb-instafetcher'); ?></div>
			<div class="col-xs-8 jbif__container--user-detail-info-value"><?php echo $userImagesCount; ?></div>
			<div class="spacer"></div>
			<div class="col-xs-4 jbif__container--user-detail-info-key"><?php _e('Most used tags	', 'jb-instafetcher'); ?></div>
			<div class="col-xs-8 jbif__container--user-detail-info-value">
				<?php foreach ($mostUsedTags as $mostUsedTag): ?>
					<?php if (intval($mostUsedTag['count']) > 0) : ?>
						<div class="jbif__tag">
							#<?php echo $mostUsedTag['tag']; ?>
							<span class="jbif__tag-count">
									<?php echo $mostUsedTag['count']; ?>
								</span>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="jbif__container--user-detail-pre-images col-xs-12">
		<h2>
			<?php if ($userNameKnown): ?>
				<?php echo sprintf(__('All images from %s', 'jb-instafetcher'), $userName); ?>
			<?php else: ?>
				<?php _e('All images from this user', 'jb-instafetcher	'); ?>
			<?php endif; ?>
		</h2>
	</div>
	<div class="jbif__container--user-detail-images col-xs-12">
		<?php echo do_shortcode('[' . self::SHOW_IMAGES_GRID . ' limit=' . self::PER_PAGE_PHOTOS . ' user=' . $userId . ' ]'); ?>
	</div>
</div>