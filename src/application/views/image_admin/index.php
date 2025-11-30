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


	<div class="aboutHeader">All images</div>
	<br/>

	<?
	$previousFilter = 0;

	foreach ($images as $image) {
		?>

		<?

		if ($image->artwork_filter != $previousFilter) {
			?>
			<div class="aboutHeader" style="text-align: center"><?=$image->name?></div><br/>
			<?
			$previousFilter = $image->artwork_filter;
		}
		?>

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
			<div class="imageListElement imageListAdminStuff">
				<UL>
					<!--<LI><a href="<?=base_url('image_admin' . $image->file_name)?>">DELETE</a><br /></LI>-->
					<LI>
						<a class="popUpForm"
						   href="#delete_image_form_<?=$image->id?>"
						   onclick="setImgId(<?=$image->id?>)">
							DELETE
						</a>
					</LI>
					<LI><a class="popUpForm"
						   href="#rename_image_form_<?=$image->id?>"
						   onclick="setImgId(<?=$image->id?>)">
						RENAME
					</a></LI>
					<LI><a class="popUpForm"
						   href="#filter_image_form_<?=$image->id?>"
						   onclick="setImgId(<?=$image->id?>)">
						FILTER
					</a></LI>
					<LI><a class="popUpForm"
						   href="#order_image_form_<?=$image->id?>"
						   onclick="setImgId(<?=$image->id?>)">
						ORDER</a>
						(<?=$image->order?>)
					</LI>
				</UL>

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
			<div style="display:none">
				<form class="rename_image_form" id="rename_image_form_<?=$image->id?>" method="post" action="">
					<img src="<?= base_url('konst/medium/' . $image->file_name);?>"
						 alt="<?=$image->title?>"/>

					<p>
						Rename image:
						<input type="text" id="rename_image_field_<?=$image->id?>" value="<?=$image->title?>" style="width:250px"/>
					</p>
					<p>
						<input type="submit" value="Rename" />
					</p>
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


