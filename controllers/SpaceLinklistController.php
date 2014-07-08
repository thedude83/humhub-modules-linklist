<?php

class SpaceLinklistController extends Controller
{
	public $subLayout = "application.modules_core.space.views.space._layout";
	
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
				'accessControl', // perform access control for CRUD operations
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
				array('allow', // allow authenticated user to perform 'create' and 'update' actions
						'users' => array('@'),
				),
				array('deny', // deny all users
						'users' => array('*'),
				),
		);
	}
	
	/**
	 * Add mix-ins to this model
	 *
	 * @return type
	 */
	public function behaviors() {
		return array(
			'SpaceControllerBehavior' => array(
					'class' => 'application.modules_core.space.behaviors.SpaceControllerBehavior',
			),
		);
	}
	
	private function isEditable($space) {
		return $space->isAdmin(Yii::app()->user->id) || $space->isOwner(Yii::app()->user->id);
	}
	
	public function actionShowLinklist() {
		
		$container = Yii::app()->getController()->getSpace();
		$categoryBuffer = Category::model()->contentContainer($container)->findAll(array('order' => 'sort_order ASC'));
		
		$categories = array();
		$links = array();
		$editable = $this->isEditable($container);
			
		foreach($categoryBuffer as $category) {
			$linkBuffer = Link::model()->findAllByAttributes(array('category_id'=>$category->id), array('order' => 'sort_order ASC'));
			// categories are only displayed if they contain at least one link or the user may edit them.
			if(!empty($linkBuffer) || $editable) {
				$categories[] = $category;
				$links[$category->id] = $linkBuffer;
			}
		}
		
		$this->render('showLinklist', array(
			'sguid' => $container->guid,
			'categories' => $categories,
			'links' => $links,
			'editable' => $editable,
		));
	}
	
	public function actionEditCategory() {
		
		$container = $this->getSpace();
		if(!$this->isEditable($container)) {
			throw new CHttpException(404, Yii::t('LinklistModule.base', 'Linklist is not editable!'));
		}
	
		$category_id = (int) Yii::app()->request->getQuery('category_id');
		$category = Category::model()->findByAttributes(array('id' => $category_id));
		$isCreated = false;
	
		if ($category == null) {
			$category = new Category;
			$isCreated = true;
		}	
	
		if (isset($_POST['Category'])) {
			$_POST = Yii::app()->input->stripClean($_POST);
	
			$category->attributes = $_POST['Category'];
			$category->content->container = $container;
			if ($category->validate()) {
				$category->save();
				$this->redirect(Yii::app()->createUrl('linklist/spacelinklist/showlinklist', array (
					'sguid' => $container->guid,
					)
				));
			}
		}
	
		$this->render('editCategory', array(
			'sguid' => $container->guid,
			'category' => $category,
			'isCreated' => $isCreated,
		));
	}
	
	public function actionDeleteCategory() {
	
		$container = $this->getSpace();
		if(!$this->isEditable($container)) {
			throw new CHttpException(404, Yii::t('LinklistModule.base', 'Linklist is not editable!'));
		}
	
		$category_id = (int) Yii::app()->request->getQuery('category_id');
		$category = Category::model()->findByAttributes(array('id' => $category_id));
	
		if ($category == null) {
			throw new CHttpException(404, Yii::t('LinklistModule.base', 'Requested category could not be found.'));
		}
	
		$category->delete();
	
		$this->redirect(Yii::app()->createUrl('linklist/spacelinklist/showlinklist', array (
			'sguid' => $container->guid,
			)
		));
	}
	
	public function actionEditLink() {
		
		$container = $this->getSpace();
		if(!$this->isEditable($container)) {
			throw new CHttpException(404, Yii::t('LinklistModule.base', 'Linklist is not editable!'));
		}
		
		$link_id = (int) Yii::app()->request->getQuery('link_id');
		$category_id = (int) Yii::app()->request->getQuery('category_id');
		$link = Link::model()->findByAttributes(array('id' => $link_id));
		$isCreated = false;
		
		if ($link == null) {
			$link = new Link();
			$link->category_id = $category_id;
			$isCreated = true;
		}
		
		if (isset($_POST['Link'])) {
			$_POST = Yii::app()->input->stripClean($_POST);
		
			$link->attributes = $_POST['Link'];
			$link->content->container = $container;
			if ($link->validate()) {
				$link->save();
				$this->redirect(Yii::app()->createUrl('linklist/spacelinklist/showlinklist', array (
					'sguid' => $container->guid,
					)
				));
			}
		}
		
		$this->render('editLink', array(
			'sguid' => $container->guid,
			'link' => $link,
			'isCreated' => $isCreated,
		));
	}
	
	public function actionDeleteLink() {
	
		$container = $this->getSpace();
		if(!$this->isEditable($container)) {
			throw new CHttpException(404, Yii::t('LinklistModule.base', 'Linklist is not editable!'));
		}
	
		$link_id = (int) Yii::app()->request->getQuery('link_id');
		$link = Link::model()->findByAttributes(array('id' => $link_id));
	
		if ($link == null) {
			throw new CHttpException(404, Yii::t('LinklistModule.base', 'Requested link could not be found.'));
		}
	
		$link->delete();
	
		$this->redirect(Yii::app()->createUrl('linklist/spacelinklist/showlinklist', array (
			'sguid' => $container->guid,
			)
		));
	}
}

?>