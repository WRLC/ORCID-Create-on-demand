<?php
/*
 * configuration options for the ORCID API
 */

// Local app configuration
define('DEBUG_LOG', false);
#define('DEBUG_LOG', true);  // debug/error logging in web server error.log
                            // also, allows bypass=authN, NOT IN PRODUCTION!

# service endpoint for storing ORCID authN access information
define('JSON_DB', 'http://api.wrlc.org:8181/api/researchers');
# service endpoint for updating researcher info in Islandora
# note: code to update MADS_DB has been removed in master branch
#       see audra-integration-pilot branch for that stuff
#define('MADS_DB', 'http://api.wrlc.org:8181/api/islandora');

# Use response scope to display human-readable authorizations
$scope_desc['/authenticate'] = "Get your ORCID iD";
$scope_desc['/read-limited'] = "Read your limited-access information";
$scope_desc['/activities/update'] = "Add or update your research activities";
$scope_desc['/person/update'] = "Add or update your personal information";

# Orcid member info
# note: code to create MADS from Orcid member info has been removed
#       see audra-integration-pilot branch for that stuff
#define('ORG_SCOPE', '@american.edu');
#define('ORG_IDENTIFIER', 8363);

# web page navigation
$home = "https://www.american.edu/library/services/orcid.cfm";

# Service Provider endpoint
$sp_url = "https://aladin-sp.wrlc.org/simplesaml/wrlcauth/orcidlogin.php";

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
define('ORCID_RESOURCE_URL', 'https://api.orcid.org/v2.0/');

// Production - Public API
//define('OAUTH_AUTHORIZATION_URL', 'https://orcid.org/oauth/authorize');//authorization endpoint
//define('OAUTH_TOKEN_URL', 'https://orcid.org/oauth/token');//token endpoint
define('ORCID_PUBLIC_RESOURCE_URL', 'https://pub.orcid.org/v2.0/');

?>
