<?php
include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerBaseGUI.php");

/**
 * Class ownclTreeGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclTreeGUI extends ilExplorerBaseGUI {

	/**
	 * @var ownclTree
	 */
	protected $tree;


	public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd, ownclTree $tree) {
		global $tpl;
		parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd);
		$this->tree = $tree;
		$css = '.jstree a.clickable_node {
               color:black !important;
             }

             .jstree a:hover {
               color:#b2052e !important;
             }';
		$tpl->addInlineCss($css);
	}


	/**
	 * @param mixed $node
	 *
	 * @return string
	 */
	function getNodeContent($node) {
		if ($node->getType() == ownclItem::TYPE_FILE) {
			$img = 'icon_dcl_file.svg';
		} else {
			$img = 'icon_dcl_fold.svg';
		}
		$node->getName() ? $name = $node->getName() : $name = 'OwnCloud';
		if ($this->isNodeClickable($node)) {
			$name = '<a class="clickable_node" href="' . $this->getNodeHref($node) . '">' . $name . '</>';
		}

		return ilUtil::img(ilUtil::getImagePath($img)) . " " . $name;
	}


	function getNodeHref($node) {
		global $ilCtrl;
		if ($node->getType() == ownclItem::TYPE_FILE) {
			return '';
		}
		$ilCtrl->setParameterByClass($this->parent_obj, 'root_path', $node->getFullPath());

		return $ilCtrl->getLinkTargetByClass($this->parent_obj, $this->parent_cmd);
	}


	function isNodeClickable($node) {
		return ($node->getType() == ownclItem::TYPE_FOLDER);
	}


	/**
	 * Get root node.
	 *
	 * Please note that the class does not make any requirements how
	 * nodes are represented (array or object)
	 *
	 * @return mixed root node object/array
	 */
	function getRootNode() {
		return $this->tree->getRootNode();
	}


	/**
	 * Get childs of node
	 *
	 * @param string $a_parent_id parent node id
	 *
	 * @return array childs
	 */
	function getChildsOfNode($a_parent_node_id) {
		$node = $this->tree->getNode($a_parent_node_id);
		if ($node->getType() == ownclItem::TYPE_FILE) {
			return array();
		}
		$child_ids = $node->getChilds();
		$childs = array();
		foreach ($child_ids as $id) {
			$childs[] = $this->tree->getNode($id);
		}

		return $childs;
	}


	/**
	 * Get id of a node
	 *
	 * @param mixed $a_node node array or object
	 *
	 * @return string id of node
	 */
	function getNodeId($a_node) {
		return $a_node->getId();
	}
}