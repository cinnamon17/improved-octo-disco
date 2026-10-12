<?php

namespace App\Command;

use App\Service\CreditManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Anula reservas de ai_usage_log que quedaron en RESERVED porque el worker de
 * Messenger murio entre el dispatch y el consumo. Sin esto, ese credito no se
 * devuelve nunca.
 *
 * Pensado para cron diario.
 */
#[AsCommand(
    name: 'app:void-stale-credit-reservations',
    description: 'Anula reservas de creditos huerfanas y devuelve el saldo'
)]
class VoidStaleCreditReservationsCommand extends Command
{
    private CreditManager $creditManager;

    public function __construct(CreditManager $creditManager)
    {
        parent::__construct();

        $this->creditManager = $creditManager;
    }

    protected function configure(): void
    {
        $this->addOption(
            'minutes',
            null,
            InputOption::VALUE_REQUIRED,
            'Antiguedad de una reserva para considerarla huerfana',
            30
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $minutes = (int) $input->getOption('minutes');
        $threshold = new \DateTimeImmutable(sprintf('-%d minutes', $minutes));

        $voided = $this->creditManager->voidStaleReservations($threshold);

        $io->success(sprintf('Reservas anuladas: %d', $voided));

        return Command::SUCCESS;
    }
}