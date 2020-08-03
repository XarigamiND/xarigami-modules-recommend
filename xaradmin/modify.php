<?php
/**
 * Modify an item
 *
 * @package modules
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * modify an item
 *
 * This function shows a form in which the user can modify the item
 *
 * @param id itemid The id of the dynamic data item to modify
 */
function recommend_admin_modify($args)
{
    extract($args);

    if(!xarVarFetch('itemid',   'id', $itemid,   NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('objectid', 'id', $objectid, NULL, XARVAR_DONT_SET)) {return;}

    // See if we have to use the universal objectid
    if (!empty($objectid)) {
        $itemid = $objectid;
    }
    // Check if we still have no id of the item to modify.
    if (empty($itemid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'item id', 'admin', 'modify', 'recommend');
        xarErrorSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return $msg;
    }

    // get the Dynamic Object defined for this module (and itemtype, if relevant)
    $object = xarModAPIFunc('dynamicdata','user','getobject',
                             array('module' => 'recommend',
                                   'itemtype' => 0,
                                   'itemid' => $itemid));
    if (!isset($object)) return;

    // get the values for this item
    $newid = $object->getItem();
    if (!isset($newid) || $newid != $itemid) return;

    // Check if the user can Edit in the dyn_example module, and then specifically for this item.
    // We pass the itemid to the SecurityCheck
    //if (!xarSecurityCheck('EditDynExample',1,'Item',$itemid)) return;

    // Add the admin menu
    $data['menulinks'] = xarModAPIFunc('recommend','admin','getmenulinks');
    $data['itemid'] = $itemid;
    $data['object'] =& $object;

    $item = array();
    $item['module'] = 'recommend';
    $hooks = xarModCallHooks('item','modify',$itemid,$item);
    if (empty($hooks)) {
        $data['hooks'] = '';
    } elseif (is_array($hooks)) {
        $data['hooks'] = join('',$hooks);
    } else {
        $data['hooks'] = $hooks;
    }

    // Return the template variables defined in this function
    return $data;
}

?>