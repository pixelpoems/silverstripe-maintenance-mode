<?php

declare(strict_types=1);

namespace dljoseph\MaintenanceMode;

use PageController;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Control\Director;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;

/**
 * Displays a utility page to users who are not logged in as admin
 *
 * @package maintenancemode
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 */
class UtilityPageController extends PageController implements PermissionProvider
{

    private static array $url_handlers = [
        '*' => 'index'
    ];

    private static array $allowed_actions = [];

    public function index(): HTTPResponse|DBHTMLText
    {
        $config = $this->SiteConfig();

        // regular non-admin users should only be able to see this utility page in maintenance mode
        if (!$config->MaintenanceMode && !Permission::check('ADMIN')) {
            return $this->redirect(Director::absoluteBaseURL());
        }

        $this->response->setStatusCode($this->ErrorCode);

        if ($this->dataRecord->RenderingTemplate) {
            return $this->renderWith([
                $this->dataRecord->RenderingTemplate
            ]);
        }

        return $this->renderWith(['UtilityPage', 'Page']);
    }

    public function providePermissions()
    {
        return [
            'VIEW_SITE_MAINTENANCE_MODE' => 'Access the site in Maintenance Mode'
        ];
    }
}
