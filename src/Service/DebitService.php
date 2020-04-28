<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Transaction;
use App\Form\DebitForm;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class DebitService
{
    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;
    /**
     * @var ValidationService
     */
    private $validationService;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ValidationService $validationService,
        EntityManagerInterface $entityManager
    ) {
        $this->responseFactory = $responseFactory;
        $this->validationService = $validationService;
        $this->entityManager = $entityManager;
    }

    public function debit(DebitForm $form): array
    {
        if ($errors = $this->validationService->validateForm($form)) {
            return $errors;
        }

        $this->entityManager->beginTransaction();
        $transaction = (new Transaction())
            ->setAmount($form->getAmount())
            ->setCreatedAt(new \DateTime())
        ;
        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        $result = $this->entityManager->getConnection()->executeQuery('select sum(amount) from transaction');
        $sum = $result->fetchColumn();
        $result->closeCursor();

        $limit = getenv('USER_BALANCE');

        if ((float) $sum > (float) $limit) {
            $errors['amount'] = 'Balance limit exceeded.';
            $this->entityManager->rollback();
        } else {
            $this->entityManager->commit();
        }

        return $errors;
    }
}
