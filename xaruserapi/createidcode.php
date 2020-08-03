<?php
/**
 * Invitation code creation
 *
 * @package Xaraya modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/*
 * Author jojodee
*/
define('_SYLLABLES', "*abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz0123456789");
define('_MAKEPASS_BOX', 5000);
define('_MAKEPASS_LEN', 8);
define('SALT_LENGTH', 9);
function _make_seed()
{
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

function recommend_userapi_createidcode()
{
    $result = '';
    mt_srand(_make_seed());
    $syllables = _SYLLABLES;
    $len = strlen($syllables) - 1;
    $box = ""; 

    // create box
    for($i = 0; $i < _MAKEPASS_BOX; $i++) {
        $ch = $syllables[mt_rand(0, $len)];
        // about 20% upper case letters
        if (mt_rand(0, $len) % 5 == 1) {
            $ch = strtoupper($ch);
        }
        // filling up the box with random chars
        $box .= $ch;
    }


    for($i = 0; $i < _MAKEPASS_LEN; $i++) {
        $result .= $box[mt_rand(0, (_MAKEPASS_BOX - 1))];
    }    
    
    $pepper = substr(md5(uniqid(rand(), true)), 0, SALT_LENGTH);
    
    $pepper = substr($result, 0, SALT_LENGTH);

    $invitecode = sha1($pepper . $result);

    return $invitecode;
}
?>