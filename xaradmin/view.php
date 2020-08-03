<?php
/**
 * View items
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * view items
 */
function recommend_admin_view()
{

    if(!xarVarFetch('startnum', 'isset', $data['startnum'], NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('catid',    'isset', $data['catid'],    NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('startnum', 'int',   $startnum, 1, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('numitems', 'int',   $numitems, 20, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('whereis', 'str',   $whereis,  NULL, XARVAR_DONT_SET)) {return;}
    if (!xarSecurityCheck('EditRecommend')) return;
    $data['menulinks'] = xarModAPIFunc('recommend','admin','getmenulinks');
    $data['itemsperpage'] = xarModGetVar('recommend','itemsperpage');
    $invitelist = xarModAPIFunc('dynamicdata','user','getitems',
                             array('module'    => 'recommend',
                                   'itemtype'  => 0,
                                   'catid'     => $data['catid'],
                                   'numitems'  => $data['itemsperpage'],
                                   'startnum'  => $data['startnum'],
                                   'where'     => $whereis,
                                   'status'    => 1,      // only get the properties with status 1 = active
                                   'getobject' => 1));    // get back the object list
    $data['invitelist'] =  $invitelist;
    $data['properties'] = $invitelist->getProperties();
    $data['values'] = $invitelist->items;
    $data['whereis'] = $whereis;
    $data['wherearg']='';
    if (isset($whereis)) {
      $data['wherearg'] = preg_match('/optout/', $whereis)?'optout':'sendfriend';
    }

    // Return the template variables defined in this function
    return $data;
}

?>