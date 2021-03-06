<?php

/**
 * TemplateFormHandler class definition
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
 * @category     TemplateSystem
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Provides sane HTML form construction
 */
class TemplateFormHandler extends PHPSTLNSHandler
{
    /**
     * hidden field
     */
    public function handleElementHidden(DOMElement $element)
    {
        $name = $this->requiredAttr($element, 'name', false);
        $value = $this->getUnquotedAttr($element, 'value');
        if (! $this->needsQuote($value)) {
            $value = "<?php echo $value ?>";
        }

        $this->compiler->write(
            '<input type="hidden"'.$this->getAttributeString(array_merge(
            $this->getAttributes($element, array(
                'name' => null,
                'id' => $name
            ), true), array(
                'value' => $value
            ))).' />'
        );
    }

    /**
     * A simple input tag
     *
     * @param string required name - the name you'd like to give this element
     * @param string optional id - the id you want for this element (defaults to name)
     * @param string optional value - the value for this field
     * @param int optional maxlength - the max length of the field (defaults to 255)
     * @param bool enabled - true if enabled, false otherwise
     */
    public function handleElementInput(DOMElement $element)
    {
        $name = $this->requiredAttr($element, 'name', false);
        $id = $this->getUnquotedAttr($element, 'id', $name);
        $value = $this->getUnquotedAttr($element, 'value');
        if (! $this->needsQuote($value)) {
            $value = "<?php echo $value ?>";
        }

        $this->compiler->write(
            '<input'.$this->getAttributeString(array_merge(
            $this->getAttributes($element, array(
                'type' => 'text',
                'name' => null,
                'id' => $name,
                'maxlength' => 255
            ), true), array(
                'disabled' => !$this->getBooleanAttr($element, 'enabled', true),
                'value' => $value
            ))).' />'
        );
    }

    /**
     * A checkbox
     *
     * @param string required name - the name you'd like to give this element
     * @param string optional id - the id you want for this element (defaults to name)
     * @param string optional value - the value for this field
     * @param string optional checked - a boolean value to select the box or not
     */
    public function handleElementCheckbox(DOMElement $element)
    {
        $name = $this->requiredAttr($element, 'name', false);
        $value = $value = $this->getUnquotedAttr($element, 'value');
        if (! $this->needsQuote($value)) {
            $value = "<?php echo $value ?>";
        }
        $this->compiler->write(
            "<input type=\"checkbox\" name=\"$name\"".
            $this->getAttributeString(array_merge(
                $this->getAttributes($element, array('id', 'checked'), true),
                array('value' => $value)
            )).' />'
        );
    }

    /**
     * A radio item
     *
     * @param string required name - the name you'd like to give this element
     * @param string optional id - the id you want for this element (defaults to name)
     * @param string optional value - the value for this field
     * @param string optional checked - a boolean value to select the box or not
     */
    public function handleElementRadio(DOMElement $element)
    {
        $name = $this->requiredAttr($element, 'name', false);
        $value = $value = $this->getUnquotedAttr($element, 'value');
        if (! $this->needsQuote($value)) {
            $value = "<?php echo $value ?>";
        }
        $this->compiler->write(
            "<input type=\"radio\" name=\"$name\"".
            $this->getAttributeString(array_merge(
                $this->getAttributes($element, array('id', 'checked'), true),
                array('value' => $value)
            )).' />'
        );
    }

    /**
     * Displays a textarea
     *
     * @param string required name - the name you'd like to give this element
     * @param string optional id - the id you want for this element (defaults to name)
     * @param string optional value - the value for this field
     * @param int optional rows - the number of rows you'd like to have (defaults to 3)
     * @param int optional cols - the number of cols you'd like to have (defaults to 50)
     * @param bool enabled - true if enabled, false otherwise
     */
    public function handleElementTextarea(DOMElement $element)
    {
        $name = $this->requiredAttr($element, 'name', false);
        $value = $this->getUnquotedAttr($element, 'value', '@content');

        $this->compiler->write(
            "<textarea name=\"$name\"".$this->getAttributeString(array_merge(
            $this->getAttributes($element, array(
                'id' => $name,
                'rows' => 3,
                'cols' => 50,
                'readonly' => false
            ), true), array(
                'disabled' => !$this->getBooleanAttr($element, 'enabled', true)
            ))).'>'
        );
        if ($value == '@content') {
            $this->process($element);
        } else {
            if ($value) {
                $this->compiler->write('<?php echo htmlentities(' . $value . ');?>');
            }
        }
        $this->compiler->write('</textarea>');
    }

