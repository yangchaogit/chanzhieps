<?php
/**
 * The browse view file of comment module of xirangEPS.
 *
 * @copyright   Copyright 2013-2013 QingDao XiRang Network Infomation Co,LTD (www.xirang.biz)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     block
 * @version     $Id$
 * @link        http://www.xirang.biz
 */
?>
<?php include '../../common/view/header.admin.html.php';?>
<?php js::set('lang.comment', $lang->comment);?>
  <table class="table table-hover table-bordered table-striped">
    <caption>
      <div class='f-left'><?php echo $lang->comment->manage;?></div>
      <div class='f-right'>
        <?php echo html::a(inlink('browseadmin', 'status=0'), $lang->comment->unReviewed);?>
        <?php echo html::a(inlink('browseadmin', 'status=1'), $lang->comment->reviewed);?>
      </div>
    </caption>
    <tr>
      <th class='w-id'><?php echo $lang->comment->id;?></th>
      <th><?php echo $lang->comment->content;?></th>
      <th class='w-120px'><?php echo $lang->actions;?></th>
    </tr>
    <?php foreach($comments as $comment):?>
    <tr>
      <td rowspan='2' class='a-right'><strong><?php echo $comment->id;?></strong></td>
      <td colspan='2'>
        <?php 
          $config->requestType = $config->frontRequestType;
          $objectViewLink = $this->createLink($comment->objectType, 'view', "id=$comment->objectID");
          $config->requestType = 'GET';
          echo <<<EOT
          <strong>$comment->author</strong> <i class='red'>$comment->email</i> at
          <strong>$comment->date</strong> post comment to 
          <strong><a href='$objectViewLink' target='_blank'>$comment->objectTitle</a>
EOT;
       ?>
      </td>
    </tr>
    <tr>
      <td><?php echo html::textarea('', $comment->content, "rows='2'cols='110' class='area-1'");?></td>
      <td class='textcenter'>
        <?php 
        echo html::a(inlink('delete', "commentID=$comment->id&type=single&status=$status"), $lang->comment->delete, '', 'class="delete"');
        if($status == 0) echo html::a(inlink('pass', "commentID=$comment->id&type=single"), $lang->comment->pass, '', "class='pass' confirminfo='{$lang->comment->confirmPassSingle}'");
        echo html::a($objectViewLink . '#comment', $lang->comment->reply, '_blank');
        echo '<br />';
        if($status == 0) echo html::a(inlink('delete', "commentID=$comment->id&type=pre&status=$status"), $lang->comment->deletePre, '', 'class="deletepre"');
        if($status == 0) echo html::a(inlink('pass',   "commentID=$comment->id&type=pre"), $lang->comment->passPre, '', "class='pass' confirminfo='{$lang->comment->confirmPassPre}'");
        ?>
      </td>
    </tr>
    <?php endforeach;?>
    </tr>
    <tr><td colspan='3' class='a-right'><?php $pager->show();?></td></tr>
  </table>
<?php include '../../common/view/footer.admin.html.php';?>