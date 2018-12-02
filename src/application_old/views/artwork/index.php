<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:43
 * To change this template use File | Settings | File Templates.
 */?>
<div id="bodyspan">
    <div class="aboutText">
        <?if($this->session->userdata('logged_in')) {?>
            <div class="aboutText">
                <div class="aboutHeader">User Status</div>
                <?echo 'User logged in as ' . $this->session->userdata('username');?>
                <hr/>
                <br/>
                <div class="aboutHeader">Create a news item</div>

                <?php echo validation_errors(); ?>

                <?php echo form_open('artwork/create') ?>

                Title<br/>
                <input type="input" id="title" name="title" /><br />

                Text<br/>
                <textarea name="text" id="text"></textarea><br />

                <input type="submit" name="submit" value="Create news item" />

                </form>
                <hr/>
            </div>
        <?}?>


        <?php foreach ($news as $news_item): ?>
            <?if($news_item['show']==1 || $this->session->userdata('logged_in')){?>
            <br/>
                <div onclick="location.href='artwork/<?php echo $artwork_item['slug'] ?>';" style="cursor: pointer;">
                    <div class="aboutHeader"><?php echo $artwork_item['title'] ?></div>
                    <?php echo $artwork_item['created'].'|'.$artwork_item['text'] ?>
                    <!--<p><a href="artwork/<?php echo $artwork_item['slug'] ?>">View article</a></p>-->

                </div>
                <?if($this->session->userdata('logged_in')){?>
                    <?if($artwork_item['show']==1){?>
                        <a href="<?=site_url('/artwork/hide/')?>/<?echo $artwork_item['id'];?>">Hide this artwork</a>
                    <?}else{?>
                        <a href="<?=site_url('/artwork/show/')?>/<?echo $artwork_item['id'];?>">Make this visible</a>
                    <?}?>
                <?}?>

                <hr/>
            <?}?>
        <?php endforeach ?>
    </div>
</div>