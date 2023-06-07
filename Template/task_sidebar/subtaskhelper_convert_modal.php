<div class="page-header">
    <h2><?= t('Subtask helper') ?></h2>
</div>
<form method="post" action="<?= $this->url->href('SubtaskHelperController', 'convertExecute', ['plugin' => 'SubtaskHelper', 'task_id' => $task['id'], 'project_id' => $task['project_id']]) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <div class="task-form-container">

        <!-- Name of subtask -->

        <div class="task-form-main-column">
            <?= $this->form->label(t('Subtask name'), 'subtaskName') ?>
            <?= $this->form->text('subtaskName', [], [], [
                'autofocus',
                'required',
            ]) ?>

            <p style="font-style: italic; font-size: .75em; opacity: .75; margin-top: 1em;">
                <?= t('Enter name for new subtask, which will get the task times') ?>
            </p>
        </div>

        <div class="task-form-bottom">
            <?= $this->modal->submitButtons() ?>
        </div>
    </div>
</form>