    /**
     * Displays a button
     *
     * @param string required label - the text for the button
     * @param string optional href - the href to go to when clicked. if not set, the button will be a submit button
     */
    public function handleElementButton(DOMElement $element)
    {
        $label = $this->requiredAttr($element, 'label', false);

        $href = $this->getUnquotedAttr($element, 'href');

        if (isset($href)) {
            $button = "<button type=\"button\" onclick=\"window.location='$href';\">$label</button>";
        } else {
            $button = "<button type=\"submit\">$label</button>";
        }

        $this->compiler->write($button);
    }

    /**
     * Displays a select list
     *
     * @param string required name - the name you'd like to give this element
     * @param string optional id - the id you want for this element (defaults to name)
     * @param array required options - the options for the select as name/value pairs
     * @param string optional select - the element to select
     * @param boolean optional multiple - if this is a multiple list or not
     * @param boolean enabled - true if enabled, false otherwise
     */
    public function handleElementSelect(DOMElement $element)
    {
        $name = $this->requiredAttr($element, 'name', false);
        $options = $this->requiredAttr($element, 'options', false);
        $select = $this->getAttr($element, 'select');

        $html =
            "<select name=\"$name\"".$this->getAttributeString(array_merge(
            $this->getAttributes($element, array(
                'id' => $name,
                'multiple', 'onchange'
            ), true), array(
                'disabled' => !$this->getBooleanAttr($element, 'enabled', true)
            ))).">".
            '<option value="">&nbsp;</option>'.
            "<?php foreach ($options as \$__value => \$__label) {\n".
            '  print "<option value=\"$__value\"";'."\n";
        if (isset($select)) {
            $html .=
                "  if (\$__value == $select) {\n".
                '    print " selected=\"selected\"";'.
                "  }\n";
        }
        $html .=
            "  print \">\$__label</option>\";\n".
            '} ?></select>';

        $this->compiler->write($html);
    }

    /**
     * TODO revamp state and country, this is way too specific; needs to be
     * implemented as some kind of a locale-aware region/locality/postal
     * selector
     */

