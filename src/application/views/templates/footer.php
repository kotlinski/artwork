<?php
/**
 * Footer - SEO Optimized (Deep Linking + Figure Support)
 * Updated: 2025-12-03
 */
?>

<br /><br/>
</div>
<footer id="sFooter">
  <div id="footerspan">
    <div class="aboutText" style="text-align: center;">
      <hr />
      Copyright © Anne Hamrin Simonsson 2012-2025
    </div>
  </div>
</footer>
</div>

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
        var imgId = -1;
        var newsId = -1;

        function setImgId(id){ imgId = id; }
        function setNewsId(id){ newsId = id; }

        // --- SEO: Save original state to revert later ---
        var originalTitle = document.title;
        var originalPath = window.location.pathname;

        // --- Main Gallery Settings ---
        var properties = {
          prevEffect  : 'fade',
          nextEffect  : 'fade',
          openSpeed   : 900,
          closeSpeed  : 800,
          nextSpeed   : 500,
          prevSpeed   : 500,
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
            var slug = filename.substring(0, filename.lastIndexOf('.')).replace(/^anne-simonsson-/, '');
            var newTitle = "";
            if (this.title) {
              var textTitle = $("<div/>").html(this.title).text();
              newTitle = textTitle.split(' - ')[0] + " | Anne Hamrin Simonsson";
            } else {
              newTitle = "Artwork | Anne Hamrin Simonsson";
            }
            document.title = newTitle;
            if (history.pushState) {
              window.history.pushState({image: slug}, newTitle, "?image=" + slug);
            }
            var figCaption = $(this.element).closest('figure').find('figcaption').text();
            var thumbAlt = $(this.element).find('img').attr('alt');
            var finalAlt = figCaption ? figCaption : (thumbAlt ? thumbAlt : newTitle);
            $('.fancybox-image').attr('alt', $.trim(finalAlt));
          },
          afterClose: function() {
            document.title = originalTitle;
            if (history.pushState) {
              window.history.pushState({}, originalTitle, originalPath);
            }
          }
        };

        var properties2 = {
          prevEffect  : 'fade',
          nextEffect  : 'fade',
          maxHeight   : '80%',
          openSpeed   : 900,
          closeSpeed  : 800,
          nextSpeed   : 500,
          prevSpeed   : 500,
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

        var searchParams = new URLSearchParams(window.location.search);
        var imageTarget = searchParams.get('image');
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
