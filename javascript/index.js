/**
 Copyright (C) <2015>  Myjive Inc. <info@myjive.com>
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

(function ($) {

    $(document).ready(function () {
        //Input handler on change
        $('.form-control', '#myjive-sections').each(function () {
            $(this).bind('change', function () {
                saveOption({
                    group: $(this).data('group'),
                    option: $(this).attr('name'),
                    value: $(this).val()
                },
                {
                    el: $('i', '#' + $(this).attr('aria-describedby')),
                    success: 'text-success icon-ok-circled',
                    failure: 'text-danger icon-circled'
                });
            });
        });

        //Source validation handler
        $('.validate-source').each(function () {
            $(this).bind('click', function (event) {
                event.preventDefault();
                var btn = this;

                $.ajax(myjiveSocialFeed.baseurl, {
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: myjiveSocialFeed.ajaxKey,
                        type: 'validate',
                        source: $(this).data('source'),
                        _ajax_nonce: myjiveSocialFeed.nonce
                    },
                    beforeSend: function () {
                        $('i', btn).attr('class', 'animate-spin icon-spin');
                    },
                    success: function (response) {
                        if (response.status === true) {
                            triggerValidationSuccess(
                                    $(btn).data('source').toLowerCase()
                            );
                        } else {
                            triggerValidationFailure(
                                    $(btn).data('source').toLowerCase(),
                                    response.errors
                            );
                        }

                    },
                    error: function () {
                        triggerValidationFailure(['Application Error']);
                    },
                    complete: function () {
                        $('i', btn).attr('class', 'icon-arrows-cw');
                    }
                });
            });
        });

        //Source activation handler
        $('.toggle-status').each(function () {
            $(this).bind('click', function (event) {
                event.preventDefault();
                //if btn is green than social is in-active
                if ($(this).hasClass('btn-success')) {
                    toogleStatus($(this).data('group'), 1, $(this));
                } else {
                    toogleStatus($(this).data('group'), 0, $(this));
                }
            });
        });

        //Clear source cache
        $('.clear-cache').each(function () {
            $(this).bind('click', function (event) {
                event.preventDefault();
                
                var btn = $(this);

                $.ajax(myjiveSocialFeed.baseurl, {
                    method: 'POST',
                    dataType: 'json',
                    async: false, //Important. Make sure call ends before next cal
                    data: {
                        action: myjiveSocialFeed.ajaxKey,
                        type: 'clear-cache',
                        source: $(this).data('group'),
                        _ajax_nonce: myjiveSocialFeed.nonce
                    },
                    beforeSend: function () {
                        $('i', btn).attr('class', 'animate-spin icon-spin');
                    },
                    complete: function () {
                        $('i', btn).attr('class', 'icon-eraser');
                    }
                });
            });
        });
        
        //Helper addons
        $('.myjive-help-addon').each(function () {
            $(this).bind('click', function () {
                //show loader
                $('.myjive-meter', $(this).parent()).toggleClass(
                        'myjive-meter-change myjive-meter-close'
                );
        
                var target = $('#' + $(this).data('target'));
                //Toogle the help context
                if ($(this).hasClass('active')) { //close the help context
                    $(this).removeClass('active');
                    $(target).removeClass('open').addClass('closing');
                    setTimeout(function () {
                        $(target).removeClass('closing');
                    }, 500);
                } else { //open the help context
                    $(this).addClass('active');
                    $(target).addClass('open');
                }
            });
        });

        //Twitter specific fields
        initializeTwitter();

        //Youtube specific fields
        initializeYoutube();
        
        //initialize Instagram fields
        initializeInstagram();
    });

    /**
     * 
     * @param {type} source
     * @param {type} status
     * @param {type} btn
     * @returns {undefined}
     */
    function toogleStatus(source, status, btn) {
        $.ajax(myjiveSocialFeed.baseurl, {
            method: 'POST',
            dataType: 'json',
            async: false, //Important. Make sure call ends before next cal
            data: {
                action: myjiveSocialFeed.ajaxKey,
                type: 'save',
                group: source,
                option: 'active',
                value: status,
                _ajax_nonce: myjiveSocialFeed.nonce
            },
            beforeSend: function () {
                $('i', btn).attr('class', 'animate-spin icon-spin');
            },
            success: function (response) {
                if (response.status === true) {
                    if (status) {
                        //update button
                        $(btn).attr('class', 'btn btn-warning');
                        $(btn).empty().append($('<i/>', {
                            'class': 'icon-inactive'
                        })).append(' Deactivate');
                        //update indicator
                        $('i', '#' + $(btn).attr('aria-describedby')).attr(
                                'class', 'text-success icon-active'
                        );
                    } else {
                        $(btn).attr('class', 'btn btn-success');
                        $(btn).empty().append($('<i/>', {
                            'class': 'icon-active'
                        })).append(' Activate');
                        //update indicator
                        $('i', '#' + $(btn).attr('aria-describedby')).attr(
                                'class', 'text-muted icon-inactive'
                        );
                    }
                }
            }
        });
    }

    /**
     * 
     * @param {type} source
     * @param {type} errors
     * @returns {undefined}
     */
    function triggerValidationFailure(source, errors) {
        var message = $('<div/>', {'class' : 'myjive-verification-status'});
        var error   = $('<div/>', {'class' : 'myjive-error'});
        
        //add error indicator icon
        $(error).append('<i class="icon-cancel-circled"></i>');
        
        //add message
        $(error).append('<span>' + errors.join('; ') + '</span>');
        
        message.append(error);
        
        //create cancel button and append it
        var cancel = $('<span/>', {'class' : 'myjive-close-thin'}).bind('click', function () {
            $('.myjive-verification-status', '#' + source).remove();
        });
        
        message.append(cancel);

        $('.panel-body', '#' + source).append(message);
    }

    /**
     * 
     * @param {type} source
     * @returns {undefined}
     */
    function triggerValidationSuccess(source) {
        var message = $('<div/>', {'class' : 'myjive-verification-status'});
        message.append(
            '<span class="myjive-ok"><i class="icon-ok-circled"></i></span>'
        );

        $('.panel-body', '#' + source).append(message);
        
        //set timeout to remove it in 800 ms
        setTimeout(function() {
            $('.panel-body .myjive-verification-status', '#' + source).remove();
        }, 800);
    }

    /**
     * 
     * @returns {undefined}
     */
    function initializeTwitter() {
        $('a', '#twitter-query-type-list').bind('click', function () {
            saveOption({
                group: 'Twitter',
                option: 'queryType',
                value: $(this).data('query-type')
            }, {
                el: $('i', '#' + $(this).attr('aria-describedby')),
                success: 'text-success icon-' + $(this).data('query-type'),
                failure: 'text-danger icon-' + $(this).data('query-type')
            });
            $('#twitter-query').attr('placeholder', $(this).text()).val('');
        });
    }

    /**
     * 
     * @returns {undefined}
     */
    function initializeYoutube() {
        $('a', '#youtube-query-type-list').bind('click', function () {
            saveOption({
                group: 'Youtube',
                option: 'queryType',
                value: $(this).data('query-type')
            }, {
                el: $('i', '#' + $(this).attr('aria-describedby')),
                success: 'text-success icon-' + $(this).data('query-type'),
                failure: 'text-danger icon-' + $(this).data('query-type')
            });
            $('#youtube-query').attr('placeholder', $(this).text()).val('');
        });
    }
    
    /**
     * 
     * @returns {undefined}
     */
    function initializeInstagram() {
        $('a', '#instagram-query-type-list').bind('click', function () {
            saveOption({
                group: 'Instagram',
                option: 'queryType',
                value: $(this).data('query-type')
            }, {
                el: $('i', '#' + $(this).attr('aria-describedby')),
                success: 'text-success icon-' + $(this).data('query-type'),
                failure: 'text-danger icon-' + $(this).data('query-type')
            });
            $('#instagram-query').attr('placeholder', $(this).text()).val('');
        });
    }

    /**
     * Save option
     * 
     * @param {Object} input
     * @param {Object} indicator
     * 
     * @returns void
     */
    function saveOption(input, indicator) {
        $.ajax(myjiveSocialFeed.baseurl, {
            method: 'POST',
            dataType: 'json',
            async: false, //Important. Make sure call ends before next call
            data: {
                action: myjiveSocialFeed.ajaxKey,
                type: 'save',
                group: input.group,
                option: input.option,
                value: input.value,
                _ajax_nonce: myjiveSocialFeed.nonce
            },
            beforeSend: function () {
                $(indicator.el).attr('class', 'animate-spin icon-spin');
            },
            success: function (response) {
                if (response.status === true) {
                    $(indicator.el).attr('class', indicator.success);
                } else {
                    $(indicator.el).attr('class', indicator.failure);
                }

            },
            error: function () {
                $(indicator.el).attr('class', indicator.failure);
            }
        });
    }
    
})(jQuery);