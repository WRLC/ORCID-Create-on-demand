<?php

/*
 * return scholar info from ORCID record in MADS-like JSON document
 * https://www.loc.gov/standards/mads/mads-doc.html
 */
function getOrcidInfo( $orcid, $access_token, $email_id ) {

    // Initialize cURL session
    $ch = getCurlSession( ORCID_RESOURCE_URL . $orcid . '/record' );

    // Set cURL options
    curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Accept: application/json',
                                                "Authorization: Bearer $access_token" ));

    // Execute OAUTH cURL command
    list ($code, $body) = sendCurlRequest( $ch );

    // Close OAUTH cURL session
    curl_close($ch);

    if ($code == 200) {
        // Transform cURL response from json string to php array
        $resp = json_decode($body, true);
        if (is_null( $resp )) {
            return jsonError( json_last_error() );
        }
        $mads['identifier']['u1'] = $orcid;
        $mads['identifier']['netid'] = $email_id;
        $mads['authority']['name']['given'] = $resp['person']['name']['given-names']['value'];
        $mads['authority']['name']['family']= $resp['person']['name']['family-name']['value'];
        $title = $resp['person']['name']['given-names']['value'] . ' '
                .$resp['person']['name']['family-name']['value'];
        # get institutional email address
        if (isset( $resp['person']['emails']['email'] )) {
            foreach ($resp['person']['emails']['email'] as $email) {
                if (substr( $email['email'], -strlen( ORG_SCOPE ) ) == ORG_SCOPE) {
                    $mads['affiliation']['email'] = $email['email'];
                }
            }
        }
        # get institutional department / title
        if (isset( $resp['activities-summary']['employments']['employment-summary'] )) {
            # if this function is going to be used in production, then
            # need to check the employment period and use only the role-title &
            # department-name associated with a current employment (ie no end date) 
            # https://github.com/WRLC/ORCID-Create-on-demand/issues/18
            foreach ($resp['activities-summary']['employments']['employment-summary'] as $org) {
                if ($org['organization']['disambiguated-organization']
                        ['disambiguated-organization-identifier'] == ORG_IDENTIFIER)
                {
                    if (isset( $org['department-name'] )) {
                        $mads['affiliation']['organization'] = $org['department-name'];
                        $title .= ' (' . $org['department-name'] . ')';
                    }
                    if (isset( $org['role-title'] )) {
                        $mads['affiliation']['position'] = $org['role-title'];
                    }
                }
            }
        }
        # set display name
        $mads['authority']['titleInfo']['title']= $title;
        # get any public URLs in ORCID record
        if (isset( $resp['person']['researcher-urls']['researcher-url'] )) {
            foreach ($resp['person']['researcher-urls']['researcher-url'] as $url) {
                if ($url['visibility'] == 'PUBLIC') {
                    $mads['url'][] = $url['url']['value'];
                }
            }
        }
        # get any biographical detail in ORCID record
        if (isset( $resp['person']['biography'] ) &&
            $resp['person']['biography']['visibility'] == 'PUBLIC')
        {
            $mads['note']['history'] = $resp['person']['biography']['content'];
        }
        # get any publications in ORCID record
        $work_codes = '';
        if (isset( $resp['activities-summary']['works']['group'] )) {
            foreach ($resp['activities-summary']['works']['group'] as $work) {
                if (isset( $work['work-summary'][0] )) {
                    # $work['work-summary'] is an array of descriptions from different sources
                    # if more than one, we just use the first one
                    $cite = $work['work-summary'][0];
                    if ($cite['visibility'] == 'PUBLIC') {
                        # TBD: also filter on 'type'?? what about "preferred" source?
                        $work_codes .= ($work_codes? ','.$cite['put-code'] : $cite['put-code']);
                    }
                }
            }
        }
        if ($work_codes) {
            $mads['citations'] = ORCID_PUBLIC_RESOURCE_URL . $orcid . '/works/' . $work_codes;
        }

        return array ($code, $mads);

    } else {
        error_log( "HTTP Response Code $code" );
        return array ($code, $body);
    }
}

/*
 * submit ORCID scholar info to IR to create 
 * scholar page and citations in Islandora
 */
function putOrcidInfo( $orcid, $mads ) {
    # Create/update MADS in Islandora
    // get a curl session handle
    $ch = getCurlSession( MADS_DB."/$orcid" );

    // Set cURL options for madsdb
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($mads))
    );
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

    // execute cURL command to update/create MADS record in Islandora
    DEBUG_LOG? error_log( "ORCID DEBUG: PUT ".MADS_DB."/$orcid?$mads" ) : true;
    list ($code, $body) = sendCurlRequest( $ch, $mads );
    DEBUG_LOG? error_log( "ORCID DEBUG: RESPONSE ($code) $body" ) : true;

    if ($code == 200 or $code == 201) {
        $json_array = json_decode($body, true);
        if (is_null( $json_array )) {
            return jsonError( json_last_error() );
        } else {
            return array ($code, $json_array);
        }
    } else {
        error_log( "HTTP Response Code $code" );
        return array ($code, $body);
    }
}

?>
