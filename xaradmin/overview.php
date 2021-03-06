<?php
/**
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007,2008 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

/**
 * The main administration function
 * @author jojodee
 */
function recommend_admin_overview()
{
    if (!xarSecurityCheck('EditRecommend')) return;

    $data=array();
    $data['menulinks'] = xarModAPIFunc('recommend','admin','getmenulinks');
    return xarTplModule('recommend', 'admin', 'main', $data,'main');
}

?>