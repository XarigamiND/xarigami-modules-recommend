<?php
/**
 * When a user successfully registers an account in roles, update the invitation code entry
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
* When a user successfully registers and creates and account in roles, update the invitation code entry
 *
 */
function recommend_userapi_createhook($args)
{
    extract($args);

    // validate args
    if (!isset($objectid) || !is_numeric($objectid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'object ID', 'user', 'createhook', 'Recommend');
        xarErrorSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // set defaults
    if (!isset($extrainfo) || !is_array($extrainfo)) $extrainfo = array();

    // get module name
    if (empty($modname)) {
        if (!empty($extrainfo['module'])) {
            $modname = $extrainfo['module'];
        } else {
            $modname = xarModGetName();
        }
    }
  
    
    // get module ID
    $modid = xarModGetIDFromName($modname);
    if (empty($modid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'module name', 'userapi', 'createhook', 'Recommend');
        xarErrorSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // get itemtype
    if (!isset($itemtype) || !is_numeric($itemtype)) {
         if (isset($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
             $itemtype = $extrainfo['itemtype'];
         } else {
             $itemtype = 0;
         }
    }

    // don't do anything if a group is updated , and don't let anyone use this hook except roles 
   if ($modname != 'roles' || $itemtype !=0)  {
        //return $extrainfo;
    }
    if (!isset($uid)) $uid = $objectid;
    
    $userdata = xarModAPIFunc('roles','user','get',array('uid'=>(int)$uid));

    $iemail = $userdata['email'];
    $iuid = $uid;
    $iuname = $userdata['uname'];
    $istate = $userdata['state'];
    
 
    if (!is_array($userdata)) {
        return $extrainfo; //no user created yet
    }
 
    //try and get the session code - new invitee?
    $icode = xarSessionGetVar('icode');

    if (!isset($icode) || empty($icode)) {
        //Try and get the invite info from the uid - it means the user is already created and we are updating
        $icodedata = xarModAPIFunc('recommend','user','get',array('iuid'=>$uid));

        if (is_array($icodedata)) {
              $icode = $icodedata['icode'];
        } else {
            $icode = '';
        }
    }

    if (!isset($icode) || empty($icode)) {
       return $extrainfo; // a problem - we can't go on ..
    }
    //now confim with  icode against email and uid
    $inviteinfo = xarModAPIFunc('recommend', 'user', 'get', array('icode'=>$icode,'iemail' => $iemail, 'iuid'=>$uid));
    $inviteid = $inviteinfo['inviteid'];
      
    if (!is_array($inviteinfo) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return $extrainfo;
    
    //we want to update the user invitation but not destroy the icode there until the user is validated and active
    //this could happen immedidately on account creation, or require email validation or approval from admin
    // if not active, make sure we update only uid to the invite 
     
    $requirevalidation = xarModGetVar('registration','requirevalidation');
    $explicitapproval = xarModGetVar('registration','explicitapproval');
    
    if (($requirevalidation == TRUE) || ($explicitapproval == TRUE)) {
        if ($istate !=3) { //activated user
        //update the UID, uname and email but leave the icode so we know they are registered, but not active yet       
        $updatedinvite = xarModAPIFunc('recommend','admin','update',
             array('module'=>$modname,'inviteid'=>$inviteid,'iuid'=>$iuid,'iuname'=>$iuname, 'iemail'=>$iemail, 'istate'=>(int)$istate));    
        return $extrainfo;
        }
    }
  
  
    // if we have nothing to do
    if ($inviteid <= 0) return $extrainfo;

    if (!isset($istate)) $istate=0;

    // now update the invite table
    $updatedinvite = xarModAPIFunc('recommend','admin','update',
        array('module'=>$modname,'inviteid'=>$inviteid,'iuid'=>$iuid,'iuname'=>$iuname, 'istate'=>$istate,'iemail'=>$iemail));    
    //add the new user as a friend to the inviter's list of friends
   // if (isset($istate) && $istate !=3) { //newly added - not yet activated  - could be a record update so don't want to add twice
    //    $friendadded = xarModAPIFunc('recommend','user','addtofriendlist',array('inviteruid'=>$inviteruid,'friend'=>$iuid));
    //add the inviter to the newly registered user's list of friends
      // $friendaddedtonewuser = xarModAPIFunc('recommend','user','addtofriendlist',array('inviteruid'=>$iuid,'friend'=>$inviteruid));
    //}
    // success

    return $extrainfo;
}

?>