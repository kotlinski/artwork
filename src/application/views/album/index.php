<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:43
 * To change this template use File | Settings | File Templates.
 */?>

	<div class="aboutText">

		<?if($this->session->userdata('logged_in')) {?>
	<br />
	<a href="<?=base_url('album/create_album');?>">Create a new album</a>
	<a name="-1"></a>

	<br /><br />
	<hr />
	<?}?>

<?$prev_id = 0?>
<?//$mode = current($cover_images);?>
<?php foreach ($album as $key=>$album_item): ?>
	<? //$mode = $cover_images[$key];?>
	<?if($album_item['show']==1 || $this->session->userdata('logged_in')){?>
		<br/>
		<div onclick="location.href='<?=base_url("album/listView/". $album_item['id'] )?>';" style="cursor: pointer;">

			<?
			if($album_item['show']==0 && $this->session->userdata('logged_in')){
				echo '<div class="aboutHeader greyText">';
			} else {
				echo '<div class="aboutHeader">';
			}?>
			<?php echo '<p style="text-decoration:underline ">'.$album_item['title'].'</p>' ?></div>
		<!--
		<? if($album_item['show']==0 && $this->session->userdata('logged_in')){
			echo '<p class="greyText">';
		} else {
			echo '<p>';
		}?>
		<?php echo $album_item['year'].'<br />'/*.$album_item['text']*/ ?>
		</p>
		<br />-->

		<? if(isset($album_item['filename'])) { ?>

			<!--a class="picture"
			   id="admin_picture_<?=$album_item['image_id']?>"
			   rel="group2"
			   href="<?=base_url('statics/img/upload/'.$album_item['filename'])?>"
			   title="<?=$album_item['title']?>">
				<img class="newsImg" src="<?=base_url('statics/img/upload/'.$album_item['filename'])?>"> </img>
			</a-->
			<? } ?>

			</div>
			<a name="<?=$prev_id?>"></a>

	<?if($this->session->userdata('logged_in')){?>
		<br /><br />
		<a href="<?=base_url('album/edit_album/'.$album_item['id'])?>">
			Redigera album</a>
		<br />

		<?if($album_item['show']==1){?>
			<p>This album is visible.
				<a href="<?=site_url('/album/hide/')?>/<?echo $album_item['id'].'/#'.($prev_id-1);?>">Click here to hide this album</a> </p>
			<?}else{?>
			<p>This album is hidden.
				<a href="<?=site_url('/album/show/')?>/<?echo $album_item['id'].'/#'.($prev_id-1);?>">Click here to make this visible</a></p>
			<?}?>
		<?}?>


	<br />
	<hr/>
	<?$prev_id +=1;
		//$mode = next($cover_images);    // $mode = 'bike';?>
	<?}?>
<?php

	?>
<?php endforeach ?>
</div>
<br /> <br /> <br /><br /> <br /> <br />
</div>