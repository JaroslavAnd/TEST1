<?php

namespace App\Controller;

use App\Entity\Credit;
use App\Repository\BankAccountRepository;
use App\Repository\ClientRepository;
use App\Repository\CreditRepository;
use App\Repository\CreditTypeRepository;
use App\Service\PaginationService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/credits', name: 'api_credits_')]
class CreditController extends AbstractController
{
    #[Route('', name: 'list', methods: [Request::METHOD_GET])]
    public function list(Request $request, CreditRepository $repository, PaginationService $paginationService): JsonResponse
    {
        $qb = $repository->getFilteredQueryBuilder($request->query->all());
        $result = $paginationService->paginate($qb, $request);

        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: [Request::METHOD_GET])]
    public function show(int $id, CreditRepository $repository): JsonResponse
    {
        $credit = $repository->find($id);
        if (!$credit) {
            return new JsonResponse(['error' => 'Кредит не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $credit->toArray()], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: [Request::METHOD_POST])]
    public function create(
        Request $request,
        ClientRepository $clientRepo,
        BankAccountRepository $bankAccountRepo,
        CreditTypeRepository $creditTypeRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $body = json_decode($request->getContent(), true) ?? [];

        if (empty($body['clientId']) || empty($body['bankAccountId']) || empty($body['creditTypeId']) || empty($body['amount'])) {
            return new JsonResponse([
                'error' => 'Поля clientId, bankAccountId, creditTypeId та amount є обов\'язковими'
            ], Response::HTTP_BAD_REQUEST);
        }

        $client = $clientRepo->find($body['clientId']);
        if (!$client) {
            return new JsonResponse(['error' => 'Клієнта не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $bankAccount = $bankAccountRepo->find($body['bankAccountId']);
        if (!$bankAccount) {
            return new JsonResponse(['error' => 'Банківський рахунок не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $creditType = $creditTypeRepo->find($body['creditTypeId']);
        if (!$creditType) {
            return new JsonResponse(['error' => 'Тип кредиту не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $amount = (string)$body['amount'];
        $termMonths = (int)($body['termMonths'] ?? $creditType->getTermMonths() ?? 12);

        $startDate = !empty($body['startDate']) ? new \DateTime($body['startDate']) : new \DateTime();
        $endDate = (clone $startDate)->modify("+{$termMonths} months");

        $credit = new Credit();
        $credit->setClient($client)
            ->setBankAccount($bankAccount)
            ->setCreditType($creditType)
            ->setAmount($amount)
            ->setRemainingAmount((string)($body['remainingAmount'] ?? $amount))
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setStatus($body['status'] ?? 'active');

        $em->persist($credit);
        $em->flush();

        return new JsonResponse([
            'data' => $credit->toArray(),
            'message' => 'Кредит успішно оформлено'
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request, CreditRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $credit = $repository->find($id);
        if (!$credit) {
            return new JsonResponse(['error' => 'Кредит не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true) ?? [];

        if (isset($body['remainingAmount'])) $credit->setRemainingAmount((string)$body['remainingAmount']);
        if (isset($body['status'])) $credit->setStatus($body['status']);
        if (isset($body['amount'])) $credit->setAmount((string)$body['amount']);

        $em->flush();

        return new JsonResponse([
            'data' => $credit->toArray(),
            'message' => 'Дані кредиту успішно оновлено'
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id, CreditRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $credit = $repository->find($id);
        if (!$credit) {
            return new JsonResponse(['error' => 'Кредит не знайдено'], Response::HTTP_NOT_FOUND);
        }

        try {
            $em->remove($credit);
            $em->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            return new JsonResponse([
                'error' => 'Неможливо видалити кредит через обмеження зовнішнього ключа'
            ], Response::HTTP_CONFLICT);
        }

        return new JsonResponse(['message' => 'Кредит успішно видалено'], Response::HTTP_OK);
    }
}
