<?php

/**
 * ActivityInfo activity, for working with data in ActivityInfo
 *
 */

require_once ('activityinfo_client.php');

class activityinfo_activity
{
    var $client, $id, $attributeGroups, $indicators;
    
    public function __construct($aiClient, $databaseId, $activityId) {
        $schema = $aiClient->getDatabase($databaseId);
        $this->client = $aiClient;
        foreach ($schema['activities'] as $activity) {
            if ($activity['id'] == $activityId) {
                $this->id = (int)$activityId;
                $this->indicators = array();
                foreach ($activity['indicators'] as $indicator) {
                    $this->indicators[$indicator['name']] = $indicator['id'];
                }
                $this->attributeGroups = array();
                foreach ($activity['attributeGroups'] as $attributeGroup) {
                    $this->attributeGroups[$attributeGroup['name']] = array('id' => $attributeGroup['id'], 'attributes' => array());
                    foreach ($attributeGroup['attributes'] as $attribute) {
                        $this->attributeGroups[$attributeGroup['name']]['attributes'][$attribute['name']] = $attribute['id'];
                    }
                }
            }
        }
    }
    
    /**
     * Find id of indicator by name
     * @param  string $indicator name of indicator
     * @return int id of indicator or false if not found
     */
    public function getIndicatorId($indicator) {
        if (isset($this->indicators[$indicator])) {
            return $this->indicators[$indicator];
        }
        return false;
    }
    
    /**
     * Find id of attribute by name
     * Throw error if attributeGroup not found
     * Create attribute if not found in group
     * @param  string $attributeGroup attributeGroup name
     * @param  string $attribute      attribute name
     * @return int                 id of attribute
     */
    public function getAttributeId($attributeGroup, $attribute) {
        
        // Find attributeGroup, throw error if not found
        if (!isset($this->attributeGroups[$attributeGroup])) {
            throw new Exception('Unknown attribute group ' . $attributeGroup);
        }
        
        // Find attribute, return if found
        if (isset($this->attributeGroups[$attributeGroup]['attributes'][$attribute])) {
            return $this->attributeGroups[$attributeGroup]['attributes'][$attribute];
        }
        
        // New attribute, store it
        $attributeGroupId = $this->attributeGroups[$attributeGroup]['id'];
        $result = $this->client->callCommand('CreateEntity', array(
            'entityName' => 'Attribute', 
            'properties' => array(
                'attributeGroupId ' => $attributeGroupId, 
                'name' => $attribute)));
        print_r($result);
        return;
    }
    
    public function addSite($info, $default) {
        $properties = $default;
        $properties['id'] = mt_rand(1 << 3, 1 << 31);
        
        foreach ($info as $key => $value) {
            $indicator = $this->getIndicatorId($key);
            if ($indicator) {
                $properties['I' . $indicator] = $value;
            } else {
                $attribute = $this->getAttributeId($key, $value);
                $properties['A' . $attribute] = true;
            }
        }
        
        // foreach ($info as $key => $value) {
        //     $indicator = $this->getIndicatorId($key);
        //     if ($indicator) {
        //         $properties['indicatorValues'][$indicator] = $value;
        //     } else {
        //         $attribute = $this->getAttributeId($key, $value);
        //         $properties['attributes'][] = $attribute;
        //     }
        // }
       
        $this->client->callCommand('CreateSite', array('properties' => $properties));
    }
}
