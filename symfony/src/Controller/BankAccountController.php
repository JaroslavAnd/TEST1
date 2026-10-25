<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Repository\BankAccountRepository;
use App\Repository\ClientRepository;
use App\Service\PaginationService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/bank-accounts', name: 'api_bank_accounts_')]
class BankAccountController extends AbstractController
{
    #[Route('', name: 'list', methods: [Request::METHOD_GET])]
    public function list(Request $request, BankAccountRepository $repository, PaginationService $paginationService): JsonResponse
    {
        $qb = $repository->getFilteredQueryBuilder($request->query->all());
        $result = $paginationService->paginate($qb, $request);

        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: [Request::METHOD_GET])]
    public function show(int $id, BankAccountRepository $repository): JsonResponse
    {
        $account = $repository->find($id);
        if (!$account) {
            return new JsonResponse(['error' => 'Банківський рахунок не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $account->toArray()], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: [Request::METHOD_POST])]
    public function create(Request $request, BankAccountRepository $repository, ClientRepository $clientRepository, EntityManagerInterface $em): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];

        if (empty($body['clientId']) || empty($body['accountNumber'])) {
            return new JsonResponse([
                'error' => 'Поля clientId та accountNumber є обов\'язковими'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Перевірка унікальності номеру рахунку
        if ($repository->findOneBy(['accountNumber' => $body['accountNumber']])) {
            return new JsonResponse([
                'error' => 'Банківський рахунок із таким номером вже існує',
                'errors' => ['accountNumber' => 'Поле accountNumber має бути унікальним']
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $client = $clientRepository->find($body['clientId']);
        if (!$client) {
            return new JsonResponse([
                'error' => 'Клієнта з ID ' . $body['clientId'] . ' не знайдено'
            ], Response::HTTP_NOT_FOUND);
        }

        $account = new BankAccount();
        $account->setClient($client)
            ->setAccountNumber($body['accountNumber'])
            ->setBalance((string)($body['balance'] ?? '0.00'))
            ->setCurrency($body['currency'] ?? 'UAH')
            ->setStatus($body['status'] ?? 'active');

        $em->persist($account);
        $em->flush();

        return new JsonResponse([
            'data' => $account->toArray(),
            'message' => 'Банківський рахунок успішно створено'
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request, BankAccountRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $account = $repository->find($id);
        if (!$account) {
            return new JsonResponse(['error' => 'Банківський рахунок не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true) ?? [];

        // Перевірка унікальності accountNumber при зміні
        if (isset($body['accountNumber']) && $body['accountNumber'] !== $account->getAccountNumber()) {
            if ($repository->findOneBy(['accountNumber' => $body['accountNumber']])) {
                return new JsonResponse([
                    'error' => 'Банківський рахунок із таким номером вже існує',
                    'errors' => ['accountNumber' => 'Поле accountNumber має бути унікальним']
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $account->setAccountNumber($body['accountNumber']);
        }

        if (isset($body['balance'])) $account->setBalance((string)$body['balance']);
        if (isset($body['currency'])) $account->setCurrency($body['currency']);
        if (isset($body['status'])) $account->setStatus($body['status']);

        $em->flush();

        return new JsonResponse([
            'data' => $account->toArray(),
            'message' => 'Банківський рахунок успішно оновлено'
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id, BankAccountRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $account = $repository->find($id);
        if (!$account) {
            return new JsonResponse(['error' => 'Банківський рахунок не знайдено'], Response::HTTP_NOT_FOUND);
        }

        // Перевірка на наявність прив'язаних кредитів перед видаленням
        if ($account->getCredits()->count() > 0) {
            return new JsonResponse([
                'error' => 'Неможливо видалити банківський рахунок, оскільки до нього прив\'язані оформлені кредити'
            ], Response::HTTP_CONFLICT);
        }

        try {
            $em->remove($account);
            $em->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            return new JsonResponse([
                'error' => 'Неможливо видалити рахунок через обмеження зовнішнього ключа'
            ], Response::HTTP_CONFLICT);
        }

        return new JsonResponse(['message' => 'Банківський рахунок успішно видалено'], Response::HTTP_OK);
    }
}
