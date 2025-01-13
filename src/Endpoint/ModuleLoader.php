<?php

namespace Sugarcrm\REST\Endpoint;

use Sugarcrm\REST\Endpoint\Abstracts\AbstractSugarBeanEndpoint;

class ModuleLoader extends SugarBean
{
    public const ACTION_INSTALL = 'install';

    public const ACTION_UNINSTALL = 'uninstall';

    public const ACTION_ENABLE = 'enable';

    public const ACTION_DISABLE = 'disable';

    public const ACTION_INSTALL_STATUS = 'installation-status';

    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => 'Administration/packages/$id/$:action',
        self::PROPERTY_AUTH => true,
    ];

    protected static array $_DEFAULT_SUGAR_BEAN_ACTIONS = [
        self::ACTION_INSTALL => 'GET',
        self::ACTION_UNINSTALL => 'GET',
        self::ACTION_ENABLE => 'GET',
        self::ACTION_DISABLE => 'GET',
        self::ACTION_INSTALL_STATUS => 'GET',
    ];

    /**
     * Setup the query params passed during File Uploads
     */
    protected function configureFileUploadQueryParams(): array
    {
        return [];
    }
}