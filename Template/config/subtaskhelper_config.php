<div class="page-header">
    <h2><?= t('Subtaskhelper configuration') ?></h2>
</div>
<form method="post" action="<?= $this->url->href('SubtaskHelperController', 'saveConfig', ['plugin' => 'SubtaskHelper']) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <br>


    <!-- Global -->

    <p>
        <h2><?= t('Global') ?></h2>
    </p>

    <div class="task-form-container">

        <div class="task-form-main-column">
            <table>

                <tr>
                    <td>
                        <?= $this->form->label(t('Interprete given subtask names in this syntax form: [title]:[estimated time]. Means you could add a subtask with the title "Subby Tasky" with an estimaed time of "1:45h" by entering "Subby Tasky:1:45" or "Subby Tasky:1.75" or "Subby Tasky:1,75" into the subtask creation field. Also for every line for every new subtask at once, basically!'), 'enable_times_syntax') ?>
                    </td>
                    <td>
                        <?= $this->form->checkbox('enable_times_syntax', t('enabled'), 1, $enable_times_syntax) ?>
                    </td>
                </tr>

            </table>
        </div>

    </div>

    <br>
    <br>



    <div class="task-form-bottom">
        <?= $this->modal->submitButtons() ?>
    </div>

</form>
