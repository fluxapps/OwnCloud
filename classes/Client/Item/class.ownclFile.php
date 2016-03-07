<?php
require_once(__DIR__ . '/class.ownclItem.php');

/**
 * Class ownclFile
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclFile extends ownclItem {

	/**
	 * @var array
	 */
	protected static $ms_formats = array(
		'doc',
		'docx',
		'dot',
		'dotx',
		'xls',
		'xlsx',
		'xlt',
		'xltx',
		'ppt',
		'pptx',

	);
	/**
	 * @var int
	 */
	protected $type = self::TYPE_FILE;
	/**
	 * @var int
	 */
	protected $size = 0;
	/**
	 * @var string
	 */
	protected $content_url = '';


	/**
	 * @param $web_url    String
	 * @param $properties array
	 */
	public function loadFromProperties($web_url, $properties, $parent_id) {
		parent::loadFromProperties($web_url, $properties, $parent_id);
		$this->setSize($properties["{DAV:}getcontentlength"]);
	}


	/**
	 * @param $web_url    String
	 * @param $properties array
	 */
	public function loadFromResponse($response, $path) {
		$this->setName(substr($path, strrpos($path, '/')));
		$this->setContentUrl($path);
	}


	/**
	 * @return bool
	 */
	public function isMsFormat() {
		return in_array($this->getSuffix(), self::$ms_formats);
	}


	/**
	 * @return null
	 */
	public function getMsURL() {
		if (!$this->isMsFormat()) {
			return NULL;
		}

		$re1 = '.*?';
		$re2 = '(\\{.*?\\})';

		if ($c = preg_match_all("/" . $re1 . $re2 . "/is", $this->getETag(), $matches)) {
			$cbraces1 = $matches[1][0];
			$strstr = strstr($this->getContentUrl(), '/_api', true) . '/_layouts/15/WopiFrame.aspx?sourcedoc=' . rawurlencode($cbraces1) . '&file='
				. $this->getName() . '&action=default';

			return $strstr;
		} else {
			return NULL;
		}
	}


	/**
	 * @return mixed
	 */
	public function getSuffix() {
		return pathinfo($this->getName(), PATHINFO_EXTENSION);
	}


	/**
	 * @return int
	 */
	public function getSize() {
		return $this->size;
	}


	/**
	 * @param int $size
	 */
	public function setSize($size) {
		$this->size = $size;
	}


	/**
	 * @return string
	 */
	public function getContentUrl() {
		return $this->content_url;
	}


	/**
	 * @param string $content_url
	 */
	public function setContentUrl($content_url) {
		$this->content_url = $content_url;
	}
}