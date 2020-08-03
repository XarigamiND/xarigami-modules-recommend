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
 * Update invite record
 *
 * @returns bool
 * @return true on success, false on failure
 */
function recommend_adminapi_update($args)
{
    // Get arguments from argument array
    extract($args);

    if (!isset($optout)) $optout = 0;

    $invalid = array();
    if (!isset($inviteid) && $optout == 0) {
        $invalid[] = xarML('inviteid ');
    }
    if (!isset($iemail) && ($optout == 1)) {
       $invalid[] = xarML('iemail');
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    join(', ',$invalid), 'admin', 'update','Recommend');
         xarErrorSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return false;
    }
    $inviteinfo = xarModAPIFunc('recommend', 'user', 'get', array('inviteid' => $inviteid));

    if (!isset($iuname) || $iuname =='') $iuname = $inviteinfo['iuname'];
    if (!isset($iuid) || $iuid <=0) $iuid = $inviteinfo['iuid'];

    if ($optout == 1) {
        $icode = 'optout';
    } elseif ($istate != 3 ){ //the user is registered but not activated their account yet
       //do not reset the icode
        $icode = $inviteinfo['icode'];
    } else {
       $icode = ''; // if we are inhere - then this will either need to be set to empty, or already is if not opting out
    }

    if ($optout == 1) {
        $joindate = $inviteinfo['joindate'];
    }elseif ($istate != 3){ // well, this is join date on date of joining - not activating!
        $joindate = 0;
    }elseif (($istate ==3) && ($inviteinfo['joindate']==0)) {
        $joindate = time();
    } else{
        $joindate =  $inviteinfo['joindate'];
    }

    //who invited this person
    $inviteruid = $inviteinfo['uid'];

      // Get database setup
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $recommendTable = $xartable['recommend'];

    $bindvars=array();

    if ($optout == 1) { // we really only need be concerned with the icode being reset for all email entries matching
       $query = "UPDATE $recommendTable
                 SET xar_icode = 'optout'
                 WHERE xar_iemail = ?";
        $bindvars = array($iemail);
    } else {
    $query = "UPDATE $recommendTable
              SET xar_iuname =?,
                  xar_joindate =?,
                  xar_icode =?,
                  xar_iuid =?
            WHERE xar_id = ?";

        $bindvars = array((string)$iuname, (int)$joindate, (string)$icode, (int)$iuid, (int)$inviteid);
    }
    $result = $dbconn->Execute($query,$bindvars);

    if (!$result) return;

    if ($optout != 1) { //call hooks only if not opting out
        $item['module'] = 'recommend';
        $item['itemid'] = $inviteid;
        $item['iuid'] = $iuid;
        $item['itemtype'] = 0;
        xarModCallHooks('item', 'update', $inviteid, $item);
    }
    return true;
}
?>