<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
use League\OAuth2\Client\Provider\GenericProvider;
/**
 * Class OAuth2Provider
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class OAuth2Provider extends GenericProvider {
	/**
	 * Builds request options used for requesting an access token.
	 *
	 * @param  array $params
	 * @return array
	 */
	protected function getAccessTokenOptions(array $params)
	{
		$options = parent::getAccessTokenOptions($params);
		$options['headers']['Authorization'] = 'Basic ' . base64_encode($this->clientId.':'.$this->clientSecret);
		return $options;
	}

}