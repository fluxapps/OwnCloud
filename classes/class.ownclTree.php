<?php

/**
 * Class swdrTree
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclTree {

	/**
	 * @var ownclClient
	 */
	public $client;

	function __construct(ownclClient $client){
		$this->client = $client;
	}


	public function getChilds($id, $order){
		return $this->client->listFolder(str_replace('__', "/", $id));
	}

	function getRootNode(){
		$root = new ownclFolder();
		$root->setName('');
		$root->setPath('/');
		return $root;
	}


}