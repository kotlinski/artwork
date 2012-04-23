<?php
	/**
	 * Created by JetBrains PhpStorm.
	 * User: Simon
	 * Date: 2012-01-05
	 * Time: 14:43
	 * To change this template use File | Settings | File Templates.
	 */?>

<div class="aboutText">

	<a name="-1"></a>


<?$prev_id = 0?>
<?//$mode = current($cover_images);?>
	<div class="submenu menu">
		<ul>
			<?foreach( $submenu as $key=>$submenu_item){
			if($selected_filter == $submenu_item['id']){
				echo '<li><a style="margin-right: 60px" href="'.base_url('album/'.$submenu_item['id']).'" class="current">'.$submenu_item['name'].'</a></li>';
			} else {
				echo '<li><a style="margin-right: 60px" href="'.base_url('album/'.$submenu_item['id']).'">'.$submenu_item['name'].'</a></li>';
			}
		}?>
		</ul><br style="clear:left"/>
	</div>


	<table style="width:370px;border: 0px solid #ff0f0f;padding:0px;margin:0px;">
		<?
		echo '<tr>';
		foreach ($images as $counter=>$image) {?>
			<? if($counter%3 == 0) {
				echo '</tr><tr>';
			}?>
			<td valign="middle" align="center" style="padding:0px;margin:0px;">
				<a class="picture"
				   id="pic"
				   rel="group2"
				   href="<?=base_url('statics/img/upload/'.$image->file_name)?>"
				   title="<?=$image->title?>"
				   style="padding:0px;margin:0px;">
					<img
						src="<?=base_url('statics/img/upload/thumb/'.$image->file_name)?>"
						style="padding:0px;margin:0px;"/></a>
			</td>

			<?}?>
	</table>


	<br /> <br /> <br /><br /> <br /> <br />
</div>