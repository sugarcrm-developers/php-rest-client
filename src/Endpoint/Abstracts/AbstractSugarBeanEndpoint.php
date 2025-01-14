<?php

/**
 * ©[2025] SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
 */

namespace Sugarcrm\REST\Endpoint\Abstracts;

use GuzzleHttp\Psr7\Stream;
use MRussell\REST\Endpoint\Data\DataInterface;
use GuzzleHttp\Exception\GuzzleException;
use MRussell\REST\Exception\Endpoint\InvalidDataType;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use MRussell\REST\Endpoint\ModelEndpoint;
use MRussell\REST\Endpoint\Traits\FileUploadsTrait;
use MRussell\REST\Exception\Endpoint\EndpointException;
use MRussell\REST\Traits\PsrLoggerTrait;
use Sugarcrm\REST\Endpoint\Data\FilterData;
use Sugarcrm\REST\Endpoint\AuditLog;
use Sugarcrm\REST\Endpoint\SugarEndpointInterface;
use Sugarcrm\REST\Endpoint\Traits\CompileRequestTrait;
use Sugarcrm\REST\Endpoint\Traits\FieldsDataTrait;
use Sugarcrm\REST\Endpoint\Traits\ModuleAwareTrait;

/**
 * SugarBean Endpoint acts as a base for any given Module API
 * - Provides action based interface for accessing stock and custom actions
 * @package Sugarcrm\REST\Endpoint\Abstracts
 * @method $this    filterLink(string $link_name = '',string $count = '')
 * @method $this    massLink(string $link_name)
 * @method $this    createLink(string $link_name)
 * @method $this    unlink(string $link_name,string $record_id)
 * @method $this    favorite()
 * @method $this    unfavorite()
 * @method $this    subscribe()
 * @method $this    unsubscribe()
 * @method $this    audit()
 * @method $this    file()
 * @method $this    duplicateCheck()
 */
abstract class AbstractSugarBeanEndpoint extends ModelEndpoint implements SugarEndpointInterface
{
    use CompileRequestTrait;
    use PsrLoggerTrait;
    use ModuleAwareTrait;
    use FieldsDataTrait;
    use FileUploadsTrait;

    public const MODEL_ACTION_VAR = 'action';

    public const BEAN_ACTION_RELATE = 'link';

    public const BEAN_ACTION_FILTER_RELATED = 'filterLink';

    public const BEAN_ACTION_MASS_RELATE = 'massLink';

    public const BEAN_ACTION_CREATE_RELATED = 'createLink';

    public const BEAN_ACTION_UNLINK = 'unlink';

    public const BEAN_ACTION_FAVORITE = 'favorite';

    public const BEAN_ACTION_UNFAVORITE = 'unfavorite';

    public const BEAN_ACTION_FOLLOW = 'subscribe';

    public const BEAN_ACTION_UNFOLLOW = 'unsubscribe';

    public const BEAN_ACTION_AUDIT = 'audit';

    public const BEAN_ACTION_FILE = 'file';

    public const BEAN_ACTION_DOWNLOAD_FILE = 'downloadFile';

    public const BEAN_ACTION_ATTACH_FILE = 'attachFile';

    public const BEAN_ACTION_TEMP_FILE_UPLOAD = 'tempFile';

    public const BEAN_ACTION_DUPLICATE_CHECK = 'duplicateCheck';

    public const BEAN_ACTION_ARG1_VAR = 'actionArg1';

    public const BEAN_ACTION_ARG2_VAR = 'actionArg2';

    public const BEAN_ACTION_ARG3_VAR = 'actionArg3';

    public const BEAN_MODULE_URL_ARG = 'module';

    /**
     * @inheritdoc
     */
    protected static array $_DEFAULT_PROPERTIES = [
        self::PROPERTY_URL => '$module/$id/$:action/$:actionArg1/$:actionArg2/$:actionArg3',
        self::PROPERTY_HTTP_METHOD => 'GET',
        self::PROPERTY_AUTH => true
    ];

