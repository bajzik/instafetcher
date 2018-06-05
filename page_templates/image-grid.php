<?php

$imageUrl = $image['thumbnail'];

if ($imageUrl === NULL || empty($imageUrl)) {
	$imageUrl = $image['image_name'];
}

$imageArgs = array(
	'count_only' => 'yes',
	'user' => $image['owner_id']
);

?>

<?php $userPhotosCount = Instafetcher_Model_Images::getImages($imageArgs); ?>
<?php $shotTime = $image['shot_time']; ?>
<?php $photoShotTime = date('d.m.Y H:i', strtotime($shotTime)); ?>
<?php $userName = $image['user_name']; ?>
<?php $userUrl = Instafetcher_Users::getUserDetailUrl($image['owner_id']); ?>
<?php $tags = $image['tags']; ?>
<?php $mentions = $image['mentions']; ?>
<?php $detailUrl = Instafetcher_Images::getImageDetailUrl($image['image_id']); ?>
	<div class="jbif__container--grid-image" id="<?php echo $image['image_id']; ?>" style="background-image:url('<?php echo $imageUrl; ?>');">
		<div class="jbif__container--grid-image-hover">
			<h2><?php echo $userName; ?></h2>
			<div class="jbif__container--grid-image-time">
				<?php echo $photoShotTime; ?>
			</div>
			<div class="jbif__container--grid-image-text">
				<?php echo Instafetcher_Images::formatFullText($image['full_text']); ?>
			</div>
			<div class="jbif__container--grid-image-profile">
				<a href="<?php echo $userUrl; ?>" class="jbif__button jbif__button-white">
					<?php _e('Show Profile', 'jb-instafetcher'); ?>
					<span class="jbif__badge">
									<?php echo $userPhotosCount; ?>
								</span>
				</a>
			</div>
			<div class="jbif__container--grid-image-icons">
<span class="jbif__container--grid-image-detail">
						<a href="<?php echo $detailUrl; ?>"><i class="fa fa-camera"></i></a>
						</span>
				<span class="jbif__container--grid-image-download">
						<a download href="<?php echo $imageUrl; ?>"><i class="fa fa-download"></i></a>
						</span>
				<span class="jbif__container--grid-image-tags">
							<i class="fa fa-tags"></i>
					<?php echo $tags; ?>
						</span>
				<span class="jbif__container--grid-image-mentions">
							<i class="fa fa-users"></i>
					<?php echo $mentions; ?>
						</span>
			</div>
		</div>
	</div>