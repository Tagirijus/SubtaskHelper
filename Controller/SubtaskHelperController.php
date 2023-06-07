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
            $this->flash->success(t('Task already has subtasks'));
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
        $user = $this->getUser();

        $this->checkCSRFForm();

        if ($subtasks) {
            $done_subtasks = $this->getDoneSubtasks($subtasks);
            if ($done_subtasks) {
                $form = $this->request->getValues();
                $new_subtask = $this->createSubtaskFromSubtasks($task, $form['subtaskName'], $done_subtasks);
                $new_subtask_id = $this->subtaskModel->create($new_subtask);
                if ($new_subtask_id) {
                    // only remove other tasks, if new subtask creation is successful
                    $this->removeSubtasks($done_subtasks);
                    $this->subtaskPositionModel->changePosition($task['id'], $new_subtask_id, 1);
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

    /**
     * Get only the done subtasks from the given subtasks.
     *
     * @param  array $subtasks
     * @return array
     */
    public function getDoneSubtasks($subtasks)
    {
        $out = [];
        foreach ($subtasks as $subtask) {
            if ($subtask['status'] == 2) {
                $out[] = $subtask;
            }
        }
        return $out;
    }

    /**
     * Create a new subtask from all the given
     * subtasks and sum up their times.
     *
     * @param  array $task
     * @param  string $title
     * @param  array $subtasks
     * @return array
     */
    public function createSubtaskFromSubtasks($task, $title, $subtasks)
    {
        $out = [
            'title' => $title,
            'status' => 2,
            'time_estimated' => 0,
            'time_spent' => 0,
            'user_id' => 0,
            'task_id' => $task['id'],
            'position' => 1,
        ];
        foreach ($subtasks as $subtask) {
            $out['time_estimated'] += $subtask['time_estimated'];
            $out['time_spent'] += $subtask['time_spent'];
            // this one is a bit tricky, since it basically just will
            // get the "last user id" in this iteration. this is not
            // quite the best solution, but its something ...
            $out['user_id'] = $subtask['user_id'];
        }
        return $out;
    }

    /**
     * Remove all the subtasks of the given array.
     *
     * @param  array $subtasks
     * @return bool
     */
    public function removeSubtasks($subtasks)
    {
        foreach ($subtasks as $subtask) {
            if (!$this->subtaskModel->remove($subtask['id'])) {
                return false;
            }
        }
        return true;
    }
}