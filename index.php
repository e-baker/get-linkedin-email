<?php

require_once 'linkedin-helper.php';

if( $_GET ) { // Did we receive anything with the page request?

    if( state_matches() ) { // Check to see if state is set & matches what we sent.

        if( $_GET['error'] ){ // Check for an error message

            add_error( $_GET['error_description'] ); // Log & Display the error

        } else { // No errors 

            $email = get_email_address();
            echo $email;

        }
    } else { // The state code received doesn't match what we sent

        add_error('The state code did not match. You may be the victim of a CSRF attack.' );
    }

    print_errors();

} else { // There weren't any URL params so we'll need to show the link

    show_login();
    
} 
