<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:43
 * To change this template use File | Settings | File Templates.
 */?>

<div class="aboutText"  style="white-space:normal;">
	<div style="margin-top:1.25cm;">
        <a class="picture"
           data-fancybox="gallery"
           data-caption="Verk av konstnär Anne Hamrin Simonsson: <?=htmlspecialchars($image->title, ENT_QUOTES)?>"
           href="<?=base_url('konst/' . $image->file_name)?>"
           title="<?=$image->title?>">
            <img class="newsImg" src="<?= base_url('konst/medium/'.$image->file_name);?>"
                 alt="Verk av konstnär Anne Hamrin Simonsson: <?=htmlspecialchars($image->title, ENT_QUOTES)?>" />
        </a>
		<div class="startpageText">
			<p>
				<?php echo $text ?>
			</p>

		</div>

	</div>
	<div id="fancyboxTitles" style="display: none;">
		<?echo '<div>'.
		'<span style="float:left;max-width:80%;">'.htmlspecialchars($image->title, ENT_QUOTES).'</span>'.
		'<span style="float:right;" title="close">'.'<a href="#" OnClick="closeButton(event);">close</a>'.'</span>'.
		'<br style="clear:both;" />'.
		'<p style="text-align:center;color:#777;font: normal 13px "Helvetica Neue",Helvetica,Arial,sans-serif;">Copyright © Anne Hamrin Simonsson '.date("Y").'</p>'.
		'</div>';?>
	</div>


	<?if($this->session->userdata('logged_in')) {?>
	<div class="aboutText">
		<br />
		<hr />
		<br />
		<div class="aboutHeader">Create a startpage item</div>

		<?php echo validation_errors(); ?>

		<?php echo form_open('startpage/create') ?>
		Text<br/>
		<textarea name="text" id="text"><?=$text?></textarea><br />
		<input type="submit" name="submit" value="Save changes" />




		<hr/>
	</div>
	<table>
		<tr>
			<th style="text-align:center">
				Cover
			</th>
			<th style="text-align:center">
				Image and name
			</th>
		</tr>

		<?
		$startpage_image = $image;
		$i = 0;
		foreach ($images as $key => $image) {
			?>

			<tr>
				<td style="text-align:center">
					<? if($startpage_image->id==$image->id) {
					echo '<input type="radio" name="cover" value="' . $image->id . '" checked="yes" />';
				} else {
					echo '<input type="radio" name="cover" value="' . $image->id . '"/>';
				}?>

				</td>

				<td style="text-align:center; padding:0px 25px">
					<div class="imageListContainer" id="container_id_<?=$image->id?>">
						<div class="imageListElement imageListImage">
							<a class="picture"
							   rel="group"
							   href="<?=base_url('konst/' . $image->file_name)?>"
							   title="<?=$image->title?>">
								<img src="<?= base_url('konst/thumb/' . $image->file_name);?>"
									 alt="<?=$image->title?>"/>
							</a>
						</div>
						<div class="imageListElement imageListText">

							<?echo $image->id . '. '?>
							<a class="picture"
							   id="admin_picture_<?=$image->id?>"
							   rel="group2"
							   href="<?=base_url('konst/' . $image->file_name)?>"
							   title="<?=$image->title?>">
								<?=strlen($image->title) > 16 ? substr($image->title, 0, 13) . '...' : $image->title;?>
							</a>

						</div>
					</div>
				</td>
			</tr>

			<?
			$i++;
		}?>
	</table>
	</form>


	<?}?>



</div>