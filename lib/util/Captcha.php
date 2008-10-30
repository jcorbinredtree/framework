<?php

/**
 * Captcha class definition
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
 * @author         Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license        MPL 1.1
 * @version        1.0
 * @link             http://framework.redtreesystems.com
 */

/**
 * Simple CAPTCHA class
 *
 * @static
 * @package        Utils
 */
class Captcha
{
    const WIDTH = 150;
    const HEIGHT = 20;
    
    private function __construct()
    {

    }

    
    static private function GetCaptchaString()
    {
        return 
            preg_replace_callback('/[^a-z]/i', 
                     create_function('', 'return chr(mt_rand(65, 90));'),
                     substr(
                            md5(
                                mt_rand() 
                            ), 
                            0, 
                            mt_rand(3, 5)
                     ) 
                    );
    }
    
    static public function Display($key)
    {
        $captcha = Captcha::GetCaptchaString();
        
        $_SESSION[$key] = $captcha;
        
        $im = @imagecreate(Captcha::WIDTH, Captcha::HEIGHT) or die("CAPTCHA Unsupported");
        $background_color = imagecolorallocate($im, 255, 255, 255);    
        $chars = str_split($captcha);
    
        $halfWidth = (Captcha::WIDTH / 2);
        $halfHeight = (Captcha::HEIGHT / 2);

        /*
         * text
         */
        $i = 0;        
        foreach($chars as $char){
            $text_color = imagecolorallocate($im, mt_rand(0, 125), mt_rand(0, 125), mt_rand(0, 125));
            imagechar($im, mt_rand(4, 5), mt_rand($i, $i+5), mt_rand(0, 7), $char, $text_color);
            $i += mt_rand(15, 40);
        }    
                            
        /*
         * noise
         */
        for ($numlines = mt_rand(2, 4); $numlines > 0; $numlines--) {
            $line_color = imagecolorallocate($im, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
 
            $xStart = mt_rand(0, $halfWidth);
            $yStart = mt_rand(0, $halfHeight);
            $xEnd = mt_rand($xStart, Captcha::WIDTH);
            $yEnd = mt_rand($yStart, Captcha::HEIGHT);
            imageline($im, $xStart, $yStart, $xEnd, $yEnd, $line_color);
        }        
        
        for ($pixels = Captcha::WIDTH + Captcha::HEIGHT; $pixels > 0; $pixels--) {
            $color = imagecolorallocate($im, mt_rand(125, 255), mt_rand(125, 255), mt_rand(125, 255));
            imagesetpixel($im, mt_rand(0, Captcha::WIDTH), mt_rand(0, Captcha::HEIGHT), $color);
        }        

        header("Content-type: image/jpeg");        
        imagejpeg($im, null, 100);
        imagedestroy($im);        
    }    
}

?>
