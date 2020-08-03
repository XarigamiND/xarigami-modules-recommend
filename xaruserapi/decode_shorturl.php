<?php
/**
 * @package modules
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

/* Decode short URLS (user)
 *
 * Extract function and arguments from short URLs for this module, and pass
 * them back to xarGetRequestInfo()
 *
 * @author Jo Dalle Nogare
 * @param  $params array containing the different elements of the virtual path
 * @returns array
 * @return array containing func the function to be called and args the query
 *          string arguments, or empty if it failed
 */
function recommend_userapi_decode_shorturl($params)
{
    /* Initialise the argument list we will return */
    $args = array();

    /* Analyse the different parts of the virtual path
     * $params[1] contains the first part after index.php/example
     */
    if (empty($params[1])) {
        /* nothing specified -> we'll go to the main function */
        return array('main', $args);
    }elseif (!empty($params[1]) && (preg_match('/^(\d+)/', $params[1]))) {
       
       /*probably message being returned */
       $message=$params[1];
       $args['message']=$message;
       $args['info']=$params[2];
       $paramnum = xarModGetVar('recommend','recommendperpage');
            $infolist=array();
            for ($i = 0; $i <$paramnum; $i++) {
                if (isset($params[2+$i])) {
                    $args['infolist'][]=$params[2+$i];
               }
            }
        return array('main', $args);
    } elseif ($params[1]== 'sendtofriend') {
        if (!empty($params[3]) && preg_match('/^(\d+)/', $params[3])) {
        /*looks like sendtofriend returning with message */
            $message=$params[2];
            $aid=$params[3];
            $args['message']=$message;
            $args['aid']=$aid;
            $args['info']=$params[4];

        }elseif (!empty($params[2]) && preg_match('/^(\d+)/', $params[2])) {
            $aid=$params[2];
            $args['aid']=$aid;
         }
        return array('sendtofriend', $args);
    } else {

    }
}

?>