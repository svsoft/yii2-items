<?php

use yii\db\Migration;

/**
 * Handles the creation of table `product`.
 */
class m190118_152700_create_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $item = \svsoft\yii\items\repositories\tables\TableItem::TABLE;
        $value = \svsoft\yii\items\repositories\tables\TableValue::TABLE;
        $type = \svsoft\yii\items\repositories\tables\TableItemType::TABLE;
        $field = \svsoft\yii\items\repositories\tables\TableField::TABLE;

        $this->createTable($type, [
            'key'=>$this->primaryKey()->unsigned(),
            'id' => $this->string(15)->unique(),
            'name' => $this->string(255)->unique(),
        ]);


        $this->createTable($field, [
            'key'=>$this->primaryKey()->unsigned(),
            'item_type_key'=>$this->integer()->unsigned()->notNull(),
            'id' => $this->string(15)->unique()->notNull(),
            'name' => $this->string(255)->notNull(),
            'type' => $this->string(255)->notNull(),
            'multiple' => $this->boolean()->notNull(),
        ]);
        $this->addForeignKey($field . '_item_type_key', $field, 'item_type_key', $type, 'key');
        $this->createIndex($field . '_name', $field, ['item_type_key','name'], true);

        $this->createTable($item, [
            'key'=>$this->primaryKey()->unsigned(),
            'item_type_key'=>$this->integer()->unsigned(),
            'id' => $this->string(15)->unique(),
        ]);
        $this->addForeignKey($item . '_item_type_key', $item, 'item_type_key', $type, 'key');

        $this->createTable($value, [
            'item_key'=> $this->integer()->unsigned()->notNull(),
            'field_key'=> $this->integer()->unsigned()->notNull(),
            'value_string'=>$this->string(),
            'value_int'=>$this->integer(),
            'value_text'=>$this->text(),
            'value_real'=>$this->decimal(10.4),
        ]);

        $this->addForeignKey($value . '_item_key', $value, 'item_key', $item, 'key');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $item = \svsoft\yii\items\repositories\tables\TableItem::TABLE;
        $value = \svsoft\yii\items\repositories\tables\TableValue::TABLE;
        $type = \svsoft\yii\items\repositories\tables\TableItemType::TABLE;
        $field = \svsoft\yii\items\repositories\tables\TableField::TABLE;

        $this->dropTable($value);
        $this->dropTable($item);
        $this->dropTable($field);
        $this->dropTable($type);
    }
}
