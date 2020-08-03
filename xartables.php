<?php
/**
 * Recommend Table function
 *
 * @package modules
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Recommend
 * @link http://xaraya.com/index.php/release/772.html
 * @author Jo Dalle Nogare <jojodee@xaraya.com>
 */
/**
 * Return Recommend table names to xaraya
 *
 * @access private 
 * @return array 
 */
function recommend_xartables()
{ 
    /* Initialise table array */
    $xarTables = array(); 

    $recommendTable = xarDBGetSiteTablePrefix() . '_recommend';
    /* Set the table name */
    $xarTables['recommend'] = $recommendTable;

    /* Return the table information */
    return $xarTables;
}

?>