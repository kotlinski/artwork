<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 13:58
 * To change this template use File | Settings | File Templates.
 */

/*<strong>&copy; 2011</strong>*/

?>

<br /><br/>
</div>
<footer id="sFooter">
<!--<br /><br /><br /><br /><br /><br /><br /><br />
	<br /><br />
	<br /><br /><br /><br />

-->
	<div id="footerspan">
		<div class="aboutText" style="text-align: center;">
			<hr />
			Copyright © Anne Hamrin Simonsson 2012
			<!-- Place this tag where you want the +1 button to render -->
			<!--<div class="g-plusone" data-size="small" data-annotation="none"></div>-->
		</div>
	</div>
</footer>
</div> <!--! end of #container -->


<!-- JavaScript at the bottom for fast page loading -->

<!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="<?=base_url('static/js/libs/jquery-1.6.2.min.js')?>"><\/script>')</script>

<script type="text/javascript">
	var imgId = -1;
	var newsId = -1;

	function setImgId(id){
		imgId = id;
	}
	function setNewsId(id){
		newsId = id;
	}

	$(document).ready(function() {
		var properties = {
			prevEffect		: 'fade',
			nextEffect		: 'fade',
			openSpeed 		: 900,
			closeSpeed 		: 800,
			nextSpeed		: 500,
			prevSpeed		: 500,
			openOpacity		: true,
			closeBtn		: true,
			closeClick		: true,
			helpers		: {
				title	: { type : 'inside'},

				overlay	: {
					opacity : 1.0,
					css : {
						'background-color' : '#FFF'
					},
					closeClick		: false
				}
			}
		};

		var properties2 = {
			prevEffect		: 'fade',
			nextEffect		: 'fade',
			maxHeight		: '80%',
			openSpeed 		: 900,
			closeSpeed 		: 800,
			nextSpeed		: 500,
			prevSpeed		: 500,
			openOpacity		: true,
			closeBtn		: false,
			closeClick		: false,
			helpers		: {
				title	: { type : 'inside'},

				overlay	: {
					opacity : 1.0,
					css : {
						'background-color' : '#FFF'
					},
					closeClick		: false
				}
			}
		};

		/*
		   *   Examples - images
		   */
		$(".picture").fancybox(properties);
		$(".startUpPicture").fancybox(properties2).trigger('click')

		$(".popUpForm").fancybox({
			'autoDimensions': true,
			'margin'		: 50,
			'padding'		: 10,
			'titleShow'		: false,
			'onClosed'		: function() {
				$("#login_error").hide();
			}
		});
		$(".popUpFormImages").fancybox({
			'scrolling'		: 'yes',
			'autoDimensions': true,
			'margin'		: 50,
			'padding'		: 10,
			'titleShow'		: false,
			'onClosed'		: function() {
				$("#login_error").hide();
			}
		});
		/*
		'scrolling'		: 'yes',
			'autoDimensions': true,
			'margin'		: 50,
			'padding'		: 10,
			'titleShow'		: false,*/
		$(".delete_image_form").bind("submit", function() {
			if (imgId < 0 ) {
				return false;
			}
			$("#container_id_"+imgId).css( 'visibility' , 'hidden'  );
			$("#container_id_"+imgId).css( 'height' , '0px'  );
			$("#container_id_"+imgId).css( 'margin' , '0px'  );
			$.ajax({
				type	: "POST",
				cache	: false,
				url		: "<?=base_url('image_admin/delete')?>/"+imgId,
				data	: $(this).serializeArray(),
				success: function(data) {
					$.fancybox(data);
				}
			});

			return false;
		});
		$(".rename_image_form").bind("submit", function() {
			if (imgId < 0 ) {
				return false;
			}
			var newName = $('#rename_image_field_'+imgId).val();
			var subName = newName;
			if(subName.length > 16){
				subName = newName.substr(0, 13)+'...';
			}
			$('#admin_picture_'+imgId).html(subName);

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: "<?=base_url('image_admin/update')?>/"+imgId,
				data	: {title: newName},
				success: function(data) {
					$.fancybox(data);
				}
			});

			return false;
		});
		$(".delete_news_form").bind("submit", function() {
			if (newsId < 0 ) {
				return false;
			}
			$.ajax({
				type	: "POST",
				cache	: false,
				url		: "<?=base_url('news/delete')?>/"+newsId,
				data	: $(this).serializeArray(),
				success: function(data) {
					$.fancybox(data);
				}
			});

			return false;
		});
		$(".rename_news_form").bind("submit", function() {
			if (newsId < 0 ) {
				return false;
			}
			var newTitle = $('#rename_title_field_'+newsId).val();
			var newText = $('#rename_text_field_'+newsId).val();

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: "<?=base_url('news/update')?>/"+newsId,
				data	: {title: newTitle, text: newText},
				success: function(data) {
					$.fancybox(data);
				}
			});

			return false;
		});
		$(".filter_image_form").bind("submit", function() {
			if (imgId < 0 ) {
				return false;
			}
			var newFilter = $('#filter_image_form_'+imgId +' option:selected').val();
			$.ajax({
				type	: "POST",
				cache	: false,
				url		: "<?=base_url('image_admin/setFilter')?>/"+imgId,
				data	: {filter_id: newFilter},
				success: function(data) {
					$.fancybox(data);
				}
			});

			return false;
		});

		$(".order_image_form").bind("submit", function() {
			if (imgId < 0 ) {
				return false;
			}
			var newOrder = $('#order_image_field_'+imgId).val();
			$.ajax({
				type	: "POST",
				cache	: false,
				url		: "<?=base_url('image_admin/setOrder')?>/"+imgId,
				data	: {order: newOrder},
				success: function(data) {
					$.fancybox(data);
				}
			});

			return false;
		});
	});

</script>

<!-- scripts concatenated and minified via ant build script-->
<!--<script defer src="<?=base_url('statics/js/plugins.js')?>"></script>
<script defer src="<?=base_url('statics/js/script.js')?>"></script>-->
<!-- end scripts-->


<!-- Change UA-XXXXX-X to be your site's ID -->
<script>
    window._gaq = [['_setAccount','UAXXXXXXXX1'],['_trackPageview'],['_trackPageLoadTime']];
    Modernizr.load({
        load: ('https:' == location.protocol ? '//ssl' : '//www') + '.google-analytics.com/ga.js'
    });
</script>

<!-- Google +1 button -->
<script type="text/javascript">
	(function() {
		var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
		po.src = 'https://apis.google.com/js/plusone.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
	})();
</script>


<!-- Add fancyBox -->
<script type="text/javascript" src="<?=base_url('statics/fancybox/source/jquery.fancybox.pack.js?v=2.0.4')?>"></script>
<!-- Optionaly add button and/or thumbnail helpers -->
<script type="text/javascript" src="<?=base_url('statics/fancybox/source/helpers/jquery.fancybox-buttons.js?v=2.0.4')?>"></script>
<script type="text/javascript" src="<?=base_url('statics/fancybox/source/helpers/jquery.fancybox-thumbs.js?v=2.0.4')?>"></script>


<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
chromium.org/developers/how-tos/chrome-frame-getting-started -->
<!--[if lt IE 7 ]>
<script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
<script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
<![endif]-->

</body>
</html>