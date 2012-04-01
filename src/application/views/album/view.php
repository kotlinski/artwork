<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:46
 * To change this template use File | Settings | File Templates.
 */?>

    <div class="aboutText">
        <div class="aboutHeader"><?php echo $album_item['title'] ?></div>
        <!--<?php echo $album_item['year'] ?>-->
    </div><br />
	<div style="visibility: hidden">
		<?if(true/*$cover_image*/) { ?>
			<!--<a class="picture startUpPicture"
			   id="cover"
			   rel="group2"
			   href="<?//=base_url('statics/img/upload/'.$cover_image['file_name'])?>"
			   title="<center><?//=$cover_image['title']?></center>">
				<img class="newsImg" src="<?//=base_url('statics/img/upload/'.$cover_image['file_name'])?>" /></a-->
		<?}?>

		<table style="width:370px;border: 0px solid #ff0f0f;padding:0px;margin:0px;">
		<?
		echo '<tr>';
		foreach ($images as $counter=>$image) {?>
			<? if($counter%3 == 0) {
				echo '</tr><tr>';
			}?>
			<td valign="middle" align="center" style="padding:0px;margin:0px;">
				<a class="picture startUpPicture"
				id="pic"
				rel="group2"
				href="<?=base_url('statics/img/upload/'.$image['file_name'])?>"
				title="<?=$image['title']?>"
				style="padding:0px;margin:0px;">
				<img
					src="<?=base_url('statics/img/upload/thumb/'.$image['file_name'])?>"
					style="padding:0px;margin:0px;"/></a>
			</td>

		<?}?>
		</table>
	</div>
</div>
