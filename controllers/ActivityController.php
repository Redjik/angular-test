<?php

namespace app\controllers;

use app\filter\auth\SimpleAuth;
use app\models\Activity;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;


/**
 * ActivityController implements the CRUD actions for Activity model.
 */
class ActivityController extends ActiveController
{
    public $modelClass = 'app\models\Activity';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'],$actions['index']);
        return $actions;
    }


    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => SimpleAuth::className(),
        ];
        return $behaviors;
    }

    public function actionIndex()
    {
        /** @var ActiveRecord $modelClass */
        $modelClass = $this->modelClass;
//        $provider = new ActiveDataProvider([
//                'query' => $modelClass::find(['user_id'=>Yii::$app->user->getId()]),
//            ]);

        return $modelClass::find()->where(['user_id'=>Yii::$app->user->getId()])->all();
    }

    public function actionCreate()
    {
        /* @var $model Activity */
        $model = new $this->modelClass([
                'scenario' => $this->createScenario,
            ]);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($userId = Yii::$app->user->getId()){
            $model->user_id = $userId;
        }else{
            throw new ForbiddenHttpException('You are not allowed to perform this action');
        }
        $model->id = null;
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * Checks the privilege of the current user.
     *
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws ForbiddenHttpException if the user does not have access
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if ((int)$model->user_id !== (int)Yii::$app->user->getId()){
            throw new ForbiddenHttpException('You are not allowed to perform this action');
        }
    }
}
