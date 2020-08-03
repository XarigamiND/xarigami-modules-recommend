<?php
/**
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007,2008 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

/* Sendfriend prepares the text or html email to send
 *
 * Used by user_sendtofriend to forward email to friend with recommended article
 * @author Jo Dalle Nogare <jojodee@xaraya.com>
 * @parameters Takes parameters passed by user_sendtofriend to generate info used by email mod
 */
function recommend_user_sendfriend()
{
    /* Get parameters */
    if (!xarVarFetch('username', 'str:1:', $username, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('useremail', 'str:1:', $useremail)) return;
    if (!xarVarFetch('fname', 'str:1:', $fname, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('info', 'str:1:', $info)) return;
    if (!xarVarFetch('aid', 'isset', $aid)) return;
    if (!xarVarFetch('usernote', 'str:1:', $usernote, '', XARVAR_NOT_REQUIRED)) return;

    /*  Confirm authorisation code. */
    if (!xarSecConfirmAuthKey()) return;

    /* Security Check */
    if(!xarSecurityCheck('OverviewRecommend')) return;

    /* Statistics */
    /* $date = date('Y-m-d G:i:s'); */
    $date = time();
    $numbersentprev = xarModGetVar('recommend', 'numbersent');
    $numbersent = $numbersentprev + 1;
    xarModSetVar('recommend', 'numbersent', $numbersent);
    xarModSetVar('recommend', 'lastsentemail', $info);
    xarModSetVar('recommend', 'lastsentname', $fname);
    xarModSetVar('recommend', 'date', $date);
    xarModSetVar('recommend', 'username', $username);

    $sitename = xarModGetVar('themes','SiteName');
    $siteurl = xarServerGetBaseURL();
    //check if we are allowing opt out
    $allowoptout = xarModGetvar('recommend','allowoptout');
    $thisuseroptout = FALSE;
    if (TRUE == $allowoptout) {
                 //check if the user wants to opt out - could be multiple here
                 $dooptout = xarModAPIFunc('recommend','user','get', array('iemail' =>$info));
                 if (is_array($dooptout)) $optout = current($dooptout);
                 if ($dooptout['icode'] == 'optout') {
                    $thisuseroptout = TRUE;
                 }
    }
    
    if ($thisuseroptout == FALSE) {//continue on
        $articleinfo=xarModAPIFunc('articles','user','get',array('aid'=>$aid));
        $title=$articleinfo['title'];
        $ptid=$articleinfo['pubtypeid'];
        $articledisplay=xarModURL('articles','user','display',array('aid'=>$aid,'ptid'=>$ptid));
        $textdisplaylink=$articledisplay;
        $htmldisplaylink=$articledisplay;

        $subject = xarML('Check out this story from #(1)',xarModGetVar('themes', 'SiteName'));

        /* Prepare to process entities in email message */
        $trans = get_html_translation_table(HTML_ENTITIES);
        $trans = array_flip($trans);

        if (xarModGetVar('recommend', 'usernote')){
            $usernote=strtr($usernote, $trans);
        }else{
            $usernote='';
        }
         $usernote = preg_replace('/\s\s+/', ' ', $usernote);
        $title=strtr($title, $trans);

        if (xarModGetVar('recommend', 'usernote')){
            $htmlusernote = strtr(xarVarPrepHTMLDisplay($usernote), $trans);
        } else {
            $htmlusernote ='';
        }
        $htmlusernote =nl2br($htmlusernote);
        $htmltitle=strtr(xarVarPrepHTMLDisplay($title), $trans);

        /* startnew message */
        $textmessage= xarTplModule('recommend', 'user', 'usersendfriend',
                                    array('username'     => $username,
                                          'friendname'   => $fname,
                                          'useremail'    => $useremail,
                                          'articletitle' => $title,
                                          'articlelink'  => $textdisplaylink,
                                          'usermessage'  => $usernote,
                                          'sitename'     => $sitename,
                                          'allowoptout'  => $allowoptout,
                                          'aid'          => $aid,
                                          'siteurl'      => $siteurl,
                                          'info'         => $info),
                                    'text');

        $htmlmessage= xarTplModule('recommend','user','usersendfriend',
                                    array('username'     => $username,
                                          'friendname'   => $fname,
                                          'useremail'    => $useremail,
                                          'articletitle' => $htmltitle,
                                          'articlelink'  => $htmldisplaylink,
                                          'usermessage'  => $htmlusernote,
                                          'sitename'     => $sitename,
                                          'allowoptout'  => $allowoptout,
                                          'aid'          => $aid,
                                          'siteurl'      => $siteurl,
                                          'info'         => $info),
                                    'html');
        /* let's send the email now */
        
        if (!xarModAPIFunc('mail','admin','sendmail',
                       array('info'         => $info,
                             'name'         => $fname,
                             'subject'      => $subject,
                             'htmlmessage'  => $htmlmessage,
                             'message'      => $textmessage,
                             'from'         => $useremail,
                             'fromname'     => $username,
                             'usetemplates' => false))) return;

    } //end send mail if optout was false

    /* lets update status and display updated configuration */
    xarResponseRedirect(xarModURL('recommend', 'user', 'sendtofriend', array('message' => '1', 'aid'=>$aid, 'info'=>$info)));

    /* Return true if successful */
    return true;
}
?>