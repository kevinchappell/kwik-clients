jQuery(document).ready(function($) {

  $('#sortable-table tbody').sortable({
    axis: 'y',
    handle: '.column-order img',
    placeholder: 'ui-state-highlight',
    forcePlaceholderSize: true,
    update: function(event, ui) {
      var theOrder = $(this).sortable('toArray');

      var data = {
        action: 'clients_update_post_order',
        postType: $(this).attr('data-post-type'),
        order: theOrder
      };

      $.post(ajaxurl, data);
    }
  }).disableSelection();

  function posts_autocomplete() {
    $('#as_meta #post_link_title').autocomplete({
      delay: 333,
      source: $('#wp-admin-bar-site-name a').attr('href') + "wp-content/themes/openpower/utils/get_posts.php",
      select: function(event, ui) {
        var element = $(this);
        element.siblings('#post_link_id').val(ui.item.id);
      },
      minLength: 3,
      messages: {
        noResults: null,
        results: function() {}
      }
    });
  }

  posts_autocomplete();



});
