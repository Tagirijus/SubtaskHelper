<?php

namespace Kanboard\Plugin\SubtaskHelper;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Translator;


class Plugin extends Base
{
    public function initialize()
    {
        // Views - Template Hook
        $this->template->hook->attach(
            'template:task:sidebar:actions',
            'SubtaskHelper:task_sidebar/subtaskhelper_convert_button'
        );
        $this->template->hook->attach(
            'template:task:sidebar:actions',
            'SubtaskHelper:task_sidebar/subtaskhelper_combine_button'
        );
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
        return '1.0.1';
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
