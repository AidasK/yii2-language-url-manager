<?php
/**
 * MainTest.php
 * @author Revin Roman http://phptime.ru
 */

namespace unit\url;

use metalguardian\language\UrlManager;

/**
 * Class MainTest
 * @package rmrevin\yii\fontawesome\tests\unit\fontawesome
 */
class MainTest extends \unit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $config = $this->loadConfig();
        $this->mockWebApplication($config);
    }

    public function testLanguagesNotSet()
    {
        $this->setExpectedException('\yii\base\InvalidConfigException', 'UrlManager::languages have to contains at least 1 item.');
        $urlManager = new UrlManager();
    }

    public function testLanguagesEmpty()
    {
        $this->setExpectedException('\yii\base\InvalidConfigException', 'UrlManager::languages have to contains at least 1 item.');
        $urlManager = new UrlManager([
            'languages' => [],
        ]);
    }

    public function testLanguagesDoNotContainDefaultLanguage()
    {
        $this->setExpectedException('\yii\base\InvalidConfigException', 'UrlManager::defaultLanguage have to be exist in UrlManager::languages.');
        $urlManager = new UrlManager([
            'languages' => ['ru'],
        ]);
    }

    public function testEnablePrettyUrlSetTrue()
    {
        $this->setExpectedException('\yii\base\InvalidConfigException', 'UrlManager::enablePrettyUrl need to be true for using language url manager.');
        $urlManager = new UrlManager([
            'languages' => ['en'],
            'enablePrettyUrl' => false,
        ]);
    }

    public function testLanguageClosure()
    {
        $urlManager = new UrlManager([
            'languages' => function () {
                return ['en', 'ua' => 'uk'];
            },
            'showDefault' => true,
        ]);
    }

    public function testDefaultLanguageClosure()
    {
        $urlManager = new UrlManager([
            'languages' => ['en'],
            'defaultLanguage' => function () {
                return 'en';
            },
        ]);
    }

    /** Yii2 tests url manager - updated */

    public function testCreateUrl()
    {
        // default setting with '/' as base url
        $manager = new UrlManager([
            'baseUrl' => '/',
            'scriptUrl' => '',
            'cache' => 'cache',
            'languages' => ['en'],
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/post/view?id=1&title=sample+post', $url);

        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'baseUrl' => '/test/',
            'scriptUrl' => '/test',
            'cache' => 'realCache',
            'languages' => ['en'],
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/test/post/view?id=1&title=sample+post', $url);

        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'baseUrl' => '/test',
            'scriptUrl' => '/test/index.php',
            'cache' => null,
            'languages' => ['en'],
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/test/index.php/post/view?id=1&title=sample+post', $url);

        // todo: test showScriptName

        // pretty URL with rules
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                [
                    'pattern' => 'post/<id>/<title>',
                    'route' => 'post/view',
                ],
            ],
            'baseUrl' => '/',
            'scriptUrl' => '',
            'languages' => ['en'],
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/post/1/sample+post', $url);
        $url = $manager->createUrl(['post/index', 'page' => 1]);
        $this->assertEquals('/post/index?page=1', $url);
        // rules with defaultAction
        $url = $manager->createUrl(['/post', 'page' => 1]);
        $this->assertEquals('/post?page=1', $url);

        // pretty URL with rules and suffix
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                [
                    'pattern' => 'post/<id>/<title>',
                    'route' => 'post/view',
                ],
            ],
            'baseUrl' => '/',
            'scriptUrl' => '',
            'suffix' => '.html',
            'languages' => ['en'],
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('/post/1/sample+post.html', $url);
        $url = $manager->createUrl(['post/index', 'page' => 1]);
        $this->assertEquals('/post/index.html?page=1', $url);

        // pretty URL with rules that have host info
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                [
                    'pattern' => 'post/<id>/<title>',
                    'route' => 'post/view',
                    'host' => 'http://<lang:en|fr>.example.com',
                ],
            ],
            'baseUrl' => '/test',
            'scriptUrl' => '/test',
            'languages' => ['en'],
        ]);
        $url = $manager->createUrl(['post/view', 'id' => 1, 'title' => 'sample post', 'lang' => 'en']);
        $this->assertEquals('http://en.example.com/test/post/1/sample+post', $url);
        $url = $manager->createUrl(['post/index', 'page' => 1]);
        $this->assertEquals('/test/post/index?page=1', $url);
    }
    /**
     * https://github.com/yiisoft/yii2/issues/6717
     */
    public function _testCreateUrlWithEmptyPattern()
    {
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                '' => 'front/site/index',
            ],
            'baseUrl' => '/',
            'scriptUrl' => '',
        ]);
        $url = $manager->createUrl(['front/site/index']);
        $this->assertEquals('/', $url);
        $url = $manager->createUrl(['/front/site/index']);
        $this->assertEquals('/', $url);
        $url = $manager->createUrl(['front/site/index', 'page' => 1]);
        $this->assertEquals('/?page=1', $url);
        $url = $manager->createUrl(['/front/site/index', 'page' => 1]);
        $this->assertEquals('/?page=1', $url);
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                '' => '/front/site/index',
            ],
            'baseUrl' => '/',
            'scriptUrl' => '',
        ]);
        $url = $manager->createUrl(['front/site/index']);
        $this->assertEquals('/', $url);
        $url = $manager->createUrl(['/front/site/index']);
        $this->assertEquals('/', $url);
        $url = $manager->createUrl(['front/site/index', 'page' => 1]);
        $this->assertEquals('/?page=1', $url);
        $url = $manager->createUrl(['/front/site/index', 'page' => 1]);
        $this->assertEquals('/?page=1', $url);
    }

    public function _testCreateAbsoluteUrl()
    {
        $manager = new UrlManager([
            'baseUrl' => '/',
            'scriptUrl' => '',
            'hostInfo' => 'http://www.example.com',
            'cache' => null,
        ]);
        $url = $manager->createAbsoluteUrl(['post/view', 'id' => 1, 'title' => 'sample post']);
        $this->assertEquals('http://www.example.com?r=post%2Fview&id=1&title=sample+post', $url);
        $url = $manager->createAbsoluteUrl(['post/view', 'id' => 1, 'title' => 'sample post'], 'https');
        $this->assertEquals('https://www.example.com?r=post%2Fview&id=1&title=sample+post', $url);
        $manager->hostInfo = 'https://www.example.com';
        $url = $manager->createAbsoluteUrl(['post/view', 'id' => 1, 'title' => 'sample post'], 'http');
        $this->assertEquals('http://www.example.com?r=post%2Fview&id=1&title=sample+post', $url);
    }

    public function _testParseRequest()
    {
        $manager = new UrlManager(['cache' => null]);
        $request = new Request;
        // default setting without 'r' param
        unset($_GET['r']);
        $result = $manager->parseRequest($request);
        $this->assertEquals(['', []], $result);
        // default setting with 'r' param
        $_GET['r'] = 'site/index';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['site/index', []], $result);
        // default setting with 'r' param as an array
        $_GET['r'] = ['site/index'];
        $result = $manager->parseRequest($request);
        $this->assertEquals(['', []], $result);
        // pretty URL without rules
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
        ]);
        // empty pathinfo
        $request->pathInfo = '';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['', []], $result);
        // normal pathinfo
        $request->pathInfo = 'site/index';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['site/index', []], $result);
        // pathinfo with module
        $request->pathInfo = 'module/site/index';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['module/site/index', []], $result);
        // pathinfo with trailing slashes
        $request->pathInfo = '/module/site/index/';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['module/site/index/', []], $result);
        // pretty URL rules
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'cache' => null,
            'rules' => [
                [
                    'pattern' => 'post/<id>/<title>',
                    'route' => 'post/view',
                ],
            ],
        ]);
        // matching pathinfo
        $request->pathInfo = 'post/123/this+is+sample';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // trailing slash is significant
        $request->pathInfo = 'post/123/this+is+sample/';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/123/this+is+sample/', []], $result);
        // empty pathinfo
        $request->pathInfo = '';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['', []], $result);
        // normal pathinfo
        $request->pathInfo = 'site/index';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['site/index', []], $result);
        // pathinfo with module
        $request->pathInfo = 'module/site/index';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['module/site/index', []], $result);
        // pretty URL rules
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'suffix' => '.html',
            'cache' => null,
            'rules' => [
                [
                    'pattern' => 'post/<id>/<title>',
                    'route' => 'post/view',
                ],
            ],
        ]);
        // matching pathinfo
        $request->pathInfo = 'post/123/this+is+sample.html';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // matching pathinfo without suffix
        $request->pathInfo = 'post/123/this+is+sample';
        $result = $manager->parseRequest($request);
        $this->assertFalse($result);
        // empty pathinfo
        $request->pathInfo = '';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['', []], $result);
        // normal pathinfo
        $request->pathInfo = 'site/index.html';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['site/index', []], $result);
        // pathinfo without suffix
        $request->pathInfo = 'site/index';
        $result = $manager->parseRequest($request);
        $this->assertFalse($result);
        // strict parsing
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'suffix' => '.html',
            'cache' => null,
            'rules' => [
                [
                    'pattern' => 'post/<id>/<title>',
                    'route' => 'post/view',
                ],
            ],
        ]);
        // matching pathinfo
        $request->pathInfo = 'post/123/this+is+sample.html';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // unmatching pathinfo
        $request->pathInfo = 'site/index.html';
        $result = $manager->parseRequest($request);
        $this->assertFalse($result);
    }

    public function _testParseRESTRequest()
    {
        $request = new Request;
        // pretty URL rules
        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'cache' => null,
            'rules' => [
                'PUT,POST post/<id>/<title>' => 'post/create',
                'DELETE post/<id>' => 'post/delete',
                'post/<id>/<title>' => 'post/view',
                'POST/GET' => 'post/get',
            ],
        ]);
        // matching pathinfo GET request
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request->pathInfo = 'post/123/this+is+sample';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/view', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // matching pathinfo PUT/POST request
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $request->pathInfo = 'post/123/this+is+sample';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/create', ['id' => '123', 'title' => 'this+is+sample']], $result);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request->pathInfo = 'post/123/this+is+sample';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/create', ['id' => '123', 'title' => 'this+is+sample']], $result);
        // no wrong matching
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request->pathInfo = 'POST/GET';
        $result = $manager->parseRequest($request);
        $this->assertEquals(['post/get', []], $result);
        // createUrl should ignore REST rules
        $this->mockApplication([
            'components' => [
                'request' => [
                    'hostInfo' => 'http://localhost/',
                    'baseUrl' => '/app'
                ]
            ]
        ], \yii\web\Application::className());
        $this->assertEquals('/app/post/delete?id=123', $manager->createUrl(['post/delete', 'id' => 123]));
        $this->destroyApplication();
        unset($_SERVER['REQUEST_METHOD']);
    }

}