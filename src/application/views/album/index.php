<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:43
 * To change this template use File | Settings | File Templates.
 */ ?>

<div class="aboutText">

  <a name="-1"></a>

  <? $prev_id = 0 ?>
  <? //$mode = current($cover_images);?>

  <br/>
  <table class="tableview">
    <?
    $titles = "";
    echo '<tr>';
    foreach ($images as $counter => $image) { ?>
      <? if ($counter % 3 == 0) {
        echo '</tr><tr>';
      } ?>
      <td valign="middle" align="center" style="padding-bottom:10px;margin:0px;">
        <a
          class="picture"
          rel="group2"
          href="<?= base_url('konst/' . $image->file_name) ?>"
          title="<?= $image->title ?>"
          style="padding:0px;margin:0px;border:0px;">
          <img
            id="<?= $image->id ?>"
            data-id="<?= $image->id ?>"
            data-imgtitle="<?= $image->title ?>"
            src="<?= base_url('konst/thumb/' . $image->file_name) ?>"
            alt="<?= htmlspecialchars('Art ' . $title . ' av konstnar Anne Hamrin Simonsson: ' . $image->title, ENT_QUOTES, 'UTF-8') ?>"
            style="padding:0px;margin:0px;border:0px;"/>
        </a>
      </td>

      <?
      $titles .=
        '<div>' .
        '<span style="float:left;max-width:80%;">' .
        htmlspecialchars($image->title, ENT_QUOTES) . '<br />' .
        '<span style="text-align:left;color:#777;font: normal 13px "Helvetica Neue",Helvetica,Arial,sans-serif;">Copyright © Anne Hamrin Simonsson '/*.date("Y")*/ . '</span>' .

        '</span>' .
        '<span style="float:right;" title="close">' . '<a href="#" OnClick="closeButton(event);">close</a>' . '</span>' .
        '<br style="clear:both;" />' .
        '</div>';

      ?>

    <? } ?>
  </table>
  <div id="fancyboxTitles" style="display: none;">
    <? echo $titles ?>
  </div>


  <br/> <br/>
</div>
</div>

<?php /*if (!empty($data['clicked_image'])): */?><!--
  <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
      var imgId = '<?php /*= htmlspecialchars($data['clicked_image'], ENT_QUOTES, 'UTF-8') */?>';
      console.log(imgId)
      var anchor = document.querySelector('a.picture img[id="' + imgId + '"]');
      if (anchor && anchor.parentElement) {
        anchor.parentElement.click();
      }
    });
  </script>
--><?php /*endif; */?>
<!-- Fancybox JS initialization with afterShow and afterClose callbacks -->
<!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {

    // 1. Save the "Original" Page Title and URL so we can revert later
    var originalTitle = document.title;
    var originalPath = window.location.pathname;

    // 2. Initialize Fancybox with History API hooks
    $("a.fancybox").fancybox({
      // OPTIONAL: Add helpers if you use them (buttons, thumbs)
      helpers: {
        overlay: { locked: false },
        title: { type: 'inside' }
      },

      // EVENT: When an image is displayed (First click OR Next/Prev)
      afterShow: function() {
        // A. Get the image URL from the clicked link
        var imgUrl = this.href;

        // B. Extract filename without extension (e.g., "my-art-work")
        // Splits by '/' to get filename, then splits by '.' to remove .jpg
        var filename = imgUrl.substring(imgUrl.lastIndexOf('/')+1);
        var slug = filename.substring(0, filename.lastIndexOf('.'));

        // C. Create the Tracking Title
        // You can use the caption (this.title) or format the slug
        var newTitle = "";
        if (this.title && this.title.length > 0) {
          // Use the caption if it exists
          newTitle = this.title.split(' - ')[0] + " | Anne Hamrin Simonsson";
        } else {
          // Fallback: Format the filename (replace - with space)
          var readableName = slug.replace(/anne-simonsson-/g, '').replace(/-/g, ' ');
          // Capitalize first letter of words (optional helper function logic)
          readableName = readableName.replace(/\b\w/g, function(l){ return l.toUpperCase() });
          newTitle = readableName + " | Anne Hamrin Simonsson";
        }

        // D. Update the Browser URL and Title (without reloading)
        document.title = newTitle;
        window.history.pushState({image: slug}, newTitle, "?image=" + slug);
      },

      // EVENT: When the gallery is closed
      afterClose: function() {
        // Revert URL and Title to the main gallery state
        document.title = originalTitle;
        window.history.pushState({}, originalTitle, originalPath);
      }
    });

    // 3. AUTO-OPEN LOGIC (From previous step)
    // This checks if someone arrived via a shared link and opens the image immediately
    var searchParams = new URLSearchParams(window.location.search);
    var imageTarget = searchParams.get('image');

    if (imageTarget) {
      // Find the link that matches the image slug and click it
      // We search for hrefs containing the slug
      var $targetLink = $('a.fancybox[href*="' + imageTarget + '"]');
      if ($targetLink.length > 0) {
        $targetLink.trigger('click');
      }
    }

  });
</script>-->
