<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\News\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\Utility\ControllerTestUtility;
use \OCA\News\BusinessLayer\BusinessLayerException;
use \OCA\News\Db\Item;

require_once(__DIR__ . "/../../classloader.php");


class ItemApiControllerTest extends ControllerTestUtility {

	private $itemBusinessLayer;
	private $itemAPI;
	private $api;
	private $user;
	private $request;
	private $msg;

	protected function setUp() {
		$this->api = $this->getMockBuilder(
			'\OCA\News\Core\API')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMockBuilder(
			'\OCP\IRequest')
			->disableOriginalConstructor()
			->getMock();
		$this->itemBusinessLayer = $this->getMockBuilder(
			'\OCA\News\BusinessLayer\ItemBusinessLayer')
			->disableOriginalConstructor()
			->getMock();
		$this->user = 'tom';
		$this->itemAPI = new ItemApiController(
			$this->api,
			$this->request,
			$this->itemBusinessLayer
		);
		$this->msg = 'hi';
	}


	private function assertDefaultAnnotations($methodName){
		$annotations = array('NoAdminRequired', 'NoCSRFRequired', 'API');
		$this->assertAnnotations($this->itemAPI, $methodName, $annotations);
	}


	public function testIndexAnnotations(){
		$this->assertDefaultAnnotations('index');
	}


	public function testUpdatedAnnotations(){
		$this->assertDefaultAnnotations('updated');
	}


	public function testReadAllAnnotations(){
		$this->assertDefaultAnnotations('readAll');
	}


	public function testReadAnnotations(){
		$this->assertDefaultAnnotations('read');
	}


	public function testStarAnnotations(){
		$this->assertDefaultAnnotations('star');
	}


	public function testUnreadAnnotations(){
		$this->assertDefaultAnnotations('unread');
	}


	public function testUnstarAnnotations(){
		$this->assertDefaultAnnotations('unstar');
	}


	public function testReadMultipleAnnotations(){
		$this->assertDefaultAnnotations('readMultiple');
	}


	public function testStarMultipleAnnotations(){
		$this->assertDefaultAnnotations('starMultiple');
	}


	public function testUnreadMultipleAnnotations(){
		$this->assertDefaultAnnotations('unreadMultiple');
	}


	public function testUnstarMultipleAnnotations(){
		$this->assertDefaultAnnotations('unstarMultiple');
	}


