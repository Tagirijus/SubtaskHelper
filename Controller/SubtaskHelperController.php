<?php

namespace Kanboard\Plugin\SubtaskHelper\Controller;

use Kanboard\Core\Controller\AccessForbiddenException;


class SubtaskHelperController extends \Kanboard\Controller\PluginController
{
    /**
     * Show the modal for entering the subtask name.
     *
     * @return HTML response
     */
    public function convertModal()
    {
        $task = $this->getTask();
        $subtasks = $this->subtaskModel->getAllByTaskIds([$task['id']]);
        $user = $this->getUser();

        if ($user['username'] !== $task['assignee_username']) {
            throw new AccessForbiddenException();
        }

        $this->response->html($this->template->render(
            'SubtaskHelper:task_sidebar/subtaskhelper_convert_modal', [
                'task' => $task,
                'user' => $user
            ]
        ));
    }

    /**
     * Execute the converter feature.
     */
    public function convertExecute()
    {
        $task = $this->getTask();
        $hasSubtasks = !empty($this->subtaskModel->getAllByTaskIds([$task['id']]));
        $user = $this->getUser();

        $this->checkCSRFForm();

        if ($hasSubtasks) {
            $this->flash->failure(t('Task already has subtasks'));
        } else {
            $form = $this->request->getValues();

            if ($user['username'] !== $task["assignee_username"]) {
                throw new AccessForbiddenException();
            }

            // prepare the subtask to create
            $subtask = [
                'title' => $form['subtaskName'],
                'task_id' => $task['id'],
                'time_estimated' => $task['time_estimated'],
                'time_spent' => $task['time_spent'],
                'user_id' => $user['id'],
                'status' => $task['time_spent'] > 0 ? 1 : 0,
            ];

            // create this subtask now
            if ($this->subtaskModel->create($subtask)) {
                $this->flash->success(t('Subtask converted from task'));
            } else {
                $this->flash->failure(t('Unable to convert to subtask'));
            }
        }

        return $this->response->redirect($this->helper->url->to('TaskViewController', 'show', ['task_id' => $task['id']]), true);
    }

    /**
     * Show the modal for entering the subtask name.
     *
     * @return HTML response
     */
    public function combineModal()
    {
        $task = $this->getTask();
        $subtasks = $this->subtaskModel->getAllByTaskIds([$task['id']]);
        $user = $this->getUser();

        if ($user['username'] !== $task['assignee_username']) {
            throw new AccessForbiddenException();
        }

        $this->response->html($this->template->render(
            'SubtaskHelper:task_sidebar/subtaskhelper_combine_modal', [
                'task' => $task,
                'user' => $user
            ]
        ));
    }

    /**
     * Execute the combiner feature.
     */
    public function combineExecute()
    {
        $task = $this->getTask();
        $subtasks = $this->subtaskModel->getAllByTaskIds([$task['id']]);

        $this->checkCSRFForm();

        if ($subtasks) {
            $done_subtasks = $this->helper->subtaskHelperHelper->getDoneSubtasks($subtasks);
            if ($done_subtasks) {
                $form = $this->request->getValues();
                $new_subtask = $this->helper->subtaskHelperHelper->combineSubtaskFromSubtasks($task, $form['subtaskName'], $done_subtasks);
                if ($this->subtaskModel->update($new_subtask, false)) {
                    // only remove other tasks, if new subtask creation is successful
                    $this->helper->subtaskHelperHelper->removeSubtasks($done_subtasks, $new_subtask['id']);
                    $this->flash->success(t('Subtask combined from done subtasks'));
                } else {
                    $this->flash->failure(t('Could not combined subtask from done subtasks'));
                }
            } else {
                $this->flash->failure(t('No subtasks available'));
            }
        } else {
            $this->flash->failure(t('No subtasks available'));
        }

        return $this->response->redirect($this->helper->url->to('TaskViewController', 'show', ['task_id' => $task['id']]), true);
    }
}