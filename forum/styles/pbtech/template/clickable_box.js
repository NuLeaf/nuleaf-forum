/*
 * This short jQuery script makes each forum section box clickable after page has
 * loaded by entering the li.row, locating the forum section's link in the forum
 * title, and then changing window location to that link.
 * --
 * @author Wolfgang C. Strack
 */

(function($) {
  var $sectionBox = $('li.row.forum-section');
  $sectionBox.css('cursor', 'pointer');
  $sectionBox.click(function() {
    window.location = $(this).find('a.forumtitle').attr('href');
  });
})(jQuery);
