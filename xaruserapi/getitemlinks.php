<?php
/**
 * Pass item links
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
 * utility function to pass individual item links to whoever
 *
 * @param $args['itemtype'] item type (optional)
 * @param $args['itemids'] array of item ids to get
 * @param $args['field'] field to return as label in the list (default 'name' - adapt as needed for your objects)
 * @returns array
 * @return array containing the itemlink(s) for the item(s).
 */
function recommend_userapi_getitemlinks($args)
{
    extract($args);

    $itemlinks = array();
    if (empty($itemtype)) {
        $itemtype = 0;
    }
    if (empty($field)) {
        $field = 'name'; // adapt as needed for your own objects
    }
    // Get all the items
    $items = xarModAPIFunc('dynamicdata','user','getitems',
                           array('module'    => 'recommend',
                                 'itemtype'  => $itemtype,
                                 'itemids'   => $itemids,
                                 'fieldlist' => array($field)));
    if (empty($items)) {
        // if none are found, we pass an empty array. This will prevent errors in the calling modules
        return array();
    }

    // if we didn't have a list of itemids, return all the ids of the items we found
    if (empty($itemids)) {
        $itemids = array_keys($items);
    }

    foreach ($itemids as $itemid) {
        if (isset($items[$itemid][$field])) {
            $label = xarVarPrepForDisplay($items[$itemid][$field]);
        } else {
            $label = xarML('Item #(1)',$itemid);
        }
        // Set the link to this specific item
        $itemlinks[$itemid] = array('url'   => xarModURL('recommend', 'user', 'display',
                                                         array('itemtype' => empty($itemtype) ? null : $itemtype,
                                                               'itemid' => $itemid)),
                                    'title' => xarML('Display Item'),
                                    'label' => $label);
    }
    // Return all the links we have created
    return $itemlinks;
}

?>
