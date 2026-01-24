<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:43
 * To change this template use File | Settings | File Templates.
 */
?>

<br/>
<?
if ($this->session->userdata('logged_in')) {
	?>

<div class="aboutText">
	<div class="aboutHeader">Image upload</div>
	<br/>
	<?php if (isset($error)) {
	echo $error;
}?>
	<?php if (isset($upload_data)) { ?>
	<div class="imageListContainer">
		<div class="imageListElement imageListImage">
			<a class="picture"
			   href="<?=base_url('konst/' . $upload_data['file_name'])?>"
			   title="<?=$upload_data['title']?>">
				<img src="<?= base_url('konst/thumb/' . $upload_data['file_name']);?>"
					 alt="<?=$upload_data['title']?>"/>
			</a>
		</div>
		<div class="imageListElement imageListText">

			<?echo $upload_data['id'] . '. '?>
			<a href="<?=base_url('konst/' . $upload_data['file_name'])?>"><?=$upload_data['title']?></a>

		</div>
	</div>
	<br/>
	<? }?>

	<?php echo form_open_multipart('image_admin/do_upload');?>

	<input type="file" name="userfile" size="25"/><br/>
	<label for="title">Image title: </label>
	<input type="text" id="title" name="title" size="25"/> <br/>

	<?
	$options = array();
	foreach($artwork_filters as $filter){
		$options[$filter->id] = $filter->name;
	}
	?>
	<p>
		<label for="upload_filter">Filter:</label>
		<?=form_dropdown('upload_filter', $options, 0, 'id="upload_filter"');?>
	</p>
	<p>
		<label for="order">Order:</label>
		<input type="text" name="order" id="order" placeholder="0" value="0"/>
	</p>

	<br/><br/>

	<input type="submit" value="upload"/>

	</form>
</div>

<hr/>
<br/>
<?
}?>

