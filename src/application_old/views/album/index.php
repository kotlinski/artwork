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

	<br />
	<table class="tableview">
		<?
		$titles ="";
		echo '<tr>';
		foreach ($images as $counter=>$image) {?>
			<? if($counter%3 == 0) {
				echo '</tr><tr>';
			}?>
			<td valign="middle" align="center" style="padding-bottom:10px;margin:0px;">
				<a class="picture"
				   rel="group2"
				   href="<?=base_url('statics/img/upload/'.$image->file_name)?>"
				   title="<?=$image->title?>"
				   style="padding:0px;margin:0px;border:0px;">
					<img
						src="<?=base_url('statics/img/upload/thumb/'.$image->file_name)?>"
						style="padding:0px;margin:0px;border:0px;"/>
				</a>
			</td>

			<?
			$titles .=
				'<div>'.
					'<span style="float:left;max-width:80%;">'.
						htmlspecialchars($image->title, ENT_QUOTES).'<br />'.
						'<span style="text-align:left;color:#777;font: normal 13px "Helvetica Neue",Helvetica,Arial,sans-serif;">Copyright © Anne Hamrin Simonsson '/*.date("Y")*/.'</span>'.

					'</span>'.
					'<span style="float:right;" title="close">'.'<a href="#" OnClick="closeButton(event);">close</a>'.'</span>'.
					'<br style="clear:both;" />'.
				'</div>';

?>

		<?}?>
	</table>
	<div id="fancyboxTitles" style="display: none;">
		<?echo $titles?>
	</div>


	<br /> <br /> <br /><br /> <br /> <br />
</div>