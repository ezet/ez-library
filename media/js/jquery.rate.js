
/**
 * A simplified rating system for jQuery
 *
 * @author     Lars Kristian Dahl <http://www.krisd.com>
 * @copyright  Copyright (c) 2011 Lars Kristian Dahl <http://www.krisd.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @version    SVN: $Id$
 */

(function($) {

    $.fn.rate = function(settings) {
        options = $.extend({}, $.fn.rate.defaults, settings);

        if (this.length == 0) {
            debug('Selector invalid or missing!');
            return this;
        } else if (this.length > 1) {
            return this.each(function() {
                $.fn.rate.apply($(this), [settings]);
            });
        }

        if (options.number > 20) {
            options.number = 20;
        } else if (options.number < 0) {
            options.number = 0;
        }

        if (options.path.substring(options.path.length - 1, options.path.length) != '/') {
            options.path += '/';
        }

        $global = $(this);

        $global.data('options', options);

        var id			= this.attr('id'),
        start		= 0,
        starFile	= options.starOn,
        hint		= '',
        width		= (options.width) ? options.width : (options.number * options.size + options.number * 4);

        if (id == '') {
            id = 'rate-' + $global.index();
            $global.attr('id', id);
        }

        if (!isNaN(options.start) && options.start > 0) {
            start = (options.start > options.number) ? options.number : options.start;
        }

        for (var i = 1; i <= options.number; i++) {
            starFile = (start >= i) ? options.starOn : options.starOff;

            hint = (i <= options.hintList.length && options.hintList[i - 1] !== null) ? options.hintList[i - 1] : i;

            $global
            .append('<img id="' + id + '-' + i + '" src="' + options.path + starFile + '" alt="' + i + '" title="' + hint + '" class="' + id + '"/>')
            .append((i < options.number) ? '&nbsp;' : '');
        }


        $('<input/>', {
            id:		id + '-score',
            type:	'hidden',
            name:	options.scoreName
        })
        .appendTo($global).val(start);

        if (!options.readOnly) {
            if (options.cancel) {
                var star	= $('img.' + id),
                cancel	= '<img src="' + options.path + options.cancelOff + '" alt="x" title="' + options.cancelHint + '" class="button-cancel"/>',
                opt		= options,
                $this	= $global;

                if (opt.cancelPlace == 'left') {
                    $global.prepend(cancel + '&nbsp;');
                } else {
                    $global.append('&nbsp;').append(cancel);
                }

                $('#' + id + ' img.button-cancel').mouseenter(function() {
                    $(this).attr('src', opt.path + opt.cancelOn);
                    star.attr('src', opt.path + opt.starOff);
                }).mouseleave(function() {
                    $(this).attr('src', opt.path + opt.cancelOff);
                    star.mouseout();
                }).click(function(evt) {
                    $('input#' + id + '-score').val(0);

                    if (opt.click) {
                        opt.click.apply($this, [0, evt]);
                    }
                });

                $global.css('width', width + options.size + 4);
            } else {
                $global.css('width', width);
            }

            $global.css('cursor', 'pointer');
            bindAll($global, options);
        } else {
            $global.css('cursor', 'default');
            fixHint($global, start, options);
        }

        return $global;
    };
	
    function bindAll(context, options) {
        var id		= context.attr('id'),
        score	= $('input#' + id + '-score'),
        qtyStar	= $('img.' + id).length;

        // context.
        $('#' + id).mouseleave(function() {
            initialize(context, score.val(), options);
        });

        $('img.' + id).mousemove(function(e) {
            fillStar(id, this.alt, options);

            if (options.half) {
                var percent = parseFloat(((e.pageX - $(this).offset().left) / options.size).toFixed(1));
                percent = (percent >= 0 && percent < 0.5) ? 0.5 : 1;

                context.data('score', parseFloat(this.alt) + percent - 1);

                splitStar(context, context.data('score'), options);
            } else {
                fillStar(id, this.alt, options);
            }
        }).click(function(evt) {
            score.val(options.half ? context.data('score') : this.alt);

            if (options.click) {
                options.click.apply(context, [score.val(), evt]);
            }
        });
    }

    function getContext(value, idOrClass, name) {
        var context = $global;

        if (idOrClass) {
            if (idOrClass.indexOf('.') >= 0) {
                var idEach;

                return $(idOrClass).each(function() {
                    idEach = '#' + $(this).attr('id');

                    if (name == 'start') {
                        $.fn.rate.start(value, idEach);
                    } else if (name == 'click') {
                        $.fn.rate.click(value, idEach);
                    } else if (name == 'readOnly') {
                        $.fn.rate.readOnly(value, idEach);
                    }
                });
            }

            context	= $(idOrClass);

            if (!context.length) {
                debug('"' + idOrClass + '" is a invalid identifier for the public funtion $.fn.rate.' + name + '().');
                return null;
            }
        }

        return context;
    }

    function debug(message) {
        if (window.console && window.console.log) {
            window.console.log(message);
        }
    }

    function fillStar(id, score, options) {
        var qtyStar	= $('img.' + id).length,
        item	= 0,
        range	= 0,
        star,
        starOn;

        for (var i = 1; i <= qtyStar; i++) {
            star = $('img#' + id + '-' + i);

            if (i <= score) {
                if (options.iconRange && options.iconRange.length > item) {

                    starOn = options.iconRange[item][0];
                    range = options.iconRange[item][1];

                    if (i <= range) {
                        star.attr('src', options.path + starOn);
                    }

                    if (i == range) {
                        item++;
                    }
                } else {
                    star.attr('src', options.path + options.starOn);
                }
            } else {
                star.attr('src', options.path + options.starOff);
            }
        }
    }

    function fixHint(context, score, options) {
        if (score != 0) {
            score = parseInt(score);
            hint = (score > 0 && options.number <= options.hintList.length && options.hintList[score - 1] !== null) ? options.hintList[score - 1] : score;
        } else {
            hint = options.noRatedMsg;
        }

        $('#' + context.attr('id')).attr('title', hint).children('img').attr('title', hint);
    }

    function initialize(context, score, options) {
        var id = context.attr('id');

        if (score < 0 || isNaN(score)) {
            score = 0;
        } else if (score > options.number) {
            score = options.number;
        }

        $('input#' + id + '-score').val(score);

        fillStar(id, score, options);

        if (options.half) {
            splitStar(context, score, options);
        }

        if (options.readOnly || context.css('cursor') == 'default') {
            fixHint(context, score, options);
        }
    }

    function splitStar(context, score, options) {
        var id		= context.attr('id'),
        rounded	= Math.ceil(score),
        diff	= (rounded - score).toFixed(1);

        if (diff >= 0.3 && diff <= 0.7) {
            rounded = rounded - 0.5;
            $('img#' + id + '-' + Math.ceil(rounded)).attr('src', options.path + options.starHalf);
        } else if (diff >= 0.8) {
            rounded--;
        } else {
            $('img#' + id + '-' + rounded).attr('src', options.path + options.starOn);
        }
    }

    $.fn.rate.defaults = {
        cancel:			false,
        cancelHint:		'cancel this rating!',
        cancelOff:		'cancel-off.png',
        cancelOn:		'cancel-on.png',
        cancelPlace:            'left',
        click:			null,
        half:			false,
        hintList:		['bad', 'poor', 'neutral', 'good', 'marvellous'],
        noRatedMsg:		'not rated yet',
        number:			5,
        path:			'/media/images/rate/',
        iconRange:		[],
        readOnly:		false,
        scoreName:		'score',
        size:			16,
        starHalf:		'star-half.png',
        starOff:		'star-off.png',
        starOn:			'star-on.png',
        start:			0,
        width:			null
    }

})(jQuery);