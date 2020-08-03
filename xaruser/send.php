<?php
/**
 * @package modules
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007,2008,2009 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

/*Prepare and send the email
 *@author John Cox
 *@author Jo Dalle Nogare
 *
 */
function recommend_user_send($args)
{
    extract($args);

    /* Get parameters */
    if (!xarVarFetch('username',   'str:1:',    $username)) return;
    if (!xarVarFetch('useremail',  'str:1:100', $useremail)) return;
    if (!xarVarFetch('fname',      'str:1:',    $fname, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('fnamelist',  'array',     $fnamelist, array(), XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('info',       'str:1:100', $info, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('infolist',   'array',     $infolist, array(), XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('usernote',   'str:1:',    $usernote, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('usersubject','str:1:',    $usersubject, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('addfriends', 'int:0:',    $addfriends, 0, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('notify',     'str:1:',    $notify, '', XARVAR_NOT_REQUIRED)) return;
    
    if (!xarVarFetch('source',     'str:1:',    $source, '', XARVAR_NOT_REQUIRED)) return;

    $newinfo=array();
    $newname=array();
    /*  retained the original info and fname for backward compat
     */
    /* Confirm authorisation code. */
    if (!xarSecConfirmAuthKey()) return;
    /* Security Check */
    if(!xarSecurityCheck('OverviewRecommend')) return;
    // check if multiple emails allowed
    $usemultiple =xarModGetVar('recommend','recommendperpage');
    // see if we want to use invites
    $useinvites = xarModGetVar('recommend','useinvites');
    // do we allow users to opt out?
    $allowoptout = xarModGetVar('recommend','allowoptout');

    if ($usemultiple>1) {
        $multiplesend = TRUE;
    } else {
        $multiplesend = FALSE;
        //set the information in the array also used for multiples so we can treat it together 
        $infolist[0]=$info;
        $fnamelist[0]=$fname;
    }

    /* just get an array of values from user where email is set, or the fname is but email empty*/
    foreach ($infolist as $key=>$infoemail) {
        if (isset($infoemail) && $infoemail !='') {
            $newinfo[$key]=$infoemail;
            $newname[$key]= isset($fnamelist[$key])?$fnamelist[$key]:'';
        } elseif (isset($fnamelist[$key]) && $fnamelist[$key] !='') {
            $newname[$key]= $fnamelist[$key];
            $newinfo[$key]='';
        }
    }
    $infolist = $newinfo;
    $fnamelist = $newname;
    $countInvalid = 0;

    $invalid=array();
    if (count($infolist) > 0 and !empty($infolist)) {
        foreach ($infolist as $key=>$infoemail) {
            $test= xarVarValidate('email',$infoemail,true);
            if ((FALSE == $test) && count($infolist)>1) { //multiple friend invites
                $invalid['infolist'][$key] = xarML('This is an invalid email, please correct it.');
            } elseif (FALSE == $test) {
              $invalid['info'] = xarML('You must enter a valid email address.');
            }
        }
    } 


    $countInvalid = count($invalid);
   $useinvites       = xarModGetVar('recommend','useinvites');
    if ($countInvalid > 0) {
        $authid = xarSecGenAuthKey();

        return xarTplModule('recommend', 'user', 'main',
          array('invalid'=>$invalid, 
                'multiplesend' => true, 
                'authid'=>$authid, 
                'info'=>$info, 
                'infolist'=>$infolist, 
                'fname'=>$fname,
                'fnamelist'=>$fnamelist,
                'subject'=>$usersubject,
                'useinvites' => $useinvites,
                'source'=>$source,
                'authid' => xarSecGenAuthKey()
                )
                
                );
    }


    //Check and see if any are a current user and keep them in an array
    // - we won't invite them, but will let the inviter know later
    $existing['email'] = array();
    $existing['fname'] = array();
    $existing['uid'] = array();
    $uidlist = array();
    if (count($infolist) > 0) {
        foreach ($infolist as $key=>$infoemail) {
            // next check the email doesn't already belong to a registered user
            $iscurrentuser = xarModAPIFunc('roles','user','get',array('email'=>$infoemail));
            if (is_array($iscurrentuser)) {
                $existing['email'][$key] = $infoemail;
                $existing['fname'][$key] = $fnamelist[$key];
                $existing['uid'][$key] = $iscurrentuser['uid'];
            }
       }
    } 


    //now get the array of valid emails to send - we remove existing users
    $infolist = array_diff($infolist,$existing['email']);
    $newfnamelist = array(); //array for user names with valid emails
    foreach ($infolist as $key=>$email) {
       $newfnamelist[$key] = $fnamelist[$key];
    }
    $fnamelist = $newfnamelist;

  
    //check we still have a valid email left and only then continue to send
    $validemails = count($infolist);

    if (!empty($infolist) && $validemails > 0) {
        /* Statistics */

        $date = time();
        $numbersentprev = xarModGetVar('recommend', 'numbersent');
        $numbersent = $numbersentprev + 1;
        xarModSetVar('recommend', 'numbersent', $numbersent);
        //if ($multiplesend) {
        xarModSetVar('recommend', 'lastsentemail', end($infolist));
        xarModSetVar('recommend', 'lastsentname', end($fnamelist));
  
        xarModSetVar('recommend', 'date', $date);
        xarModSetVar('recommend', 'username', $username);
        if (isset($usersubject) && $usersubject !='') {
           $subject = $usersubject;
        } else {
         //make a default from config setting
            $sitename=xarModGetvar('themes','SiteName');
            $subject = xarModGetVar('recommend','title')?xarModGetVar('recommend','title') :$sitename;
            $subject= preg_replace('/%%sitename%%/', $sitename, $subject);
        }
        $message = xarModGetVar('recommend', 'template');

        if (!isset($username) || $username = '') {
           $fromname=xarModGetVar('recommend','username');
        } else {
          $fromname = $username;
        }
        /* Prepare to process entities in email message
        * Do ones common to single and multiple emailing
        */
        /*
        $trans = get_html_translation_table(HTML_ENTITIES);
        $trans = array_flip($trans);

        if (xarModGetVar('recommend', 'usernote')){
           $message .= "\n";
           $message .= strtr($usernote, $trans);
        }
        */
        $defaultmessage = xarModGetVar('recommend', 'template');
        if (xarModGetVar('recommend', 'usernote')){
           if (!empty($usernote)) {
              $message = $usernote;
           } else {
              $message = $defaultmessage;
           }
        }
        $htmlmessage ='';
        if (xarModGetVar('recommend', 'usernote')){
            $htmlmessage .= "<br /><br />";
            //$htmlmessage .= strtr(xarVarPrepHTMLDisplay($usernote), $trans);
            $htmlmessage = nl2br(htmlspecialchars($message));
        }
        //we now have a string with <br />s
        
        $textmessage = preg_replace("/[\n]+/","\r\n\r\n",$message);

        $message = preg_replace('/%%name%%/', $fromname,  $textmessage);

        $htmlmessage = preg_replace('/%%name%%/', $fromname, $htmlmessage);

        $sitename=xarModGetvar('themes','SiteName');
        $siteurl = xarServerGetBaseURL();
        $message = preg_replace('/%%sitename%%/', $sitename, $message);
        $message = preg_replace('/%%siteurl%%/', $siteurl, $message);
        $htmlmessage = preg_replace('/%%sitename%%/', $sitename, $htmlmessage);
        $htmlmessage = preg_replace('/%%siteurl%%/', $siteurl, $htmlmessage);
        $subject= preg_replace('/%%sitename%%/', $sitename, $subject);

        //Now get an invite code, prepare and send email for each in the info list

        foreach ($infolist as $key=>$info) { // we have already checked the email
            /* Prepare the invite code if invite is set on */
            $icode = '';
            
            $thisuseroptout = FALSE;
            if (TRUE == $allowoptout) {
                 //check if the user wants to opt out - could be multiple here
                 $dooptout = xarModAPIFunc('recommend','user','get', array('iemail' =>$info));

                 if (is_array($dooptout)) $optout = current($dooptout);
                 if ($dooptout['icode'] == 'optout') {
                    $thisuseroptout = TRUE;
                 }
                
            }
            $icode = xarModAPIFunc('recommend','user','createidcode');
 
            if ($thisuseroptout == FALSE) {//continue on

                // used as default for recommend
                $message = preg_replace('/%%toname%%/',$fnamelist[$key],  $message);
                $htmlmessage = preg_replace('/%%toname%%/', $fnamelist[$key], $htmlmessage);
         
                /* startnew message for invites*/
                $finaltextmessage= xarTplModule('recommend', 'user', 'recommend',
                array('username'    => $username,
                      'friendname'  => $fnamelist[$key],
                      'useremail'   => $useremail,
                      'info'        => $info,
                      'usernote'    => $usernote,
                      'sitename'    => $sitename,
                      'siteurl'     => $siteurl,
                      'useinvites'  => $useinvites,
                      'allowoptout'  => $allowoptout,
                      'icode'       => $icode,
                      'message'     => $message),
                      'text');

                $finalhtmlmessage= xarTplModule('recommend', 'user','recommend',
                array('username'     => $username,
                      'friendname'   => $fnamelist[$key],
                      'useremail'    => $useremail,
                      'info'         => $info,
                      'usernote'     => $usernote,
                      'sitename'     => $sitename,
                      'siteurl'      => $siteurl,
                      'useinvites'   => $useinvites,
                      'allowoptout'  => $allowoptout,
                      'icode'        => $icode,
                      'message'      => $htmlmessage),
                      'html');

                /* let's send the email now */
                $fromname=$useremail;
                if (!xarModAPIFunc('mail', 'admin', 'sendmail',
                array('info'         => $info,
                      'name'         => $fnamelist[$key],
                      'subject'      => $subject,
                      'htmlmessage'  => $finalhtmlmessage,
                      'message'      => $finaltextmessage,
                      'usetemplates' => false,
                      'from'         => $useremail,
                      'fromname'     => $fromname))) return;

                //Save the invite
                $saveinvites = xarModGetVar('recommend','saveinvites');
                $saveinvites = TRUE; //<jojo> must be true for now, leave code here
                if (TRUE == $saveinvites ) {
                    $args = array();
                    if (xarUserIsLoggedIn()) {
                       $uid = xarUserGetVar('uid');
                    } else {
                       $uid = 0; //non registered
                    }
                    $args['uid']       = $uid;
                    $args['uname']     = $username;
                    $args['iemail']    = $info;
                    $args['idate']     = time();
                    $args['iuname']    = $fnamelist[$key];
                    $args['joindate']  = '';
                    $args['icode']     = $icode;

                    //create the invite in the db
                    $inviteid = xarModAPIFunc('recommend','admin','create',$args);
                }
            }
        } //end of the loop for all in the array
    } //endif validemails > 0 and infolist !empty

    //prepare the info for the final message
    $invitestring = '';
    if (!empty($infolist)) {
        foreach ($infolist as $info) {
            $invitestring .= '<br />'.$info;
        }
    }
    $existingstring='';
    if (is_array($existing) && current($existing['email'])=='') $existing = ''; //no existing email
    if (!empty($existing) ) {
        foreach ($existing['email'] as $k=>$existingmember) {
            $existingstring .='<br />'.$existingmember;
        }
    }
    $message ='';
    $messagetype = 0;
    if (!empty($infolist) && !empty($existing)) {
        $message = xarML("Thank You!\n\nYour invitation has been sent to the following: \n\n#(1)",$invitestring);
        $message .= xarML("\n\nWe appreciate your inviting the following but the emails already belong to existing memmbers:\n>  #(1)",$existingstring);
        $messagetype = 1;
    } elseif (empty($infolist) && !empty($existing)) {
        $message = xarML("Thank You!\n\nWe appreciate your inviting the following but the emails already belong to existing memmbers:\n  #(1)",$existingstring);
        $messagetype = 2;
    } elseif (!empty($infolist) && empty($existing)){
        $message = xarML("Thank You!\n\nYour invitation has been sent to the following:\n#(1)",$invitestring);
        $messagetype = 3;
    }
    $args =   array('multiplesend' => true, 
                    'info'=>$info, 
                    'infolist'=>$infolist,
                    'fnamelist'=>$fnamelist, 
                    'existing'=>$existing,
                    'subject'=>$subject,
                    'message'=>$message, 
                    'messagetype' => $messagetype,
                    'useinvites' => $useinvites,
                    );


    return xarTplModule('recommend', 'user', 'main', $args);

    /* Return */
    return true;
}
?>