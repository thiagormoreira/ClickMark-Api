<?php
namespace ApplicationTest\Controller;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ApplicationControllerTest extends AbstractHttpControllerTestCase
{

    public function setUp ()
    {
        $this->setApplicationConfig(
                include '/var/www/clickmark/config/application.config.php');
        parent::setUp();
    }

    public function testIndexActionCanBeAccessed ()
    {
        $this->dispatch('/');
        $this->assertResponseStatusCode(200);
        
        $this->assertModuleName('Application');
        $this->assertControllerName('Application\Controller\Index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');
    }
}