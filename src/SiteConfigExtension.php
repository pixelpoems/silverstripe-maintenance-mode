<?php

declare(strict_types=1);

namespace dljoseph\MaintenanceMode;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;


/**
 * Add settings fields to SiteConfig to control maintenance mode
 *
 * @package maintenancemode
 *
 * @author Darren-Lee Joseph <darrenleejoseph@gmail.com>
 */
class SiteConfigExtension extends Extension
{

    /**
     * Add database field for flag to either display or hide under construction pages.
     */
    private static array $db = [
        'MaintenanceMode' => 'Boolean'
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        //create new tabs in SiteConfig
        $fields->addFieldToTab(
            'Root.Access',
            FieldGroup::create(
                CheckboxField::create('MaintenanceMode', _t('MaintenanceMode.SETTINGSACTIVATE', 'Activate Offline/Maintenance Mode'))
            )->setTitle(
                _t('MaintenanceMode.SETTINGSHEADING', 'Offline/Maintenance Mode')
            )
        );
    }
}
