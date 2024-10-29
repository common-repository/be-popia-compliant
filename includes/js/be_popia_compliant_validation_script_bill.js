jQuery(document).ready(function ($) {
    function ValidateBillSAID() {
        // First, clear any leftover error messages
        $('#billsaiderror p').remove();

        // Store the error div, to save typing
        var error = $('#billsaiderror');

        var idNumber = $('#billing_user_SAID').val();

        // Assume everything is correct and if it later turns out not to be, just set this to false
        var correct = true;

        if (idNumber.length !== 13 || !isNumber(idNumber)) {
            if(idNumber.length == 0 ) {
                correct = true;
            } else {
                error.append('<p>ID number does not appear to be authentic - Please provide your South African ID Number or alternatively use the other fileds if you do not have one.</p>');
                correct = false;
            }
        }

        // Get first 6 digits as a valid date
        var tempDate = new Date(idNumber.substring(0, 2), idNumber.substring(2, 4) - 1, idNumber.substring(4, 6));

        var id_date = tempDate.getDate();
        var id_month = tempDate.getMonth();
        var id_year = tempDate.getFullYear();

        var fullDate = id_date + "-" + (id_month + 1) + "-" + id_year;

        if (
            !(
                tempDate.getYear() == idNumber.substring(0, 2) &&
                id_month == idNumber.substring(2, 4) - 1 &&
                id_date == idNumber.substring(4, 6)
            )
        ) {
            if(idNumber.length == 0 ) {
                correct = true;
            } else {
                // error.append('<p>ID number does not appear to be authentic - date part not valid</p>');
                correct = false;
            }
        }

        // Get the gender
        var genderCode = idNumber.substring(6, 10);
        var gender = parseInt(genderCode) < 5000 ? "Female" : "Male";

        // Get country ID for citizenship
        var citizenship = parseInt(idNumber.substring(10, 11)) === 0 ? "Yes" : "No";

        // Apply Luhn formula for check-digits
        var tempTotal = 0;
        var checkSum = 0;
        var multiplier = 1;
        for (var i = 0; i < 13; ++i) {
            tempTotal = parseInt(idNumber.charAt(i)) * multiplier;
            if (tempTotal > 9) {
                tempTotal = parseInt(tempTotal.toString().charAt(0)) + parseInt(tempTotal.toString().charAt(1));
            }
            checkSum = checkSum + tempTotal;
            multiplier = multiplier % 2 === 0 ? 1 : 2;
        }
        if (checkSum % 10 !== 0) {
            if(idNumber.length == 0 ) {
                correct = true;
            } else {
                error.append('<p>&nbsp;That is not a valid ID Number</p>');
                // error.append('<p>ID number does not appear to be authentic - check digit is not valid</p>');
                correct = false;
            }
        }

        // If no error found, hide the error message
        if (correct) {
            error.css('display', 'none');
        }
        // Otherwise, show the error
        else {
            error.css('display', 'block');
            $('#billing_user_SAID').focus(); // Focus on the input field with the invalid ID
        }

        return correct;
    }

    function isNumber(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    // Attach validation to the blur event of the SAID input field
    $('#billing_user_SAID').blur(ValidateBillSAID);

    // Prevent user registration if validation fails
    $('form[name="checkout"]').submit(function (event) {
        if (!ValidateBillSAID()) {
            event.preventDefault(); // Prevent form submission
        }
    });
});