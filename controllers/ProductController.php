<?php

class ProductController extends Controller
{
    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/main';

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow all users to perform 'index' and 'view' actions
                'actions' => array('index', 'view'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('create', 'update'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'delete'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
        $this->render($this->getCatalogueModule()->viewProduct, array(
            'model' => $this->loadModel($id),
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $modelProduct = $this->getModelClass();
        $modelProductInfo = $this->getCatalogueModule()->productInfoModelClass;
        $model = new $modelProduct;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);
        if (isset($_POST[$this->getModelClass()])) {

            $model->attributes = $_POST[$this->getModelClass()];

            $model->info = new $modelProductInfo;
            $model->info->attributes = $_POST[$this->getModelClass()]['info'];
            $model->info->save();

            if ($model->save()) {
                $this->redirect(array($this->getCatalogueModule()->actionProductView, 'id' => $model->id));
            }
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    /**
     * @property CatalogueProductInfo $info
     */
    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);


        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST[$this->getModelClass()])) {
            $model->attributes = $_POST[$this->getModelClass()];
            $model->info->attributes = $_POST[$this->getModelClass()]['info'];
            $model->info->save();

            if ($model->save()) {
                $this->setPropertyValues($model);
                $this->redirect(array($this->getCatalogueModule()->actionProductView, 'id' => $model->id));
            }
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    private function setPropertyValues(CatalogueProduct &$model) {
        /* @var $props CatalogueProperty[] */
        /* @var $vals CataloguePropertyValue[] */
        $props = array();

        foreach($model->categories as $c) {
            if(is_numeric($c)) $c = CatalogueCategory::model()->findByPk($c);
            foreach($c->collectProperties() as $p) {
                $props[$p->id] = $p;
            }
        }
        $vals = array();
        foreach($model->propertiesValues as $v) {
            $vals[$v->property_id] = $v;
        }
        $currentVals = isset($_POST["propValue"]) ? $_POST["propValue"] : array();

        foreach($props as $id=>$prop) {
            /* @var $value CataloguePropertyValue */
            $value = isset($vals[$id]) ? $vals[$id] : null;
            if($value) unset($vals[$id]);

            // It's removed
            if(!isset($currentVals[$id]) || !$currentVals[$id]) {
                if($value) {
                    $value->delete();
                }
                continue;
            }

            // It isn't present
            if(!$value) {
                $value = new CataloguePropertyValue();
                $value->product_id = $model->id;
                $value->property_id = $id;
            }
            // It's updated
            if($value->value != $currentVals[$id]) {
                $value->value = $currentVals[$id];
                $value->save();
            }
        }

        foreach($vals as $v) {
            $v->delete();
        }
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        $this->loadModel($id)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        $dataProvider = new CActiveDataProvider($this->getCatalogueModule()->productModelClass);
        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {   $modelProduct = $this->getModelClass();
        $model = new $modelProduct('search');
        $model->unsetAttributes(); // clear any default values
        if (isset($_GET[$this->getModelClass()]))
            $model->attributes = $_GET[$this->getModelClass()];

        $this->render('admin', array(
            'model' => $model,
        ));
    }

    /**
     * @return CatalogueModule
     */
    private function getCatalogueModule()
    {
        return Yii::app()->getModule("catalogue");
    }

    private function getModelClass()
    {
        return $this->getCatalogueModule()->productModelClass;
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        if (is_numeric($id)) {
            $model = CatalogueProduct::model()->findByPk($id);
        } else {
            $model = CatalogueProduct::model()->findByPath($id);
        }
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'product-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
