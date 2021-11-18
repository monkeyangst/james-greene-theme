// Add your custom JS here.

// Gallery
jQuery(document).ready(function($) {
  $('.tiled-gallery__item').each(function(i) {
    var title = $(this).find('img').attr('data-image-title');
    var stuff = '<caption class="tile-title">' + title + '</caption>';
    $(this).append(stuff);
    //console.log(stuff);

    $(this).on('mouseenter mouseleave', function(e) {
      $(this).find('.tile-title').fadeToggle(200);
    });
  });

});
