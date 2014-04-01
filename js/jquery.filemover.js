/* filemover jQuery plugin v1.0
 * https://github.com/mturjak/filemover
 *
 * Copyright 2014, Martin Turjak
 * http://newtpond.com
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
;(function ( $ ) {

    $.fn.filemover = function( arg, callback ) {

      // default options
      var options = $.extend({
        dir:      arg || '',
        files:    Array(),
        console:  '.console',
        data:     'fname',
        callback: callback || defaultCallback,
        method:   'POST',
        ajaxUrl:  'server/php/index.php'
      }, arguments[0] || {} ),
      $out = new $.Deferred();

      // default callback function
      function defaultCallback( object, res) {
        $.each( res, function(i){
          $(options.console).append( "<p>"+this.message+"</p>" );
          if( this.done ) {
            $( object[i] ).remove();
          }
        });
      }

      // filename from object's data attribute
      this.each(function(){
        options.files.push( $( this ).data( options.data ));
      });

      // if any filenames passed make ajax call & passed callback
      if( options.files.length ) {
        var o = this;
        $out = $.ajax({
          type: options.method,
          url: options.ajaxUrl,
          dataType: 'json',
          data: { 'dir' : options.dir, 'file' : options.files }
        }).done( function( res ) {
          if( typeof options.callback == 'function' ) {
            options.callback( o, res );
          }
        });
      }

      // return deffered object
      return $out;
    }

}( jQuery ));