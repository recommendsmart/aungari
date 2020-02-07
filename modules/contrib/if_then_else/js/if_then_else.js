/**
 * @file
 * Javascript for IfThenElse module.
 */
(function($, Drupal) {

  function GoInFullscreen(element) {
    if (element.requestFullscreen)
      element.requestFullscreen();
    else if (element.mozRequestFullScreen)
      element.mozRequestFullScreen();
    else if (element.webkitRequestFullscreen)
      element.webkitRequestFullscreen();
    else if (element.msRequestFullscreen)
      element.msRequestFullscreen();
  }

  function GoOutFullscreen() {
    if (document.exitFullscreen)
      document.exitFullscreen();
    else if (document.mozCancelFullScreen)
      document.mozCancelFullScreen();
    else if (document.webkitExitFullscreen)
      document.webkitExitFullscreen();
    else if (document.msExitFullscreen)
      document.msExitFullscreen();
  }

  function IsFullScreenCurrently() {
    var full_screen_element = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement || null;

    if (full_screen_element === null)
      return false;
    else
      return true;
  }

  $("#edit-full-screen-button").on('click', function(e) {
    if (IsFullScreenCurrently())
      GoOutFullscreen();
    else
      GoInFullscreen($("#ifthenelse_form_wrapper").get(0));
    e.preventDefault();

  });

  $(document).on('fullscreenchange webkitfullscreenchange mozfullscreenchange MSFullscreenChange', function(e) {
    if (IsFullScreenCurrently()) {
      $("#edit-full-screen-button").attr('title','Disable Full Screen');
      $('#ifthenelse_form_wrapper').removeClass('disable').addClass('enable');
    } else {
      $("#edit-full-screen-button").attr('title','Enable Full Screen');
      $('#ifthenelse_form_wrapper').removeClass('enable').addClass('disable');
    }
  });

})(jQuery, Drupal);
