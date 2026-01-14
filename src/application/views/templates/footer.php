<?php
/**
 * Footer - SEO Optimized (Deep Linking + Figure Support)
 * Updated: 2025-12-03
 */
?>

<br /><br/>
</div>
</div>

<footer id="sFooter">
  <div id="footerspan">
    <div class="aboutText" style="text-align: center;">
      <hr />
      Copyright © Anne Hamrin Simonsson 2012-2025
    </div>
  </div>
</footer>

<script>
  // Load jQuery only if not already present
  function loadJQuery(callback) {
    if (window.jQuery) {
      callback();
    } else {
      var script = document.createElement('script');
      script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js';
      script.defer = true;
      script.onload = callback;
      document.head.appendChild(script);
    }
  }

  window.setImgId = function(id) {
    imgId = id;
  };
  window.setNewsId = function(id) {
    newsId = id;
  };

  // All jQuery-dependent code goes inside this callback
  loadJQuery(function() {
    // Fancybox
    var fancyboxScript = document.createElement('script');
    fancyboxScript.type = 'text/javascript';
    fancyboxScript.src = '<?=base_url('statics/fancybox/source/jquery.fancybox.pack.js?v=2.0.4')?>';
    window.closeButton = function(event){
      event.returnValue = false;
      if(event.preventDefault){ event.preventDefault(); }
      $.fancybox.close();
    };
    fancyboxScript.onload = function() {
      $(document).ready(function() {

        // --- SEO: Save original state to revert later ---
        var originalTitle = document.title;
        var originalPath = window.location.pathname;
        window.originalDescription = $('meta[name="description"]').attr('content');
        var $jsonLdScript = $('script[type="application/ld+json"]');
        window.originalJsonLd = $jsonLdScript.length ? $jsonLdScript.text() : '';

        // --- Main Gallery Settings ---
        var properties = {
          prevEffect  : 'fade',
          nextEffect  : 'fade',
          openSpeed   : 100,
          closeSpeed  : 100,
          nextSpeed   : 100,
          prevSpeed   : 100,
          openOpacity : true,
          closeBtn    : false,
          closeClick  : true,
          helpers     : {
            title  : { type : 'inside'},
            overlay  : {
              opacity : 1.0,
              css : { 'background-color' : '#FFF' },
              closeClick  : false
            }
          },
          beforeLoad: function() {
            var el = $(this.element);
            var caption = el.closest('figure').find('figcaption').text();
            if (caption && caption.length > 0) {
              this.title = caption;
            } else {
              this.title = $("#fancyboxTitles div").eq(this.index).html();
            }
          },
          afterShow: function() {
            var imgUrl = this.href;
            var filename = imgUrl.substring(imgUrl.lastIndexOf('/')+1);
            var slug = filename.substring(0, filename.lastIndexOf('.')).replace(/^anne-hamrin-simonsson-/, '');
            var newTitle = "";
            if (slug) {
              newTitle = slug.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) + " | Anne Hamrin Simonsson";
            } else {
              newTitle = "Artwork | Anne Hamrin Simonsson";
            }
            updateJsonLdForImage(slug)
            document.title = newTitle;
            $('meta[name="description"]').attr('content', $(this.element).data('imgtitle') || $(this.element).find('img').data('imgtitle'));
            if (history.pushState) {
              var albumPath = window.location.pathname.split('/').slice(0, 3).join('/');
              window.history.pushState({image: slug}, newTitle, albumPath + '/' + slug);
            }

            var figCaption = $(this.element).closest('figure').find('figcaption').text();
            var thumbAlt = $(this.element).find('img').attr('alt');
            var finalAlt = figCaption ? figCaption : (thumbAlt ? thumbAlt : newTitle);
            $('.fancybox-image').attr('alt', $.trim(finalAlt));
            var dbId = $(this.element).data('id') || $(this.element).find('img').data('id');
            if (dbId) {
              $('.fancybox-image').attr('id', dbId);
            }
          },
          afterClose: function() {
            document.title = originalTitle;
            if (history.pushState) {
              // Remove the image slug from the path
              var albumPath = window.location.pathname.split('/').slice(0, 3).join('/');
              window.history.pushState({}, originalTitle, albumPath);
            }
            // Restore original meta description
            $('meta[name="description"]').attr('content', window.originalDescription);
            // Restore original JSON-LD
            var $jsonLdScript = $('script[type="application/ld+json"]');
            if ($jsonLdScript.length && window.originalJsonLd) {
              $jsonLdScript.text(window.originalJsonLd);
            }
          }

        };

        var properties2 = {
          prevEffect  : 'fade',
          nextEffect  : 'fade',
          maxHeight   : '80%',
          openSpeed   : 100,
          closeSpeed  : 100,
          nextSpeed   : 100,
          prevSpeed   : 100,
          openOpacity : true,
          closeBtn    : false,
          closeClick  : false,
          helpers     : {
            title  : { type : 'inside'},
            overlay  : {
              opacity : 1.0,
              css : { 'background-color' : '#FFF' },
              closeClick  : false
            }
          }
        };

        $(".picture").fancybox(properties);
        $(".startUpPicture").fancybox(properties2).trigger('click');

        $(".popUpForm").fancybox({
          'autoDimensions': true,
          'margin'    : 50,
          'padding'   : 10,
          'titleShow' : false,
          'onClosed'  : function() { $("#login_error").hide(); }
        });

        $(".popUpFormImages").fancybox({
          'scrolling'     : 'yes',
          'autoDimensions': true,
          'margin'    : 50,
          'padding'   : 10,
          'titleShow' : false,
          'onClosed'  : function() { $("#login_error").hide(); }
        });

        $(".fancybox").fancybox({
          afterLoad : function() {
            this.title = $("#fancyboxTitles div").eq(this.index).html();
          }
        });

/*        var searchParams = new URLSearchParams(window.location.search);
        var imageTarget = searchParams.get('image');*/
        var imageTarget = "<?= isset($image_slug) ? addslashes($image_slug) : '' ?>";

        if (imageTarget) {
          var $targetLink = $('a.picture[href*="' + imageTarget + '"]');
          if ($targetLink.length > 0) {
            $targetLink.trigger('click');
          }
        }

        <?php
        if($this->session->get_userdata('logged_in')){
          include './././statics/js/adminHandling.php';
        }
        ?>
      });
    };
    document.head.appendChild(fancyboxScript);
  });
