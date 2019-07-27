<?php


namespace Firesphere\CSPHeaders\Tests;


use Firesphere\CSPHeaders\Models\SRI;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\DefaultAdminService;

class SRITest extends SapphireTest
{

    public function testCan()
    {
        $this->assertFalse((new SRI())->canView(null));
        $this->assertFalse((new SRI())->canEdit(null));
        $this->assertFalse((new SRI())->canDelete(null));
        $this->assertFalse((new SRI())->canCreate(null));
        $admin = DefaultAdminService::create()->findOrCreateAdmin('test', 'test');
        $this->logInAs($admin);
        $this->assertTrue((new SRI())->canView($admin));
        $this->assertFalse((new SRI())->canEdit($admin));
        $this->assertTrue((new SRI())->canDelete($admin));
        $this->assertFalse((new SRI())->canCreate($admin));
    }
}