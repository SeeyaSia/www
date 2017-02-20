/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function ($, Drupal) {

  /**
   * Use this behavior as a template for custom Javascript.
   */
  Drupal.behaviors.exampleBehavior = {
    attach: function (context, settings) {
      var $button = document.querySelector('.button');
      $button.addEventListener('click', function() {
        var duration = 0.6,
            delay = 0.08;
        TweenMax.to($button, duration, {scaleY: 1.1, ease: Expo.easeOut});
        TweenMax.to($button, duration, {scaleX: 1.1, scaleY: 1, ease: Back.easeOut, easeParams: [3], delay: delay});
        TweenMax.to($button, duration * 1.25, {scaleX: 1, scaleY: 1, ease: Back.easeOut, easeParams: [6], delay: delay * 3 });
      });
    }
  };

})(jQuery, Drupal);
