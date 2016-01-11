<?php

namespace Robo\TaskCollection;

use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;

/**
 * Creates a task wrapper that will manage rollback and collection
 * management to a task when it runs.  Tasks are automatically
 * wrapped in a CollectionTask when added to a task collection.
 *
 * Clients may need to wrap their task in a CollectionTask if it
 * creates transient objects.  This is usually best done via
 * TransientManager::transientTask().
 *
 * @see Robo\Task\FileSystem\loadTasks::taskTmpDir
 */
class CollectionTask extends BaseTask
{
    private $collection;
    private $task;
    private $rollbackTask;

    public function __construct(Collection $collection, TaskInterface $task, TaskInterface $rollbackTask = null)
    {
        $this->collection = $collection;
        $this->task = ($task instanceof self) ? $task->getTask() : $task;
        $this->rollbackTask = $rollbackTask;
    }

    public function getTask()
    {
        return $this->task;
    }

    public function run()
    {
        if ($this->rollbackTask) {
            $this->collection->registerRollback($this->rollbackTask);
        }
        $this->collection->register($this->task);

        return $this->task->run();
    }

    public function __call($function, $args)
    {
        return call_user_func_array(array($this->task, $function), $args);
    }
}
