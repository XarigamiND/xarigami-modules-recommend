<?php
/**
 * @package Xaraya modules
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 *
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007,2008,2009 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 * @author Jo Dalle Nogare <icedlava@2skies.com>
 */

/*Prepare and send the email
 *@author Jo Dalle Nogare
 *
 */
function recommend_user_invite($args)
{
    extract($args);

    /* Get parameters */
    if (!xarVarFetch('useremail',   'str:1:100', $useremail,'', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('icode',       'str:1:', $icode, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('optout',      'int:0:', $optout, NULL, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('redirecturl', 'str:0:255',  $redirecturl, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('noregistration', 'int:0:1',  $noregistration, null, XARVAR_NOT_REQUIRED)) return;    
    

    if (!empty($icode) && xarUserIsLoggedIn()) {
        xarUserLogOut(); // Do we want this behaviour?
    }
    //check site is not locked
    $lockvars = unserialize(xarModGetVar('roles','lockdata'));
    if ($lockvars['locked'] ==1) {
        xarErrorSet(XAR_SYSTEM_MESSAGE,
       'SITE_LOCKED',
        new SystemMessage($lockvars['message']));
        return;     
     }    
     
    $useinvites = xarModGetVar('recommend','useinvites');
    if (empty($redirecturl) && $useinvites) {
        $redirecturl =xarModURL('recommend','user','invite',array('icode'=>$icode,'useremail'=>$useremail));
    } elseif (empty($redirecturl)) {
        //must be a sendtofriend that got us here - what's a good redirect?
        $redirecturl =xarServerGetBaseURL();
    }

    //check if this is a sendtofriend initiated optout
    if ($optout == 2) {
        //check if they already opted out so we don't add a new record- can only do so by their email
        $optoutinfo = xarModAPIFunc('recommend','user','get',array('iemail'=>$useremail));
        if (is_array($optoutinfo) && $optoutinfo['iuname'] == 'sendfriend' && $optoutinfo['icode'] == 'optout') {
            // they have already opted out
            $optedout = TRUE;
        } else {
            $optedout = FALSE;
        }

        if (!$optedout) {
            //this is a sendto friend initiated optout - let's opt them out now
            $args = array();
            $args['uid']       = 0; //non-registered
            $args['uname']     = '';
            $args['iemail']    = $useremail;
            $args['idate']     = time();
            $args['iuname']    = 'sendfriend';
            $args['joindate']  = '';
            $args['icode']     = 'optout';

            //create the optout record in the db
             $optoutid = xarModAPIFunc('recommend','admin','create',$args);
             $optoutinfo['icode'] = ''; //reset this for consistency in final message - will assume newly opted out
        }
        $optoutinfo['optout']= true;
        return xarTplModule('recommend','user', 'invalidcode', $optoutinfo);
    }
    
    //get the invite code and check if it is still valid
    $matchemail = xarModGetVar('recommend','matchemail') == TRUE?1:0;
    
    $useremailtemp = $useremail;
    if ($matchemail != 1) {
       $useremailtemp = '';
    }
    
    $inviteinfo = xarModAPIFunc('recommend','user','get',array('icode'=>$icode, 'iemail'=>$useremailtemp));
    $inviteinfo['noregistration'] = $noregistration;
    //valid if a record is returned, and the join date is empty (
    if (is_array($inviteinfo) && $inviteinfo['joindate']==0  && $inviteinfo['icode'] == $icode){
         $validinvite = TRUE;
    } else {
        $validinvite = FALSE;
    }

    //check again if the user wants to opt out and have clicked on optout again
    if ($validinvite == FALSE && $optout==1) {
        $inviteinfo = xarModAPIFunc('recommend','user','get',array('icode'=>'optout', 'iemail'=>$useremailtemp));
        //valid if a record is returned, and the join date is empty (
        if (is_array($inviteinfo) && $inviteinfo['joindate']==0  && $inviteinfo['icode'] == 'optout') {
             $validinvite = TRUE;
        } else {
            $validinvite = FALSE;
        }
    }

    $inviteinfo['optout'] = false; // set default
    
    if ($validinvite && ($optout == 1)) { //make sure the person was actually invited
        //we need to update all possible invitation entries with OPTOUT
        //let's retain them for now rather than delete them in case we need them for something later
        //making the assumption that unique emails are required on site and per user
        $inviteinfo['optout']= true;
        $dooptout = xarModAPIFunc('recommend','admin','update',
            array('icode'  => $inviteinfo['icode'],
                  'iemail' => $useremail,
                  'optout' => $optout,
                  'inviteid' => $inviteinfo['inviteid']
                 )
            );
            
       return xarTplModule('recommend','user', 'invalidcode', $inviteinfo);
    }

    $inviteinfo['username']  = $inviteinfo['iuname'];
    $inviteinfo['sitename'] =  xarModGetVar('themes','siteName');

    //But what if the person has an account and just not Validated yet?
    $isregistered = (isset($inviteinfo['iuid']) && ($inviteinfo['iuid']>0))?true:false;
    
    //if the code is valid, redirect to registration, else send to an error template
    if ((TRUE == $validinvite) && !$isregistered) {
  
        xarTplSetPageTitle(xarML('New Account'));
        $authid = xarSecGenAuthKey('registration');
    
        //set a session var for the user
        xarSessionSetVar('icode',$inviteinfo['icode']);
        
        $values = array('username' => $inviteinfo['iuname'],
                        'displayname' => $inviteinfo['iuname'],
                        'email'    => $inviteinfo['iemail'],
                        'pass1'    => '',
                        'pass2'    => '',
                        'icode'    => $inviteinfo['icode'],
                        'inviter'  => $inviteinfo['uid']
                        );
        $invalid = array();

         // dynamic properties (if any)

         $properties = null;
         $withupload = (int) FALSE;
         if (xarModIsHooked('dynamicdata','roles')) {
             // get the Dynamic Object defined for this module (and itemtype, if relevant)
            $object = xarModAPIFunc('dynamicdata','user','getobject',
                                         array('module' => 'roles'));
            if (isset($object) && !empty($object->objectid)) {
                    // get the Dynamic Properties of this object
                    $properties =& $object->getProperties();
            }
            if (isset($properties)) {
                foreach ($properties as $key => $prop) {
                    if (isset($prop->upload) && $prop->upload == TRUE) {
                        $withupload = (int) TRUE;
                    }
                }
            }
        }
        /* Call hooks here, others than just dyn data
         * We pass the phase in here to tell the hook it should check the data
         */
            $item['module'] = 'registration';
            $item['itemid'] = '';
            $item['values'] = $values;
            $item['phase']  = 'registerform';
            $hooks = xarModCallHooks('item', 'new', '', $item);

            if (empty($hooks)) {
                $hookoutput = array();
            } else {
                $hookoutput = $hooks;
            }

         $data = xarTplModule('registration','user', 'registerform',
                           array('authid'     => $authid,
                                 'values'     => $values,
                                 'invalid'    => $invalid,
                                 'properties' => $properties,
                                 'hookoutput' => $hookoutput,
                                 'withupload' => isset($withupload) ? $withupload : (int) FALSE,
                                 'userlabel'  => xarML('New User'),
                                 'redirecturl'   => $redirecturl));

         return $data;
        //xarResponseRedirect(xarModURL('registration', 'user', 'register', $values);

    } else {
        return xarTplModule('recommend','user', 'invalidcode', $inviteinfo);
    }
    /* Return */
    return true;
}
?>