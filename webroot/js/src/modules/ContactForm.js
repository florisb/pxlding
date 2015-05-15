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



    /**
     * Catch submission of form, do ajax call instead and show errors in form
     */
    var _initForm = function() {

        var that = this;
        var data = {};

        // make button do a submit
        $(_formId + ' .button-submit').click(function(e) {
            e.preventDefault();

            $(_formId).submit();
        });

        // catch form submit and send data
        $(_formId).submit(function(e) {
            //e.preventDefault();

            _clearErrors(this);

            data.contact = 1;
            data.name    = $(this).find('input[name=name]').val();
            data.email   = $(this).find('input[name=email]').val();
            data.website = $(this).find('input[name=website]').val();
            data.message = $(this).find('input[name=message]').val();

            _doFormSubmit(this, data);

            return false;
        });

        // clear error state on user input
        $(_formId).find('input[type=text], input[type=email], textarea').change(function() {
            _clearInvalidStateForField($(this));
        }).typing({
            stop: function (event, $elem) {
                _clearInvalidStateForField($elem);
            }
        });

    };


    /**
     * Simple removal of error class state
     */
    var _clearInvalidStateForField = function (element) {
        element.removeClass('error');
    };


    /**
     * Submit data from form through ajax
     *
     * @param  {string} formElement  element id name of the form container we're sending from
     * @param  {object} data        data to send
     */
    var _doFormSubmit = function(formElement, data) {
        var that = this;

        data.ajax = 1;

        $.ajax({
            type     : 'POST',
            'url'    : $('base').attr('href') + $('html').attr('lang') + '/' + 'home/contact',
            'data'   : data,
            dataType : 'json',
            // dataType : 'html',

            success: function(data, status) {

                // console.log(data);
                // console.log(status);

                // check result: show error or redirect to success page
                if (data.hasOwnProperty('result') && data.result) {
                    _showMessage(data.message);
                    return;
                }

                // should never happen, fallback
                if (!data.hasOwnProperty('errors')) {
                    return;
                }

                // show errors
                _showErrors(formElement, data.errors);
            },

            error: function() {

                // show generic error
                //window.location = $('base').attr('href') + 'newsletter/failed';
                _showErrors(formElement, {
                    'general': 'Error, could not sumbit. Please try again.'
                });
            }
        });

    };

    /**
     * Remove all the errors and error state from the form
     *
     * @param  {string} formElement     element
     */
    var _clearErrors = function(formElement) {

        $(formElement).find('input[type=text]').removeClass('error');
        $(formElement).find('input[type=email]').removeClass('error');
        $(formElement).find('.form-errors').text('').slideUp('fast');

    };

    /**
     * Show errors and set error state for input fields
     * @param  {string} formElement
     * @param  {object} errors
     */
    var _showErrors = function(formElement, errors) {

        var text = '<p>';

        $.each(errors, function(key, value) {
            // text += value + '<br>';
            console.log(key);
            console.log(value);
            // set error state for field if known
            var inputElem = $(formElement).find('input[name=' + key + ']');

            if (inputElem && inputElem.length) {
                $(inputElem).addClass('error');
            }
        });

        // just a single message for now

        text += 'Controleer de gemarkeerde velden,<br>er is iets loos.';

        text += '</p>';

        $(formElement).find('.form-errors').html(text).slideDown('fast');

    };

    /**
     * Show the thanks message
     */
    var _showMessage = function() {
        $(_formId).slideUp('fast');
        $(_formMessageId).slideDown('fast');
    };


})();