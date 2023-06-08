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

        $this->checkCSRFForm();

        if ($subtasks) {
            $done_subtasks = $this->getDoneSubtasks($subtasks);
            if ($done_subtasks) {
                $form = $this->request->getValues();
                $new_subtask = $this->combineSubtaskFromSubtasks($task, $form['subtaskName'], $done_subtasks);
                if ($this->subtaskModel->update($new_subtask, false)) {
                    // only remove other tasks, if new subtask creation is successful
                    $this->removeSubtasks($done_subtasks, $new_subtask['id']);
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
     * Create some kind of a new subtask from all the given
     * subtasks and sum up their times. Except it will
     * use the first subtask for it to not always create
     * a new completely new subtask.
     *
     * @param  array $task
     * @param  string $title
     * @param  array $subtasks
     * @return array
     */
    public function combineSubtaskFromSubtasks($task, $title, $subtasks)
    {
        $lowest_position = $this->getIdForLowestPositionSubtask($subtasks);
        $out = [
            'title' => $title,
            'status' => 2,
            'time_estimated' => 0,
            'time_spent' => 0,
            'user_id' => null,
            'task_id' => $task['id'],
            'position' => $lowest_position,
            'id' => null,
        ];
        foreach ($subtasks as $subtask) {
            $out['time_estimated'] += $subtask['time_estimated'];
            $out['time_spent'] += $subtask['time_spent'];
            if ($subtask['position'] == $lowest_position) {
                // this one is a bit tricky, since it basically just will
                // get the "first user id" in this iteration. this is not
                // quite the best solution, but its something ...
                $out['user_id'] = $subtask['user_id'];
                // also this line is needed to use the subtask in the
                // first position to later not create a new one
                $out['id'] = $subtask['id'];
            }
        }
        return $out;
    }

    /**
     * Output the id of the subtask with th lowest position.
     *
     * @param  array $subtasks
     * @return integer
     */
    public function getIdForLowestPositionSubtask($subtasks)
    {
        $out = 999;
        foreach ($subtasks as $subtask) {
            if ($subtask['position'] < $out) {
                $out = $subtask['position'];
            }
        }
        return $out;
    }

    /**
     * Remove all the subtasks of the given array,
     * except the one in the first position.
     *
     * @param  array $subtasks
     * @param  integer $exceptID
     * @return bool
     */
    public function removeSubtasks($subtasks, $exceptID = 0)
    {
        foreach ($subtasks as $subtask) {
            if ($subtask['id'] != $exceptID) {
                if (!$this->subtaskModel->remove($subtask['id'])) {
                    return false;
                }
            }
        }
        return true;
    }
}