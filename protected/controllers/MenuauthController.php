<?php

class MenuauthController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column1';
	protected $menuname = 'menuauth';

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
	  parent::actionCreate();
	  $model=new Menuauth;

	  if (Yii::app()->request->isAjaxRequest)
	  {
		  echo CJSON::encode(array(
			  'status'=>'success',
			  ));
		  Yii::app()->end();
	  }
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate()
	{
	  parent::actionUpdate();
      $id=$_POST['id'];
      $model=$this->loadModel($id[0]);
      if ($model != null)
      {
        if ($this->CheckDataLock($this->menuname, $id[0]) == false)
        {
          $this->InsertLock($this->menuname, $id[0]);
            echo CJSON::encode(array(
                'status'=>'success',
				'menuauthid'=>$model->menuauthid,
				'menuobject'=>$model->menuobject,
				'recordstatus'=>$model->recordstatus,
				));
            Yii::app()->end();
        }
      }
	}
	
	protected function gridData($data,$row)
  {     
    $model = Menuauth::model()->findByPk($data->menuauthid); 
    return $this->renderPartial('_view',array('model'=>$model),true); 
  }

   public function actionCancelWrite()
    {
      $this->DeleteLockCloseForm($this->menuname, $_POST['Menuauth'], $_POST['Menuauth']['menuauthid']);
    }

    public function actionWrite()
	{
	  parent::actionWrite();
	  if(isset($_POST['Menuauth']))
	  {
        $messages = $this->ValidateData(
                array(
				        array($_POST['Menuauth']['menuobject'],'emptymenuobject','emptystring'),
            )
        );
        if ($messages == '') {
          if ((int)$_POST['Menuauth']['menuauthid'] > 0)
          {
            $model=$this->loadModel($_POST['Menuauth']['menuauthid']);
			$this->olddata = $model->attributes;
			$this->useraction='update';
            $model->menuobject = $_POST['Menuauth']['menuobject'];
            $model->recordstatus = $_POST['Menuauth']['recordstatus'];
          }
          else
          {
            $model = new Menuauth();
            $model->attributes=$_POST['Menuauth'];
			$this->olddata = $model->attributes;
			$this->useraction='new';
          }
		  $this->newdata = $model->attributes;
          try
            {
              if($model->save())
              {
				$this->InsertTranslog();
                $this->DeleteLock($this->menuname, $_POST['Menuauth']['menuauthid']);
                $this->GetSMessage('sumansertsuccess');
              }
              else
              {
                $this->GetMessage($model->getErrors());
              }
            }
            catch (Exception $e)
            {
              $this->GetMessage($e->getMessage());
            }
          }
	  }
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete()
	{
		parent::actionDelete();
		  $model=$this->loadModel($_POST['id']);
		  $model->recordstatus=0;
		  $model->save();
		echo CJSON::encode(array(
                'status'=>'success',
                'div'=>'Data deleted'
				));
        Yii::app()->end();
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
	  parent::actionIndex();
		$model=new Menuauth('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Menuauth']))
			$model->attributes=$_GET['Menuauth'];
if (isset($_GET['pageSize']))
	  {
		Yii::app()->user->setState('pageSize',(int)$_GET['pageSize']);
		unset($_GET['pageSize']);  // would interfere with pager and repetitive page size change
	  }
		$this->render('index',array(
			'model'=>$model
		));
	}

public function actionUpload()
  {
      parent::actionUpload();
  }

  public function actionDownload()
	{
		parent::actionDownload();
		$sql = "select b.menuname,a.menuobject
				from menuauth a ";
		if ($_GET['id'] !== '0') {
				$sql = $sql . "where a.menuauthid = ".$_GET['id'];
		}
		$command=$this->connection->createCommand($sql);
		$dataReader=$command->queryAll();

		$this->pdf->title='Menu Authentication List';
		$this->pdf->AddPage('P');
		$this->pdf->setFont('Arial','B',12);

		// definisi font
		$this->pdf->setFont('Arial','B',8);

		$this->pdf->setaligns(array('C','C'));
		$this->pdf->setwidths(array(70,50));
		$this->pdf->Row(array('Menu Name','Menu Object'));
		$this->pdf->setaligns(array('L','L'));
		foreach($dataReader as $row1)
		{
		  $this->pdf->row(array($row1['menuobject']));
		}
		// me-render ke browser
		$this->pdf->Output();
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Menuauth::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='menuauth-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
