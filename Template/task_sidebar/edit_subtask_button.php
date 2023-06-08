<?php

$html = '<i class="fa fa-edit fa-fw js-modal-small" aria-hidden="true"></i>' . t('Edit subtask');
$href = $this->helper->url->href(
    'SubtaskHelperController',
    'selectSubtask',
    ['plugin' => 'SubtaskHelper', 'task_id' => $task['id']]
);
$a_element = '<a href="' . $href . '" class="js-modal-small" id="subtaskHelperEditSubtask" data-addUrl="' . $href . '">' . $html . '</a>';

?>

<?php if ($task['is_active'] == 1): ?>
<li>
    <?php echo $a_element; ?>
</li>
<?php endif ?>
