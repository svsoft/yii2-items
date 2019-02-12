<?php
namespace svsoft\yii\items;

use svsoft\yii\items\entities\Field;
use svsoft\yii\items\factories\FieldFactory;
use svsoft\yii\items\factories\ItemTypeFactory;
use svsoft\yii\items\repositories\ItemTypeRepository;
use svsoft\yii\items\services\ItemTypeService;
use yii\base\Component;
use yii\db\MigrationInterface;

class ItemMigration extends Component implements MigrationInterface
{

    /**
     * @var ItemTypeRepository
     */
    protected $repository;

    /**
     * @var ItemTypeService
     */
    protected $itemTypeService;


    function init()
    {
        $this->repository = \Yii::$container->get(ItemTypeRepository::class);

        $this->itemTypeService = \Yii::$container->get(ItemTypeService::class);

        parent::init();
    }

    function up()
    {


//        $this->createItemType('Product',[
//            $this->string('name'),
//            $this->text('description'),
//            $this->real('price')
//        ]);

        // $this->addField('Project', $this->field('date')->string());
//        $this->renameField('Project','address','address-2');

//        $this->createItemType('Test',[
//            $this->field('title')->string(),
//            $this->field('content')->text()
//        ]);

    }

    function down()
    {
        // TODO: Implement down() method.
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
        $itemType = (new ItemTypeFactory($name))->build();

        foreach($fieldFactories as $fieldFactory)
        {
            $itemType->addField($fieldFactory->build());
        }

        $this->repository->create($itemType);
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
    function addField($name, FieldFactory $fieldFactory)
    {
        $itemType = $this->repository->getByName($name);

        $field = $fieldFactory->build();

        $this->itemTypeService->addField($itemType, $field);
    }

    function changeType($itemTypeName, $fieldName, $type, $multiple = null)
    {
        $itemType = $this->repository->getByName($itemTypeName);

        $this->itemTypeService->changeType($itemType, $fieldName, $type, $multiple);
    }

    function deleteField($itemTypeName, $fieldName)
    {
        $itemType = $this->repository->getByName($itemTypeName);

        $this->itemTypeService->removeField($itemType, $fieldName);
    }


    function renameField($itemTypeName, $fieldName, $newFieldName)
    {
        $itemType = $this->repository->getByName($itemTypeName);

        $this->itemTypeService->renameField($itemType, $fieldName, $newFieldName);
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