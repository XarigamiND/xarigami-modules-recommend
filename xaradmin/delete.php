<?php
/**
 * Delete an item
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * delete an item
 * @param 'itemid' the id of the item to be deleted
 * @param 'confirm' confirm that this item can be deleted
 */
function recommend_admin_delete($args)
{
    if(!xarVarFetch('itemid',   'id', $itemid,   NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('objectid', 'id', $objectid, NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('confirm', 'str', $confirm,  NULL, XARVAR_DONT_SET)) {return;}

    extract($args);

    if (!empty($objectid)) {
        $itemid = $objectid;
    }
    // Show an error when the itemid is still not set
    if (empty($itemid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'item id', 'admin', 'delete', 'recommend');
        throw new EmptyParameterException(null,$msg);
        return $msg;
    }

    // get the Dynamic Object defined for this module (and itemtype, if relevant)
    $object = xarModAPIFunc('dynamicdata','user','getobject',
                             array('module' => 'recommend',
                                   'itemid' => $itemid));
    if (!isset($object)) return;

    // get the values for this item
    $newid = $object->getItem();
    if (!isset($newid) || $newid != $itemid) return;
    $data['menulinks'] = xarModAPIFunc('recommend','admin','getmenulinks');

    if (!xarSecurityCheck('AdminRecommend',1,'Item',$itemid)) return;

    // Check for confirmation.
    if (empty($confirm)) {

        // Specify for which item you want confirmation
        $data['itemid'] = $itemid;
        $data['object'] = $object;

        // Return the template variables defined in this function
        return $data;
    }

    // If we get here it means that the user has confirmed the action
    // Check for a valid Authentication Key
    if (!xarSecConfirmAuthKey()) return;
    // Now, delete the item
    $itemid = $object->deleteItem();
    if (empty($itemid)) return;
            $msg = xarML('Recommend item successfully deleted.');
            xarTplSetMessage($msg,'status');
    // Redirect to the main view function of this module after success
    xarResponseRedirect(xarModURL('recommend', 'admin', 'view'));

    // Return
    return true;
}

?>