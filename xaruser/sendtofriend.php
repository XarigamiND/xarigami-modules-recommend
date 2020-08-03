<?php
/**
 * @package modules
 * @copyright (C) 2002-2008 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007,2008 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

/**
 * @ Function: user_sendtofriend called with <xar:recommend-sendtofriend /> custom tag
 * @ also used by dyn data property SendToFriend (which may not remain but allows show or not show per item)
 * @ Author jojodee
 * @ Parameters $aid used to determine $title and $ptid for display URL construction
 */
function recommend_user_sendtofriend($args)
{
    if(!xarVarFetch('aid', 'isset', $aid,   NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('info', 'str:1:', $info,   '', XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('message', 'str:1:', $message, '', XARVAR_NOT_REQUIRED)) return;
    
    // Security Check
    if(!xarSecurityCheck('OverviewRecommend')) return;
    xarTplSetPageTitle(xarVarPrepForDisplay(xarML('Send this story to a friend')));
    
    // Generate a one-time authorisation code for this operation
    $data['authid'] = xarSecGenAuthKey();
    $data['submit'] = xarML('Submit');
    $articleinfo=xarModAPIFunc('articles','user','get',array('aid'=>$aid));
    $data['title']=$articleinfo['title'];
    $ptid=$articleinfo['pubtypeid'];
    $articledisplay=xarModURL('articles','user','display',array('aid'=>$aid,'ptid'=>$ptid));
    
    $data['htmldisplaylink']='<a href="'.$articledisplay.'" >'.$articledisplay.'</a>';
    $data['textdisplaylink']=$articledisplay;
    $data['aid']=$aid;

    if ($message == 1){
       $data['info']=$info;
      xarTplSetPageTitle(xarVarPrepForDisplay(xarML('Thank you')));
        $data['message'] = xarML('Thank You! Your email has been sent to: ');
        $data['message'] .= $info;
    } else {
        $data['message'] = '';
    }

    return $data;
}
?>