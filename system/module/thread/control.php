<?php
/**
 * The control file of thread category of chanzhiEPS.
 *
 * @copyright   Copyright 2013-2013 青岛息壤网络信息有限公司 (QingDao XiRang Network Infomation Co,LTD www.xirangit.com)
 * @license     LGPL
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     thread
 * @version     $Id$
 * @link        http://www.chanzhi.org
 */
class thread extends control
{
    /** 
     * Post a thread.
     * 
     * @param  int      $boardID 
     * @access public
     * @return void
     */
    public function post($boardID = 0)
    {
        if($this->app->user->account == 'guest') die(js::locate($this->createLink('user', 'login', "referer=" . helper::safe64Encode($this->app->getURI()))));

        /* Get the board. */
        $board = $this->loadModel('tree')->getById($boardID);

        /* Checking current user can post to the board or not. */
        if(!$this->loadModel('forum')->canPost($board))
        {
            die(js::error($this->lang->forum->readonly) . js::locate('back'));
        }

        /* Set editor for current user. */
        $this->thread->setEditor($board->id, 'post');

        /* User posted a thread, try to save it to database. */
        if($_POST)
        {
            /* If no captcha but is garbage, return the error info. */
            if($this->post->captcha === false and $this->loadModel('captcha')->isEvil($_POST['content']))
            {
                $this->send(array('result' => 'fail', 'reason' => 'needChecking', 'captcha' => $this->captcha->create4Thread()));
            }

            $threadID = $this->thread->post($boardID);
            if(dao::isError()) $this->send(array('result' =>'fail', 'message' => dao::getError()));

            $locate = inlink('view', "threadID=$threadID");
            $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => $locate));
        }

        $this->view->title     = $board->name . $this->lang->minus . $this->lang->thread->post;
        $this->view->board     = $board;
        $this->view->canManage = $this->thread->canManage($boardID);

        $this->display();
    }

    /**
     * Edit a thread.
     * 
     * @param string $threadID 
     * @access public
     * @return void
     */
    public function edit($threadID)
    {
        if($this->app->user->account == 'guest') die(js::locate($this->createLink('user', 'login')));

        $thread = $this->thread->getByID($threadID);
        if(!$thread) die(js::locate('back'));

        /* Judge current user has priviledge to edit the thread or not. */
        if(!$this->thread->canManage($thread->board, $thread->author)) die(js::locate('back'));

        /* Set editor for current user. */
        $this->thread->setEditor($thread->board, 'edit');

        if($_POST)
        {
            /* If no captcha but is garbage, return the error info. */
            if($this->post->captcha === false and $this->loadModel('captcha')->isEvil($_POST['content']))
            {
                $this->send(array('result' => 'fail', 'reason' => 'needChecking', 'captcha' => $this->captcha->create4Thread()));
            }

            $this->thread->update($threadID);
            if(dao::isError()) $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $this->send(array('result' => 'success', 'locate' => inlink('view', "threadID=$threadID")));
        }

        $board = $this->loadModel('tree')->getById($thread->board);
        
        $this->view->title     = $this->lang->thread->edit . $this->lang->minus . $thread->title;
        $this->view->thread    = $thread;
        $this->view->board     = $board;
        $this->view->canManage = $this->thread->canManage($board->id);

        $this->display();
    }

    /**
     * View a thread.
     * 
     * @param  int    $threadID 
     * @param  int    $pageID 
     * @access public
     * @return void
     */
    public function view($threadID, $pageID = 1)
    {
        $thread = $this->thread->getByID($threadID);
        if(!$thread) die(js::locate('back'));

        /* Get thread board. */
        $board = $this->loadModel('tree')->getById($thread->board);

        /* Get replies. */
        $this->app->loadClass('pager', $static = true);
        $pager   = new pager(0, 10, $pageID);
        $replies = $this->loadModel('reply')->getByThread($threadID, $pager);

        /* Get all speakers. */
        $speakers = $this->thread->getSpeakers($thread, $replies);

        /* Set the views counter + 1; */
        $this->thread->plusCounter($threadID);

        $this->view->title    = $thread->title . $this->lang->minus . $board->name;
        $this->view->board    = $board;
        $this->view->thread   = $thread;
        $this->view->replies  = $replies;
        $this->view->pager    = $pager;
        $this->view->speakers = $this->loadModel('user')->getBasicInfo($speakers);

        $this->display();
    }

    /**
     * Locate to the thread and reply.
     * 
     * @param  int    $threadID 
     * @param  int    $replyID 
     * @access public
     * @return void
     */
    public function locate($threadID, $replyID = 0)
    {
        $position = $replyID ? $this->loadModel('reply')->getPosition($replyID) : ''; 
        $location = $this->createLink('thread', 'view', "threadID=$threadID", $position);
        header("location:$location");
    }

    /**
     * Delete a thread.
     * 
     * @param  int      $threadID 
     * @access public
     * @return void
     */
    public function delete($threadID)
    {
        $thread = $this->thread->getByID($threadID);
        if(!$thread) $this->send(array('result' => 'fail', 'message' => 'Not found'));

        if(!$this->thread->canManage($thread->board)) $this->send(array('result' => 'fail'));

        if(RUN_MODE == 'admin') $locate = helper::createLink('forum', 'admin');
        if(RUN_MODE == 'front') $locate = helper::createLink('forum', 'board', "board=$thread->board"); 

        if($this->thread->delete($threadID)) $this->send(array('result' => 'success', 'locate' => $locate));
        $this->send(array('result' => 'fail', 'message' => dao::getError()));
    }
   
    /**
     * Switch a thread's status.
     * 
     * @param  int    $threadID 
     * @access public
     * @return void
     */
    public function switchStatus($threadID)
    {
        $thread = $this->thread->getByID($threadID);
        if(!$thread) $this->send(array('result' => 'fail', 'message' => 'Not found'));

        if(!$this->thread->canManage($thread->board)) $this->send(array('result' => 'fail'));

        if($this->thread->switchStatus($threadID))
        {
            if(RUN_MODE == 'admin')
            {
                $locate = helper::createLink('forum', 'admin');
            }
            else
            {
                $locate = helper::createLink('forum', 'board', "board=$thread->board");
            }
            $this->send(array('result' => 'success', 'locate' => $locate));
        }

        $this->send(array('result' => 'fail', 'message' => dao::getError()));
    }

    /**
     * Set the stick level of a thread.
     * 
     * @param  int    $threadID 
     * @param  int    $stick 
     * @access public
     * @return void
     */
    public function stick($threadID, $stick)
    {
        $thread = $this->thread->getByID($threadID);
        if(!$this->thread->canManage($thread->board)) exit;

        $this->dao->update(TABLE_THREAD)->set('stick')->eq($stick)->where('id')->eq($threadID)->exec();
        if(dao::isError()) $this->send(array('result' =>'fail', 'message' => dao::getError()));

        $message = $stick == 0 ? $this->lang->thread->successUnstick : $this->lang->thread->successStick;
        $this->send(array('message' => $message, 'target' => '#manageBox', 'source' => inlink('view', "threaID=$threadID") . ' #manageMenu'));
    }

    /**
     * Delete a file.
     * 
     * @param  int    $threadID 
     * @param  int    $fileID 
     * @access public
     * @return void
     */
    public function deleteFile($threadID, $fileID)
    {
        if($this->app->user->account == 'guest') $this->send(array('result'=>'fail', 'message'=> 'guest'));

        $thread = $this->thread->getByID($threadID);
        if(!$thread) $this->send(array('result'=>'fail', 'message'=> 'data error'));

        /* Judge current user has priviledge to edit the thread or not. */
        if($this->thread->canManage($thread->board, $thread->author))
        {
            if($this->loadModel('file')->delete($fileID)) $this->send(array('result'=>'success'));
        }
        $this->send(array('result'=>'fail', 'message'=> 'error'));
    }
}
