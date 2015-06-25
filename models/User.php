<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\base\Security;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $token
 * @property string $create_date
 * @property string $update_date
 *
 * @property Activity[] $activities
 */
class User extends ActiveRecord implements IdentityInterface
{
    protected $pass;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    public function fields()
    {
        return [
            'id','username'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password_hash'], 'required'],
            [['create_date', 'update_date'], 'safe'],
            [['username'], 'string', 'max' => 64],
            [['token'], 'string', 'max' => 32],
            [['password'], 'string', 'max' => 20, 'min'=>6],
            [['password_hash'], 'string', 'max' => 255],
            [['username','token'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password_hash' => 'Password Hash',
            'token' => 'Token',
            'create_date' => 'Create Date',
            'update_date' => 'Update Date',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivities()
    {
        return $this->hasMany(Activity::className(), ['user_id' => 'id']);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = $this->getSecurity()->generatePasswordHash($password);
        $this->pass = $password;
    }


    public function getPassword()
    {
        return $this->pass;
    }

    /**
     * @return Security
     */
    public function getSecurity()
    {
        return Yii::$app->security;
    }

    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->getSecurity()->validatePassword($password, $this->password_hash);
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param string|integer $id the ID to be looked for
     *
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     *
     * @param mixed $token the token to be looked for
     * @param mixed $type  the type of the token. The value of this parameter depends on the implementation.
     *                     For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     *
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['token'=>$token]);
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     *
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Regenearate token
     */
    public function regenerateToken()
    {
        $this->token = $this->getSecurity()->generateRandomString();
        if (!$this->save(true,['token'])){
            $this->regenerateToken();
        }
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     *
     * @throws NotSupportedException
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        throw new NotSupportedException('No auto login implemented');
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     *
     * @param string $authKey the given auth key
     *
     * @throws NotSupportedException
     * @return boolean whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        throw new NotSupportedException('No auto login implemented');
}}