    /**
     * All the extra actions that can be done on a Sugar Bean
     */
    protected static array $_DEFAULT_SUGAR_BEAN_ACTIONS = [
        self::BEAN_ACTION_FAVORITE => "PUT",
        self::BEAN_ACTION_UNFAVORITE => "PUT",
        self::BEAN_ACTION_FILTER_RELATED => "GET",
        self::BEAN_ACTION_RELATE => "POST",
        self::BEAN_ACTION_MASS_RELATE => "POST",
        self::BEAN_ACTION_UNLINK => "DELETE",
        self::BEAN_ACTION_CREATE_RELATED => "POST",
        self::BEAN_ACTION_FOLLOW => "POST",
        self::BEAN_ACTION_UNFOLLOW => "DELETE",
        self::BEAN_ACTION_AUDIT => "GET",
        self::BEAN_ACTION_FILE => "GET",
        self::BEAN_ACTION_DOWNLOAD_FILE => "GET",
        self::BEAN_ACTION_ATTACH_FILE => "POST",
        self::BEAN_ACTION_TEMP_FILE_UPLOAD => "POST",
        self::BEAN_ACTION_DUPLICATE_CHECK => "POST",
    ];

    /**
     * Files waiting to be attached to record
     */
    protected array $_uploadFile = [];

    /**
     * The file path where downloaded file is located
     */
    protected string $_downloadFile = '';

    protected bool $_deleteFileOnFail = false;

    public function __construct(array $urlArgs = [], array $properties = [])
    {
        parent::__construct($urlArgs, $properties);
        foreach (static::$_DEFAULT_SUGAR_BEAN_ACTIONS as $action => $method) {
            $this->actions[$action] = $method;
        }
    }

    /**
     * Passed in options get changed such that 1st Option (key 0) becomes Module
     * 2nd Option (Key 1) becomes ID
     * @inheritdoc
     */
    public function setUrlArgs(array $args): static
    {
        $args = $this->configureModuleUrlArg($args);
        if (isset($args[1])) {
            $this->set($this->getKeyProperty(), $args[1]);
            unset($args[1]);
        }

        return parent::setUrlArgs($args);
    }

    /**
     * Configure Uploads on Request
     * @inheritdoc
     */
    protected function configureRequest(Request $request, $data): Request
    {
        if ($this->_upload && !empty($this->_uploadFile['field']) && $this->_uploadFile['path']) {
            $request = $this->configureFileUploadRequest($request, [
                $this->_uploadFile['field'] => $this->_uploadFile['path'],
            ]);
            $data = null;
        } elseif ($this->getCurrentAction() === self::MODEL_ACTION_RETRIEVE) {
            $data = $this->configureFieldsDataProps($data);
        }

        return parent::configureRequest($request, $data);
    }

    /**
     * @return array|Stream|DataInterface|string|null
     */
    protected function configurePayload(): mixed
    {
        $data = $this->getData();
        switch ($this->getCurrentAction()) {
            case self::MODEL_ACTION_CREATE:
            case self::MODEL_ACTION_UPDATE:
                $data->reset();
                break;
            case self::BEAN_ACTION_DUPLICATE_CHECK:
                $data->reset();
                $data->set($this->toArray());
                break;
        }

        return parent::configurePayload();
    }

    /**
     * @inheritdoc
     *  - Add a reset for Upload Settings
     *  - Sync Model on Favorite/Unfavorite actions
     * - Set filename_guid and filename for temp file uploads
     */
    protected function parseResponse(Response $response): void
    {
        $this->resetUploads();
        if ($response->getStatusCode() == 200) {
            $this->getData()->reset();
            switch ($this->getCurrentAction()) {
                case self::BEAN_ACTION_TEMP_FILE_UPLOAD:
                    $body = $this->getResponseBody();
                    if (isset($body['record'])) {
                        $this->set([
                            'filename_guid' => $body['record']['id'],
                            'filename' => $body['filename']['guid'],
                        ]);
                    }

                    return;
                case self::BEAN_ACTION_FAVORITE:
                case self::BEAN_ACTION_UNFAVORITE:
                    $body = $this->getResponseBody();
                    $this->clear();
                    $this->syncFromApi($this->parseResponseBodyToArray($body, $this->getModelResponseProp()));
                    return;
            }
        }

        parent::parseResponse($response);
    }

    /**
     * @inheritDoc
     */
    public function reset(): static
    {
        $this->_fields = [];
        $this->_view = '';
        return parent::reset();
    }

    /**
     * @inheritDoc
     */
    public function clear(): static
    {
        $this->resetUploads();
        return parent::clear();
    }

    /**
     * Reset the Upload Settings back to defaults
     */
    protected function resetUploads(): void
    {
        if ($this->_upload) {
            $this->getData()->reset();
        }

        $this->_upload = false;
        $this->_uploadFile = [];
        $this->_deleteFileOnFail = false;
    }

