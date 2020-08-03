<?php
/**
 *  Update
 *
 * @package modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007,2008 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * Delete optout for a specific email address (opt back in)
 *
 * @returns bool
 * @return true on success, false on failure
 */
function recommend_adminapi_delete($args)
{
    // Get arguments from argument array
    extract($args);

    if (!xarSecurityCheck('AdminRecommend')) return;

    $invalid = array();
    if (!isset($iemail) || ($optin != 1)) {
       $invalid[] = xarML('iemail');
    }

    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    join(', ',$invalid), 'admin', 'update','Recommend');
         xarErrorSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return false;
    }
    $optininfo = xarModAPIFunc('recommend', 'user', 'get', array('iemail' => $iemail));

    if ($optininfo['icode'] !='optout') return;

      // Get database setup
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $recommendTable = $xartable['recommend'];

    $bindvars=array();

    $query = "DELETE FROM $recommendTable
            WHERE xar_iemail = ?";

        $bindvars = array((string)$iemail);

    $result = $dbconn->Execute($query,$bindvars);

    if (!$result) return;
    //do not call hooks here
    return true;
}
?>