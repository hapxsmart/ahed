<?php
namespace Aheadworks\Sarp2\Console\Command;

use Magento\Framework\DB\Ddl\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Magento\Setup\Module\Setup;

/**
 * Class AddDisplayingModeColumn
 *
 * @package Aheadworks\Sarp2\Console\Command
 */
class AddDisplayingModeColumn extends Command
{
    /**
     * @var Setup
     */
    private $setup;

    /**
     * @param Setup $setup
     * @param string|null $name
     */
    public function __construct(
        Setup $setup,
        string $name = null
    ) {
        parent::__construct($name);
        $this->setup = $setup;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('aw-sarp2:add-frontend-labels-displaying-mode-column')
            ->setDescription('Add frontend displaying mode column');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->setup->getConnection();

        if (!$connection->tableColumnExists(
            'aw_sarp2_plan_definition',
            'frontend_displaying_mode'
        )) {
            $connection->addColumn(
                $this->setup->getTable('aw_sarp2_plan_definition'),
                'frontend_displaying_mode',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Frontend displaying mode',
                    'size' => 255
                ]
            );
            $connection->query(
                'UPDATE ' . $this->setup->getTable('aw_sarp2_plan_definition') .
                ' SET frontend_displaying_mode = \'subscription\''
            );
        }

        if (!$connection->tableColumnExists(
            'aw_sarp2_profile_definition',
            'frontend_displaying_mode'
        )) {
            $connection->addColumn(
                $this->setup->getTable('aw_sarp2_profile_definition'),
                'frontend_displaying_mode',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Frontend displaying mode',
                    'size' => 255
                ]
            );
            $connection->query(
                'UPDATE ' . $this->setup->getTable('aw_sarp2_profile_definition') .
                ' SET frontend_displaying_mode = \'subscription\''
            );
        }

        $output->writeln('Installment displaying mode column has been added');
        return Cli::RETURN_SUCCESS;
    }
}
