<?php

declare(strict_types=1);

/**
 * Runs before the init function of every Page_Controller
 * to redirect regular non-admin users to the Utility
 * Page if maintenance mode is switched on.
 *
 * @package maintenancemode
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 * @author Patrick Nelson <pat@catchyour.com>
 */
namespace dljoseph\MaintenanceMode;

use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Extension;
use SilverStripe\Security\Permission;
use SilverStripe\SiteConfig\SiteConfig;

class ContentControllerExtension extends Extension
{

    /**
     * Allowed IP addresses
     */
    private static array $allowed_ips = [];

    public function onBeforeInit(): void
    {
        $config = SiteConfig::current_site_config();

        // If Maintenance Mode is Off, skip processing
        if (!$config->MaintenanceMode) {
            return;
        }


        // Check if the visitor is Admin OR if they have an allowed IP.
        if (Permission::check('VIEW_SITE_MAINTENANCE_MODE') || Permission::check('ADMIN') || $this->hasAllowedIP()) {
            return;
        }

        // Are we already on the UtilityPage? If so, skip processing.
        if ($this->getOwner() instanceof UtilityPageController) {
            return;
        }


        //Is visitor trying to hit the admin URL?  Give them a chance to log in.
        if (strstr($this->getOwner()->RelativeLink(), "Security")) {
            return;
        }


        // Fetch our utility page instance now.
        /**
         * @var Page $utilityPage
         */
        $utilityPage = UtilityPage::get()->first();
        if (!$utilityPage) {
            return;
        }

        // We need a utility page before we can do anything.

        // Are we configured to prevent redirection to the UtilityPage URL?
        if ($utilityPage->config()->DisableRedirect) {

            // Process the request internally to ensure that the URL is maintained
            // (instead of redirecting to the maintenance page's URL) and skip any further processing.

            $controller = ModelAsController::controller_for($utilityPage);
            $response = $controller->handleRequest(new HTTPRequest('GET', ''));

            throw new HTTPResponse_Exception($response);
        }

        // Default: Skip any further processing and immediately respond with a redirect to the UtilityPage.
        $response = HTTPResponse::create();
        $response->redirect($utilityPage->AbsoluteLink(), 302);

        throw new HTTPResponse_Exception($response);
    }

    /**
     * Check if the visitors IP is in the array of allowed IP's
     */
    public function hasAllowedIP(): bool
    {
        return in_array($this->getClientIP(), $this->getOwner()->config()->allowed_ips);
    }

    /**
     * Get the visitors IP based on the following
     *
     * @return string
     */
    public function getClientIP()
    {
        return $this->getOwner()->getRequest()->getIP();
    }
}
