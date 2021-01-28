<?php
namespace app\modules\manage\behaviors;

use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;

class EditorBehavior extends AttributeBehavior
{
    public $createdById = 'created_by';
    public $updatedById = 'updated_by';
    public $value;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->createdById, $this->updatedById],
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->updatedById,
            ];
        }
    }

    /**
     * @inheritdoc
     */
    protected function getValue($event)
    {
        if ($this->value === null) {
            return \Yii::$app->user->id;
        }

        return parent::getValue($event);
    }
}
