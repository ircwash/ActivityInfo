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
    
    /**
     * set the path for the request, by prepending path with the base url and adding params as query
     * @param string $path   path name relative to base url
     * @param array  $params optional list of parameters
     */
    protected function setPath($path, $params = array()) {
        $fullPath = $this->baseUrl . $path;
        if (count($params) > 0) {
            $fullPath.= '?' . http_build_query($params);
        }
        printf("%s\n", $fullPath);
        curl_setopt($this->ch, CURLOPT_URL, $fullPath);
    }
    
    /**
     * Execute the request, report status if not OK, otherwise return result, decode if JSON
     * @param  string $path path name relative to base url
     * @return [mixed]       result from request
     */
    protected function exec($path) {
        $result = curl_exec($this->ch);
        $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        if ($status != 200) {
            printf("HTTP Status code: %s\n", $status);
            return false;
        }
        if (preg_match('/json/', curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE))) {
            $result = json_decode($result, true);
        }
        return $result;
    }
    
    /**
     * make a request to get information
     * @param  string $path   of the request
     * @param  array  $params optional
     * @return [mixed]         request result
     */
    protected function makeRequest($path, $params = array()) {
        curl_setopt($this->ch, CURLOPT_HTTPGET, true);
        $this->setPath($path, $params);
        return $this->exec();
    }
    
    /**
     * Call a command using type and parameters -- need to find documentation
     * @param  string $type       [description]
     * @param  array $properties [description]
     * @return [mixed]             [description]
     */
    public function callCommand($type, $properties) {
        $data = array('type' => $type, 'command' => array('properties' => $properties));
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        $this->setPath('command');
        return $this->exec();
    }
    
    /**
     * List all databases
     * @return array of databases (name and id)
     */
    public function getDatabases() {
        return $this->makeRequest('resources/databases');
    }
    
    /**
     * Get the structure of a database
     * @param  int $dbId id of database
     * @return array (associative) of database info
     */
    public function getDatabase($dbId) {
        $path = sprintf('resources/database/%s/schema', $dbId);
        return $this->makeRequest($path);
    }
    
    /**
     * List all sites for a partner, activities,...
     * @param  array   $params  associative array to filter by partner, activity, indicator or attribute
     * @param  boolean $include_monthly_reports 
     * @return array of sites
     */
    public function getSites($params = array(), $include_monthly_reports = true) {
        $keys = array('partner', 'activity', 'indicator', 'attribute');
        $sites = $this->makeRequest('resources/sites', $params);
        if ($include_monthly_reports) {
            foreach ($sites as $i => $site) {
                $sites[$i]['monthlyReports'] = $this->getMonthlyReportsForSite($site['id']);
            }
        }
        return $sites;
    }
    
    /**
     * Get monthly reports for a site
     * @param  int $siteId id of site
     * @return array of monthly reports
     */
    public function getMonthlyReportsForSite($siteId) {
        $path = sprintf('resources/sites/%s/monthlyReports', $siteId);
        return $this->makeRequest($path);
    }
    
    /**
     * Get administrative levels for a country
     * @param  string $country 2-character uppercase country code
     * @return array administrative levels
     */
    public function getAdminLevels($country) {
        $path = sprintf('resources/country/%s/adminLevels', $country);
        return $this->makeRequest($path);
    }
    
    /**
     * Get location types for a country
     * @param  string $country 2-character uppercase country code
     * @return array locations types
     */
    public function getLocationTypes($country) {
        $path = sprintf('resources/country/%s/locationTypes', $country);
        return $this->makeRequest($path);
    }
    
    /**
     * Get entities for an administrative level
     * @param  int $levelId id of administrative level, as returned by getAdminLevels
     * @return array administrative levels
     */
    public function getEntities($levelId) {
        $path = sprintf('resources/adminLevel/%s/entities', $levelId);
        return $this->makeRequest($path);
    }
    
    /**
     * Get locations of given type
     * @param  int $typeId id of location type
     * @return array of locations
     */
    public function getLocations($typeId) {
        return $this->makeRequest('resources/locations', array('type' => $typeId));
    }
}
