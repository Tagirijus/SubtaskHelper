<?php

namespace Kanboard\Plugin\SubtaskHelper;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Translator;


class Plugin extends Base
{
    public function initialize()
    {
        // Helper
        $this->helper->register('subtaskHelperHelper', '\Kanboard\Plugin\SubtaskHelper\Helper\SubtaskHelperHelper');

        // Views - Template Hook
        $this->template->hook->attach(
            'template:task:sidebar:actions',
            'SubtaskHelper:task_sidebar/subtaskhelper_convert_button'
        );
        $this->template->hook->attach(
            'template:task:sidebar:actions',
            'SubtaskHelper:task_sidebar/subtaskhelper_combine_button'
        );
        $this->template->hook->attach(
            'template:task:sidebar:actions',
            'SubtaskHelper:task_sidebar/edit_subtask_button'
        );
        $this->template->hook->attach(
            'template:task:sidebar:actions',
            'SubtaskHelper:task_sidebar/subtaskhelper_removeall_button'
        );
        $this->template->hook->attach(
            'template:config:sidebar',
            'SubtaskHelper:config/subtaskhelper_config_sidebar'
        );

        // View - Task Creation Modal Hooks
        $this->template->hook->attach(
            'template:task:form:first-column',
            'SubtaskHelper:task_creation_modal/subtasks_in_description'
        );


        // Reference hook
        $this->hook->on('model:subtask:creation:prepare', function (&$values) {
            return $this->helper->subtaskHelperHelper->prepareSubtaskByTimesSyntax($values);
        });
        $this->hook->on('model:task:creation:aftersave', function ($task_id) {
            return $this->container['subtaskHelperController']->parseSubtasksOnTaskCreation($task_id);
        });

        // Extra Page - Routes
        $this->route->addRoute('/subtaskhelper/config', 'SubtaskHelperController', 'showConfig', 'SubtaskHelper');
    }

    public function getClasses()
    {
        return [
            'Plugin\SubtaskHelper\Controller' => [
                'SubtaskHelperController',
            ]
        ];
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginName()
    {
        return 'SubtaskHelper';
    }

    public function getPluginDescription()
    {
        return t('Adds features to do stuff with subtasks');
    }

    public function getPluginAuthor()
    {
        return 'Tagirijus';
    }

    public function getPluginVersion()
    {
        return '1.5.0';
    }

    public function getCompatibleVersion()
    {
        // Examples:
        // >=1.0.37
        // <1.0.37
        // <=1.0.37
        return '>=1.2.27';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/Tagirijus/SubtaskHelper';
    }
}
