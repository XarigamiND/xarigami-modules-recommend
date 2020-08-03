<?php
/*
 * @package modules
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */
/**
 * Modify the confirmation email for users
 */
function recommend_admin_modifyconfig()
{
    /* Security Check */
    if (!xarSecurityCheck('EditRole')) return;

    if (!xarVarFetch('phase', 'str:1:100', $phase, 'modify', XARVAR_NOT_REQUIRED)) return;

    switch (strtolower($phase)) {
        case 'modify':
        default:
            $data['menulinks'] = xarModAPIFunc('recommend','admin','getmenulinks');
            $data['title']          = xarModGetVar('recommend', 'title');
            $data['template']       = xarModGetVar('recommend', 'template');
            $data['numbersent']     = xarModGetVar('recommend', 'numbersent');
            if (empty($data['numbersent'])){
                $data['numbersent'] = '0';
            }
            $data['lastemailaddy']  = xarModGetVar('recommend', 'lastsentemail');
            if (empty($data['lastemailaddy'])){
                $data['lastemailaddy'] = '';
            }
            $data['lastemailname']  = xarModGetVar('recommend', 'lastsentname');

            if (empty($data['lastemailname'])){
                $data['lastemailname'] = '';
            }

            $data['date']               = xarModGetVar('recommend', 'date');
            $data['username']           = xarModGetVar('recommend', 'username');
            $data['authid']             = xarSecGenAuthKey();
            $data['submitlabel']        = xarML('Submit');
            $data['SupportShortURLs']   = xarModGetVar('recommend', 'SupportShortURLs') ;
            $data['itemsperpage']       = xarModGetVar('recommend','itemsperpage');
            $data['useinvites']         = xarModGetVar('recommend','useinvites');
            $data['matchemail']         = xarModGetVar('recommend','matchemail');
            $data['recommendperpage']   = xarModGetVar('recommend','recommendperpage');
            $data['usersubject']        = xarModGetVar('recommend','usersubject');
            $data['invitesandreg']      = xarModGetVar('recommend','invitesandreg');
            $data['allowoptout']        = xarModGetVar('recommend','allowoptout');
            $data['usernote']           = xarModGetVar('recommend','usernote');
            /* dynamic properties (if any) */
            /*
            $data['properties'] = null;
            if (xarModIsAvailable('dynamicdata')) {
                // get the Dynamic Object defined for this module (and itemtype, if relevant)
                $object = xarModAPIFunc('dynamicdata', 'user', 'getobject',
                    array('module' => 'roles'));
                if (isset($object) && !empty($object->objectid)) {
                    // get the Dynamic Properties of this object
                    $data['properties'] = $object->getProperties();
                }
            }
            */
            break;

        case 'update':

            if (!xarVarFetch('template', 'str:1:', $template)) return;
            if (!xarVarFetch('title', 'str:1:', $title)) return;
            if (!xarVarFetch('usernote', 'checkbox', $usernote, false, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('shorturls', 'checkbox', $shorturls, false, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('saveinvites', 'checkbox', $saveinvites, true, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('useinvites', 'checkbox', $useinvites, false, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('invitesandreg', 'checkbox', $invitesandreg, false, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('matchemail', 'checkbox', $matchemail, false, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('itemsperpage', 'int:0:', $itemsperpage, 20, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('recommendperpage', 'int:0:', $recommendperpage, 1, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('usersubject', 'checkbox', $usersubject, false, XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('allowoptout', 'checkbox', $allowoptout, false, XARVAR_NOT_REQUIRED)) return;
            /* Confirm authorisation code */
            if (!xarSecConfirmAuthKey()) return;

            xarModSetVar('recommend', 'template', $template);
            xarModSetVar('recommend', 'title', $title);
            xarModSetVar('recommend', 'usernote', $usernote);
            xarModSetVar('recommend', 'SupportShortURLs', $shorturls);
            xarModSetVar('recommend', 'itemsperpage',20);
            xarModSetVar('recommend', 'allowoptout',$allowoptout);
            xarModSetVar('recommend', 'useinvites',$useinvites);
            if (FALSE == $useinvites) {
            //let's make sure and set dependent vars to correct value
               $saveinvites = TRUE; //always atm
               $matchemail = FALSE;
               $invitesandreg = TRUE;
            }
            xarModSetVar('recommend', 'invitesandreg',$invitesandreg);
            xarModSetVar('recommend', 'saveinvites',$saveinvites);
            xarModSetVar('recommend', 'matchemail',$matchemail);
            xarModSetVar('recommend','itemsperpage',$itemsperpage);
            xarModSetVar('recommend','recommendperpage',$recommendperpage);
            xarModSetVar('recommend','usersubject',$usersubject);
            $msg = xarML('Recommend configuration successfully updated.');
            xarTplSetMessage($msg,'status');

            xarResponseRedirect(xarModURL('recommend', 'admin', 'modifyconfig'));
            return true;

            break;
    }

    return $data;
}

?>