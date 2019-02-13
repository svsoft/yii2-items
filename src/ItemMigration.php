<?php
namespace svsoft\yii\items;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\factories\FieldFactory;
use svsoft\yii\items\factories\ItemTypeFactory;
use svsoft\yii\items\repositories\ItemTypeRepository;
use svsoft\yii\items\services\ItemTypeService;
use yii\base\Component;
use yii\db\Connection;
use yii\db\MigrationInterface;

abstract class ItemMigration extends Component implements MigrationInterface
{

    /**
     * @var ItemTypeRepository
     */
    protected $repository;

    /**
     * @var ItemTypeService
     */
    protected $itemTypeService;

    /**
     * @var Connection
     */
    public $db;


    /**
     * @var bool indicates whether the console output should be compacted.
     * If this is set to true, the individual commands ran within the migration will not be output to the console.
     * Default is false, in other words the output is fully verbose by default.
     * @since 2.0.13
     */
    public $compact = false;

    function init()
    {
        $this->repository = \Yii::$container->get(ItemTypeRepository::class);

        $this->itemTypeService = \Yii::$container->get(ItemTypeService::class);

        $this->db = \Yii::$container->get(Connection::class);

        parent::init();
    }

    /**
     * Prepares for a command to be executed, and outputs to the console.
     *
     * @param string $description the description for the command, to be output to the console.
     * @return float the time before the command is executed, for the time elapsed to be calculated.
     * @since 2.0.13
     */
    protected function beginCommand($description)
    {
        if (!$this->compact) {
            echo "    > $description ...";
        }
        return microtime(true);
    }

    /**
     * Finalizes after the command has been executed, and outputs to the console the time elapsed.
     *
     * @param float $time the time before the command was executed.
     * @since 2.0.13
     */
    protected function endCommand($time)
    {
        if (!$this->compact) {
            echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
        }
    }

    /**
     * @param $name
     * @param FieldFactory[] $fieldFactories
     *
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    function createItemType($name, $fieldFactories)
    {
        $time = $this->beginCommand("create item type $name");

        $itemType = (new ItemTypeFactory($name))->build();

        foreach($fieldFactories as $fieldFactory)
        {
            $itemType->addField($fieldFactory->build());
        }

        $this->repository->create($itemType);

        $this->endCommand($time);
    }

    /**
     * @param $name
     *
     * @return FieldFactory
     */
    function field($name, $type)
    {
        return new FieldFactory($name, $type);
    }

    /**
     * @param $name
     * @param FieldFactory $fieldFactory
     *
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws exceptions\FieldException
     * @throws exceptions\ItemTypeNotFoundException
     */
    function addField($itemTypeName, FieldFactory $fieldFactory)
    {

        $itemType = $this->repository->getByName($itemTypeName);

        $field = $fieldFactory->build();

        $time = $this->beginCommand("add field {$itemTypeName}.{$field->getName()}");

        $this->itemTypeService->addField($itemType, $field);

        $this->endCommand($time);
    }

    function changeType($itemTypeName, $fieldName, $type, $multiple = null)
    {
        $itemType = $this->repository->getByName($itemTypeName);

        $time = $this->beginCommand("change type to $type for field {$itemType->getName()}.{$fieldName}");
        $this->itemTypeService->changeType($itemType, $fieldName, $type, $multiple);
        $this->endCommand($time);
    }

    function deleteField($itemTypeName, $fieldName)
    {
        $time = $this->beginCommand("delete field {$itemTypeName}.{$fieldName}");

        $itemType = $this->repository->getByName($itemTypeName);
        $this->itemTypeService->removeField($itemType, $fieldName);

        $this->endCommand($time);
    }


    function renameField($itemTypeName, $fieldName, $newFieldName)
    {
        $time = $this->beginCommand("rename field {$itemTypeName}.{$fieldName} to {$newFieldName}");

        $itemType = $this->repository->getByName($itemTypeName);

        $this->itemTypeService->renameField($itemType, $fieldName, $newFieldName);

        $this->endCommand($time);
    }


    function deleteItemType($itemTypeName)
    {
        $time = $this->beginCommand("delete item type {$itemTypeName}");

        $itemType = $this->repository->getByName($itemTypeName);
        $this->itemTypeService->deleteItemType($itemType);

        $this->endCommand($time);
    }

    /**
     * @param $name
     *
     * @return FieldFactory
     */
    function integer($name)
    {
        return $this->field($name, Field::TYPE_INT);
    }

    /**
     * @param $name
     *
     * @return FieldFactory
     */
    function string($name)
    {
        return $this->field($name, Field::TYPE_STRING);
    }

    /**
     * @param $name
     *
     * @return FieldFactory
     */
    function text($name)
    {
        return $this->field($name, Field::TYPE_TEXT);
    }

    /**
     * @param $name
     *
     * @return FieldFactory
     */
    function file($name)
    {
        return $this->field($name, Field::TYPE_FILE);
    }

    /**
     * @param $name
     *
     * @return FieldFactory
     */
    function real($name)
    {
        return $this->field($name, Field::TYPE_REAL);
    }
}