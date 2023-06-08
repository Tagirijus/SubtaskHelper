<?php

namespace Kanboard\Plugin\SubtaskHelper\Helper;

use Kanboard\Core\Base;


class SubtaskHelperHelper extends Base
{
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