    /**
     * Displays a country select box
     *
     * @param string required select - the country you want to see selected
     * @param string required name - the name you'd like to give this element
     * @param string optional id - the id you want for this element (defaults to name)
     */
    public function handleElementCountries(DOMElement $element)
    {
        static $countries = array(
           "United States"=>"United States", "United Kingdom"=>"United Kingdom", "Afghanistan"=>"Afghanistan",
           "Albania"=>"Albania", "Algeria"=>"Algeria", "American Samoa"=>"American Samoa",
           "Andorra"=>"Andorra", "Angola"=>"Angola", "Anguilla"=>"Anguilla", "Antarctica"=>"Antarctica",
           "Antigua and Barbuda"=>"Antigua and Barbuda", "Argentina"=>"Argentina", "Armenia"=>"Armenia",
           "Aruba"=>"Aruba", "Australia"=>"Australia", "Austria"=>"Austria", "Azerbaijan"=>"Azerbaijan",
           "Bahamas"=>"Bahamas", "Bahrain"=>"Bahrain", "Bangladesh"=>"Bangladesh", "Barbados"=>"Barbados",
           "Belarus"=>"Belarus", "Belgium"=>"Belgium", "Belize"=>"Belize", "Benin"=>"Benin", "Bermuda"=>"Bermuda",
           "Bhutan"=>"Bhutan", "Bolivia"=>"Bolivia", "Bosnia and Herzegovina"=>"Bosnia and Herzegovina",
           "Botswana"=>"Botswana", "Bouvet Island"=>"Bouvet Island", "Brazil"=>"Brazil",
           "British Indian Ocean Territory"=>"British Indian Ocean Territory",
           "Brunei Darussalam"=>"Brunei Darussalam", "Bulgaria"=>"Bulgaria", "Burkina Faso"=>"Burkina Faso",
           "Burundi"=>"Burundi", "Cambodia"=>"Cambodia", "Cameroon"=>"Cameroon", "Canada"=>"Canada",
           "Cape Verde"=>"Cape Verde", "Cayman Islands"=>"Cayman Islands", "Central African Republic"=>"Central African Republic",
           "Chad"=>"Chad", "Chile"=>"Chile", "China"=>"China", "Christmas Island"=>"Christmas Island",
           "Cocos (Keeling) Islands"=>"Cocos (Keeling) Islands", "Colombia"=>"Colombia", "Comoros"=>"Comoros",
           "Congo"=>"Congo", "Congo, The Democratic Republic of The"=>"Congo, The Democratic Republic of The",
           "Cook Islands"=>"Cook Islands", "Costa Rica"=>"Costa Rica", "Cote D'ivoire"=>"Cote D'ivoire",
           "Croatia"=>"Croatia", "Cuba"=>"Cuba", "Cyprus"=>"Cyprus", "Czech Republic"=>"Czech Republic",
           "Denmark"=>"Denmark", "Djibouti"=>"Djibouti", "Dominica"=>"Dominica",
           "Dominican Republic"=>"Dominican Republic", "Ecuador"=>"Ecuador", "Egypt"=>"Egypt",
           "El Salvador"=>"El Salvador", "Equatorial Guinea"=>"Equatorial Guinea", "Eritrea"=>"Eritrea",
           "Estonia"=>"Estonia", "Ethiopia"=>"Ethiopia", "Falkland Islands (Malvinas)"=>"Falkland Islands (Malvinas)",
           "Faroe Islands"=>"Faroe Islands", "Fiji"=>"Fiji", "Finland"=>"Finland", "France"=>"France",
           "French Guiana"=>"French Guiana", "French Polynesia"=>"French Polynesia",
           "French Southern Territories"=>"French Southern Territories", "Gabon"=>"Gabon", "Gambia"=>"Gambia",
           "Georgia"=>"Georgia", "Germany"=>"Germany", "Ghana"=>"Ghana", "Gibraltar"=>"Gibraltar",
           "Greece"=>"Greece", "Greenland"=>"Greenland", "Grenada"=>"Grenada", "Guadeloupe"=>"Guadeloupe",
           "Guam"=>"Guam", "Guatemala"=>"Guatemala", "Guinea"=>"Guinea", "Guinea-bissau"=>"Guinea-bissau",
           "Guyana"=>"Guyana", "Haiti"=>"Haiti",
           "Heard Island and Mcdonald Islands"=>"Heard Island and Mcdonald Islands",
           "Holy See (Vatican City State)"=>"Holy See (Vatican City State)",
           "Honduras"=>"Honduras", "Hong Kong"=>"Hong Kong", "Hungary"=>"Hungary",
           "Iceland"=>"Iceland", "India"=>"India", "Indonesia"=>"Indonesia",
           "Iran, Islamic Republic of"=>"Iran, Islamic Republic of", "Iraq"=>"Iraq",
           "Ireland"=>"Ireland", "Israel"=>"Israel", "Italy"=>"Italy", "Jamaica"=>"Jamaica",
           "Japan"=>"Japan", "Jordan"=>"Jordan", "Kazakhstan"=>"Kazakhstan", "Kenya"=>"Kenya",
           "Kiribati"=>"Kiribati",
           "Korea, Democratic People's Republic of"=>"Korea, Democratic People's Republic of",
           "Korea, Republic of"=>"Korea, Republic of", "Kuwait"=>"Kuwait", "Kyrgyzstan"=>"Kyrgyzstan",
           "Lao People's Democratic Republic"=>"Lao People's Democratic Republic",
           "Latvia"=>"Latvia", "Lebanon"=>"Lebanon", "Lesotho"=>"Lesotho", "Liberia"=>"Liberia",
           "Libyan Arab Jamahiriya"=>"Libyan Arab Jamahiriya", "Liechtenstein"=>"Liechtenstein",
           "Lithuania"=>"Lithuania", "Luxembourg"=>"Luxembourg", "Macao"=>"Macao",
           "Macedonia, The Former Yugoslav Republic of"=>"Macedonia, The Former Yugoslav Republic of",
           "Madagascar"=>"Madagascar", "Malawi"=>"Malawi", "Malaysia"=>"Malaysia", "Maldives"=>"Maldives",
           "Mali"=>"Mali", "Malta"=>"Malta", "Marshall Islands"=>"Marshall Islands",
           "Martinique"=>"Martinique", "Mauritania"=>"Mauritania", "Mauritius"=>"Mauritius",
           "Mayotte"=>"Mayotte", "Mexico"=>"Mexico",
           "Micronesia, Federated States of"=>"Micronesia, Federated States of",
           "Moldova, Republic of"=>"Moldova, Republic of", "Monaco"=>"Monaco",
           "Mongolia"=>"Mongolia", "Montserrat"=>"Montserrat", "Morocco"=>"Morocco",
           "Mozambique"=>"Mozambique", "Myanmar"=>"Myanmar", "Namibia"=>"Namibia",
           "Nauru"=>"Nauru", "Nepal"=>"Nepal", "Netherlands"=>"Netherlands",
           "Netherlands Antilles"=>"Netherlands Antilles", "New Caledonia"=>"New Caledonia",
           "New Zealand"=>"New Zealand", "Nicaragua"=>"Nicaragua", "Niger"=>"Niger",
           "Nigeria"=>"Nigeria", "Niue"=>"Niue", "Norfolk Island"=>"Norfolk Island",
           "Northern Mariana Islands"=>"Northern Mariana Islands", "Norway"=>"Norway",
           "Oman"=>"Oman", "Pakistan"=>"Pakistan", "Palau"=>"Palau",
           "Palestinian Territory, Occupied"=>"Palestinian Territory, Occupied", "Panama"=>"Panama",
           "Papua New Guinea"=>"Papua New Guinea", "Paraguay"=>"Paraguay", "Peru"=>"Peru",
           "Philippines"=>"Philippines", "Pitcairn"=>"Pitcairn", "Poland"=>"Poland",
           "Portugal"=>"Portugal", "Puerto Rico"=>"Puerto Rico", "Qatar"=>"Qatar",
           "Reunion"=>"Reunion", "Romania"=>"Romania", "Russian Federation"=>"Russian Federation",
           "Rwanda"=>"Rwanda", "Saint Helena"=>"Saint Helena",
           "Saint Kitts and Nevis"=>"Saint Kitts and Nevis", "Saint Lucia"=>"Saint Lucia",
           "Saint Pierre and Miquelon"=>"Saint Pierre and Miquelon",
           "Saint Vincent and The Grenadines"=>"Saint Vincent and The Grenadines",
           "Samoa"=>"Samoa", "San Marino"=>"San Marino", "Sao Tome and Principe"=>"Sao Tome and Principe",
           "Saudi Arabia"=>"Saudi Arabia", "Senegal"=>"Senegal",
           "Serbia and Montenegro"=>"Serbia and Montenegro", "Seychelles"=>"Seychelles",
           "Sierra Leone"=>"Sierra Leone", "Singapore"=>"Singapore", "Slovakia"=>"Slovakia",
           "Slovenia"=>"Slovenia", "Solomon Islands"=>"Solomon Islands", "Somalia"=>"Somalia",
           "South Africa"=>"South Africa",
           "South Georgia and The South Sandwich Islands"=>"South Georgia and The South Sandwich Islands",
           "Spain"=>"Spain", "Sri Lanka"=>"Sri Lanka", "Sudan"=>"Sudan", "Suriname"=>"Suriname",
           "Svalbard and Jan Mayen"=>"Svalbard and Jan Mayen", "Swaziland"=>"Swaziland",
           "Sweden"=>"Sweden", "Switzerland"=>"Switzerland", "Syrian Arab Republic"=>"Syrian Arab Republic",
           "Taiwan, Province of China"=>"Taiwan, Province of China", "Tajikistan"=>"Tajikistan",
           "Tanzania, United Republic of"=>"Tanzania, United Republic of", "Thailand"=>"Thailand",
           "Timor-leste"=>"Timor-leste", "Togo"=>"Togo", "Tokelau"=>"Tokelau", "Tonga"=>"Tonga",
           "Trinidad and Tobago"=>"Trinidad and Tobago", "Tunisia"=>"Tunisia", "Turkey"=>"Turkey",
           "Turkmenistan"=>"Turkmenistan", "Turks and Caicos Islands"=>"Turks and Caicos Islands",
           "Tuvalu"=>"Tuvalu", "Uganda"=>"Uganda", "Ukraine"=>"Ukraine",
           "United Arab Emirates"=>"United Arab Emirates", "United Kingdom"=>"United Kingdom",
           "United States"=>"United States",
           "United States Minor Outlying Islands"=>"United States Minor Outlying Islands",
           "Uruguay"=>"Uruguay", "Uzbekistan"=>"Uzbekistan", "Vanuatu"=>"Vanuatu",
           "Venezuela"=>"Venezuela", "Viet Nam"=>"Viet Nam",
           "Virgin Islands, British"=>"Virgin Islands, British",
           "Virgin Islands, U.S."=>"Virgin Islands, U.S.", "Wallis and Futuna"=>"Wallis and Futuna",
           "Western Sahara"=>"Western Sahara", "Yemen"=>"Yemen", "Zambia"=>"Zambia",
           "Zimbabwe"=>"Zimbabwe"
           );

           $select = $this->requiredAttr( $element, 'select', false );
           $name = $this->requiredAttr( $element, 'name', false );
           $id = $this->getUnquotedAttr( $element, 'id', $name );
           $this->compiler->write( '<select name = "' . $name . '" id = "' . $id . '">' );
           foreach ( $countries as $value => $label ) {
               $this->compiler->write( '<option value = "' . $value . '" <?php echo (("' . $value . '"==' . $select . ')?"selected=\"selected\"":""); ?>>' . $label . '</option>' );
           }
           $this->compiler->write( '</select>' );
    }

