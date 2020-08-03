<?php
/**
 * Update an item
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 20077-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * update an item
 */
function recommend_admin_update($args)
{
    if(!xarVarFetch('itemid',   'id', $itemid,   NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('objectid', 'id', $objectid, NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('preview', 'str', $preview,  NULL, XARVAR_DONT_SET)) {return;}
    extract($args);

    if (!empty($objectid)) {
        $itemid = $objectid;
    }

    if (empty($itemid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'item id', 'admin', 'update', 'recommend');
         throw new EmptyParameterException(null,$msg);
    }

    // get the Dynamic Object defined for this module (and itemtype, if relevant)
    $object = xarModAPIFunc('dynamicdata','user','getobject',
                             array('module' => 'recommend',
                                   'itemid' => $itemid));
    if (!isset($object)) return;

    // get the values for this item
    $newid = $object->getItem();
    if (!isset($newid) || $newid != $itemid) return;

    // check the input values for this object
    $isvalid = $object->checkInput();

    // if we're in preview mode, or if there is some invalid input, show the form again
    if (!empty($preview) || !$isvalid) {
       // $data = xarModAPIFunc('recommend','admin','menu');

        $data['object'] =  $object;
        $data['itemid'] = $itemid;

        $data['preview'] = $preview;

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

        return xarTplModule('recommend','admin','modify', $data);
    }

    // update the item
    $itemid = $object->updateItem();

    if (empty($itemid)) return; // throw back
            $msg = xarML('Recommend item successfully updated.');
            xarTplSetMessage($msg,'status');
    // let's go back to the admin view
    xarResponseRedirect(xarModURL('recommend', 'admin', 'view'));

    // Return
    return true;
}
?>
