<?php

/**
 * ActivityInfo client, based on https://github.com/UNICEFLebanonInnovation/ActvityInfoPython
 * See also https://about.activityinfo.org/feature/restful-api/
 * 
 */

class activityinfo_client
{
    var $baseUrl, $username, $password, $ch, $jsonOptions = JSON_PRETTY_PRINT;
    
    public function __construct($username, $password, $baseUrl = "https://www.activityinfo.org/") {
        $this->baseUrl = $baseUrl;
        
        // Init curl channel
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
        
        //timeout after 30 seconds
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($this->ch, CURLOPT_USERPWD, "$username:$password");
    }
    
    public function makeRequest($path, $params = array()) {
    	$fullPath = $this->baseUrl . $path;
    	if( count($params) > 0 ) {
    		$fullPath .= '?' . http_build_query( $params );
    	}
    	printf( "%s\n", $fullPath);
        curl_setopt($this->ch, CURLOPT_URL, $fullPath);
        $result = curl_exec($this->ch);
        $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        return json_decode($result, true);
    }
    
    public function

    public function getDatabases() {
        return $this->makeRequest('resources/databases');
    }
    
    public function getDatabase($dbId) {
        $path = sprintf('resources/database/%s/schema', $dbId);
        return $this->makeRequest($path);
    }
    
    public function getSites($partner = false, 
	    	$activity = false,
	    	$indicator = false, 
	    	$attribute = false, 
	    	$include_monthly_reports = true) {
    	$keys = array( 'partner', 'activity', 'indicator','attribute');
    	$params = array();
		foreach( $keys as $key) {
			if( ${$key}) {
				$params[$key] = ${$key};
			}
		}
    	$sites = $this->makeRequest( 'resources/sites', $params );
    	if( $include_monthly_reports) {
    		foreach( $sites as $i => $site) {
    			$sites[$i]['monthlyReports'] = $this->getMonthlyReportsForSite( $site['id']);
    		}
    	}
    	return $sites;
    }
    
    /**
     * Get monthly reports for a site
     * @param  int $siteId id of site
     * @return array of monthly reports
     */
    public function getMonthlyReportsForSite( $siteId ) {
        $path = sprintf('resources/sites/%s/monthlyReports', $siteId);
        return $this->makeRequest($path);
    }

    /**
     * Get administrative levels for a country
     * @param  string $country 2-character uppercase country code
     * @return array administrative levels
     */
    public function getAdminLevels( $country ) {
        $path = sprintf('resources/country/%s/adminLevels', $country);
        return $this->makeRequest($path);
    }

    /**
     * Get location types for a country
     * @param  string $country 2-character uppercase country code
     * @return array locations types
     */
    public function getLocationTypes( $country ) {
        $path = sprintf('resources/country/%s/locationTypes', $country);
        return $this->makeRequest($path);
    }

    /**
     * Get entities for an administrative level
     * @param  int $levelId id of administrative level, as returned by getAdminLevels
     * @return array administrative levels
     */
    public function getEntities( $levelId ) {
        $path = sprintf('resources/adminLevel/%s/entities', $levelId);
        return $this->makeRequest($path);
    }

    /**
     * Get locations of given type
     * @param  int $typeId id of location type
     * @return array of locations
     */
    public function getLocations( $typeId ) {
        return $this->makeRequest('resources/locations', array( 'type' => $typeId ));
    }

}
