/**
 * Global settings shared for all scripts
 */
module.exports = {

    // breakpoints used for responsivity (these should correspond to media queries)
    breakPoints: {
        desktop : parseInt( $('body').attr('data-width-desktop') , 10),
        tablet  : parseInt( $('body').attr('data-width-tablet') , 10),
        phone   : parseInt( $('body').attr('data-width-phone') , 10),
        tiny    : 320
    },

    // default margin used in CSS
    defaultMargin: 20
};
