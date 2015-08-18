/*
 * This short jQuery script makes each forum topic box clickable after page has
 * loaded by entering the li.row, locating the forum topic's link in the forum
 * title, and then changing window location to that link.
 * --
 * @author Wolfgang C. Strack
 */

(function($) {
  var $topicBox = $('li.row');
  $topicBox.css('cursor', 'pointer');
  $topicBox.click(function() {
    window.location = $(this).find('a.forumtitle').attr('href');
  });
})(jQuery);
