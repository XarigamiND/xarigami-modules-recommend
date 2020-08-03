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

/* Main user function for Recommend
 */
function recommend_user_main($args)
{
    extract($args);
    if (!xarVarFetch('message',  'str:1:', $message,   '', XARVAR_NOT_REQUIRED)) return;
    if(!xarVarFetch('info',      'str:1:', $info,      '', XARVAR_NOT_REQUIRED)) return;
    if(!xarVarFetch('infolist',  'array',  $infolist, array(), XARVAR_NOT_REQUIRED)) return;
    if(!xarVarFetch('invalid',   'array',  $invalid,  '', XARVAR_NOT_REQUIRED)) return;
    if(!xarVarFetch('existing',  'array',  $existing,  array(), XARVAR_NOT_REQUIRED)) return;
    if(!xarSecurityCheck('ReadRecommend')) return;

    xarTplSetPageTitle(xarVarPrepForDisplay(xarML('Thank You')));

    /* Generate a one-time authorisation code for this operation */
    $data['authid']            = xarSecGenAuthKey();
    $data['submit']            = xarML('Submit');
    $data['invalid']           = $invalid;
    $data['allowusersubject']  = xarModGetVar('recommend','usersubject');
    $data['defaultsubject']    = !empty($usersubject)?$usersubject:xarModGetVar('recommend','title');
    $data['howmanytosend']     = xarModGetVar('recommend','recommendperpage');
    $data['useinvites']        = xarModGetVar('recommend','useinvites');
    $data['inviteandreg']      = xarModGetVar('recommend','invitesandreg');
    
    if ($data['howmanytosend'] > 1) {
        $data['multiplesend'] = TRUE;
    } else {
        $data['multiplesend'] = FALSE;
    }
    
    $data['info']      = !empty($info)  ? $info: '';
    $data['fname']     = !empty($fname) ? $fname:'';
    $data['infolist']  = array();
    $data['fnamelist'] = array();
    
    //make a default from config setting
    $sitename=xarModGetvar('themes','SiteName');
    $subject = xarModGetVar('recommend','title')?xarModGetVar('recommend','title') :$sitename;
    $data['subject']= preg_replace('/%%sitename%%/', $sitename, $subject);

    if ($message == 1){
        $data['message'] = xarML('Thank You! Your invitation has been sent to: #(1)',$info);
    } elseif ($message == 2) {
        if (!empty($infolist) && !empty($existing)) {
        $data['message'] = xarML("Thank You!\n\rYour invitation has been sent to the following: \n\r#(1)",$invitestring);
        $data['message'] .= xarML("We appreciate your inviting the following but the emails already belong to existing memmbers:\n\r  #(1)",$existingstring);

        } elseif (empty($infolist) && !empty($existing)) {
            $data['message'] = xarML("Thank You! \n\rWe appreciate your inviting the following but the emails already belong to existing memmbers:\n\r  #(1)",$existingstring);
        } elseif (!empty($infolist) && empty($existing)){
            $data['message'] = xarML("Thank You!\n\rYour invitation has been sent to the following: #(1)",$invitestring);
       }
    } else {
        $data['message'] = '';
    }
    $data['message']='';

    return $data;
}
?>