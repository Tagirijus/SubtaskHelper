<?php

$html = '<i class="fa fa-arrow-right fa-fw js-modal-small" aria-hidden="true"></i>' . t('Subtask from task times');
$href = $this->helper->url->href(
    'SubtaskHelperController',
    'convertModal',
    ['plugin' => 'SubtaskHelper', 'task_id' => $task['id']]
);
$a_element = '<a href="' . $href . '" class="js-modal-small" id="subtaskHelperConvert" data-addUrl="' . $href . '">' . $html . '</a>';

?>

<?php if ($task['is_active'] == 1): ?>
<li>
    <?php echo $a_element; ?>
</li>
<?php endif ?>
