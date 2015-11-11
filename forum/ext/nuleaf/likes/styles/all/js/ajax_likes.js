'user strict';

function like(post_id)
{
  (function($) // Avoid conflicts with other libraries
  {
    $(document).ready(function()
    {
      var span = $(this).find('#likes-count-' + post_id);
      var a = span.next('.like-icon');
      var url = a.attr('href').slice(1);

      $.post(url, null, function(res)
      {
        if (res.error)
        {
          alert(res.error);
          return;
        }
        
        // Update the count after post has been liked/unliked.
        span.text(res.likes_count);

        // Change class to display unlike css.
        // TODO: How to dynamically change title and span text to phpbb language entries.
        if(res.liked)
        {
          a.addClass('unlike');
          a.attr('title', res.UNLIKE_POST);
          a.find('span').text(res.UNLIKE_BUTTON);
        }
        else
        {
          a.removeClass('unlike');
          a.attr('title', res.LIKE_POST);
          a.find('span').text(res.LIKE_BUTTON);
        }
      });
    });
  })(jQuery);
}