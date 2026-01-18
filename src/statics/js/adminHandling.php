
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
      var imgId = $(this).find('input[name="image_id"]').val(); // Get ID from hidden field

      if (imgId < 0) {
        return false;
      }
			var title = $('#rename_image_field_'+imgId).val();
			var caption = $('#rename_image_caption_'+imgId).val();
			var project = $('#project_'+imgId).val();
			var geo_location = $('#geo_location_'+imgId).val();
      var file_id = $('#rename_file_id_field_'+imgId).val();
      // Validate fileId: only a-z, numbers and -
      if (!/^[a-z0-9\-]+$/.test(file_id)) {
        alert("File ID may only contain lowercase letters a-z, numbers 0-9, and hyphens (-).");
        return false;
      }
      var subName = title;
			if(subName.length > 16){
				subName = title.substr(0, 13)+'...';
			}
			$('#admin_picture_'+imgId).html(subName);

      $.ajax({
        type: "POST",
        cache: false,
        url: "<?=base_url('image_admin/update')?>/"+imgId,
        data: { title, file_id, caption, project, geo_location },
        success: function(data) {
          window.location.hash = 'container_id_' + imgId;
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