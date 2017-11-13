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
        $mads['identifier']['u1'] = $orcid;
        $mads['identifier']['netid'] = $email_id;
        $mads['authority']['name']['given'] = $resp['person']['name']['given-names']['value'];
        $mads['authority']['name']['family']= $resp['person']['name']['family-name']['value'];
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
            foreach ($resp['activities-summary']['employments']['employment-summary'] as $org) {
                if ($org['organization']['disambiguated-organization']
                        ['disambiguated-organization-identifier'] == ORG_IDENTIFIER)
                {
                    if (isset( $org['department-name'] )) {
                        $mads['affiliation']['organization'] = $org['department-name'];
                    }
                    if (isset( $org['role-title'] )) {
                        $mads['affiliation']['position'] = $org['role-title'];
                    }
                }
            }
        }
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
                if (isset( $work['work-summary'] )) {
                    foreach ($work['work-summary'] as $cite) {
                        # TBD: also filter on 'type'??
                        if ($cite['visibility'] == 'PUBLIC') {
                            $work_codes .= ($work_codes? ','.$cite['put-code'] : $cite['put-code']);
                        }
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
?>
