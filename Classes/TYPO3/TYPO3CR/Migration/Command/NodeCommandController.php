<?php
namespace TYPO3\TYPO3CR\Migration\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3CR".               *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\TYPO3CR\Migration\Service\NodeMigration;
use TYPO3\TYPO3CR\Migration\Domain\Model\MigrationStatus;
use TYPO3\TYPO3CR\Migration\Domain\Model\MigrationConfiguration;
use TYPO3\Flow\Annotations as Flow;

/**
 * Command controller for tasks related to node handling.
 *
 * @Flow\Scope("singleton")
 */
class NodeCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Configuration\Source\YamlSource
	 */
	protected $yamlSourceImporter;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository
	 */
	protected $nodeDataRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Migration\Domain\Repository\MigrationStatusRepository
	 */
	protected $migrationStatusRepository;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Migration\Domain\Factory\MigrationFactory
	 */
	protected $migrationFactory;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface
	 */
	protected $contextFactory;

	/**
	 * Do the configured migrations in the given migration file for the given workspace
	 *
	 * By default the up direction is applied, using the direction parameter this can
	 * be changed.
	 *
	 * @param string $version The version of the migration configuration you want to use.
	 * @param boolean $confirmation Confirm application of this migration, only needed if the given migration contains any warnings.
	 * @param string $direction The direction to work in, MigrationStatus::DIRECTION_UP or MigrationStatus::DIRECTION_DOWN
	 * @return void
	 */
	public function migrateCommand($version, $confirmation = FALSE, $direction = MigrationStatus::DIRECTION_UP) {
		$migrationConfiguration = $direction === MigrationStatus::DIRECTION_UP ?
			$this->migrationFactory->getMigrationForVersion($version)->getUpConfiguration() :
			$this->migrationFactory->getMigrationForVersion($version)->getDownConfiguration();

		$this->outputCommentsAndWarnings($migrationConfiguration);
		if ($migrationConfiguration->hasWarnings() && $confirmation === FALSE) {
			$this->outputLine();
			$this->outputLine('Migration has warnings. You need to confirm execution by adding the "--confirmation TRUE" option to the command.');
			$this->quit(1);
		}

		$nodeMigrationService = new NodeMigration($migrationConfiguration->getMigration());
		$nodeMigrationService->execute();
		$migrationStatus = new MigrationStatus($version, $direction, new \DateTime());
		$this->migrationStatusRepository->add($migrationStatus);
		$this->outputLine();
		$this->outputLine('Successfully applied migration.');
	}

	/**
	 * List available and applied migrations
	 *
	 * @return void
	 * @see typo3.typo3cr.migration:node:listavailablemigrations
	 */
	public function migrationStatusCommand() {
		/** @var $appliedMigration MigrationStatus */
		$this->outputLine();

		$availableMigrations = $this->migrationFactory->getAvailableMigrationsForCurrentConfigurationType();
		if (count($availableMigrations) === 0) {
			$this->outputLine('No migrations available.');
			$this->quit();
		}

		$tableRows = array();
		foreach ($availableMigrations as $version => $migration) {
			$migrationConfiguration = $this->migrationFactory->getMigrationForVersion($version)->getUpConfiguration();
			$tableRows[] = array($version, $migration['formattedVersionNumber'], $migration['package']->getPackageKey(), $migrationConfiguration->getComments());
		}

		$this->outputLine('<b>Available migrations</b>');
		$this->outputLine();
		$this->output->outputTable($tableRows, array('Version', 'Date', 'Package', 'Comments'));
	}

	/**
	 * Helper to output comments and warnings for the given configuration.
	 *
	 * @param \TYPO3\TYPO3CR\Migration\Domain\Model\MigrationConfiguration $migrationConfiguration
	 * @return void
	 */
	protected function outputCommentsAndWarnings(MigrationConfiguration $migrationConfiguration) {
		if ($migrationConfiguration->hasComments()) {
			$this->outputLine();
			$this->outputLine('<b>Comments</b>');
			$this->outputFormatted($migrationConfiguration->getComments(), array(), 2);
		}

		if ($migrationConfiguration->hasWarnings()) {
			$this->outputLine();
			$this->outputLine('<b><u>Warnings</u></b>');
			$this->outputFormatted($migrationConfiguration->getWarnings(), array(), 2);
		}
	}
}
