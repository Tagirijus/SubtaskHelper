<div class="page-header">
    <h2><?= t('Subtask helper') . ' - ' . t('Remove all subtasks') ?></h2>
</div>

<div class="confirm">
    <p class="alert alert-info">
        <?= t('Do you really want to remove all subtasks?') ?>
    </p>

    <?= $this->modal->confirmButtons(
        'SubtaskHelperController',
        'removeAllSubtasks',
        array(
            'task_id' => $task['id'],
            'confirmation' => 'yes',
            'plugin' => 'SubtaskHelper'
        )
    ) ?>
</div>
