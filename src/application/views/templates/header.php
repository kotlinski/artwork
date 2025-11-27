<!DOCTYPE html>
<html class="no-js" lang="en" itemscope itemtype="http://schema.org/WebPage">

<head>
	<meta charset="utf-8">

	<!-- Use the .htaccess and remove these lines to avoid edge case issues.
	 More info: h5bp.com/b/378 -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Person",
            "name": "Anne Simonsson",
            "alternateName": "Anne Hamrin Simonsson",
            "birthDate": "1967",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Färjestaden",
                "addressCountry": "Sweden"
            },
            "jobTitle": "Conceptual Artist",
            "description": "Anne Hamrin Simonsson is a conceptual artist working with paintings, objects, and installations. Her work explores the theme of 'LIFE' through various techniques and materials, often in unexpected public spaces.",
            "sameAs": [
                "https://www.instagram.com/ahamrinsimonsson/",
                "https://www.konstikalmarlan.se/verksamhet/anne-hamrin-simonsson/",
                "https://www.smalandstriennalen.se/medverkande/anne-hamrin-simonsson",
                "https://www.linkedin.com/in/anne-hamrin-simonsson-1948aba5/"
            ]
        }
    </script>

	<?if($title == "Startpage"){ ?>
		<title>Anne Hamrin Simonsson</title>
		<meta name="description" content="Official website of Swedish artist Anne Hamrin Simonsson; news, artwork, about and contact. All images and texts belong to Anne Hamrin Simonsson.">
	<? } else {?>
		<title>Anne Hamrin Simonsson <?= ' - ' . $title ?></title>
		<meta name="description" content="Official website of Swedish artist Anne Hamrin Simonsson; <?=$title?>. All images and texts belong to Anne Hamrin Simonsson.">
	<? } ?>
	<meta name="revisit-after" content="1 days">
    <meta property="og:title" content="Anne Hamrin Simonsson - Artwork">
    <meta property="og:description" content="Official website of Swedish artist Anne Hamrin Simonsson. View artwork, news, and contact information.">
    <meta property="og:image" content="https://www.annesimonsson.se/konst/medium/anne-simonsson-konstverk-smalandstrienalen-rotvalta.jpg">
    <meta property="og:url" content="https://www.annesimonsson.se/">

	<meta name="keywords" content="anne simonsson, anne hamrin simonsson, öland, konst, artwork, artist, konstnär, vernisage, utställning, helg">
	<meta name="author" content="The website is made by Simon Kotlinski">
    <meta name="robots" content="index,follow">

	<meta itemprop="name" content="Anne Hamrin Simonsson">

    <link rel="canonical" href="https://www.annesimonsson.se<?= $_SERVER['REQUEST_URI'] ?>" />

	<!-- Mobile viewport optimized: j.mp/bplateviewport -->
	<meta name="viewport" content="width=device-width,initial-scale=1">

	<!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->
    <link rel="icon" href="<?=base_url('favicon.ico')?>" type="image/x-icon" />

	<!-- CSS: implied media=all -->
	<!-- CSS concatenated and minified via ant build script-->

	<!-- Add fancyBox -->
	<link rel="stylesheet" href="<?=base_url('statics/fancybox/source/jquery.fancybox.css?v=2.0.4')?>" type="text/css" media="screen" />
	<!-- Optionaly add button and/or thumbnail helpers -->
	<link rel="stylesheet" href="<?=base_url('statics/fancybox/source/helpers/jquery.fancybox-thumbs.css?v=2.0.4')?>" type="text/css" media="screen" />
	<link rel="stylesheet" href="<?=base_url('statics/fancybox/source/helpers/jquery.fancybox-buttons.css?v=2.0.4')?>" type="text/css" media="screen" />


	<link rel="stylesheet" href="<?=base_url('statics/css/styles.css?v=20251126')?>" />

	<!-- end CSS-->

	<!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

	<!-- All JavaScript at the bottom, except for Modernizr / Respond.
	Modernizr enables HTML5 elements & feature detects; Respond is a polyfill for min/max-width CSS3 Media Queries
	For optimal performance, use a custom Modernizr build: www.modernizr.com/download/ -->
	<script src="<?=base_url('statics/js/libs/modernizr-2.0.6.min.js')?>"></script>

	<script type="text/javascript">

		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-29440811-1']);
		_gaq.push(['_trackPageview']);

		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js ';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();

	</script>
</head>

<body>

<div id="container">
	<div id="sHeader">
		<div id="headspan">
			<div id="header" itemscope itemtype="http://schema.org/Person">
				<br/>
				<h1 itemprop="name">ANNE HAMRIN SIMONSSON</h1>
				<br/>
			</div>
			<?
			$list = array("news" => "NEWS",
				"album" => "ARTWORK",
				"about" => "ABOUT",
				"contact" => "CONTACT"
			);

			if( !isset($menu_item) ){
				$menu_item = '';
			}
			?>

			<div class="menu" itemprop="breadcrumb">
				<ul>
					<?
					$i = 0;
					foreach($list as $key=>$item){
						$special_attribute="";
						if($i == 0) {
							$special_attribute = 'style="float:left;"';
						}
						if($key == $menu_item){?>
							<li><h2><a itemprop="significantLinks" <?=$special_attribute?> href="<?=base_url($key)?>" class="current" id="<?=$i==0?'spec':''?>"><?=$item?></a></h2></li>
							<?} else {?>
							<li><h2><a itemprop="significantLinks" <?=$special_attribute?> href="<?=base_url($key)?>"  id="<?=$i==0?'spec':''?>"><?=$item?></a></h2></li>
							<?} $i++;?>
						<?}?>
				</ul>

			</div>
		<?		if($menu_item == 'album'){ ?>
			<div class="submenu menu" style="margin-top:10px;">
				<ul>
					<?foreach( $submenu as $key=>$submenu_item){
					if($selected_filter == $submenu_item['id']){
						echo '<li><h3><a href="'.base_url('album/'.$submenu_item['name']).'" class="current">'.strtoupper($submenu_item['name']).'</a></h3></li>';
					} else {
						echo '<li><h3><a href="'.base_url('album/'.$submenu_item['name']).'">'.strtoupper($submenu_item['name']).'</a></h3></li>';
					}
				}?>
				</ul>
			</div>
		<?}?>
			<hr/>
		</div>
	</div>

	<div id="sMain" role="main">
		<div id="bodyspan">
			<?if($this->session->userdata('logged_in')) {?>

			<div class="aboutHeader">Administration</div>
			<p>
				Du är inloggad som <?= $this->session->userdata('username');?>. Du kan använda den vanliga menyn
				för att komma till motsvarande admin-sidan.
			</p>

				<ul>
					<li>
						<a href="<?=base_url('startpage');?>">Edit startpage</a>
					</li>
					<li>
						<a href="<?=base_url('image_admin');?>">Image administration</a>
					</li>
					<li>
						<a href="<?=base_url('news');?>">News administration</a>
					</li>
					<li>
						<a href="<?=base_url('about');?>">About administration</a>
					</li>
					<li>
						<a href="<?=base_url('contact');?>">Contact administration</a>
					</li>
				</ul>

				<br />
			<a href="<?=site_url('/login/logout/')?>">Click here to logout.</a><br />

			<br/>
			<hr />
		<?}?>


