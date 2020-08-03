<?php
/**
 * Add a friend to a user's friends list
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
 * add a friend to the friends list of a user integrated into the rest of the invite/friend functionality in recommend
 * @author Jo Dalle Nogare
 * Removes dependencies on site specifc DD ids and so on 
 * @param $faid - the articleid (aid) of the profile of the user to be added as a friend (optional)
 * @param $friend - the userid (uid) of the user which will be added to the friend list
 * #param $inviteruid  - the userid (inviteruid) of the user that invited this person (optional)
 *  - if set we assume it is not an 'add friend' requeste
 */
function recommend_userapi_addtofriendlist($args)
{
    extract($args);

    if (xarModIsAvailable('articles')) {
       $friendpubtypeid = 14;
    }
    //is the inviter uid set? and the friend uid is not provided
    if (!isset($friend)) return;

    //must be logged in to update a profile
    if (!xarUserIsLoggedIn()) return;
    
    //Shoutmouth specific
    $friendcheck = array();
    if (!isset($faid) || empty($faid)) {
      $friendcheck['aid']='';
       // need a user profile and friend list
       $friendprofiledata = xarModAPIFunc('articles','user','getall',array('ptid' => $friendpubtypeid, 'extra' => array('dynamicdata'),'authorid' => $friend));
       $friendcheck = current($friendprofiledata);
       if (is_array($friendcheck)) {
           $faid = $friendcheck['aid'];
       } else {
          $faid ='';
       }
    }
    $inviteruidlist = array();
     
    if (!empty($friend) && !empty($faid)) {
        // Get the roles data for the friend
        $friendroledata = xarModAPIFunc('roles','user','get',array('uid' => $friend));
        if (!isset($inviteruid)) { //get the invite data
          $invites = xarModAPIFunc('recommend', 'user', 'get', array('iuid'=>$friend));
              $inviteruid=$invites['uid'];
        } else {
            $inviteruid=$inviteruid;
        }

        if (!empty($friendroledata)) {
            // need a user profile and friend list of the person we are adding the friend to
                $befrienderprofile = xarModAPIFunc('articles','user','getall',array('ptid' => $friendpubtypeid, 'extra' => array('dynamicdata'),'authorid' => (int)$inviteruid));

                if (!empty($befrienderprofile) && is_array($befrienderprofile)) {
                    $inviterprofile = current($befrienderprofile);
                    if (!empty($inviterprofile['friends'])) { //we have a friends list
                        $friends = unserialize($inviterprofile['friends']);
                     } else {
                        $friends = '';
                    }
                    if (!isset($inviterprofile['cids'])) {
                        $inviterprofile['cids']=array();
                    }
                    //using the profile aid of the friend as the array key
                    $friends[$faid] = $friend;
                    $friendarray = array_unique($friends); //make sure we have them once only
                    $friendlist = serialize($friendarray);

                //update user profile with new friend data
           /*
                   if (xarModIsHooked('dynamicdata','articles')) {
            
                        $args['module'] = 'articles';
                        $args['itemtype'] =$friendpubtypeid;
                        $args['cids'] =$profile['cids'];
                        $args['recommendfriend']=true;
                        $args['friend']=$friend;
                        $args['befriender'] = $inviteruid;
                        $args['fields'] = array('name'=>'friends',
                                                'value'=> $friendlist);
                        xarModCallHooks('item', 'update', $profile['aid'], $args);
                    }
*/
                    $updateprofile = xarModAPIFunc('articles','admin','update',array(
                                                 'ptid'  => $friendpubtypeid,
                                                 'cids'  => $inviterprofile['cids'],
                                                 'aid'   => $inviterprofile['aid'],
                                                 'title' => $inviterprofile['title'],
                                                 'dd_76' => $friendlist,
                                                 'recommendfriend'=>true,
                                                 'isinviter'=>false,
                                                 'befriender' => $inviteruid,
                                                 'friend' => $friend));
/*
                    //now do the vice versa situation add the inviter to the invitee friend list
                    $inviteeprofile =  $friendcheck; //we have this already
                    if (!empty($inviteeprofile) && is_array($inviteeprofile)) {
                        if (!empty($inviteeprofile['friends'])) { //we have a friends list
                            $friends = unserialize($inviteeprofile['friends']);
                        } else {
                             $friends = '';
                        }
                        if (!isset($inviteeprofile['cids'])) {
                            $inviteeprofile['cids']=array();
                        }
                        //using the profile aid of the inviter as the array key
                        $friends[$inviterprofile['aid']] = $inviteruid;
                        $friendarray = array_unique($friends); //make sure we have them once only
                        $friendlist = serialize($friendarray);
  */
                        /*if (xarModIsHooked('dynamicdata','articles')) {
                            $args['module'] = 'articles';
                            $args['itemtype'] =$friendpubtypeid;
                            $args['cids'] =$profile['cids'];
                            $args['recommendfriend']=true;
                            $args['friend']=$inviteruid;
                            $args['befriender'] = $friend;
                            $args['fields'] = array('name'=>'friends',
                                                'value'=> $friendlist);
                        xarModCallHooks('item', 'update', $profile['aid'], $args);
                    } */
               /*
                           $updateprofile = xarModAPIFunc('articles','admin','update',array(
                                                 'ptid'  => $friendpubtypeid,
                                                 'cids'  => $inviteeprofile['cids'],
                                                 'aid'   => $inviterprofile['aid'],
                                                 'title' => $inviteeprofile['title'],
                                                 'dd_76' => $friendlist,
                                                 'recommendfriend'=>true,
                                                 'isinviter'=>true,
                                                 'befriender' => $friend,
                                                 'friend' => $inviteruid));
             
               }  */
           }
       }
    }
    return true;
}
?>