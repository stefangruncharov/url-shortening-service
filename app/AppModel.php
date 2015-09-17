<?php

/**
 * Class provides access to database tables in object manner
 * That also called ORM (Object-relational mapping)
 */
abstract class AppModel {

	/**
	 * Method must return name of table
	 * @return string
	 */
	abstract public static function getTableName();

	/**
	 * Method must return the name of the column, which is the primary key of the table.
	 *    TODO Or array of names if primary key is complex
	 * @return string|string[]
	 */
	abstract public static function getPrimaryKey();

	/**
	 * Method must return names of all columns of the table
	 * @return string[]
	 */
	abstract public static function getColumnNames();

	/**
	 * Returns one instance of current class found by primary key $pk
	 * @param mixed $pk
	 * @return AppModel|false
	 */
	public static function findOneByPk($pk) {
		$columns = static::getColumnNames();
		$columnsCommaList = implode(',', $columns);
		$tableName = static::getTableName();
		$primaryKeyColumn = static::getPrimaryKey();

		$sql = "SELECT {$columnsCommaList} FROM {$tableName} WHERE {$primaryKeyColumn} = ?";
		$row = App::db()->getRow($sql, array($pk));

		if (is_array($row)) {
			$model = new static;
			$model->populate($row);
			return $model;
		}

		return false;
	}

	/**
	 * Set object propertios with values from $values
	 * @param array $values
	 */
	protected function populate($values) {
		$columns = static::getColumnNames();
		foreach($columns as $column) {
			$this->{$column} = $values[$column];
		}
	}

	/**
	 * Save object into database
	 */
	public function save() {
		$columns = static::getColumnNames();
		$columnsCommaList = implode(',', $columns);
		$tableName = static::getTableName();
		$primaryKeyColumn = static::getPrimaryKey();

		$valuesPlaceholders = implode(',', array_fill(0, count($columns), '?')); // -> ?, ?, ?
		$values = array();
		foreach($columns as $column) {
			$values[] = $this->{$column};
		}

		App::db()->execute("LOCK TABLES {$tableName} WRITE"); // "transaction lock" for MyISAM tables

		// TODO Update object if already saved
		
		$sql = "INSERT INTO {$tableName} ({$columnsCommaList}) VALUES ({$valuesPlaceholders})";
		$newPk = App::db()->execute($sql, $values);
		$this->{$primaryKeyColumn} = $newPk;

		App::db()->execute("UNLOCK TABLES");
		return true;
	}
}