<?
if ($this->session->userdata('logged_in')) {
	?>
<div class="aboutText">


	<?
	$previousFilter = 0;

	foreach ($images as $image) {
		?>

		<?

		if ($image->artwork_filter != $previousFilter) {
			?>
      <br />
      <br />
      <h2 class="aboutHeader" style="text-align: center; font-size: 1.2em; font-weight: bold; margin-bottom: 0.5em;"><?=$image->name?></h2>
      <hr style="margin: 0 0 1em 0;"/>
      <?
			$previousFilter = $image->artwork_filter;
		}
		?>

		<div class="imageListContainer" id="container_id_<?=$image->id?>" style="margin-bottom: 20px;">
			<div class="imageListElement imageListImage" style="min-width: 85px;">
				<a
          class="picture"
				   rel="group"
				   href="<?=base_url('konst/' . $image->file_name)?>"
				   title="<?=$image->caption?>">
					<img
            id="<?= $image->id ?>"
            data-id="<?= $image->id ?>"
            data-file-id="<?= $image->file_id ?>"
            data-title="<?= $image->title ?>"
            data-description="<?= $image->caption ?>"
            src="<?= base_url('konst/thumb/' . $image->file_name);?>"
						 alt="<?=$image->caption?>"/>
				</a>
			</div>
			<div class="imageListElement imageListText">
				<?echo $image->id . '. '?>
        <a class="popUpForm"
           rel="popUpFormGroup"
           href="#rename_image_form_<?=$image->id?>"
				   title="<?=$image->caption?>">
          <?=$image->file_id?>
				</a><br />
        <?=$image->caption?>
        <div style="display: flex; gap: 15px;">
          <ul style="margin: 5px; display: flex; flex-direction: row; gap: 15px; padding: 0;">
            <li><a class="popUpForm"
                   href="#filter_image_form_<?=$image->id?>"
                   onclick="setImgId(<?=$image->id?>)">
                FILTER
              </a></li>
            <li><a class="popUpForm"
                   href="#order_image_form_<?=$image->id?>"
                   onclick="setImgId(<?=$image->id?>)">
                ORDER</a>
              (<?=$image->order?>)
            </li>
            <li style="margin-left:150px;">
              <a class="popUpForm"
                 href="#delete_image_form_<?=$image->id?>"
                 onclick="setImgId(<?=$image->id?>)">
                DELETE
              </a>
            </li>
          </ul>
        </div>
			</div>

			<div class="imageListElement imageListAdminStuff">
        <!-- clean out div -->
			</div>
			<div style="display:none">
				<form class="delete_image_form" id="delete_image_form_<?=$image->id?>" method="post" action="">
					<p>
						Delete this image?
					</p>
					<img src="<?= base_url('konst/medium/' . $image->file_name);?>"
						 alt="<?=$image->title?>"/>

					<p>
						<input type="submit" value="Delete"/>
					</p>
				</form>
			</div>
      <div style="display: none;">
        <form class="rename_image_form" id="rename_image_form_<?=$image->id?>" method="post" action="" style="margin-right: 20px; flex: 1;">
          <div style="display: flex; align-items: flex-start; gap: 30px;">
            <img src="<?= base_url('konst/medium/' . $image->file_name);?>"
                 alt="<?=$image->caption?>"
                 style="max-width: 250px; border: 1px solid #ccc;margin-top: 20px;margin-left:20px;"/>

            <div style="flex: 1; display: flex; flex-direction: column;">
              <input type="hidden" name="image_id" value="<?=$image->id?>" />

              <div style="display: flex; gap: 30px; align-items: flex-start; width: 100%;">

                <div style="flex: 1; display: flex; flex-direction: column; gap: 12px;">
                  <label><strong>Grundläggande information</strong></label>
                  <div style="display: flex; flex-direction: column;">
                    <label for="rename_image_field_<?=$image->id?>">Titel</label>
                    <input name="title" type="text" id="rename_image_field_<?=$image->id?>" value="<?=$image->title?>" style="width:100%" />
                  </div>
                  <div style="display: flex; flex-direction: column;">
                    <label for="alternate_name_<?=$image->id?>">Alternativ titel</label>
                    <input name="alternate_name" type="text" id="alternate_name_<?=$image->id?>" value="<?=$image->alternate_name?>" style="width:100%" placeholder="English title" />
                  </div>
                  <div style="display: flex; flex-direction: column;">
                    <label for="rename_file_id_field_<?=$image->id?>">Bild ID (URL-vänlig)</label>
                    <input name="file_id" type="text" id="rename_file_id_field_<?=$image->id?>" value="<?=$image->file_id?>" style="width:100%" />
                  </div>
                  <div style="display: flex; flex-direction: column;">
                    <label for="project_<?=$image->id?>">Projekt</label>
                    <input name="project" type="text" id="project_<?=$image->id?>" value="<?=$image->project?>" style="width:100%" />
                  </div>
                  <div style="display: flex; flex-direction: column;">
                    <label for="rename_image_caption_<?=$image->id?>">Beskrivning / Bildtext</label>
                    <textarea name="caption" id="rename_image_caption_<?=$image->id?>" style="width:100%; height:80px; resize:vertical;" rows="3"><?=$image->caption?></textarea>
                  </div>
                </div>

                <div style="flex: 1; display: flex; flex-direction: column; gap: 12px;">
                  <label><strong>Konstnärlig data & Mått</strong></label>
                  <div style="display: flex; gap: 15px;">
                    <div style="flex:1; display: flex; flex-direction: column;">
                      <label for="artform_<?=$image->id?>">Konstform</label>
                      <input name="artform" type="text" id="artform_<?=$image->id?>" value="<?=$image->artform?>" style="width:100%" />
                      <small style="color: #666;margin-top: 4px;">Exempel: Painting, Sculpture, Photograph, Drawing</small>
                    </div>
                  </div>
                  <div style="display: flex; gap: 15px;">
                    <div style="flex:1; display: flex; flex-direction: column;">
                      <label for="date_created_<?=$image->id?>">År</label>
                      <input name="date_created" type="text" id="date_created_<?=$image->id?>" value="<?=$image->date_created?>" style="width:100%" placeholder="YYYY" />
                    </div>
                  </div>
                  <div style="display: flex; gap: 15px;">
                    <div style="flex:1; display: flex; flex-direction: column;">
                      <label for="art_medium_<?=$image->id?>">Medium</label>
                      <input name="art_medium" type="text" id="art_medium_<?=$image->id?>" value="<?=$image->art_medium?>" style="width:100%" />
                      <small style="color: #666; margin-top: 4px;">Exempel: Oil, Acrylic, Watercolor, Ink, Bronze</small>
                    </div>
                  </div>
                  <div style="display: flex; gap: 15px;">
                    <div style="flex:1; display: flex; flex-direction: column;">
                      <label for="artwork_surface_<?=$image->id?>">Underlag</label>
                      <input name="artwork_surface" type="text" id="artwork_surface_<?=$image->id?>" value="<?=$image->artwork_surface?>" style="width:100%" />
                      <small style="color: #666; margin-top: 4px;">Exempel: Canvas, Paper, Wood panel, Metal</small>
                    </div>
                  </div>

                  <div style="display: flex; gap: 10px;">
                    <div style="flex:1; display: flex; flex-direction: column;">
                      <label>Höjd (cm)</label>
                      <input name="height_cm" type="number" step="0.1" id="height_cm_<?=$image->id?>" value="<?=$image->height_cm?>" style="width:100%" />
                    </div>
                    <div style="flex:1; display: flex; flex-direction: column;">
                      <label>Bredd (cm)</label>
                      <input name="width_cm" type="number" step="0.1" id="width_cm_<?=$image->id?>" value="<?=$image->width_cm?>" style="width:100%" />
                    </div>
                    <div style="flex:1; display: flex; flex-direction: column;">
                      <label>Djup (cm)</label>
                      <input name="depth_cm" type="number" step="0.1" id="depth_cm_<?=$image->id?>" value="<?=$image->depth_cm?>" style="width:100%" />
                    </div>
                  </div>
                </div>
              </div>

              <div style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 20px; display: flex; flex-direction: column; gap: 12px; width: 100%;">
                <label><strong>Plats & Fotograf</strong></label>
                <div style="display: flex; gap: 15px;">
                  <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="geo_location_<?=$image->id?>">Platsnamn</label>
                    <input name="geo_location" type="text" id="geo_location_<?=$image->id?>" value="<?=$image->geo_location?>" style="width:100%" />
                  </div>
                  <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="address_locality_<?=$image->id?>">Stad</label>
                    <input name="address_locality" type="text" id="address_locality_<?=$image->id?>" value="<?=$image->address_locality?>" style="width:100%" />
                  </div>
                  <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="photographer_name_<?=$image->id?>">Fotograf</label>
                    <input name="photographer_name" type="text" id="photographer_name_<?=$image->id?>" value="<?=$image->photographer_name?>" style="width:100%" />
                  </div>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column;">
                  <label for="map_url_<?=$image->id?>">Karta (URL)</label>
                  <input name="map_url" type="text" id="map_url_<?=$image->id?>" value="<?=$image->map_url?>" style="width:100%" />
                </div>
              </div>

              <div style="margin-top: 10px; padding-top: 10px; text-align: center; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" id="my-custom-prev-btn" style="padding: 12px 24px; cursor: pointer; background: #95a5a6; color: #fff; border: none; border-radius: 4px; font-weight: bold;">&#8678; Prev</button>
                <input type="submit" value="Uppdatera" style="padding: 12px 24px; cursor: pointer; background: #2c3e50; color: #fff; border: none; border-radius: 4px; font-weight: bold; margin: 0 20px;" />
                <button type="button" id="my-custom-next-btn" style="padding: 12px 24px; cursor: pointer; background: #3498db; color: #fff; border: none; border-radius: 4px; font-weight: bold;">Next &#8680;</button>
              </div>
            </div>
          </div>
        </form>
      </div>
			<div style="display:none">
				<form class="filter_image_form" id="filter_image_form_<?=$image->id?>"  method="post" action="">
					<img src="<?= base_url('konst/medium/'.$image->file_name);?>" alt="<?=$image->title?>" />
					<?
					$options = array();
					foreach($artwork_filters as $filter){
						$options[$filter->id] = $filter->name;
					}
					?>
					<p>
						<label for="filter_image_field_<?=$image->id?>">Filter:</label>
						<?=form_dropdown('filter_image_field_'.$image->id, $options, $image->artwork_filter, 'id="filter_image_field_'.$image->id.'"');?>
					</p>
					<p>
						<input type="submit" value="Save" />
					</p>
				</form>
			</div>
			<div style="display:none">
				<form class="order_image_form" id="order_image_form_<?=$image->id?>"  method="post" action="">
					<img src="<?= base_url('konst/medium/'.$image->file_name);?>" alt="<?=$image->title?>" />
					<p>
						Order:
						<input type="text" id="order_image_field_<?=$image->id?>" value="<?=$image->order?>" style="width:250px"/>
					</p>
					<p>
						<input type="submit" value="Save" />
					</p>
				</form>
			</div>

		</div>

		<?
	}
	?>


</div>
<br/>
<?}?>

</div>


