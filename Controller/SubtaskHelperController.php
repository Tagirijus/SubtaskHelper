<?php

namespace Kanboard\Plugin\SubtaskHelper\Controller;

use Kanboard\Core\Controller\AccessForbiddenException;


class SubtaskHelperController extends \Kanboard\Controller\PluginController
{
    /**
     * Settins page for the SubtaskHelper plugin.
     *
     * @return HTML response
     */
    public function showConfig()
    {
        // !!!!!
        // When I want to add new config options, I also have to add them
        // in the SubtaskHelperHelper.php in the getConfig() Method !
        // !!!!!
        $this->response->html($this->helper->layout->config('SubtaskHelper:config/subtaskhelper_config', $this->helper->subtaskHelperHelper->getConfig()));
    }

    /**
     * Save the setting for SubtaskHelper.
     */
    public function saveConfig()
    {
        $form = $this->request->getValues();

        $values = [
            'subtaskhelper_enable_times_syntax' => isset($form['enable_times_syntax']) ? 1 : 0,
        ];

        $this->languageModel->loadCurrentLanguage();

        if ($this->configModel->save($values)) {
            $this->flash->success(t('Settings saved successfully.'));
        } else {
            $this->flash->failure(t('Unable to save your settings.'));
        }

        return $this->response->redirect($this->helper->url->to('SubtaskHelperController', 'showConfig', ['plugin' => 'SubtaskHelper']), true);
    }

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

    /**
     * Show the modal for selecting the subtask.
     *
     * @return HTML response
     */
    public function selectSubtask()
    {
        $task = $this->getTask();
        $subtasks = $this->subtaskModel->getAllByTaskIds([$task['id']]);

        if (empty($subtasks)) {
            $this->flash->success(t('No subtasks available'));
            return $this->response->redirect($this->helper->url->to('TaskViewController', 'show', ['task_id' => $task['id']]), true);
        }

        $user = $this->getUser();

        if ($user['username'] !== $task['assignee_username']) {
            throw new AccessForbiddenException();
        }

        $this->response->html($this->template->render(
            'SubtaskHelper:task_sidebar/edit_subtask_modal', [
                'task' => $task,
                'user' => $user,
                'subtasks' => $subtasks,
            ]
        ));
    }

    /**
     * Open the edit modal for the selected subtask.
     *
     * @return HTML response
     */
    public function editSubtask()
    {
        $task = $this->getTask();
        $form = $this->request->getValues();
        $subtask = $this->subtaskModel->getById($form['subtask']);

        return $this->response->html($this->template->render('subtask/edit', array(
            'values' => $subtask,
            'errors' => [],
            'users_list' => $this->projectUserRoleModel->getAssignableUsersList($task['project_id']),
            'status_list' => $this->subtaskModel->getStatusList(),
            'subtask' => $subtask,
            'task' => $task,
        )));
        // return $this->response->redirect($this->helper->url->to('SubtaskController', 'edit', ['task_id' => $task['id'], 'subtask_id' => $form['subtask']]), true);
    }
}