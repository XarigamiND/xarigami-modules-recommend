<?php
/**
 * @package modules
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 */
 /*
 * @subpackage Recommend Module
 * @author John Cox
 * @author Jo Dalle Nogare
*/
$modversion['name'] = 'recommend';
$modversion['directory'] ='recommend';
$modversion['id'] = '772';
$modversion['version'] = '1.2.2';
$modversion['displayname']    = 'Recommend';
$modversion['description'] = 'Make site recommendations to friends';
$modversion['credits'] = 'xardocs/credits.txt';
$modversion['help'] = 'xardocs/help.txt';
$modversion['changelog'] = 'xardocs/changelog.txt';
$modversion['license'] = 'xardocs/license.txt';
$modversion['official'] = 1;
$modversion['author'] = 'Jo Dalle Nogare';
$modversion['contact'] = 'http://xarigami.com';
$modversion['admin'] = 1;
$modversion['user'] = 1;
$modversion['class'] = 'Complete';
$modversion['category'] = 'Content';
$modversion['dependency']= array();
$modversion['dependencyinfo']   = array(
                                    0 => array(
                                            'name' => 'core',
                                            'version_ge' => '1.4.0'
                                         )
                                );

if (false) {
    xarML('Recommend');
    xarML('Recommend site and items to friends');
}
?>