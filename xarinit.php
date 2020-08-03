<?php
/**
 * Recommend Table function
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
 * initialise the Recommend Module
 * @author John Cox
 * @author jojodee
 */
function recommend_init()
{
    $title = 'Interesting Site :: %%sitename%%';
    /* Set ModVar */
    $email = 'Hello %%toname%%, your friend %%name%% considered our site interesting and wanted to send it to you.

Site Name: %%sitename%% :: %%siteslogan%%
Site URL: %%siteurl%%';

    //$date = date('Y-m-d G:i:s');
    $date = time();
    xarModSetVar('recommend', 'numbersent', 0);
    xarModSetVar('recommend', 'date', $date);
    xarModSetVar('recommend', 'username', 'Admin');
    xarModSetVar('recommend', 'title', $title);
    xarModSetVar('recommend', 'template', $email);

    // Register Masks
    xarRegisterMask('OverviewRecommend','All','recommend','All','All','ACCESS_OVERVIEW');
    xarRegisterMask('EditRecommend','All','recommend','All','All','ACCESS_EDIT');

    xarTplRegisterTag(
        'recommend', 'recommend-sendtofriend', array(),
        'recommend_userapi_rendersendtofriend'
    );

    /* This init function brings our module to version 1.0, run the upgrades for the rest of the initialisation */
    return recommend_upgrade('1.0.1');
}

/**
 * upgrade the send to friend module from an old version
 */
