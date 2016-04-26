<?php
namespace app\controllers;

use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\models\Community;

class CommunityController extends ActiveController
{
	public $modelClass = 'app\models\Community';
	public $serializer = [ 'class' => 'yii\rest\Serializer', 'collectionEnvelope' => 'items'];
	
	public function actionShow()
	{
		$request= \Yii::$app->request->get();
		$community = Community::find();
		
		if(isset($request['value'])){
			$community->select(['name', 'prefix', 'username'])
			->innerJoinWith('users')
			->andFilterWhere(['like', 'name', $request['value']])
			->asArray();	
		}else{
			$community->select(['name', 'prefix', 'username'])
			->innerJoinWith('users')
			->asArray();
		}

		$dataProvider = new ActiveDataProvider([
			'query' => $community,
			'pagination' => [
				'pageSize' => 20,
				'pageParam' => 'page',
			],
		]);
		
		return $dataProvider;
	}

	public function actionAddcomm()
    {
        // add Community controller
        if (!$post = \Yii::$app->getRequest()->getBodyParams()) {
            throw new \yii\web\HttpException(400, 'Дані не отримані');
        }
        $communityModel = new Community();
        if ($communityModel->findByCommunityName($post['community_name'])){
            throw new \yii\web\HttpException(400, 'Користувач з таким логіном уже існує');
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $communityModel = new Community();
            $communityModel->name = $post['community_name'];
            $communityModel->prefix = $post['community_num'];
            $communityModel->commissioner_id = 1;
            $communityModel->notes = $post['community_additions'];
            if (!$communityModel->save()){
                foreach($communityModel->errors as $key){
                    $errorMessage .= $key[0];
                }
                throw new \yii\web\HttpException(422,$errorMessage);
            }
            $transaction->commit();
            // $userModel = User::findUserById($post['user_id']);
            // $userModel->role_id = $post['parametr'];               // parametr = '0' or '1' 
            // $userModel->save();
            // Add validation for data here
    
            return 'true';
            
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new \yii\web\HttpException(422,$errorMessage . $error);
            return $errorMessage . $error;
        }
        exit('end');
    }
}