    /**
     * Redefine some Actions to another Action, for use in URL
     * @inheritdoc
     */
    protected function configureURL(array $urlArgs): string
    {
        $action = null;
        $urlArgs = $this->configureModuleUrlArg($urlArgs);
        switch ($this->getCurrentAction()) {
            case self::BEAN_ACTION_CREATE_RELATED:
            case self::BEAN_ACTION_MASS_RELATE:
            case self::BEAN_ACTION_UNLINK:
            case self::BEAN_ACTION_FILTER_RELATED:
                $action = self::BEAN_ACTION_RELATE;
                break;
            case self::BEAN_ACTION_TEMP_FILE_UPLOAD:
                $urlArgs[self::MODEL_ID_VAR] = 'temp';
                // no break
            case self::BEAN_ACTION_ATTACH_FILE:
            case self::BEAN_ACTION_DOWNLOAD_FILE:
                $action = self::BEAN_ACTION_FILE;
                break;
            case self::BEAN_ACTION_DUPLICATE_CHECK:
                $urlArgs[self::MODEL_ID_VAR] = $this->getCurrentAction();
                // no break
            case self::MODEL_ACTION_DELETE:
            case self::MODEL_ACTION_UPDATE:
            case self::MODEL_ACTION_CREATE:
            case self::MODEL_ACTION_RETRIEVE:
                if (isset($urlArgs[self::MODEL_ACTION_VAR])) {
                    unset($urlArgs[self::MODEL_ACTION_VAR]);
                }

                break;
            default:
                $action = $this->getCurrentAction();
        }

        if ($action !== null && empty($urlArgs[self::MODEL_ACTION_VAR])) {
            $urlArgs[self::MODEL_ACTION_VAR] = $action;
        }

        return parent::configureURL($urlArgs);
    }

    /**
     * @inheritdoc
     */
    protected function configureAction(string $action, array $arguments = []): void
    {
        $urlArgs = $this->getUrlArgs();
        if (isset($urlArgs[self::BEAN_ACTION_ARG1_VAR])) {
            unset($urlArgs[self::BEAN_ACTION_ARG1_VAR]);
        }

        if (isset($urlArgs[self::BEAN_ACTION_ARG2_VAR])) {
            unset($urlArgs[self::BEAN_ACTION_ARG2_VAR]);
        }

        if (isset($urlArgs[self::BEAN_ACTION_ARG3_VAR])) {
            unset($urlArgs[self::BEAN_ACTION_ARG3_VAR]);
        }

        if (!empty($arguments)) {
            switch ($action) {
                case self::BEAN_ACTION_TEMP_FILE_UPLOAD:
                case self::BEAN_ACTION_ATTACH_FILE:
                    $this->_upload = true;
                    // no break
                case self::BEAN_ACTION_RELATE:
                case self::BEAN_ACTION_DOWNLOAD_FILE:
                case self::BEAN_ACTION_UNLINK:
                case self::BEAN_ACTION_CREATE_RELATED:
                case self::BEAN_ACTION_FILTER_RELATED:
                    if (isset($arguments[0])) {
                        $urlArgs[self::BEAN_ACTION_ARG1_VAR] = $arguments[0];
                        if (isset($arguments[1])) {
                            $urlArgs[self::BEAN_ACTION_ARG2_VAR] = $arguments[1];
                            if (isset($arguments[2])) {
                                $urlArgs[self::BEAN_ACTION_ARG3_VAR] = $arguments[2];
                            }
                        }
                    }
            }
        }

        $this->setUrlArgs($urlArgs);
        parent::configureAction($action, $arguments);
    }

    /**
     * System friendly name for subscribing to a record
     */
    public function follow(): static
    {
        return $this->subscribe();
    }

    /**
     * System friendly name for unsubscribing to a record
     */
    public function unfollow(): static
    {
        return $this->unsubscribe();
    }

    /**
     * Human friendly method name for Link action
     * @param string $linkName - Relationship Link Name
     * @param string $related_id - ID to Relate
     */
    public function relate(string $linkName, string $related_id): static
    {
        return $this->link($linkName, $related_id);
    }

    /**
     * Fetch Audits as a Collection on Current Record
     */
    public function auditLog(?int $limit = null): AuditLog
    {
        $auditCollection = new AuditLog([],['module' => $this->getModule(), 'id' => $this->get('id')]);

        if ($limit !== null) {
            $auditCollection->setLimit($limit);
        }

        $versionUpdated = false;
        $client = $this->getClient();
        if ($client) {
            $auditCollection->setClient($client);
            $originalClientVersion = $client->getVersion();
            // check if client version is older than 11_11
            if (version_compare($originalClientVersion, "11_11", "<")) {
                $client->setVersion("11_11");
                $versionUpdated = true;
            }
        } else {
            $auditCollection->setBaseUrl($this->getBaseUrl());
        }

        $auditCollection->fetch();

        if ($versionUpdated) {
            $client->setVersion($originalClientVersion);
        }

        return $auditCollection;
    }

