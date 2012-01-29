<?php

session_start();

include("../../lib/default_config.php");

require_once "common.php";

if(isset($_REQUEST['turl'])){
	$_SESSION['labtrove']['turl'] = $_REQUEST['turl'];
}

if(isset($_REQUEST['remember']) && $_REQUEST['remember']=="true"){
	$_SESSION['labtrove']['openid']['remember']	= true;
}else{
	$_SESSION['labtrove']['openid']['remember']	= false;
} 
function getOpenIDURL() {
    // Render a default page if we got a submission without an openid
    // value.
    if (empty($_GET['openid_identifier'])) {
        $error = "Expected an OpenID URL.";
        header('Location: /'.$_REQUEST['turl']."?flogin=1&loginbox=1&msg=$error");
        exit(0);
    }

    return $_GET['openid_identifier'];
}

function run() {
  $openid = getOpenIDURL();
  $consumer = getConsumer();

    // Begin the OpenID authentication process.
    $auth_request = $consumer->begin($openid);

    // No auth request means we can't begin OpenID.
    if (!$auth_request) {
        displayError("Authentication error; not a valid OpenID.");
    }

    $sreg_request = Auth_OpenID_SRegRequest::build(
                                     // Required
                                     array('fullname', 'email'),
                                     // Optional
                                     array());

    if ($sreg_request) {
        $auth_request->addExtension($sreg_request);
    }

    $policy_uris = $_GET['policies'];

    $pape_request = new Auth_OpenID_PAPE_Request($policy_uris);
    if ($pape_request) {
        $auth_request->addExtension($pape_request);
    }

    // Redirect the user to the OpenID server for authentication.
    // Store the token for this authentication so we can verify the
    // response.

    // For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
    // form to send a POST request to the server.
    if ($auth_request->shouldSendRedirect()) {
        $redirect_url = $auth_request->redirectURL(getTrustRoot(),
                                                   getReturnTo());

        // If the redirect URL can't be built, display an error
        // message.
        if (Auth_OpenID::isFailure($redirect_url)) {
            displayError("Could not redirect to server: " . $redirect_url->message);
        } else {
            // Send redirect.
            header("Location: ".$redirect_url);
        }
    } else {
        // Generate form markup and render it.
        $form_id = 'openid_message';
        $form_html = $auth_request->htmlMarkup(getTrustRoot(), getReturnTo(),
                                               false, array('id' => $form_id));

        // Display an error if the form markup couldn't be generated;
        // otherwise, render the HTML.
        if (Auth_OpenID::isFailure($form_html)) {
            displayError("Could not redirect to server: " . $form_html->message);
        } else {
            print $form_html;
        }
    }
}

run();

?>