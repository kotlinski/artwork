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

            var $imgLink = $('a.picture[href*="' + slug + '"]');
            var $img = $imgLink.find('img');
            if ($img.length === 0) return;

            // Pull data from your SQL-backed attributes
            var description = $img.data('description'); // e.g., "Lök no 2 acrylic on masonite 1x1m 2009"
            var title = $img.data('title');
            var file_id = $img.data('file-id');
            var project = $img.data('project');
            var geo_location = $img.data('geo-location');
            var height_px = $img.data('height-px');
            var width_px = $img.data('width-px');

            // Save original canonical href
            var $canonical = $('link[rel="canonical"]');
            window.originalCanonical = $canonical.length ? $canonical.attr('href') : null;
            window.originalDescription = $('meta[property="og:description"]').attr('content');
            window.originalTitle = $('meta[property="og:title"]').attr('content');
            window.originalImage = $('meta[property="og:image"]').attr('content');
            window.originalImageWidth = $('meta[property="og:image:width"]').attr('content');
            window.originalImageHeight = $('meta[property="og:image:height"]').attr('content');
            $('meta[name="description"]').attr('content', description);
            $('meta[property="og:description"]').attr('content', description);
            $('meta[property="og:title"]').attr('content', title);
            $('meta[property="og:image"]').attr('content', "https://www.annesimonsson.se/konst/" + filename);
            $('meta[property="og:image:width"]').attr('content', width_px);
            $('meta[property="og:image:height"]').attr('content', height_px);

            var newTitle = "";
            if (slug) {
              newTitle = title + " | Anne Hamrin Simonsson";
            } else {
              newTitle = "Artwork | Anne Hamrin Simonsson";
            }
            var album_path = window.location.pathname.split('/').slice(0, 3).join('/');
            album_path = (album_path === '/') ? '' : album_path;
            updateJsonLdForImage(title, description, filename, file_id, album_path, project, geo_location, width_px, height_px)
            document.title = newTitle;
            if (history.pushState) {
              window.history.pushState({image: slug}, newTitle, album_path + '/' + slug);
            }

            var figCaption = $(this.element).closest('figure').find('figcaption').text();
            var thumbAlt = $(this.element).find('img').attr('alt');
            var finalAlt = figCaption ? figCaption : (thumbAlt ? thumbAlt : newTitle);
            $('.fancybox-image').attr('alt', $.trim(finalAlt));
            var dbId = $(this.element).data('id') || $(this.element).find('img').data('id');
            if (dbId) {
              $('.fancybox-image').attr('id', dbId);
            }
            var canonicalUrl = "https://www.annesimonsson.se/album" + album_path + "/" + slug;
            var $canonical = $('link[rel="canonical"]');
            if ($canonical.length) {
              $canonical.attr('href', canonicalUrl);
              $('meta[property="og:url"]').attr('content', canonicalUrl);
            } else {
              $('<link rel="canonical" href="' + canonicalUrl + '">').appendTo('head');
              $('meta[property="og:url"]').attr('content', canonicalUrl);
            }
          },
          afterClose: function() {
            document.title = originalTitle;
            if (history.pushState) {
              var segments = window.location.pathname.split('/').filter(Boolean);
              if (segments.includes('image_admin')) {
                // Keep /image_admin and any following segments
                var idx = segments.indexOf('image_admin');
                var newPath = '/' + segments.slice(0, idx + 1).join('/');
                window.history.pushState({}, originalTitle, newPath);
              } else if (segments.includes('album') && segments.length > 1) {
                // Remove last segment, keep the rest
                var newPath = '/' + segments.slice(0, -1).join('/');
                window.history.pushState({}, originalTitle, newPath);
              } else {
                window.history.pushState({}, originalTitle, '/');
              }
            }
            $('meta[name="description"]').attr('content', window.originalDescription);
            $('meta[property="og:description"]').attr('content', window.originalDescription);
            $('meta[property="og:title"]').attr('content', window.originalTitle);
            $('meta[property="og:image"]').attr('content', window.originalImage);
            $('meta[property="og:image:width"]').attr('content', window.originalImageWidth);
            $('meta[property="og:image:height"]').attr('content', window.originalImageHeight);
            var $jsonLdScript = $('script[type="application/ld+json"]');
            if ($jsonLdScript.length && window.originalJsonLd) {
              $jsonLdScript.text(window.originalJsonLd);
            }
            var $canonical = $('link[rel="canonical"]');
            if (window.originalCanonical) {
              $canonical.attr('href', window.originalCanonical);
              $('meta[property="og:url"]').attr('content', window.originalCanonical);
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
          updateJsonLdFromSlug(imageTarget);
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
        window.onpopstate = function(event) {
          if (event.state && event.state.image) {
            // Open the image if navigating to a state with an image
            var $targetLink = $('a.picture[href*="' + event.state.image + '"]');
            if ($targetLink.length > 0) {
              $targetLink.trigger('click');
            }
          } else {
            // Close Fancybox if navigating back to the gallery
            if ($.fancybox && $.fancybox.isOpen) {
              $.fancybox.close();
            }
            document.title = originalTitle;
            $('meta[name="description"]').attr('content', window.originalDescription);
            var $jsonLdScript = $('script[type="application/ld+json"]');
            if ($jsonLdScript.length && window.originalJsonLd) {
              $jsonLdScript.text(window.originalJsonLd);
            }
          }
        };

      });
    };
    document.head.appendChild(fancyboxScript);
  });
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
