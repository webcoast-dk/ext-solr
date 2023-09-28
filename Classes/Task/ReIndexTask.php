<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace ApacheSolrForTypo3\Solr\Task;

use ApacheSolrForTypo3\Solr\Domain\Index\Queue\QueueInitializationService;
use Doctrine\DBAL\ConnectionException as DBALConnectionException;
use Doctrine\DBAL\Exception as DBALException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Scheduler task to empty the indexes of a site and re-initialize the
 * Solr Index Queue thus making the indexer re-index the site.
 *
 * @author Christoph Moeller <support@network-publishing.de>
 */
class ReIndexTask extends AbstractSolrTask
{
    /**
     * Indexing configurations to re-initialize.
     */
    protected array $indexingConfigurationsToReIndex = [];

    /**
     * Initializes the Index Queue
     * and returns TRUE if the execution was successful
     *
     * @return bool Returns TRUE on success, FALSE on failure.
     *
     * @throws DBALConnectionException
     * @throws DBALException
     *
     * @noinspection PhpMissingReturnTypeInspection See {@link \TYPO3\CMS\Scheduler\Task\AbstractTask::execute()}
     */
    public function execute()
    {
        // initialize for re-indexing
        /** @var QueueInitializationService $indexQueueInitializationService */
        $indexQueueInitializationService = GeneralUtility::makeInstance(QueueInitializationService::class);
        $indexQueueInitializationResults = $indexQueueInitializationService
            ->initializeBySiteAndIndexConfigurations($this->getSite(), $this->indexingConfigurationsToReIndex);

        return !in_array(false, $indexQueueInitializationResults);
    }

    /**
     * Gets the indexing configurations to re-index.
     */
    public function getIndexingConfigurationsToReIndex(): array
    {
        return $this->indexingConfigurationsToReIndex;
    }

    /**
     * Sets the indexing configurations to re-index.
     */
    public function setIndexingConfigurationsToReIndex(array $indexingConfigurationsToReIndex): void
    {
        $this->indexingConfigurationsToReIndex = $indexingConfigurationsToReIndex;
    }

    /**
     * This method is designed to return some additional information about the task,
     * that may help to set it apart from other tasks from the same class
     * This additional information is used - for example - in the Scheduler's BE module
     * This method should be implemented in most task classes
     *
     * @return string Information to display
     *
     * @throws DBALException
     *
     * @noinspection PhpMissingReturnTypeInspection See {@link \TYPO3\CMS\Scheduler\Task\AbstractTask::getAdditionalInformation()}
     */
    public function getAdditionalInformation()
    {
        $site = $this->getSite();
        if (is_null($site)) {
            return 'Invalid site configuration for scheduler please re-create the task!';
        }

        $information = 'Site: ' . $this->getSite()->getLabel();
        if (!empty($this->indexingConfigurationsToReIndex)) {
            $information .= ', Indexing Configurations: ' . implode(
                ', ',
                $this->indexingConfigurationsToReIndex
            );
        }

        return $information;
    }
}
