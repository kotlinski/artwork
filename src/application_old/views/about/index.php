<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:43
 * To change this template use File | Settings | File Templates.
 */
?>
    <div class="aboutText">
        <?php echo $about['text'] ?>
    </div>

    <?
    if($this->session->userdata('logged_in')) {
        echo '<br/><hr/><br />';?>
        <div class="aboutText">
            <div class="aboutHeader">User Status</div>
            <?echo 'User logged in as ' . $this->session->userdata('username');?>
            <hr/>
            <br/>

            <div class="aboutHeader">Ändra about-texten</div>

            För rubriker använd ??? och !!!. Exempel: ???Rubtik!!!
        </div>


        <?php echo validation_errors(); ?>

        <?php echo form_open('about/create') ?>
        <textarea name="text" id="aboutTextarea"><?=$about_raw['text']?></textarea><br />

        <input type="submit" name="submit" value="Uppdatera texten." />

        <br/>
        <br/>
        <p>
            (Alla uppdateringar ligger sparade i databasen.)
        </p>
        <br/>
        <br/>

        </form>

        <? } ?>
</div>




