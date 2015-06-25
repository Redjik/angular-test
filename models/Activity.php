<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "activity".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $note
 * @property integer $duration
 * @property string $date
 *
 * @property User $user
 */
class Activity extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'note', 'duration', 'date'], 'required'],
            [['user_id', 'duration'], 'integer'],
            [['note'], 'string'],
            [['date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'note' => 'Note',
            'duration' => 'Duration',
            'date' => 'Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
