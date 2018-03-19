<?php

$GLOBALS['redirect_uri'] = ""; // This is callback URL for LinkedIn - make sure it matched what's in your LinkedIn App
$GLOBALS['state'] = ''; // This is a random string that you create to make sure you're not a victim of a CSRF attack
$GLOBALS['client_id'] = ''; // The client id from your LinkedIn App settings
$GLOBALS['client_secret'] = ''; // The client secret from your LinkedIn App settings
$GLOBALS['errors'] = array();
$GLOBALS['access_token'] = null;

function add_error( $error )
{
    array_push( $GLOBALS['errors'], $error );
    debug_console( $error );
}

function debug_console( $message )
{
    echo '<script>console.log("', $message, '");</script>';
}

function get_access_token()
{
    // Use the code in a POST request to 'https://www.linkedin.com/oauth/v2/accessToken' to
    // get an actual access token or error, whichever the case may be.
    $response = json_decode( httpPost( 
        'https://www.linkedin.com/oauth/v2/accessToken',
        array(
            'grant_type'    => 'authorization_code',
            'code'          => $_GET['code'],
            'redirect_uri'  => $GLOBALS['redirect_uri'],
            'client_id'     => $GLOBALS['client_id'],
            'client_secret' => $GLOBALS['client_secret']
        )
    ), false );

    // Check for an error or access token, whichever we get.
    $response->error ? add_error( $response->error_description ) : $GLOBALS['access_token'] = $response->access_token;
    
}

function get_email_address()
{
    get_access_token();

    // Ask the API 'https://api.linkedin.com/v1/people/~:(email-address)?format=json'
    $details = json_decode( httpGet( 'https://api.linkedin.com/v1/people/~:(email-address)?format=json', $GLOBALS['access_token'] ), false);
    return $details->emailAddress;
}

function httpGet($url, $access_token)
{
    $headers = array(
        'Content-Type: application/json',
        sprintf('Authorization: Bearer %s', $access_token)
    );
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL             => $url,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_HTTPHEADER      => $headers
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function httpPost($url, $data)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL             => $url,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_POST            => true,
        CURLOPT_POSTFIELDS      => http_build_query($data)
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function print_errors() 
{
    if( count( $GLOBALS['errors'] ) > 0 ) {
        $output = '<div class="error"><ul>';
        foreach($GLOBALS['errors'] as $error) {
            $output .= sprintf('<li>%1$s</li>', $error);
        }
        $output .= '</ul></div>';
        echo $output;
    }
}

function show_login(){
    echo sprintf('<a href="https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id=%1$s&redirect_uri=%2$s&state=%3$s">Authorize Email Collection</a>',
        $GLOBALS['client_id'],
        urlencode($GLOBALS['redirect_uri']),
        $GLOBALS['state']
    );
}

function state_matches()
{
    return $_GET['state'] === $GLOBALS['state'];
}