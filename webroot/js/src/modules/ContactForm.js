/**
 * Contact Form
 *
 * not a class; self-instantiating module
 */
module.exports = (function() {
    "use strict";

    // initialize everything
    $(function() {

        _initForm();
    });


    /*
     * PRIVATE
     */

    var _formId        = '#contact-form';
    var _formMessageId = '#contact-form-thanks';





    var _initForm = function() {

        $(_formId + ' .button-submit').click(function(e) {
            e.preventDefault();

            $(_formId).submit();
        });

    };


})();