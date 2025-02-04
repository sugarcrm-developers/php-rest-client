<?php

namespace Sugarcrm\REST\Endpoint;

use GuzzleHttp\Psr7\Response;
use MRussell\REST\Endpoint\ModelEndpoint;

class MLPackage extends SugarBean
{
    public const ACTION_INSTALL = 'install';

    public const ACTION_UNINSTALL = 'uninstall';

    public const ACTION_ENABLE = 'enable';

    public const ACTION_DISABLE = 'disable';

    public const ACTION_INSTALL_STATUS = 'installation-status';

    public const MLP_FIELD_PROP = 'upgrade_zip';

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
        self::BEAN_ACTION_ATTACH_FILE => 'POST',
    ];

    protected bool $_installing = false;

    protected array $_installOutput = [];

    public function setUrlArgs(array $args): static
    {
        if (isset($args[0])) {
            $this->set($this->getKeyProperty(), $args[0]);
            unset($args[0]);
        }
        return ModelEndpoint::setUrlArgs($args);
    }

    public function install(array $options = [], bool $async = false): static
    {
        $this->_installing = true;
        $this->setCurrentAction(self::ACTION_INSTALL);
        if ($async) {
            return $this->asyncExecute($options);
        } else {
            return $this->execute($options);
        }
    }

    public function isInstalling(): bool
    {
        return $this->_installing;
    }

    public function checkInstallStatus(): array
    {
        $this->setCurrentAction(self::ACTION_INSTALL_STATUS);
        $this->execute();
        return $this->_installOutput;
    }

    public function upload(string $filePath): static
    {
        $this->setCurrentAction(self::MODEL_ACTION_CREATE);
        $this->_upload = true;
        $this->setFile(self::MLP_FIELD_PROP, $filePath);
        return $this->execute();
    }

    protected function configurePayload(): mixed
    {
        $data = $this->getData();
        //If someone set field of ZIP, instead of using upload Method
        if (isset($data[self::MLP_FIELD_PROP]) && !$this->_upload && $this->getCurrentAction() !== self::MODEL_ACTION_CREATE) {
            $this->setFile(self::MLP_FIELD_PROP, $data[self::MLP_FIELD_PROP]);
            $this->_upload = true;
        }
        return parent::configurePayload();
    }

    protected function parseResponse(Response $response): void
    {
        parent::parseResponse($response);
        if ($response->getStatusCode() == 200) {
            $data = $this->getResponseBody();
            switch ($this->getCurrentAction()) {
                case self::ACTION_INSTALL:
                case self::ACTION_UNINSTALL:
                    $this->_installing = false;
                    break;
                case self::ACTION_INSTALL_STATUS:
                    if (!empty($data['message'])) {
                        $this->_installOutput = $data['message'] ?? [];
                    }
                    break;
            }
            if (($data['status'] ?? "") == 'installed') {
                $this->_installing = false;
            }
        }
    }

    /**
     * Setup the query params passed during File Uploads
     * @codeCoverageIgnore
     */
    protected function configureFileUploadQueryParams(): array
    {
        return [];
    }
}
