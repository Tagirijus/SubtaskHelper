<?php

namespace Kanboard\Plugin\SubtaskHelper\Helper;

use Kanboard\Core\Base;


class SubtaskHelperHelper extends Base
{
    /**
     * Get configuration for plugin as array.
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'title' => t('SubtaskHelper') . ' &gt; ' . t('Settings'),
            'enable_times_syntax' => $this->configModel->get('subtaskhelper_enable_times_syntax', 1),
        ];
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
     * Get only the started subtasks from the given subtasks.
     *
     * @param  array $subtasks
     * @return array
     */
    public function getStartedSubtasks($subtasks)
    {
        $out = [];
        foreach ($subtasks as $subtask) {
            if ($subtask['status'] == 1) {
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
     * @param  array $done_subtasks
     * @param  array $started_subtasks
     * @return array
     */
    public function combineSubtaskFromSubtasks($task, $title, $done_subtasks, $started_subtasks)
    {
        $out = $this->prepareCombinedSubtask($task, $title, $done_subtasks);
        $out = $this->combineSubtaskFromDoneSubtasks($out, $done_subtasks);
        $out = $this->combineSubtaskFromStartedSubtasks($out, $started_subtasks);
        return $out;
    }

    /**
     * Generate a new array, which will be the combined subtask
     * later. It might be a new subtask or it might even be
     * an existing one to not have a new subtask id for every
     * combining of subtasks.
     *
     * @param  array $task
     * @param  string $title
     * @param  array $done_subtasks
     * @return array
     */
    public function prepareCombinedSubtask($task, $title, $done_subtasks)
    {
        $out = [
            'title' => $title,
            'status' => 2,
            'time_estimated' => 0,
            'time_spent' => 0,
            'user_id' => null,
            'task_id' => $task['id'],
            'position' => -1,
            'id' => null,
        ];
        // basically this will use the lowest done subtask instead
        // of creating a new subtask for the combined subtask later.
        // this way a new subtask won't be created for every
        // combining of subtask to save a little Sql DB ids in the
        // subtasks table. it might be really pre-mature optimization
        // however ...
        foreach ($done_subtasks as $subtask) {
            // find a subtask with a lower position and use
            // its position and ids
            if ($out['position'] == -1 || $subtask['position'] < $out['position']) {
                $out['position'] = $subtask['position'];
                $out['user_id'] = $subtask['user_id'];
                $out['id'] = $subtask['id'];
            }
        }
        // maybeeeee ... create a new subtask, since there is no
        // existing done subtask
        if (empty($done_subtasks)) {
            $id = $this->subtaskModel->create($out);
            $out['id'] = $id;
        }
        return $out;
    }

    /**
     * Get the times from the done subtasks and add them into
     * the new subtask.
     *
     * @param  array $new_subtasks
     * @param  array $done_subtasks
     * @return array
     */
    public function combineSubtaskFromDoneSubtasks($new_subtask, $done_subtasks)
    {
        foreach ($done_subtasks as $subtask) {
            $new_subtask['time_estimated'] += $subtask['time_estimated'];
            $new_subtask['time_spent'] += $subtask['time_spent'];
        }
        return $new_subtask;
    }

    /**
     * Get the times from the started subtasks and add them into
     * the new subtask. This time use the spent times for both:
     * estimated and spent, since the tasks are still running.
     *
     * @param  array $new_subtasks
     * @param  array $started_subtasks
     * @return array
     */
    public function combineSubtaskFromStartedSubtasks($new_subtask, $started_subtasks)
    {
        foreach ($started_subtasks as $subtask) {
            $new_subtask['time_estimated'] += $subtask['time_estimated'];
            $new_subtask['time_spent'] += $subtask['time_spent'];
            if (is_null($new_subtask['user_id'])) {
                $new_subtask['user_id'] = $subtask['user_id'];
            }
        }
        return $new_subtask;
    }

    /**
     * Remove all the done subtasks of the given array,
     * except the one with the given id.
     *
     * @param  array $done_subtasks
     * @param  integer $exceptID
     * @return bool
     */
    public function removeDoneSubtasks($done_subtasks, $exceptID = 0)
    {
        foreach ($done_subtasks as $subtask) {
            if ($subtask['id'] != $exceptID) {
                if (!$this->subtaskModel->remove($subtask['id'])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Adjust the times of the started subtasks to have
     * none time spent anymor and the estimated times
     * are the estimation - spent.
     *
     * @param  array $started_subtasks
     * @return bool
     */
    public function adjustStartedSubtasks($started_subtasks)
    {
        foreach ($started_subtasks as $subtask) {
            $subtask_updated = [
                'id' => $subtask['id'],
                'time_estimated' => $subtask['time_estimated'],
                'time_spent' => $subtask['time_spent'],
            ];
            $subtask_updated['time_estimated'] -= $subtask_updated['time_spent'];
            // yet the estimated should never be negative, but 0 instead then
            if ($subtask_updated['time_estimated'] < 0) {
                $subtask_updated['time_estimated'] = 0.0;
            }
            $subtask_updated['time_spent'] = 0.0;
            if (!$this->subtaskModel->update($subtask_updated, false)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Interpretes a given subtask title this way:
     *
     * [title]:[estimated time]
     *
     * So it splits the title string by the first ":".
     * Left site is the title and the right site is
     * a time string like 0:45, 0.75 or 0,75.
     *
     * It then changes the title of the given subtask
     * to this and also changes the estimated time.
     *
     * @param  array &$values
     * @return array
     */
    public function prepareSubtaskByTimesSyntax(&$values)
    {
        if ($this->configModel->get('subtaskhelper_enable_times_syntax', 1) == 1) {
            $split = explode(':', $values['title'], 2);
            $values['title'] = trim($split[0]);
            if (isset($split[1])) {
                $values['time_estimated'] = $this->parseTime(trim($split[1]));
            }
        }
        return $values;
    }

    /**
     * This function is for interpreting the given time
     * input string.
     *
     * @param  string $time
     * @return float
     */
    private function parseTime($time)
    {
        // for the german freaks like me, who might use , instead of .
        $time = str_replace(',', '.', $time);

        // maybe it's a time formatted string like "1:45" ...
        if (strpos($time, ':') !== false) {
            // ... then convert it to a float
            $hours = explode(':', $time)[0];
            $minutes = explode(':', $time)[1];
            $time = (float) $hours + (float) $minutes / 60;
        }

        return (float) $time;
    }
}