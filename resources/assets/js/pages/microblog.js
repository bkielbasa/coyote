$(function () {
    'use strict';

    $(document).ajaxError(function(event, jqxhr) {
        var error;

        if (typeof jqxhr.responseJSON.error !== 'undefined') {
            error = jqxhr.responseJSON.error;
        } else if (typeof jqxhr.responseJSON.text !== 'undefined') {
            error = jqxhr.responseJSON.text;
        }

        if (error) {
            $('#alert').modal('show').find('.modal-body').text(error);
        }
    });

    // zawartosc tresci wpisow
    // dane do tego obiektu zapisywane sa w momencie klikniecia przycisku "Edytuj"
    var entries = {};
    // zawartosc komentarzy
    // dane do tego boiektu zapisywane sa w momencie edycji komentarza. jezeli user zrezygnuje z edycji
    // to przywracamy HTML-a z tego obiektu
    var comments = {};
    var timeoutId;

    var Thumbs =
    {
        click: function () {
            var count = parseInt($(this).data('count'));
            var $this = $(this);

            $this.addClass('loader').text('Proszę czekać...');

            $.post($this.attr('href'), function (data) {
                count = parseInt(data.count);
                $this.data('count', count);

                if (!$this.hasClass('thumbs-on')) {
                    $this.next('.btn-subscribe:not(.subscribe-on)').click(); // po doceneniu wpisu automatycznie go obserwujemy
                }

                $this.toggleClass('thumbs-on');
            })
                .complete(function () {
                    $this.removeClass('loader');
                    $this.text(count + ' ' + declination(count, ['głos', 'głosy', 'głosów']));

                    // jezeli wpis jest w sekcji "popularne wpisy" to tam tez nalezy oznaczyc, ze
                    // wpis jest "lubiany"
                    $('a[href="' + $this.attr('href') + '"]').not($this).toggleClass('thumbs-on', $this.hasClass('thumbs-on')).text($this.text());
                });

            return false;
        },
        enter: function () {
            var count = parseInt($(this).data('count'));

            if (count > 0) {
                var $this = $(this);

                if (typeof $this.attr('title') === 'undefined' || $this.attr('title') === '') {
                    timeoutId = setTimeout(function() {
                        $.get($this.attr('href'), function(html) {
                            $this.attr('title', html);

                            if (html.length) {
                                var count = html.split("\n").length;

                                $this.attr('title', html.replace(/\n/g, '<br />'))
                                    .data('count', count)
                                    .text(count + ' ' + declination(count, ['głos', 'głosy', 'głosów']))
                                    .tooltip({html: true, trigger: 'hover'})
                                    .tooltip('show');
                            }
                        });

                    }, 500);
                }
            }

            $(this).off('mouseenter');
        },
        leave: function () {
            clearTimeout(timeoutId);
        }
    };

    $('#microblog')
        .on('click', '.btn-reply', function () {
            $(this).parent().next('.microblog-comments').find('input').focus();
        })
        .on('click', '.btn-subscribe', function () {
            var $this = $(this);

            $.post($this.attr('href'), function () {
                $this.toggleClass('subscribe-on');
            });

            return false;
        })
        .on('click', '.btn-thumbs, .btn-sm-thumbs', Thumbs.click)
        .on('mouseenter', '.btn-thumbs, .btn-sm-thumbs', Thumbs.enter)
        .on('mouseleave', '.btn-thumbs, .btn-sm-thumbs', Thumbs.leave)
        .on('click', '.btn-edit', function (e) {
            var $this = $(this);
            var entryText = $('#entry-' + $this.data('id')).find('.microblog-text');

            if (typeof entries[$this.data('id')] === 'undefined') {
                $.get($this.attr('href'), function (html) {
                    entries[$this.data('id')] = entryText.html();
                    entryText.html(html);

                    var $form = initForm($('.microblog-submit', entryText));

                    $form.unbind('submit').submit(function() {
                        var data = $form.serialize();
                        $(':input', $form).attr('disabled', 'disabled');

                        $.post($form.attr('action'), data, function(html) {
                            entryText.html(html);
                            delete entries[$this.data('id')];
                        })
                            .always(function() {
                                $(':input', $form).removeAttr('disabled');
                            });

                        return false;
                    });
                });
            } else {
                entryText.html(entries[$this.data('id')]);
                delete entries[$this.data('id')];
            }

            e.preventDefault();
        })
        .on('click', '.btn-remove', function () {
            var $this = $(this);

            $('#confirm').modal('show').one('click', '.danger', function() {
                $.post($this.attr('href'), function() {
                    $('#entry-' + $this.data('id')).fadeOut(500);
                });

                $('#confirm').modal('hide');
                return false;
            });

            return false;
        })
        .on('focus', '.comment-submit input', function() {
            if (typeof $(this).data('prompt') === 'undefined') {
                $(this).prompt().data('prompt', 'yes');
            }
        })
        .on('submit', '.comment-submit', function() {
            var $form = $(this);
            var $input = $('input[type="text"]', $form);
            var data = $form.serialize();

            $input.attr('disabled', 'disabled');

            $.post($form.attr('action'), data, function(json) {
                $(json.html).hide().insertBefore($form).fadeIn(800);
                $input.val('');

                if (json.subscribe) {
                    $('#entry-' + $('input[name="parent_id"]', $form).val()).find('.btn-subscribe').addClass('subscribe-on');
                }
            })
            .always(function() {
                $input.removeAttr('disabled');
            });

            return false;
        })
        .on('click', '.btn-sm-edit', function(e) {
            var $this = $(this);
            var commentText = $('#comment-' + $this.data('id')).find('.inline-edit');

            var cancel = function() {
                commentText.html(comments[$this.data('id')]);
                delete comments[$this.data('id')];
            };

            if (typeof comments[$this.data('id')] === 'undefined') {
                $.get($this.attr('href'), function(text) {
                    comments[$this.data('id')] = commentText.html();
                    commentText.html('');

                    var $form = $('<form>');
                    var $input = $('<input>', {'value': text, 'class': 'form-control', 'name': 'text', 'autocomplete': 'off', 'data-prompt-url': commentText.data('prompt-url')})
                        .keydown(function(e) {
                            if (e.keyCode === 27) {
                                cancel();
                            }
                        })
                        .appendTo($form);

                    $form.submit(function() {
                        var data = $form.serialize();
                        $input.attr('disabled', 'disabled');

                        $.post($this.attr('href'), data, function(json) {
                            $('#comment-' + $this.data('id')).replaceWith(json.html);
                            delete comments[$this.data('id')];
                        })
                            .always(function() {
                                $input.removeAttr('disabled');
                            });

                        return false;
                    });

                    $form.appendTo(commentText);
                    $input.inputFocus().prompt();
                });
            } else {
                cancel();
            }

            e.preventDefault();
        })
        .on('click', '.btn-sm-remove', function() {
            var $this = $(this);

            $('#confirm').modal('show').one('click', '.danger', function() {
                $.post($this.attr('href'), function() {
                    $('#comment-' + $this.data('id')).fadeOut(500);
                });

                $('#confirm').modal('hide');
                return false;
            });

            return false;
        })
        .on('click', '.show-all a', function() {
            var $this = $(this);
            $this.text('Proszę czekać...');

            $.get($this.attr('href'), function(html) {
                $this.parent().replaceWith(html);
            });

            return false;
        })
        .on('click', 'a[data-toggle="lightbox"]', function(e) {
            e.preventDefault();
            $(this).ekkoLightbox();
        })
        .on('click', '.read-more', function()
        {
            $(this).prev().css('max-height', '9999px').children('.microblog-gradient').remove();
            $(this).remove();
        });

    function initForm($form) {

        var removeThumbnail = function () {
            $(this).parent().parent().remove();
        };

        function add(data) {
            var thumbnail = $('.thumbnail:last', $form);

            $('.spinner', thumbnail).remove();
            $('img', thumbnail).attr('src', data.url);

            $('<div>', {'class': 'btn-flush'}).html('<i class="fa fa-remove fa-2x"></i>').click(removeThumbnail).appendTo(thumbnail);
            $('<input type="hidden" name="thumbnail[]" value="' + data.name + '">').appendTo(thumbnail);
        }

        if (jQuery.fn.pasteImage) {
            $('textarea', $form).pasteImage(function (textarea, result) {
                    $.get(uploadUrl, function (tmpl) {
                        $('.thumbnails', $form).append(tmpl);
                        add(result);
                    });
                })
                .prompt()
                .fastSubmit()
                .autogrow()
                .inputFocus();
        }

        $form.on('click', '.btn-flush', removeThumbnail)
            .submit(function () {
                var data = $form.serialize();
                $(':input', $form).attr('disabled', 'disabled');

                $.post($form.attr('action'), data, function(html) {
                    $(html).hide().insertAfter('nav.text-center').fadeIn(900);
                    $('textarea', $form).val('').trigger('keydown');
                    $('.thumbnails', $form).html('');
                })
                    .always(function() {
                        $(':input', $form).removeAttr('disabled');
                    });

                return false;
            })
            .on('click', '.btn-cancel', function () {
                var id = parseInt($(this).data('id'));
                $('#entry-' + id).find('.microblog-text').html(entries[id]);

                delete entries[id];
                return false;
            })
            .delegate('#btn-upload', 'click', function () {
                $('.input-file', $form).click();
            })
            .delegate('.input-file', 'change', function () {
                var file = this.files[0];

                if (file.type !== 'image/png' && file.type !== 'image/jpg' && file.type !== 'image/gif' && file.type !== 'image/jpeg') {
                    $('#alert').modal('show');
                    $('.modal-body').text('Format pliku jest nieprawidłowy. Załącznik musi być zdjęciem JPG, PNG lub GIF');
                }
                else {
                    var formData = new FormData($form[0]);

                    $.get(uploadUrl, function(tmpl) {
                        $('.thumbnails', $form).append(tmpl);

                        $.ajax({
                            url: uploadUrl,
                            type: 'POST',
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (data) {
                                add(data);
                            },
                            error: function (err) {
                                $('#alert').modal('show');

                                if (typeof err.responseJSON !== 'undefined') {
                                    $('.modal-body').text(err.responseJSON.photo[0]);
                                }

                                $('.thumbnail:last', $form).remove();
                            }
                        }, 'json');
                    });
                }
            });

        return $form;
    }

    initForm($('.microblog-submit'));

    $(window).load(function() {
        $('.microblog-text').each(function() {
            if ($(this).height() > 305) {
                $(this).css('max-height', '305px').append('<div class="microblog-gradient"></div>');
                $('<a class="read-more" href="javascript:">Zobacz całość</a>').insertAfter(this);
            }
        });
    });

    if ('onhashchange' in window) {
        var onHashChange = function () {
            var hash = window.location.hash;

            if (hash.substring(1, 6) === 'entry' || hash.substring(1, 8) === 'comment') {
                var object = $(hash);
                var panel = object.find('.panel');

                if (panel.length) {
                    object = panel;
                }

                object.css('background-color', '#FFDCA5');
                $('#container-fluid').one('mousemove', function () {
                    object.animate({backgroundColor: '#FFF'}, 1500);
                });
            }
        };

        window.onhashchange = onHashChange;
        onHashChange();
    }
});