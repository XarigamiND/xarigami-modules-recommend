<?php
/**
 * Display a response
 *
 * @package Xaraya modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007,2008 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * Display a response
 *
 * This is a standard function to provide detailed informtion on a single item
 * available from the module.
 *
 * @param  $args an array of arguments (if called by other modules)
 * @param  $args ['objectid'] a generic object id (if called by other modules)
 * @param  $args ['iid'] the item id used for this example module
 * @return array $data The array that contains all data for the template
 */
function recommend_user_display($args)
{
    extract($args);
    
    //only admin should view the responses
    if (!xarSecurityCheck('AdminRecommend')) return;
    
    if (!xarVarFetch('itemid', 'id', $itemid)) return;
    if (!xarVarFetch('objectid', 'id', $objectid, $objectid, XARVAR_NOT_REQUIRED)) return;

    if (!empty($objectid)) {
        $scrid = $objectid;
    }
    $lastview = xarSessionGetVar('recommend.LastView');
    if (!empty($lastview)) {
        $lastview= unserialize($lastview);
    }

    $data['itemid'] = (int)$itemid;
     $data['inviteid'] =  $data['itemid'];


    $item['returnurl'] = xarModURL('recommend','admin','display',array('itemid' => $itemid));
    $item['module'] = 'recommend';
    $item['itemid'] = $itemid;
    $item['itemtype'] = 0;
    $hooks = xarModCallHooks('item','display',$itemid,$item);

    if (empty($hooks)) {
        $data['hookoutput'] = '';
    } else {
        $data['hookoutput'] = $hooks;
    }        


    return $data;
}
?>