<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon Kotlinski
 * Date: 2012-01-30
 * Time: 21:46
 * To change this template use File | Settings | File Templates.
 */

?>


	<div class="aboutText">

		<?if($this->session->userdata('logged_in')) {?>
		<div class="aboutText">

			<div class="aboutHeader">Image upload</div> <br/>
			<a href="<?=base_url('image_admin');?>">Load up new images here</a><br /><br />
			<hr/>
			<br/>
			<div class="aboutHeader">Create a album item</div>

			<?php echo validation_errors(); ?>

			<?php echo form_open('album/update_album') ?>

			<input type="hidden" id="id" name="id" value="<?=$album_item['id']?>" />

			Title<br/>
			<input type="input" id="title" name="title" value="<?=$album_item['title']?>"/><br />

			Year<br/>
			<input type="input" id="year" name="year" value="<?=$album_item['year']?>"/><br /><br />

			<div class="aboutHeader">Pick pictures below</div> <br />

			<input type="submit" name="submit" value="Save updates" /><br /><br /><br />

			<table>
				<tr>
					<th style="text-align:center">
						Cover
					</th>
					<th style="text-align:center">
						Add to Album
					</th>
					<th style="text-align:center">
						Image and name
					</th>
				</tr>

				<?


				$image_ids_in_album = array();
				foreach($album_images as $image){
					array_push($image_ids_in_album, $image['id']);
				}
				$i = 0;
				foreach ($images as $key => $image) {
					?>

					<tr>
						<td style="text-align:center">
							<? if($cover_image['id']==$image->id) {
							echo '<input type="radio" name="cover" value="' . $image->id . '" checked="yes" />';
						} else {
							echo '<input type="radio" name="cover" value="' . $image->id . '"/>';
						}?>

						</td>
						<td style="text-align:center">
						<? if (in_array($image->id, $image_ids_in_album)) {
							echo '<input type="checkbox" name="inAlbum[]" value="' . $image->id . '" checked="yes" />';
						} else {
							echo '<input type="checkbox" name="inAlbum[]" value="' . $image->id . '"/>';
						}?>

						</td>

						<td style="text-align:center">
							<div class="imageListContainer" id="container_id_<?=$image->id?>">
								<div class="imageListElement imageListImage">
									<a class="picture"
									   rel="group"
									   href="<?=base_url('statics/img/upload/' . $image->file_name)?>"
									   title="<?=$image->title?>">
										<img src="<?= base_url('statics/img/upload/thumb/' . $image->file_name);?>"
											 alt="<?=$image->title?>"/>
									</a>
								</div>
								<div class="imageListElement imageListText">

									<?echo $image->id . '. '?>
									<a class="picture"
									   id="admin_picture_<?=$image->id?>"
									   rel="group2"
									   href="<?=base_url('statics/img/upload/' . $image->file_name)?>"
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
			<hr/>
		</div>
		<?}?>

	</div>
</div>