    /**
     * Displays a drop-down of states
     *
     * @param string required select - the state you want to see selected
     * @param string required name - the name you'd like to give this element
     * @param string optional id - the id you want for this element (defaults to name)
     */
    public function handleElementStates(DOMElement $element)
    {
        static $states = array(
            '' => '&nbsp;',
            "AL" => "Alabama", "AK" => "Alaska", "AZ" => "Arizona", "AR" => "Arkansas",
            "CA" => "California",    "CO" => "Colorado", "CT" => "Connecticut", "DE" => "Delaware", "DC" => "D.C.",
            "FL" => "Florida", "GA" => "Georgia", "HI" => "Hawaii", "ID" => "Idaho",
            "IL" => "Illinois", "IN" => "Indiana", "IA" => "Iowa", "KS" => "Kansas",
            "KY" => "Kentucky", "LA" => "Louisiana", "ME" => "Maine", "MD" => "Maryland",
            "MA" => "Massachusetts", "MI" => "Michigan", "MN" => "Minnesota", "MS" => "Mississippi",
            "MO" => "Missouri", "MT" => "Montana", "NE" => "Nebraska", "NV" => "Nevada",
            "NH" => "New Hampshire", "NM" => "New Mexico", "NJ" => "New Jersey", "NY" => "New York",
            "NC" => "North Carolina", "ND" => "North Dakota", "OH" => "Ohio", "OK" => "Oklahoma",
            "OR" => "Oregon", "PA" => "Pennsylvania", "RI" => "Rhode Island", "SC" => "South Carolina",
            "SD" => "South Dakota", "TN" => "Tennessee", "TX" => "Texas", "UT" => "Utah",
            "VT" => "Vermont", "VA" => "Virginia", "WA" => "Washington", "WV" => "West Virginia",
            "WI" => "Wisconsin", "WY" => "Wyoming"
        );

        $select = $this->requiredAttr($element, 'select', false);
        $name = $this->requiredAttr($element, 'name', false);
        $id = $this->getUnquotedAttr($element, 'id', $name);

        $this->compiler->write('<select name = "' . $name . '" id = "' . $id . '">');

        foreach ($states as $abbr => $full) {
            $this->compiler->write('<option value = "' . $abbr . '" <?php echo (("' . $abbr . '"==' . $select . ')?"selected=\"selected\"":""); ?>>' . $full . '</option>');
        }

        $this->compiler->write('</select>');
    }
}

?>
