<div class="page-header">
    <h2><?= t('Subtask helper') . ' - ' . t('Edit') ?></h2>
</div>
<form method="post" action="<?= $this->url->href('SubtaskHelperController', 'editSubtask', ['plugin' => 'SubtaskHelper', 'task_id' => $task['id'], 'project_id' => $task['project_id']]) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <div class="task-form-container">

        <!-- Selector for subtasks -->

        <?php
            // find the first non-done subtask
            $values = [];
            foreach ($subtasks as $subtask) {
                if ($subtask['status'] != 2) {
                    $values['subtask'] = $subtask['id'];
                    break;
                }
            }
            if (empty($values)) {
                $values['subtask'] = -999;
            }

            // prepare the select options
            $prepared_subtasks = [];
            foreach ($subtasks as $subtask) {
                $prepared_subtasks[$subtask['id']] = $subtask['title'];
            }
        ?>

        <div class="task-form-main-column">
            <?= $this->form->label(t('Subtask'), 'subtask') ?>
            <!-- Maybe uncomment the following so that I can tab into the select -->
            <!-- <input
                type="text"
                value=""
                class="input"
                placeholder="workaround for autofocus"
                autofocus
            > -->
            <?= $this->form->select('subtask', $prepared_subtasks, $values, [], ['utofocus']) ?>
        </div>

        <div class="task-form-bottom">
            <?= $this->modal->submitButtons(['submitLabel' => 'Ok']) ?>
        </div>
    </div>
</form>
