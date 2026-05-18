$(".delete_image_form").bind("submit", function () {
  if (imgId < 0) {
    return false;
  }
  $("#container_id_" + imgId).css('visibility', 'hidden');
  $("#container_id_" + imgId).css('height', '0px');
  $("#container_id_" + imgId).css('margin', '0px');
  $.ajax({
    type: "POST",
    cache: false,
    url: "<?=base_url('image_admin/delete')?>/" + imgId,
    data: $(this).serializeArray(),
    success: function (data) {
      $.fancybox(data);
    }
  });

  return false;
});
$(".rename_image_form").bind("submit", function () {
  var imgId = $(this).find('input[name="image_id"]').val();

  if (imgId < 0) {
    return false;
  }

  // Grundläggande fält
  var title = $('#rename_image_field_' + imgId).val();
  var alternate_name = $('#alternate_name_'+imgId).val();
  var caption = $('#rename_image_caption_' + imgId).val();
  var project = $('#project_' + imgId).val();
  var file_id = $('#rename_file_id_field_' + imgId).val();

  // Konstnärlig metadata
  var artform = $('#artform_' + imgId).val();
  var art_medium = $('#art_medium_' + imgId).val();
  var artwork_surface = $('#artwork_surface_' + imgId).val();
  var art_edition = $('#art_edition_' + imgId).val();
  var genre = $('#genre_' + imgId).val();
  var date_created = $('#date_created_' + imgId).val();

  // Mått
  var height_cm = $('#height_cm_' + imgId).val();
  var width_cm = $('#width_cm_' + imgId).val();
  var depth_cm = $('#depth_cm_' + imgId).val();

  // Plats & Foto
  var geo_location = $('#geo_location_' + imgId).val();
  var address_locality = $('#address_locality_' + imgId).val();
  var address_region = $('#address_region_' + imgId).val();
  var address_country = $('#address_country_' + imgId).val();
  var map_url = $('#map_url_' + imgId).val();
  var photographer_name = $('#photographer_name_' + imgId).val();

  // Validering av file_id
  if (!/^[a-z0-9\-]+$/.test(file_id)) {
    alert("File ID may only contain lowercase letters a-z, numbers 0-9, and hyphens (-).");
    return false;
  }

  var subName = title;
  if (subName.length > 16) {
    subName = title.substr(0, 13) + '...';
  }
  $('#admin_picture_' + imgId).html(subName);

  $.ajax({
    type: "POST",
    cache: false,
    url: "<?=base_url('image_admin/update')?>/" + imgId,
    // Inkludera alla nya variabler i data-objektet
    data: {
      title, alternate_name, file_id, caption, project,
      artform, art_medium, artwork_surface, art_edition, genre, date_created,
      height_cm, width_cm, depth_cm,
      geo_location, address_locality, address_region, address_country, map_url,
      photographer_name
    },
    success: function (data) {
      window.location.hash = 'container_id_' + imgId;
      $.fancybox(data);
    }
  });

  return false;
});
$(".delete_news_form").bind("submit", function () {
  if (newsId < 0) {
    return false;
  }
  $.ajax({
    type: "POST",
    cache: false,
    url: "<?=base_url('news/delete')?>/" + newsId,
    data: $(this).serializeArray(),
    success: function (data) {
      $.fancybox(data);
    }
  });

  return false;
});
$(".rename_news_form").bind("submit", function () {
  if (newsId < 0) {
    return false;
  }
  var newTitle = $('#rename_title_field_' + newsId).val();
  var newText = $('#rename_text_field_' + newsId).val();

  $.ajax({
    type: "POST",
    cache: false,
    url: "<?=base_url('news/update')?>/" + newsId,
    data: {title: newTitle, text: newText},
    success: function (data) {
      $.fancybox(data);
    }
  });

  return false;
});
$(".filter_image_form").bind("submit", function () {
  if (imgId < 0) {
    return false;
  }
  var newFilter = $('#filter_image_form_' + imgId + ' option:selected').val();
  $.ajax({
    type: "POST",
    cache: false,
    url: "<?=base_url('image_admin/setFilter')?>/" + imgId,
    data: {filter_id: newFilter},
    success: function (data) {
      $.fancybox(data);
    }
  });

  return false;
});

$(".order_image_form").bind("submit", function () {
  if (imgId < 0) {
    return false;
  }
  var newOrder = $('#order_image_field_' + imgId).val();
  $.ajax({
    type: "POST",
    cache: false,
    url: "<?=base_url('image_admin/setOrder')?>/" + imgId,
    data: {order: newOrder},
    success: function (data) {
      $.fancybox(data);
    }
  });

  return false;
});