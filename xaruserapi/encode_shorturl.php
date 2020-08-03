<?php
/**
 * @package modules
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @package Xaraya modules
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

/* Support for short URLs (user functions)
 *
 *  Return the path for a short URL to xarModURL for this module
 *
 * @author Jo Dalle Nogare
 * @param  $args the function and arguments passed to xarModURL
 * @returns string
 * @return path to be added to index.php for a short URL, or empty if failed
 */
function recommend_userapi_encode_shorturl($args)
{
    /* Get arguments from argument array */
    extract($args);
    /* Check if we have something to work with */
    if (!isset($func)) {
        return;
    }
    $path = '';
    /* if we want to add some common arguments as URL parameters below */
    $join = '?';

    /* we can't rely on xarModGetName() here -> you must specify the modname ! */
    $module = 'recommend';

    /* specify some short URLs relevant to your module */
    if ($func == 'sendtofriend') {
        if (isset($message)) {
                $path = '/' . $module . '/sendtofriend/1/'.$aid;
                if (isset($info)) {
                    $path .= '/' . $info;   
                }
        }elseif (isset($aid) && is_numeric($aid)) {
            $path = '/' . $module . '/sendtofriend/'.$aid;
        }else{
           //hmmm..
        }
    }else if ($func == 'main') {
        $path = '/' . $module . '/';
        if (isset($message) && is_numeric($message)) {
            $path = '/' . $module . '/' . $message;
            if (isset($info)) {
                $path .= '/' . $info;   
            } elseif (isset($infolist)) {
               $newpath = '';
               foreach ($infolist as $info) {
                 $newpath .= '/'.$info;
               }
                $path .= $newpath;
            }
        }
    }else{
    //ooohhh
    }

    return $path;
}

?>