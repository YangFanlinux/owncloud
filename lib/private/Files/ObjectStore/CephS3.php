<?php
/**
 * @author Fan Yang <yangfan-400@163.com>
 *
 * @copyright Copyright (c) 2016, Neunn, Inc.
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

use Guzzle\Http\Exception\ClientErrorResponseException;
use OCP\Files\ObjectStore\IObjectStore;

set_include_path(get_include_path() . PATH_SEPARATOR .
	\OC_App::getAppPath('files_external') . '/3rdparty/aws-sdk-php');
require 'aws-autoloader.php';
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;


class CephS3 implements IObjectStore {
	/**
	 * @var \Aws\S3\S3Client
	 */
	private $client;
	/**
	 * @var array
	 */
	private $params;
	/**
	 * @var array
	 */
	private $bucket;
	

	public function __construct($params) {
		if (empty($params['key']) || empty($params['secret']) || empty($params['endpoint'])) {
			throw new \Exception("Access Key, Secret an endpoint have to be configured.");
		}
		
		if (!isset($params['bucket'])) {
			$params['bucket'] = 'owncloud';
		}
		if (!isset($params['autocreate'])) {
			$params['autocreate'] = false;
		}
		$this->bucket = $params['bucket'];
		$this->params = $params;
	}

	protected function getClient() {
		if ($this->client) {
			return;
		}
		$this->client = S3Client::factory(array(
			'key' => $this->params['key'],
			'secret' => $this->params['secret'],
			'base_url' => $this->params['endpoint'],
			'region' => $this->params['region'],
			S3Client::COMMAND_PARAMS => [
				'PathStyle' => $this->params['PathStyle'],
			],
		));

		if (!$this->client->isValidBucketName($this->bucket)) {
			error_log($this->bucket);
			throw new \Exception("The configured bucket name is invalid.");
		}

		if (!$this->client->doesBucketExist($this->bucket)) {
			if (isset($this->params['autocreate']) && $this->params['autocreate'] === true) {
				try {
					$this->client->createBucket(array(
						'Bucket' => $this->bucket
					));
					$this->client->waitUntilBucketExists(array(
						'Bucket' => $this->bucket,
						'waiter.interval' => 1,
						'waiter.max_attempts' => 15
					));
				} catch (S3Exception $e) {
					throw new \Exception('Creation of bucket failed. '.$e->getMessage());
				}
                        } 
		}
	}

	/**
	 * @return string the bucket name where objects are stored
	 */
	public function getStorageId() {
		return $this->params['bucket'];
	}

	/**
	 * @param string $urn the unified resource name used to identify the object
	 * @param resource $stream stream with the data to write
	 * @throws Exception from Amazone s3 lib when something goes wrong
	 */
	public function writeObject($urn, $stream) {
		$this->getClient();	
		$this->client->putObject(array(
			'Bucket' => $this->bucket,
			'Key'    => $urn,
			'Body'   => $stream));
	
	}

	/**
	 * @param string $urn the unified resource name used to identify the object
	 * @return resource stream with the read data
	 * @throws Exception from Amazone s3 lib when something goes wrong
	 */
	public function readObject($urn) {
		$this->getClient();
		$object = $this->client->getObject(array(
						'Bucket' => $this->bucket,
						'Key' => $urn));

		/*The contents of the object are stored in the Body parameter of the model object
		  Other parameters are stored in model including ContentType, ContentLength, VersionId, ETag, etc...
		  The Body parameter stores a reference to a Guzzle\Http\EntityBody object. The SDK will store the data in a temporary PHP stream by default. This will work for most use-cases and will automatically protect your application from attempting to download extremely large files into memory.
		  The 'Body' value of the result is an EntityBody object
	      echo get_class($result['Body']) . "\n";
          >Guzzle\Http\EntityBody
		*/
		
		// we need to keep a reference to objectContent or
		// the stream will be closed before we can do anything with it
		/** @var $objectContent \Guzzle\Http\EntityBody * */
		// move fp to beginner
		$object['Body']->rewind();

		$stream = $object['Body']->getStream();
		// save the object content in the context of the stream to prevent it being gc'd until the stream is closed
		stream_context_set_option($stream, 'cephS3','content', $object['Body']);

		return $stream;
	}

	/**
	 * @param string $urn Unified Resource Name
	 * @return void
	 * @throws Exception from Amazone s3 lib when something goes wrong
	 */
	public function deleteObject($urn) {
		$this->getClient();
		$this->client->deleteObject(array(
			'Bucket' => $this->bucket,
			'Key' => $urn
		));
	}
	public function deleteBucket($recursive = false) {
		$this->client->deleteBucket(array('Bucket' => $this->bucket));	
	}
}
