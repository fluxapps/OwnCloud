<?php

/**
 * Class ownclTreeGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclTreeGUI extends ilTreeExplorerGUI
{

    /**
     * @var ownclTree
     */
    protected $tree;
    /**
     * @var ilLog
     */
    protected $log;


    public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd, ownclTree $tree)
    {
        global $tpl, $ilLog;
        parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $tree);
        $this->setSkipRootNode(false);
        $this->setPreloadChilds(false);
        $this->setAjax(true);

        // necessary from 5.4 to fix bug where only root node shows
        $this->setNodeOpen($this->getNodeId($this->getRootNode()));

        $this->log = $ilLog;
        $css
            = '.jstree a.clickable_node {
               color:black !important;
             }

             .jstree a:hover {
               color:#b2052e !important;
             }';
        $tpl->addInlineCss($css);

        // shows loading gif, which is hidden (hard-coded in tpl)
        $container_outer_id = "il_expl2_jstree_cont_out_" . $this->getId();
        $tpl->addOnLoadCode('$("#' . $container_outer_id . '").removeClass("ilNoDisplay");');
    }


    /**
     * Get node icon
     *
     * @param array $a_node node data
     *
     * @return string icon path
     */
    function getNodeIcon($a_node)
    {
        if ($a_node->getType() == ownclItem::TYPE_FILE) {
            $img = 'icon_dcl_file.svg';
        } else {
            $img = 'icon_dcl_fold.svg';
        }

        return ilUtil::getImagePath($img);
    }


    /**
     * Get node icon alt attribute
     *
     * @param mixed $a_node node object/array
     *
     * @return string image alt attribute
     */
    function getNodeIconAlt($a_node)
    {
        return '';
    }


    /**
     * @param mixed $node
     *
     * @return string
     */
    function getNodeContent($node)
    {
        $config = new ownclConfig();

        $node->getName() ? $name = $node->getName() : $name = $config->getServiceTitle();

        return htmlspecialchars($name);
    }


    function getNodeHref($node)
    {
        global $ilCtrl;
        $ilCtrl->setParameter($this->parent_obj, 'root_path', $this->urlencode($node->getFullPath()));

        return $ilCtrl->getLinkTarget($this->parent_obj, 'editSettings');
    }


    /**
     * urlencode without encoding slashes
     *
     * @param $str
     *
     * @return mixed
     */
    protected function urlencode($str)
    {
        return str_replace('%2F', '/', rawurlencode($str));
    }


    function isNodeClickable($node)
    {
        return ($node->getType() == ownclItem::TYPE_FOLDER);
    }


    /**
     * Get root node.
     *
     * Please note that the class does not make any requirements how
     * nodes are represented (array or object)
     *
     * @return ownclFolder root node object/array
     */
    function getRootNode()
    {
        return $this->tree->getRootNode();
    }


    /**
     * Get id of a node
     *
     * @param mixed $a_node node array or object
     *
     * @return string id of node
     */
    function getNodeId($a_node)
    {
        return str_replace("/", '__', $a_node->getFullPath());
    }
}
