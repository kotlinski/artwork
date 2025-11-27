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
    </div><br />

	<table class="tableview">
	<?
	foreach ($images as $counter=>$image) {?>
		<tr>
		<td valign="middle" style="padding:4px 0px;margin:0px;">
			<?=$counter+1?>.
			<a href="<?=base_url('album/'.$album_item['title'].'/'.$image['id'])?>"
			style="padding:0px;margin:0px 0px;">
			<?=$image['title']?>
			</a>
		</td>
		</tr>

	<?}?>

	</table>
</div>