	public function testIndex() {
		$items = array(
			new Item()
		);
		$request = $this->getRequest(array('params' => array(
			'batchSize' => 30,
			'offset' => 20,
			'type' => 1,
			'id' => 2,
			'getRead' => 'false'
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('findAll')
			->with(
				$this->equalTo(2),
				$this->equalTo(1),
				$this->equalTo(30),
				$this->equalTo(20),
				$this->equalTo(false),
				$this->equalTo($this->user)
			)
			->will($this->returnValue($items));

		$response = $this->itemAPI->index();

		$this->assertEquals(array(
			'items' => array($items[0]->toAPI())
		), $response->getData());
	}


	public function testIndexDefaultBatchSize() {
		$items = array(
			new Item()
		);
		$request = $this->getRequest(array('params' => array(
			'offset' => 20,
			'type' => 1,
			'id' => 2,
			'getRead' => 'false'
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('findAll')
			->with(
				$this->equalTo(2),
				$this->equalTo(1),
				$this->equalTo(20),
				$this->equalTo(20),
				$this->equalTo(false),
				$this->equalTo($this->user)
			)
			->will($this->returnValue($items));

		$response = $this->itemAPI->index();

		$this->assertEquals(array(
			'items' => array($items[0]->toAPI())
		), $response->getData());
	}


	public function testUpdated() {
		$items = array(
			new Item()
		);
		$request = $this->getRequest(array('params' => array(
			'lastModified' => 30,
			'type' => 1,
			'id' => 2,
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('findAllNew')
			->with(
				$this->equalTo(2),
				$this->equalTo(1),
				$this->equalTo(30),
				$this->equalTo(true),
				$this->equalTo($this->user)
			)
			->will($this->returnValue($items));

		$response = $this->itemAPI->updated();

		$this->assertEquals(array(
			'items' => array($items[0]->toAPI())
		), $response->getData());
	}


	public function testRead() {
		$request = $this->getRequest(array('urlParams' => array(
			'itemId' => 2
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('read')
			->with(
				$this->equalTo(2),
				$this->equalTo(true),
				$this->equalTo($this->user)
			);

		$response = $this->itemAPI->read();

		$this->assertEmpty($response->getData());
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


	public function testReadDoesNotExist() {
		$request = $this->getRequest(array('urlParams' => array(
			'itemId' => 2
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('read')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->itemAPI->read();

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testUnread() {
		$request = $this->getRequest(array('urlParams' => array(
			'itemId' => 2
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('read')
			->with(
				$this->equalTo(2),
				$this->equalTo(false),
				$this->equalTo($this->user)
			);

		$response = $this->itemAPI->unread();

		$this->assertEmpty($response->getData());
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


	public function testUnreadDoesNotExist() {
		$request = $this->getRequest(array('urlParams' => array(
			'itemId' => 2
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('read')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->itemAPI->unread();

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testStar() {
		$request = $this->getRequest(array('urlParams' => array(
			'feedId' => 2,
			'guidHash' => 'hash'
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('star')
			->with(
				$this->equalTo(2),
				$this->equalTo('hash'),
				$this->equalTo(true),
				$this->equalTo($this->user)
			);

		$response = $this->itemAPI->star();

		$data = $response->getData();
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


	public function testStarDoesNotExist() {
		$request = $this->getRequest(array('urlParams' => array(
			'feedId' => 2,
			'guidHash' => 'hash'
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('star')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->itemAPI->star();

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testUnstar() {
		$request = $this->getRequest(array('urlParams' => array(
			'feedId' => 2,
			'guidHash' => 'hash'
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('star')
			->with(
				$this->equalTo(2),
				$this->equalTo('hash'),
				$this->equalTo(false),
				$this->equalTo($this->user)
			);

		$response = $this->itemAPI->unstar();

		$this->assertEmpty($response->getData());
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


	public function testUnstarDoesNotExist() {
		$request = $this->getRequest(array('urlParams' => array(
			'feedId' => 2,
			'guidHash' => 'hash'
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('star')
			->will($this->throwException(new BusinessLayerException($this->msg)));

		$response = $this->itemAPI->unstar();

		$data = $response->getData();
		$this->assertEquals($this->msg, $data['message']);
		$this->assertEquals(Http::STATUS_NOT_FOUND, $response->getStatus());
	}


	public function testReadAll() {
		$request = $this->getRequest(array(
			'params' => array(
				'newestItemId' => 30,
			)
		));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->once())
			->method('readAll')
			->with(
				$this->equalTo(30),
				$this->equalTo($this->user));

		$response = $this->itemAPI->readAll();

		$this->assertEmpty($response->getData());
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}



	public function testReadMultiple() {
		$request = $this->getRequest(array('params' => array(
			'items' => array(2, 4)
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->at(0))
			->method('read')
			->with($this->equalTo(2),
				$this->equalTo(true),
				$this->equalTo($this->user));
		$this->itemBusinessLayer->expects($this->at(1))
			->method('read')
			->with($this->equalTo(4),
				$this->equalTo(true),
				$this->equalTo($this->user));
		$response = $this->itemAPI->readMultiple();
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


	public function testReadMultipleDoesntCareAboutException() {
		$request = $this->getRequest(array('params' => array(
			'items' => array(2, 4)
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->at(0))
			->method('read')
			->will($this->throwException(new BusinessLayerException('')));
		$this->itemBusinessLayer->expects($this->at(1))
			->method('read')
			->with($this->equalTo(4),
				$this->equalTo(true),
				$this->equalTo($this->user));
		$this->itemAPI->readMultiple();
	}


	public function testUnreadMultiple() {
		$request = $this->getRequest(array('params' => array(
			'items' => array(2, 4)
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->at(0))
			->method('read')
			->with($this->equalTo(2),
				$this->equalTo(false),
				$this->equalTo($this->user));
		$this->itemBusinessLayer->expects($this->at(1))
			->method('read')
			->with($this->equalTo(4),
				$this->equalTo(false),
				$this->equalTo($this->user));
		$response = $this->itemAPI->unreadMultiple();
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


	public function testStarMultiple() {
		$request = $this->getRequest(array('params' => array(
			'items' => array(
				array(
					'feedId' => 2,
					'guidHash' => 'a'
				),
				array(
					'feedId' => 4,
					'guidHash' => 'b'
				)
			)
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->at(0))
			->method('star')
			->with($this->equalTo(2),
				$this->equalTo('a'),
				$this->equalTo(true),
				$this->equalTo($this->user));
		$this->itemBusinessLayer->expects($this->at(1))
			->method('star')
			->with($this->equalTo(4),
				$this->equalTo('b'),
				$this->equalTo(true),
				$this->equalTo($this->user));
		$response = $this->itemAPI->starMultiple();
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


	public function testStarMultipleDoesntCareAboutException() {
		$request = $this->getRequest(array('params' => array(
			'items' => array(
				array(
					'feedId' => 2,
					'guidHash' => 'a'
				),
				array(
					'feedId' => 4,
					'guidHash' => 'b'
				)
			)
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->at(0))
			->method('star')
			->will($this->throwException(new BusinessLayerException('')));
		$this->itemBusinessLayer->expects($this->at(1))
			->method('star')
			->with($this->equalTo(4),
				$this->equalTo('b'),
				$this->equalTo(true),
				$this->equalTo($this->user));
		$this->itemAPI->starMultiple();
	}


	public function testUnstarMultiple() {
		$request = $this->getRequest(array('params' => array(
			'items' => array(
				array(
					'feedId' => 2,
					'guidHash' => 'a'
				),
				array(
					'feedId' => 4,
					'guidHash' => 'b'
				)
			)
		)));
		$this->itemAPI = new ItemApiController(
			$this->api,
			$request,
			$this->itemBusinessLayer
		);

		$this->api->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->user));
		$this->itemBusinessLayer->expects($this->at(0))
			->method('star')
			->with($this->equalTo(2),
				$this->equalTo('a'),
				$this->equalTo(false),
				$this->equalTo($this->user));
		$this->itemBusinessLayer->expects($this->at(1))
			->method('star')
			->with($this->equalTo(4),
				$this->equalTo('b'),
				$this->equalTo(false),
				$this->equalTo($this->user));
		$response = $this->itemAPI->unstarMultiple();
		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
	}


}
