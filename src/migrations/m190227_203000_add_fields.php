<?php
namespace  svsoft\yii\items\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `product`.
 */
class m190227_203000_add_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $value = \svsoft\yii\items\repositories\tables\TableValue::TABLE;
        $this->addColumn($value, 'value_tiny_int', $this->tinyInteger());
        $this->dropColumn($value, 'value_timestamp');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $value = \svsoft\yii\items\repositories\tables\TableValue::TABLE;
        $this->dropColumn($value, 'value_tiny_int');
    }
}
