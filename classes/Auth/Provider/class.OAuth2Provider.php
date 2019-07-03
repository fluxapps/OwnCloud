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
	 * @param array $params
	 * @return \Psr\Http\Message\RequestInterface
	 */
	protected function getAccessTokenRequest(array $params)
	{
		$method  = $this->getAccessTokenMethod();
		$url     = $this->getAccessTokenUrl($params);
		$options = $this->optionProvider->getAccessTokenOptions($this->getAccessTokenMethod(), $params);
		$options['headers']['Authorization'] = 'Basic ' . base64_encode($this->clientId.':'.$this->clientSecret);

		return $this->getRequest($method, $url, $options);
	}

}