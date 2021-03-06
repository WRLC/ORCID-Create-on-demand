# ORCID-Create-on-demand
ORCID create-on-demand API client application

## Installing

### Requirements
- PHP
- Nginx or Apache 2
- ORCID Member API credentials (see https://members.orcid.org/api/getting-started)

### Configuration
Edit config.php and adjust the settings to match your environment.

The PHP files in the www folder have some text specific to American University; adjust if deploying for another institution.

Create a file in the top-level `ORCID-Create-on-demand` named `oauth-client-secret.php` that looks like this, with your client secret replacing the dummy value:
```
<?php
    //ORCID Client Secret - keep this credential secret
    define('OAUTH_CLIENT_SECRET', 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');
?>
```

Configure your web server to serve up the `ORCID-Create-on-demand` directory. For example, for Apache:
```
Alias /orcid "/var/www/ORCID-Create-on-demand/www"
```

