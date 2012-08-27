<?php

/**
 * This is the model base class for the table "{{catalogue_property}}".
 * DO NOT MODIFY THIS FILE! It is automatically generated by giix.
 * If any changes are necessary, you must set or override the required
 * property or method in class "CatalogueProperty".
 *
 * Columns in table "{{catalogue_property}}" available as properties of the model,
 * followed by relations of table "{{catalogue_property}}" available as properties of the model.
 *
 * @property integer $id
 * @property string $title
 *
 * @property mixed $tblCatalogueCategories
 * @property CataloguePropertyValue[] $cataloguePropertyValues
 */
abstract class BaseCatalogueProperty extends GxActiveRecord {

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return '{{catalogue_property}}';
	}

	public static function label($n = 1) {
		return Yii::t('app', 'CatalogueProperty|CatalogueProperties', $n);
	}

	public static function representingColumn() {
		return 'title';
	}

	public function rules() {
		return array(
			array('title', 'required'),
			array('title', 'length', 'max'=>255),
			array('id, title', 'safe', 'on'=>'search'),
		);
	}

	public function relations() {
		return array(
			'tblCatalogueCategories' => array(self::MANY_MANY, 'CatalogueCategory', '{{catalogue_property_to_category}}(property_id, category_id)'),
			'cataloguePropertyValues' => array(self::HAS_MANY, 'CataloguePropertyValue', 'property_id'),
		);
	}

	public function pivotModels() {
		return array(
			'tblCatalogueCategories' => 'CataloguePropertyToCategory',
		);
	}

	public function attributeLabels() {
		return array(
			'id' => Yii::t('app', 'ID'),
			'title' => Yii::t('app', 'Title'),
			'tblCatalogueCategories' => null,
			'cataloguePropertyValues' => null,
		);
	}

	public function search() {
		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id);
		$criteria->compare('title', $this->title, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
}