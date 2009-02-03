<?php

/**
 *
 *
 * Map class definition
 *
 * PHP version 5
 *
 * LICENSE: The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 *
 * The Original Code is Red Tree Systems Code.
 *
 * The Initial Developer of the Original Code is Red Tree Systems, LLC. All Rights Reserved.
 *
 * @category     Utils
 * @author         Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license        MPL 1.1
 * @version        1.0
 * @link             http://framework.redtreesystems.com
 */

/**
 * Contains methods for geocoding and plotting maps.
 *
 * There should be only one instance of this class throughout the platform,
 * but is not made into a singleton class.
 *
 * @package        Utils
 * @static
 */
class Map
{
    /**
     * Sets the key used for this map
     *
     * @static
     * @access public
     * @var string
     */
    public static $key = '';

    /**
     * Contains whether or not the map has been loaded
     *
     * @static
     * @access private
     * @var boolean
     */
    private static $mapLoaded = false;

    /**
     * Geocode an address. This is a 'reverse lookup' in
     * a way. An address is given, and coordinates are returned.
     *
     * @static
     * @access public
     * @param string $address The address to geocode
     * @return array [latitude, longitude], null on failure
     */
    public static function Geocode($address)
    {
        $request = 'http://maps.google.com/maps/geo?q=' . urlencode($address);
        $request .= '&key=' . urlencode(Map::$key) . '&output=csv';

        $response = explode(',', file_get_contents($request));
        if (!count($response) || ($response[0] != 200)) {
            return null;
        }

        return array($response[2], $response[3]);
    }

    /**
     * Displays the map at the current point
     *
     * @param string $id the id of the component to use
     * @param string|float $lat the latitude
     * @param string|float $long the longitude
     * @param array $options the array of options to pass the map. here
     * are some of the recognized options:
     *     -> zoom => the amount to zoom the map (default is 14)
     *     -> info => the string displayed when the marker is clicked
     * @return void
     */
    public static function Display($id, $lat, $long, $options=array())
    {

        print '<script type = "text/javascript">' . "\n";
        print "//<![CDATA[\nwindow.onload=function(){";
        print 'if (GBrowserIsCompatible()) {';
         print 'var map = new GMap2(document.getElementById("' . $id . '"));';
         print 'var point = new GLatLng(' . $lat . ', ' . $long . ');';
         print 'var marker = new GMarker(point);';

         if (isset($options['info']) && $options['info']) {
             print 'GEvent.addListener(marker, "click", function() { ';
             print 'marker.openInfoWindowHtml("' .    $options['info'] . '"); });';
         }

        print 'map.setCenter(point, ' . (isset($options['zoom']) ? $options['zoom'] : 14) . ');';
        print 'map.addOverlay(marker);';
        print '}};';
        print "\n//]]>\n</script>\n";
    }

    /**
     * Displays the map for several points
     *
     * @param string $id the id of the component to use
     * @param array $points stores the latitude and longitudes of properties to be added
     * points[0] = array([latitude] => '1.23432', [longitude] => '-1.46224')
     * @param array $options the array of options to pass the map. here
     * are some of the recognized options:
     *     -> zoom => the amount to zoom the map (default is 14)
     *     -> info => the string displayed when the marker is clicked
     * @return void
     */
    public static function DisplayMany($id, $points=array(), $options=array())
    {
        print '<script type = "text/javascript">' . "\n";
        print "//<![CDATA[\n";
        print 'if (GBrowserIsCompatible()) {';
         print 'var map = new GMap2(document.getElementById("' . $id . '"));';
        print 'map.addControl(new GSmallMapControl());';
         $ctr = 0;
         $centerlat= null;
         $centerlong = null;

         foreach($points as $point){
             print 'var point' . $ctr . ' = new GLatLng(' . $point['latitude'] . ', ' . $point['longitude'] . ');';
             print 'var marker' . $ctr . ' = new GMarker(point' . $ctr . ');';

             if (isset($options['info']) && $options['info']) {
                 print 'GEvent.addListener(marker' . $ctr . ', "click", function() { ';
                 print 'marker' . $ctr . '.openInfoWindowHtml("' .    $options['info'] . '"); });';
             }
             if($centerlat == null){
                 $centerlat = $point['latitude'];
                 $centerlong = $point['longitude'];
             }
             else{
                 $centerlat = ($centerlat + $point['latitude']) / 2;
                 $centerlong = ($centerlong + $point['longitude']) / 2;
             }

             print 'point' . $ctr . ' = new GLatLng(' . $centerlat . ', ' . $centerlong . ');';
             print 'map.setCenter(point' . $ctr . ', ' . (isset($options['zoom']) ? $options['zoom'] : 14) . ');';
            print 'map.addOverlay(marker' . $ctr . ');';
             $ctr++;
         }

         print '}';
        print "\n//]]>\n</script>\n";
    }

    /**
     * Returns a map requirements array suitable for inclusion in Component::getHead()
     *
     * @return array the requirements array
     */
    public static function LoadMap()
    {
        if (Map::$mapLoaded) {
            return array();
        }

        return array('http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . urlencode(Map::$key));
    }
}

?>
