/**
 * backend.js
 *
 * A few initializers for different backend widgets
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

window.newjennysplace = window.newjennysplace || {};
window.newjennysplace.backend = window.newjennysplace.backend || {};
window.newjennysplace.backend.ckEditor = window.newjennysplace.backend.ckEditor || {};

if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
    CKEDITOR.tools.enableHtml5Elements( document );

CKEDITOR.config.height = 250;
CKEDITOR.config.width = 'auto';

window.newjennysplace.backend.ckEditor.init = ( function() {
    var wysiwygareaAvailable = isWysiwygareaAvailable(),
        isBBCodeBuiltIn = !!CKEDITOR.plugins.get( 'bbcode' );

    return function() {
        var editorElement = CKEDITOR.document.getById( 'editor' );

        // :(((
        if ( isBBCodeBuiltIn ) {
            editorElement.setHtml(
                'Hello world!\n\n' +
                'I\'m an instance of [url=http://ckeditor.com]CKEditor[/url].'
            );
        }

        // Depending on the wysiwygare plugin availability initialize classic or inline editor.
        if ( wysiwygareaAvailable ) {
            CKEDITOR.replace( 'editor' );
        } else {
            editorElement.setAttribute( 'contenteditable', 'true' );
            CKEDITOR.inline( 'editor' );

            // TODO we can consider displaying some info box that
            // without wysiwygarea the classic editor may not work.
        }
    };

    function isWysiwygareaAvailable() {
        // If in development mode, then the wysiwygarea must be available.
        // Split REV into two strings so builder does not replace it :D.
        if ( CKEDITOR.revision == ( '%RE' + 'V%' ) ) {
            return true;
        }

        return !!CKEDITOR.plugins.get( 'wysiwygarea' );
    }
} )();

// Initialize other elements
$(document).ready(function ()
{
    // Initialize date pickers
    $('.ui-datepicker').datepicker();

    // Initialize list check boxes
    $("body").delegate('li.list-group-item-check', 'click', function (e) {
        $(this).find("input:checkbox").click();
    });
});
