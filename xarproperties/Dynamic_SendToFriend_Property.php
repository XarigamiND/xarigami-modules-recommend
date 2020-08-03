<?php
/**
 * @package modules
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
*
 * @subpackage Xarigami Recommend
 * @copyright (C) 2007-2011 2skies.com
 * @link http://xarigami.com/project/xarigami_recommend
 */
/**
 * Class to handle Send To Friend property
 * @author jojodee
 * @package recommend
 */
sys::import('modules.base.xarproperties.Dynamic_Checkbox_Property');
class Dynamic_SendToFriend_Property extends Dynamic_Checkbox_Property
{
    public $id          = 105;
    public $name        = 'sendtofriend';
    public $desc        = 'Send to Friend';
    public $reqmodules  =  'recommend';

    function __construct($args)
    {
       parent::__construct($args);
        $this->tplmodule = 'base'; //we're using the parent tplmodule and template for Input
        $this->template  = 'checkbox';
        $this->filepath  = 'modules/recommend/xarproperties';
    }


    function showOutput(Array $data = array())
    {
        extract($data);

        if(!xarVarFetch('aid',  'isset', $aid,   NULL, XARVAR_DONT_SET)) {return;}

        if (!isset($value)) {
            $value = $this->value;
        }
        $data['value']=$value;
        if (isset($aid)) {
           $data['aid']=    $aid;
           //we are using the sendtofriend templates for output
           if (!isset($template)) $data['template'] = 'sendtofriend';
            if (!isset($tplmodule)) $data['tplmodule'] = 'recommend';

           return parent::showOutput($data);
       }else{
           return false;
       }
    }

    /**
     * Get the base information for this property.
     *
     * @returns array
     * @return base information for this property
     **/
     function getBasePropertyInfo()
     {
        $args = array();
        $validation = $this->getBaseValidationInfo();
        $baseInfo = array(
                              'id'           => 106,
                              'name'         => 'sendtofriend',
                              'label'        => 'Send To A Friend',
                              'format'       => '106',
                              'validation' => serialize($validation),
                              'source'       => '',
                              'dependancies' => '',
                              'filepath'      => 'modules/recommend/xarproperties',
                              'requiresmodule' => 'recommend',
                              'aliases'        => '',
                              'args'           => serialize($args)
                           );
        return $baseInfo;
     }

}
?>