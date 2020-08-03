<?php
/**
 *Update the invitation record when a user validates their account 
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
 *Update the invitation record when a user validates their account 
 *
 * @param array $args
 */
function recommend_userapi_updatehook($args)
{

    // We handle this with the create hook (which can update current records, too)
    return xarModAPIFunc('recommend', 'user', 'createhook', $args);
}
?>
