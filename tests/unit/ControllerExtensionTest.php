<?php


namespace Firesphere\CSPHeaders\Tests;

use BadMethodCallException;
use Firesphere\CSPHeaders\Extensions\ControllerCSPExtension;
use Firesphere\CSPHeaders\View\CSPBackend;
use Page;
use PageController;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\Cookie;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\NullHTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Security;

class ControllerExtensionTest extends SapphireTest
{
    public function setUp(): void
    {
        parent::setUp();
        CSPBackend::config()->merge('useNonce', false);
    }
    public function testInit()
    {
        $this->assertFalse(ControllerCSPExtension::checkCookie(new NullHTTPRequest()));
        $page = new Page();
        $controller = new PageController($page);

        // Shouldn't add CSP if not enabled
        $extension = new ControllerCSPExtension();
        $extension->setOwner($controller);
        $config = CSPBackend::config()->get('csp_config');
        $config['enabled'] = false;
        CSPBackend::config()->merge('csp_config', $config);
        $this->assertFalse($extension->isAddPolicyHeaders());
        $extension->onBeforeInit();

        $this->assertArrayNotHasKey('content-security-policy-report-only', $controller->getResponse()->getHeaders());

        // Should add CSP if enabled (and build headers not requested)
        $config['enabled'] = true;
        CSPBackend::config()->merge('csp_config', $config);
        $request = new HTTPRequest('GET', '/');
        $controller->setRequest($request);
        $extension = new ControllerCSPExtension();
        $extension->setOwner($controller);
        $this->assertFalse($extension->isAddPolicyHeaders());
        $extension->onBeforeInit();
        $this->assertNotNull($extension->getNonce());
    }

    public function testNonceOnExcludedControllers()
    {
        //when CSPBackend.useNonce is true, it should only apply to controllers
        //with the extension applied. By default, this is page controller
        CSPBackend::setUsesNonce(true);
        $page = new Page();
        $controller = new PageController($page);
        $extension = new ControllerCSPExtension();
        $extension->setOwner($controller);

        //useNonce is set but only applies on the PageController.
        //let's check Security controller for logins: it should be absent
        $secController = new Security();
        $this->expectException('BadMethodCallException');
        $this->assertNull($secController->getNonce());

        //also check CMS-level controllers
        $cmsController = new LeftAndMain();
        $this->expectException('BadMethodCallException');
        $this->assertNull($secController->getNonce());

        //now apply the extension, getNonce should not be null
        $extension2 = new ControllerCSPExtension();
        $extension2->setOwner($secController);
        $this->assertNotNull($secController->getNonce());

        $extension3 = new ControllerCSPExtension();
        $extension3->setOwner($cmsController);
        $this->assertNotNull($cmsController->getNonce());
    }
}
