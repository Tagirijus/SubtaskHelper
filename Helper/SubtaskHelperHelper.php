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