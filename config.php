<?php
/*
 * configuration options for the ORCID API
 */

// Local app configuration
$log_debug = true;  // debug/error logging in apache2 error.log
                    // also, allow bypass=authN, NOT IN PRODUCTION!
$JSONDB = '../db.json';

# Use response scope to display human-readable authorizations
$scope_desc['/authenticate'] = "Get your ORCID iD";
$scope_desc['/read-limited'] = "Read your limited-access information";
$scope_desc['/activities/update'] = "Add or update your research activities";
$scope_desc['/person/update'] = "Add or update your personal information";

# web page navigation
$home = "https://dra.american.edu/orcid/";
$info = "https://orcid.org";
$repo = "http://dra.american.edu/audra-ir";
#$repo = "http://auislandora-dev/audra-ir";
$docs = "https://docs.google.com/document/d/1HygRQ6hqoElILQvGjxkgZ4cSS_Y4B4vbx59Ex0ApFB0/edit?usp=sharing";

# Service Provider endpoint
$sp_url = "https://aladin-sp.wrlc.org/simplesaml/wrlcauth/orcidlogin.php";
#$sp_url = "https://aladin-tst.wrlc.org/simplesaml/wrlcauth/orcidlogin.php";


// ORCID API CREDENTIALS
////////////////////////////////////////////////////////////////////////
// ORCID sandbox API app:
#define('OAUTH_CLIENT_ID', 'APP-010CTGKA36MQQR7X');
#define('OAUTH_REDIRECT_URI', 'https://api-stage.wrlc.org/orcid/oauth-redirect.php');
// ORCID production API app:
define('OAUTH_CLIENT_ID', 'APP-B9KGUIQ1W750ISKG');
define('OAUTH_REDIRECT_URI', 'https://dra.american.edu/orcid/oauth-redirect.php');
// define OAUTH_CLIENT_SECRET in this file:
require_once('oauth-client-secret.php');

// ORCID API ENDPOINTS
////////////////////////////////////////////////////////////////////////

// Sandbox - Member API
//define('OAUTH_AUTHORIZATION_URL', 'https://sandbox.orcid.org/oauth/authorize');//authorization endpoint
//define('OAUTH_TOKEN_URL', 'https://sandbox.orcid.org/oauth/token'); //token endpoint

// Sandbox - Public API
//define('OAUTH_AUTHORIZATION_URL', 'https://sandbox.orcid.org/oauth/authorize');//authorization endpoint
//define('OAUTH_TOKEN_URL', 'https://pub.sandbox.orcid.org/oauth/token');//token endpoint

// Production - Member API
define('OAUTH_AUTHORIZATION_URL', 'https://orcid.org/oauth/authorize');//authorization endpoint
define('OAUTH_TOKEN_URL', 'https://orcid.org/oauth/token'); //token endpoint

// Production - Public API
//define('OAUTH_AUTHORIZATION_URL', 'https://orcid.org/oauth/authorize');//authorization endpoint
//define('OAUTH_TOKEN_URL', 'https://orcid.org/oauth/token');//token endpoint

?>
