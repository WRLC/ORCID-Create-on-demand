<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ORCID Create on Demand Pilot</title>
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
    $denied = isset($_GET['denied']);
    $bypass = isset($_GET['bypass']) && $_GET['bypass'] == 'authN' && $log_debug;
    $oauURL = OAUTH_AUTHORIZATION_URL
            . '?client_id=' . OAUTH_CLIENT_ID
            . '&response_type=code&scope=/read-limited%20/activities/update'
            . '&redirect_uri=' . OAUTH_REDIRECT_URI;
    if ($bypass) {
        $aladin = $oauURL;
    } else {
        $aladin = $sp_url . '?institution=au&url=' . urlencode($oauURL);
    }
?>

    <div class="container-narrow">

      <div class="masthead">
        <ul class="nav nav-pills pull-right">
          <li class="active"><a href="<?php echo $home; ?>">Pilot Home</a></li>
          <li><a href="<?php echo $info; ?>" target="_blank">About ORCID</a></li>
          <li><a href="<?php echo $repo;?>">AUDRA-IR</a></li>
        </ul>
        <h4 class="muted">ORCID @ American University Library</h4>
      </div>

      <hr>

      <div class="jumbotron">
<?php if ($denied) { ?>
        <div class="alert alert-info"><h3>No authorization has been given</h3></div>
        <p class="lead">You have not given permission to AU Library to connect your ORCID iD to the AU Digital Research Archive and update your ORCID record.</p>
<?php } else { ?>
        <h2>Create or Connect your ORCID iD</h2>
<?php } ?>
        <p class="lead">ORCID iDs are used by publishers, funders, associations and other organizations to make sure your work is correctly attributed to you. Connecting your iD to the AU Library provides added benefits including:</p>
        <table class="table">
        <tr>
            <td><strong>Library Profile Update</strong></td>
            <td>Matches publications by your iD, eliminating the need for you to confirm that each publication is yours</td>
        </tr>
        <tr>
            <td><strong>Repository Services</strong></td>
            <td>When depositing articles and datasets into the Digital Research Archive, we will automatically add them to your ORCID record, making it easier for you to make them available to other organizations and services</td>
        </tr>
        </table>
        <p class="lead">To do this, the AU Library needs your permission.</p>
<?php if ($denied) { ?>
        <p>Click the button below to try again.
<?php } else { ?>
        <p>Click the button below to create an ORCID iD (if you don't already have one) and connect it to the American University Library.
<?php } ?>
        <br>
        <a class="btn btn-large" href="<?php echo $aladin; ?>">
          <img id="orcid-id-logo" src="icons/orcid_24x24.png" width='24' height='24' alt="ORCID logo"/>
          Create/Connect ORCID iD</a>
        </p>
        <br>
        <p>If you would like to disconnect your iD from the AU Library or report any problems with this page, please send a request to <a href="mailto:servicedesk@wrlc.org?subject=ORCID+Connect+Question">ServiceDesk@wrlc.org</a>.</p>
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

  </body>
</html>
