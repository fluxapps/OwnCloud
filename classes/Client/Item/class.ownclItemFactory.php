<?php
require_once(__DIR__ . '/class.ownclFolder.php');
require_once(__DIR__ . '/class.ownclFile.php');
require_once(__DIR__ . '/class.ownclItemCache.php');
require_once('./Modules/Cloud/exceptions/class.ilCloudException.php');

/**
 * Class ownclItemFactory
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclItemFactory {

	/**
	 * @param array $response
	 *
	 * @return ownclFolder[]|ownclFile[]
	 */
	public static function getInstancesFromResponse($response) {
		$return = array();
		if (count($response) == 0) {
			return $return;
		}
		$parent = array_shift($response);
		$parent_id = $parent['{http://owncloud.org/ns}id'];
		foreach ($response as $web_url => $props) {
			if (!$props["{DAV:}getcontenttype"]) {//is folder
				$exid_item = new ownclFolder();
				$exid_item->loadFromProperties($web_url, $props, $parent_id);
				ownclItemCache::store($exid_item);
				$return[] = $exid_item;
			} else { // is file
				$exid_item = new ownclFile();
				$exid_item->loadFromProperties($web_url, $props, $parent_id);
				ownclItemCache::store($exid_item);
				$return[] = $exid_item;
			}
		}

		return $return;
	}
}
