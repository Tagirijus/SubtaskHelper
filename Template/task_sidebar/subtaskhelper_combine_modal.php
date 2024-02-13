<div class="page-header">
    <h2><?= t('Subtask helper') . ' - ' . t('Combiner') ?></h2>
</div>
<form method="post" action="<?= $this->url->href('SubtaskHelperController', 'combineExecute', ['plugin' => 'SubtaskHelper', 'task_id' => $task['id'], 'project_id' => $task['project_id']]) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <div class="task-form-container">

        <!-- Name of subtask -->

        <div class="task-form-main-column">
            <?= $this->form->label(t('Subtask name'), 'subtaskName') ?>
            <?= $this->form->text('subtaskName', ['subtaskName' => $subtaskName], [], [
                'autofocus',
                'required',
            ]) ?>

            <p style="font-style: italic; font-size: .75em; opacity: .75; margin-top: 1em;">
                <?= t('Enter name for the combining subtask, which will combine done or tarted work of other subtasks. Done subtasks will be sumarized in this and be deleted afterwards. Started yet not done subtasks will add their spent time to the combined subtask and leave their remaining time.') ?>
            </p>
        </div>

        <div class="task-form-bottom">
            <?= $this->modal->submitButtons() ?>
        </div>
    </div>
</form>
