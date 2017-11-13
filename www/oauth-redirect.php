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
    require_once('../include/orcidinfo.php');

    if ($log_debug) {
        error_log( 'ORCID DEBUG: REQUEST ' . $_SERVER["REQUEST_URI"] );
    }

    // if user denies authorization send them to that page
    if (isset($_GET['error']) && $_GET['error'] == 'access_denied') {
        # error=access_denied&error_description=User%20denied%20access
        header( "Location: $home?denied" );
        exit;

    // If an authorization code exists, fetch the access token
    } else if (isset($_GET['code'])) {
        $code = $_GET['code'];

        if (isset($_GET['state'])) {
            $state = $_GET['state'];
        } else {
            $state = '';
        }

        if ($log_debug) {
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
        $log_debug? error_log( "ORCID DEBUG: POST ".OAUTH_TOKEN_URL."?$params" ) : true;
        list ($code, $body) = sendCurlRequest( $ch, $params );
        $log_debug? error_log( "ORCID DEBUG: RESPONSE ($code) $body" ) : true;

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
            $ch = getCurlSession( "$jsondb/$orcid" );

            // Set cURL options for jsondb
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body))
            );
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

            // execute cURL command to store authZ info in json db
            $log_debug? error_log( "ORCID DEBUG: PUT $jsondb/$orcid?$body" ) : true;
            list ($code, $body) = sendCurlRequest( $ch, $body );
            $log_debug? error_log( "ORCID DEBUG: RESPONSE ($code) $body" ) : true;

            if ($code == 200) {
                list ($code, $mads) = getOrcidInfo( $orcid, $token, $state );
                if ($code == 200) {
                    if ($log_debug) {
                        $time = @date('d-M-Y:H:i:s');
                        $logger[$time] = $mads;
                        $json = json_encode($logger, JSON_PRETTY_PRINT);
                        if (is_null( $json )) {
                            list ($code, $msg) = jsonError( json_last_error(), 500,
                                                            'JSON encode error: ' );
                            $message = "problem encoding Orcid info -- see error log";
                            echo "--- WARNING: $message";
                        } else {
                            if (!file_put_contents( '../debug.json', $json . "\n", FILE_APPEND )) {
                                error_log( "ORCID warning: $message" );
                                $message = "problem writing Orcid info -- see error log";
                                echo "--- WARNING: $message";
                            }
                        }
                    }
                    # TBD: create scholar page and citations in Islandora
                } else {
                    error_log( "ORCID WARNING: getOrcidInfo returned ($code) $body" );
                    $message = "problem reading Orcid info -- see error log";
                    echo "--- WARNING: $message";
                }
            } else {
                error_log( "ORCID ERROR: $jsondb/$orcid returned ($code) $body" );
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

<div class="container">

    <div class="masthead">
      <ul class="nav nav-pills pull-right">
        <li><a href="<?php echo $home; ?>">Pilot Home</a></li>
        <li><a href="<?php echo $info; ?>" target="_blank">About ORCID</a></li>
        <li><a href="<?php echo $repo; ?>">AUDRA-IR</a></li>
      </ul>
      <h4 class="muted">ORCID @ American University Library</h4>
    </div>

    <hr>

    <div class="jumbotron">
        <h2>ORCiD Confirmation</h2>
        <br>
<?php if (isset( $orcid )) { ?>
        <p class="lead">Thanks, <?php echo $oname; ?>. You have authorized AU Library to:
    <?php echo $scope_list; ?>
        </p>
        <br> <br>
        <p class="lead">Please keep track of your ORCID <img src="icons/orcid_16x16.png" class="logo" width='16' height='16' alt="iD"/> <a href="<?php echo "https://orcid.org/$orcid"; ?>">orcid.org/<?php echo $orcid; ?></a></p>
        <p>If you would like to disconnect your iD from the AU Library, please send a request to <a href="mailto:servicedesk@wrlc.org?subject=ORCID+Disconnect+Request:+<?php echo $orcid; ?>">ServiceDesk@wrlc.org</a>.</p>
<?php } else { ?>
        <p class="lead">Sorry, it appears some problem has ocurred.</p>
        <p>Please report this to <a href="mailto:servicedesk@wrlc.org?subject=ORCID+AUTH+FAILED:+<?php echo urlencode($message); ?>">ServiceDesk@wrlc.org</a>.</p>
<?php } ?>
    </div>

    <hr>

    <div class="footer">
        <img class="pull-right" src="icons/ORCID_Member_Web_170px.png">
        <a href="<?php echo $docs; ?>" target="_blank">AU/WRLC ORCID Create-on-demand Pilot Project</a>
    </div>

</div> <!-- /container -->

<!-- Javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="bootstrap/js/jquery.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>

</body></html>
