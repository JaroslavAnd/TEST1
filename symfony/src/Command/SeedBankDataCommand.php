<?php

namespace App\Command;

use App\Entity\BankAccount;
use App\Entity\Client;
use App\Entity\Credit;
use App\Entity\CreditType;
use App\Entity\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-bank-data',
    description: 'Seeds initial demonstration data for Banking Credit System (clients, accounts, credits, payments)',
)]
class SeedBankDataCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Check if data already exists
        $existing = $this->em->getRepository(Client::class)->findOneBy(['email' => 'ivan.kovalenko@example.com']);
        if ($existing) {
            $io->warning('Initial demonstration data already exists. Skipping seed.');
            return Command::SUCCESS;
        }

        $client1 = new Client();
        $client1->setFirstName('Іван')
            ->setLastName('Коваленко')
            ->setEmail('ivan.kovalenko@example.com')
            ->setPhone('+380501112233')
            ->setTaxId('1234567890');
        $this->em->persist($client1);

        $client2 = new Client();
        $client2->setFirstName('Олена')
            ->setLastName('Петренко')
            ->setEmail('olena.petrenko@example.com')
            ->setPhone('+380672223344')
            ->setTaxId('0987654321');
        $this->em->persist($client2);

        $acc1 = new BankAccount();
        $acc1->setClient($client1)
            ->setAccountNumber('UA213052990000026001234567891')
            ->setBalance('150000.00')
            ->setCurrency('UAH')
            ->setStatus('active');
        $this->em->persist($acc1);

        $acc2 = new BankAccount();
        $acc2->setClient($client2)
            ->setAccountNumber('UA213052990000026009876543212')
            ->setBalance('85000.00')
            ->setCurrency('UAH')
            ->setStatus('active');
        $this->em->persist($acc2);

        $creditType1 = new CreditType();
        $creditType1->setName('Споживчий кредит')
            ->setInterestRate('18.50')
            ->setMinAmount('5000.00')
            ->setMaxAmount('100000.00')
            ->setTermMonths(12);
        $this->em->persist($creditType1);

        $creditType2 = new CreditType();
        $creditType2->setName('Автокредит')
            ->setInterestRate('14.00')
            ->setMinAmount('100000.00')
            ->setMaxAmount('800000.00')
            ->setTermMonths(36);
        $this->em->persist($creditType2);

        $credit1 = new Credit();
        $credit1->setClient($client1)
            ->setBankAccount($acc1)
            ->setCreditType($creditType1)
            ->setAmount('50000.00')
            ->setRemainingAmount('40000.00')
            ->setStartDate((new \DateTime())->modify('-2 months'))
            ->setEndDate((new \DateTime())->modify('+10 months'))
            ->setStatus('active');
        $this->em->persist($credit1);

        $payment1 = new Payment();
        $payment1->setCredit($credit1)
            ->setAmount('10000.00')
            ->setPaymentDate((new \DateTime())->modify('-1 month'))
            ->setPaymentMethod('bank_transfer')
            ->setStatus('completed');
        $this->em->persist($payment1);

        $this->em->flush();

        $io->success('Demonstration banking data seeded successfully!');

        return Command::SUCCESS;
    }
}