</script>

<script>
  // After the Fancybox image is shown and slug is available
  function updateJsonLdForImage(slug) {
    // Find the image element by slug
    var $imgLink = $('a.picture[href*="' + slug + '"]');
    var $img = $imgLink.find('img');
    if ($img.length === 0) return;

    // Gather data attributes or fallback to defaults
    var imgUrl = $img.attr('src');
    var creatorImg = "https://www.annesimonsson.se/konst/anne-hamrin-simonsson-liv-no-8-performance.jpg";

/*    <?php
      // I would like to load the ldjson template from statics/ldjson/art.json
     //  $ldjson = file_get_contents('./././statics/ldjson/art.json');
      // the $ldjson I want to replace some template strings. {{{name}}} {{{album}}} {{{filename}}}
      // $ldjson = str_replace('{{{album}}}', rtrim($title, 's'), $ldjson);
     //  print $ldjson;
      ?>*/
    var jsonLd = {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": slug.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) + " - <?= ucfirst(rtrim($title, 's')) ?> by Anne Hamrin Simonsson",
      "url": window.location.href.replace(/\?.*$/, '') + "/" + slug,
      "mainEntity": {
        "@type": "VisualArtwork",
        "@id": window.location.href.replace(/\?.*$/, '') + "/" + slug,
        "name": slug.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase()),
        "image": "https:" + imgUrl.replace('/thumb', ''),
        "artform": "<?= ucfirst(rtrim($title, 's')) ?>",
        "creator": {
          "@type": "Person",
          "name": "Anne Hamrin Simonsson",
          "image": creatorImg
        }
      }
    };

    // Replace the existing JSON-LD script
    var $jsonLdScript = $('script[type="application/ld+json"]');
    if ($jsonLdScript.length) {
      $jsonLdScript.text(JSON.stringify(jsonLd, null, 2));
    }
  }
</script>

<script>
  function loadGoogleAPI() {
    var script = document.createElement('script');
    script.src = 'https://apis.google.com/js/plusone.js';
    script.async = true;
    document.head.appendChild(script);
  }
  // Call loadGoogleAPI() only when you need Google Plus features
</script>

</body>
</html>
