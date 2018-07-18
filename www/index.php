<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Create/Connect ORCID iD</title>
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
    $aladin = $home; // to avoid PHP undefined variable notice
    $debug = isset($_GET['debug']) && DEBUG_LOG;
    $bypass = isset($_GET['bypass']) && $_GET['bypass'] == 'authN' && $debug;
    if ($debug) {
        $state = ($bypass && isset($_GET['state']) ? "&state={$_GET['state']}" : '');
        $oauURL = OAUTH_AUTHORIZATION_URL
                . '?client_id=' . OAUTH_CLIENT_ID . $state
                . '&response_type=code&scope=/read-limited%20/activities/update'
                . '&redirect_uri=' . OAUTH_REDIRECT_URI;
        if ($bypass) {
            $aladin = $oauURL;
        } else {
            $aladin = $sp_url . '?institution=au&url=' . urlencode($oauURL);
        }
    } else {
        /* landing page has moved to AU Library CMS */
        header( "Location: $home" );
    }
?>

    <div class="container-narrow">

      <div class="masthead">
        <ul class="nav nav-pills pull-right">
          <li class="active"><a href="<?php echo $home; ?>">Back to AU Library</a></li>
        </ul>
        <h4 class="muted">Create/Connect your ORCID iD</h4>
      </div>

      <hr>

      <div class="jumbotron">
        <h2>Create/Connect ORCID iD Test Page</h2>
        <p>
        <a class="btn btn-large" href="<?php echo $aladin; ?>">
          <img id="orcid-id-logo" src="icons/orcid_24x24.png" width='24' height='24' alt="ORCID logo"/>
          Create/Connect ORCID iD</a>
        </p>
      </div>

      <hr>

      <div class="footer">
        <img class="pull-right" src="icons/ORCID_Member_Web_170px.png">
        <a href="https://www.american.edu/">
        <img style="max-width:50%; max-height:50%" alt="American University Homepage" src="icons/au_logo_h_1.png"></a>
      </div>

    </div> <!-- /container -->

    <!-- Javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="bootstrap/js/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>

  </body>
</html>
