<?php
namespace app\controllers;

use app\components\enums\Roles;
use app\models\Language;
use app\models\search\TranslationMessageSearch;
use app\models\translation\Message;
use app\models\translation\SourceMessage;
use app\models\translation\TranslationSearch;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class TranslationController
 * @package app\controllers
 */
class TranslationController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'denyCallback' => function($a, $b) {
                    return \Yii::$app->response->redirect(['/login']);
                },
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => [Roles::ADMIN],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all SourceMessage models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TranslationSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $language = Language::findOne($id);

        if (!is_null($language) && $language->load(\Yii::$app->request->post())) {
            $language->save();
        }

        return $this->redirect(\Yii::$app->request->referrer ?: Url::to(['/translation']));
    }

    public function actionCreate()
    {
        $language = new Language();

        if ($language->load(\Yii::$app->request->post())) {

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if (!$language->save()) {
                    throw new Exception('Save error.');
                }

                $sourceMessages = SourceMessage::find()->all();
                foreach($sourceMessages as $sourceMessage) {
                    $message = new Message([
                        'id' => $sourceMessage->id,
                        'language' => $language->lang,
                    ]);
                    $message->save();
                }

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
            }

            if ($language->id) {
                return $this->redirect(['/translation/edit', 'id' => $language->id]);
            }
        }

        return $this->redirect(['/translation']);
    }

    public function actionEdit($id)
    {
        $language = Language::findOne($id);

        if (is_null($language)) {
            throw new NotFoundHttpException();
        }

        return $this->render('edit', [
            'language' => $language,
            'translations' => (new TranslationMessageSearch())->search($language->lang),
        ]);
    }

    public function actionUpdateMessage()
    {
        $rq = \Yii::$app->request;
        $id = $rq->post('id');
        $ms = $rq->post('message');
        $lg = $rq->post('lang');

        $source = SourceMessage::findOne($id);
        if (is_null($source)) {
            \Yii::$app->response->statusCode = 404;
            return Json::encode(['isError' => true, 'message' => 'Requested source message not found.']);
        }

        $translation = $source->addTranslation($lg, $ms);
        if ($translation->hasErrors()) {
            \Yii::$app->response->statusCode = 422;
            $errorMessage = array_pop($translation->getFirstErrors());
            return Json::encode(['isError' => true, 'message' => 'Unable to save translation: ' . $errorMessage]);
        }

        return Json::encode(['isError' => false, 'translation' => $translation->toArray()]);
    }
}
