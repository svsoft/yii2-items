<?php
namespace  svsoft\yii\items\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `product`.
 */
class m190321_085600_add_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $field = \svsoft\yii\items\repositories\tables\TableField::TABLE;
        $this->addColumn($field, 'sort', $this->smallInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $field = \svsoft\yii\items\repositories\tables\TableField::TABLE;
        $this->dropColumn($field, 'sort');
    }
}
