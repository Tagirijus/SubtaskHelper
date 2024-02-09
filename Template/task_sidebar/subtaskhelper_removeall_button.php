<?php

$html = '<i class="fa fa-trash fa-fw js-modal-small" aria-hidden="true"></i>' . t('Remove all subtasks');
$href = $this->helper->url->href(
    'SubtaskHelperController',
    'removeAllModal',
    ['plugin' => 'SubtaskHelper', 'task_id' => $task['id']]
);
$a_element = '<a href="' . $href . '" class="js-modal-small" id="subtaskHelperRemoveAll" data-addUrl="' . $href . '">' . $html . '</a>';

?>

<?php if ($task['is_active'] == 1): ?>
<li>
    <?php echo $a_element; ?>
</li>
<?php endif ?>
