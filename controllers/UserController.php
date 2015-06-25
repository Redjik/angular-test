<?php

namespace app\controllers;

use app\models\User as UserModel;
use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\User;
use yii\web\UserEvent;


/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';

    public function actions()
    {
        return [];
    }

    public function actionCreate()
    {
        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass([
                'scenario' => $this->createScenario,
            ]);

        /** @var UserModel $model */
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $this->attachUserEvent();
        if ($model->save() && Yii::$app->user->login($model)) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(200);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    public function actionUpdate()
    {
        /** @var User $model */
        $model = Yii::$app->getUser()->getIdentity();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

    }

    public function actionIndex($username, $password)
    {
        /* @var $modelClass UserModel */
        $modelClass = $this->modelClass;
        $model = $modelClass::findOne(['username'=>$username]);
        $this->attachUserEvent();
        if (isset($model) && $model->validatePassword($password) && Yii::$app->user->login($model)) {
            return $model;
        } else {
            throw new NotFoundHttpException("Object not found: $username");
        }
    }

    protected function attachUserEvent()
    {
        Yii::$app->user->on(User::EVENT_AFTER_LOGIN,function(UserEvent $event)
            {
                /** @var UserModel $user */
                $user = $event->identity;
                $user->regenerateToken();
                Yii::$app->getResponse()->getHeaders()->set(Yii::$app->params['auth_header_name'], $user->token);
            }
        );
    }

}
