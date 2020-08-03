<?php
/*
 *
 * @package modules
 * @copyright (C) 2005 The Digital Development Foundationn
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007,2008,2009 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * utility function pass individual menu items to the admin panels
 *
 * @author John Cox
 * @author jojodee
 * @returns array
 * @return array containing the menulinks for the main menu items.
 */
function recommend_adminapi_getmenulinks()
{ 
   /*  Security Check */
    if (xarSecurityCheck('EditRecommend', 0)) {
   
        $menulinks[] = array('url' => xarModURL('recommend', 'admin', 'modifyconfig'),
                            'title' => xarML('Modify the configuration for the recommend module'),
                            'label' => xarML('Modify Config'),
                            'active' => array('modifyconfig'));
        $menulinks[] = Array('url' => xarModURL('recommend','admin','view'),
                             'title' => xarML('Review inviations'),
                             'label' => xarML('Review Invitations'),
                             'active'=> array('view'));
    } 
    if (empty($menulinks)) {
        $menulinks = '';
    } 

    return $menulinks;
}
?>