<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:43
 * To change this template use File | Settings | File Templates.
 */?>

	<div class="aboutText" style="white-space:normal;">
		<?if($this->session->userdata('logged_in')) {?>
		<div class="aboutText">

			<div class="aboutHeader">Create a news item</div>

			<?php echo validation_errors(); ?>

			<?php echo form_open('news/create') ?>
			<p>
				För en bild använd: ???<a class="popUpFormImages"
										  href="#listAllImages">Bildnummer</a>!!!<br/>
				Exempel: ???12!!!<br />
				För att se alla bildnummer:
				<a class="popUpFormImages"
				   href="#listAllImages">
					klicka här</a>
				<br />
				<a href="<?=base_url('image_admin');?>">Go to image administration</a><br />
			</p>
			<div style="display:none">
				<div id="listAllImages" style="padding:0px;margin:0px;width:0px;">
					<?foreach ($images as $image) {?>
					<div style="padding:0px 50px;margin:0px 50px;width:150px">

						<img src="<?= base_url('statics/img/upload/thumb/'.$image->file_name);?>" alt="<?=$image->title?>" />
						<?echo ' '.$image->id. '.'?>

					</div>
					<br/>
					<?}?>
				</div>
			</div>


			Title<br/>
			<input type="input" id="title" name="title" /><br />

			Text<br/>
			<textarea name="text" id="text"></textarea><br />
			<a name="-1"></a>
			<input type="submit" name="submit" value="Create news item" />

			</form>

			<hr/>
		</div>
		<?}?>


		<?$prev_id = 0?>
		<?php foreach ($news as $news_item): ?>
		<?if($news_item['show']==1 || $this->session->userdata('logged_in')){?>

			<br/>
			<div>

				<?
				if($news_item['show']==0 && $this->session->userdata('logged_in')){
					echo '<div class="aboutHeader greyText">';
				} else {
					echo '<div class="aboutHeader">';
				}?>
				<?php echo $news_item['title'] ?></div>
				<?
				if($news_item['show']==0 && $this->session->userdata('logged_in')){
					echo '<p class="greyText">';
				} else {
					echo '<p>';
				}?>

				<?php echo $news_item['text'] ?>
				</p>

			</div>

			<div style="display:none">
				<form class="delete_news_form" id="delete_news_form_<?=$news_item['id']?>"  method="post" action="">
					<p>
						Delete this image?
					</p>
					<p>
						<input type="submit" value="Delete" />
					</p>
				</form>
			</div>
			<a name="<?=$prev_id?>"></a>
			<div style="display:none">
				<form class="rename_news_form" id="rename_news_form_<?=$news_item['id']?>"  method="post" action="">

					<p>
						Title<br />
						<input type="text" id="rename_title_field_<?=$news_item['id']?>" value="<?=$news_item['title']?>" style="width:250px"/>
					</p>

					<p>
					Text<br />
					<textarea name="text" id="rename_text_field_<?=$news_item['id']?>"><?=$news_item['text_raw']?></textarea><br />
					</p>
					<p>
						<input type="submit" value="Save" />
					</p>
				</form>
			</div>
			<?if($this->session->userdata('logged_in')){?>
				<br /><br />
				<a class="popUpForm"
				   href="#delete_news_form_<?=$news_item['id']?>"
				   onclick="setNewsId(<?=$news_item['id']?>)">
					DELETE</a>,

				<a class="popUpForm"
				   href="#rename_news_form_<?=$news_item['id']?>"
				   onclick="setNewsId(<?=$news_item['id']?>)">
					EDIT</a>

				<?if($news_item['show']==1){?>
					<p>This news is visible.
						<a href="<?=site_url('/news/hide/')?>/<?echo $news_item['id'].'/#'.($prev_id-1);?>">Click here to hide this news</a> </p><br />
					<?}else{?>
					<p>This news is hidden.
						<a href="<?=site_url('/news/show/')?>/<?echo $news_item['id'].'/#'.($prev_id-1);?>">Click here to make this visible</a></p><br />
					<?}?>
				<?}?>
			<?$prev_id +=1;?>

			<hr/>
			<?}?>
		<?php endforeach ?>
	</div>

</div>