<?php
/**
 * Get invite
 *
 * @package Xaraya modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007,2008 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * Get all response items
 *
 * @param numitems $ the number of items to retrieve (default -1 = all)
 * @param startnum $ start with this item number (default 1)
 * @returns array
 * @return array of items, or false on failure
 * @raise BAD_PARAM, DATABASE_ERROR, NO_PERMISSION
 */
function recommend_userapi_get($args)
{
    extract($args);

    // Argument check
    if (!isset($icode) && !isset($inviteid) && !isset($iemail) && !isset($iuid)) {

        // don't throw an exception here, just return and let hooks continue;
        /*    $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'Recommend vars', 'user', 'get',
                    'Recommend');
                xarErrorSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return false;
        */
        return;
    }

    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $recommendTable = $xartable['recommend'];

    $bindvars=array();
    $wherelist = array();
    $where = '';
    $matchemail = xarModGetVar('recommend','matchemail');
    if (!empty($icode) && $matchemail && !empty($iemail) && empty($uid)) {
        //we want to grab an invite code only if it belongs to the email as specified
        $fieldlist = array('icode','iemail',);
        foreach ($fieldlist as $field) {
            if (isset($$field)) {
                $wherelist[] = "xar_$field = ?";
                $bindvars[] = $$field;
            }
        }
    } elseif (!empty($icode) && empty($uid)) {
        $wherelist[] = "xar_icode = ?";
        $bindvars[] = $icode;
    } elseif (empty($icode)) {
        if (isset($inviteid) && $inviteid > 0) {
            $wherelist[] = "xar_id = ?";
            $bindvars[] = $inviteid;

        }elseif (isset($iuid) && $iuid !=0) { //we are updating or getting info
              $wherelist[] = "xar_iuid = ?";
              $bindvars[] = $iuid;
        }elseif (isset($iemail) && !empty($iemail)) {
              $wherelist[] = "xar_iemail = ?";
              $bindvars[] = $iemail;
        }
    }

    if (count($wherelist) > 0) {
            $where = "WHERE " . join(' AND ',$wherelist);
    }

    $query =   "SELECT  xar_id,
                        xar_uid,
                        xar_uname,
                        xar_iemail,
                        xar_idate,
                        xar_iuname,
                        xar_joindate,
                        xar_icode,
                        xar_iuid
                     FROM $recommendTable
                    $where";

        $result = $dbconn->Execute($query,$bindvars);
        if (!$result) return;

    list($id,$uid, $uname,$iemail,$idate,$iuname,$joindate,$icode,$iuid) = $result->fields;

            $invitedata =array('inviteid'   => (int)$id,
                            'uid'           => (int)$uid,
                            'uname'         => $uname,
                            'iemail'        => $iemail,
                            'idate'         => $idate,
                            'iuname'        => $iuname,
                            'joindate'      => $joindate,
                            'icode'         => $icode,
                            'iuid'          => $iuid
                            );
    $result->Close();

    /* Return the items */
    return $invitedata;
}
?>