function recommend_upgrade($oldversion)
{
    switch ($oldversion) {
        case '0.01':
            // Remove Masks and Instances
            xarRemoveMasks('recommend');
            xarRemoveInstances('recommend');

            /* Set custom sendtofriend tag */
            xarTplRegisterTag(
               'recommend', 'recommend-sendtofriend', array(),
               'recommend_userapi_rendersendtofriend'
            );
            /* Set ModVar */
            $email = 'Hello %%toname%%, your friend %%name%% considered our site interesting and wanted to send it to you.

        Site Name: %%sitename%% :: %%siteslogan%%
        Site URL: %%siteurl%%';
            $title = 'Interesting Site :: %%sitename%%';
            $date = date('Y-m-d G:i:s');
            xarModSetVar('recommend', 'title', $title);
            xarModSetVar('recommend', 'numbersent', 1);
            xarModSetVar('recommend', 'lastsentemail', 'niceguyeddie@xaraya.com');
            xarModSetVar('recommend', 'lastsentname', 'John Cox');
            xarModSetVar('recommend', 'date', $date);
            xarModSetVar('recommend', 'username', 'Admin');
            xarModSetVar('recommend', 'template', $email);

            /* Register Masks */
            xarRegisterMask('OverviewRecommend','All','recommend','All','All','ACCESS_OVERVIEW');
            xarRegisterMask('EditRecommend','All','recommend','All','All','ACCESS_EDIT');

            case '1.0.0':

            $olddate = xarModGetVar('recommend', 'date');
            $newdate = strtotime($olddate);
            xarModSetVar('recommend', 'date', $newdate);

            case '1.0.1':
            xarRegisterMask('ReadRecommend','All','recommend','All','All','ACCESS_READ');

            case '1.0.2':
                /* Add an admin mask */
                xarRegisterMask('AdminRecommend','All','recommend','All','All','ACCESS_ADMIN');

                 /* Setup some new mod vars */
                xarModSetVar('recommend','itemsperpage',20);
                xarModSetVar('recommend','useinvites',true);
                xarModSetVar('recommend','saveinvites',true);
                xarModSetVar('recommend','matchemail',false);

                /* Setup the table to hold the initer, invitee, and related info */
                $dbconn = xarDBGetConn();
                $xartable = xarDBGetTables();
                $recommendTable = $xartable['recommend'];
                xarDBLoadTableMaintenanceAPI();
                /*
                                            * Fields
                                            * iid - invite id
                                            * uname - username of person doing the inviting
                                            * iemail - invitee's email address
                                            * idate - date of the invitation
                                            * iuname - invitee's final username
                                            * joindate - date the invitee joined (registered)
                                            * icode - the invite code
                                            */
                $query = xarDBCreateTable($recommendTable,
                          array('xar_id'       => array('type'        => 'integer',
                                                        'null'       => false,
                                                        'increment'  => true,
                                                        'primary_key' => true),
                                'xar_uid'    => array('type'        => 'integer',
                                                        'size'        => 10,
                                                        'null'        => false,
                                                        'default'     => '0'),
                                'xar_uname'    => array('type'        => 'varchar',
                                                        'size'        => 254,
                                                        'null'        => false,
                                                        'default'     => ''),
                                'xar_iemail'   => array('type'        => 'varchar',
                                                        'size'        => 254,
                                                        'null'        => false,
                                                        'default'     => ''),
                                'xar_idate'    => array('type'        => 'integer',
                                                        'unsigned'    => true,
                                                        'size'        => 10,
                                                        'null'        => false,
                                                        'default'     => '0'),
                                'xar_iuname'   => array('type'        => 'varchar',
                                                        'size'        => 64,
                                                        'null'        => false,
                                                        'default'     => ''),
                                'xar_joindate'  => array('type'        => 'integer',
                                                        'unsigned'    => true,
                                                        'size'        => 10,
                                                        'null'        => false,
                                                        'default'     => '0'),
                                'xar_icode'     => array('type'        => 'varchar',
                                                        'size'        => 40,
                                                        'null'        => false,
                                                        'default'     => ''),
                                'xar_iuid'    => array('type'        => 'integer',
                                                        'size'        => 10,
                                                        'null'        => false,
                                                        'default'     => '0'),
                                ));

                if (empty($query)) return;

                /* Pass the Table Create DDL to adodb to create the table and send exception if unsuccessful */
                $result = $dbconn->Execute($query);
                if (!$result) return;

                /* Now handle the DD object overlay for this table */
                $objectid = xarModAPIFunc('dynamicdata','util','import',
                              array('file' => 'modules/recommend/xardata/recommend-def.xml'));
                if (empty($objectid)) return;
                // save the object id for later
                xarModSetVar('recommend','recommendobjectid',$objectid);

            case '1.0.3':
                if (!xarModRegisterHook('item','create','API','recommend','user','createhook')) {
                    return false;
                }
                if (!xarModRegisterHook('item','update','API','recommend','user','updatehook')) {
                    return false;
                }
                xarModSetVar('recommend','recommendperpage',1);
                xarModSetVar('recommend','usersubject',true);
            case '1.0.4':
                xarModSetVar('recommend','invitesandreg', true);
                xarModSetVar('recommend','allowoptout', true);
            case '1.1.0':
            case '1.2.0':
                //update to indicate removal of references in code and update for version 1.4.0 core
            case '1.2.1': //current version
            break;
    }

    /* Update successful */
    return true;
}

/**
 * delete the send to friend module
 */
function recommend_delete()
{

    // delete the dynamic object and their properties

    $objectid = xarModGetVar('recommend','recommendobjectid');
    if (!empty($objectid)) {
        xarModAPIFunc('dynamicdata','admin','deleteobject',array('objectid' => $objectid));
    }
    // Get database information
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables();

    // Delete recommend table
    $query = "DROP TABLE ".$xartable['recommend'];
    $result = $dbconn->Execute($query);
    if (!$result) return;

    /* Remove Masks and Instances */
    xarModDelAllVars('recommend');
    xarRemoveMasks('recommend');
    xarRemoveInstances('recommend');
    xarTplUnregisterTag('recommend-sendtofriend');
    if (!xarModUnregisterHook('item', 'update', 'API',
                             'recommend', 'user', 'updatehook'))return;
    if (!xarModUnregisterHook('item', 'create', 'API',
                             'recommend', 'user', 'createhook'))return;
    return true;
}
?>