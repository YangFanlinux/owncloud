<?php
/**
 * @author Roeland Jago Douma <rullzer@owncloud.com>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
t
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OC\Files\ObjectStore;

use OCP\IUser;

/**
 * Class Mapper
 *
 * @package OC\Files\ObjectStore
 *
 * Map a user to a bucket.
 */
class Mapper {
	/** @var IUser */
	private $user;
        
        /**
	 * @var array
	 */
	private $params; 
	/**
	 * @var value
	 */
	private $hashlength; 
	/**
	 * @var value
	 */
	private $prefix; 
	


	
	/**
	 * Mapper constructor.
	 *
	 * @param IUser $user
	 */
	public function __construct(IUser $user,$params) {
		$this->user = $user;
                if (!isset($params['prefix'])) {
			$params['prefix'] = 'NeunnOwnCloud_';
		}
		
                if (!isset($params['hashlength'])) {
			$params['hashlength'] = 3;
		}
		$this->prefix = $params['prefix'];
		$this->hashlength = $params['hashlength'];
	}

	/**
	 * @return string
	 */
	public function getBucket() {
		$hash = md5($this->user->getUID());
		$retvalue = substr($hash, 0, $this->hashlength);
		return $this->prefix.$retvalue;
	}
}