    /**
     * Another Human Friendly overload, file & files are the same action
     */
    public function files(): AbstractSugarBeanEndpoint
    {
        return $this->file();
    }

    /**
     * @param string|null $destination
     * @throws GuzzleException
     */
    public function downloadFile(string $field, string $destination = null): AbstractSugarBeanEndpoint
    {
        $id = $this->get('id');
        if (empty($id) && empty($destination)) {
            throw new EndpointException("Download file only works when record ID is set or destination is passed.");
        }

        $this->setCurrentAction(self::BEAN_ACTION_DOWNLOAD_FILE, [$field]);
        if (empty($destination)) {
            $destination = tempnam(sys_get_temp_dir(), $id);
        }

        $this->_downloadFile = $destination;
        $stream = Utils::streamFor(fopen($destination, "w+"));
        return $this->execute(['sink' => $stream]);
    }

    /**
     * Get the downloaded file
     */
    public function getDownloadedFile(): string
    {
        return $this->_downloadFile;
    }

    /**
     * Human friendly overload for filterLink action
     * @param string $linkName - Name of Relationship Link
     */
    public function getRelated(string $linkName, bool $count = false): static
    {
        if ($count) {
            return $this->filterLink($linkName, 'count');
        }

        return $this->filterLink($linkName);
    }

    /**
     * Filter generator for Related Links
     * @param $linkName - Name of Relationship Link
     * @param bool $count - Whether or not to just do a count request
     */
    public function filterRelated(string $linkName, bool $count = false): FilterData
    {
        $Filter = new FilterData($this);
        $this->setCurrentAction(self::BEAN_ACTION_FILTER_RELATED);
        $args = [$linkName];
        if ($count) {
            $args[] = 'count';
        }

        $this->configureAction($this->action, $args);
        return $Filter;
    }

    /**
     * Mass Related records to current Bean Model
     */
    public function massRelate(string $linkName, array $related_ids): static
    {
        $this->setData([
            'link_name' => $linkName,
            'ids' => $related_ids,
        ]);
        return $this->massLink($linkName);
    }

    /**
     * Overloading attachFile dynamic method to handle more functionality for file uploads
     * @throws InvalidDataType
     */
    public function attachFile(
        string $fileField,
        string $filePath,
        bool $deleteOnFail = false,
        string $uploadName = '',
        string $mimeType = '',
    ): static {
        $this->setCurrentAction(self::BEAN_ACTION_ATTACH_FILE, [$fileField]);
        $this->_deleteFileOnFail = $deleteOnFail;
        $this->_upload = true;
        $this->setFile($fileField, $filePath, [
            'mimeType' => $mimeType,
            'filename' => $uploadName,
        ]);
        return $this->execute();
    }

    /**
     * Overloading tempFile dynamic method to provide more functionality
     * @throws InvalidDataType
     */
    public function tempFile(
        string $fileField,
        string $filePath,
        bool $deleteOnFail = true,
        string $uploadName = '',
        string $mimeType = '',
    ): static {
        $this->setCurrentAction(self::BEAN_ACTION_TEMP_FILE_UPLOAD, [$fileField]);
        $this->_upload = true;
        $this->_deleteFileOnFail = $deleteOnFail;
        $this->setFile($fileField, $filePath, [
            'mimeType' => $mimeType,
            'filename' => $uploadName,
        ]);
        $this->execute();
        return $this;
    }

    /**
     * Setup the query params passed during File Uploads
     */
    protected function configureFileUploadQueryParams(): array
    {
        $data = [
            'format' => 'sugar-html-json',
            'delete_if_fails' => $this->_deleteFileOnFail,
        ];

        if ($this->_deleteFileOnFail) {
            $Client = $this->getClient();
            if ($Client) {
                $data['platform'] = $Client->getPlatform();
                $token = $Client->getAuth()->getTokenProp('access_token');
                if ($token) {
                    $data['oauth_token'] = $Client->getAuth()->getTokenProp('access_token');
                }
            }
        }

        return $data;
    }

    /**
     * Add a file to the internal Files array to be added to the Request
     * @param $field
     * @param $path,
     */
    protected function setFile(string $field, string $path, array $properties = []): static
    {
        if (file_exists($path)) {
            $this->_uploadFile = array_replace($properties, [
                'field' => $field,
                'path' => $path,
            ]);
        }

        return $this;
    }
}
