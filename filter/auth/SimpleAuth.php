<?php
/**
 * @author Ivan Matveev <Redjiks@gmail.com>.
 */

namespace app\filter\auth;


use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\IdentityInterface;
use yii\web\Request;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use app\models\User as UserModel;
use yii\web\User;
use yii\web\UserEvent;

class SimpleAuth extends AuthMethod
{

    /**
     * Authenticates the current user.
     *
     * @param User     $user
     * @param Request  $request
     * @param Response $response
     *
     * @return IdentityInterface the authenticated user identity. If authentication information is not provided, null will be returned.
     * @throws UnauthorizedHttpException if authentication information is provided but is invalid.
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get(Yii::$app->params['auth_header_name']);

        if ($authHeader !== null) {

            $user->on(User::EVENT_AFTER_LOGIN,function(UserEvent $event)
                {
                    /** @var UserModel $user */
                    $user = $event->identity;
                    $user->regenerateToken();
                    Yii::$app->getResponse()->getHeaders()->set(Yii::$app->params['auth_header_name'], $user->token);
                }
            );
            /** @var UserModel $identity */
            $identity = $user->loginByAccessToken($authHeader, get_class($this));

            if ($identity === null) {
                $this->handleFailure($response);
            }

            return $identity;
        }

        return null;
    }


}