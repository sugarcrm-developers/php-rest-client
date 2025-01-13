<?php

namespace Sugarcrm\REST\Endpoint;

class AuditLog extends SugarBeanCollection
{
    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => '$module/$id/audit',
        self::PROPERTY_AUTH => true,
    ];
}