<?php
namespace svsoft\yii\items;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\factories\FieldBuilder;
use svsoft\yii\items\factories\FieldFactory;
use svsoft\yii\items\factories\FieldTypeBuilder;
use svsoft\yii\items\factories\ItemTypeBuilder;
use svsoft\yii\items\repositories\ItemTypeRepository;
use svsoft\yii\items\services\ItemManager;
use svsoft\yii\items\services\Items;
use svsoft\yii\items\services\ItemTypeManager;
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
     * @var ItemTypeManager
     */
    private $itemTypeService;

    /**
     * @var ItemManager
     */
    private $itemManager;

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
        /** @var Items $items */
        $items = \Yii::$container->get(Items::class);

        $this->repository = $items->getItemTypeRepository();

        $this->itemManager = $items->itemManager;
        $this->itemTypeService = \Yii::$container->get(ItemTypeManager::class);

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
    /**
     * @param $name
     * @param FieldTypeBuilder[] $builders
     *
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws exceptions\FieldException
     */
    function createItemType($name, $typeBuilders = [])
    {
        $time = $this->beginCommand("create item type $name");

        foreach($typeBuilders as $fieldName=>$typeBuilder)
        {
            $type = $typeBuilder->build();

            /** @var FieldBuilder $fieldBuilder */
            $fieldBuilder = \Yii::createObject(FieldBuilder::class);
            $field = $fieldBuilder->setType($type)->setName($fieldName)->build();
            $fields[] = $field;
            //$itemType->addField($field);
        }

        $itemType = (new ItemTypeBuilder())->setName($name)->setFields($fields)->build();

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
        //return new
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

    function changeType($itemTypeName, $fieldName, FieldTypeBuilder $typeBuilder)
    {
        $itemType = $this->repository->getByName($itemTypeName);
        $type = $typeBuilder->build();

        $time = $this->beginCommand("change type to {$type->getId()} for field {$itemType->getName()}.{$fieldName}");
        $this->itemTypeService->changeType($itemType, $fieldName, $type);
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

    /**
     * @param $itemTypeName
     *
     * @return entities\ItemType
     * @throws exceptions\ItemTypeNotFoundException
     */
    function getItemType($itemTypeName)
    {
        return $this->repository->getByName($itemTypeName);
    }


    function deleteItemType($itemTypeName)
    {
        $time = $this->beginCommand("delete item type {$itemTypeName}");

        $itemType = $this->repository->getByName($itemTypeName);
        $this->itemTypeService->deleteItemType($itemType);

        $this->endCommand($time);
    }

    /**
     * @param $typeId
     *
     * @return FieldTypeBuilder
     * @throws \yii\base\InvalidConfigException
     */
    function fieldTypeBuilder($typeId)
    {
        /** @var FieldTypeBuilder $builder */
        $builder = \Yii::createObject(FieldTypeBuilder::class);
        $builder->setId($typeId);

        return $builder;
    }

    /**
     * @return FieldTypeBuilder
     * @throws \yii\base\InvalidConfigException
     */
    function intType()
    {
        return $this->fieldTypeBuilder(Field::TYPE_INT);
    }

    /**
     * @param $itemTypeName
     *
     * @return FieldTypeBuilder
     * @throws \yii\base\InvalidConfigException
     * @throws exceptions\ItemTypeNotFoundException
     */
    function itemType($itemTypeName)
    {
        $itemType = $this->repository->getByName($itemTypeName);

        return $this->fieldTypeBuilder(Field::TYPE_ITEM)->setParam('itemTypeId', $itemType->getId());

    }

    function stringType()
    {
        return $this->fieldTypeBuilder(Field::TYPE_STRING);
    }

    function textType()
    {
        return $this->fieldTypeBuilder(Field::TYPE_TEXT);
    }

    /**
     * @return FieldTypeBuilder
     * @throws \yii\base\InvalidConfigException
     */
    function fileType()
    {
        return $this->fieldTypeBuilder(Field::TYPE_FILE);
    }

    /**
     * @return FieldTypeBuilder
     * @throws \yii\base\InvalidConfigException
     */
    function htmlType()
    {
        return $this->fieldTypeBuilder(Field::TYPE_HTML);
    }

    /**
     * @return FieldTypeBuilder
     * @throws \yii\base\InvalidConfigException
     */
    function realType()
    {
        return $this->fieldTypeBuilder(Field::TYPE_REAL);
    }


    /**
     * @param $itemTypeName
     * @param $attributes
     *
     * @throws \Throwable
     * @throws exceptions\ItemAttributeNotFound
     * @throws exceptions\ItemTypeNotFoundException
     * @throws exceptions\ValidationErrorException
     */
    function insertItem($itemTypeName, $attributes)
    {
        $itemType = $this->getItemType($itemTypeName);

        $itemForm = $this->itemManager->createForm($itemType);

        $itemForm->setAttributes($attributes);

        return $this->itemManager->create($itemForm);
    }


}