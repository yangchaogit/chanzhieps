<?php
/**
 * The admin view file of message module of chanzhiEPS.
 *
 * @copyright   Copyright 2013-2013 青岛息壤网络信息有限公司 (QingDao XiRang Network Infomation Co,LTD www.xirangit.com)
 * @license     LGPL
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     message
 * @version     $Id$
 * @link        http://www.chanzhi.org
 */
?>
<?php include '../../common/view/header.admin.html.php';?>
<?php js::set('type', $type);?>
<table class='table table-bordered'>
  <caption style="background:none;">
    <ul class="nav nav-tabs">
      <li <?php if($status == 0) echo "class='active'";?>><?php echo html::a(inlink('admin', "type={$type}&status=0"), $lang->message->statusList[0], "class='first-nav'");?></li>
      <li <?php if($status == 1) echo "class='active'";?>><?php echo html::a(inlink('admin', "type={$type}&status=1"), $lang->message->statusList[1]);?></li>
    </ul>
  </caption>
  <thead>
    <tr>
      <th class='w-60px'><?php echo $lang->message->id;?></th>
      <th><?php echo $lang->message->content;?></th>
      <th class='w-160px a-center'><?php echo $lang->actions;?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($messages as $messageID => $message):?>
    <tr>
      <td rowspan='2' class='a-center'><strong><?php echo $message->id;?></strong></td>
      <td>
        <?php 
        $config->requestType = $config->frontRequestType;

        if($message->objectTitle != '')
        {
            $objectViewLink = html::a($message->objectViewURL, $message->objectTitle, "target='_blank'");
        }
        else
        {
            $objectViewLink = "<span class='alert-error'>{$lang->message->deletedObject}</span>";
        }

        $config->requestType = 'GET';
        echo <<<EOT
        <strong>$message->author</strong><i class='blue'>$message->email</i> 
        <strong>$message->date</strong>{$lang->message->messageTo}
        $objectViewLink
EOT;
        ?>
      </td>
      <td rowspan='2' class='a-center v-middle'>
        <?php 
        echo html::a(inlink('reply', "messageID=$message->id"), $lang->message->reply, "data-toggle='modal'");
        echo html::a(inlink('delete', "messageID=$message->id&type=single&status=$status"), $lang->message->delete, "class='deleter'");
        if($status == 0) echo html::a(inlink('pass', "messageID=$message->id&type=single"), $lang->message->pass, "class='pass'");
        echo html::a($message->objectViewURL . '#message', $lang->message->reply, "target='_blank'");
        echo '<br />';
        if($status == 0) echo html::a(inlink('delete', "messageID=$message->id&type=pre&status=$status"), $lang->message->deletePre, "class='pre' data-confirm='{$lang->message->confirmDeletePre}'");
        if($status == 0) echo html::a(inlink('pass',   "messageID=$message->id&type=pre"), $lang->message->passPre, "class='pre' data-confirm='{$lang->message->confirmPassPre}'");
        ?>
      </td>
    </tr>
    <tr>
      <td class='content-box'>
        <?php echo html::textarea('', $message->content, "rows='2' class='area-1' spellcheck='false'");?>
        <?php 
        if(!empty($replies[$messageID]))
        {
            echo "<dl class='alert alert-info'>";
            foreach($replies[$messageID] as $reply)
            {
                printf($lang->message->replyItem, $reply->from, $reply->date, $reply->content);
            }
            echo '</dl>';
        }
        ?>
      </td>
    </tr>
    <?php endforeach;?>
  </tbody>
  <tfoot><tr><td colspan='3' class='a-right'><?php $pager->show();?></td></tr></tfoot>
</table>
<?php include '../../common/view/footer.admin.html.php';?>