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

function closeButton(event){
	event.returnValue = false;
	if(event.preventDefault){
		event.preventDefault();
	}

	$.fancybox.close();
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
		closeBtn		: false,
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
		},
		afterLoad : function() {
			this.title = $("#fancyboxTitles div").eq(this.index).html();
		} // afterload
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

	$(".startUpPicture").fancybox(properties2).trigger('click');


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

<?
	if($this->session->get_userdata('logged_in')){
		include './././statics/js/adminHandling.php';
		/*$this->load->helper('file');
		$string = read_file('./././statics/js/adminHandling.php');
		echo $string;*/
	} else {

	}
?>

	$(".fancybox").fancybox({
		afterLoad : function() {
			this.title = $("#fancyboxTitles div").eq(this.index).html();
		}
	}); //fancybox
});


</script>

<!-- scripts concatenated and minified via ant build script-->
<!--<script defer src="<?=base_url('statics/js/plugins.js')?>"></script>
<script defer src="<?=base_url('statics/js/script.js')?>"></script>-->
<!-- end scripts-->

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
