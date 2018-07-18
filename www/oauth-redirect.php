<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ORCID Access Approved</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Styles -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="bootstrap/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="icons/orcid_16x16.png" />
</head>

<body>

<?php
    require_once('../config.php');
    require_once('../include/jsonapi.php');

    if (DEBUG_LOG) {
        error_log( 'ORCID DEBUG: REQUEST ' . $_SERVER["REQUEST_URI"] );
    }

    $denied = false;

    // TBD: send user to a 'denied' page at home institution?
    if (isset($_GET['error']) && $_GET['error'] == 'access_denied') {
        # error=access_denied&error_description=User%20denied%20access
        $denied = true;

    // If an authorization code exists, fetch the access token
    } else if (isset($_GET['code'])) {
        $code = $_GET['code'];

        if (isset($_GET['state'])) {
            $state = $_GET['state'];
        } else {
            $state = '';
        }

        if (DEBUG_LOG) {
            error_log( "ORCID DEBUG: Auth code $code for '$state' received by OAuth Client ".OAUTH_CLIENT_ID );
        }

        // Initialize OAUTH cURL session
        $ch = getCurlSession( OAUTH_TOKEN_URL );

        // Build request parameter string
        $params = "client_id=" . OAUTH_CLIENT_ID
                . "&client_secret=" . OAUTH_CLIENT_SECRET
                . "&grant_type=authorization_code&code=" . $code
                . "&redirect_uri=" . OAUTH_REDIRECT_URI;

        // Set OAUTH cURL options
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_POST, true);
        // Turn off SSL certificate check for testing - remove this for production version!
        //curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        //curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);

        // Execute OAUTH cURL command
        DEBUG_LOG? error_log( "ORCID DEBUG: POST ".OAUTH_TOKEN_URL."?$params" ) : true;
        list ($code, $body) = sendCurlRequest( $ch, $params );
        DEBUG_LOG? error_log( "ORCID DEBUG: RESPONSE ($code) $body" ) : true;

        // Close OAUTH cURL session
        curl_close($ch);

        if ($code == 200) {
            // Transform cURL response from json string to php array
            $json_array = json_decode($body, true);
            if (is_null( $json_array )) {
                list ($code, $body) = jsonError( json_last_error() );
            }

            # save response fields for building our response page
            $oname = $json_array['name'];
            $orcid = $json_array['orcid'];
            $token = $json_array['access_token'];

            # Use response scope to display human-readable authorizations
            $scope_list = '<ul class="list-group">';
            $scopes = explode(" ", $json_array['scope']);
            foreach ($scopes as $scope) {
                $scope_list .= '<li class="list-group-item">'.$scope_desc[$scope].'</li>';
            }
            $scope_list .= "</ul>\n";

            # Store authZ data in a json database
            // Initialize a new cURL session
            $ch = getCurlSession( JSON_DB."/$orcid" );

            // Set cURL options for jsondb
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body))
            );
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

            // execute cURL command to store authZ info in json db
            DEBUG_LOG? error_log( "ORCID DEBUG: PUT ".JSON_DB."$orcid?$body" ) : true;
            list ($code, $body) = sendCurlRequest( $ch, $body );
            DEBUG_LOG? error_log( "ORCID DEBUG: RESPONSE ($code) $body" ) : true;

            if ($code == 200) {
                // removed code to update AUDRA-IR; see branch audra-integration-pilot
            } else {
                error_log( "ORCID ERROR: ".JSON_DB."/$orcid returned ($code) $body" );
                $message = "HTTP Response Code $code";
                echo "--- DB WRITE FAILED: $message";
            }

            // Close jsondb cURL session
            curl_close($ch);

        } else {
            $message = "HTTP Response Code $code";
            echo "--- AUTHORIZATION FAILED: $message";
        }

    // If an authorization code doesn't exist, throw an error
    } else {
        $message = "Unable to connect to ORCID";
        error_log( "ORCID WARNING: $message" );
        echo "--- AUTHORIZATION FAILED: $message";
    }

?>

<!-- Javascript
================================================== -->
<script src="bootstrap/js/jquery.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>

<div class="container">

    <div class="masthead">
      <ul class="nav nav-pills pull-right">
          <li class="active"><a href="<?php echo $home; ?>">Back to AU Library</a></li>
      </ul>
      <h4 class="muted">Create/Connect your ORCID iD</h4>
    </div>

    <hr>

<?php if ($denied) { ?>

    <div class="jumbotron">
        <div class="alert alert-info"><h3>No authorization has been given</h3></div>
        <p class="lead">You have not given permission to the AU Library to connect your ORCID iD to your institution.</p>
        <p class="lead">ORCID iDs are used by publishers, funders, associations and other organizations to make sure your work is correctly attributed to you, to unambiguously differentiate you from other scholars with the same name, and to streamline workflows such as submitting and reviewing journal articles, applying for funding.</p>
        <p>Return to the <a href="<?php echo $home; ?>">AU Library ORCID page</a> to try again or for additional information.</p>
      </div>

<?php } else { ?>

    <div class="jumbotron">
        <h2>ORCID Confirmation</h2>
        <br>
<?php if (isset( $orcid )) { ?>
        <p class="lead">Thanks, <?php echo $oname; ?>. You have authorized the AU Library to:
    <?php echo $scope_list; ?>
        </p>
        <p class="lead">Please keep track of your ORCID <img src="icons/orcid_16x16.png" class="logo" width='16' height='16' alt="iD"/> <a href="<?php echo "https://orcid.org/$orcid"; ?>">orcid.org/<?php echo $orcid; ?></a></p>
        <p>If you would like to disconnect your iD from AU or for additional information, <a href="mailto:skramer@american.edu?subject=ORCID+Connect+Question">Email AU about ORCID</a>.</p>
<?php } else { ?>
        <p class="lead">Sorry, it appears some problem has ocurred.</p>
        <p>Please report this to <a href="mailto:servicedesk@wrlc.org?subject=ORCID+AUTH+FAILED:+<?php echo urlencode($message); ?>">ServiceDesk@wrlc.org</a>.</p>
<?php } ?>
    </div>

<?php } ?>

    <hr>

    <div class="footer">
        <img class="pull-right" src="icons/ORCID_Member_Web_170px.png">
        <a href="https://www.american.edu/">
        <img style="max-width:50%; max-height:50%" alt="American University Homepage" src="icons/au_logo_h_1.png"></a>
    </div>

</div> <!-- /container -->

</body></html>
