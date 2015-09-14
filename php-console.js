/*jslint browser: true */
/*global ace, jQuery */
/**
 * PHP Console
 *
 * A web-based php sandbox
 *
 * Copyright (C)
 * 2010, Jordi Boggiano <j.boggiano@seld.be>
 * 2015, Ondřej Hruška <ondra@ondrovo.com>
 *
 * Licensed under the new BSD License
 * See the LICENSE file for details
 *
 * Source on Github http://github.com/MightyPork/php-sandbox
 */
(function (require, $, ace) {
    "use strict";

    var updateStatusBar, handleSubmit, initializeAce, handleAjaxError, options, editor;

    options = {
        tabsize: 4,
        editor: 'editor'
    };

    /**
     * updates the text of the status bar
     */
    updateStatusBar = function (e) {
        var cursor_position = editor.getCursorPosition();
        $('.statusbar .position').text('Line: ' + (1 + cursor_position.row) + ', Column: ' + cursor_position.column);
    };

    /**
     * does an async request to eval the php code and displays the result
     */
    handleSubmit = function (e) {
        e.preventDefault();
        //$('div.output').html('<img src="loader.gif" class="loader" alt="" /> Loading ...'); /* loader sucks */

        // store session
        if (window.localStorage) {
            localStorage.setItem('phpCode', editor.getSession().getValue());
        }

        // eval server-side
        $.post('?js=1', { code: editor.getSession().getValue() }, function (res, status, jqXHR) {
            var mem = jqXHR.getResponseHeader("X-Memory-Usage") || "",
                rendertime = jqXHR.getResponseHeader("X-Rendertime") || "";

            if (mem || rendertime) {
                $('.statusbar .runtime-info').text('Memory usage: '+ mem + ' MB, Rendertime: ' + rendertime + 'ms');
            } else {
                $('.statusbar .runtime-info').text('');
            }

            if (res.match(/#end-php-console-output#$/)) {
                var result = res.substring(0, res.length - 24);
                $('div.output').text(result); // or html?
            } else {
                $('div.output').text(res + "\n\n*** Script ended unexpectedly. ***");
            }
        });
    };

    handleAjaxError = function (event, jqxhr, settings, exception) {
        $('div.output').html("<em>Error occured while posting your code.</em>");
    };

    initializeAce = function () {
        var PhpMode, code, storedCode;

        code = $('#' + options.editor).text();

        // reload last session
        if (window.localStorage && code.match(/(<\?php)?\s*/)) {
            storedCode = localStorage.getItem('phpCode');
            if (storedCode) {
                code = storedCode;
            }
        }

        $('#' + options.editor).replaceWith('<div id="' + options.editor + '" class="' + options.editor + '"></div>');
        $('#' + options.editor).text(code);

        editor = ace.edit(options.editor);

        editor.focus();
        editor.gotoLine(3, 0);
        editor.setTheme("ace/theme/monokai");
        editor.setOptions({
            showPrintMargin: false,
            fontSize: '18px',
            enableBasicAutocompletion: true,
            fontFamily: 'Source Code Pro'
        });

        // set mode
        PhpMode = require("ace/mode/php").Mode;
        editor.getSession().setMode(new PhpMode());

        // tab size
        if (options.tabsize) {
            editor.getSession().setTabSize(options.tabsize);
            editor.getSession().setUseSoftTabs(true);
        } else {
            editor.getSession().setUseSoftTabs(false);
        }

        // events
        editor.getSession().selection.on('changeCursor', updateStatusBar);

        // reset button
        if (window.localStorage) {
            $('.statusbar .reset').on('click', function (e) {
                editor.getSession().setValue('<?php\n\n');
                editor.focus();
                editor.gotoLine(3, 0);
                window.localStorage.setItem('phpCode', '');
                e.preventDefault();
            });
        }

        // commands
        editor.commands.addCommand({
            name: 'submitForm',
            bindKey: {
                win: 'Ctrl-Return|Alt-Return',
                mac: 'Command-Return|Alt-Return'
            },
            exec: function (editor) {
                $('form').submit();
            }
        });
    };

    $.console = function (settings) {
        $.extend(options, settings);

        $(function () {
            $(document).ready(initializeAce);
            $(document).ajaxError(handleAjaxError);

            $('form').submit(handleSubmit);
        });
    };
}(ace.require, jQuery, ace));
