<?php

/**
 * Class ownclItemCache
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclItemCache {

	const ITEM_CACHE = 'owncl_item_cache';
	/**
	 * @var array
	 */
	protected static $instances = array();


	/**
	 * @param ownclItem $ownclItem
	 */
	public static function store(ownclItem $ownclItem) {
		$_SESSION[self::ITEM_CACHE][$ownclItem->getId()] = serialize($ownclItem);
	}


	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public static function exists($id) {
		return (unserialize($_SESSION[self::ITEM_CACHE][$id]) instanceof ownclItem);
	}


	/**
	 * @param $id
	 *
	 * @return ownclItem
	 */
	public static function get($id) {
		if (self::exists($id)) {
			return unserialize($_SESSION[self::ITEM_CACHE][$id]);
		}

		return NULL;
	}
}