# SubtaskHelper

#### _Plugin for [Kanboard](https://github.com/fguillot/kanboard "Kanboard - Kanban Project Management Software")_

With this plugin I added some new functions on the task site. It will help e.g. generating a subtask with the already entered times (estimated & spent) from the main task. Also there is the option to combine already done subtasks into one.

In the next version (coding it after I write this) it should be possible to enter subtask in the main task description field (underneath the last "---" string) so that they get parsed as in the "add subtasks" modal.


Screenshots
----------

**Entering modal for the conversion**

![SubtaskHelper entering modal](../master/Screenshots/SubtaskHelper_convert_modal.png)

**Entering modal for the summarizing**

![SubtaskHelper entering modal](../master/Screenshots/SubtaskHelper_combine_modal.png)


Usage
-------------

On the tasks summary page there are new options in the sidebar to execute the respecting features:

1. **Converting to subtask**: With this option you can create a new subtask, if noone exists already. This subtask will get the name the user enteres into the modal. After that it will get the estimated and spent time from the main task.
2. **Combine subtasks into one**: This lets you enter a subtask name, which will be used to combine all done subtasks. It will get all their times summarized and delete the other subtasks.
3. **Remove all subtasks**: With this you can, gues what?, remove all subtasks of the task.


Compatibility
-------------

- Requires [Kanboard](https://github.com/fguillot/kanboard "Kanboard - Kanban Project Management Software") ≥`1.2.27`

#### Other Plugins & Action Plugins
- _No known issues_
#### Core Files & Templates
- _No database changes_


Changelog
---------

Read the full [**Changelog**](../master/changelog.md "See changes")
 

Installation
------------

1. Go into Kanboards `plugins/` folder
2. `git clone https://github.com/Tagirijus/SubtaskHelper`


Translations
------------

- _Contributors welcome_
- _Starter template available_

Authors & Contributors
----------------------

- [@Tagirijus](https://github.com/Tagirijus) - Author
- _Contributors welcome_


License
-------
- This project is distributed under the [MIT License](../master/LICENSE "Read The MIT license")
