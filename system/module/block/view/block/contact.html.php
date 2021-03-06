<?php
/**
 * The contact front view file of block module of chanzhiEPS.
 *
 * @copyright   Copyright 2013-2013 青岛息壤网络信息有限公司 (QingDao XiRang Network Infomation Co,LTD www.xirangit.com)
 * @license     LGPL (http://www.gnu.org/licenses/lgpl.html)
 * @author      Yidong wang <yidong@cnezsoft.com>
 * @package     block
 * @version     $Id$
 * @link        http://www.chanzhi.org
*/
?>
<?php $contact = $this->loadModel('company')->getContact();?>
<div id="block<?php echo $block->id;?>" class='panel panel-block'>
  <div class='panel-heading'>
    <h4><i class='icon-phone'></i> <?php echo $block->title;?></h4>
  </div>
  <div class='panel-body'>
    <dl class='dl-horizontal'>
    <?php foreach($contact as $item => $value):?>
      <dt><?php echo $this->lang->company->$item . $this->lang->colon;?></dt>
      <dd><?php echo $value;?></dd>
    <?php endforeach;?>
    </dl>
  </div>
</div>
