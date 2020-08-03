<?php
/**
 * Optout
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
 * @param 'confirm' confirm that this item can be deleted
 */
function recommend_admin_optin($args)
{
    extract($args);
    if(!xarVarFetch('iemail',   'str', $iemail,   NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('confirm', 'str', $confirm,  NULL, XARVAR_DONT_SET)) {return;}

    if (!xarSecurityCheck('AdminRecommend',0)) return;

    $validoptin = false;
    $data['validoptin'] =  $validoptin;
    $data['menulinks'] = xarModAPIFunc('recommend','admin','getmenulinks');
    // Check for confirmation.
    if (!isset($confirm)) {
         $data['iemail'] =$iemail;
         $data['authid'] = xarSecGenAuthKey();
         //$data['message'] = xarML('Please enter a valid email for opting back in');
        // Return the template variables defined in this function
        return $data;
    }

    if (!xarSecConfirmAuthKey()) return;
    //check there is actually an email for this
    $optback = xarModAPIFunc('recommend','user','get',array('iemail' => $iemail));

    if ($optback['icode'] == 'optout') {
             $validoptin = TRUE;
    } else {
             $validoptin = FALSE;
               return xarTplModule('recommend', 'admin', 'optin',array('authid' => xarSecGenAuthKey(), 'iemail'=>$iemail, 'validoptin'=>0));

    }

    if ($validoptin) {
        $optbackin = xarModAPIFunc('recommend','admin','delete',array('iemail' => $iemail, 'optin'=> $validoptin));
        return xarTplModule('recommend', 'admin', 'optin',array('authid' => xarSecGenAuthKey(), 'iemail'=>$iemail, 'validoptin'=>1));


        // Redirect to the main view function of this module after success
        xarResponseRedirect(xarModURL('recommend', 'admin', 'optin', array('validoptin'=>1,'iemail'=>$iemail)));
         
    }

     // Return
    return true;
}

?>