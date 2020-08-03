<?php
/**
 * Create an invite record
 *
 * @package modules
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * Create an invite record
 * Usage : $scrid = xarModAPIFunc('recommend', 'admin', 'create', $args);
 *
 * @return articles item ID on success, false on failure
 */
function recommend_adminapi_create($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check (all the rest is optional, and set to defaults below)
    if (empty($iemail)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'title', 'admin', 'create', 'Recommend');
         throw new EmptyParameterException(null,$msg);
    }

     // Security check
     if(!xarSecurityCheck('ReadRecommend', 0)) return;
     // we don't want to error display here and distrupt, anyone that can use recommend should be able to save here if the config says so

    // Get database setup
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();
    $recommendTable = $xartable['recommend'];

    // Get next ID in table
    $nextId = $dbconn->GenId($recommendTable);

    // Add item
    $query = "INSERT INTO $recommendTable (
              xar_id,
              xar_uid,
              xar_uname,
              xar_iemail,
              xar_idate,
              xar_iuname,
              xar_joindate,
              xar_icode)
            VALUES (?,?,?,?,?,?,?,?)";
    $bindvars = array($nextId,
                      (string)  $uid,
                      (string)  $uname,
                      (string)  $iemail,
                      (int)     $idate,
                      (string)  $iuname,
                      (int)     $joindate,
                      (string)  $icode
                      );
    $result = $dbconn->Execute($query,$bindvars);
    if (!$result) return;

    // Get scrid to return
    $inviteid = $dbconn->PO_Insert_ID($recommendTable , 'xar_id');

    //update hook
       $args['inviteid'] = $inviteid;
       $args['module'] = 'recommend';
       $args['itemtype'] = $inviteid;
       $args['itemid'] = $inviteid;
       xarModCallHooks('item', 'create', $inviteid, $args);

    return $inviteid;
}

?>